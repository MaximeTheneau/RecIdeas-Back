<?php

namespace App\MessageHandler;

use App\Message\TriggerNextJsBuild;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\HttpClient\HttpClient;

#[AsMessageHandler]
final class TriggerNextJsBuildHandler
{    

    public function __construct()
    {
    }

    public function __invoke(TriggerNextJsBuild $message)
    {

        try {
            $url = 'https://api.github.com/repos/MaximeTheneau/' . $_ENV['TARGET_REPO_NAME'] . '/dispatches';
            
            $data = [
                'event_type' => 'trigger-nextjs-build',
            ];

            $headers = [
                'Content-Type: application/json',
                'Authorization: token ' . $_ENV['TARGET_REPO_PAT'],  
                'Accept: application/vnd.github.everest-preview+json',  
            ];
    
            $client = HttpClient::create();
            $response = $client->request('POST', $url, [
                'headers' => $headers,
                'json' => $data,
                'timeout' => 120,
            ]);
    
            $statusCode = $response->getStatusCode();
            $content = $response->getContent();
            $message->setContent($content);
        } catch (\Exception $e) {
            return 'Une erreur est survenue lors de la requête.' . $e->getCode();
        }
    }
}
