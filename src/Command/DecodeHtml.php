<?php

namespace App\Command;

use App\Entity\ParagraphPostsTranslation;
use App\Entity\PostsTranslation;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(
    name: 'app:decode',
    description: 'Add a short description for your command',
)]
class DecodeHtml extends Command
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $repository = $this->entityManager->getRepository(PostsTranslation::class);
        $paragrahRepository = $this->entityManager->getRepository(ParagraphPostsTranslation::class);
        $paragraphs = $paragrahRepository->findAll();
        $posts = $repository->findAll();


        foreach ($paragraphs as $paragraph) {
            $paragraph->setSubtitle(html_entity_decode($paragraph->getSubtitle()));
        }
        
        foreach ($posts as $post) {
            $post->setTitle(html_entity_decode($post->getTitle()));
            $post->setHeading(html_entity_decode($post->getHeading()));
            $post->setMetaDescription(html_entity_decode($post->getMetaDescription()));
            $post->setAltImg(html_entity_decode($post->getAltImg()));

        }

        $this->entityManager->flush();

        $output->writeln('Decode completed.');

        return Command::SUCCESS;
    }
}
