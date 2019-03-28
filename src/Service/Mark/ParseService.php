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
use App\Entity\Catalog\Mark\TechnoMedia;
use App\Entity\Catalog\Mark\TechnoSegment;
use App\Entity\Catalog\Mark\TypesB2CSegment;
use App\Entity\Catalog\Mark\TypeSegment;
use App\Repository\Catalog\Mark\EntityRepository;
use App\Repository\Catalog\Mark\ProductMediaRepository;
use Psr\Log\LoggerInterface;


/**
 * TODO: refactor...
 * Class ParseService
 * @package App\Service\Bdm
 */
class ParseService
{
    //All languages managed
    const MDB_LANGUAGES_SUPPORTED = [
        "en_AA",
        "de_DE",
        "fr_FR",
        "it_IT",
        "es_ES",
        "en_US",
    ];

    //All Product attributes
    const PRODUCT_SEGMENTS = [
        'trademarkgroup',
        'categorysport',
        'producttype',
        'producttypeb2c',
        'gender',
        'category1',
        'category2',
        'category3',
    ];

    const SUPPORTED_MIMETYPES_IMAGES = ['image/png', 'image/jpg', 'image/jpeg'];

    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * @var EntityRepository
     */
    protected $entityRepository;

    /**
     * @var ProductMediaRepository
     */
    protected $productMediaRepository;

    /**
     * @var PersistService
     */
    protected $bdmPersistService;


    public function __construct(
        LoggerInterface $logger,
        EntityRepository $entityRepository,
        ProductMediaRepository $productMediaRepository,
        PersistService $bdmPersistService
    )
    {
        $this->logger = $logger;
        $this->entityRepository = $entityRepository;
        $this->productMediaRepository = $productMediaRepository;
        $this->bdmPersistService = $bdmPersistService;
    }


    /**
     * fixme: undefined variables!!!
     *
     * @param $chunck
     * @throws \Exception
     */
    public function getChunksetModule($chunck)
    {
        foreach ($chunck as $oidarray) {
            if (isset($oidarray['type'])) {
                $type = $oidarray['type'];
                break;
            }
        }
        if (empty($chunck)) {
            $this->updateOrCreateSegment(null, 0);
        } else {
            foreach (JsonClientService::MDB_TYPES_MODULESNUMBERS as $moduleNumber => $typeString) {
                if ($type == $typeString) {
                    $module = $moduleNumber;
                    break;
                }
            }

            $this->getDataFromDistantModules($module, $chunck);
            $chunck = null;
        }
    }


