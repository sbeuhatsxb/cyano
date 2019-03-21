<?php

namespace App\Entity\Catalog\Mark;

use App\Entity\Catalog\Mark\Segment;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Catalog\Mark\BrandSegmentRepository")
 */
class BrandSegment extends Segment
{

    public static $bdm_module = 66;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $code;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Catalog\Mark\BGroupSegment", cascade={"persist", "remove"})
     */
    private $bGroup;


    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function __toString()
    {
        return $this->getCode();
    }

    public function getBGroup(): ?BGroupSegment
    {
        return $this->bGroup;
    }

    public function setBGroup(?BGroupSegment $bGroup): self
    {
        $this->bGroup = $bGroup;

        return $this;
    }


}
