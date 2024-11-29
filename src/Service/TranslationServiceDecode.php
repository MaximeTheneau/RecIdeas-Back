<?php

namespace App\Service;

use Google\Cloud\Translate\V2\TranslateClient;

class TranslationServiceDecode
{
    private $translateClient;

    public function __construct()
    {
        $this->translateClient = new TranslateClient([
            'key' => $_ENV['GOOGLE_API_KEY'],
            'retries' => 2,
        ]);
    }

    public function translateText(string $text, string $targetLanguage): string
    {
        $translation = $this->translateClient->translate($text, [
            'target' => $targetLanguage,
        ]);

        return html_entity_decode($translation['text']);
    }
}