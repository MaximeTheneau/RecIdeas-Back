<?php

namespace App\Command;

use App\Entity\Posts;
use App\Entity\ParagraphPosts;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:change-url-img',
    description: 'change url img',
)]
class ChangeUrlImgCommand extends Command
{
    private $entityManager;
    private const IMAGE_SIZES = [320, 640, 750, 828, 1080, 1200, 1920, 2048, 3840];

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
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

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        if ($input->getOption('option1')) {

        }
        $posts = $this->entityManager->getRepository(Posts::class)->findAll();
        foreach ($posts as $post) {
        $urlImg = $_ENV['DOMAIN_IMG'] . $post->getSlug() . '.webp';
        $post->setImgPost($urlImg);

        // Srcset Image
        $srcset = '';
        foreach (self::IMAGE_SIZES as $size) {
            if($size <= $post->getImgWidth()) {
                $srcset .= $urlImg . '?width=' . $size . ' ' . $size . 'w,';
            }
        }
        $srcset .= $urlImg . ' ' . $post->getImgWidth() . 'w';

        $post->setSrcset($srcset);
        }

        $paragraphs = $this->entityManager->getRepository(ParagraphPosts::class)->findAll();

        foreach ($paragraphs as $paragraph) {
            $urlImg = $_ENV['DOMAIN_IMG'] . $paragraph->getImgPostParagh() . '.webp';
            if ($paragraph->getImgPostParagh() !== null) {
                $paragraph->setImgPost($urlImg);
                $srcset = '';
                foreach (self::IMAGE_SIZES as $size) {
                    if($size <= $paragraph->getImgWidth()) {
                        $srcset .= $urlImg . '?width=' . $size . ' ' . $size . 'w,';
                    }
        }
        $srcset .= $urlImg . ' ' . $paragraph->getImgWidth() . 'w';
        $paragraph->setSrcset($srcset);
            }
        }
        $this->entityManager->flush();
        $io->success('Url img changed');

        return Command::SUCCESS;
    }
}
