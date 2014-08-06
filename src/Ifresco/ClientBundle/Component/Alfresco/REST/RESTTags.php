<?php
namespace Ifresco\ClientBundle\Component\Alfresco\REST;

use Ifresco\ClientBundle\Component\Alfresco\BaseObject;
use Ifresco\ClientBundle\Component\Alfresco\NamespaceMap;
use Ifresco\ClientBundle\Component\Alfresco\REST\HTTP\RESTClient;

class RESTTags extends BaseObject {
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
    
    public function GetNodeForTag($tag="",$format="json") {
        $result = array();
        $tag = rawurlencode($tag);
        $this->_restClient->createRequest($this->_connectionUrl."/api/tags/workspace/SpacesStore/$tag/nodes?format=$format","GET");

        $this->_restClient->sendRequest();

        $result = $this->workWithResult($this->_restClient->getResponse(),$format);    

        return $result;
    }  
    
    public function GetNodeTags($id="",$format="json") {
        $result = array();

        
        $this->_restClient->createRequest($this->_connectionUrl."/api/node/workspace/SpacesStore/$id/tags?format=$format","GET");

        $this->_restClient->sendRequest();

        $result = $this->workWithResult($this->_restClient->getResponse(),$format);    

        return $result;
    }  
    
    
    public function AddNodeTags($id,$tags=array(),$format="json") {
        $result = array();

        $postArr = json_encode($tags);                             
        $this->_restClient->createRequest($this->_connectionUrl."/api/node/workspace/SpacesStore/$id/tags?format=$format","POST",$postArr,"json");

        $this->_restClient->sendRequest();

        $result = $this->workWithResult($this->_restClient->getResponse(),$format);    

        return $result;
    }
    
    public function GetSiteTags($SiteName="",$format="json") {
        $result = array();
        $SiteName = rawurlencode($SiteName);
        
        $this->_restClient->createRequest($this->_connectionUrl."/api/tagscopes/site/$SiteName/tags?format=$format","GET");

        $this->_restClient->sendRequest();

        $result = $this->workWithResult($this->_restClient->getResponse(),$format);    

        return $result;
    }  
    
    public function GetSiteContainerTags($SiteName="",$container="",$format="json") {
        $result = array();
        $SiteName = rawurlencode($SiteName);
        
        $this->_restClient->createRequest($this->_connectionUrl."/api/tagscopes/site/$SiteName/$container/tags?format=$format","GET");

        $this->_restClient->sendRequest();

        $result = $this->workWithResult($this->_restClient->getResponse(),$format);    

        return $result;
    }  
    
    public function GetAllTags($filter="",$details=false,$format="json") {
        $result = array();

        $this->_restClient->createRequest($this->_connectionUrl."/api/tags/workspace/SpacesStore?tf=$filter".($details == true ? '&details=true' : '')."&format=$format","GET");
        $this->_restClient->sendRequest();

        $result = $this->workWithResult($this->_restClient->getResponse(),$format);    
        
        return $result;
    }  
    
    public function GetTagQuery($sort="count",$max=100,$format="json") {
        $result = array();

        $this->_restClient->createRequest($this->_connectionUrl."/collaboration/tagQuery?d=".strtotime("now")."&s=$sort&m=$max&n=alfresco%3A%2F%2Fcompany%2Fhome&format=$format","GET");
        $this->_restClient->sendRequest();

        $result = $this->workWithResult($this->_restClient->getResponse(),$format);    
        
        return $result;
    }  
    
    public function EditTag($tag,$tagChanged) {
    	$result = array();
    	$postArr = json_encode(array("name"=>$tagChanged));
    	$this->_restClient->createRequest($this->_connectionUrl."/api/tags/workspace/SpacesStore/$tag?alf_method=PUT","PUT",$postArr);
    	$this->_restClient->sendRequest();
    
    	$result = $this->workWithResult($this->_restClient->getResponse(),"json");
    
    	return $result;
    }

    public function DeleteTag($tag) {
    	$result = array();

    	$this->_restClient->createRequest($this->_connectionUrl."/api/tags/workspace/SpacesStore/$tag","DELETE");
    	$this->_restClient->sendRequest();
    
    	$result = $this->workWithResult($this->_restClient->getResponse(),"json");
    
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
