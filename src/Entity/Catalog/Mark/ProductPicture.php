<?php

namespace App\Entity\Catalog\Mark;

use App\Entity\Catalog\Mark\ProductMedia;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Catalog\Mark\ProductPictureRepository")
 */
class ProductPicture extends ProductMedia
{
    const FORMAT_THUMBNAIL = 'thumbnail';
    const FORMAT_ZOOM = 'zoom';
    
    /**
     * @ORM\Column(type="text")
     */
    private $url;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $displayOrder;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isReference;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $color;

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getDisplayOrder(): ?int
    {
        return $this->displayOrder;
    }

    public function setDisplayOrder(?int $displayOrder): self
    {
        $this->displayOrder = $displayOrder;

        return $this;
    }

    public function getIsReference(): ?bool
    {
        return $this->isReference;
    }

    public function setIsReference(?bool $isReference): self
    {
        $this->isReference = $isReference;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }
}
