<?php

namespace App\Repository\Catalog\Mark;

use App\Entity\Catalog\Mark\TechnoMedia;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TechnoMedia|null find($id, $lockMode = null, $lockVersion = null)
 * @method TechnoMedia|null findOneBy(array $criteria, array $orderBy = null)
 * @method TechnoMedia[]    findAll()
 * @method TechnoMedia[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TechnoMediaRepository extends AbstractEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TechnoMedia::class);
    }
}
