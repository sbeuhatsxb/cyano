<?php

namespace App\Repository\Catalog\Mark;

use App\Entity\Catalog\Mark\Product;
use App\Entity\Catalog\Mark\ProductPicture;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ProductPicture|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductPicture|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductPicture[]    findAll()
 * @method ProductPicture[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductPictureRepository extends AbstractEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ProductPicture::class);
    }

    /**
     * @param Product $product
     *
     * @return ProductPicture[]
     *
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function findByProduct(Product $product)
    {
        $qb = $this->createQueryBuilder('pm');
        $qb->select('pm', 'm')
            ->join('pm.products', 'p')
            ->join('pm.media', 'm')
            ->where($qb->expr()->eq('p', ':product'))
            ->orderBy($qb->expr()->desc('pm.isReference'))
            ->setParameter('product', $product->getId());

        return $qb->getQuery()->execute();
    }
}
