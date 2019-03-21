<?php

namespace App\Repository\Catalog\Mark;

use App\Entity\Catalog\Mark\CategoryB2BSegment;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CategoryB2BSegment|null find($id, $lockMode = null, $lockVersion = null)
 * @method CategoryB2BSegment|null findOneBy(array $criteria, array $orderBy = null)
 * @method CategoryB2BSegment[]    findAll()
 * @method CategoryB2BSegment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryB2BRepository extends AbstractEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CategoryB2BSegment::class);
    }
}
