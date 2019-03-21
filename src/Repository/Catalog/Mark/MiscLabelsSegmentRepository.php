<?php

namespace App\Repository\Catalog\Mark;

use App\Entity\Catalog\Mark\MiscLabelsSegment;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MiscLabelsSegment|null find($id, $lockMode = null, $lockVersion = null)
 * @method MiscLabelsSegment|null findOneBy(array $criteria, array $orderBy = null)
 * @method MiscLabelsSegment[]    findAll()
 * @method MiscLabelsSegment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MiscLabelsSegmentRepository extends AbstractEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MiscLabelsSegment::class);
    }
}
