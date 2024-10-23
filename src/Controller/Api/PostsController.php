<?php

namespace App\Controller\Api;

use App\Entity\Posts;
use App\Entity\PostsTranslation;
use App\Entity\Category;
use App\Entity\DailyRecype;
use App\Entity\CategoryTranslation;
use App\Entity\Subcategory;
use App\Repository\CommentsRepository;
use App\Repository\PostsRepository;
use App\Repository\PostsTranslationRepository;
use App\Repository\ParagraphPostsTranslationRepository;
use App\Repository\SubcategoryRepository;
use App\Repository\CategoryRepository;
use App\Repository\KeywordRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\Cookie;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Contracts\Translation\TranslatorInterface;
use Doctrine\ORM\EntityManagerInterface;

#[Route(
    path: '/api/posts',
    name: 'api_posts_',
)]
class PostsController extends ApiController
{
    private $entityManager;
    public function __construct(
        EntityManagerInterface $entityManager,
    )
    {
        $this->entityManager = $entityManager;
    }
    #[Route('/homeAll', name: 'home', methods: ['GET'])]
    public function home(TranslatorInterface $translator, PostsRepository $postsRepository, CategoryRepository $categoryRepository): JsonResponse
    {      

        $post = $postsRepository->findBy(['slug' => 'home']);
        $postsTranslation = $post[0]->getTranslations();

        if ($post) {
            return $this->json([
                'post' => $post[0],
                'translation' => $postsTranslation,
            ],
                Response::HTTP_OK,
                [],
                [
                    "groups" => ["api_posts_home"]
                ]
            );
        }
        $responsePosts = [];
        foreach ($posts as $post) {
            $translation = $post->getTranslations()->filter(function ($t) use ($_locale) {
                return $t->getLocale() === $_locale;
            })->first();

            // Si une traduction existe, utilisez-la. Sinon, utilisez le contenu de base
            $responsePosts[] = [
                'id' => $post->getId(),
                'heading' => $translation ? $translation->getHeading() : $post->getHeading(),
                'title' => $translation ? $translation->getTitle() : $post->getTitle(),
                'metaDescription' => $translation ? $translation->getMetaDescription() : $post->getMetaDescription(),
                'contents' => $translation ? $translation->getContents() : $post->getContents(),
            ];
        }

        return $this->json(
            [
                 'home' =>  $responsePosts,
            ],
            Response::HTTP_OK,
            [],
            [
                "groups" => 
                [
                    "api_posts_home"
                ]
            ]
        );
    }

    #[Route('/category/{slug}', name: 'category', methods: ['GET'])]
    public function category(string $slug): JsonResponse
    {
        $category = $this->entityManager->getRepository(Category::class)->findOneBy(['slug' => $slug]);
        $posts = $this->entityManager->getRepository(Posts::class)->findBy(['category' => $category, 'draft' => false],  ['createdAt' => 'DESC']);

        if ($posts){
            return $this->json(
                $posts,
                Response::HTTP_OK,
                [],
                [
                    "groups" => 
                    [
                        "api_posts_category"
    
                    ]
                ]
            );
        }
        $categoryTranslation = $this->entityManager->getRepository(CategoryTranslation::class)->findOneBy(['slug' => $slug]);
        $postsTrans = $this->entityManager->getRepository(Posts::class)->findBy(['category' => $categoryTranslation->getCategory(), 'draft' => false],  ['createdAt' => 'DESC']);

        if ($postsTrans){
            return $this->json(
                $postsTrans,
                Response::HTTP_OK,
                [],
                [
                    "groups" => 
                    [
                        "api_posts_category_translations"
                    ]
                ]
            );
        }
        
    }
    #[Route('/category=blog', name: 'blog', methods: ['GET'])]
    public function categoryBlog(PostsRepository $postsRepository, PostsTranslationRepository $postsTranslationRepository): JsonResponse
    {
        $posts = $postsRepository->findByCategoryExcluding('Page');
        $postsTrans = $postsTranslationRepository->findByCategoryExcluding('Page');
        $data = array_merge($posts, $postsTrans);

        return $this->json(
            $data,
            Response::HTTP_OK,
            [],
            [
                "groups" => 
                [
                    "api_posts_blog"

                ]
            ]
        );
    }
    #[Route('&subcategory={slug}', name: 'subcategory', methods: ['GET'])]
    public function subcategory(PostsRepository $postsRepository, Subcategory $subcategory): JsonResponse
    {
        $posts = $postsRepository->findBy(['subcategory' => $subcategory, 'draft' => false],  ['createdAt' => 'DESC']);

        return $this->json(
            $posts,
            Response::HTTP_OK,
            [],
            [
                "groups" => 
                [
                    "api_posts_subcategory"

                ]
            ]
        );
    }

