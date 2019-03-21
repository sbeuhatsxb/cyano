<?php

namespace App\Entity\Catalog\Mark;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Catalog\Mark\InfoModuleBdmRepository")
 */
class InfoModuleBdm
{

    //Lonely class only dedicated to be updated at each update.
    // $lastUpdateDate is getting "now" each time the BDM update is launched

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $moduleNumber;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastUpdateDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getModuleNumber(): ?string
    {
        return $this->moduleNumber;
    }

    public function setModuleNumber(string $moduleNumber): self
    {
        $this->moduleNumber = $moduleNumber;

        return $this;
    }

    public function getLastUpdateDate(): ?\DateTimeInterface
    {
        return $this->lastUpdateDate;
    }

    public function setLastUpdateDate(?\DateTimeInterface $lastUpdateDate): self
    {
        $this->lastUpdateDate = $lastUpdateDate;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function __toString()
    {
        return $this->getId() . " " . $this->getModuleNumber() . " " . $this->getLastUpdateDate()->format('Y-m-d H:i:s');
    }
}
