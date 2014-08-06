<?php
namespace Ifresco\ClientBundle\Component\Alfresco\REST;

use Ifresco\ClientBundle\Component\Alfresco\BaseObject;
use Ifresco\ClientBundle\Component\Alfresco\NamespaceMap;
use Ifresco\ClientBundle\Component\Alfresco\REST\HTTP\RESTClient;

class RESTGoogleDocs extends BaseObject {
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
    
    public function AuthUrl($state) {
        $result = array();

        
        $this->_restClient->createRequest($this->_connectionUrl."/googledocs/authurl?state=$state&override=true","GET");
        $this->_restClient->sendRequest();

        $result = $this->workWithResult($this->_restClient->getResponse(),"json");    

        return $result;    
    }
    
    public function CompleteAuth($token) {
    	$result = array();

    	$this->_restClient->createRequest($this->_connectionUrl."/googledocs/completeauth?access_token=$token","GET");
    	$this->_restClient->sendRequest();
    
    	$result = $this->workWithResult($this->_restClient->getResponse(),"");
    
    	return $result;
    }
    
    public function uploadContent($nodeId) {
    	$result = array();
    	$nodeRef = "workspace://SpacesStore/".$nodeId;
    	$this->_restClient->createRequest($this->_connectionUrl."/googledocs/uploadContent?nodeRef=$nodeRef","GET");
    	$this->_restClient->sendRequest();
    
    	$result = $this->workWithResult($this->_restClient->getResponse(),"json");
    
    	return $result;
    }
    
    public function saveContent($nodeId, $description="", $majorVersion=false, $override=false) {
    	$result = array();
    	$nodeRef = "workspace://SpacesStore/".$nodeId;
    	/*description: ""
    	 majorVersion: "false"
    	nodeRef: "workspace://SpacesStore/bf4ef067-6155-4022-bec9-408604162122"
    	override: "false"*/
    	$postArr = json_encode(array("description"=>$description,"majorVersion"=>$majorVersion,"nodeRef"=>$nodeRef,"override"=>$override));

    	$this->_restClient->createRequest($this->_connectionUrl."/googledocs/saveContent","POST",$postArr,"json");
    	$this->_restClient->sendRequest();

    	$result = $this->workWithResult($this->_restClient->getResponse(),"json");
    
    	return $result;
    }
    
    public function discardContent($nodeId) {
    	$result = array();
    	$nodeRef = "workspace://SpacesStore/".$nodeId;
    	/*description: ""
    	 majorVersion: "false"
    	nodeRef: "workspace://SpacesStore/bf4ef067-6155-4022-bec9-408604162122"
    	override: "false"*/
    	$postArr = json_encode(array("nodeRef"=>$nodeRef));
    	$this->_restClient->createRequest($this->_connectionUrl."/googledocs/discardContent","POST",$postArr,"json");
    	$this->_restClient->sendRequest();
    
    	$result = $this->workWithResult($this->_restClient->getResponse(),"json");
    
    	return $result;
    }
    
    private function setRESTClient() {
        if ($this->_restClient == null) {
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
