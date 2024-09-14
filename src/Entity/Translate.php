<?php

namespace App\Entity;

use App\Repository\TranslateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TranslateRepository::class)]
class Translate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private ?string $name = null;

    #[ORM\OneToMany(targetEntity: TranslateTranslation::class, mappedBy: 'translate')]
    private Collection $translateTranslations;

    #[ORM\Column(length: 1000)]
    private ?string $translate = null;

    public function __construct()
    {
        $this->translateTranslations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, TranslateTranslation>
     */
    public function getTranslateTranslations(): Collection
    {
        return $this->translateTranslations;
    }

    public function addTranslateTranslation(TranslateTranslation $translateTranslation): static
    {
        if (!$this->translateTranslations->contains($translateTranslation)) {
            $this->translateTranslations->add($translateTranslation);
            $translateTranslation->setTranslate($this);
        }

        return $this;
    }

    public function removeTranslateTranslation(TranslateTranslation $translateTranslation): static
    {
        if ($this->translateTranslations->removeElement($translateTranslation)) {
            // set the owning side to null (unless already changed)
            if ($translateTranslation->getTranslate() === $this) {
                $translateTranslation->setTranslate(null);
            }
        }

        return $this;
    }

    public function getTranslate(): ?string
    {
        return $this->translate;
    }

    public function setTranslate(string $translate): static
    {
        $this->translate = $translate;

        return $this;
    }
}
