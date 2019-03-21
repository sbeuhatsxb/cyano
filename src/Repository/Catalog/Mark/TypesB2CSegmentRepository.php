<?php

namespace App\Repository\Catalog\Mark;

use App\Entity\Catalog\Mark\TypesB2CSegment;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TypesB2CSegment|null find($id, $lockMode = null, $lockVersion = null)
 * @method TypesB2CSegment|null findOneBy(array $criteria, array $orderBy = null)
 * @method TypesB2CSegment[]    findAll()
 * @method TypesB2CSegment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypesB2CSegmentRepository extends AbstractEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TypesB2CSegment::class);
    }
}
