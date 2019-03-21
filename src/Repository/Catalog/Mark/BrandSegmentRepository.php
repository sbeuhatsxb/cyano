<?php

namespace App\Repository\Catalog\Mark;

use App\Entity\Catalog\Mark\BrandSegment;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method BrandSegment|null find($id, $lockMode = null, $lockVersion = null)
 * @method BrandSegment|null findOneBy(array $criteria, array $orderBy = null)
 * @method BrandSegment[]    findAll()
 * @method BrandSegment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BrandSegmentRepository extends AbstractEntityRepository
{

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, BrandSegment::class);
    }
}
