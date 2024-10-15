<?php

namespace App\Entity;

use App\Repository\CategoryTranslationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CategoryTranslationRepository::class)]
class CategoryTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['api_posts_read_translation'])]
    private ?int $id = null;

    #[ORM\Column(length: 70)]
    #[Groups(['api_posts_read_translation', 'api_posts_all', 'api_posts_blog', 'api_posts_blog', 'api_posts_category_limit' ])]
    private ?string $name = null;

    #[ORM\Column(length: 70)]
    #[Groups(['api_posts_read_translation', 'api_posts_all', 'api_posts_blog', 'api_posts_blog', 'api_posts_category_limit', 'api_posts_category_translations', 'api_posts_category'])]
    private ?string $slug = null;

    #[ORM\Column(length: 10)]
    private ?string $locale = null;

    /**
     * @var Collection<int, PostsTranslation>
     */
    #[ORM\OneToMany(targetEntity: PostsTranslation::class, mappedBy: 'category')]
    private Collection $postsTranslations;

    #[ORM\ManyToOne(inversedBy: 'categoryTranslations', cascade: ['persist', 'remove'])]
    private ?Category $category = null;


    public function __construct()
    {
        $this->postsTranslations = new ArrayCollection();
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, PostsTranslation>
     */
    public function getPostsTranslations(): Collection
    {
        return $this->postsTranslations;
    }

    public function addPostsTranslation(PostsTranslation $postsTranslation): static
    {
        if (!$this->postsTranslations->contains($postsTranslation)) {
            $this->postsTranslations->add($postsTranslation);
            $postsTranslation->setCategory($this);
        }

        return $this;
    }

    public function removePostsTranslation(PostsTranslation $postsTranslation): static
    {
        if ($this->postsTranslations->removeElement($postsTranslation)) {
            // set the owning side to null (unless already changed)
            if ($postsTranslation->getCategory() === $this) {
                $postsTranslation->setCategory(null);
            }
        }

        return $this;
    }

}