    /**
     * @param $module
     * @param $jsonArraysClient
     * @throws \Exception
     */
    public function getDataFromDistantModules($module, $jsonArraysClient)
    {
        $langArray = self::MDB_LANGUAGES_SUPPORTED;

        $output = [];

        switch ($module) {
            case CategorySportSegment::getBdmModuleNumber():
            case GenderSegment::getBdmModuleNumber():
            case BGroupSegment::getBdmModuleNumber():
            case TypeSegment::getBdmModuleNumber():
            case TypesB2CSegment::getBdmModuleNumber():
            case Category1Segment::getBdmModuleNumber():
            case Category2Segment::getBdmModuleNumber():
            case Category3Segment::getBdmModuleNumber():
            case CategoryB2BSegment::getBdmModuleNumber():
            case CollectionSegment::getBdmModuleNumber():
            case MiscLabelsSegment::getBdmModuleNumber():
                foreach ($jsonArraysClient as $jsonArrayClient) {
                    $oid = $jsonArrayClient['id'];

                    $displayOrder = '';

                    if (isset($jsonArrayClient["attributes"])) {
                        $bdmAttributes = $jsonArrayClient["attributes"];

                        switch ($module) {
                            case CategorySportSegment::getBdmModuleNumber():
                                if (!isset($bdmAttributes["CORDER"])) {
                                    $this->logger->error(sprintf(
                                        '%s %s DISPLAYORDER is not set correctly',
                                        $jsonArrayClient["id"],
                                        $jsonArrayClient["type"]
                                    ));
                                } else {
                                    $displayOrder = $bdmAttributes["CORDER"];
                                }
                                break;

                            case GenderSegment::getBdmModuleNumber():
                                if (!isset($bdmAttributes["GORDER"])) {
                                    $this->logger->error(sprintf(
                                        '%s %s DISPLAYORDER is not set correctly',
                                        $jsonArrayClient["id"],
                                        $jsonArrayClient["type"]
                                    ));
                                } else {
                                    $displayOrder = $bdmAttributes["GORDER"];
                                }
                                break;

                            case TypeSegment::getBdmModuleNumber():
                                if (!isset($bdmAttributes["TORDER"])) {
                                    $this->logger->error(sprintf(
                                        '%s %s DISPLAYORDER is not set correctly',
                                        $jsonArrayClient["id"],
                                        $jsonArrayClient["type"]
                                    ));
                                } else {
                                    $displayOrder = $bdmAttributes["TORDER"];
                                }
                                break;
                        }
                    }

                    $genericData = $this->seekLabels($jsonArraysClient, $module, $langArray);
                    $output[$oid] = [
                        'oid' => $oid,
                        'type' => $jsonArrayClient["type"],
                        'lastMDBUpdate' => $this->retrieveLastBdmUpdate($jsonArrayClient),
                        'label' => $genericData[$oid]['label'],
                        'displayOrder' => $displayOrder,
                    ];

                    if (count($output) >= 25) {
                        $this->updateOrCreateSegment($output, $module);
                        unset($output);
                    }
                }
                if (isset($output)) {
                    $this->updateOrCreateSegment($output, $module);
                    unset($output);
                }
                break;

            case SpecLabelSegment::getBdmModuleNumber():
                $output = [];
                foreach ($jsonArraysClient as $jsonArrayClient) {
                    $oid = $jsonArrayClient['id'];

                    $code = null;

                    if (isset($jsonArrayClient["attributes"])) {
                        $bdmAttributes = $jsonArrayClient["attributes"];

                        if (isset($bdmAttributes["VARIABL"])) {
                            $code = $bdmAttributes["VARIABL"];
                            if (substr($code, 0, 3) === "SL_") {
                                $code = ltrim($code, "SL_");
                            } else {
                                $this->logger->error(sprintf(
                                    '%s %s VARIABL is not set correctly',
                                    $jsonArrayClient["id"],
                                    $jsonArrayClient["type"]
                                ));
                            }
                        } else {
                            $this->logger->error(
                                sprintf('%s %s VARIABL is not available', $jsonArrayClient["id"], $jsonArrayClient["type"])
                            );
                        }
                    }

                    $genericData = $this->seekLabels($jsonArraysClient, $module, $langArray);
                    $output[$oid] = [
                        'oid' => $oid,
                        'type' => $jsonArrayClient["type"],
                        'lastMDBUpdate' => $this->retrieveLastBdmUpdate($jsonArrayClient),
                        'label' => $genericData[$oid]['label'],
                        'code' => $code,
                    ];

                    if (count($output) >= 25) {
                        $this->updateOrCreateSegment($output, $module);
                        unset($output);
                    }
                }
                if (isset($output)) {
                    $this->updateOrCreateSegment($output, $module);
                    unset($output);
                }
                break;

            case SpecDefinitionSegment::getBdmModuleNumber():
                foreach ($jsonArraysClient as $jsonArrayClient) {
                    $output = [];
                    $oid = $jsonArrayClient["id"];

                    $label = [];
                    $code = null;

                    if (isset($jsonArrayClient["attributes"])) {
                        $bdmAttributes = $jsonArrayClient["attributes"];

                        foreach ($langArray as $lang) {
                            if (isset($bdmAttributes["LABEL"][$lang])) {
                                $label[$lang] = $bdmAttributes["LABEL"][$lang];
                            } elseif (isset($bdmAttributes["TITLE"])) {
                                foreach ($bdmAttributes["TITLE"] as $firstFound) {
                                    $label[$lang] = $firstFound;
                                    break;
                                }
                            } else {
                                $label[$lang] = null;
                            }
                        }

                        if (isset($bdmAttributes["NAME"])) {
                            $code = $bdmAttributes["NAME"];
                        } else {
                            $this->logger->error(
                                sprintf('%s %s CODE is not set correctly', $jsonArrayClient["id"], $jsonArrayClient["type"])
                            );
                        }
                    }

                    $output[$oid] = [
                        'type' => $jsonArrayClient["type"],
                        'lastMDBUpdate' => $this->retrieveLastBdmUpdate($jsonArrayClient),
                        'oid' => $oid,
                        'label' => $label,
                        'code' => $code,
                    ];

                    if (count($output) >= 25) {
                        $this->updateOrCreateSegment($output, $module);
                        unset($output);
                    }
                }
                if (isset($output)) {
                    $this->updateOrCreateSegment($output, $module);
                    unset($output);
                }
                break;

            case Season::getBdmModuleNumber():
                $output = [];
                foreach ($jsonArraysClient as $jsonArrayClient) {
                    $oid = $jsonArrayClient['id'];

                    $genericData = $this->seekLabels($jsonArraysClient, $module, $langArray);
                    $label = $genericData[$oid]['label'];

                    $code = null;


                    if (isset($jsonArrayClient["attributes"])) {
                        $bdmAttributes = $jsonArrayClient["attributes"];

                        if (isset($bdmAttributes["CODE"])) {
                            $code = $bdmAttributes["CODE"];
                        } else {
                            $this->logger->error(sprintf(
                                '%s %s CODE is not set correctly', $jsonArrayClient["id"], $jsonArrayClient["type"]
                            ));
                        }
                    }

                    $output[$oid] = [
                        'oid' => $oid,
                        'type' => $jsonArrayClient["type"],
                        'lastMDBUpdate' => $this->retrieveLastBdmUpdate($jsonArrayClient),
                        'label' => $label,
                        'code' => $code,
                    ];

                    if (count($output) >= 25) {
                        $this->updateOrCreateSegment($output, $module);
                        unset($output);
                    }
                }

                if (isset($output)) {
                    $this->updateOrCreateSegment($output, $module);
                    unset($output);
                }
                break;

            case BrandSegment::getBdmModuleNumber():
                $output = [];
                foreach ($jsonArraysClient as $jsonArrayClient) {
                    $oid = $jsonArrayClient['id'];

                    $code = null;
                    $bGroup = ""; // fixme: null everywhere but not here? why?

                    if (isset($jsonArrayClient["attributes"])) {
                        $bdmAttributes = $jsonArrayClient["attributes"];

                        if (isset($bdmAttributes["CODE"])) {
                            $code = $bdmAttributes["CODE"];
                        } else {
                            $this->logger->error(sprintf(
                                '%s %s CODE is not set correctly', $jsonArrayClient["id"], $jsonArrayClient["type"]
                            ));
                        }
                    }

                    if (isset($jsonArrayClient["relationships"])) {
                        $bdmRelationships = $jsonArrayClient["relationships"];

                        if (isset($bdmRelationships["BGROUP"]["data"]["id"])) {
                            $bGroup = $bdmRelationships["BGROUP"]["data"]["id"];
                        } else {
                            $this->logger->error(sprintf(
                                '%s %s BGROUP is not set correctly',
                                $jsonArrayClient["id"],
                                $jsonArrayClient["type"]
                            ));
                        }
                    }

                    $genericData = $this->seekLabels($jsonArraysClient, $module, $langArray);
                    $output[$oid] = [
                        'oid' => $oid,
                        'type' => $jsonArrayClient["type"],
                        'lastMDBUpdate' => $this->retrieveLastBdmUpdate($jsonArrayClient),
                        'label' => $genericData[$oid]['label'],
                        'code' => $code,
                        'bGroup' => $bGroup,
                    ];

                    if (count($output) >= 25) {
                        $this->updateOrCreateSegment($output, $module);
                        unset($output);
                    }
                }

                if (isset($output)) {
                    $this->updateOrCreateSegment($output, $module);
                    unset($output);
                }
                break;

            case Awards::getBdmModuleNumber(): //AWARDS
                $output = [];

                foreach ($jsonArraysClient as $jsonArrayClient) {
                    $oid = $jsonArrayClient['id'];

                    $url = null;
                    $img = null;

                    $value = [];

                    // POPULATE ATTRIBUTES

                    if (isset($jsonArrayClient["attributes"])) {
                        $bdmAttributes = $jsonArrayClient["attributes"];

                        //This is a translatable field
                        //foreach language (i.e. "en_AA" / "de_DE" / "fr_FR" ...) we set the correct value
                        //associated with its language code.
                        //
                        // for example :
                        // $value
                        // array:6 [
                        //  "en_AA" => "ACCESS PERF - SKI CHRONO"
                        //  "de_DE" => "ACCESS PERF - SKI CHRONO"
                        //  "fr_FR" => "ACCESS PERF - SKI CHRONO"
                        // ...]
                        foreach (self::MDB_LANGUAGES_SUPPORTED as $lang) {
                            if (isset($bdmAttributes["TITLE"][$lang])) {
                                $value[$lang] = $bdmAttributes["TITLE"][$lang];
                            }
                        }

                        if (isset($bdmAttributes["IMAGE"]["mimetype"])) {
                            $bdmImgMimeType = $bdmAttributes["IMAGE"]["mimetype"];

                            if (in_array($bdmImgMimeType, self::SUPPORTED_MIMETYPES_IMAGES)) {
                                $img = $bdmAttributes["IMAGE"]["originalname"];
                                $url = $bdmAttributes["IMAGE"]["url"];
                            } else {
                                $this->logger->alert(sprintf(
                                    '%s %s IMAGE/mimetype or IMAGE/orignalname is invalid',
                                    $jsonArrayClient["id"],
                                    $jsonArrayClient["type"]
                                ));
                            }
                        } else {
                            $this->logger->alert(sprintf(
                                'oid : %s, no IMAGE key found in jsonArrayClient', $jsonArrayClient["id"]
                            ));
                        }
                    }

                    $output[$oid] = [
                        'oid' => $oid,
                        'type' => $jsonArrayClient["type"],
                        'lastMDBUpdate' => $this->retrieveLastBdmUpdate($jsonArrayClient),
                        'value' => $value, //array
                        'img' => $img,
                        'url' => $url,
                    ];

                    if (count($output) >= 25) {
                        $this->updateOrCreateSegment($output, $module);
                        unset($output);
                    }
                }

                if (isset($output)) {
                    $this->updateOrCreateSegment($output, $module);
                    unset($output);
                }
                break;

            case TechnoSegment::getBdmModuleNumber():
                $output = [];

                foreach ($jsonArraysClient as $jsonArrayClient) {
                    $oid = $jsonArrayClient['id'];

                    $genericData = $this->seekLabels($jsonArraysClient, $module, $langArray);
                    $label = $genericData[$oid]['label'];

                    $description = null;

                    if (isset($jsonArrayClient["attributes"])) {
                        $bdmAttributes = $jsonArrayClient["attributes"];

                        foreach ($langArray as $lang) {
                            if (isset($bdmAttributes["DESCRIPTION"][$lang])) {
                                $description[$lang] = $bdmAttributes["DESCRIPTION"][$lang];
                            } else {
                                $description[$lang] = "NO TRANSLATION AVAILABLE";
                                $this->logger->error(sprintf(
                                    '%s %s DESCRIPTION is not set correctly',
                                    $jsonArrayClient["id"],
                                    $jsonArrayClient["type"]
                                ));
                            }
                        }
                    }

                    $output[$oid] = [
                        'oid' => $oid,
                        'type' => $jsonArrayClient["type"],
                        'lastMDBUpdate' => $this->retrieveLastBdmUpdate($jsonArrayClient),
                        'label' => $label,
                        'description' => $description,
                    ];

                    if (count($output) >= 25) {
                        $this->updateOrCreateSegment($output, $module);
                        unset($output);
                    }
                }

                if (isset($output)) {
                    $this->updateOrCreateSegment($output, $module);
                    unset($output);
                }
                break;

            case ProductMedia::getBdmModuleNumber():
                foreach ($jsonArraysClient as $jsonArrayClient) {
                    $this->updateMedia($module, $jsonArrayClient);
                }
                break;

            case TechnoMedia::getBdmModuleNumber():
                $output = [];
                foreach ($jsonArraysClient as $jsonArrayClient) {
                    $oid = $jsonArrayClient["id"];

                    $originalName = null;
                    $url = null;

                    if (isset($jsonArrayClient["attributes"])) {
                        $bdmAttributes = $jsonArrayClient["attributes"];

                        if (isset($bdmAttributes["MEDIAFILE"]["url"])) {
                            $url = $bdmAttributes["MEDIAFILE"]["url"];
                        } else {
                            $this->logger->error(
                                sprintf(
                                    '%s %s MEDIAFILE/url is not set correctly',
                                    $jsonArrayClient["id"],
                                    $jsonArrayClient["type"]
                                )
                            );
                        }

                        if (isset($bdmAttributes["MEDIAFILE"]["originalname"])) {
                            $originalName = $bdmAttributes["MEDIAFILE"]["originalname"];
                        }
                    }

                    $output[$oid] = [
                        'oid' => $oid,
                        'type' => $jsonArrayClient["type"],
                        'lastMDBUpdate' => $this->retrieveLastBdmUpdate($jsonArrayClient),
                        'url' => $url,
                        'originalName' => $originalName,
                    ];

                    if (count($output) >= 25) {
                        $this->updateOrCreateSegment($output, $module);
                        unset($output);
                    }
                }

                if (isset($output)) {
                    $this->updateOrCreateSegment($output, $module);
                    unset($output);
                }
                break;

            case Package::getBdmModuleNumber():
                $output = [];
                foreach ($jsonArraysClient as $jsonArrayClient) {
                    $oid = $jsonArrayClient['id'];

                    $genericData = $this->seekLabels($jsonArraysClient, $module, $langArray);
                    $label = $genericData[$oid]['label'];

                    $brandOid = null;
                    $seasonOid = null;

                    if (isset($jsonArrayClient["relationships"])) {
                        $bdmRelationships = $jsonArrayClient["relationships"];

                        if (isset($bdmRelationships["SEASON"]["data"]["id"])) {
                            $seasonOid = $bdmRelationships["SEASON"]["data"]["id"];
                        } else {
                            $this->logger->info(
                                sprintf(
                                    '%s %s Package/SeasonOid is not available on BDM',
                                    $jsonArrayClient["id"],
                                    $jsonArrayClient["type"]
                                )
                            );
                        }

                        if (isset($bdmRelationships["BRAND"]["data"]["id"])) {
                            $brandOid = $bdmRelationships["BRAND"]["data"]["id"];
                        } else {
                            $this->logger->info(
                                sprintf(
                                    '%s %s Package/BrandOid is not available on BDM',
                                    $jsonArrayClient["id"],
                                    $jsonArrayClient["type"]
                                )
                            );
                        }
                    }

                    $output[$oid] = [
                        'oid' => $oid,
                        'type' => $jsonArrayClient["type"],
                        'lastMDBUpdate' => $this->retrieveLastBdmUpdate($jsonArrayClient),
                        'label' => $label,
                        'season' => $seasonOid,
                        'trademark' => $brandOid,
                    ];

                    if (count($output) >= 25) {
                        $this->updateOrCreateSegment($output, $module);
                        unset($output);
                    }
                }

                if (isset($output)) {
                    $this->updateOrCreateSegment($output, $module);
                    unset($output);
                }
                break;

            case Product::getBdmModuleNumber():
                foreach ($jsonArraysClient as $jsonArrayClient) {
                    $this->updateThisProduct($module, $jsonArrayClient);
                }
                break;

            case PackageProduct::getBdmModuleNumber():
                $output = [];
                foreach ($jsonArraysClient as $jsonArrayClient) {
                    $oid = $jsonArrayClient['id'];

                    $sequence = null;
                    $package = null;
                    $product = null;

                    if (isset($jsonArrayClient["attributes"])) {
                        $bdmAttributes = $jsonArrayClient["attributes"];

                        if (isset($bdmAttributes["ORDERP"])) {
                            $sequence = $bdmAttributes["ORDERP"];
                        } else {
                            $this->logger->info(sprintf(
                                'Sequence %s %s / reference is not available on BDM',
                                $jsonArrayClient["id"],
                                $jsonArrayClient["type"]
                            ));
                        }
                    }

                    if (isset($jsonArrayClient["relationships"])) {
                        $bdmRelationships = $jsonArrayClient["relationships"];

                        if (isset($bdmRelationships["package"]["data"]["id"])) {
                            $package = $bdmRelationships["package"]["data"]["id"];
                        } else {
                            $this->logger->info(sprintf(
                                'Package\'s not available for this PackProd %s %s',
                                $jsonArrayClient["id"],
                                $jsonArrayClient["type"]
                            ));
                        }

                        if (isset($bdmRelationships["product"]["data"]["id"])) {
                            $product = $bdmRelationships["product"]["data"]["id"];
                        } else {
                            $this->logger->info(sprintf(
                                '%s %s Product/reference is not available on BDM',
                                $jsonArrayClient["id"],
                                $jsonArrayClient["type"]
                            ));
                        }
                    }

                    $output[$oid] = [
                        'oid' => $oid,
                        'type' => $jsonArrayClient["type"],
                        'lastMDBUpdate' => $this->retrieveLastBdmUpdate($jsonArrayClient),
                        'sequence' => $sequence,
                        'package' => $package,
                        'product' => $product,
                    ];

                    if (count($output) >= 25) {
                        $this->updateOrCreateSegment($output, $module);
                        unset($output);
                    }
                }

                if (isset($output)) {
                    $this->updateOrCreateSegment($output, $module);
                    unset($output);
                }
                break;

            default:
                throw new \Exception('Unknown module ' . $module);
        }
    }

