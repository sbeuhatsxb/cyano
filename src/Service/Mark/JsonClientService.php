<?php

// *** DOC ***
// See /interns/doc/source/docTech/bdm.rst or http://rossignolb2b-doc.local/docTech/bdm.html for global specifications

namespace App\Service\Mark;


use Psr\Log\LoggerInterface;
use Symfony\Component\Dotenv\Dotenv;

/**
 * Class JsonClientService
 * @package App\Service\Bdm
 */
class JsonClientService extends JsonApiClientService
{
    const SESSION_VALIDITY = 163200; // 50 hours - 40 minutes
    const HTTP_STATUS_OK = 200;
    const DEFAULT_PAGE_SIZE = 30;
    const PAGE_SIZE_MAX = 50;
    const DEFAULT_SESSION_VALIDITY = 6000;

    /* Modules names*/
    const MODULE_SPECLABELS_SEGMENT = 11;
    const MODULE_CATEGORYSPORT_SEGMENT = 62;
    const MODULE_GENDER_SEGMENT = 63;
    const MODULE_TECHNO = 64;
    const MODULE_GROUP_SEGMENT = 65;
    const MODULE_BRAND_SEGMENT = 66;
    const MODULE_SEASON = 67;
    const MODULE_TYPE_SEGMENT = 68;
    const MODULE_PRODUCT = 70;
    const MODULE_CATEGORY1_SEGMENT = 72;
    const MODULE_CATEGORY2_SEGMENT = 73;
    const MODULE_CATEGORY3_SEGMENT = 74;
    const MODULE_MISC_LABELS_SEGMENT = 78;
    const MODULE_PRODMEDIA = 80;
    const MODULE_COLLECTION_SEGMENT = 85;
    const MODULE_TECHNOMEDIA = 87;
    const MODULE_AWARDS = 108;
    const MODULE_PACKAGE = 109;
    const MODULE_PACKPROD = 110;
    const MODULE_M3FAMILLE = 117;
    const MODULE_CATEGORYB2B_SEGMENT = 125;
    const MODULE_TYPESB2C_SEGMENT = 130;
    const MODULE_SPECDEFINITION_SEGMENT = 150;

    /**
     * [NEW_THEME_COMPONENT]
     */
    const PRODUCT_GROUP_ROSSIGNOL = 'BGROUP:4hcqz49svdsr';
    const PRODUCT_GROUP_DYNASTAR = 'BGROUP:4hcqz7ecrogn';
    const PRODUCT_GROUP_TIME = 'BGROUP:oea5923wx7pv';
    const PRODUCT_GROUP_RAIDLIGHT = 'BGROUP:5125923wuwwb';
    const PRODUCT_GROUP_FELT = 'BGROUP:m3d5d2ncz6ug';

    const CATEGORY_ALPIN = "SEGCATSPRT:cgeuxutg4w96j";

    const MEDIA_SEGMENT_EXTERNALVIDEO = 'MEDIASEGMENTATION:56uahgbja8g6';

    const YOUTUBE_VIDEO = 'youtube';
    const DAILYMOTION_VIDEO = 'dailymotion';
    const VIMEO_VIDEO = 'vimeo';

