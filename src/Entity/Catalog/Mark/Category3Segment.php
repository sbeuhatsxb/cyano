<?php

namespace App\Entity\Catalog\Mark;

use App\Entity\Catalog\Mark\Segment;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Catalog\Mark\Category3SegmentRepository")
 */
class Category3Segment extends Segment
{
    public static $bdm_module = 74;
}
