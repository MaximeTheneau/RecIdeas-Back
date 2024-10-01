<?php

namespace App\Entity;

use App\Repository\PostsTranslationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;


#[ORM\Entity(repositoryClass: PostsTranslationRepository::class)]
#[ApiRessource(
    normalizationContext: ['groups' => ['api_posts_keyword']],
)]
class PostsTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['api_posts_read_translation', 'api_posts_desc', 'api_posts_category', 'api_posts_keyword', 'api_posts_home' ])]
    private ?int $id = null;

    #[ORM\Column(length: 70)]
    #[Groups(['api_posts_read_translation', 'api_posts_home', 'api_posts_draft'])]
    private ?string $heading = null;
    
    #[ORM\Column(length: 70,  type: Types::STRING)]
    #[Groups(['api_posts_home', 'api_posts_all', 'api_posts_draft', 'api_posts_read_translation', 'api_posts_desc', 'api_posts_category', 'api_posts_blog', 'api_posts_articles_desc', 'api_posts_keyword' ])]
    private ?string $title = null;

    #[ORM\Column(length: 1000)]
    #[Groups(['api_posts_read_translation', 'api_posts_home', 'api_posts_draft', 'api_posts_category', 'api_posts_all'])]
    private ?string $metaDescription = null;
    
    #[ORM\Column(length: 70, type: Types::STRING)]
    #[Groups(['api_posts_home', 'api_posts_read_translation', 'api_posts_desc', 'api_posts_category', 'api_posts_blog',  'api_posts_keyword', 'api_posts_sitemap' ])]
    private ?string $slug = null;
    
    #[ORM\Column(length: 5000, nullable: true, type: Types::STRING)]
    #[Type(type: Types::string)]
    #[Groups(['api_posts_read_translation', 'api_posts_home', 'api_posts_draft'])]
    private ?string $contents = null;

    #[ORM\Column(length: 255)]
    #[Groups(['api_posts_read_translation'])]
    private ?string $formattedDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $links = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $textLinks = null;

    #[ORM\Column(length: 125, nullable: true)]
    #[Groups(['api_posts_category', 'api_posts_home', 'api_posts_read_translation',  'api_posts_desc', 'api_posts_keyword', 'api_posts_category', 'api_posts_subcategory'])]
    private ?string $altImg = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[Groups([ 'api_posts_category', 'api_posts_desc', 'api_posts_subcategory', 'api_posts_read_translation', 'api_posts_keyword'])]
    private ?Subcategory $subcategory = null;

    #[ORM\OneToMany(mappedBy: 'posts', targetEntity: Comments::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['api_posts_read_translation'])]
    private Collection $comments;

    #[ORM\ManyToMany(targetEntity: Keyword::class, mappedBy: 'posts')]
    #[Groups(['api_posts_read_translation'])]
    private Collection $keywords;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['api_posts_all', 'api_posts_category', 'api_posts_desc', 'api_posts_blog', 'api_posts_read', 'api_posts_read_translation','api_posts_keyword', 'api_posts_sitemap', 'api_posts_home'  ])]
    private ?string $url = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private ?bool $draft = false;

    #[ORM\ManyToOne(targetEntity: Posts::class, inversedBy: 'translations', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['api_posts_blog' ])]
    private ?Posts $post = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['api_posts_read', 'api_posts_read_translation', 'api_posts_home', 'api_posts_category', 'api_posts_draft', 'api_posts_all', 'api_posts_blog'])]
    private ?string $locale = null;

    #[ORM\OneToMany(targetEntity: ParagraphPostsTranslation::class, mappedBy: 'postsTranslation', cascade: ['persist', 'remove'])]
    #[Groups(['api_posts_read_translation', 'api_posts_sitemap', 'api_posts_home' ])]
    private Collection $paragraphPosts;

    #[ORM\ManyToOne(inversedBy: 'postsTranslations')]
    #[Groups(['api_posts_read_translation', 'api_posts_sitemap', 'api_posts_home', 'api_posts_blog', 'api_posts_category',  ])]
    private ?CategoryTranslation $category = null;


    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->keywords = new ArrayCollection();
        $this->paragraphPosts = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }
    

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContents(): ?string
    {
        return $this->contents;
    }

    public function setContents(string $contents): self
    {
        $this->contents = $contents;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }


    public function getLinks(): ?string
    {
        return $this->links;
    }

    public function setLinks(?string $links): self
    {
        $this->links = $links;

        return $this;
    }

   
    public function getTextLinks(): ?string
    {
        return $this->textLinks;
    }

    public function setTextLinks(?string $textLinks): self
    {
        $this->textLinks = $textLinks;

        return $this;
    }

    // public function getCategory(): ?Category
    // {
    //     return $this->category;
    // }

    // public function setCategory(?Category $category): self
    // {
    //     $this->category = $category;

    //     return $this;
    // }

    public function getAltImg(): ?string
    {
        return $this->altImg;
    }

    public function setAltImg(?string $altImg): self
    {
        $this->altImg = $altImg;

        return $this;
    }

    public function getImgPost(): ?string
    {
        return $this->imgPost;
    }

    public function setImgPost(?string $imgPost): self
    {
        $this->imgPost = $imgPost;

        return $this;
    }

    public function getSubcategory(): ?Subcategory
    {
        return $this->subcategory;
    }

    public function setSubcategory(?Subcategory $subcategory): self
    {
        $this->subcategory = $subcategory;

        return $this;
    }

    /**
     * @return Collection<int, Comments>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comments $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setPosts($this);
        }

        return $this;
    }

    public function removeComment(Comments $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getPosts() === $this) {
                $comment->setPosts(null);
            }
        }

        return $this;
    }

    public function setComments(Collection $comments): static
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * @return Collection<int, Keyword>
     */
    public function getKeywords(): Collection
    {
        return $this->keywords;
    }

    public function addKeyword(Keyword $keyword): static
    {
        if (!$this->keywords->contains($keyword)) {
            $this->keywords->add($keyword);
            $keyword->addPost($this);
        }

        return $this;
    }

    public function removeKeyword(Keyword $keyword): static
    {
        if ($this->keywords->removeElement($keyword)) {
            $keyword->removePost($this);
        }

        return $this;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(string $metaDescription): static
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    public function getFormattedDate(): ?string
    {
        return $this->formattedDate;
    }

    public function setFormattedDate(string $formattedDate): static
    {
        $this->formattedDate = $formattedDate;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function isDraft(): ?bool
    {
        return $this->draft;
    }

    public function setDraft(?bool $draft): static
    {
        $this->draft = $draft;

        return $this;
    }

    public function getHeading(): ?string
    {
        return $this->heading;
    }

    public function setHeading(string $heading): static
    {
        $this->heading = $heading;

        return $this;
    }

    public function getImgWidth(): ?int
    {
        return $this->imgWidth;
    }

    public function setImgWidth(?string $imgWidth): static
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

    public function getSrcset(): ?string
    {
        return $this->srcset;
    }

    public function setSrcset(?string $srcset): static
    {
        $this->srcset = $srcset;

        return $this;
    }

    public function getPost(): ?Posts
    {
        return $this->post;
    }

    public function setPost(?Posts $post): static
    {
        $this->post = $post;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return Collection<int, ParagraphPostsTranslation>
     */
    public function getParagraphPosts(): Collection
    {
        return $this->paragraphPosts;
    }

    public function addParagraphPost(ParagraphPostsTranslation $paragraphPost): static
    {
        if (!$this->paragraphPosts->contains($paragraphPost)) {
            $this->paragraphPosts->add($paragraphPost);
            $paragraphPost->setPostsTranslation($this);
        }

        return $this;
    }

    public function removeParagraphPost(ParagraphPostsTranslation $paragraphPost): static
    {
        if ($this->paragraphPosts->removeElement($paragraphPost)) {
            // set the owning side to null (unless already changed)
            if ($paragraphPost->getPostsTranslation() === $this) {
                $paragraphPost->setPostsTranslation(null);
            }
        }

        return $this;
    }

    public function getCategory(): ?CategoryTranslation
    {
        return $this->category;
    }

    public function setCategory(?CategoryTranslation $category): static
    {
        $this->category = $category;

        return $this;
    }

}