<?php

namespace App\Entity\Catalog\Mark;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Catalog\Mark\ProductTranslationRepository")
 */
class ProductTranslation extends EntityTranslation
{
    /**
     * @ORM\Column(type="text")
     */
    protected $description;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $metaData;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    protected $translatableAttributes;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getMetaData(): ?string
    {
        return $this->metaData;
    }

    public function setMetaData(?string $metaData): self
    {
        $this->metaData = $metaData;

        return $this;
    }

    public function getTranslatableAttributes(): ?array
    {
        return $this->translatableAttributes;
    }

    public function setTranslatableAttributes(?array $translatableAttributes): self
    {
        $this->translatableAttributes = $translatableAttributes;

        return $this;
    }
}
