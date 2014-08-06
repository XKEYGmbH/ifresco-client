<?php
namespace Ifresco\ClientBundle\Component\Alfresco\REST;

use Ifresco\ClientBundle\Component\Alfresco\BaseObject;
use Ifresco\ClientBundle\Component\Alfresco\NamespaceMap;
use Ifresco\ClientBundle\Component\Alfresco\Repository;
use Ifresco\ClientBundle\Component\Alfresco\REST\HTTP\RESTClient;
use Ifresco\ClientBundle\Component\Alfresco\Session;
use Ifresco\ClientBundle\Component\Alfresco\SpacesStore;

class RESTGroups extends BaseObject {
    private $_restClient = null;
    
    private $_repository;
    private $_session;
    private $_store;
    private $_ticket;
    private $_connectionUrl;
    
    public $namespaceMap;

    /**
     * @param Repository $repository
     * @param SpacesStore $store
     * @param Session $session
     */
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
    
    
    public function GetGroups($shortNameFilter="",$sortBy="displayName",$maxResults=250,$zone="APP.DEFAULT",$format="json") {
        $result = array();
        $this->_restClient->createRequest($this->_connectionUrl."/api/groups?shortNameFilter=$shortNameFilter&zone=$zone&maxResults=$maxResults&sortBy=$sortBy&format=$format","GET");

        $this->_restClient->sendRequest();

        $result = $this->workWithResult($this->_restClient->getResponse(),$format);
        return $result;
    }
    
    /*public function UpdatePerson($userName,$fields=array(),$format="json") {
    	
    	$result = array();
        if ($userName != null) {
            $postArr = json_encode($fields);
            $this->_restClient->createRequest($this->_connectionUrl."/api/people/$userName","PUT",$postArr,"json");
            $this->_restClient->sendRequest();

            $result = $this->workWithResult($this->_restClient->getResponse(),$format);    
        }
        return $result;
    }*/

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
