<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class OpenaiApiImageService
{
    public function prompt($prompt)
    {
        try {

            // $client = HttpClient::create();
            // $response = $client->request('POST', 'https://api.openai.com/v1/images/generations', [
            //     'headers' => [
            //         'Authorization' => 'Bearer ' . $_ENV['CHATGPT_API_KEY'],
            //         'Content-Type' => 'application/json',
            //     ],
            //     'json' => [
            //         'model' => 'dall-e-3',
            //         'prompt' => $prompt,
            //         'n' => 1,
            //         'size' => '1024x1024',
            //     ],
            // ]);
            // $data = $response->toArray();
            // $responseData = $data['data'][0]['url']; 

            // return $responseData;

            return 'https://oaidalleapiprodscus.blob.core.windows.net/private/org-QbaI7X6TmpkrQLv3M95eiHWr/user-tpXnXDZBZ8JO5IT2a2X0DiLq/img-ZCvTC1neTHYySk3M8l9UdpsY.png?st=2024-09-23T16%3A13%3A43Z&se=2024-09-23T18%3A13%3A43Z&sp=r&sv=2024-08-04&sr=b&rscd=inline&rsct=image/png&skoid=d505667d-d6c1-4a0a-bac7-5c84a87759f8&sktid=a48cca56-e6da-484e-a814-9c849652bcb3&skt=2024-09-22T23%3A30%3A40Z&ske=2024-09-23T23%3A30%3A40Z&sks=b&skv=2024-08-04&sig=CEU1%2B9S22ygxktmdB5Ie00q%2BJTd/coyGuz61y7ICUmM%3D';
        }
        catch (\Exception $e) {
    
            // $email = (new TemplatedEmail())
            // ->to($_ENV['MAILER_TO_WEBMASTER'])
            // ->from($_ENV['MAILER_TO'])
            // ->subject('Erreur lors de l\'envoie de l\'email')
            // ->htmlTemplate('emails/error.html.twig')
            // ->context([
            //     'error' => $e->getMessage(),
            // ]);
            // $mailer->send($email);
    
            // return $this->json(
            //     [
            //         "erreur" => "Error",
            //         "code_error" => Response::HTTP_FORBIDDEN
            //     ],
            //     Response::HTTP_FORBIDDEN
            // );
        }
    }
}
