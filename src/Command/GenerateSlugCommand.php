<?php

namespace App\Command;
use App\Entity\ParagraphPosts;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(
    name: 'app:generate-slug',
    description: 'Add a short description for your command',
)]
class GenerateSlugCommand extends Command
{ 
    private $entityManager;
    private $slugger;

    public function __construct(EntityManagerInterface $entityManager, SluggerInterface $slugger)
    {
        $this->entityManager = $entityManager;
        $this->slugger = $slugger;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $articles = $this->entityManager->getRepository(ParagraphPosts::class)->findAll();

        foreach ($articles as $article) {
            $slug = $this->slugger->slug($article->getSubtitle())->lower();
            $slug = substr($slug, 0, 30); // Tronquer le slug à 30 caractères
            $article->setSlug($slug);

        }

        $this->entityManager->flush();

        $output->writeln('Slugs generated successfully.');

        return Command::SUCCESS;
    }
}
