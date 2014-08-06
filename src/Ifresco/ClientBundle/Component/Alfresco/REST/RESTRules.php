<?php

class RESTRules extends BaseObject {
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

    public function GetRuleset($nodeId,$ruleType="",$format="json") {
        $result = array();

        $this->_restClient->createRequest($this->_connectionUrl."/api/node/workspace/SpacesStore/$nodeId/ruleset?ruleType=$ruleType&format=$format","GET");  
        $this->_restClient->sendRequest();

        $result = $this->workWithResult($this->_restClient->getResponse(),$format);    

        return $result;
    }
    
    public function GetRulesCollection($nodeId,$ruleType="",$format="json") {
        $result = array();

        $this->_restClient->createRequest($this->_connectionUrl."/api/node/workspace/SpacesStore/$nodeId/ruleset/rules?ruleType=$ruleType&format=$format","GET");  
        $this->_restClient->sendRequest();

        $result = $this->workWithResult($this->_restClient->getResponse(),$format);    

        return $result;
    }  
    
    
    public function GetRuleTypes($format="json") {
        $result = array();

        $this->_restClient->createRequest($this->_connectionUrl."/api/ruletypes?format=$format","GET");  
        $this->_restClient->sendRequest();

        $result = $this->workWithResult($this->_restClient->getResponse(),$format);    

        return $result;
    }
    
    public function GetActionConditionDefinitions($format="json") {
        $result = array();

        $this->_restClient->createRequest($this->_connectionUrl."/api/actionconditiondefinitions?format=$format","GET");  
        $this->_restClient->sendRequest();

        $result = $this->workWithResult($this->_restClient->getResponse(),$format);    

        return $result;
    }     
    
    public function GetActionDefinitions($format="json") {
        $result = array();

        $this->_restClient->createRequest($this->_connectionUrl."/api/actiondefinitions?format=$format","GET");  
        $this->_restClient->sendRequest();

        $result = $this->workWithResult($this->_restClient->getResponse(),$format);    

        return $result;
    }    

    public function GetActionConstraints($name="",$format="json") {
        $result = array();

        $this->_restClient->createRequest($this->_connectionUrl."/api/actionConstraints?name=$name&format=$format","GET");  
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
