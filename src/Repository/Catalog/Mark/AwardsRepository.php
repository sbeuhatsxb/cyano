<?php

namespace App\Repository\Catalog\Mark;

use App\Entity\Catalog\Mark\Awards;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Awards|null find($id, $lockMode = null, $lockVersion = null)
 * @method Awards|null findOneBy(array $criteria, array $orderBy = null)
 * @method Awards[]    findAll()
 * @method Awards[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AwardsRepository extends AbstractEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Awards::class);
    }
}
