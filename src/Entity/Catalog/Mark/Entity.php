<?php

namespace App\Entity\Catalog\Mark;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Catalog\Mark\EntityRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="entity_type", type="integer")
 * @ORM\DiscriminatorMap({
 *      "1" = "App\Entity\Catalog\Mark\Segment",
 *      "2" = "App\Entity\Catalog\Mark\OrderedSegment",
 *      "11" = "App\Entity\Catalog\Mark\SpecLabelSegment",
 *      "62" = "App\Entity\Catalog\Mark\CategorySportSegment",
 *      "63" = "App\Entity\Catalog\Mark\GenderSegment",
 *      "64" = "App\Entity\Catalog\Mark\TechnoSegment",
 *      "65" = "App\Entity\Catalog\Mark\BGroupSegment",
 *      "66" = "App\Entity\Catalog\Mark\BrandSegment",
 *      "67" = "App\Entity\Catalog\Mark\Season",
 *      "68" = "App\Entity\Catalog\Mark\TypeSegment",
 *      "70" = "App\Entity\Catalog\Mark\Product",
 *      "72" = "App\Entity\Catalog\Mark\Category1Segment",
 *      "73" = "App\Entity\Catalog\Mark\Category2Segment",
 *      "74" = "App\Entity\Catalog\Mark\Category3Segment",
 *      "78" = "App\Entity\Catalog\Mark\MiscLabelsSegment",
 *      "80" = "App\Entity\Catalog\Mark\ProductMedia",
 *      "85" = "App\Entity\Catalog\Mark\CollectionSegment",
 *      "87" = "App\Entity\Catalog\Mark\TechnoMedia",
 *      "108" = "App\Entity\Catalog\Mark\Awards",
 *      "109" = "App\Entity\Catalog\Mark\Package",
 *      "110" = "App\Entity\Catalog\Mark\PackageProduct",
 *      "125" = "App\Entity\Catalog\Mark\CategoryB2BSegment",
 *      "130" = "App\Entity\Catalog\Mark\TypesB2CSegment",
 *      "150" = "App\Entity\Catalog\Mark\SpecDefinitionSegment",
 *      "1080" = "App\Entity\Catalog\Mark\ProductPicture",
 *      "2080" = "App\Entity\Catalog\Mark\ProductVideo",
 * })
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(
 *   indexes={
 *     @ORM\Index(name="modelCode", columns={"model_code"}),
 *     @ORM\Index(name="entityType", columns={"entity_type"}),
 *     @ORM\Index(name="code", columns={"code"})
 *   }
 * )
 */
abstract class Entity
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $oid;

    /**
     * @ORM\Column(type="datetime")
     */
    private $lastMDBUpdate;

    public static $bdm_module;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $label;


    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    public static function getBdmModuleNumber()
    {
        return static::$bdm_module;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOid(): ?string
    {
        return $this->oid;
    }

    public function setOid(string $oid): self
    {
        $this->oid = $oid;

        return $this;
    }

    public function getLastMDBUpdate(): ?\DateTimeInterface
    {
        return $this->lastMDBUpdate;
    }

    public function setLastMDBUpdate(\DateTimeInterface $lastMDBUpdate): self
    {
        $this->lastMDBUpdate = $lastMDBUpdate;

        return $this;
    }

    public function __toString()
    {
        return $this->oid;
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

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;

        return $this;
    }
}