    /**
     * @param $jsonArraysClient
     * @param $module
     * @param $langArray
     * @return array
     * Retrieving Labels form almost all modules since most of them are more or less similar
     * Almost all modules are concerned except (MODULE_PRODUCT,MODULE_PRODMEDIA,MODULE_PACKPROD)
     */
    public function seekLabels($jsonArraysClient, $module, $langArray)
    {
        $data = [];
        $candidates = ['NAME', 'LABEL', 'LABEL1', 'LABTYPE'];
        $defaultLang = 'en_AA';

        foreach ($jsonArraysClient as $jsonArrayClient) {
            $oid = $jsonArrayClient["id"];
            $labels = [];

            if (isset($jsonArrayClient["attributes"])) {
                $bdmAttributes = $jsonArrayClient["attributes"];

                foreach ($langArray as $lang) {
                    // -- Try to get label from common keys, stop when first one has been found
                    foreach ($candidates as $candidate) {
                        if (isset($bdmAttributes[$candidate])) {
                            $labels[$lang] = $this->retrieveLocalizedLabel(
                                $bdmAttributes[$candidate],
                                $lang,
                                $defaultLang
                            );
                            break;
                        }
                    }
                }
            }

            $data[$oid] = [
                'label' => $labels,
            ];
        }

        return $data;
    }

