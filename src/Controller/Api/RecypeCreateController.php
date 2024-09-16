<?php

namespace App\Controller\Api;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use App\Repository\TranslateRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\NamedAddress;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Validator\Constraints as Assert;
use App\Service\MarkdownProcessor;

#[Route('/api/recype')]
class RecypeCreateController extends ApiController
{    
    private $tokenService;
    private $serializer;
    private $imagine;
    private $markdownProcessor;

    public function __construct(
        TokenStorageInterface $token,
        SerializerInterface $serializer,
        MarkdownProcessor $markdownProcessor,
    ) {
        $this->tokenService = $token;
        $this->serializer = $serializer;
        $this->imagine = new Imagine();
        $this->markdownProcessor = $markdownProcessor;

    }
	
    #[Route('', name: '', methods: ['POST'])]
    public function add(TranslateRepository $translateRepository, Request $request, MailerInterface $mailer): JsonResponse
    {

    $content = $request->getContent();
    $data = $request->request->all();
    $data = json_decode($content, true);

    try {
        $translations = $translateRepository->findByTranslate($data['type'], $data['locale']);
        
        $promptRecypes = $translations->getTranslateTranslations();
        
        foreach ($promptRecypes as $translation) {
        }


        $client = HttpClient::create();
        $response = $client->request('POST', 'https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $_ENV['CHATGPT_API_KEY'],
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => $translation->getTranslation() . $data['type']
                            ],
                        ]
                    ]
                ],
                'max_tokens' => 300
            ],
        ]);

        $data = $response->toArray();

        if (isset($data['choices']) && count($data['choices']) > 0) {

            $content = $this->markdownProcessor->processMarkdown($data['choices'][0]['message']['content']);
            
            return $this->json([
                'message' => $content
            ]);
        }


    }
    catch (\Exception $e) {

        $email = (new TemplatedEmail())
        ->to($_ENV['MAILER_TO_WEBMASTER'])
        ->from($_ENV['MAILER_TO'])
        ->subject('Erreur lors de l\'envoie de l\'email')
        ->htmlTemplate('emails/error.html.twig')
        ->context([
            'error' => $e->getMessage(),
        ]);
        $mailer->send($email);


        return $this->json(
            [
                "erreur" => "Erreur lors de l'identification, veuillez réessayer plus tard",
                "code_error" => Response::HTTP_FORBIDDEN
            ],
            Response::HTTP_FORBIDDEN
        );
    }
}
            
}
