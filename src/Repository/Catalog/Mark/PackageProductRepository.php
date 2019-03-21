<?php

namespace App\Repository\Catalog\Mark;

use App\Entity\Catalog\Mark\PackageProduct;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PackageProduct|null find($id, $lockMode = null, $lockVersion = null)
 * @method PackageProduct|null findOneBy(array $criteria, array $orderBy = null)
 * @method PackageProduct[]    findAll()
 * @method PackageProduct[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PackageProductRepository extends AbstractEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PackageProduct::class);
    }
}
