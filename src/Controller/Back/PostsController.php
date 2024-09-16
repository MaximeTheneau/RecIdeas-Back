<?php

namespace App\Controller\Back;

use App\Entity\Posts;
use App\Entity\Category;
use App\Entity\ListPosts;
use App\Entity\ParagraphPosts;
use App\Entity\Keyword;
use App\Entity\PostsTranslation;
use App\Form\PostsType;
use App\Message\TriggerNextJsBuild;
use App\Repository\PostsRepository;
use App\Repository\PostsTranslationRepository;
use App\Repository\CategoryRepository;
use App\Repository\ParagraphPostsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use DateTime;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use App\Service\ImageOptimizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Michelf\MarkdownExtra;
use \IntlDateFormatter;
use App\Service\MarkdownProcessor;
use App\Service\UrlGeneratorService;
use App\Service\TranslationService;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\DependencyInjection\ContainerInterface;


#[Route('/posts')]
class PostsController extends AbstractController
{
    private $params;
    private $imageOptimizer;
    private $slugger;
    private $photoDir;
    private $projectDir;
    private $entityManager;
    private $markdown;
    private $markdownProcessor;
    private $urlGeneratorService;
    private $translationService;

    public function __construct(
        ContainerBagInterface $params,
        ImageOptimizer $imageOptimizer,
        SluggerInterface $slugger,
        EntityManagerInterface $entityManager,
        MarkdownProcessor $markdownProcessor,
        UrlGeneratorService $urlGeneratorService,
        TranslationService $translationService
    )
    {
        $this->params = $params;
        $this->entityManager = $entityManager;
        $this->imageOptimizer = $imageOptimizer;
        $this->slugger = $slugger;
        $this->projectDir =  $this->params->get('app.projectDir');
        $this->photoDir =  $this->params->get('app.imgDir');
        $this->markdown = new MarkdownExtra();
        $this->markdownProcessor = $markdownProcessor;
        $this->urlGeneratorService = $urlGeneratorService;
        $this->translationService = $translationService;
        $this->translations = [ 'es', 'en', 'it', 'de' ];

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
    public function new(Request $request, PostsRepository $postsRepository, MessageBusInterface $messageBus): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $post = new Posts();

        $category = new Category();

        $form = $this->createForm(PostsType::class, $post);
        $form->handleRequest($request);
        
        
        if ($form->isSubmitted() && $form->isValid()) {
        



            // SLUG
            $slug =  strtolower($this->slugger->slug($post->getTitle()));
            
            $categorySlug = $post->getCategory() ? $post->getCategory()->getSlug() : null;
            $subcategorySlug = $post->getSubcategory() ? $post->getSubcategory()->getSlug() : null;

            if($post->getSlug() !== "home") {
                
                $slug = $this->slugger->slug($post->getTitle());
                $post->setSlug($slug);

            
                $url = $this->urlGeneratorService->generatePath($slug, $categorySlug, $subcategorySlug, 'fr');

                $post->setUrl($url);

            } else {
                $post->setSlug('Accueil');
                $url = '';
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
            $contentsText = $post->getContents();
            
            $htmlText = $this->markdownProcessor->processMarkdown($contentsText);
            
            $post->setContents($htmlText);


            foreach ($this->translations as $locale) {
                $translation = new PostsTranslation();
                $translation->setContents($this->translationService->translateText($post->getContents(), $locale));
                $translation->setTitle($this->translationService->translateText($post->getTitle(), $locale));
                $translation->setPost($post);
                $translation->setCategory($post->getCategory());
                $translation->setSubCategory($post->getSubCategory());

                // SLug 
                $translation->setSlug(
                    strtolower($this->slugger->slug(
                    $this->translationService->translateText($post->getSlug(), $locale)
                )));
                $urlTranslation = $this->urlGeneratorService->generatePath($translation->getSlug(), $categorySlug, $subcategorySlug, $locale);
                $translation->setUrl($urlTranslation);
                

                $translation->setFormattedDate($this->translationService->translateText('Published on ', $locale) . $createdAt);
                $translation->setLocale($locale);
                $translation->setHeading($this->translationService->translateText($post->getHeading(), $locale));
                $translation->setMetaDescription($this->translationService->translateText($post->getMetaDescription(), $locale));
    
                $this->entityManager->persist($translation);
            }
            $this->entityManager->flush();

            
            // $message = new TriggerNextJsBuild('Build');
            // $messageBus->dispatch($message);
            // $buildResponse = $message->getContent();

            $postsRepository->save($post, true);
            return $this->render('back/posts/edit.html.twig', [
            'post' => $post,

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
    public function edit(Request $request, Posts $post, $id, PostsRepository $postsRepository, MessageBusInterface $messageBus, PostsTranslationRepository $postsTranslationRepository): Response
    {
        $imgPost = $post->getImgPost();
        
        $articles = $postsRepository->findAll();
        $form = $this->createForm(PostsType::class, $post);
        $form->handleRequest($request);

        $postExist = $postsRepository->find($id);
        if ($form->isSubmitted() && $form->isValid() ) {
            
            
            // SLUG
            $slug = $this->slugger->slug($post->getTitle());
            $categorySlug = $post->getCategory() ? $post->getCategory()->getSlug() : null;
            $subcategorySlug = $post->getSubcategory() ? $post->getSubcategory()->getSlug() : null;
            if($post->getSlug() !== "Accueil") {
                $post->setSlug($slug);
                
                $url = $this->urlGeneratorService->generatePath($slug, $categorySlug, $subcategorySlug, 'fr');
                
                $post->setUrl($url);
                
            } else {
                $post->setSlug('Accueil');
                $url = '/';
                $post->setUrl($url);
            }
            
            // IMAGE Principal
            $brochureFile = $form->get('imgPost')->getData();
            
            if (!empty($brochureFile)) {
                $this->imageOptimizer->setPicture($brochureFile, $post, $slug);
            } else {
                $post->setImgPost($imgPost);
            }
            
            // DATE
            $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, null, null, 'dd MMMM yyyy');
            $post->setUpdatedAt(new DateTime());
            $updatedDate = $formatter->format($post->getUpdatedAt());
            $createdAt = $formatter->format($post->getCreatedAt());

            $post->setFormattedDate('Publié le ' . $createdAt . '. Mise à jour le ' . $updatedDate);
            
            $translations = $postsTranslationRepository->findByPostAndLanguage($post);
            foreach ($translations as $translation) {
                $translation->setContents($this->translationService->translateText($post->getContents(), $translation->getLocale()));
                $translation->setTitle($this->translationService->translateText($post->getTitle(), $translation->getLocale()));
                $translation->setPost($post);
                $translation->setCategory($post->getCategory());
                $translation->setSubCategory($post->getSubCategory());

                // SLug 
                
                $translation->setSlug(
                    strtolower($this->slugger->slug(
                    $this->translationService->translateText($translation->getTitle(), $translation->getLocale())
                )));
                $urlTranslation = $this->urlGeneratorService->generatePath($translation->getSlug(), $categorySlug, $subcategorySlug, $translation->getLocale());
                $translation->setUrl($urlTranslation);
                

                $translation->setFormattedDate($this->translationService->translateText('Published on ', $translation->getLocale()) . $createdAt);
                $translation->setLocale($translation->getLocale());
                $translation->setHeading($this->translationService->translateText($post->getHeading(), $translation->getLocale()));
                $translation->setMetaDescription($this->translationService->translateText($post->getMetaDescription(), $translation->getLocale()));
    
                $this->entityManager->persist($translation);
                $this->entityManager->flush();
            }


            $postsRepository->save($post, true);

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
