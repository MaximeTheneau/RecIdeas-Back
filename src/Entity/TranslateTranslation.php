<?php

namespace App\Entity;

use App\Repository\TranslateTranslationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TranslateTranslationRepository::class)]
class TranslateTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'translateTranslations')]
    private ?Translate $translate = null;

    #[ORM\Column(length: 1000)]
    private ?string $translation = null;

    #[ORM\Column(length: 10)]
    private ?string $locale = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTranslate(): ?Translate
    {
        return $this->translate;
    }

    public function setTranslate(?Translate $translate): static
    {
        $this->translate = $translate;

        return $this;
    }

    public function getTranslation(): ?string
    {
        return $this->translation;
    }

    public function setTranslation(string $translation): static
    {
        $this->translation = $translation;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

}
