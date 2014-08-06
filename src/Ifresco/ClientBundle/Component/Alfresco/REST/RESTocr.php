<?php
namespace Ifresco\ClientBundle\Component\Alfresco\REST;

use Ifresco\ClientBundle\Component\Alfresco\BaseObject;
use Ifresco\ClientBundle\Component\Alfresco\NamespaceMap;
use Ifresco\ClientBundle\Component\Alfresco\REST\HTTP\RESTClient;

class RESTocr extends BaseObject {
    private $_restClient = null;

    private $_repository;
    private $_session;
    private $_store;
    private $_ticket;
    private $_connectionUrl;

    public $namespaceMap;

    public function __construct($repository, $store, $session) {
        $this->_repository = $repository;
        $this->_store = $store;
        $this->_session = $session;
        $this->_ticket = $this->_session->getTicket();

        $this->namespaceMap = new NamespaceMap();

        $this->_connectionUrl = $this->_repository->connectionUrl;
        $this->_connectionUrl = str_replace("soapapi","service",$this->_connectionUrl); $this->_connectionUrl = str_replace("api","service",$this->_connectionUrl);
        $this->setRESTClient();
    }

    public function fetchConfig($format="json") {

        $this->_restClient->createRequest($this->_connectionUrl."/ifresco/autoocr/admin?format=$format","GET");
        $this->_restClient->sendRequest();

        $result = $this->workWithResult($this->_restClient->getResponse(),$format);

        return $result;
    }

    public function fetchSettings($format="json") {

        $this->_restClient->createRequest($this->_connectionUrl."/ifresco/autoocr/client/settings?format=$format","GET");
        $this->_restClient->sendRequest();

        $result = $this->workWithResult($this->_restClient->getResponse(),$format);

        return $result;
    }

    public function saveConfig($data = array(), $format="json") {

        $postArr = json_encode($data);

        $this->_restClient->createRequest($this->_connectionUrl."/ifresco/autoocr/admin?format=$format","POST",$postArr,"json");
        $this->_restClient->sendRequest();

        $result = $this->workWithResult($this->_restClient->getResponse(),$format);

        return $result;
    }

    public function getStatus($format="json") {

        $this->_restClient->createRequest($this->_connectionUrl."/ifresco/autoocr/client/serverInfos?format=$format","GET");
        $this->_restClient->sendRequest();

        $result = $this->workWithResult($this->_restClient->getResponse(),$format);

        return $result;
    }

    public function testConnection($data = array(), $format="json") {

        $endpoint = isset($data->endpoint)?$data->endpoint:'';
        $username = isset($data->username)?$data->username:'';
        $password = isset($data->password)?$data->password:'';

        $this->_restClient->createRequest($this->_connectionUrl."/ifresco/autoocr/connectiontest?endpoint=$endpoint&username=$username&password=$password&format=$format","GET");
        $this->_restClient->sendRequest();

        $result = $this->workWithResult($this->_restClient->getResponse(),$format);

        return $result;
    }

    public function testAPI($data = array(), $format="json") {

        $endpoint = isset($data->endpoint)?$data->endpoint:'';
        $username = isset($data->username)?$data->username:'';
        $password = isset($data->password)?$data->password:'';
        $apikey = isset($data->apiKey)?$data->apiKey:'';

        $this->_restClient->createRequest($this->_connectionUrl."/ifresco/autoocr/apikeytest?endpoint=$endpoint&username=$username&password=$password&apikey=$apikey&format=$format","GET");
        $this->_restClient->sendRequest();

        $result = $this->workWithResult($this->_restClient->getResponse(),$format);

        return $result;
    }

    public function jobsList($type = 'CREATED', $format="json") {

        $this->_restClient->createRequest($this->_connectionUrl."/ifresco/autoocr/client/getAllJobs?state=$type&format=$format","GET");
        $this->_restClient->sendRequest();

        $result = $this->workWithResult($this->_restClient->getResponse(),$format);

        return $result;
    }

    public function getAvailableTransformations($mimeType = '', $format="json") {

        $this->_restClient->createRequest($this->_connectionUrl."/ifresco/autoocr/mimetypes?transformFrom=$mimeType&format=$format","GET");
        $this->_restClient->sendRequest();

        $result = $this->workWithResult($this->_restClient->getResponse(),$format);

        return $result;
    }

    public function doTransformation($params = array(), $format="json") {

        $params = json_encode($params);

        $this->_restClient->createRequest($this->_connectionUrl."/ifresco/autoocr/doTransformation","POST",$params,"json");
        $this->_restClient->sendRequest();

        $result = $this->workWithResult($this->_restClient->getResponse(),$format);

        return $result;
    }

    private function setRESTClient() {
        if ($this->_restClient == null) {
            //$this->_restClient = new RESTClient("",$this->_repository->getUsername(),$this->_repository->getPassword());    
            $this->_restClient = new RESTClient($this->_session->getTicket(),$this->_session->getLanguage());
        }
    }

    private function workWithResult($resultGet,$format) {
        switch ($format) {
            case "json":
                $result = json_decode($resultGet);
                break;
            default:

                break;
        }
        return $result;
    }
}

?>
