<?php

namespace App\Command;

use App\Entity\Posts;
use App\Entity\PostsTranslation;
use App\Service\SocialMediaService;
use App\Service\GoogleIndexingService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(
    name: 'app:social-media-post',
    description: 'Add a short description for your command',
)]
class SocialMediaPostCommand extends Command
{
    private $entityManager;
    private $socialMediaService;
    private $indexingService;

    public function __construct(
        EntityManagerInterface $entityManager,
        SocialMediaService $socialMediaService,
        GoogleIndexingService $indexingService,
    )
    {
        $this->socialMediaService = $socialMediaService;
        $this->entityManager = $entityManager;
        $this->indexingService = $indexingService;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        $latestPost = $this->entityManager->getRepository(Posts::class)->findOneBy([], ['createdAt' => 'DESC']); 
        $io->section('Step 1 : Facebook Page Post');
        // Google Search
        $urlFront = 'https://' . $_ENV['NGINX_DOMAIN'] . '/' ;
        try {
            $this->indexingService->publishUrl($urlFront . $latestPost->getUrl());
            // Translation
            $translations = $this->entityManager->getRepository(PostsTranslation::class)->findBy(['post' => $latestPost]);
            foreach ($translations as $translation) {
                $this->indexingService->publishUrl($urlFront . $translation->getUrl());
            }
            
            $output->writeln('SiteMap envoyé avec succès.');
        } catch (\Exception $e) {
            $output->writeln('Erreur Sitemap ');
        }
        $message = 'Recette du jour : ' . $latestPost->getTitle() . "\n\n" .
        'La recette ici : ' . $urlFront . $latestPost->getUrl();

        $imageUrl = $latestPost->getImgPost();

        // Facebook
        try {
            $this->socialMediaService->postToFacebookPage($imageUrl, $message);
            $output->writeln('Post publié sur Facebook avec succès.');
        } catch (\Exception $e) {
            $output->writeln('Erreur lors de la publication sur Facebook: ' . $e->getMessage());
        }

        // Instagram
        try {
            $this->socialMediaService->postToInstagram($imageUrl, $message); 
            $output->writeln('Post publié sur Instagram avec succès.');
        } catch (\Exception $e) {
            $output->writeln('Erreur lors de la publication sur Instagram: ' . $e->getMessage());
        }


      
        return Command::SUCCESS;
    }
}
