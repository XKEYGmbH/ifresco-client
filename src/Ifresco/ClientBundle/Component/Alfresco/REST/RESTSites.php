<?php
namespace Ifresco\ClientBundle\Component\Alfresco\REST;

use Ifresco\ClientBundle\Component\Alfresco\BaseObject;
use Ifresco\ClientBundle\Component\Alfresco\NamespaceMap;
use Ifresco\ClientBundle\Component\Alfresco\REST\HTTP\RESTClient;

class RESTSites extends BaseObject {
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
    
    /*public function GetSites($userName="",$size=10,$format="json") {
        $result = array();
        $userName = rawurlencode($userName);
        $size = rawurlencode($size);
        $position = rawurlencode($position);
        
        $this->_restClient->createRequest($this->_connectionUrl."/api/people/$userName/sites?size=$size&format=$format","GET");

        $this->_restClient->sendRequest();

        $result = $this->workWithResult($this->_restClient->getResponse(),$format);    

        return $result;
    }  */
    
    public function GetSites($search="",$size=25,$format="json") {
    	$result = array();
    	$search = rawurlencode($search);
    	$size = rawurlencode($size);

    	$this->_restClient->createRequest($this->_connectionUrl."/api/sites?roles=user&size=$size&nf=$search&format=$format","GET");
    
    	$this->_restClient->sendRequest();
    
    	$result = $this->workWithResult($this->_restClient->getResponse(),$format);
    
    	return $result;
    }
    
    public function GetSiteTree($site="",$children="true",$perms="false",$max="-1",$format="json") {
    	$result = array();
    	$site = rawurlencode($site);
    
    	$this->_restClient->createRequest($this->_connectionUrl."/slingshot/doclib/treenode/site/$site/documentLibrary?perms=$perms&children=$children&max=$max&format=$format","GET");
    
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
