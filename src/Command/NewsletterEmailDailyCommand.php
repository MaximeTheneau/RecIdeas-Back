<?php

namespace App\Command;

use App\Entity\Posts;
use App\Entity\UserNewsletter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

#[AsCommand(
    name: 'app:newsletter-email-daily',
    description: 'Add a short description for your command',
)]
class NewsletterEmailDailyCommand extends Command
{
    private $entityManager;
    private $mailer;

    public function __construct(
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        )
    {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
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
        
        if (!$latestPost) {
            $io->error('Aucune recette trouvée.');
            return Command::FAILURE;
        }

        $subscribers = $this->entityManager->getRepository(UserNewsletter::class)->findAll();
        
        
        if (count($subscribers) === 0) {
            $io->warning('Aucun utilisateur abonné à la newsletter.');
            return Command::SUCCESS;
        }

        foreach ($subscribers as $subscriber) {
            $encodedToken = urlencode($subscriber->getPassword());
            $unsubscribeUrl = $_ENV['API_PATH'] . 'newsletter/unsubscribe/' . $subscriber->getId() . '?t=' . $encodedToken;
            $latestPostTranslation = $latestPost->getTranslations()->filter(function ($lang) use ($subscriber) {
                return $lang->getLocale() === $subscriber->getLocale();
            });

            $post = !$latestPostTranslation->first() ? $latestPost : $latestPostTranslation->first();
            $email = (new TemplatedEmail())
                ->from($_ENV['MAILER_TO_NOREPLY']) 
                ->to($subscriber->getEmail()) 
                ->subject($post->getTitle() )
                ->htmlTemplate('emails/newsletterRecype.html.twig')
                ->context([
                    'h1' =>  $post->getTitle(),
                    'img' => $latestPost->getImgPost(),
                    'p' => $post->getContents() ,
                    'unsubscribe' =>  $unsubscribeUrl,
                ]);   

            $this->mailer->send($email);
        }

        $io->success('Les e-mails ont été envoyés à tous les abonnés !');

        return Command::SUCCESS;
    }

    
}
