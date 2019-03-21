<?php

namespace App\Repository\Catalog\Mark;

use App\Entity\Catalog\Mark\Product;
use App\Entity\Catalog\Catalog;
use App\Entity\Catalog\ProductModel;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Product|null findOneByOid($oid)
 */
class ProductRepository extends AbstractEntityRepository
{
    /**
     * @var SeasonRepository
     */
    protected $seasonRepository;

    public function __construct(RegistryInterface $registry, SeasonRepository $seasonRepository)
    {
        parent::__construct($registry, Product::class);
        $this->seasonRepository = $seasonRepository;
    }

    public function findByModel(ProductModel $model)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->where('p.modelCode = :modelCode')
            ->setParameters(['modelCode' => $model->getCode()]);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param ProductModel $model
     * @param Catalog      $catalog
     *
     * @return Product|null
     */
    public function findOneByModelAndCatalog(ProductModel $model): ?Product
    {
        $oldSeasonsIds = $this->seasonRepository->getCachedOldSeasonsIds();
        $qb = $this->createQueryBuilder('p');
        $qb
            ->join('p.season', 's')
            // we remove last century seasons (9899, 9900), so we can sort by season code
            ->where('p.modelCode = :modelCode', 's.id NOT IN (:old_seasons)')
            ->orderBy($qb->expr()->desc('s.code'))
            ->setParameters(['modelCode' => $model->getCode(), 'old_seasons' => $oldSeasonsIds])
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
