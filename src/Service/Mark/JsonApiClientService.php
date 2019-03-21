<?php

// *** DOC ***
// See /interns/doc/source/docTech/bdm.rst or http://rossignolb2b-doc.local/docTech/bdm.html for global specifications
//if BDM DATE reset needs to be done : SQL $ "update app_info_module_bdm set last_update_date = null;"

namespace App\Service\Mark;

use Doctrine\DBAL\Exception\InvalidArgumentException;
use Prophecy\Exception\Exception;
use Psr\Log\LoggerInterface;


define('DATA_FIELD', 'data');
define('LINKS_FIELD', 'links');

/**
 * Generic class for Json Api Interface
 * /!\ Requires PHP curl /!\
 *
 * Usage :
 *  Instantiate class:
 *      $client = new JsonApiClient( String $apiLogin, String $apiPassword, String $apiUrl, [int $curlConnectionTimout, int $curlTimeout]);
 *
 *  Define module (resource URI):
 *      $client->setCurrentModule(String $module); (example 'product');
 *
 *  Set page size:
 *      $client->setCurrentPagesize(Int $pageSize);
 *
 *  Fetch a single resource : $data = $client->findOne(String $resourceId);
 *
 *  Fetch multiple resources:
 *  1) Define parameters (Optional, if no parameters are provided, every resources for current module will be returned):
 *      Instantiate parameters group:
 *          $apiParemeters = new JsonApiParameterGroup();
 *
 *      Instantiate URL parameters:
 *          $apiParameters->addParam(new JsonApiParameter(String $name, String $value); // TODO For each parameter
 *
 *      A special class is provided: JsonApiFilter, which will transform $name into filter[$name] into final URL
 *      Pass the parameters to the client:
 *          $client->setCurrentParameters($apiParameters);
 *
 *  2a) Fetch every matching resource (one-shot):
 *      $data = $client->findAll();
 *
 *  2b) Fetch chunk by chunk (where chunk_size = page_size)
 *      while( $data = $client->find() ) { ... }
 *
 *  Define session duration (time to live for the token, in seconds)
 *      $client->setSessionValidity(int $validity);
 *
 *  Change curl configuration
 *      $client->setCurlOpt(curl_const $opt, mixed $value);
 *
 * Date: 16/03/17
 * Time: 10:30
 */

/**
 * Class JsonApiClientService
 * @package App\Service\Bdm
 */
class JsonApiClientService
{
    /** @var String */
    protected $module;
    /** @var  String */
    protected $sessionId;
    /** @var  integer */
    protected $sessionValidity;
    /** @var  integer */
    private $sessionExpires;
    /** @var String */
    protected $login;
    /** @var String */
    protected $password;
    /** @var String */
    protected $baseUrl;
    /** @var  JsonApiParameterDefinition */
    protected $parameters;
    /** @var  integer */
    protected $pageSize;
    /** @var  integer */
    private $pageOffset;
    /** @var String */
    private $currentURL;
    /** @var String */
    private $nextURL;
    /** @var String */
    private $lastURL;
    /** @var resource */
    private $curl;

    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * @var ParseService
     */
    protected $bdmParseService;

    protected $curlConnectionTimeout;
    protected $curlTimeout;

    /**
     * JsonApiClient constructor.
     * @param String $login
     * @param String $password
     * @param String $url
     * @param integer $curlConnectionTimeout
     * @param integer $curlTimeout
     */
    function __construct(
        LoggerInterface $logger,
        ParseService $bdmParseService,
        string $login = '',
        string $password = '',
        string $url = '',
        int $curlConnectionTimeout = 300,
        int $curlTimeout = 600
    )
    {
        $this->logger = $logger;
        $this->bdmParseService = $bdmParseService;
        $this->login = $login;
        $this->password = $password;
        $this->baseUrl = $url;
        $this->curlConnectionTimeout = $curlConnectionTimeout;
        $this->curlTimeout = $curlTimeout;

        $this->setCurrentPageSize(JsonClientService::DEFAULT_PAGE_SIZE);
        $this->setSessionValidity(JsonClientService::DEFAULT_SESSION_VALIDITY);

        $this->resetLinks();
    }

