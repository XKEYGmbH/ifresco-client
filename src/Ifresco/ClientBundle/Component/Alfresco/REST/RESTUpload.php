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
class RESTUpload extends BaseObject {
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

    public function UploadNewContent($contentFile,$fileName,$contentType,$targetDirectory,$overwrite=false) {
        $postArr = array(
        	"filename"=>$fileName,
            "contenttype"=>$contentType,
            "description"=>'',
            "destination"=>$targetDirectory,
        	"overwrite"=>$overwrite == true ? "true" : "false"
        );

        $url = $this->_connectionUrl."/api/upload?format=json";
        $this->_restClient->createRequest($url,"POST",$postArr);
        $this->_restClient->addPostFile("filedata",$contentFile, $fileName, $fileName);

        $this->_restClient->sendRequest();
        $result = $this->workWithResult($this->_restClient->getResponse(),"json");
        print_R($result);
        return $result;
    }

    public function UploadNewFile($contentFile,$fileName,$contentType,$targetDirectory,$overwrite=false) {
        $postArr = array(
            "filename"=>$fileName,
            "contenttype"=>$contentType,
            //"uploaddirectory"=>$targetDirectory,
            "destination"=>$targetDirectory,
            "overwrite"=>$overwrite == true ? "true" : "false"
        );

        return $this->UploadFile($contentFile,$postArr);
    }
    
    public function UploadNewVersion($contentFile,$fileName,$contentType,$targetNodeId,$note="",$majorVersion=false) {
        if (!preg_match("#.*\://.*?/.*#eis",$targetNodeId))
            $targetNodeId = "workspace://SpacesStore/".$targetNodeId;
               
        $postArr = array(
            "filename"=>$fileName,
            "contenttype"=>$contentType,
            "updatenoderef"=>$targetNodeId,
            "overwrite"=>"true",
            "majorVersion"=>$majorVersion == true ? "true" : "false",
            "description"=>$note
        );

        return $this->UploadFile($contentFile,$postArr);
    }
    
    private function UploadFile($contentFile,$postArr) {
        $result = array();
        $url = $this->_connectionUrl."/api/upload?format=json";
        $this->_restClient->createRequest($url,"POST",$postArr);
        //$this->_restClient->addPostFile("filedata",$contentFile);
        $this->_restClient->addPostFile("filedata",$contentFile,$postArr["filename"],$postArr["contenttype"]);
        
        $this->_restClient->sendRequest();
        $result = $this->workWithResult($this->_restClient->getResponse(),"json");   
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