    protected function retrieveLocalizedLabel($data, $lang, $defaultLang)
    {
        if (is_array($data)) {
            if (isset($data[$lang])) {
                return $data[$lang];

            } elseif (isset($data[$defaultLang])) {
                return $data[$defaultLang];

            } else {
                return '';

            }

        } elseif (is_string($data)) {
            return $data;

        }

        return '';
    }

    /**
     * @param $jsonArraysClient
     * @param $lang
     * @param \DateTime $lastLocalUpdate
     * @return array
     * GET LABEL FROM SPECIIFIC FORMAT // AWARDS(MODULE_AWARDS) module
     */
    public function seekAwardsLabel($jsonArraysClient, $lang)
    {
        $data = [];
        foreach ($jsonArraysClient as $jsonArrayClient) {
            $oid = $jsonArrayClient["id"];

            $label = null;

            if (isset($jsonArrayClient["attributes"])) {
                $bdmAttributes = $jsonArrayClient["attributes"];
                if (isset($bdmAttributes["NAME"][$lang])) {
                    $label = $bdmAttributes["NAME"][$lang]; // useful case ?
                } elseif (isset($bdmAttributes["TITLE"][$lang])) {
                    $label = $bdmAttributes["TITLE"][$lang];
                } elseif (isset($bdmAttributes["TITLE"])) { // exception case module MODULE_AWARDS
                    foreach ($bdmAttributes["TITLE"] as $firstFound) {
                        $label = $firstFound;
                        break;
                    }
                } else {
                    $this->logger->error(sprintf(
                        '%s %s LABEL is not set correctly', $jsonArrayClient["id"], $jsonArrayClient["type"]
                    ));
                }
            }

            $data[$oid] = [
                'awardslabel' => $label,
            ];
        }

        return $data;
    }