    /**
     * JsonApiClient destructor.
     */
    function __destruct()
    {
        if ($this->curl) {
            curl_close($this->curl);
        }
    }

    protected function getCurl()
    {
        if (!$this->curl) {
            $this->curl = curl_init();
            $this->setCurlOpt(CURLOPT_SSL_VERIFYPEER, false);
            $this->setCurlOpt(CURLOPT_RETURNTRANSFER, true);
            $this->setCurlOpt(CURLOPT_CONNECTTIMEOUT, $this->curlConnectionTimeout);
            $this->setCurlOpt(CURLOPT_TIMEOUT, $this->curlTimeout); //timeout in seconds
            set_time_limit(3000);// to infinity for example
        }

        return $this->curl;
    }

    /**
     * @param String $sessId
     */
    public function setSessionId($sessId)
    {
        $this->sessionId = $sessId;
    }

    /**
     * @return String
     */
    public function getSessionId()
    {
        if (!$this->isLogged()
            || (!is_null($this->sessionExpires) && time() > $this->sessionExpires)) {
            $this->sessionExpires = time() + $this->getSessionValidity();
            $this->login();
        }

        return $this->sessionId;
    }

    /**
     * @return int
     */
    public function getSessionValidity()
    {
        return $this->sessionValidity;
    }

    /**
     * @param int $sessionValidity
     */
    public function setSessionValidity($sessionValidity)
    {
        $this->sessionValidity = $sessionValidity;
    }

    /**
     * @return int
     */
    protected function getSessionExpires()
    {
        return $this->sessionExpires;
    }

    /**
     * @param string $module
     */
    public function setCurrentModule($module)
    {
        if (is_null($module) || empty($module) || $module === '') {
            throw new InvalidArgumentException(__METHOD__ . ' $module parameter cannot be null or unset.');
        }

        $this->resetLinks(); // If module changes, cursor has to be reset
        $this->module = $module;
    }

    /**
     * @return string
     */
    public function getCurrentModule()
    {
        return $this->module;
    }

    /**
     * @param JsonApiParameterDefinition $parameters
     * @param boolean $refresh
     */
    public function setCurrentParameters(JsonApiParameterDefinition $parameters, $refresh = true)
    {
        $this->parameters = $parameters;
        if ($refresh) {
            $this->resetLinks();
        } // If Parameters change, reset cursors
    }

    /**
     * @return mixed
     */
    public function getCurrentParameters()
    {
        return $this->parameters;
    }

    /**
     * @param int $pageSize
     */
    public function setCurrentPageSize($pageSize)
    {
        if ($pageSize < 0) {
            $this->pageSize = JsonClientService::DEFAULT_PAGE_SIZE;
        } else {
            if ($pageSize > JsonClientService::PAGE_SIZE_MAX) {
                $this->pageSize = JsonClientService::PAGE_SIZE_MAX;
            } else {
                $this->pageSize = $pageSize;
            }
        }
    }

    /**
     * @return int
     */
    public function getCurrentPageSize()
    {
        return $this->pageSize;
    }

    /**
     * @return String
     */
    public function getLastURL()
    {
        return $this->lastURL;
    }

    /**
     * @param string $option
     * @param string $value
     */
    public function setCurlOpt($option, $value)
    {
        curl_setopt($this->curl, $option, $value);
    }

    /**
     * Login to the Json API and store session ID inside class member
     */
    protected function login()
    {
        $parameters = new JsonApiParameterGroup();
        $parameters->addParam(new JsonApiParameter('login', $this->login));
        $parameters->addParam(new JsonApiParameter('password', $this->password));

        $response = $this->makeRequest($this->prepareRequest('login', $parameters, false, null), null, null);
        $this->sessionId = isset($response['sessionid']) ? $response['sessionid'] : null;
    }

