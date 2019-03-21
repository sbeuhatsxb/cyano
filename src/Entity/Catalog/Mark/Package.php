<?php

namespace App\Entity\Catalog\Mark;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Catalog\Mark\PackageRepository")
 */
class Package extends Entity
{
    public static $bdm_module = 109;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Catalog\Mark\Season")
     */
    private $season;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Catalog\Mark\BrandSegment")
     */
    private $brand;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $packprod;

    public function getSeason(): ?Season
    {
        return $this->season;
    }

    public function setSeason(?Season $season): self
    {
        $this->season = $season;

        return $this;
    }

    public function getBrand(): ?BrandSegment
    {
        return $this->brand;
    }

    public function setBrand(?BrandSegment $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function getPackprod(): ?string
    {
        return $this->packprod;
    }

    public function setPackprod(string $packprod): self
    {
        $this->packprod = $packprod;

        return $this;
    }
}
