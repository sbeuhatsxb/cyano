<?php

namespace App\Repository\Catalog\Mark;

use App\Entity\Catalog\Mark\ProductTranslation;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ProductTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductTranslation[]    findAll()
 * @method ProductTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductTranslationRepository extends AbstractEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ProductTranslation::class);
    }
}
