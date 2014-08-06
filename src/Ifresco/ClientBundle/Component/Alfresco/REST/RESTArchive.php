<?php
namespace Ifresco\ClientBundle\Component\Alfresco\REST;

use Ifresco\ClientBundle\Component\Alfresco\BaseObject;
use Ifresco\ClientBundle\Component\Alfresco\NamespaceMap;
use Ifresco\ClientBundle\Component\Alfresco\REST\HTTP\RESTClient;

class RESTArchive extends BaseObject {
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
    
    public function GetArchiveWorkspace($maxItems=51,$Space="workspace",$Store="SpacesStore",$format="json") {
        $result = array();

        $this->_restClient->createRequest($this->_connectionUrl."/api/archive/$Space/$Store?maxItems=$maxItems&format=$format","GET");

        $this->_restClient->sendRequest();

        $result = $this->workWithResult($this->_restClient->getResponse(),$format);    

        return $result;
    }  
    
    public function DeleteNode($nodeId,$format="json") {
    	$result = array();
    
    	$this->_restClient->createRequest($this->_connectionUrl."/api/archive/archive/SpacesStore/$nodeId?format=$format","DELETE");
    
    	$this->_restClient->sendRequest();
    
    	$result = $this->workWithResult($this->_restClient->getResponse(),$format);
    
    	return $result;
    }
    
    public function RestoreNode($nodeId,$format="json") {
    	$result = array();
    
    	$this->_restClient->createRequest($this->_connectionUrl."/api/archive/archive/SpacesStore/$nodeId?format=$format","PUT");
    
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
