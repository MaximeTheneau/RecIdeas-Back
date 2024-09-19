<?php

namespace App\Entity;

use App\Repository\ParagraphPostsTranslationRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ParagraphPostsTranslationRepository::class)]
class ParagraphPostsTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['api_posts_read', 'api_posts_sitemap', 'api_posts_home' ])]
    private ?int $id = null;

    #[ORM\Column(length: 170)]
    #[Groups(['api_posts_read', 'api_posts_sitemap', 'api_posts_home' ])]
    private ?string $subtitle = null;

    #[ORM\Column(length: 5000)]
    #[Groups(['api_posts_read', 'api_posts_sitemap', 'api_posts_home' ])]
    private ?string $paragraph = null;

    #[ORM\Column(length: 50)]
    #[Groups(['api_posts_read', 'api_posts_sitemap', 'api_posts_home' ])]
    private ?string $slug = null;

    #[ORM\ManyToOne(inversedBy: 'paragraphPosts', targetEntity: PostsTranslation::class)]
    private ?PostsTranslation $postsTranslation = null;

    #[ORM\ManyToOne(inversedBy: 'paragraphPostsTranslations')]
    private ?ParagraphPosts $paragraphPosts = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function setSubtitle(string $subtitle): static
    {
        $this->subtitle = $subtitle;

        return $this;
    }

    public function getParagraph(): ?string
    {
        return $this->paragraph;
    }

    public function setParagraph(string $paragraph): static
    {
        $this->paragraph = $paragraph;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getPostsTranslation(): ?PostsTranslation
    {
        return $this->postsTranslation;
    }

    public function setPostsTranslation(?PostsTranslation $postsTranslation): static
    {
        $this->postsTranslation = $postsTranslation;

        return $this;
    }

    public function getParagraphPosts(): ?ParagraphPosts
    {
        return $this->paragraphPosts;
    }

    public function setParagraphPosts(?ParagraphPosts $paragraphPosts): static
    {
        $this->paragraphPosts = $paragraphPosts;

        return $this;
    }


}
