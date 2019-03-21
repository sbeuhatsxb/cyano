<?php

namespace App\Repository\Catalog\Mark;

use App\Entity\Catalog\Mark\Category2Segment;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Category2Segment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category2Segment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category2Segment[]    findAll()
 * @method Category2Segment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class Category2SegmentRepository extends AbstractEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Category2Segment::class);
    }
}
