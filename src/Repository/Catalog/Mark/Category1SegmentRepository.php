<?php

namespace App\Repository\Catalog\Mark;

use App\Entity\Catalog\Mark\Category1Segment;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Category1Segment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category1Segment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category1Segment[]    findAll()
 * @method Category1Segment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class Category1SegmentRepository extends AbstractEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Category1Segment::class);
    }
}