    /**
     * Fetches the data, page per page, until everything has been loaded
     * Sends these data (current chunks) to the ParseService
     * If empty json response, it re-attempts to relaunch the import up to 5 times
     * @return array
     */
    public function findAll($offset = null, $module = null, $reDo = false, $lastLocalUpdate)
    {
        $i = 0;
        while ($out = $this->find($offset, $module, $lastLocalUpdate)) {

            //Set the last chunk's offset loaded
            $currentOffset = $out[LINKS_FIELD]['CurrentOffset'];
            $this->bdmParseService->getChunksetModule($out[DATA_FIELD]);

            //  todo ADDING THIS CONDITIONAL BREAK BELOW $j SETS THE MAX NUMBER OF CHUNKS ($j == number_of_chunks)
            //  $j++;
            //  if($j == 2) {
            //  break;
            //  }
        }
    }


    /**
     * Fetches one chunk of data, according to the currentPageSize, and returns it.
     * Returns false if there is no chunk to retrieve
     * @return mixed|bool
     */
    public function find($offset = null, $module = null, $lastLocalUpdate)
    {
        $parameters = $this->getCurrentParameters();
        if (!$parameters instanceof JsonApiParameterGroup) {
            $parameters = new JsonApiParameterGroup();
        }

        $pagination = new JsonApiPage();
        $pagination->setSize($this->getCurrentPageSize());

        if (is_null($this->currentURL)) {
            $pagination->setOffset($offset);
        } else {
            if ($this->currentURL !== $this->lastURL) {
                $nextOffset = $this->extractPageOffset($this->nextURL);
                $pagination->setOffset($nextOffset);
            } else {
                $this->resetLinks();

                return false;
            }
        }

        $parameters->setPagination($pagination);
        if ($offset) {
            $url = $this->relaunchImportFromOffset($module, $parameters, true, $offset);
        } else {
            $url = $this->prepareRequest($this->getCurrentModule(), $parameters, true, $lastLocalUpdate);
        }

        $offset = $parameters->getPaginationOffset();

        $jsonResponse = $this->makeRequest($url, $offset, $module, $lastLocalUpdate);

        $links = $jsonResponse[LINKS_FIELD];

        $this->currentURL = $links['self'];
        $this->nextURL = isset($links['next']) ? $links['next'] : $this->currentURL;
        $this->lastURL = isset($links['last']) ? $links['last'] : $this->currentURL;
        $this->pageOffset++;
        $jsonResponse[LINKS_FIELD]['CurrentOffset'] = $this->pageOffset;


        return $jsonResponse;
    }

    /**
     * Fetches a single resource by its ID
     * @param String $id
     * @param String|null $module
     * @return mixed
     */
    public function findOneByOid($oid, $module = null)
    {

        // Check module parameter
        if (is_null($module)) {
            $module = $this->getCurrentModule();
        }
        $request = $this->prepareRequest($module, new JsonApiResource($oid));
        $jsonResponse = $this->makeRequest($request, null, null);

        return $jsonResponse[DATA_FIELD];
    }


    /**
     * Fetches a single resource by its ID
     * @param String $id
     * @param String|null $module
     * @return mixed
     */
    public function findOneByRef($reference, $module = null)
    {

        // Check module parameter
        if (is_null($module)) {
            $module = $this->getCurrentModule();
        }
        $jsonResponse = $this->makeRequest(
            $this->prepareSpecRequest($module, new JsonApiResource('?filter[GC_REFERENCE]=' . $reference))
        );

        $this->sessionId = $jsonResponse['sessionid'];

        return $jsonResponse[DATA_FIELD];
    }

    /**
     * Makes the actual HTTP request
     * @param String $url
     * @return null|mixed
     * @throws InvalidArgumentException
     */
    protected function makeRequest($url, $offset, $module, $lastLocalUpdate = null)
    {
        try {
            $curl = $this->getCurl();
            // Set the url
            $this->setCurlOpt(CURLOPT_URL, $url);
            // Execute
            $response = curl_exec($curl);
            //$this->logger->alert($url);

        } catch (InvalidArgumentException $e) {
            throw $e;
        }

        $jsonResponse = $this->decode($response, $offset, $module, $lastLocalUpdate);

        return $jsonResponse;
    }