    #[Route('&limit=3&category={slug}', name: 'category_limit', methods: ['GET'])]
    public function limit(PostsRepository $postsRepository, Category $category): JsonResponse
    {
        $posts = $postsRepository->findBy(['category' => $category, 'draft' => false], ['createdAt' => 'DESC'], 3);


        return $this->json(
            $posts,
            Response::HTTP_OK,
            [],
            [
                "groups" => 
                [
                    "api_posts_category_limit"

                ]
            ]
        );
    }
        
    #[Route('&limit=3&filter=desc&category={slug}', name: 'desc', methods: ['GET'])]
    public function desc(PostsRepository $postsRepository, string $slug ): JsonResponse
    {

        $posts = $postsRepository->findByCategorySlug($slug, 3);


        return $this->json(
            $posts,
            Response::HTTP_OK,
            [],
            [
                "groups" => 
                [
                    "api_posts_desc"
                ]
            ]
        );
    }

    #[Route('/all', name: 'all', methods: ['GET'])]
    public function all(PostsRepository $postsRepository, PostsTranslationRepository $postsTranslationRepository ): JsonResponse
    {
        $langs = [ 'en', 'es', 'de', 'it'];
        $pages = [ 'contact', 'dons'];

        $pageAdds = []; 

        foreach ($pages as $page) { 
            $pageAdds[] = [ 
                'locale' => 'fr',
                'url' => 'fr/' . $page,
                'translations' => [] 
            ];

            foreach ($langs as $lang) {
                $pageAdds[count($pageAdds) - 1]['translations'][] = [ 
                    'locale' => $lang,
                    'url' => $lang . '/' . $page,
                ];
            }
        }

        // $postsTranslation = $postsTranslationRepository->findBy(['draft' => false]);
        $postAll = array_merge($pageAdds, $postsRepository->findAllPosts(['draft' => false]));
            
        return $this->json(
            $postAll ,
            Response::HTTP_OK,
            [],
            [
                "groups" => 
                [
                    "api_posts_all",
                ]
            ]
        );
    }

    #[Route('/sitemap', name: 'sitemap', methods: ['GET'])]
    public function sitemap(PostsRepository $postsRepository ): JsonResponse
    {
    
        $excludeSlugs = ['search'];

        $allPosts = $postsRepository->findAllPostsExcludingSlugs($excludeSlugs);
    
        return $this->json(
            $allPosts,
            Response::HTTP_OK,
            [],
            [
                "groups" => 
                [
                    "api_posts_sitemap"
                ]
            ]
        );
    }

    #[Route('/thumbnail/{slug}', name: 'thumbnail', methods: ['GET'])]
    public function thumbnail(PostsRepository $postsRepository, Posts $posts = null ): JsonResponse
    {
    
        if ($posts === null)
        {
            // on renvoie donc une 404
            return $this->json(
                [
                    "erreur" => "Page non trouvée",
                    "code_error" => 404
                ],
                Response::HTTP_NOT_FOUND,// 404
            );
        }

        return $this->json(
            $posts,
            Response::HTTP_OK,
            [],
            [
                "groups" => 
                [
                    "api_posts_thumbnail"
                ]
            ]
        );
    }

    // #[Route('/{slug}', name: 'read', methods: ['GET'])]
    // public function read(string $slug, Posts $post, CommentsRepository $commentRepository,PostsRepository $postsRepository, PostsTranslationRepository $postsTranslationRepository)
    // {     
        
    //     // $post = $postsRepository->findBy(['slug' => $slug]);
        
    //     $comments = $commentRepository->findNonReplyComments($post->getId());

    //     // $commentsCollection = new ArrayCollection($comments);

    //     // $post->setComments($commentsCollection);
        
    //     $postsTranslation = $postsTranslationRepository->findBy(['slug' => $slug]);
        
    //     if ($postsTranslation) {
    //         $post = $postsTranslation;
    //         return $this->json([
    //             'post' => $postsTranslation[0],
    //         ],
    //             Response::HTTP_OK,
    //             [],
    //             [
    //                 "groups" => ["api_posts_read"]
    //             ]
    //         );
    //     }
        


    //     return $this->json404();
    // }

