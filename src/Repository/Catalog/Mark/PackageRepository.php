<?php

namespace App\Repository\Catalog\Mark;

use App\Entity\Catalog\Mark\Package;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Package|null find($id, $lockMode = null, $lockVersion = null)
 * @method Package|null findOneBy(array $criteria, array $orderBy = null)
 * @method Package[]    findAll()
 * @method Package[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PackageRepository extends AbstractEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Package::class);
    }
}
