<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PriceRepository")
 */
class PriceModifier
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     */
    private $ratio;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $durationInit;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $durationEnd;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $label;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $code;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Article", mappedBy="linkedPrice")
     */
    private $articles;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Currency")
     */
    private $linkedCurrency;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
        $this->linkedCurrency = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRatio(): ?float
    {
        return $this->ratio;
    }

    public function setRatio(float $ratio): self
    {
        $this->ratio = $ratio;

        return $this;
    }

    public function getDurationInit(): ?\DateTimeInterface
    {
        return $this->durationInit;
    }

    public function setDurationInit(?\DateTimeInterface $durationInit): self
    {
        $this->durationInit = $durationInit;

        return $this;
    }

    public function getDurationEnd(): ?\DateTimeInterface
    {
        return $this->durationEnd;
    }

    public function setDurationEnd(?\DateTimeInterface $durationEnd): self
    {
        $this->durationEnd = $durationEnd;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return Collection|Article[]
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Article $article): self
    {
        if (!$this->articles->contains($article)) {
            $this->articles[] = $article;
            $article->addLinkedPrice($this);
        }

        return $this;
    }

    public function removeArticle(Article $article): self
    {
        if ($this->articles->contains($article)) {
            $this->articles->removeElement($article);
            $article->removeLinkedPrice($this);
        }

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
        }

        return $this;
    }

    public function removeLinkedCurrency(Currency $linkedCurrency): self
    {
        if ($this->linkedCurrency->contains($linkedCurrency)) {
            $this->linkedCurrency->removeElement($linkedCurrency);
        }

        return $this;
    }

    public function __toString()
    {
        return (string)$this->getCode();
    }

}
