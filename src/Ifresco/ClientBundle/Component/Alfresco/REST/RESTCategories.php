<?php
namespace Ifresco\ClientBundle\Component\Alfresco\REST;

use Ifresco\ClientBundle\Component\Alfresco\BaseObject;
use Ifresco\ClientBundle\Component\Alfresco\NamespaceMap;
use Ifresco\ClientBundle\Component\Alfresco\REST\HTTP\RESTClient;

class RESTCategories extends BaseObject {
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
    
    public function GetCategories($categoryName="",$format="json") {
        $result = array();
        $orgcategoryName = $categoryName;
        $categoryName = urldecode($categoryName);
        if ($categoryName == "") {
            $this->_restClient->createRequest($this->_connectionUrl."/slingshot/doclib/categorynode/node/workspace/SpacesStore/company_home?format=$format","GET");
        }
        else { 
            
            $explode = explode("/",$categoryName);
            if (count($explode) > 0) {
                $newCategory = "";
                for ($i = 0; $i < count($explode); $i++) {
                    $decode = rawurldecode($explode[$i]);

                    //if (!preg_match("/%/eis",$explode[$i])) {
                        $explode[$i] = rawurlencode($decode); 
                    //}
                }  
                $categoryName = join("/",$explode);
            }   
            else {
                if (!preg_match("/%/eis",$categoryName))
                    $categoryName = rawurlencode($categoryName);   
                
            }  

            //todo: enable
             //FB::log($this->_connectionUrl."/slingshot/doclib/categorynode/node/workspace/SpacesStore/company_home/$categoryName?format=$format");
            $this->_restClient->createRequest($this->_connectionUrl."/slingshot/doclib/categorynode/node/workspace/SpacesStore/company_home/$categoryName?format=$format","GET");  
        }    
        
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
