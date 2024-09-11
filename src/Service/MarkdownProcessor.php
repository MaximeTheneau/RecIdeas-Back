<?php

namespace App\Service;

use Michelf\MarkdownExtra;

class MarkdownProcessor
{
    private $markdown;

    public function __construct()
    {
        $this->markdown = new MarkdownExtra();


    }

    public function processMarkdown($markdownText)
    {

        return $this->markdown->transform($markdownText);
    }

}
