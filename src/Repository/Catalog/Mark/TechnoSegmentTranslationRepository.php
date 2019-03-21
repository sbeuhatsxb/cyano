<?php

namespace App\Repository\Catalog\Mark;

use App\Entity\Catalog\Mark\TechnoSegmentTranslation;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TechnoSegmentTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method TechnoSegmentTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method TechnoSegmentTranslation[]    findAll()
 * @method TechnoSegmentTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TechnoSegmentTranslationRepository extends AbstractEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TechnoSegmentTranslation::class);
    }
}
