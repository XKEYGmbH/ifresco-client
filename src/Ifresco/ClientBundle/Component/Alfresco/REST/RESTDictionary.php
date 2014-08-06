<?php
namespace Ifresco\ClientBundle\Component\Alfresco\REST;

use Ifresco\ClientBundle\Component\Alfresco\BaseObject;
use Ifresco\ClientBundle\Component\Alfresco\NamespaceMap;
use Ifresco\ClientBundle\Component\Alfresco\REST\HTTP\RESTClient;

class RESTDictionary extends BaseObject {
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
    
    public function GetClassDefinitions($className, $format="json")
    {
        if (!$this->namespaceMap->isShortName($className))
            $className = $this->namespaceMap->getShortName($className, "_", false);

        $result = array();
        if ($className !=null) {
            $this->_restClient->createRequest($this->_connectionUrl."/api/classes/$className?format=$format","GET");
            $this->_restClient->sendRequest();

            $result = $this->workWithResult($this->_restClient->getResponse(),$format);    
        }

        return $result;
    } 
    
    public function GetSubClassDefinitions($className,$recursive="true",$format="json") {

        if (!$this->namespaceMap->isShortName($className))
            $className = $this->namespaceMap->getShortName($className,"_",false);

        $result = array();
        if ($className !=null) {

            $this->_restClient->createRequest($this->_connectionUrl."/api/classes/$className/subclasses?r=$recursive&format=$format","GET");
            $this->_restClient->sendRequest();

            $result = $this->workWithResult($this->_restClient->getResponse(),$format);    
        }
        return $result;
    }  
    
    public function GetClassAssociations($className,$format="json") {

        if (!$this->namespaceMap->isShortName($className))
            $className = $this->namespaceMap->getShortName($className,"_",false);

        $result = array();
        if ($className !=null) {

            $this->_restClient->createRequest($this->_connectionUrl."/api/classes/$className/associations?format=$format","GET");
            $this->_restClient->sendRequest();

            $result = $this->workWithResult($this->_restClient->getResponse(),$format);    
        }
        return $result;
    }  
    
    public function GetClassAssociation($className,$assocName,$format="json") {

        if (!$this->namespaceMap->isShortName($className))
            $className = $this->namespaceMap->getShortName($className,"_",false);

        $result = array();
        if ($className !=null) {

            $this->_restClient->createRequest($this->_connectionUrl."/api/classes/$className/association/$assocName?format=$format","GET");
            $this->_restClient->sendRequest();

            $result = $this->workWithResult($this->_restClient->getResponse(),$format);    
        }
        return $result;
    }

    /**
     * @param $className
     * @param string $format
     * @return array|mixed
     */
    public function GetClassProperties($className, $format="json") {

        if (!$this->namespaceMap->isShortName($className))
            $className = $this->namespaceMap->getShortName($className,"_",false);

        $result = array();
        if ($className !=null) {

            $this->_restClient->createRequest($this->_connectionUrl."/api/classes/$className/properties?format=$format","GET");
            $this->_restClient->sendRequest();

            $result = $this->workWithResult($this->_restClient->getResponse(), $format);
        }
        return $result;
    }  
    
    public function GetAllProperties($format="json") {
        $result = array();

        $this->_restClient->createRequest($this->_connectionUrl."/api/properties?nsp=*&n=","GET");
        $this->_restClient->sendRequest();

        $result = $this->workWithResult($this->_restClient->getResponse(),$format);    

        return $result;
    } 
    
    public function GetPropertyByName($propertyName,$format="json") {
        if (!$this->namespaceMap->isShortName($propertyName))
            $propertyName = $this->namespaceMap->getShortName($propertyName,":",false);
            
        $this->_restClient->createRequest($this->_connectionUrl."/api/properties?nsp=*&name=$propertyName&format=$format","GET");
        $this->_restClient->sendRequest();

        $result = $this->workWithResult($this->_restClient->getResponse(),$format);       
    }
    
