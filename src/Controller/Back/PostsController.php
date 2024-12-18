<?php

namespace App\Controller\Back;

use App\Entity\Posts;
use App\Entity\Category;
use App\Entity\CategoryTranslation;
use App\Entity\PostsTranslation;
use App\Entity\ParagraphPostsTranslation;
use App\Form\PostsType;
use App\Message\TriggerNextJsBuild;
use App\Repository\PostsRepository;
use App\Repository\PostsTranslationRepository;
use App\Repository\ParagraphPostsRepository;
use App\Repository\ParagraphPostsTranslationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Messenger\MessageBusInterface;
use DateTime;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Service\ImageOptimizer;
use Doctrine\ORM\EntityManagerInterface;
use \IntlDateFormatter;
use App\Service\MarkdownProcessor;
use App\Service\UrlGeneratorService;
use App\Service\TranslationService;
use App\Service\TranslationServiceDecode;

#[Route('/posts')]
class PostsController extends AbstractController
{
    private $imageOptimizer;
    private $slugger;
    private $entityManager;
    private $markdownProcessor;
    private $urlGeneratorService;
    private $translationService;
    private $translationServiceDecode;

    public function __construct(
        ImageOptimizer $imageOptimizer,
        SluggerInterface $slugger,
        EntityManagerInterface $entityManager,
        MarkdownProcessor $markdownProcessor,
        UrlGeneratorService $urlGeneratorService,
        TranslationService $translationService,
        TranslationServiceDecode $translationServiceDecode,
    )
    {
        $this->entityManager = $entityManager;
        $this->imageOptimizer = $imageOptimizer;
        $this->slugger = $slugger;
        $this->markdownProcessor = $markdownProcessor;
        $this->urlGeneratorService = $urlGeneratorService;
        $this->translationService = $translationService;
        $this->translationServiceDecode = $translationServiceDecode;
        $this->translations = [ 'es', 'en', 'it', 'de' ];

    }
    private function createSlug(string $inputString): string
    {
        return strtolower($this->slugger->slug($inputString)->slice(0, 50)->toString());
    }
    #[Route('/', name: 'app_back_posts_index', methods: ['GET'])]
    public function index(PostsRepository $postsRepository, Request $request): Response
    {
        return $this->render('back/posts/index.html.twig', [
            'posts' => $postsRepository->findAll(),
        ]);
    }

    #[Route('/category/{name}', name: 'app_back_posts_list', methods: ['GET'])]
    public function categoryPage(PostsRepository $postsRepository, Category $category): Response
    {
        $posts = $postsRepository->findBy(['category' => $category]);
        return $this->render('back/posts/index.html.twig', [
            'posts' => $posts,
            'category' => $category,
        ]);
    }

