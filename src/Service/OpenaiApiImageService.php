<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class OpenaiApiImageService
{
    public function prompt($prompt)
    {
        $client = HttpClient::create();
        $response = $client->request('POST', 'https://api.openai.com/v1/images/generations', [
            'headers' => [
                'Authorization' => 'Bearer ' . $_ENV['CHATGPT_API_KEY'],
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'dall-e-3',
                'prompt' => $prompt,
                'n' => 1,
                'size' => '1024x1024',
            ],
        ]);
        $data = $response->toArray();
        $responseData = $data['data'][0]['url']; 

        return $responseData;

    }
}