    /**
     * @param String $module
     * @param JsonApiParameterDefinition|null $parameters
     * @param boolean $token
     * @return string Final URL
     * Prepares the request <=> generates the final URL to target
     */
    private function prepareRequest($module, $parameters = null, $token = true, $lastLocalUpdate = null)
    {
        /* MODULE */
        $url = $this->baseUrl . '/' . $module . '/';

        /* PARAMETERS */
        $operator = '&';

        if ($parameters instanceof JsonApiParameterDefinition) {

            if ($parameters instanceof JsonApiResource) {

                // When fetching a resource, its id must be passed inside the URL and not in GET parameters
                $url .= $parameters->toURLParameter();

            } else {
                $url .= '?' . $parameters->toURLParameter();
                $operator = '&';
            }
        }

        /* TOKEN */
        if ($token) {
            if (strpos($url, '?') == 0) {
                //?filter[UPD_op]=>=&filter[UPD]=23/08/2011
                $urlFragment = '?' . 'filter[UPD_op]=>=&filter[UPD]=';
            } else {
                //&filter[UPD_op]=>=&filter[UPD]=23/08/2011
                $urlFragment = '&' . 'filter[UPD_op]=>=&filter[UPD]=';
            }
            $url .= $urlFragment . $lastLocalUpdate;
            //&sessionid=e32615f3adc33687c9f3240785833a1e
            $url .= $operator . 'sessionid=' . $this->getSessionId();

        }

        return $url;

    }

    /**
     * @param String $module
     * @param JsonApiParameterDefinition|null $parameters
     * @param boolean $token
     * @return string Final URL
     * Prepares the request <=> generates the final URL to target
     */
    private function prepareSpecRequest($module, $parameters = null, $token = true)
    {
        /* MODULE */
        $url = $this->baseUrl . '/' . $module . '/';

        /* PARAMETERS */
        $operator = '&';

        $url .= $parameters->toURLParameter();


        /* TOKEN */
        if ($token) {
            $url .= $operator . 'sessionid=' . $this->getSessionId();
        }

        return $url;

    }

    /**
     * @param String $module
     * @param JsonApiParameterDefinition|null $parameters
     * @param boolean $token
     * @return string Final URL
     * Prepares the request <=> generates the final URL to target
     */
    private function relaunchImportFromOffset($module, $parameters = null, $token = true, $offset)
    {
        /* MODULE */
        $url = $this->baseUrl . '/' . $module . '/';

        /* PARAMETERS */
        $operator = '?';

        if ($parameters instanceof JsonApiParameterDefinition) {
            if ($parameters instanceof JsonApiResource) {
                // When fetching a resource, its id must be passed inside the URL and not in GET parameters
                $url .= $parameters->setOffsetToURLParameter($offset);
            } else {
                $url .= '?' . $parameters->setOffsetToURLParameter($offset);
                $operator = '&';
            }
        }

        /* TOKEN */
        if ($token) {
            $url .= $operator . 'sessionid=' . $this->getSessionId();
        }


        return $url;

    }

