<?php

namespace App\Repository\Catalog\Mark;

use App\Entity\Catalog\Mark\Category3Segment;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Category3Segment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category3Segment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category3Segment[]    findAll()
 * @method Category3Segment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class Category3SegmentRepository extends AbstractEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Category3Segment::class);
    }
}
