<?php

namespace App\Repository\Catalog\Mark;

use App\Entity\Catalog\Mark\SpecLabelSegment;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SpecLabelSegment|null find($id, $lockMode = null, $lockVersion = null)
 * @method SpecLabelSegment|null findOneBy(array $criteria, array $orderBy = null)
 * @method SpecLabelSegment[]    findAll()
 * @method SpecLabelSegment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SpecLabelSegmentRepository extends AbstractEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SpecLabelSegment::class);
    }
}
