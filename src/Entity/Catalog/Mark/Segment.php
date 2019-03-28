<?php

namespace App\Entity\Catalog\Mark;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Catalog\Mark\SegmentRepository")
 * @ORM\DiscriminatorColumn(name="segment_type", type="integer")
 * @ORM\HasLifecycleCallbacks
 */
abstract class Segment extends Entity
{

    public function __toString()
    {
        return $this->getLabel() . ' ' . parent::__toString();
    }
}
