<?php

namespace App\Command;

use App\Entity\ParagraphPosts;
use App\Entity\Posts;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\HttpClient;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(
    name: 'app:FetchImageDimensions',
    description: 'Add a short description for your command',
)]
class FetchImageDimensionsCommand extends Command
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
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
        $httpClient = HttpClient::create();

        $paragraphPosts = $this->entityManager->getRepository(ParagraphPosts::class)->findAll();

        $posts = $this->entityManager->getRepository(Posts::class)->findAll();

        foreach ($paragraphPosts as $paragraphPost) {
            $url = $paragraphPost->getImgPostParagh(); 
            try {
                if ($url === null) {
                    continue;
                }
                $response = $httpClient->request('GET',  'https://res.cloudinary.com/dsn2zwbis/image/upload/fl_getinfo/unetaupechezvous/' . $url . '.jpg');
                $content = $response->getContent();
                $data = json_decode($content, true);
                $width = $data['input']['width'];
                $height = $data['input']['height'];

                $paragraphPost->setImgWidth($width);
                $paragraphPost->setImgHeight($height);
                $this->entityManager->persist($paragraphPost);
                $this->entityManager->flush();

                $output->writeln("Dimensions de l'image $url : $width x $height");
            } catch (\Throwable $e) {
                $output->writeln("Erreur lors de la récupération des dimensions de l'image $url : " . $e->getMessage());
            }
        }

        return Command::SUCCESS;
    }
}