    /**
     * @param $jsonArraysClient
     * @param \DateTime $lastLocalUpdate
     * @return array
     * BRAND(MODULE_BRAND_SEGMENT) module
     */
    public function seekBGroup($jsonArraysClient)
    {
        $data = [];
        foreach ($jsonArraysClient as $jsonArrayClient) {
            $lastMDBUpdate = $this->retrieveLastBdmUpdate($jsonArrayClient);
            $oid = $jsonArrayClient["id"];

            if (isset($jsonArrayClient["relationships"]["BGROUP"]["data"]["id"])) {
                $bGroup = $jsonArrayClient["relationships"]["BGROUP"]["data"]["id"];

            } else {
                $bGroup = "";
                $this->logger->error(
                    sprintf('%s %s BGROUP is not set correctly', $jsonArrayClient["id"], $jsonArrayClient["type"])
                );
            }

            $data[$oid] = [
                'oid' => $oid,
                'type' => $jsonArrayClient["type"],
                'lastMDBUpdate' => $lastMDBUpdate,
                'bGroup' => $bGroup,
            ];
        }

        return $data;
    }

    public function updateThisProduct($module, array $jsonArrayClient, $forceUpdate = false): void
    {
        $oid = $jsonArrayClient['id'];

        $season = null;
        $reference = null;
        $attributes = null;
        $associatedMedias = null;
        $mainTechno = null;
        $secondaryTechno = null;
        $trademark = null;

        $label = [];
        $metadata = [];
        $relationships = [];
        $translatableAttributes = [];

        // POPULATE ATTRIBUTES

        if (isset($jsonArrayClient["attributes"])) {
            $bdmAttributes = $jsonArrayClient["attributes"];

            if (isset($bdmAttributes["reference"])) {
                $reference = $bdmAttributes["reference"];
            }

            if (!isset($bdmAttributes["DESCR_METADATA"])) {
                $this->logger->info(sprintf(
                        '%s %s BdmProduct/DESCR_METADATA is not available on BDM.',
                        $jsonArrayClient["id"], $jsonArrayClient["type"])
                );
            }
            foreach (self::MDB_LANGUAGES_SUPPORTED as $lang) {
                $metadata[$lang] = isset($bdmAttributes["DESCR_METADATA"]) ? $bdmAttributes["DESCR_METADATA"] : null;
            }

            if (!isset($bdmAttributes["DESCR_DESCRIPTIONCONSUMERS"])) {
                $this->logger->info(
                    sprintf(
                        '%s %s BdmProduct/DESCR_DESCRIPTIONCONSUMERS is not available on BDM',
                        $jsonArrayClient["id"],
                        $jsonArrayClient["type"]
                    )
                );
            }
            foreach (self::MDB_LANGUAGES_SUPPORTED as $lang) {
                $label[$lang] = isset($bdmAttributes["DESCR_DESCRIPTIONCONSUMERS"][$lang]) ?
                    $bdmAttributes["DESCR_DESCRIPTIONCONSUMERS"][$lang] : null;
            }

            foreach ($bdmAttributes as $key => $value) {
                if ($key != "UPD") {
                    if (is_array($value)) {
                        // keep only managed locales
                        foreach ($value as $lang => $langValue) {
                            if (in_array($lang, self::MDB_LANGUAGES_SUPPORTED)) {
                                $translatableAttributes[$key][$lang] = $langValue;
                            }
                        }
                    } else {
                        $attributes[$key] = $value;
                    }
                }
            }

        } // end attributes

        // POPULATE RELATIONSHIPS

        if (isset($jsonArrayClient["relationships"])) {
            $bdmRelationships = $jsonArrayClient["relationships"];
            foreach ($jsonArrayClient["relationships"] as $key => $value) {
                if (isset($value['data']['type']) && in_array($value['data']['type'], self::PRODUCT_SEGMENTS)) {
                    $relationships[] = [$value['data']['type'] => $value['data']['id']];
                }
            }

            if (isset($bdmRelationships["season"]["data"]["id"])) {
                $season = $bdmRelationships["season"]["data"]["id"];
            } else {
                $this->logger->info(
                    sprintf(
                        '%s %s BdmProduct/season is not available on BDM',
                        $jsonArrayClient["id"],
                        $jsonArrayClient["type"]
                    )
                );
            }

            if (isset($bdmRelationships["trademark"]["data"]["id"])) {
                $trademark = $bdmRelationships["trademark"]["data"]["id"];
            } else {
                $this->logger->info(
                    sprintf(
                        '%s %s BdmProduct/trademark is not available on BDM',
                        $jsonArrayClient["id"],
                        $jsonArrayClient["type"]
                    )
                );
            }

            if (isset($bdmRelationships["DESCR_MAINTECHNOLOGY"]["data"]["id"])) {
                $mainTechno = $bdmRelationships["DESCR_MAINTECHNOLOGY"]["data"]["id"];
            } else {
                $this->logger->info(
                    sprintf(
                        '%s %s product is not associated with any TECHNO on BDM',
                        $jsonArrayClient["id"],
                        $jsonArrayClient["type"]
                    )
                );
            }

            if (isset($bdmRelationships["DESCR_OTHERTECHNOLOGIES"]["data"])) {
                $secondaryTechno = $bdmRelationships["DESCR_OTHERTECHNOLOGIES"]["data"];
            } else {
                $this->logger->info(
                    sprintf(
                        '%s %s product has no secondary TECHNO bound on BDM',
                        $jsonArrayClient["id"],
                        $jsonArrayClient["type"]
                    )
                );
            }

            if (isset($bdmRelationships["medias"])) {
                $associatedMedias = $bdmRelationships["medias"];
            } else {
                $this->logger->info(
                    sprintf(
                        '%s %s product is not associated with any MEDIAS on BDM',
                        $jsonArrayClient["id"],
                        $jsonArrayClient["type"]
                    )
                );
            }

        } else {
            $this->logger->info(
                sprintf('%s %s has no relationships !', $jsonArrayClient["id"], $jsonArrayClient["type"])
            );
        } // end relationships

        $output[$oid] = [
            'type' => $jsonArrayClient["type"],
            'lastMDBUpdate' => $this->retrieveLastBdmUpdate($jsonArrayClient),
            'oid' => $oid,
            'trademark' => $trademark,
            'season' => $season,
            'reference' => $reference,
            'metadata' => $metadata,
            'label' => $label,
            'attributes' => $attributes,
            'translatableAttributes' => $translatableAttributes,
            'associatedMedias' => $associatedMedias,
            'relationships' => $relationships,
            'mainTechno' => $mainTechno,
            'secondaryTechno' => $secondaryTechno,
        ];

        $this->updateOrCreateSegment($output, $module, $forceUpdate);
        unset($output);
    }

