<?php

namespace App\Entity\Catalog\Mark;

use App\Entity\Catalog\Mark\Segment;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Catalog\Mark\SpecDefinitionSegmentRepository")
 */
class SpecDefinitionSegment extends Segment
{
    public static $bdm_module = 150;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $code;

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }
}
