<?php
namespace Ifresco\ClientBundle\Component\Alfresco\REST;

use Ifresco\ClientBundle\Component\Alfresco\BaseObject;
use Ifresco\ClientBundle\Component\Alfresco\NamespaceMap;
use Ifresco\ClientBundle\Component\Alfresco\REST\HTTP\RESTClient;

class RESTDashlets extends BaseObject {
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

    
    public function GetMySites($userName,$size=100,$format="json") {
    	$result = array();
    	$size = rawurlencode($size);

    	$this->_restClient->createRequest($this->_connectionUrl."/api/people/$userName/sites?roles=user&size=$size&format=$format","GET");
    
    	$this->_restClient->sendRequest();
    
    	$result = $this->workWithResult($this->_restClient->getResponse(),$format);
    
    	return $result;
    }
    
    public function GetMyDocuments($filter="recentlyModifiedByMe",$max=50,$format="json") {
    	$result = array();
    	$max = rawurlencode($max);
    	$filter = rawurlencode($filter);
    
    	$this->_restClient->createRequest($this->_connectionUrl."/slingshot/doclib/doclist/documents/node/alfresco/company/home?max=$max&filter=$filter&format=$format","GET");
    
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
    
    private function workWithResult($result,$format) {
        switch ($format) {
            case "json":
                $result = json_decode($result);    
            break;
            default:
                
                break;
        }
        return $result;
    }
}

?>
