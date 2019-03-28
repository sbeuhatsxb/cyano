<?php

namespace App\Repository\Catalog\Mark;

use App\Application\Sonata\MediaBundle\Entity\Media;
use App\Entity\Catalog\Mark\Product;
use App\Entity\Catalog\Mark\ProductMedia;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ProductMedia|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductMedia|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductMedia[]    findAll()
 * @method ProductMedia[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductMediaRepository extends AbstractEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ProductMedia::class);
    }

    /**
     * @param ProductMedia $productMedia
     * @param bool $andFlush
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function persist(ProductMedia $productMedia, $andFlush = false)
    {
        $this->getEntityManager()->persist($productMedia);
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
     * @param ProductMedia $productMedia
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function flush(ProductMedia $productMedia)
    {
        $this->getEntityManager()->flush($productMedia);
    }

    /**
     * @param Product $product
     * @return Media[]
     */
    public function findByProduct(Product $product)
    {
        $qb = $this->createQueryBuilder('pm');
        $qb->select('pm')
            ->where(
                $qb->expr()->isMemberOf(':product', 'pm.products')
            //$qb->expr()->isNotNull('pm.media')
            )
            ->setParameter('product', $product->getId());

        return $qb->getQuery()->getResult();
    }
}
