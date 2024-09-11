<?php

namespace App\Command;

use App\Entity\Posts;
use App\Service\UrlGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:url-generator',
    description: 'Add a short description for your command',
)]
class UrlGeneratorCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    private $urlGeneratorService;
    private $entityManager;

    public function __construct(UrlGeneratorService $urlGeneratorService, EntityManagerInterface $entityManager)
    {
        $this->urlGeneratorService = $urlGeneratorService;
        $this->entityManager = $entityManager;

        parent::__construct();
    }



    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $posts = $this->entityManager->getRepository(Posts::class)->findAll();

        foreach ($posts as $post) {
            if($post->getSlug() !== "Accueil") {
                $post->setSlug($post->getSlug());
                $categorySlug = $post->getCategory() ? $post->getCategory()->getSlug() : null;
                $subcategorySlug = $post->getSubcategory() ? $post->getSubcategory()->getSlug() : null;
            
                $url = $this->urlGeneratorService->generatePath($post->getSlug(), $categorySlug, $subcategorySlug);
                $post->setUrl($url);
            } else {
                $post->setSlug('Accueil');
                $url = '';
                $post->setUrl($url);
            }
        }

        $this->entityManager->flush();

        $output->writeln('Url generated !!! ');

        return Command::SUCCESS;
    }
}
