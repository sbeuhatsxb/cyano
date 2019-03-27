<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ArticleRepository")
 * @ORM\Entity @ORM\HasLifecycleCallbacks
 * @Vich\Uploadable
 */
class Article
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPublished = false;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Category")
     */
    private $linkedCategory;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Image",cascade={"persist"}, fetch="EAGER")
     */
    private $linkedImage;

    //not mapped property - this one needs to be public
    private $preview;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Currency", mappedBy="article")
     */
    private $linkedCurrency;

    /**
     * @ORM\ManyToMany(targetEntity="PriceModifier", inversedBy="articles")
     */
    private $linkedPriceModifier;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $price;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Brand", inversedBy="articles")
     */
    private $linkedBrand;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $slug;

    public function __construct()
    {
        $this->linkedCategory = new ArrayCollection();
        $this->linkedCurrency = new ArrayCollection();
        $this->linkedPriceModifier = new ArrayCollection();
        $this->linkedBrand = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getcreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setcreatedAt(?\DateTimeInterface $createdAt): self
    {
        $createdAt = new \DateTime('now');
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getIsPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): self
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection|Category[]
     */
    public function getLinkedCategory(): Collection
    {
        return $this->linkedCategory;
    }

    public function addLinkedCategory(Category $linkedCategory): self
    {
        if (!$this->linkedCategory->contains($linkedCategory)) {
            $this->linkedCategory[] = $linkedCategory;
        }

        return $this;
    }

    public function removeLinkedCategory(Category $linkedCategory): self
    {
        if ($this->linkedCategory->contains($linkedCategory)) {
            $this->linkedCategory->removeElement($linkedCategory);
        }

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(?int $score): self
    {
        $this->score = $score;

        return $this;
    }

    public function getLinkedImage(): ?Image
    {
        return $this->linkedImage;
    }

    public function setLinkedImage(?Image $linkedImage): self
    {
        $this->linkedImage = $linkedImage;

        return $this;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updatedTimestamps(): void
    {
        $this->setUpdatedAt(new \DateTime('now'));
        if ($this->getCreatedAt() === null) {
            $this->setCreatedAt(new \DateTime('now'));
        }
    }

    public function __toString()
    {
        return (string)$this->getTitle();
    }

    public function getImageFile()
    {
        return '/uploads/images/'.$this->getLinkedImage()->getImageFile();
    }

    public function getPreview(): ?string
    {
        return $this->preview;
    }

    public function setPreview(?string $preview): self
    {
        $this->preview = $preview;

        return $this;
    }

    /**
     * @return Collection|Currency[]
     */
    public function getLinkedCurrency(): Collection
    {
        return $this->linkedCurrency;
    }

    public function addLinkedCurrency(Currency $linkedCurrency): self
    {
        if (!$this->linkedCurrency->contains($linkedCurrency)) {
            $this->linkedCurrency[] = $linkedCurrency;
            $linkedCurrency->setArticle($this);
        }

        return $this;
    }

    public function removeLinkedCurrency(Currency $linkedCurrency): self
    {
        if ($this->linkedCurrency->contains($linkedCurrency)) {
            $this->linkedCurrency->removeElement($linkedCurrency);
            // set the owning side to null (unless already changed)
            if ($linkedCurrency->getArticle() === $this) {
                $linkedCurrency->setArticle(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|PriceModifier[]
     */
    public function getLinkedPriceModifier(): Collection
    {
        return $this->linkedPriceModifier;
    }

    public function addLinkedPrice(PriceModifier $linkedPrice): self
    {
        if (!$this->linkedPriceModifier->contains($linkedPrice)) {
            $this->linkedPriceModifier[] = $linkedPrice;
        }

        return $this;
    }

    public function removeLinkedPrice(PriceModifier $linkedPrice): self
    {
        if ($this->linkedPriceModifier->contains($linkedPrice)) {
            $this->linkedPriceModifier->removeElement($linkedPrice);
        }

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): self
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return Collection|Brand[]
     */
    public function getLinkedBrand(): Collection
    {
        return $this->linkedBrand;
    }

    public function addLinkedBrand(Brand $linkedBrand): self
    {
        if (!$this->linkedBrand->contains($linkedBrand)) {
            $this->linkedBrand[] = $linkedBrand;
        }

        return $this;
    }

    public function removeLinkedBrand(Brand $linkedBrand): self
    {
        if ($this->linkedBrand->contains($linkedBrand)) {
            $this->linkedBrand->removeElement($linkedBrand);
        }

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

}
