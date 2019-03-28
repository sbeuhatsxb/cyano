<?php

namespace App\Repository\Catalog\Mark;

use App\Entity\Catalog\Mark\Segment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use App\Repository\AbstractRepository;

/**
 * @method Segment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Segment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Segment[]    findAll()
 * @method Segment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SegmentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Segment::class);
    }

    /**
     * @param string $oid
     * @return bdmSegments
     */
    public function findOneByOid(string $oid)
    {
        return $this->findOneBy(['oid' => $oid]);
    }

    /**
     * @param Segment $segment
     * @param bool $andFlush
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function persist(Segment $segment, $andFlush = false)
    {
        $this->getEntityManager()->persist($segment);
        if ($andFlush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function clearMem()
    {
        $this->getEntityManager()->clear();
    }

    /**
     * @param Segment $segment
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function flush(Segment $segment)
    {
        $this->getEntityManager()->flush($segment);
    }

    //    /**
    //     * @return Segment[] Returns an array of Segment objects
    //     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Segment
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
