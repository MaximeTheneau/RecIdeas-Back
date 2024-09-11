<?php

namespace App\Service;

use Google\Cloud\Translate\V2\TranslateClient;

class TranslationService
{
    private $translateClient;

    public function __construct()
    {
        $this->translateClient = new TranslateClient([
            'key' => $_ENV['GOOGLE_API_KEY'],
        ]);
    }

    public function translateText(string $text, string $targetLanguage): string
    {
        $translation = $this->translateClient->translate($text, [
            'target' => $targetLanguage,
        ]);

        return $translation['text'];
    }
}