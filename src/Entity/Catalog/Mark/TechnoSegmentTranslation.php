<?php

namespace App\Entity\Catalog\Mark;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Catalog\Mark\TechnoSegmentTranslationRepository")
 */
class TechnoSegmentTranslation extends EntityTranslation
{
    // Be sure all your properties are protected, not private. If some are private, isEmpty() will not work

    /**
     * @ORM\Column(type="text")
     */
    protected $description;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
