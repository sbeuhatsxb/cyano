<?php

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
use App\Entity\Catalog\Mark\ProductPicture;
use App\Entity\Catalog\Mark\ProductVideo;
use App\Entity\Catalog\Mark\Season;
use App\Entity\Catalog\Mark\Segment;
use App\Entity\Catalog\Mark\SpecDefinitionSegment;
use App\Entity\Catalog\Mark\SpecLabelSegment;
use App\Entity\Catalog\Mark\TechnoMedia;
use App\Entity\Catalog\Mark\TechnoSegment;
use App\Entity\Catalog\Mark\TypesB2CSegment;
use App\Entity\Catalog\Mark\TypeSegment;
use App\Repository\Catalog\Mark\EntityRepository;
use App\Repository\Catalog\Mark\SegmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;


/**
 * TODO: refactor...
 * Class PersistService
 *
 * @package App\Service\Bdm
 *
 * See /interns/doc/source/docTech/bdm.rst or http://rossignolb2b-doc.local/docTech/bdm.html for global specifications
 */
class PersistService
{

    const DEFAULT_LANG = 'en_AA';

    /**
     * @var $segmentRepository
     */
    protected $segmentRepository;

    /**
     * @var $entityRepository
     */
    protected $entityRepository;

    /**
     * @var $entityManager
     */
    protected $entityManager;

    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * PersistService constructor.
     * @param SegmentRepository $segmentRepository
     * @param EntityRepository $entityRepository
     * @param LoggerInterface $logger
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        SegmentRepository $segmentRepository,
        EntityRepository $entityRepository,
        LoggerInterface $logger,
        EntityManagerInterface $entityManager
    )
    {
        $this->entityManager = $entityManager;
        $this->segmentRepository = $segmentRepository;
        $this->entityRepository = $entityRepository;
        $this->logger = $logger;
    }

    /**
     * @param $module
     * @param $oidArray
     * @param $languages
     * @param $update
     * @throws \Exception
     */
    public function createSegment($module, $oidArray, $languages, $update)
    {

        $localOid = strtoupper($oidArray['type']) . ":" . $oidArray['oid'];

        //... else (case isset FALSE), if this entry doesn't exist, we directly and simply create it by...
        // ... declaring a new object...
        //As those OrderedSegments are belonging to $this->returnSegments(), they are handled first
        switch ($module) {
            case CategorySportSegment::getBdmModuleNumber():
                if (array_key_exists('displayOrder', $oidArray)) {
                    $input = $this->getExistingEntityOrNewOne($update, CategorySportSegment::class, $localOid);
                    $this->setOidAndLabelAndLastupdate($module, $input, $oidArray, $languages);
                    $this->setDisplayOrder($input, $oidArray);
                }
                break;

            case GenderSegment::getBdmModuleNumber():
                if (array_key_exists('displayOrder', $oidArray)) {
                    $input = $this->getExistingEntityOrNewOne($update, GenderSegment::class, $localOid);
                    $this->setOidAndLabelAndLastupdate($module, $input, $oidArray, $languages);
                    $this->setDisplayOrder($input, $oidArray);
                }
                break;

            case TypeSegment::getBdmModuleNumber():
                if (array_key_exists('displayOrder', $oidArray)) {
                    $input = $this->getExistingEntityOrNewOne($update, TypeSegment::class, $localOid);
                    $this->setOidAndLabelAndLastupdate($module, $input, $oidArray, $languages);
                    $this->setDisplayOrder($input, $oidArray);
                }
                break;

            case TypesB2CSegment::getBdmModuleNumber():
                $input = $this->getExistingEntityOrNewOne($update, TypesB2CSegment::class, $localOid);
                $this->setOidAndLabelAndLastupdate($module, $input, $oidArray, $languages);
                break;

            case BGroupSegment::getBdmModuleNumber():
                $input = $this->getExistingEntityOrNewOne($update, BGroupSegment::class, $localOid);
                $this->setOidAndLabelAndLastupdate($module, $input, $oidArray, $languages);
                $this->setBGroup($input, $oidArray);
                break;

            case Category1Segment::getBdmModuleNumber():
                $input = $this->getExistingEntityOrNewOne($update, Category1Segment::class, $localOid);
                $this->setOidAndLabelAndLastupdate($module, $input, $oidArray, $languages);
                break;

            case Category2Segment::getBdmModuleNumber():
                $input = $this->getExistingEntityOrNewOne($update, Category2Segment::class, $localOid);
                $this->setOidAndLabelAndLastupdate($module, $input, $oidArray, $languages);
                break;

            case Category3Segment::getBdmModuleNumber():
                $input = $this->getExistingEntityOrNewOne($update, Category3Segment::class, $localOid);
                $this->setOidAndLabelAndLastupdate($module, $input, $oidArray, $languages);
                break;

            case CategoryB2BSegment::getBdmModuleNumber():
                $input = $this->getExistingEntityOrNewOne($update, CategoryB2BSegment::class, $localOid);
                $this->setOidAndLabelAndLastupdate($module, $input, $oidArray, $languages);
                break;

            case CollectionSegment::getBdmModuleNumber():
                $input = $this->getExistingEntityOrNewOne($update, CollectionSegment::class, $localOid);
                $this->setOidAndLabelAndLastupdate($module, $input, $oidArray, $languages);
                break;

            case MiscLabelsSegment::getBdmModuleNumber():
                $input = $this->getExistingEntityOrNewOne($update, MiscLabelsSegment::class, $localOid);
                $this->setOidAndLabelAndLastupdate($module, $input, $oidArray, $languages);
                break;

            case SpecLabelSegment::getBdmModuleNumber():
                $input = $this->getExistingEntityOrNewOne($update, SpecLabelSegment::class, $localOid);
                $this->setOidAndLabelAndLastupdate($module, $input, $oidArray, $languages);
                $this->setCode($input, $oidArray);
                break;

            case SpecDefinitionSegment::getBdmModuleNumber():
                $input = $this->getExistingEntityOrNewOne($update, SpecDefinitionSegment::class, $localOid);
                $this->setOidAndLabelAndLastupdate($module, $input, $oidArray, $languages);
                $this->setCode($input, $oidArray);
                break;

            case BrandSegment::getBdmModuleNumber():
                $input = $this->getExistingEntityOrNewOne($update, BrandSegment::class, $localOid);
                $this->setOidAndLabelAndLastupdate($module, $input, $oidArray, $languages);
                $this->setBGroupBrandAssoc($input, $oidArray);
                $this->setCode($input, $oidArray);
                break;

            case Season::getBdmModuleNumber():
                $input = $this->getExistingEntityOrNewOne($update, Season::class, $localOid);
                $this->setOidAndLabelAndLastupdate($module, $input, $oidArray, $languages);
                $this->setCode($input, $oidArray);
                break;

            case Awards::getBdmModuleNumber():
                $input = $this->getExistingEntityOrNewOne($update, Awards::class, $localOid);
                $this->setOidAndLabelAndLastupdate($module, $input, $oidArray, $languages);
                $this->setAwards($input, $oidArray, $languages);
                break;

            case TechnoSegment::getBdmModuleNumber():
                $input = $this->getExistingEntityOrNewOne($update, TechnoSegment::class, $localOid);
                $this->setTechno($input, $oidArray, $languages);
                break;

            case Product::getBdmModuleNumber():
                $input = $this->getExistingEntityOrNewOne(Product::class, $localOid);
                $this->setOidAndLabelAndLastupdate($module, $input, $oidArray, $languages);
                $this->setProduct($input, $oidArray, $languages);
                ('Submiting product : ' . $input);
                break;

            case Package::getBdmModuleNumber():
                $input = $this->getExistingEntityOrNewOne($update, Package::class, $localOid);
                $this->setOidAndLabelAndLastupdate($module, $input, $oidArray, $languages);
                $this->setPackage($input, $oidArray);
                break;

            case PackageProduct::getBdmModuleNumber():
                $input = $this->getExistingEntityOrNewOne($update, PackageProduct::class, $localOid);
                $this->setOidAndLabelAndLastupdate($module, $input, $oidArray, $languages);
                $this->setPackProd($input, $oidArray);
                break;

            case TechnoMedia::getBdmModuleNumber():
                $input = $this->getExistingEntityOrNewOne($update, TechnoMedia::class, $localOid);
                $this->setOidAndLabelAndLastupdate($module, $input, $oidArray, $languages);
                $this->setTechnoMediaUrl($input, $oidArray);
                if (isset($oidArray['originalName'])) {
                    $input->setOriginalName($oidArray['originalName']);
                }
                break;

            case ProductMedia::getBdmModuleNumber():
                if (empty($oidArray['isVideo'])) {
                    $input = $this->getExistingEntityOrNewOne(ProductPicture::class, $localOid);
                }
                if(isset($input)){
                    $this->setProductMedias($input, $oidArray);

                    if (is_null($input) || is_null($input->getOid())) {
                        unset($input);
                    }
                }
                break;

            default:
                throwException("Module $module was not found in local database.");
        }

        if (isset($input)) {
            $this->specialFlush($input);
        }
    }

    /**
     * @param $update
     * @param $className
     * @param $oid
     * @return null|object
     */
    protected function getExistingEntityOrNewOne($className, $localOid)
    {
        if (is_null($this->entityManager->getRepository($className)->findOneByOid($localOid))) {
            dump('New Product');

            $input = new $className();
        } else {
            dump('existingProduct');
            $input = $this->entityManager->getRepository($className)
                ->findOneByOid($localOid);
        }

        return $input;
    }

    /**
     * @param $module
     * @param $input
     * @param $oidArray
     * @param $languages
     * @return mixed
     * @throws \Exception
     */
    public function setOidAndLabelAndLastupdate($module, $input, $oidArray, $languages)
    {

        //OID
        if (isset($oidArray['oid'])) {
            $input->setOid(strtoupper($oidArray['type']) . ":" . $oidArray['oid']);
        }

        //LAST UPDATE
        if (isset($oidArray['lastMDBUpdate'])) {
            $lastMDBUpdate = New \DateTime($oidArray['lastMDBUpdate']);
            $input->setLastMDBUpdate($lastMDBUpdate);
        }

        //LABEL
        if (!empty($oidArray['label'])) {
            foreach ($languages as $lang) {
                if($lang == "fr_FR"){
                    $input->setLabel(iconv('UTF-8', 'UTF-8//IGNORE', $oidArray['label'][$lang]));
                }
            }
        }

        return $input;
    }

    /**
     * @param $input
     * @param $oidArray
     * @return mixed
     */
    public function setCode($input, $oidArray)
    {
        if (isset($oidArray['code'])) {
            $input->setCode($oidArray['code']);
        }
        return $input;
    }

    /**
     * @param $input
     * @param $oidArray
     * @return mixed
     */
    public function setDisplayOrder($input, $oidArray)
    {
        if (array_key_exists('displayOrder', $oidArray)) {
            $input->setDisplayOrder($oidArray['displayOrder']);
        }
        return $input;
    }

    /**
     * @param $input
     * @param $oidArray
     * @return mixed
     */
    public function setBGroup($input, $oidArray)
    {
        /** @var BrandSegment $input */
        if (isset($oidArray['bgroup'])) {
            $input->setBGroup($oidArray['bgroup']);
        }
        return $input;
    }

    /**
     * @param $input
     * @param $oidArray
     * @return mixed
     */
    public function setBGroupBrandAssoc($input, $oidArray)
    {
        /** @var BrandSegment $input */
        $bgroup = $this->entityManager->getRepository(BGroupSegment::class)
            ->findOneByOid(
                ['oid' => 'TRADEMARKGROUP:' . $oidArray['bGroup']]
            );

        if (!$bgroup) {
            $this->logger->error(sprintf('No Associated bGroup found for brand (trademark) ' . $oidArray['oid']));
        } else {
            $input->setBgroup($bgroup);
        }

        return $input;
    }

    /**
     * @param $input
     * @param $oidArray
     * @param $languages
     */
    public function setProduct($input, $oidArray, $languages)
    {
        /** @var Product $input */
        if (isset($oidArray['reference'])) {
            $input->setModelCode(strtoupper($oidArray['reference']));
        }

        $attributes = [];
        if (isset($oidArray['attributes'])) {
            foreach ($oidArray['attributes'] as $attributeKey => $attributeValue) {
                $attributes[$attributeKey] = $attributeValue;
            }
            $input->setAttributes($attributes);
        }

        if (isset($oidArray['season'])) {
            $season = $this->entityManager->getRepository(Season::class)
                ->findOneByOid('SEASON:' . $oidArray['season']);
            if (!$season) {
                $this->logger->error(sprintf('No product found for season ' . $oidArray['season']));
            } else {
                $input->setSeason($season);
            }
        }

        if (isset($oidArray['trademark'])) {
            $brand = $this->entityManager->getRepository(BrandSegment::class)
                ->findOneByOid('TRADEMARK:' . $oidArray['trademark']);
            if (!$brand) {
                $this->logger->error(sprintf('No product found for brand (trademark) ' . $oidArray['trademark']));
            } else {
                $input->setBrand($brand);
            }
        }

        if (isset($oidArray['mainT7echno'])) {
            $mainTechno = $this->entityManager->getRepository(TechnoSegment::class)
                ->findOneByOid('TECHNO:' . $oidArray['mainTechno']);
            if (!$mainTechno) {
                $this->logger->error(sprintf('No product found for brand (trademark) ' . $oidArray['trademark']));
            } else {
                $input->setMainTechno($mainTechno);
            }
        }

        if (isset($oidArray['secondaryTechno'])) {
            $technos = [];
            foreach ($oidArray['secondaryTechno'] as $secondaryTechnos) {
                if (isset($secondaryTechnos['id'])) {
                    $technoOid = $secondaryTechnos['id'];
                    $techno = $this->entityManager->getRepository(TechnoSegment::class)
                        ->findOneByOid('TECHNO:' . $technoOid);
                    if (!$techno) {
                        $this->logger->error(sprintf('No product found for ' . $technoOid));
                    } else {
                        $technos[] = $techno;
                    }
                }
            }
            $input->setSecondaryTechnos($technos);
        }

        if (isset($oidArray['relationships'])) {
            $segments = [];
            foreach ($oidArray['relationships'] as $relationships) {
                foreach ($relationships as $segmentKey => $segmentValue) {
                    $segment = $this->entityManager->getRepository(Segment::class)
                        ->findOneByOid(strtoupper($segmentKey) . ':' . $segmentValue);
                    if (!$segment) {
                        $this->logger->error(sprintf('No product found for ' . $segmentKey . '/' . $segmentValue));
                    } else {
                        $segments[] = $segment;
                    }
                }
            }
            $input->setSegments($segments);
        }

        foreach ($languages as $lang) {
            if (!empty($oidArray['label'][$lang]) && $oidArray['label'][$lang] == "fr_FR") {
                $input->setLabel(iconv('UTF-8', 'UTF-8//IGNORE', $oidArray['label'][$lang]));
            }

            if (isset($oidArray['metadata'][$lang]) && $oidArray['metadata'][$lang] == "fr_FR") {
                $input->setMediaSegment($oidArray['metadata'][$lang]);
            }
        }
//
//        $this->entityManager->persist($input);
//        $this->entityManager->flush();
//        $this->entityManager->clear();
//        dump("Product flushed !! " . $input);
    }

    /**
     * @param $input
     * @param $oidArray
     * @param $languages
     * @return mixed
     */
    public function setAwards($input, $oidArray, $languages)
    {
        /** @var Awards $input */
        if (isset($oidArray['img'])) {
            $input->setUrl($oidArray['url']);
            $input->setOriginalName($oidArray['img']);
        }

        foreach ($languages as $lang) {
            $locale = $this->langLocaleConverter($lang);
//            $input->setCurrentLocale($locale);
            if (isset($oidArray['value'])) {
                if (in_array($locale, $oidArray['value'])) {
//                    $input->setValue(iconv('UTF-8', 'UTF-8//IGNORE', $oidArray['value'][$lang]));
                }
//                $this->mergeNewNonEmptyTranslations($input);
            }

        }

        return $input;
    }

    /**
     * @param $input
     * @param $oidArray
     * @param $languages
     * @return mixed
     * @throws \Exception
     */
    public function setTechno($input, $oidArray, $languages)
    {
        /** @var TechnoSegment $input */
        if (isset($oidArray['oid'])) {
            $input->setOid(strtoupper($oidArray['type']) . ":" . $oidArray['oid']);
        }

        if (isset($oidArray['lastMDBUpdate'])) {
            $lastMDBUpdate = New \DateTime($oidArray['lastMDBUpdate']);
            $input->setLastMDBUpdate($lastMDBUpdate);
        }

        if (isset($oidArray['label'])) {
            $input->setLabel(($oidArray['label'][self::DEFAULT_LANG]));
        }

        foreach ($languages as $lang) {
            $locale = $this->langLocaleConverter($lang);
//            $input->setCurrentLocale($locale);
//            $input->setValue(iconv('UTF-8', 'UTF-8//IGNORE', $oidArray['label'][$lang]));
//            $input->setDescription(iconv('UTF-8', 'UTF-8//IGNORE', $oidArray['description'][$lang]));
        }

        return $input;
    }

    /**
     * @param $input
     * @param $oidArray
     * @return mixed
     */
    public function setTechnoMediaUrl($input, $oidArray)
    {
        /** @var TechnoMedia $input */
        if (isset($oidArray['url'])) {
            $input->setUrl($oidArray['url']);
        }
        return $input;
    }

    /**
     * @param $input
     * @param $oidArray
     * @return mixed
     * @throws \Exception
     */
    public function setProductMedias($input, $oidArray)
    {
        /** @var ProductMedia $input */
        // If we're getting a product associated, we check is this product exist in DB. If so, we do import this media.
//        if (isset($oidArray['productOid'])) {
//            $product = $this->entityManager->getRepository(Product::class)
//                ->findOneByOid('PRODUCT:' . $oidArray['productOid']);
//            if (!$product) {
////                $this->logger->error(
////                    sprintf(
////                        'WARNING : The product reference : ' . $oidArray['associatedProduct'] . ' was not found in database. Skipping.'
////                    )
////                );
//            } else {
//                dump($oidArray);

//                $input->addProduct($product);
                $input->setOid(strtoupper($oidArray['type']) . ":" . $oidArray['oid']);
                $lastMDBUpdate = New \DateTime($oidArray['lastMDBUpdate']);
                $input->setLastMDBUpdate($lastMDBUpdate);

                if (isset($oidArray['label']) && is_array($oidArray['label'])) {
                    $input->setLabel($oidArray['label'][self::DEFAULT_LANG]);
                } else if (isset($oidArray['label']) && is_string($oidArray['label'])) {
                    $input->setLabel($oidArray['label']);
                }

                if (empty($oidArray['isVideo'])) {
                    if (isset($oidArray['oid'])) {
                        $input->setOid(strtoupper($oidArray['type']) . ":" . $oidArray['oid']);
                    }

                    $lastMDBUpdate = New \DateTime($oidArray['lastMDBUpdate']);
                    $input->setLastMDBUpdate($lastMDBUpdate);

                    if (isset($oidArray['color'])) {
                        $input->setColor($oidArray['color']);
                    }

                    if (isset($oidArray['originalName'])) {
                        $input->setOriginalName($oidArray['originalName']);
                    }

                    $input->setUrl($oidArray['url']);
                    $input->setIsReference($oidArray['referenceImage']);
                }


        return $input;
    }

    /**
     * @param $input
     * @param $oidArray
     * @return mixed
     */
    public function setPackage($input, $oidArray)
    {
        /** @var Package $input */
        if (isset($oidArray['season'])) {
            $season = $this->entityManager->getRepository(Season::class)
                ->findOneByOid('SEASON:' . $oidArray['season']);
            if (!$season) {
                $this->logger->error(sprintf('No product found for season ' . $oidArray['season']));
            } else {
                $input->setSeason($season);
            }
        }

        if (isset($oidArray['trademark'])) {
            $brand = $this->entityManager->getRepository(BrandSegment::class)
                ->findOneByOid('TRADEMARK:' . $oidArray['trademark']);
            if (!$brand) {
                $this->logger->error(sprintf('No product found for brand (trademark) ' . $oidArray['trademark']));
            } else {
                $input->setBrand($brand);
            }
        }

        return $input;
    }

    /**
     * @param $input
     * @param $oidArray
     * @return mixed
     */
    public function setPackProd($input, $oidArray)
    {
        /** @var PackageProduct $input */
        $i = 0;
        if (isset($oidArray['package'])) {

            $package = $this->entityManager->getRepository(Package::class)
                ->findOneByOid('PACKAGE:' . $oidArray['package']);
            if (!$package) {
                $this->logger->error(sprintf('No package found with OID : ' . $oidArray['package']));
            } else {
                $i++;
            }
        }

        if (isset($oidArray['product'])) {
            $product = $this->entityManager->getRepository(\App\Entity\Catalog\Mark\Product::class)
                ->findOneByOid('PRODUCT:' . $oidArray['product']);
            if (!$product) {
                $this->logger->error(sprintf('No product found with OID : ' . $oidArray['product']));
            } else {
                $i++;
            }
        }

        if ($i == 2) {
            if (isset($oidArray['sequence'])) {
                $input->setDisplayOrder($oidArray['sequence']);
            }
            if (isset($package)) {
                $input->setPackage($package);
            }
            if (isset($product)) {
                $input->setProduct($product);
            }
            return $input;
        }

        return $input = null;
    }

    /**
     * @param $input
     */
    public function specialFlush($input)
    {
        $this->entityManager->persist($input);
        $this->entityManager->flush();
        $this->entityManager->clear();
        dump('Flushed !');
    }

    /**
     * @param $lang
     * @return null|string
     */
    public function langLocaleConverter($lang)
    {
        switch ($lang) {
            case "en_AA":
                return 'en';
            case "de_DE":
                return 'de';
            case "fr_FR":
                return 'fr';
            case "it_IT":
                return 'it';
            case "es_ES":
                return 'es';
            case "en_US":
                return 'en';
            default:
                throwException("language $lang not supported");

                return null;
        }
    }

}
