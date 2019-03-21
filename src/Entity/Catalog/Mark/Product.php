<?php

namespace App\Entity\Catalog\Mark;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Catalog\Mark\ProductRepository")
 * @ORM\Table(
 *   indexes={
 *     @ORM\Index(name="modelCode", columns={"model_code"})
 *   }
 * )
 */
class Product extends Entity
{

    public static $bdm_module = 70;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $modelCode;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Catalog\Mark\Season")
     */
    private $season;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Catalog\Mark\BrandSegment")
     */
    private $brand;

    /**
     * todo: looks unused
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $mediaSegment;


    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $attributes = [];

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Catalog\Mark\TechnoSegment")
     */
    private $mainTechno;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Catalog\Mark\TechnoSegment")
     * @ORM\JoinTable(name="app_product_secondary_techno_segment",
     *      joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="techno_id", referencedColumnName="id")}
     *      )
     */
    private $secondaryTechnos;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Catalog\Mark\Segment")
     * @ORM\JoinTable(name="app_product_segment",
     *      joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="segment_id", referencedColumnName="id")}
     *      )
     */
    private $segments;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Catalog\Mark\ProductMedia", mappedBy="product")
     */
    private $media;


    public function __construct()
    {
        $this->segments = new ArrayCollection();
        $this->secondaryTechnos = new ArrayCollection();
        $this->media = new ArrayCollection();
    }

    public function getModelCode(): ?string
    {
        return $this->modelCode;
    }

    public function setModelCode(string $modelCode): self
    {
        $this->modelCode = $modelCode;

        return $this;
    }

    public function getMediaSegment(): ?string
    {
        return $this->mediaSegment;
    }

    public function setMediaSegment(string $mediaSegment): self
    {
        $this->mediaSegment = $mediaSegment;

        return $this;
    }


    public function getSeason(): ?Season
    {
        return $this->season;
    }

    public function setSeason(Season $season): self
    {
        $this->season = $season;

        return $this;
    }

    public function getBrand(): ?BrandSegment
    {
        return $this->brand;
    }

    public function setBrand(BrandSegment $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function getAttributes(): ?array
    {
        return $this->attributes;
    }

    public function setAttributes(?array $attributes): self
    {
        $this->attributes = $attributes;

        return $this;
    }


    public function setSegments($segments): self
    {
        $this->segments = $segments;

        return $this;
    }

    public function addSegment(\App\Entity\Catalog\Mark\Segment $segment)
    {
        $this->segments->add($segment);

        return $this;
    }

    public function removeSegment(\App\Entity\Catalog\Mark\Segment $segment)
    {
        $this->segments->removeElement($segment);

        return $this;
    }

    /**
     * @return ArrayCollection|Segment[]
     */
    public function getSegments()
    {
        return $this->segments;
    }

    public function getSegmentsAsString()
    {
        return implode(
            ', ',
            array_map(
                function ($elem) {
                    return $elem->__toString() ?: '';
                },
                $this->segments->toArray()
            )
        );
    }

    public function getMainTechno(): ?TechnoSegment
    {
        return $this->mainTechno;
    }

    public function setMainTechno(TechnoSegment $mainTechno): self
    {
        $this->mainTechno = $mainTechno;

        return $this;
    }

    public function setSecondaryTechnos($secondaryTechnos): self
    {
        $this->secondaryTechnos = $secondaryTechnos;

        return $this;
    }

    /**
     * @param TechnoSegment $secondaryTechno
     * @return $this
     */
    public function addSecondaryTechnos(TechnoSegment $secondaryTechno)
    {
        $this->secondaryTechnos->add($secondaryTechno);
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getSecondaryTechnos()
    {
        return $this->secondaryTechnos;
    }

    public function getSecondaryTechnosAsString()
    {
        return implode(
            ', ',
            array_map(
                function ($elem) {
                    return $elem->__toString() ?: '';
                },
                $this->secondaryTechnos->toArray()
            )
        );
    }

    // Attributes helpers methods

    /**
     * @param string $key
     * @return string|null
     */
    public function getAttributeValueByKey(string $key)
    {
        return $this->attributes[$key] ?? null;
    }

    // Segment helpers methods

    /**
     * @param string $type
     *
     * @return Segment|null
     */
    public function getSegmentByType(string $type): ?Segment
    {
        foreach ($this->segments as $segment) {
            if ($segment instanceof $type) {
                return $segment;
            }
        }

        return null;
    }

    /**
     * @return Collection|ProductMedia[]
     */
    public function getMedia(): Collection
    {
        return $this->media;
    }

    public function addMedium(ProductMedia $medium): self
    {
        if (!$this->media->contains($medium)) {
            $this->media[] = $medium;
            $medium->setProduct($this);
        }

        return $this;
    }

    public function removeMedium(ProductMedia $medium): self
    {
        if ($this->media->contains($medium)) {
            $this->media->removeElement($medium);
            // set the owning side to null (unless already changed)
            if ($medium->getProduct() === $this) {
                $medium->setProduct(null);
            }
        }

        return $this;
    }


}
