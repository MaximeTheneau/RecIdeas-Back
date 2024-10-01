<?php

namespace App\Service;

use Google_Client;
use Google_Service_Indexing;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class GoogleIndexingService
{
    private $client;
    private $params;

    public function __construct(
        ContainerBagInterface $params,
        )
    {
        // Configure Google Client
        $this->client = new Google_Client();
        $this->client->setAuthConfig($_ENV['GOOGLE_CLOUD_CREDENTIALS_PATH']);
        $this->client->addScope(Google_Service_Indexing::INDEXING);
    }

    public function publishUrl(string $url): bool
    {
        $service = new Google_Service_Indexing($this->client);

        $content = new \Google_Service_Indexing_UrlNotification();
        $content->setType('URL_UPDATED');
        $content->setUrl($url);

        $service->urlNotifications->publish($content);
        try {
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
