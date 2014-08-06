<?php
namespace AutoOCR;

use AutoOCR\HTTP;
use AutoOCR\MimetypeHandler;

class Upload {
    
    private $_restClient = null;
    
    private $_repository;
    private $_session;
    private $_store;
    private $_ticket;
    private $_connectionUrl;
    
    public $namespaceMap;
    
    public function __construct($url) {
        $this->_connectionUrl = $url;
        $this->setRESTClient();
    }  
    
    public function UploadNewFile($contentFile,$settingsName="") {
        $postArr = array(
            //"ext"=>$this->getMimeType($fileName),
            "ext"=>$this->getFileExt($contentFile),
            "settingsName"=>$settingsName
        );

        return $this->UploadFile($contentFile,$postArr);
    }
    
    public function UploadNewContent($content,$fileName,$settingsName="") {
        $postArr = array(
            //"ext"=>$this->getMimeType($fileName),
            "ext"=>$this->getFileExt($fileName),
            "settingsName"=>$settingsName
        );
        
        return $this->UploadFile($content,$postArr,"content");
    }
    
    private function UploadFile($contentFile,$postArr,$type="file") {
        $result = array();
        //$url = $this->_connectionUrl;
        $params = http_build_query($postArr);
        $url = $this->_connectionUrl."?".$params;
        //echo $url;

        if ($type == "file")
            $filedata = file_get_contents($contentFile);
        else
            $filedata = $contentFile;
        //$this->_restClient->createRequest($url,"POST",$postArr);
        $this->_restClient->createRequest($url,"PUT",$filedata);
        
        //$this->_restClient->addPostFile("file",$contentFile,$this->getMimeType($contentFile));

        $this->_restClient->sendRequest();

        $result = $this->workWithResult($this->_restClient->getResponse(),"json");   
        //$result = $this->_restClient->getResponse();
        return $result;
    }
    
    private function setRESTClient() {
        if ($this->_restClient == null) {
            $this->_restClient = new HTTP\AutoOCRRESTClient();    
        }
    }
    
    
    private function workWithResult($resultGet,$format) {
        switch ($format) {
            case "json":
                $result = json_decode($resultGet); 
            break;
            case "xml":
                
                $resultGet = preg_replace("/<UploadJobExResponse.*?>/is","<UploadJobExResponse>",$resultGet);
                $resultGet = preg_replace("/<UploadJobExResult.*?>/is","<UploadJobExResult>",$resultGet);
                $resultGet = preg_replace("/a:JobGuid/is","JobGuid",$resultGet);
                $resultGet = preg_replace("/a:JobID/is","JobID",$resultGet);
                $resultGet = preg_replace("/a:PageCount/is","PageCount",$resultGet);
                $resultGet = preg_replace("/a:Status/is","Status",$resultGet);
                $result = (object)simplexml_load_string($resultGet);
            break;
            case "xmlold":
                $result = (object)simplexml_load_string($resultGet);
            break;
            default:
                $result = $resultGet;
                break;
        }
        return $result;
    }
    
    private function getMimeType($filename) {

        $mime = new MimetypeHandler\MimetypeHandler();
        return $mime->getMimetype($filename);
    }
    
    private function getFileExt($filename) {

        $fileext = preg_replace("#.*\.(.*)#is","$1",$filename);
        return $fileext;
    }
}