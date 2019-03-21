<?php

namespace App\Entity\Catalog\Mark;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Catalog\Mark\ProductMediaRepository")
 */
abstract class ProductMedia extends Entity
{

    public static $bdm_module = 80;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Catalog\Mark\Product")
     */
    protected $products;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $originalName;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Catalog\Mark\Product", inversedBy="media")
     */
    private $product;

    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    public function setOriginalName(string $originalName): self
    {
        $this->originalName = $originalName;

        return $this;
    }


    public static function getBdmModuleNumber()
    {
        return static::$bdm_module;
    }

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public function addProduct(\App\Entity\Catalog\Mark\Product $product)
    {
        $this->products[] = $product;

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
