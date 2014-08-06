<?php
namespace Ifresco\ClientBundle\Component\Alfresco\REST;

use Ifresco\ClientBundle\Component\Alfresco\BaseObject;
use Ifresco\ClientBundle\Component\Alfresco\NamespaceMap;
use Ifresco\ClientBundle\Component\Alfresco\REST\HTTP\RESTClient;

class RESTAspects extends BaseObject {
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
    
    public function GetAllAspects($namespace="",$format="json") {
        $result = array();
        $this->_restClient->createRequest($this->_connectionUrl."/api/classes?r=true&nsp=$namespace&format=$format","GET");

        $this->_restClient->sendRequest();

        
        $result = $this->workWithResult($this->_restClient->getResponse(),$format);  
        $newResult = array();

        if (count($result) > 0) {
            foreach($result as $Key => $Object) {
                if ($Object->isAspect == true) {
                    $newResult[] = $Object;
                }    
            } 
            $result = $newResult;
        } 

        return $result;
    } 
    
    public function GetAspect($name="",$prefix=":",$format="json") {
        $result = array();
        $split = explode($prefix,$name);
        if (count($split) > 1) {
            $namespace = $split[0];  
            $name = $split[1];
            $this->_restClient->createRequest($this->_connectionUrl."/api/classes?nsp=$namespace&n=$name&format=$format","GET");

            $this->_restClient->sendRequest();

            
            $result = $this->workWithResult($this->_restClient->getResponse(),$format);
            if (is_array($result) && count($result) == 1) {
                $result = $result[0];    
            }
             
        }

        return $result;
    }  
    
    public function GetNodeAspects($nodeId,$format="json") {
        $result = array();
        $this->_restClient->createRequest($this->_connectionUrl."/slingshot/doclib/aspects/node/workspace/SpacesStore/$nodeId?format=$format","GET");

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
                if (!$result) {
                    
                    $resultGet = str_replace("\r\n","",$resultGet);
                    $resultGet = str_replace("\t","",$resultGet);                
                    $resultGet = preg_replace("/([a-zA-Z0-9\s\.\!\?]+)/is","\"$1\"",$resultGet);      

                    $result = json_decode($resultGet); 
                }
            break;
            default:
                
                break;
        }
        return $result;
    }
}

?>
