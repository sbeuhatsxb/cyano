<?php

namespace App\Repository\Catalog\Mark;

use App\Doctrine\ColumnHydrator;
use App\Entity\Catalog\Mark\Season;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Season|null find($id, $lockMode = null, $lockVersion = null)
 * @method Season|null findOneBy(array $criteria, array $orderBy = null)
 * @method Season[]    findAll()
 * @method Season[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SeasonRepository extends AbstractEntityRepository
{
    private $oldSeasonsIds;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Season::class);
    }

    public function getOldSeasonsIds()
    {
        $qb = $this->createQueryBuilder('s')
            ->select('s.id')
            ->where('s.code LIKE :last_century_seasons')
            ->setParameter('last_century_seasons', '9%');

        return $qb->getQuery()->getResult(ColumnHydrator::NAME);
    }

    public function getCachedOldSeasonsIds()
    {
        if (null === $this->oldSeasonsIds) {
            $this->oldSeasonsIds = $this->getOldSeasonsIds();
        }

        return $this->oldSeasonsIds;
    }
}
