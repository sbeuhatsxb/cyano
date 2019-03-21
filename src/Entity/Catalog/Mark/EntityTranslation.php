<?php

namespace App\Entity\Catalog\Mark;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Catalog\Mark\EntityTranslationRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="entity_type", type="integer")
 * @ORM\DiscriminatorMap({
 *     "0" = "App\Entity\Catalog\Mark\EntityTranslation",
 *     "64" = "App\Entity\Catalog\Mark\TechnoSegmentTranslation",
 *     "70" = "App\Entity\Catalog\Mark\ProductTranslation"})
 */
class EntityTranslation
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $value;

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
