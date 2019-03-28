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
use App\Entity\Catalog\Mark\Entity;
use App\Entity\Catalog\Mark\GenderSegment;
use App\Entity\Catalog\Mark\InfoModuleBdm;
use App\Entity\Catalog\Mark\MiscLabelsSegment;
use App\Entity\Catalog\Mark\Package;
use App\Entity\Catalog\Mark\PackageProduct;
use App\Entity\Catalog\Mark\Product;
use App\Entity\Catalog\Mark\ProductMedia;
use App\Entity\Catalog\Mark\Season;
use App\Entity\Catalog\Mark\SpecDefinitionSegment;
use App\Entity\Catalog\Mark\SpecLabelSegment;
use App\Entity\Catalog\Mark\TechnoMedia;
use App\Entity\Catalog\Mark\TechnoSegment;
use App\Entity\Catalog\Mark\TypesB2CSegment;
use App\Entity\Catalog\Mark\TypeSegment;
use App\Repository\Catalog\Mark\EntityRepository;
use App\Repository\Catalog\Mark\ProductMediaRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;


//Import Service of the Rossignol's Marketing Database. See .env for connections settings.

/**
 * Class QueryService
 * @package App\Service\Bdm
 */
class QueryService
{
    /**
     * Since sync is filtered by date, we force the update by setting a very old date.
     */
    const VERY_OLD_TIMESTAMP_DATE = 338767200;

    /**
     * @var Entity[] array
     */
    public static $modules = [
        //        CategorySportSegment::class,
        //        GenderSegment::class,
        //        BGroupSegment::class,
        //        TypeSegment::class,
        //        TypesB2CSegment::class,
        //        Category1Segment::class,
        //        CategoryB2BSegment::class,
        //        Category3Segment::class,
        //        CollectionSegment::class,
        //        SpecLabelSegment::class,
        //        MiscLabelsSegment::class,
        //        SpecDefinitionSegment::class,
        //        Category2Segment::class,
        //        TechnoSegment::class,
        //        TechnoMedia::class,

        // /!\ IMPORTANT /!\ IN THIS SPECIFIC BELOW ORDER
        // SINCE SEASONS AND BRANDS HAVE TO BE LOADED BEFORE PACKAGES AND SO ON...
        // (in case of random manipulations :
        // 1) Season, 2) BrandSegment, 3) Awards, 4) Package, 5) Product, 6) PackageProduct, 7) ProductMedia

        //        Season::class,
        //        BrandSegment::class,
        //        Awards::class,
        //        Package::class,
        //        Product::class,
        //        PackageProduct::class,
        ProductMedia::class,
    ];

    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * @var PersistService
     */
    protected $bdmPersistService;

    /**
     * @var EntityRepository
     */
    protected $entityRepository;

    /**
     * @var ProductMediaRepository
     */
    protected $productMediaRepository;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var ParseService
     */
    protected $bdmParseService;

    /**
     * @var JsonClientService
     */
    protected $jsonClientService;


    public function __construct(
        LoggerInterface $logger,
        PersistService $bdmPersistService,
        ParseService $bdmParseService,
        EntityRepository $entityRepository,
        ProductMediaRepository $productMediaRepository,
        EntityManagerInterface $entityManager,
        JsonClientService $jsonClientService
    )
    {
        $this->logger = $logger;
        $this->bdmPersistService = $bdmPersistService;
        $this->bdmParseService = $bdmParseService;
        $this->entityRepository = $entityRepository;
        $this->productMediaRepository = $productMediaRepository;
        $this->entityManager = $entityManager;
        $this->jsonClientService = $jsonClientService;
    }

    /**
     * @param array|null $modulesArray
     * @param bool $isUnicOid
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateBdmSegments(array $modulesArray)
    {
        $this->runClientForEachModule($modulesArray);
    }

    //Encapsulated method dedicated to get last local BdmUpdate and set the Entity Manager

    /**
     * @param $modulesArray
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function runClientForEachModule($modulesArray)
    {
        foreach ($modulesArray as $module) {
            $lastLocalUpdate = $this->getModuleLastUpdate($module);
            $this->runJsonApiClient($module, $lastLocalUpdate);
        }
    }

    //Encapsulated method dedicated to get last local BdmUpdate and set the Entity Manager

    /**
     * @param $productReference
     * @param $module
     * @throws \Exception
     */
    public function runClientForOneProduct($productReference, $module)
    {
        $lastLocalUpdate = new \DateTime();
        $lastLocalUpdate->setTimestamp(self::VERY_OLD_TIMESTAMP_DATE);
        $this->findOneProductByReference($productReference, $module, $lastLocalUpdate);
    }

