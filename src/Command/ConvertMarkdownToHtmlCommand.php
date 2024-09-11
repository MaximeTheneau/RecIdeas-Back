<?php

namespace App\Command;

use App\Entity\Posts;
use App\Entity\ParagraphPosts;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Table\TableExtension;
use Michelf\MarkdownExtra;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(
    name: 'app:convert-markdown',
    description: 'Add a short description for your command',
)]
class ConvertMarkdownToHtmlCommand extends Command
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $repository = $this->entityManager->getRepository(Posts::class);
        $paragrahRepository = $this->entityManager->getRepository(ParagraphPosts::class);
        $paragraphs = $paragrahRepository->findAll();
        $articles = $repository->findAll();

        $markdown = new MarkdownExtra();
    

        foreach ($paragraphs as $paragraph) {
            $markdownText = $paragraph->getParagraph();
            if ($markdownText === null) {
                continue;
            }
            $containsTable = preg_match('/\|.*\|/', $markdownText);
            $containsMarkdownElements = preg_match('/(\*\*|###)/', $markdownText);
            $containsNumberedList = preg_match('/^\d+\./m', $markdownText);
            $containsBulletedList = preg_match('/^\*/m', $markdownText);

            $htmlText = $markdown->transform($markdownText);

            // Supprimer les espaces inutiles après les balises ouvrantes <p>
            $htmlText = preg_replace('/<p>\s+/', '<p>', $htmlText);

            // Supprimer les espaces inutiles avant les balises fermantes </p>
            $htmlText = preg_replace('/\s+<\/p>/', '</p>', $htmlText);

            // Supprimer les espaces inutiles à la fin des lignes à l'intérieur des balises <p>
            $htmlText = preg_replace('/\s+<\/p>\s+/', '</p>', $htmlText);
        
            $htmlText = preg_replace('/<!--(.*?)-->/s', '', $htmlText);

            $paragraph->setParagraph($htmlText);
        }
        
        foreach ($articles as $article) {
            $markdownText = $article->getContents();
            if ($markdownText === null) {
                continue;
            }
            $containsTable = preg_match('/\|.*\|/', $markdownText);
            $containsMarkdownElements = preg_match('/(\*\*|###)/', $markdownText);
            $containsNumberedList = preg_match('/^\d+\./m', $markdownText);
            $containsBulletedList = preg_match('/^\*/m', $markdownText);

            $htmlText = $markdown->transform($markdownText);
            $htmlText = preg_replace('/\>\s+\</', '><', $htmlText);
        
            $htmlText = preg_replace('/\s+\</', '<', $htmlText);
        
            $htmlText = preg_replace('/\s+$/m', '', $htmlText);
        
            $htmlText = preg_replace('/<!--(.*?)-->/s', '', $htmlText);

            $article->setContents($htmlText);

        }

        $this->entityManager->flush();

        $output->writeln('Markdown to HTML conversion completed.');

        return Command::SUCCESS;
    }
}
