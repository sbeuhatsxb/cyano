<?php

namespace App\Repository\Catalog\Mark;

use App\Doctrine\ColumnHydrator;
use App\Entity\Catalog\Mark\EntityTranslation;
use App\Entity\Catalog\Mark\SpecDefinitionSegment;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SpecDefinitionSegment|null find($id, $lockMode = null, $lockVersion = null)
 * @method SpecDefinitionSegment|null findOneBy(array $criteria, array $orderBy = null)
 * @method SpecDefinitionSegment[]    findAll()
 * @method SpecDefinitionSegment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SpecDefinitionSegmentRepository extends AbstractEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SpecDefinitionSegment::class);
    }

    public function getSpecLabels(): array
    {
        $qb = $this->createQueryBuilder('s')
            ->distinct()
            ->select(['s.code AS spec', 'st.value AS label', 'st.locale AS locale'])
            ->join(EntityTranslation::class, 'st', Join::WITH, 'st.translatable=s');

        $result = [];
        foreach ($qb->getQuery()->iterate() as $row) {
            ['spec' => $spec, 'locale' => $locale, 'label' => $label] = reset($row);
            $result[$spec][$locale] = $label;
        }

        return $result;
    }

    public function getSpecLabel($code, $locale = 'en')
    {
        $qb = $this->createQueryBuilder('s');
        $qb
            ->select(['st.value AS label', 's.id AS id'])
            ->join(EntityTranslation::class, 'st', Join::WITH, 'st.translatable=s')
            ->where('s.code = :code', 'st.locale = :locale')
            ->indexBy('s', 's.id');

        $query = $qb->getQuery();
        $query->useQueryCache(true)->useResultCache(true);

        return $query->execute(['code' => $code, 'locale' => $locale]);
    }
}
