<?php

namespace App\Repository\Catalog\Mark;

use App\Entity\Catalog\Mark\CategorySportSegment;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CategorySportSegment|null find($id, $lockMode = null, $lockVersion = null)
 * @method CategorySportSegment|null findOneBy(array $criteria, array $orderBy = null)
 * @method CategorySportSegment[]    findAll()
 * @method CategorySportSegment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategorySportSegmentRepository extends AbstractEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CategorySportSegment::class);
    }
}
