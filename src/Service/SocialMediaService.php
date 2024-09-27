<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class SocialMediaService
{
    private $httpClient;
    private $cache;
    private $appId;
    private $appSecret;
    private $shortLivedToken;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->cache = new FilesystemAdapter(); 

        $this->appId = $_ENV['FACEBOOK_APP_ID'];
        $this->appSecret = $_ENV['FACEBOOK_APP_SECRET'];
        $this->shortLivedToken = $_ENV['FACEBOOK_SHORT_LIVED_TOKEN'];
    }

    public function postToFacebookPage(string $imageUrl, string $caption)
    {
        $pageAccessToken = $this->getPageAccessToken();

        $url = 'https://graph.facebook.com/v20.0/' . $_ENV['FACEBOOK_PAGE_ID'] . '/photos/';

        $response = $this->httpClient->request('POST', $url, [
            'query' => [
                'url' => $imageUrl,
                'message' => $caption,
                'access_token' => $pageAccessToken,
            ],
        ]);

        return $response->toArray();
    }

    public function postToInstagram(string $imageUrl, string $caption)
    {
        $instagramAccountId = $this->getInstagramAccountId();
        $pageAccessToken = $this->getPageAccessToken();

        $mediaCreationResponse = $this->httpClient->request('POST', 'https://graph.facebook.com/v20.0/' . $instagramAccountId . '/media', [
            'query' => [
                'image_url' => $imageUrl,
                'caption' => $caption,
                'access_token' => $pageAccessToken,
            ],
        ]);

        $mediaData = $mediaCreationResponse->toArray();

        if (!isset($mediaData['id'])) {
            throw new \Exception('Impossible de créer le média sur Instagram.');
        }

        // Étape 2 : Publier le média sur Instagram
        $publishResponse = $this->httpClient->request('POST', 'https://graph.facebook.com/v20.0/' . $instagramAccountId . '/media_publish', [
            'query' => [
                'creation_id' => $mediaData['id'],
                'access_token' => $pageAccessToken,
            ],
        ]);

        return $publishResponse->toArray();
    }

    private function getPageAccessToken(): string
    {
        return $this->cache->get('facebook_page_access_token', function (ItemInterface $item) {
            $item->expiresAfter(60 * 60 * 24 * 60); 
            return $this->generateLongLivedAccessToken();
        });
    }

    private function getInstagramAccountId(): string
    {
        $pageAccessToken = $this->getPageAccessToken();

        // Récupérer l'ID du compte Instagram
        $response = $this->httpClient->request('GET', 'https://graph.facebook.com/v20.0/' . $_ENV['FACEBOOK_PAGE_ID'] . '?fields=instagram_business_account', [
            'query' => [
                'access_token' => $pageAccessToken,
            ],
        ]);

        $data = $response->toArray();

        if (!isset($data['instagram_business_account']['id'])) {
            throw new \Exception('Aucun compte Instagram Business associé à cette page.');
        }

        return $data['instagram_business_account']['id'];
    }
    private function generateLongLivedAccessToken()
    {
        $response = $this->httpClient->request('GET', 'https://graph.facebook.com/v20.0/oauth/access_token', [
            'query' => [
                'grant_type' => 'fb_exchange_token',
                'client_id' => $this->appId,
                'client_secret' => $this->appSecret,
                'fb_exchange_token' => $this->shortLivedToken,
            ],
        ]);

        $data = $response->toArray();
        $longLivedToken = $data['access_token'];

        $response = $this->httpClient->request('GET', 'https://graph.facebook.com/v20.0/me/accounts', [
            'query' => [
                'access_token' => $longLivedToken,
            ],
        ]);

        $accounts = $response->toArray();
        foreach ($accounts['data'] as $account) {
            if ($account['id'] === $_ENV['FACEBOOK_PAGE_ID']) {
                return $account['access_token'];
            }
        }

        throw new \Exception("Impossible de récupérer le token d'accès pour la page.");
    }
}