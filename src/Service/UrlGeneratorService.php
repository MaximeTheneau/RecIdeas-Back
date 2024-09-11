<?php

namespace App\Service;

class UrlGeneratorService
{
    public function generatePath(string $slug, ?string $category = null, ?string $subcategory = null): string
    {
        if ($subcategory !== null && $category !== null) {
            return sprintf('/%s/%s/%s', $category, $subcategory, $slug);
        } elseif ($category === 'Pages') {
            return sprintf('/%s', $slug);
        } elseif ($category !== null) {
            return sprintf('/%s/%s', $category, $slug);
        } elseif ($slug === 'Accueil')
        {
            return '/';
        } else {
            return sprintf('/%s', $slug);
        }
    }
}