    /**
     * Decode response from the API and validate its coherence
     * @param String $textResponse
     * @return array|mixed
     */
    private function decode($textResponse, $offset, $module, $lastLocalUpdate = null)
    {
        $jsonResponse = null;
        try {
            if (is_null($textResponse) || !is_string($textResponse)) {
                $type = gettype($textResponse);
                $this->logger->error(
                    sprintf(
                        __METHOD__ . "Parameter for " . __METHOD__ . " must be a string, << $type >> received at chunk ($this->pageOffset) and module ($this->module) :" .
                        "If << boolean >> received, it probably means that your connection attempt failed because your connection settings are empty.
                        Please take a look at your .ENV file (# Concerning Marketing Databases entry - Are BDM_DEV / BDM_PROD / BDM_PASSWORD / BDM_DEV set correctly ?)"
                    )
                );

                throw new \Exception(
                    __METHOD__ . "Parameter for " . __METHOD__ . " must be a string, " . PHP_EOL . PHP_EOL . "<< $type >> received" . PHP_EOL . PHP_EOL . "at chunk ($this->pageOffset) and module ($this->module) :" .
                    "If << boolean >> received, it probably means that your connection attempt failed because your connection settings are empty. " . PHP_EOL . PHP_EOL .
                    "Please take a look at your .ENV file (# Concerning Marketing Databases entry - Are BDM_DEV / BDM_PROD / BDM_PASSWORD / BDM_DEV set correctly ?)"
                );
            }
        } catch (\Exception $e) {
            echo 'Exception sent : ', $e->getMessage(), "\n";
            exit;
        }

        $jsonResponse = json_decode($textResponse, true);

        return $this->validateResponse($jsonResponse, $offset, $module, $lastLocalUpdate) ? $jsonResponse : [];
    }

    /**
     * Validate the JSON response
     * @param $jsonResponse
     * @return bool
     * @throws Exception
     */
    private function validateResponse($jsonResponse, $offset, $module, $lastLocalUpdate = null)
    {
        if (empty($jsonResponse)) {
            //This case might happen when the remote BDM server is jugging.
            $this->logger->error(sprintf(__METHOD__ . 'jsonResponse is empty - Relaunching current session'));
            //We wait for a while (10 minutes) before launching again the connection.
            sleep(600);
            $this->findAll($offset, $module, true, $lastLocalUpdate);
        }

        try {
            if (isset($jsonResponse['errors'])) {
                $errorArray = $jsonResponse['errors'];

                foreach ($errorArray as $error) {
                    if (isset($jsonResponse['links']['self'])) {
                        $url = ' with URL : ' . $jsonResponse['links']['self'];
                    } else {
                        $url = "";
                    }

                    if (intval($error['status']) !== JsonClientService::HTTP_STATUS_OK) {

                        $message = " ERROR: " . $error['status'] . ' => ' . $error['detail'] . $url;

                        $this->logger->error(sprintf(__METHOD__ . $message));
                        throw new \Exception(
                            $message . PHP_EOL . PHP_EOL .
                            "It usually means that your login/password are incorrect" . PHP_EOL .
                            "Please take a look at your BDM connection settings" . PHP_EOL . PHP_EOL .
                            "If your settings are correct, are you sure to look for an existing PRODUCT ?" . PHP_EOL .
                            "Else, have you checked that your MODULE number ($module) is an existing one ?" . PHP_EOL
                        );

                        return false;
                    }
                }
            }
        } catch (\Exception $e) {
            echo 'Exception sent : ', $e->getMessage(), "\n";
            exit;
        }

        return true;
    }

    /**
     * Check if login has already been made
     * @return bool
     */
    private function isLogged()
    {
        return !is_null($this->sessionId);
    }

    /**
     * If module or parameters should change, this will reset the cursor (ie. the navigation links)
     */
    private function resetLinks()
    {
        $this->currentURL = $this->nextURL = $this->lastURL = null;
        $this->pageOffset = 0;
    }

    /**
     * @param $url
     * @return int|null
     */
    private function extractPageOffset($url)
    {
        $exp = '/page\[offset\]=(\d+)&/';
        if (preg_match($exp, $url, $matches)) {
            return intval($matches[1]);
        }

        return 0;
    }
}

/**
 * Class JsonApiParameterDefinition
 * Common class to have a single type for every request parameter. Also defines the method that every sub-parameter should implement;
 */
abstract class JsonApiParameterDefinition
{
    public abstract function toURLParameter();

    public abstract function setOffsetToURLParameter($offset);
}

/**
 * Class JsonApiParameterGroup
 * Defines a group of parameters to make a query
 */
class JsonApiParameterGroup extends JsonApiParameterDefinition
{
    /** @var JsonApiParameter[] */
    protected $parameters = [];

    /** @var  JsonApiPage */
    protected $pagination;


