<?php

namespace App\Entity\Catalog\Mark;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Catalog\Mark\ProductVideoRepository")
 */
class ProductVideo extends ProductMedia
{

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $provider;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $videoId;



    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function setProvider(?string $provider): self
    {
        $this->provider = $provider;

        return $this;
    }

    public function getVideoId(): ?string
    {
        return $this->videoId;
    }

    public function setVideoId(?string $videoId): self
    {
        $this->videoId = $videoId;

        return $this;
    }
}
