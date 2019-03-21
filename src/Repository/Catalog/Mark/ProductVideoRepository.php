<?php

namespace App\Repository\Catalog\Mark;

use App\Entity\Catalog\Mark\ProductVideo;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ProductVideo|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductVideo|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductVideo[]    findAll()
 * @method ProductVideo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductVideoRepository extends AbstractEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ProductVideo::class);
    }
}
