<?php
namespace Ifresco\ClientBundle\Component\Alfresco\REST;

use Ifresco\ClientBundle\Component\Alfresco\BaseObject;
use Ifresco\ClientBundle\Component\Alfresco\NamespaceMap;
use Ifresco\ClientBundle\Component\Alfresco\REST\HTTP\RESTClient;
use Ifresco\ClientBundle\Component\Alfresco\Node;
use Ifresco\ClientBundle\Component\Alfresco\Helpers\json_encode;

class RESTDoclib extends BaseObject {
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
    
    
    public function GetDocLib($id="",$type="treenode",$space="workspace",$store="SpacesStore",$max=50,$format="json") {
        $result = array();
        //http://testalf.may.co.at:8080/alfresco/service/slingshot/doclib/doclist/treenode/node/workspace/SpacesStore/5677ba1d-b9fa-4ad8-a511-22f9d3a2ae85
        
        //http://ifresco.new:8080/alfresco/service/slingshot/doclib/doclist/all/node/alfresco/company/home/?filter=path&size=50&pos=1&sortAsc=true&sortField=cm%3Aname&libraryRoot=alfresco%3A%2F%2Fshared&view=browse&noCache=1390361338166
        $children = false;
        $perms = false;
        $this->_restClient->createRequest($this->_connectionUrl."/slingshot/doclib/doclist/$type/node/$space/$store/$id?max=$max&children=$children&perms=$perms&format=$format","GET");
        $this->_restClient->sendRequest();
        $result = $this->workWithResult($this->_restClient->getResponse(),"jsondoclib");    
        return $result;
    } 
    
    public function GetDocLibShared($max=50,$format="json") {
    	$result = array();
    	//http://testalf.may.co.at:8080/alfresco/service/slingshot/doclib/doclist/treenode/node/workspace/SpacesStore/5677ba1d-b9fa-4ad8-a511-22f9d3a2ae85
    
    	//http://ifresco.new:8080/alfresco/service/slingshot/doclib/doclist/all/node/alfresco/company/home/?filter=path&size=50&pos=1&sortAsc=true&sortField=cm%3Aname&libraryRoot=alfresco%3A%2F%2Fshared&view=browse&noCache=1390361338166
    	$children = false;
    	$perms = false;
    	$this->_restClient->createRequest($this->_connectionUrl."/slingshot/doclib/doclist/all/node/alfresco/company/home/?filter=path&max=$max&libraryRoot=alfresco://shared&format=$format","GET");
    	$this->_restClient->sendRequest();
    	$result = $this->workWithResult($this->_restClient->getResponse(),"json");

    	return $result;
    }

    
    public function Search($term,$tag="",$sort="",$maxResults="50",$queryFields=array(),$format="json") {
        $result = array();

        if (!empty($queryFields))
            $query = json_encode($queryFields);
        else
            $query = "";
        $this->_restClient->createRequest($this->_connectionUrl."/slingshot/search?term=$term&query=$query&sort=$sort&maxResults=$maxResults&repo=true&tag=$tag&site=&format=$format","GET");
        $this->_restClient->sendRequest();
        $result = $this->workWithResult($this->_restClient->getResponse(),$format);    
        return $result;
    }  
    
    //http://testalf.may.co.at:8080/alfresco/service/slingshot/search?term={term?}&tag={tag?}&site={site?}&container={container?}&sort={sort?}&query={query?}&repo={repo?}
    /* http://testalf.may.co.at:8080/share/page/search?t=Japan&q=%7B%22prop_cm_name%22%3A%22%22%2C%22prop_cm_title%22%3A%22%22%2C%22prop_cm_description%22%3A%22%22%2C%22prop_mimetype%22%3A%22%22%2C%22prop_cm_modified-date-range%22%3A%22%22%2C%22prop_cm_modifier%22%3A%22%22%2C%22datatype%22%3A%22cm%3Acontent%22%7D*/
    
    public function SearchFolder($term,$format="json") {
    	$result = array();

    	$this->_restClient->createRequest($this->_connectionUrl."/bulkfsimport/ajax/suggest/spaces?query=$term&format=$format","GET");
    	$this->_restClient->sendRequest();
    	$result = $this->workWithResult($this->_restClient->getResponse(),$format);
    	return $result;
    }
    
    private function setRESTClient() {
        if ($this->_restClient == null) {
            $this->_restClient = new RESTClient($this->_session->getTicket(),$this->_session->getLanguage());    
        }
    }
    
    private function workWithResult($resultGet,$format) {
        switch ($format) {
            case "jsondoclib":
                $result = json_decode($resultGet);
                if (count($result->items) > 0) {
                    $newResult = array();
                    for  ($i = 0; $i < count($result->items); $i++) {
                        //$newResult[] = Node::createFromRestData($this->_session, $result->items[$i]);
                        $Node = Node::createFromRestData($this->_session, $result->items[$i]);
                        $newResult[] = $Node;
                    }
                    $result->items = $newResult;
                    return $result;
                }
            break;
            case "json":
            	$result = json_decode($resultGet);
            	break;
            default:
                
                break;
        }
        return $result;
    }
}

?>