    #[Route('/new', name: 'app_back_posts_new', methods: ['GET', 'POST'])]
    public function new(Request $request, PostsRepository $postsRepository, MessageBusInterface $messageBus, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $post = new Posts();

        $category = new Category();

        $form = $this->createForm(PostsType::class, $post);
        $form->handleRequest($request);
        
        
        if ($form->isSubmitted() && $form->isValid()) {

            // SLUG
            $slug = $this->createSlug($post->getTitle());
            
            $categorySlug = $post->getCategory() ? $post->getCategory()->getSlug() : null;
            $subcategorySlug = $post->getSubcategory() ? $post->getSubcategory()->getSlug() : null;

            if($post->getSlug() !== "home") {
                
                $slug = $this->createSlug($post->getTitle());
                $post->setSlug($slug);

            
                $url = $this->urlGeneratorService->generatePath($slug, $categorySlug, $subcategorySlug, 'fr');

                $post->setUrl($url);

            } else {
                $post->setSlug('home');
                $url = '/';
                $post->setUrl($url);
            }

            // IMAGE Principal
            $brochureFile = $form->get('imgPost')->getData();
            if (empty($brochureFile)) {
                $post->setImgPost('Accueil');
                $post->setAltImg('Une Taupe Chez Vous ! image de présentation');
                $post->setImgWidth('1000');
                $post->setImgHeight('563');
            } else {
                $this->imageOptimizer->setPicture($brochureFile, $post, $slug);
            }

            // ALT IMG
            if (empty($post->getAltImg())) {
                $post->setAltImg($post->getTitle());
            } else {
                $post->setAltImg($post->getAltImg());
            }
            
            // DATE
            $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, null, null, 'dd MMMM yyyy');
            $post->setCreatedAt(new DateTime());
            $createdAt = $formatter->format($post->getCreatedAt());

            $post->setFormattedDate('Publié le ' . $createdAt);
            
            
            // MARKDOWN TO HTML
            $contentsText = $form->get('visibleContents')->getData();
            $htmlText = $this->markdownProcessor->processMarkdown($contentsText);
            $post->setContents($htmlText);


            foreach ($this->translations as $locale) {
                $translation = new PostsTranslation();
                $translation->setContents($this->translationService->translateText($post->getContents(), $locale));
                $translation->setTitle($this->translationServiceDecode->translateText($post->getTitle(), $locale));
                $translation->setPost($post);
                
                // Category

                $categoryRepository = $em->getRepository(CategoryTranslation::class);

                $pageCategory = $categoryRepository->findOneBy(['slug' => 'Page']);

                if ($post->getCategory()->getSlug() === 'Page') {
                    $translation->setCategory($pageCategory);
                }
                else {
                
                    $categoryTranslations = $post->getCategory()->getCategoryTranslations()->filter(function ($translations) use ($locale) {
                        return $translations->getLocale() === $locale;
                    });
                    $categoryTranslation = $categoryTranslations->first();
    
                    $translation->setCategory($categoryTranslation);
                }
                // $translation->setSubCategory($this->translationService->translateText($post->getSubCategory(), $locale));

                // SLug 
                if($translation->getSlug() !== $locale . "home") {

                    $categorySlug = $translation->getCategory() ? $translation->getCategory()->getSlug() : null;
                    $subcategorySlug = $translation->getSubcategory() ? $translation->getSubcategory()->getSlug() : null;

                    $translation->setSlug(
                        $this->createSlug(
                        $this->translationService->translateText($post->getSlug(), $locale)
                    ));
                    $urlTranslation = $this->urlGeneratorService->generatePath($translation->getSlug(), $categorySlug, $subcategorySlug, $locale);
                    $translation->setUrl($urlTranslation);
                } else {
                    $translation->setSlug($locale .'home');
                    $url = '/' . $locale;
                    $translation->setUrl($url);
                }
    

                $translation->setFormattedDate($this->translationService->translateText('Published on ', $locale) . $createdAt);
                $translation->setLocale($locale);
                $translation->setHeading($this->translationServiceDecode->translateText($post->getHeading(), $locale));
                $translation->setAltImg($this->translationServiceDecode->translateText($post->getAltImg(), $locale));
                $translation->setMetaDescription($this->translationServiceDecode->translateText($post->getMetaDescription(), $locale));
    
                $this->entityManager->persist($translation);
            }
            $this->entityManager->flush();

            
            // $message = new TriggerNextJsBuild('Build');
            // $messageBus->dispatch($message);
            // $buildResponse = $message->getContent();

            $postsRepository->save($post, true);
            return $this->render('back/posts/edit.html.twig', [
            'post' => $post,
            'form' => $form,
            ]);

        }
        return $this->render('back/posts/new.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }


    #[Route('/{id}', name: 'app_back_posts_show', methods: ['GET'])]
    public function show(Posts $post): Response
    {
        return $this->render('back/posts/show.html.twig', [
            'post' => $post,
        ]);
    }


    #[Route('/{id}/edit', name: 'app_back_posts_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Posts $post, $id, PostsRepository $postsRepository, MessageBusInterface $messageBus, PostsTranslationRepository $postsTranslationRepository, ParagraphPostsRepository $paragraphPostsRepository, ParagraphPostsTranslationRepository $paragraphPostsTranslationRepository, EntityManagerInterface $em ): Response
    {
        $imgPost = $post->getImgPost();
        
        $articles = $postsRepository->findAll();
        $form = $this->createForm(PostsType::class, $post);
        $form->handleRequest($request);

        $postExist = $postsRepository->find($id);
        if ($form->isSubmitted() && $form->isValid() ) {
            
            $translations = $postsTranslationRepository->findByPostAndLanguage($post);
            
            $originalContents = $form->get('contents')->getData();
            $contentsText = $form->get('visibleContents')->getData();
            if ($originalContents !== $contentsText) {
                // MARKDOWN TO HTML
                $htmlText = $this->markdownProcessor->processMarkdown($contentsText);
                $post->setContents($htmlText);
                $this->entityManager->persist($post);
                $this->entityManager->flush();
            }

            
            // // SLUG
            // $slug = $this->createSlug($post->getTitle());
            // $categorySlug = $post->getCategory() ? $post->getCategory()->getSlug() : null;
            // $subcategorySlug = $post->getSubcategory() ? $post->getSubcategory()->getSlug() : null;
            // if($post->getSlug() !== "home") {
            //     $post->setSlug($slug);
                
            //     $url = $this->urlGeneratorService->generatePath($slug, $categorySlug, $subcategorySlug, 'fr');
                
            //     $post->setUrl($url);
                
            // } else {
            //     $post->setSlug('home');
            //     $url = '/';
            //     $post->setUrl($url);
            // }
            
            // IMAGE Principal
            $brochureFile = $form->get('imgPost')->getData();
            
            if (!empty($brochureFile)) {
                $this->imageOptimizer->setPicture($brochureFile, $post, $post->getSlug());
            } else {
                $post->setImgPost($imgPost);
            }
            
            // DATE
            $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, null, null, 'dd MMMM yyyy');
            $post->setUpdatedAt(new DateTime());
            $updatedDate = $formatter->format($post->getUpdatedAt());
            $createdAt = $formatter->format($post->getCreatedAt());

            $post->setFormattedDate('Publié le ' . $createdAt . '. Mise à jour le ' . $updatedDate);
            
            // PARAGRAPH
            $paragraphPosts = $form->get('paragraphPosts')->getData();

            foreach ($paragraphPosts as $paragraph) {

                // MARKDOWN TO HTML
                $markdownText = $paragraph->getParagraph();

                $htmlText = $this->markdownProcessor->processMarkdown($markdownText);

                $paragraph->setParagraph($htmlText);

                // LINK
                $articleLink = $paragraph->getLinkPostSelect();
                if ($articleLink !== null) {
                    
                    $paragraph->setLinkSubtitle($articleLink->getTitle());
                    $slugLink = $articleLink->getSlug();

                    $categoryLink = $articleLink->getCategory()->getSlug();
                    if ($categoryLink === "Page") {
                        $paragraph->setLink('/'.$slugLink);
                    }                     
                    if ($categoryLink === "Annuaire") {
                        $paragraph->setLink('/'.$categoryLink.'/'.$slugLink);
                    } 
                    if ($categoryLink === "Articles") {
                        $subcategoryLink = $articleLink->getSubcategory()->getSlug();
                        $paragraph->setLink('/'.$categoryLink.'/'.$subcategoryLink.'/'.$slugLink);
                    }
            } 

          
            
            // $deletedLink = $form['paragraphPosts'];

            // if ($deletedLink[$paragraphPosts->indexOf($paragraph)]['deleteLink']->getData() === true) {
            //     $paragraph->setLink(null);
            //     $paragraph->setLinkSubtitle(null);
            // }

            // SLUG
            if (!empty($paragraph->getSubtitle())) {
                $slugPara = $this->createSlug($paragraph->getSubtitle());
                $slugPara = substr($slugPara, 0, 30); 
                $paragraph->setSlug($slugPara);

            } else {
                $this->entityManager->remove($paragraph);
                $this->entityManager->flush();
                }

            // IMAGE PARAGRAPH
            if (!empty($paragraph->getImgPostParaghFile())) {
                $brochureFileParagraph = $paragraph->getImgPostParaghFile();
                $slugPara = $this->createSlug($paragraph->getSubtitle());
                $slugPara = substr($slugPara, 0, 30);
                $paragraph->setImgPostParagh($slugPara);
                $this->imageOptimizer->setPicture($brochureFileParagraph, $paragraph, $slugPara);
                
                // ALT IMG PARAGRAPH
                if (empty($paragraph->getAltImg())) {
                    $paragraph->setAltImg($paragraph->getSubtitle());
                } 
            }
        } 

            // Translations
            foreach ($translations as $translation) {
                $translation->setContents($this->translationService->translateText($post->getContents(), $translation->getLocale()));
                $translation->setTitle($this->translationServiceDecode->translateText($post->getTitle(), $translation->getLocale()));
                $translation->setPost($post);
                $translation->setDraft($post->isDraft());

                // Paragraphe 
                $paragraphPostsCollection = $post->getParagraphPosts(); 
                $translationCollection = $translation->getParagraphPosts(); 

                foreach ($paragraphPostsCollection as $paragraph) {
                   
                    $paragraphTranslation = $translationCollection->filter(function($existingTranslation) use ($paragraph, $translation) {
                        return $existingTranslation->getParagraphPosts()->getId() === $paragraph->getId() ;
                    })->first();
                    
                    if (!$paragraphTranslation) {
                        $paragraphTranslation = new ParagraphPostsTranslation();
                        $paragraph->addParagraphPostsTranslation($paragraphTranslation);
                        $translation->addParagraphPost($paragraphTranslation);
                    } 
                    
                    $translatedParagraphContent = $this->translationService->translateText($paragraph->getParagraph(), $translation->getLocale());
                    $translatedSubtitle = $this->translationServiceDecode->translateText($paragraph->getSubtitle(), $translation->getLocale());
                    
                    $paragraphTranslation->setParagraph($translatedParagraphContent);
                    $paragraphTranslation->setSubtitle($translatedSubtitle);
                    $paragraphTranslation->setSlug($this->createSlug($translatedSubtitle));

                    $this->entityManager->persist($paragraphTranslation);

                }


                // Category

                $categoryRepository = $em->getRepository(CategoryTranslation::class);

                $pageCategory = $categoryRepository->findOneBy(['slug' => 'Page']);

                if ($post->getCategory()->getSlug() === 'Page') {
                    $translation->setCategory($pageCategory);
                }
                else {
                
                    $categoryTranslations = $post->getCategory()->getCategoryTranslations()->filter(function ($translations) use ($translation) {
                        return $translations->getLocale() === $translation->getLocale();
                    });
                    $categoryTranslation = $categoryTranslations->first();
    
                    $translation->setCategory($categoryTranslation);
                }


                
                // SLug

                // if($translation->getSlug() !== $translation->getLocale() . "home") {
                //     $translation->setSlug(
                //         $this->createSlug($translation->getTitle())
                //     );
                //     $categorySlug = $translation->getCategory() ? $translation->getCategory()->getSlug() : null;
                //     $subcategorySlug = $translation->getSubcategory() ? $translation->getSubcategory()->getSlug() : null;

                //     $urlTranslation = $this->urlGeneratorService->generatePath($translation->getSlug(), $categorySlug, $subcategorySlug, $translation->getLocale());
                //     $translation->setUrl($urlTranslation);
                // }
                

                $translation->setFormattedDate($this->translationService->translateText('Published on ', $translation->getLocale()) . $createdAt);
                $translation->setLocale($translation->getLocale());
                $translation->setHeading($this->translationServiceDecode->translateText($post->getHeading(), $translation->getLocale()));
                $translation->setMetaDescription($this->translationServiceDecode->translateText($post->getMetaDescription(), $translation->getLocale()));
                $translation->setAltImg($this->translationServiceDecode->translateText($post->getAltImg(), $translation->getLocale()));
    
                $this->entityManager->persist($translation);
                $this->entityManager->flush();
            }


            $postsRepository->save($post, true);

            $message = new TriggerNextJsBuild('Build');
            $messageBus->dispatch($message);
            
            return $this->redirectToRoute('app_back_posts_index', [
            ], Response::HTTP_SEE_OTHER);
            
        }
        $keyChatGpt = $_ENV['CHATGPT_API_KEY'];
        return $this->render('back/posts/edit.html.twig', [
            'post' => $post,
            'form' => $form,
            'articles' => $articles,
            'keyChatGpt' => $keyChatGpt,
         ]);
    }


    #[Route('/{id}', name: 'app_back_posts_delete', methods: ['POST'])]
    public function delete(Request $request, Posts $post, PostsRepository $postsRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $this->imageOptimizer->deletedPicture($post->getSlug());
            $postsRepository->remove($post, true);

        }

        return $this->redirectToRoute('app_back_posts_index', [], Response::HTTP_SEE_OTHER);
    }


   
}
