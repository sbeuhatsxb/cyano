<?php

namespace App\Repository\Catalog\Mark;

use App\Entity\Catalog\Mark\OrderedSegment;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method OrderedSegment|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderedSegment|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderedSegment[]    findAll()
 * @method OrderedSegment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderedSegmentRepository extends AbstractEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, OrderedSegment::class);
    }
}
