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
use App\Entity\Catalog\Mark\Package;
use App\Entity\Catalog\Mark\PackageProduct;
use App\Entity\Catalog\Mark\Product;
use App\Entity\Catalog\Mark\ProductMedia;
use App\Entity\Catalog\Mark\Season;
use App\Entity\Catalog\Mark\SpecDefinitionSegment;
use App\Entity\Catalog\Mark\SpecLabelSegment;
use App\Entity\Catalog\Mark\TechnoSegment;
use App\Entity\Catalog\Mark\TechnoMedia;
use App\Entity\Catalog\Mark\TypesB2CSegment;
use App\Entity\Catalog\Mark\TypeSegment;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;


class ImportOneModuleService
{

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
     * @param EntityManagerInterface $entityManager
     * @param QueryService $bdmQueryService
     */
    public function __construct(EntityManagerInterface $entityManager, QueryService $bdmQueryService)
    {
        $this->entityManager = $entityManager;
        $this->bdmQueryService = $bdmQueryService;
    }

    /**
     *
     */
    public function execute($moduleSelected = null)
    {

        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);

        $client = $this->bdmQueryService;

        if ($moduleSelected === null) {
            $displaySelectModulesArray =
                PHP_EOL .
                '*****************************************' . PHP_EOL .
                '******* Select a module to import *******' . PHP_EOL .
                '*****************************************' . PHP_EOL .

                PHP_EOL .
                '1 => CategorySportSegment' . PHP_EOL .
                '2 => GenderSegment' . PHP_EOL .
                '3 => BGroupSegment' . PHP_EOL .
                '4 => TypeSegment' . PHP_EOL .
                '5 => TypesB2CSegment' . PHP_EOL .
                '6 => Category1Segment' . PHP_EOL .
                '7 => CategoryB2BSegment' . PHP_EOL .
                '8 => Category3Segment' . PHP_EOL .
                '9 => CollectionSegment' . PHP_EOL .
                '10 => SpecLabelSegment' . PHP_EOL .
                '11 => MiscLabelsSegment' . PHP_EOL .
                '12 => SpecDefinitionSegmentRepository' . PHP_EOL .
                '13 => Category2Segment' . PHP_EOL .
                '14 => TechnoSegment' . PHP_EOL .
                '15 => TechnoMedia' . PHP_EOL .
                '/!\ IMPORTANT /!\ MODULES BELOW MUST BE LOADED IN THIS SPECIFIC ORDER' . PHP_EOL .
                '16 => Season' . PHP_EOL .
                '17 => BrandSegment' . PHP_EOL .
                '18 => Awards' . PHP_EOL .
                '19 => Package' . PHP_EOL .
                '20 => Product' . PHP_EOL .
                '21 => PackageProduct' . PHP_EOL .
                '22 => ProductMedia' . PHP_EOL;

            $modulesArray = [

                //    ORDER DOESNT MATTER HERE
                "1" => CategorySportSegment::getBdmModuleNumber(),
                "2" => GenderSegment::getBdmModuleNumber(),
                "3" => BGroupSegment::getBdmModuleNumber(),
                "4" => TypeSegment::getBdmModuleNumber(),
                "5" => TypesB2CSegment::getBdmModuleNumber(),
                "6" => Category1Segment::getBdmModuleNumber(),
                "7" => CategoryB2BSegment::getBdmModuleNumber(),
                "8" => Category3Segment::getBdmModuleNumber(),
                "9" => CollectionSegment::getBdmModuleNumber(),
                "10" => SpecLabelSegment::getBdmModuleNumber(),
                "11" => MiscLabelsSegment::getBdmModuleNumber(),
                "12" => SpecDefinitionSegment::getBdmModuleNumber(),
                "13" => Category2Segment::getBdmModuleNumber(),
                "14" => TechnoSegment::getBdmModuleNumber(),
                "15" => TechnoMedia::getBdmModuleNumber(),

                //     /!\ IMPORTANT /!\ IN THIS SPECIFIC BELOW ORDER

                "16" => Season::getBdmModuleNumber(),
                "17" => BrandSegment::getBdmModuleNumber(),
                "18" => Awards::getBdmModuleNumber(),
                "19" => Package::getBdmModuleNumber(),
                "20" => Product::getBdmModuleNumber(),
                "21" => PackageProduct::getBdmModuleNumber(),
                "22" => ProductMedia::getBdmModuleNumber(),
            ];

            echo $displaySelectModulesArray;
            $moduleSelected = $this->promptModule();
            $offset = $this->promptOffset();
        } else {
            $offset = 1;
        }

        $module = $modulesArray[strval($moduleSelected)];

        $client->runImportRecovery($module, $offset);
    }

    /**
     * @return string
     */
    private function promptOffset()
    {
        echo PHP_EOL."Type in the offset from which you wish to restart the import (min. 1) : ";
        $prompt = strtoupper(mb_convert_encoding(rtrim(fgets(STDIN)), 'UTF-8'));

        return $prompt;
    }

    /**
     * @return string
     */
    private function promptModule()
    {
        echo PHP_EOL."Type in the module concerned : ";
        $prompt = strtoupper(mb_convert_encoding(rtrim(fgets(STDIN)), 'UTF-8'));

        return $prompt;
    }


}
