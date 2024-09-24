<?php

namespace App\Command;

use App\Entity\Posts;
use App\Entity\PostsTranslation;
use App\Entity\DailyRecype;
use App\Entity\Category;
use App\Service\OpenaiApiService;
use App\Service\OpenaiApiImageService;
use App\Service\ImageOptimizer;
use App\Service\MarkdownProcessor;
use App\Service\UrlGeneratorService;
use App\Service\TranslationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use \IntlDateFormatter;
use DateTime;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

#[AsCommand(
    name: 'app:daily-recype',
    description: 'Add a short description for your command',
)]
class DailyRecypeCommand extends Command
{
    private $openaiApiService;
    private $openaiApiImageService;
    private $imageOptimizer;
    private $entityManager;
    private $slugger;
    private $markdownProcessor;
    private $urlGeneratorService;
    private $translationService;
    private $params;

    public function __construct(
        OpenaiApiService $openaiApiService,
        OpenaiApiImageService $openaiApiImageService,
        ImageOptimizer $imageOptimizer,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        MarkdownProcessor $markdownProcessor,
        UrlGeneratorService $urlGeneratorService,
        TranslationService $translationService,
        ContainerBagInterface $params,

        )
    {
        $this->openaiApiService = $openaiApiService;
        $this->openaiApiImageService = $openaiApiImageService;
        $this->imageOptimizer = $imageOptimizer;
        $this->entityManager = $entityManager;
        $this->slugger = $slugger;
        $this->markdownProcessor = $markdownProcessor;
        $this->urlGeneratorService = $urlGeneratorService;
        $this->translationService = $translationService;
        $this->translations = [ 'es', 'en', 'it', 'de' ];
        $this->params = $params;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }
    
    private function createSlug(string $inputString): string
    {
        return strtolower($this->slugger->slug($inputString)->slice(0, 50)->toString());
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        $post = new Posts();
        $category = $this->entityManager->getRepository(Category::class)->findOneBy(['slug' => 'recette-du-jour']);

        $listRecype = $this->entityManager->getRepository(Posts::class)->findBy(['category' => $category]);

        $titles = array_map(fn($recipe) => $recipe->getTitle(), $listRecype);
        $titlesString = implode(', ', $titles);

        $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, null, null, 'dd MMMM yyyy');
        $prompt ='Génère une recette d\'un plats populaire et de saison à la date du ' . (new \DateTime())->format('Y-m-d') . ' avec un title de 60 caractères max, un heading de 60 caractères max et une metaDescription de 130 caractères max, le content (recette) doit être sous forme de markdown sans titre juste la recette en h2 les sous-titre et inclure une courte introduction. Le altImg doit être concis, sans dupliquer les titres suivants : coq au vin' . $titlesString;

        $responseJson = $this->openaiApiService->prompt(
            $prompt,
            false,
            'Tu es un assistant de cuisine qui aide à générer des recettes aux format Json seulement'
        );
        preg_match('/```json\n(.*?)\n```/s', $responseJson, $matches);
        $jsonContent = $matches[1]; 
        
        $response = json_decode($jsonContent, true);
        
        
        $post->setTitle($response['title']);
        $post->setHeading($response['heading']);
        $post->setMetaDescription($response['metaDescription']);
        $post->setCategory($category);
        $post->setAltImg($response['altImg']);
        $post->setLocale('fr');
        
        $post->setDraft(0);
        
        
        $slug =  $this->slugger->slug($post->getTitle());
        $post->setSlug($slug);

        $url = $this->urlGeneratorService->generatePath($slug, $post->getCategory()->getSlug(), null, 'fr');
        $post->setUrl($url);
        
        // DATE
        $post->setCreatedAt(new DateTime());
        $createdAt = $formatter->format($post->getCreatedAt());

        $post->setFormattedDate('Publié le ' . $createdAt);

        // MARKDOWN TO HTML
        $contentsText = $response['content'];
        $htmlText = $this->markdownProcessor->processMarkdown($contentsText);
        $post->setContents($htmlText);

        $listRecype = $this->entityManager->getRepository(Posts::class)->findBy(['category' => $category]);
        
        $imageJson = $this->openaiApiImageService->prompt(
            'Créer une Photo aérienne réaliste et appétissante pour la recette : ' . $post->getTitle() . '  avec un léger filtre pour rehausser les couleurs et textures.'
        );

        $imageContent = file_get_contents($imageJson);
        $localImagePath = $this->params->get('app.imgDir') . 'dailyRecipe.webp'; 
        file_put_contents($localImagePath, $imageContent);
        
        $this->imageOptimizer->setPicture($localImagePath, $post, $slug);

        unlink($localImagePath);

        // Daily Recype
        // $dailyRecype = new DailyRecype;
        $dailyRecype = $this->entityManager->getRepository(DailyRecype::class)->findOneBy(['locale' => 'fr']);
        $dailyRecype->setTitle($post->getTitle());
        $dailyRecype->setUrl($post->getUrl());
        $dailyRecype->setLocale('fr');
        $this->entityManager->persist($dailyRecype);

        foreach ($this->translations as $locale) {

            $translation = new PostsTranslation();

            $translation->setContents($this->translationService->translateText($post->getContents(), $locale));
            $translation->setTitle($this->translationService->translateText($post->getTitle(), $locale));
            $translation->setPost($post);

            // Category

            $categoryTranslations = $post->getCategory()->getCategoryTranslations()->filter(function ($translations) use ($locale) {
                return $translations->getLocale() === $locale;
            });
            $categoryTranslation = $categoryTranslations->first();

            $translation->setCategory($categoryTranslation);

            // $translation->setSubCategory($this->translationService->translateText($post->getSubCategory(), $locale));

            // SLug 

            $categorySlug = $translation->getCategory() ? $translation->getCategory()->getSlug() : null;
            $subcategorySlug = $translation->getSubcategory() ? $translation->getSubcategory()->getSlug() : null;

            $translation->setSlug(
                $this->createSlug(
                $this->translationService->translateText($post->getSlug(), $locale)
            ));
            $urlTranslation = $this->urlGeneratorService->generatePath($translation->getSlug(), $categorySlug, $subcategorySlug, $locale);
            $translation->setUrl($urlTranslation);
          
            // Daily Recype
            $dailyRecypeTranslation = $this->entityManager->getRepository(DailyRecype::class)->findOneBy(['locale' => $locale]);

            // $dailyRecypeTranslation = new DailyRecype;
            $dailyRecypeTranslation->setTitle($translation->getTitle());
            $dailyRecypeTranslation->setUrl($translation->getUrl());
            $dailyRecypeTranslation->setLocale($locale);

            // Date 
            $translation->setFormattedDate($this->translationService->translateText('Published on ', $locale) . $createdAt);
            $translation->setLocale($locale);
            $translation->setHeading($this->translationService->translateText($post->getHeading(), $locale));
            $translation->setMetaDescription($this->translationService->translateText($post->getMetaDescription(), $locale));

            $this->entityManager->persist($translation);
            $this->entityManager->persist($dailyRecypeTranslation);

        }

        $this->entityManager->persist($post);

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
