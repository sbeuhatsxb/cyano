<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CurrencyRepository")
 */
class Currency
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $label;

    /**
     * @ORM\Column(type="string", length=5, unique=true)
     */
    public $code;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private $symbol;

    /**
     * @ORM\Column(type="boolean")
     */
    private $symbolLocation;

    /**
     * @ORM\Column(type="string", length=1, nullable=true)
     */
    private $decimalSeparator;

    /**
     * @ORM\Column(type="string", length=1, nullable=true)
     */
    private $thousandsSeparator;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Article", inversedBy="linkedCurrency")
     */
    private $article;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
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

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function setSymbol(string $symbol): self
    {
        $this->symbol = $symbol;

        return $this;
    }

    public function getSymbolLocation(): ?bool
    {
        return $this->symbolLocation;
    }

    public function setSymbolLocation(bool $symbolLocation): self
    {
        $this->symbolLocation = $symbolLocation;

        return $this;
    }

    public function getDecimalSeparator(): ?string
    {
        return $this->decimalSeparator;
    }

    public function setDecimalSeparator(string $decimalSeparator): self
    {
        $this->decimalSeparator = $decimalSeparator;

        return $this;
    }

    public function getThousandsSeparator(): ?string
    {
        return $this->thousandsSeparator;
    }

    public function setThousandsSeparator(string $thousandsSeparator): self
    {
        $this->thousandsSeparator = $thousandsSeparator;

        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): self
    {
        $this->article = $article;

        return $this;
    }

    public function __toString()
    {
        return (string)$this->getCode();
    }
}
