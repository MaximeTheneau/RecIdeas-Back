<?php

namespace App\Controller\Back;

use App\Entity\Translate;
use App\Entity\TranslateTranslation;
use App\Form\TranslateType;
use App\Repository\TranslateRepository;
use App\Repository\TranslateTranslationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\TranslationService;

#[Route('/translate')]
class TranslateController extends AbstractController
{
    private $translationService;
    private $entityManager;

    public function __construct(
        TranslationService $translationService,
        EntityManagerInterface $entityManager,

        ){
            $this->translationService = $translationService;
            $this->translations = [ 'es', 'en', 'it', 'de' ];
            $this->entityManager = $entityManager;
        }

    #[Route('/', name: 'app_back_translate_index', methods: ['GET'])]
    public function index(TranslateRepository $translateRepository): Response
    {
        return $this->render('back/translate/index.html.twig', [
            'translates' => $translateRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_back_translate_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $translate = new Translate();
        $form = $this->createForm(TranslateType::class, $translate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($translate);
            $entityManager->flush();

            foreach ($this->translations as $locale) {
                $translation = new TranslateTranslation();
                $translation->setLocale($locale);
                $translation->setTranslation($this->translationService->translateText($translate->getTranslate(), $locale));
                $translation->setTranslate($translate);
                $this->entityManager->persist($translation);
            }
            $this->entityManager->flush();

            return $this->redirectToRoute('app_back_translate_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/translate/new.html.twig', [
            'translate' => $translate,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_back_translate_show', methods: ['GET'])]
    public function show(Translate $translate): Response
    {
        return $this->render('back/translate/show.html.twig', [
            'translate' => $translate,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_back_translate_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Translate $translate, EntityManagerInterface $entityManager, TranslateTranslationRepository $translateRepository): Response
    {
        $form = $this->createForm(TranslateType::class, $translate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $translations = $translateRepository->findByLanguage($translate);
            
            foreach ($translations as $translation) {
                $translation->setTranslation($this->translationService->translateText($translate->getTranslate(), $translation->getLocale()));
                $this->entityManager->persist($translation);
                $this->entityManager->flush();
            }
            
            return $this->redirectToRoute('app_back_translate_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/translate/edit.html.twig', [
            'translate' => $translate,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_back_translate_delete', methods: ['POST'])]
    public function delete(Request $request, Translate $translate, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$translate->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($translate);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_back_translate_index', [], Response::HTTP_SEE_OTHER);
    }
}