    /**
     * todo: need serious review!
     *
     * @param $module
     * @param $jsonArrayClient
     */
    public function updateMedia($module, $jsonArrayClient)
    {
        $output = [];
        if (!isset($jsonArrayClient["id"])) {
            $this->logger->error(sprintf('No OID for this Media, skipping...'));
            return;
        }

        $oid = $jsonArrayClient["id"];

        $mediaFile = null;
        $mediaOid = null;
        $originalName = null;
        $productOid = null;

        $associatedProduct = '';
        $needleAttributes = "";
        $needleRelationships = "";

        $mediaSegment = [];

        if (isset($jsonArrayClient["attributes"])) {
            $bdmAttributes = $jsonArrayClient["attributes"];

            if (isset($bdmAttributes["ORIGINALNAME"])) {
                $originalName = $bdmAttributes["ORIGINALNAME"];
            }

            if (isset($bdmAttributes["MEDIAFILE"])) {
                $mediaFile = $bdmAttributes["MEDIAFILE"];
            }
        }

        if (isset($jsonArrayClient["relationships"])) {
            $bdmRelationships = $jsonArrayClient["relationships"];

            if (isset($bdmRelationships["MEDIASEGMENT"]["data"])) {
                $mediaSegment = $bdmRelationships["MEDIASEGMENT"]["data"]; //array (fields "type", "id", "attributes"/"MEDIATYPE"
            }

            if (isset($bdmRelationships["product"]["data"]["attributes"]["GC_REFERENCE"])) {
                $associatedProduct = $bdmRelationships["product"]["data"]["attributes"]["GC_REFERENCE"];
            } else {
                $this->logger->error(sprintf(
                    '%s %s NO PRODUCT REFERENCE ASSOCIATED FOR THIS MEDIA',
                    $jsonArrayClient["id"],
                    $jsonArrayClient["type"]
                ));
            }

            if (isset($bdmRelationships["MEDIASEGMENT"]["data"]['id'])) {
                $mediaOid = $bdmRelationships["MEDIASEGMENT"]["data"]['id'];
            } else {
                $this->logger->error(
                    sprintf('%s %s MEDIAOID is not set', $jsonArrayClient["id"], $jsonArrayClient["type"])
                );
            }

            if (isset($bdmRelationships["product"]["data"]['id'])) {
                $productOid = $bdmRelationships["product"]["data"]['id'];
            } else {
                $this->logger->error(
                    sprintf('%s %s PRODUCTOID is not set correctly', $jsonArrayClient["id"], $jsonArrayClient["type"])
                );
            }
        }

        $haystack = ['RVB72', 'ILLUSTRATION', 'WMV', 'MOV', 'EXTERNAL', 'PNG', 'ACTIONSHOT'];

        if (isset($mediaSegment["attributes"]["MEDIATYPE"])) {
            $needleAttributes = $mediaSegment["attributes"]["MEDIATYPE"];
        }

        if (isset($mediaSegment["relationships"]["MEDIATYPE"])) {
            $needleRelationships = $mediaSegment["relationships"]["MEDIATYPE"];
        }

        if (in_array($needleAttributes, $haystack) || in_array($needleRelationships, $haystack)) {
            $referenceImage = null;
            if (!isset($bdmAttributes) || !isset($bdmAttributes['REFERENCEIMAGE'])) {
                // todo: skip??
            } else {
                $referenceImage = strtolower($bdmAttributes["REFERENCEIMAGE"]) === "yes";
            }

            //IS THIS MEDISEGMENT A VIDEO ? Youtube, Vimeo, Dailymotion
            if ($mediaSegment["type"] == 'mediasegmentation' && $mediaSegment["id"] == '56uahgbja8g6' && $mediaFile !== null) {

                if ((isset($mediaFile["externalType"]))) {
                    $url = $mediaFile["externalUrl"];

                    $provider = $mediaFile["externalType"];
                    switch ($provider) {
                        case 'youtube':
                            $videoId = $this->getYoutubeVideoIdFromLink($url);
                            break;
                        case 'dailymotion':
                            $videoId = $this->getDailymotionVideoIdFromLink($url);
                            break;
                        case 'vimeo':
                            $videoId = $this->getVimeoVideoIdFromLink($url);
                            break;
                        default:
                            // TODO: skip??
                            $videoId = '';
                            break;
                    }

                    $label = $videoId . " - " . $provider;

                    $output[$oid] = [
                        'type' => $jsonArrayClient["type"],
                        'lastMDBUpdate' => $this->retrieveLastBdmUpdate($jsonArrayClient),
                        'oid' => $oid,
                        'label' => $label,
                        'url' => $url,
                        'isVideo' => true,
                        'videoId' => $videoId,
                        'provider' => $provider,
                        'productOid' => $productOid,
                        'originalName' => $originalName,
                        'associatedProduct' => $associatedProduct,
                    ];

                } else {
                    $this->logger->error(sprintf(
                        '%s %s MEDIAFILE VIDEO (segmentation) is not set correctly',
                        $jsonArrayClient["id"],
                        $jsonArrayClient["type"]
                    ));
                }

                //IS THIS MEDIASEGMENT A PICTURE/IMAGE JPEG/PNG
            } elseif (isset($mediaFile["mimetype"])) {

                if (in_array($mediaFile["mimetype"], self::SUPPORTED_MIMETYPES_IMAGES)) {
                    $url = $mediaFile["url"];
                    $label = '';
                    $color = null;

                    // Recover color from ORIGINAL NAME "_" and "-" separators
                    if (isset($mediaFile["originalname"])) {
                        $label = $mediaFile["originalname"];
                        $colorArray = explode("_", $label);

                        if (isset($colorArray[1])) {
                            $temp = explode('.', $colorArray[1]);
                            $color = $temp[0];
                            if (strpos($color, '-') != false) {
                                $colorArray = explode('-', $color);
                                $color = $colorArray[0];
                            }
                        }
                    }

                    if (!$color) {
                        $color = '000';
                        $this->logger->error(sprintf(
                            '%s %s NO PRODUCT OR NO COLOR ASSOCIATED FOR THIS MEDIA',
                            $jsonArrayClient["id"],
                            $jsonArrayClient["type"]
                        ));
                    }

                    $output[$oid] = [
                        'type' => $jsonArrayClient["type"],
                        'lastMDBUpdate' => $this->retrieveLastBdmUpdate($jsonArrayClient),
                        'oid' => $oid,
                        'originalName' => $originalName,
                        'referenceImage' => $referenceImage,
                        'label' => $label,
                        'url' => $url,
                        'color' => $color,
                        'mediaOid' => $mediaOid,
                        'productOid' => $productOid,
                        'isVideo' => false,
                        'associatedProduct' => $associatedProduct,
                    ];
                } else {
                    $this->logger->error(sprintf(
                        '%s %s mimetype %s is not supported',
                        $jsonArrayClient["id"],
                        $jsonArrayClient["type"],
                        $mediaFile["mimetype"]
                    ));
                    return;
                }
            }
        } else {
            $this->logger->warning(sprintf(
                '%s %s MEDIAFILE MEDIATYPE is excluded from authorized segments',
                $jsonArrayClient["id"],
                $jsonArrayClient["type"]
            ));
            return;
        }

        if (count($output) >= 25) {
            $this->updateOrCreateSegment($output, $module);
            unset($output);

        }
        if (isset($output)) {
            $this->updateOrCreateSegment($output, $module);
            unset($output);
        }
    }

