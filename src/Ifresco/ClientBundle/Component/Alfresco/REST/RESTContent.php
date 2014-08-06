<?php
namespace Ifresco\ClientBundle\Component\Alfresco\REST;

use Ifresco\ClientBundle\Component\Alfresco\BaseObject;
use Ifresco\ClientBundle\Component\Alfresco\NamespaceMap;
use Ifresco\ClientBundle\Component\Alfresco\REST\HTTP\RESTClient;

class RESTContent extends BaseObject {
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
    
    public function GetWebPreview($nodeId, $versioned = false) {
        $result = array();

        if($versioned)
            $this->_restClient->createRequest($this->_connectionUrl."/api/node/workspace/version2Store/$nodeId/content/thumbnails/webpreview?c=force","GET");
        else
            $this->_restClient->createRequest($this->_connectionUrl."/api/node/workspace/SpacesStore/$nodeId/content/thumbnails/webpreview?c=force","GET");
        $this->_restClient->sendRequest();

        $result = $this->workWithResult($this->_restClient->getResponse(),"");    

        return $result;    
    }

    public function GetWebImagePreview($nodeId,$force=true,$type="imgpreview") {
        $result = array();

        switch ($type) {
            case "imgpreview":
            case "medium":
            case "doclib":
                    
            break;
            default:
                $type = "imgpreview";
        }
        
        if ($force)
            $append = "?c=force";
        else
            $append = "?c=queue&ph=true";

        $this->_restClient->createRequest($this->_connectionUrl."/api/node/workspace/SpacesStore/$nodeId/content/thumbnails/".$type.$append,"GET");
        $this->_restClient->sendRequest();

        $result = $this->workWithResult($this->_restClient->getResponse(),"");

        return $result;
    }

    public function DeleteNode($nodeId,$format="json") {
        $result = array();

        $this->_restClient->createRequest($this->_connectionUrl."/slingshot/doclib/action/file/node/workspace/SpacesStore/$nodeId?format=$format","DELETE");  

        $this->_restClient->sendRequest();

        $result = $this->workWithResult($this->_restClient->getResponse(),$format);    

        return $result;
    }
    
    public function DeleteSpace($nodeId,$format="json") {
        $result = array();

        $this->_restClient->createRequest($this->_connectionUrl."/slingshot/doclib/action/folder/node/workspace/SpacesStore/$nodeId?format=$format","DELETE");  
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
                return $result;
                break;
        }
        return $result;
    }
}

?>
