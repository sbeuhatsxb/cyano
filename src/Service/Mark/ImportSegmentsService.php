<?php

// *** DOC ***
// See /interns/doc/source/docTech/bdm.rst or http://rossignolb2b-doc.local/docTech/bdm.html for global specifications

namespace App\Service\Mark;

use App\Entity\Catalog\Mark\Awards;
use App\Entity\Catalog\Mark\BGroupSegment;
use App\Entity\Catalog\Mark\BrandSegment;
use App\Entity\Catalog\Mark\Category1Segment;
use App\Entity\Catalog\Mark\Category2Segment;
use App\Entity\Catalog\Mark\Category3Segment;
use App\Entity\Catalog\Mark\CategoryB2BSegment;
use App\Entity\Catalog\Mark\CategorySportSegment;
use App\Entity\Catalog\Mark\CollectionSegment;
use App\Entity\Catalog\Mark\GenderSegment;
use App\Entity\Catalog\Mark\MiscLabelsSegment;
use App\Entity\Catalog\Mark\Season;
use App\Entity\Catalog\Mark\SpecDefinitionSegment;
use App\Entity\Catalog\Mark\SpecLabelSegment;
use App\Entity\Catalog\Mark\TechnoMedia;
use App\Entity\Catalog\Mark\TechnoSegment;
use App\Entity\Catalog\Mark\TypesB2CSegment;
use App\Entity\Catalog\Mark\TypeSegment;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

// TODO: is this service really useful???
class ImportSegmentsService
{
    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * @var $entityManager
     */
    protected $entityManager;

    /**
     * @var QueryService
     */
    protected $bdmQueryService;

    /**
     * PersistService constructor.
     * @param LoggerInterface $logger
     * @param EntityManagerInterface $entityManager
     * @param QueryService $bdmQueryService
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        QueryService $bdmQueryService,
        LoggerInterface $logger
    )
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->bdmQueryService = $bdmQueryService;
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function execute()
    {
        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);
        $this->logger->info(sprintf('BDM Segments import launched with ' . __CLASS__ . ' ' . __METHOD__));

        $moduleClassArray = [
            CategorySportSegment::class,
            GenderSegment::class,
            BGroupSegment::class,
            TypeSegment::class,
            TypesB2CSegment::class,
            Category1Segment::class,
            CategoryB2BSegment::class,
            Category3Segment::class,
            CollectionSegment::class,
            SpecLabelSegment::class,
            MiscLabelsSegment::class,
            SpecDefinitionSegment::class,
            Category2Segment::class,
            TechnoSegment::class,
            TechnoMedia::class,

            // /!\ IMPORTANT /!\ IN THIS SPECIFIC BELOW ORDER
            // SINCE SEASONS AND BRANDS HAVE TO BE LOADED BEFORE PACKAGES AND SO ON...
            // (in case of random manipulations :
            // 1) Season, 2) BrandSegment, 3) Awards, 4) Package, 5) Product, 6) PackageProduct, 7) ProductMedia

            Season::class,
            BrandSegment::class,
            Awards::class,
        ];

        foreach ($moduleClassArray as $classMethod) {
            $this->logger->info(sprintf('Beginning import of : ' . $classMethod . '.'));
            $this->bdmQueryService->updateBdmSegments([$classMethod::getBdmModuleNumber()]);
            $this->logger->info(sprintf($classMethod . ' import successful.'));
        }
    }
}
