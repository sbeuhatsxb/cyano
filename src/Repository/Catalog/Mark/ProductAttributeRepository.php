<?php

namespace App\Repository\Catalog\Mark;

use App\Entity\Catalog\Mark\ProductAttribute;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * FIXME: ProductAttribute ?????
 * @method ProductAttribute|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductAttribute|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductAttribute[]    findAll()
 * @method ProductAttribute[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductAttributeRepository extends AbstractEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ProductAttribute::class);
    }
}
