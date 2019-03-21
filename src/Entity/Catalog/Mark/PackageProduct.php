<?php

namespace App\Entity\Catalog\Mark;

use App\Entity\Catalog\Mark\Product;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Catalog\Mark\PackageProductRepository")
 */
class PackageProduct extends Entity
{

    public static $bdm_module = 110;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $displayOrder;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Catalog\Mark\Package")
     */
    private $package;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Catalog\Mark\Product")
     */
    private $product;


    public function getDisplayOrder(): ?string
    {
        return $this->displayOrder;
    }

    public function setDisplayOrder(string $displayOrder): self
    {
        $this->displayOrder = $displayOrder;

        return $this;
    }

    public static function getBdmModuleNumber()
    {
        return static::$bdm_module;
    }

    public function getPackage(): ?Package
    {
        return $this->package;
    }

    public function setPackage(?Package $package): self
    {
        $this->package = $package;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

}
