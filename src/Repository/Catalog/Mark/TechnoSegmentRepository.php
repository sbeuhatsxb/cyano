<?php

namespace App\Repository\Catalog\Mark;

use App\Entity\Catalog\Mark\TechnoSegment;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TechnoSegment|null find($id, $lockMode = null, $lockVersion = null)
 * @method TechnoSegment|null findOneBy(array $criteria, array $orderBy = null)
 * @method TechnoSegment[]    findAll()
 * @method TechnoSegment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method TechnoSegment|null findOneByOid($oid)
 */
class TechnoSegmentRepository extends AbstractEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TechnoSegment::class);
    }
}
