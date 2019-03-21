<?php

namespace App\Repository\Catalog\Mark;

use App\Entity\Catalog\Mark\GenderSegment;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GenderSegment|null find($id, $lockMode = null, $lockVersion = null)
 * @method GenderSegment|null findOneBy(array $criteria, array $orderBy = null)
 * @method GenderSegment[]    findAll()
 * @method GenderSegment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GenderSegmentRepository extends AbstractEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GenderSegment::class);
    }
}
