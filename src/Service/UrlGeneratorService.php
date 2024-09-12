<?php

namespace App\Service;

class UrlGeneratorService
{
    public function generatePath(string $slug, ?string $category = null, ?string $subcategory = null, string $lang): string
    {
        if ($subcategory !== null && $category !== null) {
            return sprintf('/%s/%s/%s/%s', $lang, $category, $subcategory, $slug);
        } elseif ($category === 'Page') {
            return sprintf('/%s/%s',$lang, $slug);
        } elseif ($category !== null) {
            return sprintf('/%s/%s/%s',$lang, $category, $slug);
        } elseif ($slug === 'Accueil')
        {
            return '/';
        } else {
            return sprintf('/%s', $slug);
        }
    }
}
