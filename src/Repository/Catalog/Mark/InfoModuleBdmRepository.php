<?php

namespace App\Repository\Catalog\Mark;

use App\Entity\Catalog\Mark\InfoModuleBdm;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method InfoModuleBdm|null find($id, $lockMode = null, $lockVersion = null)
 * @method InfoModuleBdm|null findOneBy(array $criteria, array $orderBy = null)
 * @method InfoModuleBdm[]    findAll()
 * @method InfoModuleBdm[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InfoModuleBdmRepository extends AbstractEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, InfoModuleBdm::class);
    }
}