    /**
     * @param $jsonArrayClient
     * @return string
     */
    public function retrieveLastBdmUpdate($jsonArrayClient)
    {

        $lastMDBUpdate = date_create($jsonArrayClient["lst_upd"])->format('Y-m-d H:i:s');

        return $lastMDBUpdate;
    }

    /**
     * @param $output
     * @param $module
     * Returns results according to update status and those are sent to bdmPersistService->createSegment()
     */
    public function updateOrCreateSegment($output, $module, $forceUpdate = false)
    {
        // Some debuging tools in case of laking memory
        // $memoryUsage = Helper::formatMemory(memory_get_usage(true));
        // $moduleLog = JsonClientService::$modulesJSONApiBinding[$module];
        // $oidLog = $oidArray['oid'];
        if ($module == 0) {
            // ***** NO NEW AVAILABLE OID SINCE LAST UPDATE *****;
        } else {

            $languages = self::MDB_LANGUAGES_SUPPORTED;

            //For each of these OID's arrays we...
            foreach ($output as $oidArray) {
                //... we query the local database with this current $oid
                // If it's a Product Media we query if it exists in our MediaRepo
                if ($module == ProductMedia::getBdmModuleNumber()) {
                    $localSegment = $this->productMediaRepository->findOneByOid(
                        strtoupper($oidArray['type']) . ":" . $oidArray['oid']
                    );
                    // Else we query our Entity Repo.
                } else {
                    $localSegment = $this->entityRepository->findOneByOid(
                        strtoupper($oidArray['type']) . ":" . $oidArray['oid']
                    );
                }

                //... checking if it exists into it
                if ($localSegment) {
                    // ****** EXISTING IN DB ******;
                    $update = true;

                    //... then we compare if the last distant update is later than the local one
                    $lastMDBUpdate = New \DateTime($oidArray['lastMDBUpdate']);
                    $lastMDBUpdate->format('Y-m-d H:i:s'); // TODO CLean

                    if ($localSegment->getUpdatedAt() <= $lastMDBUpdate || $forceUpdate == true) {
                        // ***** EXISTING IN DB AND OUTDATED *****;
                        // ... case OUTDATED, we update all variable fields
                        $this->bdmPersistService->createSegment($module, $oidArray, $languages, $update);

                    } else {
                        // This segment exists in our DB but doesn't require an update.
                        // echo "***** EXISTING IN DB AND UP TO DATE *****;
                        //... case UP TO DATE, we simply skip this iteration
                        continue;
                    }
                } else {
                    // *********** NEW SEGMENT **********
                    $update = false;
                    $this->bdmPersistService->createSegment($module, $oidArray, $languages, $update);
                }
            }
        }

        $jsonArraysClient = null;
    }


