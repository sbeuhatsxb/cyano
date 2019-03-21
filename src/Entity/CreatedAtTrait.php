<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Do not forget to add @ORM\HasLifecycleCallbacks annotation to your entity !
 */
trait CreatedAtTrait
{
    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function _initCreatedAt()
    {
        $this->createdAt = new \DateTime();
    }
}