    /**
     * @param JsonApiParameter $parameter
     */
    public function addParam(JsonApiParameter $parameter)
    {
        $this->parameters[] = $parameter;
    }

    /**
     * @param JsonApiParameter $pagination
     */
    public function getPaginationOffset(): ?string
    {
        return $this->pagination->getOffset();
    }

    /**
     * @param JsonApiFilter $filter
     */
    public function addFilter(JsonApiFilter $filter)
    {
        $this->parameters[] = $filter;
    }

    /**
     * @param JsonApiPage $page
     */
    public function setPagination(JsonApiPage $page)
    {
        $this->pagination = $page;
    }

    /**
     * @return string
     */
    public function toURLParameter()
    {
        $out = '';
        foreach ($this->parameters as $parameter) {
            $out .= $parameter->toURLParameter() . '&';
        }

        if (!is_null($this->pagination)) {
            $out .= $this->pagination->toURLParameter() . '&';
        }

        return rtrim($out, '&');
    }

    public function setOffsetToURLParameter($offset)
    {
        $out = '';
        foreach ($this->parameters as $parameter) {
            $out .= $parameter->toURLParameter() . '&';
        }

        if (!is_null($this->pagination)) {
            $out .= $this->pagination->toURLParameter() . '&';
        }

        return rtrim($out, '&');
    }


}

/**
 * Class JsonApiParameter
 * Defines a single query parameter by its name and its value
 */
class JsonApiParameter extends JsonApiParameterDefinition
{
    protected $field;
    protected $value;

    /**
     * JsonApiParameter constructor.
     * @param $field
     * @param $value
     */
    function __construct($field, $value)
    {
        $this->field = $field;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function toURLParameter()
    {
        return $this->field . '=' . urlencode($this->value);
    }

    /**
     * @return string
     */
    public function setOffsetToURLParameter($offset)
    {
        return $this->field . '=' . urlencode($this->value);
    }
}

/**
 * Class JsonApiFilter
 *  Special class which will transform parameter's name into filter[name] in the final URL automatically
 */
class JsonApiFilter extends JsonApiParameter
{
    /**
     * @return string
     */
    public function toURLParameter()
    {
        return 'filter[' . $this->field . ']=' . urlencode($this->value);
    }

    /**
     * @return string
     */
    public function setOffsetToURLParameter($offset)
    {
        return 'filter[' . $this->field . ']=' . urlencode($this->value);
    }
}

/**
 * Class JsonApiPage
 *  Special class which will transform parameter's name into page[name] in the final URL automatically
 */
class JsonApiPage extends JsonApiParameter
{
    /** @var  integer */
    private $offset;

    /** @var  integer */
    private $size;

    /**
     * JsonApiPage constructor.
     * @param int $pageSize
     * @param int $pageOffset
     */
    function __construct($pageSize = JsonClientService::DEFAULT_PAGE_SIZE, $pageOffset = 0)
    {

        $this->setSize($pageSize);
        $this->setOffset($pageOffset);

        parent::__construct(null, null);
    }

    /**
     * @param int $offset
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param int $offset
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
    }

    /**
     * @param int $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * @return string
     */
    public function toURLParameter()
    {
        return 'page[size]=' . urlencode($this->size) . '&page[offset]=' . urlencode($this->offset);
    }

    /**
     * @return string
     */
    public function setOffsetToURLParameter($offset)
    {

        $this->offset = $offset;

        return 'page[size]=' . urlencode($this->size) . '&page[offset]=' . urlencode($this->offset);
    }
}

/**
 * Class JsonApiResource
 *  Special class to fetch one single resource, only the value is needed, which will be injected in the final URL
 */
class JsonApiResource extends JsonApiParameter
{
    /**
     * JsonApiResource constructor.
     * @param $value
     */
    function __construct($value)
    {
        parent::__construct(null, $value);
    }

    /**
     * @return mixed
     */
    public function toURLParameter()
    {
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function setOffsetToURLParameter($offset = null)
    {
        return $this->value;
    }
}

