<?php

namespace App\Entity\Catalog\Mark;

use App\Entity\Catalog\Mark\Segment;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Catalog\Mark\CollectionSegmentRepository")
 */
class CollectionSegment extends Segment
{
    public static $bdm_module = 85;
}
