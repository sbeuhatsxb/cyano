<?php

namespace App\Repository;

use App\Entity\Currency;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Currency|null find($id, $lockMode = null, $lockVersion = null)
 * @method Currency|null findOneBy(array $criteria, array $orderBy = null)
 * @method Currency[]    findAll()
 * @method Currency[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CurrencyRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Currency::class);
    }

    /**
     * @param $code
     *
     * @return Currency|null
     */
    public function findOneByCode($code): ?Currency
    {
        return $this->findOneBy(['code' => $code]);
    }

    /**
     * @return array
     */
    public function findAllLabelsIndexByCodes()
    {
        $qb = $this->createQueryBuilder('c');
        $qb->select('c.code', 'c.label');

        $results = $qb->getQuery()->getResult();
        $labelsIndexByCodes = [];
        foreach ($results as $infos) {
            $labelsIndexByCodes[$infos['code']] = $infos['label'];
        }
        return $labelsIndexByCodes;
    }
}
