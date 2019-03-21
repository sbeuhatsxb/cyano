<?php

namespace App\Entity\Catalog\Mark;

use App\Entity\Catalog\Mark\Segment;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Catalog\Mark\TypesB2CSegmentRepository")
 */
class TypesB2CSegment extends Segment
{
    public static $bdm_module = 130;
}
