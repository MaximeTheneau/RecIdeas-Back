<?php

namespace App\Command;

use App\Service\TranslationService;
use App\Entity\Posts;
use App\Entity\PostsTranslation;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(
    name: 'app:translation',
    description: 'Translate specified fields of posts ',
)]
class TranslationCommand extends Command
{
    private $translationService;
    private $entityManager;
    
    public function __construct(
        TranslationService $translationService,
        EntityManagerInterface $entityManager,
        )
    {
        $this->translationService = $translationService;
        $this->entityManager = $entityManager;
        $this->translations = [ 'es', 'en', 'it', 'de' ];

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('fields', InputArgument::IS_ARRAY, 'The fields to translate (altImg, title, description, etc.)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output ): int
    {
        // Get the fields to translate (passed as arguments)
        $fieldsToTranslate = $input->getArgument('fields');  // Example: ['altImg', 'title']

        // Fetch all posts
        $posts = $this->entityManager->getRepository(Posts::class)->findAll();

        foreach ($posts as $post) {
            foreach ($fieldsToTranslate as $field) {
                $getter = 'get' . ucfirst($field); 

                if (method_exists($post, $getter)) {  
                    $fieldValue = $post->$getter(); 

                    if ($fieldValue) {
                        foreach ($this->translations as $locale) {
                            $translation = $this->entityManager->getRepository(PostsTranslation::class)
                                ->findOneBy([
                                    'post' => $post,
                                    'locale' => $locale
                                ]);

                            if (!$translation) {
                                $translation = new PostsTranslation();
                                $translation->setPost($post);
                                $translation->setLocale($locale);

                                // Translate the field using the translation service
                                $translatedValue = $this->translationService->translateText($fieldValue, $locale);

                                // Dynamically set the translated field (altImg, title, etc.)
                                $setter = 'set' . ucfirst($field);  // Dynamically generate the setter method (e.g., setAltImg, setTitle)
                                
                                if (method_exists($translation, $setter)) {
                                    $translation->$setter($translatedValue);  // Set the translated value
                                }

                                // Persist the new translation
                                $this->entityManager->persist($translation);

                                $output->writeln("Translation of field '{$field}' for post ID " . $post->getId() . " in " . $locale . " created.");
                            } else {

                                $translatedValue = $this->translationService->translateText($fieldValue, $locale);

                                $setter = 'set' . ucfirst($field);  

                                if (method_exists($translation, $setter)) {
                                    $translation->$setter($translatedValue);  
                                }

                                $output->writeln("Translation already exists for field '{$field}' of post ID " . $post->getId() . " in " . $locale);
                            }
                        }
                    }
                } else {
                    $output->writeln("The field '{$field}' does not exist on the Posts entity.");
                }
            }
        }

        // Save all changes to the database
        $this->entityManager->flush();

        $output->writeln('Translations completed.');

        return Command::SUCCESS;
    }
}