    /**
     * @param $url
     * @return null
     */
    public static function getYoutubeVideoIdFromLink($url)
    {
        preg_match(
            "#(?<=v=)[a-zA-Z0-9-]+(?=&)|(?<=v\/)[^&\n]+(?=\?)|(?<=v=)[^&\n]+|(?<=youtu.be/)[^&\n]+#",
            $url,
            $matches
        );

        return $matches[0] ? $matches[0] : null;
    }


    /**
     * @param $url
     * @return null
     */
    public static function getDailymotionVideoIdFromLink($url)
    {
        preg_match('#http://www.dailymotion.com/video/([A-Za-z0-9]+)#s', $url, $matches);

        return $matches[1] ? $matches[1] : null;
    }


    /**
     * @param $url
     * @return null
     */
    public static function getVimeoVideoIdFromLink($url)
    {
        $regex = '~
		# Match Vimeo link and embed code
		(?:<iframe [^>]*src=")?         # If iframe match up to first quote of src
		(?:                             # Group vimeo url
				https?:\/\/             # Either http or https
				(?:[\w]+\.)*            # Optional subdomains
				vimeo\.com              # Match vimeo.com
				(?:[\/\w]*\/videos?)?   # Optional video sub directory this handles groups links also
				\/                      # Slash before Id
				([0-9]+)                # $1: VIDEO_ID is numeric
				[^\s]*                  # Not a space
		)                               # End group
		"?                              # Match end quote if part of src
		(?:[^>]*></iframe>)?            # Match the end of the iframe
		(?:<p>.*</p>)?                  # Match any title information stuff
		~ix';

        preg_match($regex, $url, $matches);

        return $matches[1] ? $matches[1] : null;
    }

}