    //Encapsulated method dedicated to get last local BdmUpdate and set the Entity Manager

    /**
     * @param $module
     * @param null $offset
     * @throws \Exception
     */
    public function runImportRecovery($module, $offset = null)
    {
        $lastLocalUpdate = $this->getModuleLastUpdate($module);
        $this->reRunJsonApiClientFromOffset($module, $lastLocalUpdate, $offset);
    }

    //Method dedicated to retrieve distant BDM Oid. Launched daily.
    //For each of these modules, we execute the appropriate query from the QueryService class
    //This class sends back an array of results ($result) containing OID arrays which matches
    // a specific type of segment (a specific module method if you'd rather to)

    /**
     * @param $module
     * @param string $lastLocalUpdate
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function runJsonApiClient($module, $lastLocalUpdate)
    {
        $this->setModuleLastUpdate($module);

        $this->jsonClientService->setCurrentModule($module);
        $apiParameters = null;
        $apiParameters = new JsonApiParameterGroup();
        $apiParameters->addFilter(new JsonApiFilter('UPD', $lastLocalUpdate));
        $apiParameters->addFilter(new JsonApiFilter('UPD_op', '>'));
        $this->jsonClientService->setCurrentParameters($apiParameters);
        //http://bdm.grouperossignol.com/scripts/json.php/categorysport/
        //?page[size]=50&page[offset]=
        //&filter[UPD_op]=>=&filter[UPD]=23/08/2011
        //&sessionid=1a35ac707bde767d374afed331b4c610
        $jsonArraysClient[$module] = $this->jsonClientService->browse('', [], '', $module, $lastLocalUpdate);

        if (empty($jsonArraysClient)) {
            die('*****************' . PHP_EOL . ' WARNING : NO MODULE LOADED' . PHP_EOL . '*****************' . PHP_EOL);
        } else {
            return $jsonArraysClient;
        }
    }

    /**
     * Retrieve a distant product and update all its related media.
     *
     * @param $reference
     * @param $module
     * @param $lastLocalUpdate
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function findOneProductByReference(
        $reference,
        $module,
        $lastLocalUpdate
    ): void
    {
        $this->setModuleLastUpdate($module);

        $apiParameters = null;
        $this->jsonClientService->setCurrentModule($module);

        $apiParameters = new JsonApiParameterGroup();
        $apiParameters->addFilter(new JsonApiFilter('UPD', $lastLocalUpdate));
        $apiParameters->addFilter(new JsonApiFilter('UPD_op', '>'));
        $this->jsonClientService->setCurrentParameters($apiParameters);

        //Seek products
        $products = $this->jsonClientService->findOneByRef($reference, $module);

        foreach ($products as $product) {
            //Update current product
            $this->bdmParseService->updateThisProduct(JsonClientService::MODULE_PRODUCT, $product, true);

            //Fetch associated medias
            if (isset($product['relationships']['medias']['data'])) {
                $medias = $product['relationships']['medias']['data'];
                foreach ($medias as $media) {
                    //Seek medias
                    $mediaOid = $media['id'];
                    $media = $this->jsonClientService->findOneByOid($mediaOid, JsonClientService::MODULE_PRODMEDIA);
                    //Update associated media
                    $this->bdmParseService->updateMedia(JsonClientService::MODULE_PRODMEDIA, $media);
                }
            }
        }
    }


    //Method to recover a crashed import from a specific module/chunk.
    //Can be launched manually :
    //$ console app:quick-run -q interns/quick-runs/BdmSyncRedo.php
    //Can only update one module at time.
    //If you'd like to import next modules, go back to the main method : runJsonApiClient()
    public function reRunJsonApiClientFromOffset(
        $module,
        $lastLocalUpdate,
        $offset = null
    )
    {
        $this->setModuleLastUpdate($module);

        $apiParameters = null;
        $apiParameters = new JsonApiParameterGroup();
        $apiParameters->addFilter(new JsonApiFilter('UPD', $lastLocalUpdate));
        $apiParameters->addFilter(new JsonApiFilter('UPD_op', '>'));
        $this->jsonClientService->setCurrentParameters($apiParameters);

        $jsonArraysClient[$module] = $this->jsonClientService->browse('', [], $offset, $module, $lastLocalUpdate);
        if (empty($jsonArraysClient)) {
            die('*****************' . PHP_EOL . ' WARNING : NO MODULE LOADED' . PHP_EOL . '*****************' . PHP_EOL);
        } else {
            return $jsonArraysClient;
        }
    }


    // Following ModuleLastUpdate() methods are dedicated to feed the connexion to set a date filter from which OID will be imported

    /**
     * @param $module
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function setModuleLastUpdate($module)
    {

        $now = new \DateTime();

        $moduleNumber = $this->entityManager->getRepository(InfoModuleBdm::class)
            ->findOneBy(
                ['moduleNumber' => $module]
            );

        if (!$moduleNumber) {
            throw new \Exception(
                'No moduleNumber found for this module number ' . $moduleNumber
            );
        }

        $moduleNumber->setLastUpdateDate($now);
        $this->entityManager->flush();
    }

    /**
     * @param $module
     * @return string|null
     * @throws \Exception
     */
    public function getModuleLastUpdate($module)
    {
        /** @var InfoModuleBdm $lastLocalUpdate */
        $lastLocalUpdate = $this->entityManager->getRepository(InfoModuleBdm::class)->findOneBy(['moduleNumber' => $module]);
        if (!$lastLocalUpdate) {
            $this->loadInfoModuleBdm();
            throw new \Exception('No lastLocalUpdate found for this module number ' . $lastLocalUpdate);
        }

        if ($lastLocalUpdate->getLastUpdateDate() == NULL) {
            $newDate = new \DateTime();
            $newDate->setTimestamp(self::VERY_OLD_TIMESTAMP_DATE);
            $lastLocalUpdate->setLastUpdateDate($newDate);
        }

        return $lastLocalUpdate = $lastLocalUpdate->getLastUpdateDate()->format('Y-m-d');
    }

