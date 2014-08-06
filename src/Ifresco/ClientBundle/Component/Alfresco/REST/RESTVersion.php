<?php
namespace Ifresco\ClientBundle\Component\Alfresco\REST;

use Ifresco\ClientBundle\Component\Alfresco\BaseObject;
use Ifresco\ClientBundle\Component\Alfresco\NamespaceMap;
use Ifresco\ClientBundle\Component\Alfresco\REST\HTTP\RESTClient;
 /**
 *
 * @package    ifresco PHP library
 * @author Dominik Danninger 
 * @website http://www.ifresco.at
 *
 * ifresco PHP library - extends Alfresco PHP Library
 * 
 * Copyright (c) 2013 X.KEY GmbH
 * 
 * This file is part of "ifresco PHP library".
 * 
 * "ifresco PHP library" is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * "ifresco PHP library" is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with "ifresco PHP library".  If not, see <http://www.gnu.org/licenses/>. (http://www.gnu.org/licenses/gpl.html)
 */
class RESTVersion extends BaseObject {
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
    
    public function GetVersionInfo($id="",$format="json") {
        $result = array();

        
        $this->_restClient->createRequest($this->_connectionUrl."/api/version?nodeRef=workspace://SpacesStore/$id&format=$format","GET");

        $this->_restClient->sendRequest();

        $result = $this->workWithResult($this->_restClient->getResponse(),$format);    

        return $result;
    }  
    
    
    public function RevertVersion($id,$version,$majorVersion=true,$description="",$format="json") {
        $result = array();
        
        $postArray = array("nodeRef"=>"workspace://SpacesStore/".$id,"version"=>$version,"majorVersion"=>$majorVersion,"description"=>$description);
        
        $postArr = json_encode($postArray);                             
        $this->_restClient->createRequest($this->_connectionUrl."/api/revert?format=$format","POST",$postArr,"json");

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
