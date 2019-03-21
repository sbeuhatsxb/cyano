<?php

namespace App\Repository\Catalog\Mark;

use App\Entity\Catalog\Mark\CollectionSegment;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CollectionSegment|null find($id, $lockMode = null, $lockVersion = null)
 * @method CollectionSegment|null findOneBy(array $criteria, array $orderBy = null)
 * @method CollectionSegment[]    findAll()
 * @method CollectionSegment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CollectionSegmentRepository extends AbstractEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CollectionSegment::class);
    }
}