    /** @var array : Contains the bindings between the module's integers codes and their names in the JSON API */
    const MDB_TYPES_MODULESNUMBERS = [
        11  => 'labels',    //Labels
        62  => 'categorysport',    //Categories
        63  => 'gender',    //Categories
        64  => 'techno',    //Technologies
        65  => 'trademarkgroup',    //Groups
        66  => 'trademark',    //Groups
        67  => 'season',    //Seasons
        68  => 'producttype',    //Categories
        70  => 'product',    //All
        72  => 'category1',    //Category
        73  => 'category2',    //Category
        74  => 'category3',    //Category
        78  => 'misclabels',    //Misc.
        80  => 'media',    //Products
        84  => 'countries',    //Countries
        85  => 'collection',    //Collections
        87  => 'technomedia',    //Technologies
        88  => 'mediasegmentation',    //Medias
        89  => 'continent',    //Continents
        108 => 'pressreview',    //Products
        109 => 'package',    //Packages
        110 => 'packageproduct',    //Packages
        115 => 'price',    //Prices
        117 => 'm3famille',    //M3
        125 => 'categoryb2b',    //Category
        126 => 'advantage',    //Advantages
        127 => 'colors',    //M3
        128 => 'colorgroup',    //Color
        130 => 'producttypeb2c',    //Categories
        134 => 'selections',    //Selections
        138 => 'pricediscount',    //Prices
        139 => 'athlete',    //Athletes
        140 => 'athletemedia',    //Athletes
        141 => 'athletepalmares',    //Athletes
        142 => 'athletediscipline',    //Athletes
        143 => 'athletecateg',    //Athletes
        144 => 'athletecompetition',    //Athlete
        145 => 'kits',    //Kits
        146 => 'sizingchart',    //Sizing
        147 => 'athleteequipment',    //Athletes
        148 => 'segmententiontext',    //BdmProduct
        150 => "productspecs", //SPECDEFINITION
        155 => 'skus',    //Skus
    ];

    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * @var ParseService
     */
    protected $bdmParseService;

    /* METHODS OVERRIDE */
    /**
     * This implementation constructs the client with the configuration data for Rossignol Marketing Database
     * JsonClientService constructor.
     * @param LoggerInterface $logger
     * @param ParseService $bdmParseService
     */
    function __construct(LoggerInterface $logger, ParseService $bdmParseService, $url, $login, $password)
    {
        parent::__construct($logger, $bdmParseService, $login, $password, $url);
        //TODO NUMBER OF ENTRIES BY CHUNK
        $this->setCurrentPageSize(50);
        $this->setSessionValidity(self::SESSION_VALIDITY);

        $validity = gmdate("H", $this->getSessionValidity()) . ' hours ';

        $this->bdmParseService = $bdmParseService;
    }

    /**
     * Override of default authentication methdo to keep the session id in redis cache.
     * This allows to use the APiu for three hours without having to login again, from everywhere in Change.
     * @return String
     */
    public function getSessionId()
    {
        $data = [];
        $data['created'] = time();
        $data['sessId'] = parent::getSessionId();
        $data['validity'] = $this->getSessionExpires();

        return $data['sessId'];
    }

    /**
     * Override parent method "makeRequest" to keep a log of every call to the API
     * @param String $url
     * @return mixed|null
     */
    protected function makeRequest($url, $offset = null, $module = null, $lastLocalUpdate = NULL)
    {
        $this->logger->info(sprintf('[BDM_IMPORT] API Fetch URL (%s)', $url));

        return parent::makeRequest($url, $offset, $module, $lastLocalUpdate);
    }

    /**
     * We keep trace of modules by their integer id's (remnant from the soap era)
     * So this override will transform the interger code into the module name as string for the JSON API
     * @param integer $moduleCode
     */
    public function setCurrentModule($moduleCode)
    {
        $module = $this->getModuleNameByModuleCode($moduleCode);

        parent::setCurrentModule($module);
    }

    /**
     * Fetches module's name by its integer code
     * @param int $code
     * @return mixed|string
     */
    private function getModuleNameByModuleCode($code)
    {
        return isset(self::MDB_TYPES_MODULESNUMBERS[$code]) ? self::MDB_TYPES_MODULESNUMBERS[$code] : '';
    }

    /* COMPATIBILITY LAYER : Implement same methods than the old SOAP DB Client to still be compatible */

    /**
     * Set the session ID before first API call to avoid an unnecessary login
     * @param String $id
     */
    public function setPersistentSession($id)
    {
        $this->setSessionId($id);
    }

