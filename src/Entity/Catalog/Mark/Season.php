<?php

namespace App\Entity\Catalog\Mark;

use App\Entity\Catalog\Mark\Segment;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Catalog\Mark\SeasonRepository")
 */
class Season extends Entity
{
    public static $bdm_module = 67;

    /**
     * TODO: unique=true ? but we cannot because of single table inheritance and multiple "code" attribute use
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

    public function __toString()
    {
        return $this->getCode();
    }
}