    #[Route('/{locale}/{slug}', name: 'read', methods: ['GET'])]
    public function read(string $slug, string $locale, CommentsRepository $commentRepository, PostsRepository $postsRepository, PostsTranslationRepository $postsTranslationRepository, ParagraphPostsTranslationRepository $paragraphPostsTranslationRepository)
    {     
        
        $post = $postsRepository->findOneBy(['slug' => $slug, 'locale' => $locale]);
        $postsTranslation = $postsTranslationRepository->findBy(['post' => $post]);
        
        if ($post) {
            // Si le post est trouvé, retourner les informations du post
            $postsTranslation = $postsTranslationRepository->findBy(['post' => $post]);
    
            return $this->json([
                'post' => $post,
            ],
            Response::HTTP_OK,
            [],
            [
                "groups" => ["api_posts_read"]
            ]);
        }

        $postsTranslation = $postsTranslationRepository->findOneBy(['slug' => $slug, 'locale' => $locale]);

        if ($postsTranslation) {
            return $this->json([
                'post' => $postsTranslation->getPost(),
            ],
            Response::HTTP_OK,
            [],
            [
                "groups" => ["api_posts_read_translation"]
            ]);
        }

    }

    #[Route('&filter=subcategory', name: 'allSubcategory', methods: ['GET'])]
    public function allSubcategory(SubcategoryRepository $subcategories ): JsonResponse
    {
    
        $subcategories = $subcategories->findAll();

        return $this->json(
            $subcategories,
            Response::HTTP_OK,
            [],
            [
                "groups" => ["api_posts__allSubcategory"]
            ]
        );
    }
    #[Route('/draft/{locale}/{slug}', name: 'draft', methods: ['GET'])]
    public function readDraft(string $slug, string $locale, PostsRepository $postsRepository, PostsTranslationRepository $postsTranslationRepository, ParagraphPostsTranslationRepository $paragraphPostsTranslationRepository): JsonResponse
    {
    
        $draft = $postsRepository->findOneBy([  'locale' => $locale, 'slug' => $slug]);
        $draftTranslation = $postsTranslationRepository->findOneBy([  'locale' => $locale, 'slug' => $slug]);

        return $this->json(
            $draft ?? $draftTranslation,
            Response::HTTP_OK,
            [],
            [
                "groups" => ["api_posts_draft"]
            ]
        );
    }
    #[Route('&filter=keyword&limit=3&id={id}', name: 'keyword', methods: ['GET'])]
    public function postsFilterKeyword(PostsRepository $postsRepository, KeywordRepository $keywordRepository,  int $id): JsonResponse
    {
        $responsePosts = [];

        $post = $postsRepository->find($id);
        
        $postId = $post->getId();
        $postsKeyword = $post->getKeywords()->getValues();
        if($postsKeyword === [])
        {
            $responsePosts = $postsRepository->findByCategorySlug($post->getCategory()->getSlug(), 3);
            return $this->json(
                $responsePosts,
                Response::HTTP_OK,
                [],
                [
                    "groups" => 
                    [
                        "api_posts_keyword"
                    ]
                ]
            );
        }

        foreach ($postsKeyword as $keyword) {
            $postsKeyword = $keyword->getPosts();
            $filteredPostId = $postsKeyword->filter(function ($otherPost) use ($postId) {
                return $otherPost->getId() != $postId && !$otherPost->isDraft() && $otherPost->getSlug() !== 'Accueil';
            });
            
            foreach ($filteredPostId as $filteredPost) {
                $filteredPosts[] = $filteredPost;
            }
        }
        
        $sortedPosts = $filteredPosts; 
        
        usort($sortedPosts, function ($a, $b) {
            $updatedAtA = $a->getUpdatedAt();
            $updatedAtB = $b->getUpdatedAt();

            if ($updatedAtA && $updatedAtB) {
                return $updatedAtB <=> $updatedAtA;
            } elseif ($updatedAtA && !$updatedAtB) {
                return -1;
            } elseif (!$updatedAtA && $updatedAtB) {
                return 1;
            } else {
                $createdAtA = $a->getCreatedAt();
                $createdAtB = $b->getCreatedAt();
                return $createdAtB <=> $createdAtA;
            }
        });
        
        if (count($sortedPosts) > 3) {
            $responsePosts = array_slice($sortedPosts, 0, 3);
        } else {
            $responsePosts = $postsRepository->findByCategorySlug($post->getCategory()->getSlug(), 3);

        }
        return $this->json(
            $responsePosts,
            Response::HTTP_OK,
            [],
            [
                "groups" => 
                [
                    "api_posts_keyword"
                ]
            ]
        );
    }
    #[Route('/daily-recype', name: 'daily', methods: ['GET'])]
    public function daily( ): JsonResponse
    {
        $recype = $this->entityManager->getRepository(DailyRecype::class)->findAll();

        return $this->json(
            $recype,
            Response::HTTP_OK,
            [],
            [
                "groups" => 
                [
                    "api_posts_daily"

                ]
            ]
        );
    }

}