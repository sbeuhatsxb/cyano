<?php

namespace App\Repository\Catalog\Mark;

use App\Entity\Catalog\Mark\TypeSegment;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TypeSegment|null find($id, $lockMode = null, $lockVersion = null)
 * @method TypeSegment|null findOneBy(array $criteria, array $orderBy = null)
 * @method TypeSegment[]    findAll()
 * @method TypeSegment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeSegmentRepository extends AbstractEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TypeSegment::class);
    }
}
