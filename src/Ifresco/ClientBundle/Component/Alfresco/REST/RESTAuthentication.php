<?php
namespace Ifresco\ClientBundle\Component\Alfresco\REST;

use Ifresco\ClientBundle\Component\Alfresco\BaseObject;
use Ifresco\ClientBundle\Component\Alfresco\Repository;
use Ifresco\ClientBundle\Component\Alfresco\NamespaceMap;
use Ifresco\ClientBundle\Component\Alfresco\REST\HTTP\RESTClient;

class RESTAuthentication extends BaseObject {
    private $_restClient = null;
    
    private $_repository;
    private $_session;
    private $_store;
    private $_ticket;
    private $_connectionUrl;
    private $_repositoryUrl;
    private $_userName;
    private $_password;
    
    public $namespaceMap;
    
    public function __construct($repositoryUrl) {
        /*$this->_repository = $repository;
        $this->_store = $store;
        $this->_session = $session;
        $this->_ticket = $this->_session->getTicket();*/
        $this->setRESTClient();
        $this->_repositoryUrl = $repositoryUrl;
        $this->_connectionUrl = $repositoryUrl;
        $this->_connectionUrl = str_replace("soapapi","service",$this->_connectionUrl); $this->_connectionUrl = str_replace("api","service",$this->_connectionUrl);

    }  
    
    public function getTicket() {
        return $this->_ticket;
    }

    
    public function getUsername()
    {
        return $this->_userName;
    }
    
    public function getPassword()
    {
        return $this->_password;
    }
    
    public function getRepository()
    {
        return $this->_repository;
    }
    
    public function getSession()
    {
        return $this->_session;
    }

    
    public function login($userName, $password) {

        $this->_userName = $userName;
        $this->_password = $password;

        $postArr = json_encode(array("username"=>$userName,"password"=>$password));
        $this->_restClient->createRequest($this->_connectionUrl."/api/login?format=json","POST",$postArr,"json");
        $this->_restClient->sendRequest();

        $result = $this->workWithResult($this->_restClient->getResponse(),"json");    
        $this->ticket = false;

        if (!empty($result->data->ticket)) {
            $this->_ticket = $result->data->ticket;
            try {
                $this->_repository = new Repository($this->_repositoryUrl);
                $this->_ticket = $this->_repository->authenticate($userName, $password);
                $this->_session = $this->_repository->createSession($this->_ticket);
                $this->namespaceMap = new NamespaceMap();   
              
            }
            catch (\Exception $e) {
                return false;
            }
        }
        return $this->_ticket;       
    }
    
    
    private function setRESTClient() {
        if ($this->_restClient == null) {
            $this->_restClient = new RESTClient("");    
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
