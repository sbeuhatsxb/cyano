<?php

namespace App\Entity\Catalog\Mark;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Catalog\Mark\TechnoSegmentRepository")
 */
class TechnoSegment extends Segment
{
    public static $bdm_module = 64;


}
