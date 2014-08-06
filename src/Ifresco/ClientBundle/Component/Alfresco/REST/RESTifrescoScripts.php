<?php
namespace Ifresco\ClientBundle\Component\Alfresco\REST;

use Ifresco\ClientBundle\Component\Alfresco\BaseObject;
use Ifresco\ClientBundle\Component\Alfresco\NamespaceMap;
use Ifresco\ClientBundle\Component\Alfresco\REST\HTTP\RESTClient;
use Ifresco\ClientBundle\Component\Alfresco\Node;
use Ifresco\ClientBundle\Component\Alfresco\Helpers\json_encode;

class RESTifrescoScripts extends BaseObject {
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
    
    private function urlencodesub($string) {
    	$entities = array('%2F');
    	$replacements = array("/", "?", "%", "#", "[", "]");
    	return str_replace($entities, $replacements, urlencode($string));
    }
    
    public function GetDocLib($path="",$max=50,$pos=1,$sortField="",$sortAsc="true",$filter="path",$type="all",$identif="node",$libaryRoot="alfresco://company/home",$view="browse",$format="json") {
    	$path = "alfresco/company/home/".ltrim(rtrim($path,"/"),"/");
        $result = array();
        
    
        $path = $this->urlencodesub($path);
        $path = str_replace("+","%20",$path);

//echo $this->_connectionUrl."/ifresco/doclib/doclist/$type/$identif/$path?filter=$filter&size=$max&pos=$pos&sortAsc=$sortAsc".(!empty($sortField) ? "&sortField=$sortField" : "")."&libraryRoot=$libaryRoot&format=$format";
        $this->_restClient->createRequest($this->_connectionUrl."/ifresco/doclib/doclist/$type/$identif/$path?filter=$filter&size=$max&pos=$pos&sortAsc=$sortAsc".(!empty($sortField) ? "&sortField=$sortField" : "")."&libraryRoot=$libaryRoot&format=$format","GET");
        $this->_restClient->sendRequest();
        $result = $this->workWithResult($this->_restClient->getResponse(),"jsonadvanced");    
        return $result;
    } 
    
    public function Search($query="",$term="",$sortField="",$sortDesc=false,$maxResults=20,$skip=0,$max=null,$site="",$tag="",$repo="true",$libaryRoot="alfresco://company/home",$format="json") {
    	$result = array();
    
    	if (is_array($query)) {
    		$query = json_encode($query);
    		$query = $this->urlencodesub($query);
    		$query = str_replace("+","%20",$query);
    	}

    	//$term = utf8_decode($term);

    	$term = $this->urlencodesub($term);
    	$term = str_replace("+","%20",$term);

    	$url = $this->_connectionUrl."/ifresco/search?site=$site&term=$term&tag=$tag&query=$query&maxResults=$maxResults&skip=$skip".($max != null ? "&max=".$max : "")."&sort=$sortField".($sortDesc == true ? "|DESC" : "")."&repo=$repo&rootNode=$libaryRoot&format=$format";

    	//echo $this->_connectionUrl."/ifresco/search?site=$site&term=$term&tag=$tag&query=$query&maxResults=$maxResults&skip=$skip".($max != null ? "&max=".$max : "")."&sort=$sortField".($sortDesc == true ? "|DESC" : "")."&repo=$repo&rootNode=$libaryRoot&format=$format";
    	$this->_restClient->createRequest($url,"GET");
    	$this->_restClient->sendRequest();
    	//print_R($this->_restClient->getResponse());
    	$result = $this->workWithResult($this->_restClient->getResponse(),"jsonscript");
    	if (is_object($result))
    		$result->url = $url;
    	else if (is_array($result))
    		$result["url"] = $url;
    	else if (is_null($result))
    		$result = (object)array("url"=>$url);
    	
    	return $result;
    }
    
    public function FolderSearch($query="",$format="json") {
    	$result = array();

    	$this->_restClient->createRequest($this->_connectionUrl."/ifresco/foldersearch/search?query=$query&format=$format","GET");
    	$this->_restClient->sendRequest();
    	$result = $this->workWithResult($this->_restClient->getResponse(),$format);
    	 
    	return $result;
    }
    
    public function RefreshWebScripts() {
    	$result = array();
    	
    	$postArr = array();
    	$this->_restClient->createRequest($this->_connectionUrl."/index?reset=on","POST",$postArr,"application/x-www-form-urlencoded");
    	
    	$this->_restClient->sendRequest();
    	
    	$result = $this->workWithResult($this->_restClient->getResponse(),"html");
    	
    	return $result;
    }

    private function setRESTClient() {
        if ($this->_restClient == null) {
            //$this->_restClient = new RESTClient($this->_session->getTicket(),$this->_session->getLanguage(),"admin","geheim!");  // TODO -change to username + password or ticket
            $this->_restClient = new RESTClient($this->_session->getTicket(),$this->_session->getLanguage());
        }
    }
    
    private function workWithResult($resultGet,$format) {
        switch ($format) {
        	case "jsonscript":
        		//print_R($resultGet);
            	//die();
            	
        		$resultGet = preg_replace('/\t+|\r\n|\n|\s\s+/i', '', $resultGet);
        		$resultGet = str_replace("},]","}]",$resultGet);
                $result = json_decode($resultGet);
                if (isset($result->items) && count($result->items) > 0) {
                    $newResult = array();
                    for  ($i = 0; $i < count($result->items); $i++) {
                    	try {
	                        //$newResult[] = Node::createFromRestData($this->_session, $result->items[$i]);
	                        $Node = Node::publishFromScript($this->_session, $result->items[$i]);
	                        $newResult[] = $Node;
                        
                    	}
                    	catch (\Exception $e) {
                    		echo "ERROR in Item\n";
                    		print_R($result->items[$i]);
                    		echo $e->getMessage();
                    	}
                    }
                    $result->items = $newResult;
                    return $result;
                }

            	return $result;
            case "jsonadvanced":
            	//print_R($resultGet);
            	//die();
                $result = json_decode($resultGet);
                if (isset($result->items) && count($result->items) > 0) {
                    $newResult = array();
                    for  ($i = 0; $i < count($result->items); $i++) {
                        //$newResult[] = Node::createFromRestData($this->_session, $result->items[$i]);
                        $Node = Node::createFromDocLib($this->_session, $result->items[$i]);
                        $newResult[] = $Node;
                    }
                    $result->items = $newResult;
                    return $result;
                }

            	return $result;
            break;
            case "json":
            	$result = json_decode($resultGet);
            	break;
            default:
            	$result = $resultGet;
                break;
        }
        return $result;
    }
}

?>
