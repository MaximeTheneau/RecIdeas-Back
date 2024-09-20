<?php

namespace App\Controller\Back;

use App\Entity\Category;
use App\Entity\CategoryTranslation;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Service\TranslationService;
use Doctrine\ORM\EntityManagerInterface;


#[Route('/category')]
class CategoryController extends AbstractController
{

    private $slugger;
    private $translationService;
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        TranslationService $translationService
    )
    {
        $this->slugger = $slugger;
        $this->translationService = $translationService;
        $this->translations = [ 'es', 'en', 'it', 'de' ];
        $this->entityManager = $entityManager;
    }
    

    #[Route('/', name: 'app_back_category_index', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository): Response
    {
        return $this->render('back/category/index.html.twig', [
            'categories' => $categoryRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_back_category_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CategoryRepository $categoryRepository): Response
    {
        $category = new Category();

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        
        if ($form->isSubmitted() && $form->isValid()) {

            // Create slug 
            if(empty($category->getSlug())) {
                $slug = strtolower($this->slugger->slug($category->getName()));
                $category->setSlug($slug);
            }
            $category->setLocale('fr');

            foreach ($this->translations as $locale) {
                $categoryTranslation = new CategoryTranslation();
                $categoryTranslation->setName($this->translationService->translateText($category->getName(), $locale));
                $categoryTranslation->setSlug($this->translationService->translateText($category->getSlug(), $locale));
                $categoryTranslation->setLocale($locale);
                $categoryTranslation->setCategory($category);
                $this->entityManager->persist($categoryTranslation);
            }
            $this->entityManager->flush();


            $categoryRepository->save($category, true);

            return $this->redirectToRoute('app_back_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/category/new.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_back_category_show', methods: ['GET'])]
    public function show(Category $category): Response
    {
        return $this->render('back/category/show.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_back_category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Category $category, CategoryRepository $categoryRepository): Response
    {
        $orginalCategorySlug = $category->getSlug();
        $orginalCategoryName = $category->getName();
        
        
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        

        if ($form->isSubmitted() && $form->isValid()) {
            // Create slug 
            if($orginalCategorySlug && $orginalCategoryName !== $form->get('name')->getData() ) {
                $slug = $this->slugger->slug($category->getName());
                $category->setSlug($slug);
                $slug = $this->slugger->slug($category->getName());
            }
            // Empty slug
            if(empty($category->getSlug())) {
                $slug = $this->slugger->slug($category->getName());
                $category->setSlug($slug);
            }            
            

            $categoryRepository->save($category, true);
            return $this->redirectToRoute('app_back_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/category/edit.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_back_category_delete', methods: ['POST'])]
    public function delete(Request $request, Category $category, CategoryRepository $categoryRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->request->get('_token'))) {
            $categoryRepository->remove($category, true);
        }

        return $this->redirectToRoute('app_back_category_index', [], Response::HTTP_SEE_OTHER);
    }
}


