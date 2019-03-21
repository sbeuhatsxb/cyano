<?php

namespace App\Repository\Catalog\Mark;

use App\Entity\Catalog\Mark\EntityTranslation;
use App\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method EntityTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method EntityTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method EntityTranslation[]    findAll()
 * @method EntityTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EntityTranslationRepository extends AbstractEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, EntityTranslation::class);
    }
}