    protected function loadInfoModuleBdm()
    {
        $arrModuleName = [
            Awards::getBdmModuleNumber() => 'AWARDS',
            BGroupSegment::getBdmModuleNumber() => 'GROUP_SEGMENT',
            BrandSegment::getBdmModuleNumber() => 'BRAND_SEGMENT',
            Category1Segment::getBdmModuleNumber() => 'CATEGORY1_SEGMENT',
            Category2Segment::getBdmModuleNumber() => 'CATEGORY2_SEGMENT',
            Category3Segment::getBdmModuleNumber() => 'CATEGORY3_SEGMENT',
            CategoryB2BSegment::getBdmModuleNumber() => 'CATEGORYB2B_SEGMENT',
            CategorySportSegment::getBdmModuleNumber() => 'CATEGORYSPORT_SEGMENT',
            CollectionSegment::getBdmModuleNumber() => 'COLLECTION_SEGMENT',
            GenderSegment::getBdmModuleNumber() => 'GENDER_SEGMENT',
            MiscLabelsSegment::getBdmModuleNumber() => 'MISC_LABELS_SEGMENT',
            Package::getBdmModuleNumber() => 'PACKAGE',
            PackageProduct::getBdmModuleNumber() => 'PACKPROD',
            Product::getBdmModuleNumber() => 'PRODUCT',
            ProductMedia::getBdmModuleNumber() => 'PRODUCTMEDIA',
            Season::getBdmModuleNumber() => 'SEASON',
            SpecDefinitionSegment::getBdmModuleNumber() => 'SPECDEFINITION_SEGMENT',
            SpecLabelSegment::getBdmModuleNumber() => 'SPECLABELS_SEGMENT',
            TechnoSegment::getBdmModuleNumber() => 'TECHNO',
            TechnoMedia::getBdmModuleNumber() => 'TECHNOMEDIA',
            TypeSegment::getBdmModuleNumber() => 'TYPE_SEGMENT',
            TypesB2CSegment::getBdmModuleNumber() => 'TYPESB2C_SEGMENT',
        ];

        foreach ($arrModuleName as $strModuleNumber => $strModuleDescription) {

            $objInfoModuleBdm = New InfoModuleBdm();
            $objInfoModuleBdm->setModuleNumber($strModuleNumber);
            $objInfoModuleBdm->setDescription($strModuleDescription);

            $this->entityManager->persist($objInfoModuleBdm);
        }

        $this->entityManager->flush();
    }
}