    public function GetClassProperty($className,$propertyName,$format="json") {

        if (!$this->namespaceMap->isShortName($className))
            $className = $this->namespaceMap->getShortName($className,"_",false);
            
        if (!$this->namespaceMap->isShortName($propertyName))
            $propertyName = $this->namespaceMap->getShortName($propertyName,"_",false);

        $result = array();
        if ($className !=null) {

            $this->_restClient->createRequest($this->_connectionUrl."/api/classes/$className/property/$propertyName?format=$format","GET");
            $this->_restClient->sendRequest();

            $result = $this->workWithResult($this->_restClient->getResponse(),$format);    
        }
        return $result;
    }  
    
    public function GetClassObjectUrl($url,$format="json") {

        $result = array();
        if ($url != null) {

            $this->_restClient->createRequest($this->_connectionUrl."$url?format=$format","GET");
            $this->_restClient->sendRequest();

            $result = $this->workWithResult($this->_restClient->getResponse(),$format);    
        }
        return $result;
    }
    
    public function GetFormdefinitions($nodeRef,$format="json") {

        $result = array();
        if ($nodeRef != null) {
            $postArr = json_encode(array("itemKind"=>"node","itemId"=>$nodeRef));
            $this->_restClient->createRequest($this->_connectionUrl."/api/formdefinitions","POST",$postArr,"json");
            $this->_restClient->sendRequest();
            

            $result = $this->workWithResult($this->_restClient->getResponse(),$format);
  
        }
        return $result;
    }
    
    public function GetClassForm($class,$format="json") {

        $result = array();
        if ($class != null) {
            //$class = str_replace(":","_",$class);
            $postArr = json_encode(array("itemKind"=>"type","itemId"=>$class));
            $this->_restClient->createRequest($this->_connectionUrl."/api/formdefinitions","POST",$postArr,"json");
            $this->_restClient->sendRequest();
            

            $result = $this->workWithResult($this->_restClient->getResponse(),$format);
  
        }

        return $result;
    }
    
    public function GetClasses($format = "json")
    {
        $this->_restClient->createRequest($this->_connectionUrl . "/api/classes?format=$format", "GET");
        $this->_restClient->sendRequest();
        return $this->workWithResult($this->_restClient->getResponse(), $format);
    }
    
    public function SpecifyType($nodeId, $type, $format="json")
    {
        $postArr = json_encode(array("type" => $type));
        $this->_restClient->createRequest(
            $this->_connectionUrl . "/slingshot/doclib/type/node/workspace/SpacesStore/$nodeId",
            "POST",
            $postArr,
            "json"
        );

        $this->_restClient->sendRequest();
        return $this->workWithResult($this->_restClient->getResponse(), $format);
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

                // ALFRESO IS DOING SOME **** HERE SO I HAVE TO FIX IT:
                if (!$result) {
                      
                    $resultGet = str_replace("\/","/",$resultGet);
                    $resultGet = str_replace("\n\"","\"",$resultGet);
                    $resultGet = str_replace("\t"," ",$resultGet);
                    $resultGet = preg_replace("/\"expression\":\"(.*?)\",/is","\"expression\":\"\",",$resultGet);
                    $resultGet = preg_replace("/\"prop_cm_content\": \"(.*?)\",/is","",$resultGet);
                    $resultGet = preg_replace("/\"prop_cm_template\": \"(.*?)\",/is","",$resultGet);

                    $resultGet = utf8_encode($resultGet);
                    $result = json_decode($resultGet);
                }
            break;
            default:
                
                break;
        }
        return $result;
    }
    
    function json_form_decode($json)
    {
        // Author: walidator.info 2009
        $comment = false;
        $out = '$x=';
       
        for ($i=0; $i<strlen($json); $i++)
        {
            if (!$comment)
            {
                if ($json[$i] == '{')        $out .= ' array(';
                else if ($json[$i] == '}')    $out .= ')';
                else if ($json[$i] == ':')    $out .= '=>';
                else                         $out .= $json[$i];           
            }
            else $out .= $json[$i];
            if ($json[$i] == '"')    $comment = !$comment;
        }
        eval($out . ';');
        return $x;//TODO : WTF?
    } 
}