    /**
     * Fetches a collection of elements
     *
     * @param null $fields
     * @param null $filters
     * @param null $offset
     * @param $module
     * @param $lastLocalUpdate
     *
     * @return array
     */
    public function browse($fields = null, $filters = null, $offset = null, $module, $lastLocalUpdate)
    {

        // @param $fields will be ignored, kept to behave like SOAP client

        $apiParameters = null;
        if (is_array($filters)) {
            $apiParameters = new JsonApiParameterGroup();
            foreach ($filters as $key => $val) {
                if (is_array($val)) {
                    foreach ($val as $subVal) {
                        $apiParameters->addFilter(new JsonApiFilter($key, $subVal));
                    }
                } else {
                    $apiParameters->addFilter(new JsonApiFilter($key, $val));
                }
            }
        } else {
            if ($filters instanceof JsonApiParameterDefinition) {
                $apiParameters = $filters;
            }
        }
        $this->setCurrentParameters($apiParameters);
        return $this->findAll($offset, $module, false, $lastLocalUpdate);
    }

}

/**
 * Class rossignol_BdmJsonApiHelper
 *  Contains helper methods to use and extract data from the responses sent by the JSON API
 */
class rossignol_BdmJsonApiHelper
{

    /**
     * @var array
     * Conversion des noms de champs entre l'ancienne version de la BDM et la nouvelle
     * ainsi, lorsque l'on essaye de récupérer la valeur d'un segment avec son ancien nom,
     * le champ ciblé est automatiquement corrigé pour renvoyer la donnée malgré tout.
     */
    private static $segmentsBindingForV2 = [
        'GC_CATEGORYSPORT' => 'category',
        'GC_TYPE'          => 'producttype',
        'GC_BRAND'         => 'trademark',
        'GC_SEASON'        => 'season',
        'GC_REFERENCE'     => 'reference',
    ];

    public static $segmentsBindingInverseForV2 = [
        'category'    => 'GC_CATEGORYSPORT',
        'producttype' => 'GC_TYPE',
        'trademark'   => 'GC_BRAND',
        'season'      => 'GC_SEASON',
        'reference'   => 'GC_REFERENCE',
    ];

    /**
     * @var array
     * Binding between fields names in the new JSON Api (array keys) and the types for segments that previously
     * already existed in change (array values). This is used to preserve compatibility with all methods relying on old types labels
     * while allowing to use and parse the JSON Api.
     */
    private static $typeInjection = [
        'CATEGORYSPORT'  => 'SEGCATSPRT',
        'GENDER'         => 'SEGGENDER',
        'TRADEMARKGROUP' => 'BGROUP',
        'PRODUCT'        => 'PRODUCTS',
        'PRODUCTTYPE'    => 'SEGTYPE',
        'CATEGORY1'      => 'SEGCAT1',
        'CATEGORY2'      => 'SEGCAT2',
        'CATEGORY3'      => 'SEGCAT3',
        'MISCLABELS'     => 'MISCPRODUCTSLABELS',
        'COLLECTION'     => 'PRODUCTSCOLLECTION',
        'CATEGORYB2B'    => 'SEGCATB2B',
        'PRODUCTTYPEB2C' => 'B2CTYPE',
        'MEDIA'          => 'PRODMEDIA',

    ];

    /** @var array : Currently supported languages, used tu build the lang string */
    private static $langs = [
        'fr' => 'FR',
        'en' => 'EN',
        'de' => 'DE',
        'it' => 'IT',
    ];

    /**
     * Returns current lang in a JSON API notation friendly format
     * @return string
     */
    private static function getLang()
    {
        return 'en_AA';
    }

    /**
     * Returns the object ID with formatted as we use them in Change, ie "TYPE:id"
     * @param mixed $data
     * @return string
     */
    public function getOid($data)
    {

        $type = strtoupper($data['type']);

        if (isset(self::$typeInjection[$type])) {
            $type = self::$typeInjection[$type];
        }

        return $type . ':' . $data['id'];
    }

    /**
     * Extract the ID from a string like "TYPE:ID"
     * @param String $oid
     * @return String
     */
    public function extractId($oid)
    {
        if (f_util_StringUtils::contains($oid, ':')) {
            list(, $oid) = explode(':', $oid);
        }

        return $oid;
    }

