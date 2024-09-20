<?php

namespace App\Entity;

use App\Repository\ParagraphPostsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: ParagraphPostsRepository::class)]
class ParagraphPosts
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['api_posts_read', 'api_posts_home'])]
    private ?int $id = null;

    #[ORM\Column(length: 170, nullable: true)]
    #[Groups(['api_posts_read', 'api_posts_home'])]
    private ?string $subtitle = null;

    #[ORM\Column(length: 5000, nullable: true)]
    #[Groups(['api_posts_read', 'api_posts_home'])]
    private ?string $paragraph = null;

    #[ORM\ManyToOne(inversedBy: 'paragraphPosts', targetEntity: Posts::class)]
    private $posts;

    #[ORM\Column(length: 500, nullable: true)]
    #[Groups(['api_posts_read'])]
    private ?string $imgPostParagh = null;

    #[ORM\Column(length: 170, nullable: true)]
    #[Groups(['api_posts_read'])]
    private ?string $altImg = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['api_posts_read'])]
    private ?string $slug = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Groups(['api_posts_read'])]
    private ?string $link = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['api_posts_read', 'api_posts_home'])]
    private ?string $linkSubtitle = null;

    #[ORM\ManyTone(targetEntity: Posts::class)]
    private $linkPostSelect;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imgPostParaghFile = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['api_posts_read'])]
    private ?int $imgWidth = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['api_posts_read'])]
    private ?int $imgHeight = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Groups(['api_posts_read', 'api_posts_sitemap'])]
    private ?string $imgPost = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['api_posts_read'])]
    private ?string $srcset = null;

    #[ORM\OneToMany(targetEntity: ParagraphPostsTranslation::class, mappedBy: 'paragraphPosts', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $paragraphPostsTranslations;

    public function __construct()
    {
        $this->paragraphPostsTranslations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function setSubtitle(?string $subtitle): self
    {
        $this->subtitle = $subtitle;

        return $this;
    }

    public function getParagraph(): ?string
    {
        return $this->paragraph;
    }

    public function setParagraph(?string $paragraph): self
    {
        $this->paragraph = $paragraph;

        return $this;
    }

    public function getPosts(): ?Posts
    {
        return $this->posts;
    }

    public function setPosts(?Posts $posts): self
    {
        $this->posts = $posts;

        return $this;
    }

    public function getImgPostParagh(): ?string
    {
        return $this->imgPostParagh;
    }

    public function setImgPostParagh(?string $imgPostParagh): self
    {
        $this->imgPostParagh = $imgPostParagh;

        return $this;
    }

    public function getAltImg(): ?string
    {
        return $this->altImg;
    }

    public function setAltImg(?string $altImg): self
    {
        $this->altImg = $altImg;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getLinkSubtitle(): ?string
    {
        return $this->linkSubtitle;
    }

    public function setLinkSubtitle(?string $linkSubtitle): self
    {
        $this->linkSubtitle = $linkSubtitle;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): static
    {
        $this->link = $link;

        return $this;
    }

    public function getLinkPostSelect(): ?Posts
    {
        return $this->linkPostSelect;
    }

    public function setLinkPostSelect(?Posts $linkPostSelect): self
    {
        $this->linkPostSelect = $linkPostSelect;

        return $this;
    }

    public function getImgPostParaghFile(): ?string
    {
        return $this->imgPostParaghFile;
    }

    public function setImgPostParaghFile(?string $imgPostParaghFile): static
    {
        $this->imgPostParaghFile = $imgPostParaghFile;

        return $this;
    }

    public function getImgWidth(): ?int
    {
        return $this->imgWidth;
    }

    public function setImgWidth(?int $imgWidth): static
    {
        $this->imgWidth = $imgWidth;

        return $this;
    }

    public function getImgHeight(): ?int
    {
        return $this->imgHeight;
    }

    public function setImgHeight(?int $imgHeight): static
    {
        $this->imgHeight = $imgHeight;

        return $this;
    }

    public function getImgPost(): ?string
    {
        return $this->imgPost;
    }

    public function setImgPost(?string $imgPost): static
    {
        $this->imgPost = $imgPost;

        return $this;
    }

    public function getSrcset(): ?string
    {
        return $this->srcset;
    }

    public function setSrcset(?string $srcset): static
    {
        $this->srcset = $srcset;

        return $this;
    }

    /**
     * @return Collection<int, ParagraphPostsTranslation>
     */
    public function getParagraphPostsTranslations(): Collection
    {
        return $this->paragraphPostsTranslations;
    }

    public function addParagraphPostsTranslation(ParagraphPostsTranslation $paragraphPostsTranslation): static
    {
        if (!$this->paragraphPostsTranslations->contains($paragraphPostsTranslation)) {
            $this->paragraphPostsTranslations->add($paragraphPostsTranslation);
            $paragraphPostsTranslation->setParagraphPosts($this);
        }

        return $this;
    }

    public function removeParagraphPostsTranslation(ParagraphPostsTranslation $paragraphPostsTranslation): static
    {
        if ($this->paragraphPostsTranslations->removeElement($paragraphPostsTranslation)) {
            // set the owning side to null (unless already changed)
            if ($paragraphPostsTranslation->getParagraphPosts() === $this) {
                $paragraphPostsTranslation->setParagraphPosts(null);
            }
        }

        return $this;
    }

}