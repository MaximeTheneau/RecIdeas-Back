<?php

namespace App\Service;

class UrlGeneratorService
{
    public function generatePath(string $slug, ?string $category = null, ?string $subcategory = null, string $lang): string
    {
        if ($slug === 'Accueil') {
            return sprintf('/%s', $lang);
        }

        if ($subcategory !== null && $category !== null) {
            return sprintf('%s/%s/%s/%s', $lang, $category, $subcategory, $slug);
        } elseif ($category === 'Page') {
            return sprintf('%s/%s',$lang, $slug);
        } elseif ($category !== null) {
            return sprintf('%s/%s/%s', $lang, $category, $slug);
        }  else {
            return sprintf('%s/%s', $lang, $slug);
        }
    }
}