    /**
     * Search for a field value in a JSON API Response
     *  Will look first in the "attributes", if something is found:
     *      - It's a simple string, will be returned
     *      - It's an array:
     *          - Check current language and check if a key matches the language (i18n) fields, then returns it
     *          - Otherwise returns the component as an array
     *
     * If nothing has been returned, we then look in the relations:
     *      - If a relation with the key corresponding to the parameter is found:
     *          - If it's a "simple array" : returns the object-id for the relation
     *          - If it has sub-arrays, returns an array of object-ids for every relation
     *
     * @param mixed $data
     * @param string $field
     * @return string|mixed|null
     */
    public function findValueForField($data, $field)
    {
        /* Special cases
         * On traite les bindings définis à la main dans la variable privée de classe $this->segmentsBindingForV2
         */
        if (isset(self::$segmentsBindingForV2[$field])) {
            return $this->findValueForField($data, self::$segmentsBindingForV2[$field]);
        }

        $lang = self::getLang();

        $attributes = $data['attributes'];

        if (isset($attributes[$field])) {
            $dataForField = $attributes[$field];

            if (is_array($dataForField)) {
                if (isset($dataForField[$lang])) {
                    return $dataForField[$lang];
                } else {
                    if ($lang === 'en_EN') {
                        // -- Cas spécial de l'anglais
                        if (isset($dataForField['en_US'])) {
                            return $dataForField['en_US'];
                        }
                        if (isset($dataForField['en_AA'])) {
                            return $dataForField['en_AA'];
                        }
                    }
                }
            }

            return $dataForField;
        }

        $relations = $data['relationships'];

        if (isset($relations[$field])) {
            $relationInfo = $relations[$field]['data'];
            if (is_array(f_util_ArrayUtils::firstElement($relationInfo))) {
                // -- Plusieurs valeures de relation
                return $this->findOidsForField($data, $field);
            }

            return $this->getOid($relationInfo);
        }

        return null;
    }

    /**
     * @param Array $data
     * @param String $field
     * @return array|null|string
     */
    public function findOidsForField($data, $field)
    {
        $relations = $data['relationships'];

        if (isset($relations[$field])) {
            $relationInfo = $relations[$field]['data'];


            $oids = [];
            foreach ($relationInfo as $item) {
                if (is_array($item)) {
                    $oids[] = $this->getOid($item);
                } else {
                    return $this->getOid($relationInfo);
                }
            }

            return $oids;
        }

        return null;
    }

    /**
     * @param mixed $data
     * @return string
     */
    public function findRelationSeasonCode($data)
    {
        $relations = $data['relationships'];
        $seasonData = $relations['season']['data'];
        $seasonAttr = $seasonData['attributes'];

        return $seasonAttr['CODE'];
    }

    /**
     * @param array $data
     * @return mixed|null
     */
    public function findMediaFile($data)
    {
        if (isset($data['attributes']['MEDIAFILE'])) {
            return $data['attributes']['MEDIAFILE'];
        }

        return null;
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function findMedias($data)
    {
        $relations = $data['relationships'];
        $medias = $relations['medias']['data'];

        return $medias;
    }

    /**
     * @param $data
     *
     * @return null| String
     */
    public function getMediaType($data)
    {
        if (isset($data['relationships']['MEDIASEGMENT']['data']['attributes']['MEDIATYPE'])) {
            return $data['relationships']['MEDIASEGMENT']['data']['attributes']['MEDIATYPE'];
        }

        return null;
    }

    /**
     * @param $data
     * @return mixed
     */
    public function getProductRefRelation($data)
    {
        if (isset($data['relationships']['product']['data']['attributes']['GC_REFERENCE'])) {
            ;
        }
        {
            return $data['relationships']['product']['data']['attributes']['GC_REFERENCE'];
        }
    }

    /**
     * Formats a "dd/mm/yyyy" date to be JSON API Friendly
     * @param String $dateString
     * @return string
     */
    public function formatDate($dateString)
    {
        $preg_eur = '/\d{2}\/\d{2}\/\d{4}/';
        $preg_us = '/\d{4}\-\d{2}\-\d{2}/';

        if (is_string($dateString) && preg_match($preg_eur, $dateString)) {
            list($day, $month, $year) = explode('/', $dateString);

            return $year . '-' . $month . '-' . $day;
        }

        if (is_string($dateString) && preg_match($preg_us, $dateString)) {
            return $dateString;
        }

        return '';
    }

}
