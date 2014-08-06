<?php
namespace Ifresco\ClientBundle\Component\Alfresco\REST;

use Ifresco\ClientBundle\Component\Alfresco\BaseObject;
use Ifresco\ClientBundle\Component\Alfresco\NamespaceMap;
use Ifresco\ClientBundle\Component\Alfresco\REST\HTTP\RESTClient;

class RESTComments extends BaseObject {
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
    
    public function GetCommentsForNode($nodeId="",$format="json") {
        $result = array();
        $this->_restClient->createRequest($this->_connectionUrl."/api/node/workspace/SpacesStore/$nodeId/comments?format=$format","GET");

        $this->_restClient->sendRequest();

        $result = $this->workWithResult($this->_restClient->getResponse(),$format);    

        return $result;
    }  
    
    public function AddComment($nodeId,$content,$format="json") {

        $result = array();
        if ($nodeId != null && $content != null) {
            $postArr = json_encode(array("content"=>$content));
            $this->_restClient->createRequest($this->_connectionUrl."/api/node/workspace/SpacesStore/$nodeId/comments","POST",$postArr,"json");
            $this->_restClient->sendRequest();

            $result = $this->workWithResult($this->_restClient->getResponse(),$format);    
        }
        return $result;
    }
    
    public function UpdateComment($commentId,$content,$format="json") {

        $result = array();
        if ($commentId != null && $content != null) {
            $postArr = json_encode(array("content"=>$content));
            $this->_restClient->createRequest($this->_connectionUrl."/api/comment/node/workspace/SpacesStore/$commentId","PUT",$postArr,"json");
            $this->_restClient->sendRequest();

            $result = $this->workWithResult($this->_restClient->getResponse(),$format);    
        }
        return $result;
    }
    
    public function DeleteComment($commentId="",$format="json") {
        $result = array();
        $this->_restClient->createRequest($this->_connectionUrl."/api/node/workspace/SpacesStore/$commentId/comments?format=$format","DELETE");

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
