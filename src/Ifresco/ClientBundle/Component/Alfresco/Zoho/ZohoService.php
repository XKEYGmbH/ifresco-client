<?php

class ZohoService extends BaseObject {
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
        $this->_connectionUrl = str_replace("api","service",$this->_connectionUrl);
        $this->setRESTClient();
    }  
    
 
    public function UploadZoho($Node,$saveurl,$apiKey,$skey) {
        $docMimetypes = array("application/msword",
                              "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
                              "application/rtf",
                              "text/rtf",
                              "text/html",
                              "application/vnd.oasis.opendocument.text",
                              "application/vnd.sun.xml.writer",
                              "text/plain"
                             );
        $writerMimetypes = array("application/vnd.ms-excel",
                                 "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                                 "application/vnd.oasis.opendocument.spreadsheet",
                                 "application/vnd.sun.xml.calc",
                                 "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                                 "text/csv",
                                 "text/comma-separated-values",
                                 "text/tab-separated-values"
                                );
        
        $zohoLink = "";
        
        $result = array();
        
        $fileName = $Node->cm_name;
        
        $ext = substr($fileName, strrpos($fileName, '.') + 1);
        $content = $Node->cm_content;
        $contentFile = null;
        $contentData = "";
        if ($content != null && $content instanceof ContentData) {
            $contentData = $content->getContent();
            $contentFile = tempnam($tempFile = sfConfig::get('sf_cache_dir'), $Node->cm_name);
            $content->readContentToFile($contentFile);
            $mimetype = $content->getMimetype();
            if (in_array($mimetype,$docMimetypes))
                $zohoLink = "https://export.writer.zoho.com/remotedoc.im";
            else if (in_array($mimetype,$writerMimetypes))
                $zohoLink = "https://sheet.zoho.com/remotedoc.im"; 
            else
                throw new Exception("Not supported mimetype [$mimetype]");                 
        }
        
        if ($contentFile == null) 
            return false;
        
        $documentId = preg_replace("/[^0-9]/","",$Node->getId());
        
        
        

        $postArr = array(
            //"documentid"=>$documentId,
            "apikey"=>$apiKey,
            "output"=>"url",
            "mode"=>"normaledit",
            "filename"=>$fileName,
            "lang"=>"en",
            "id"=>$Node->getId()."#".$this->_ticket,
            "format"=>$ext,
            "saveurl"=>$saveurl
        );
        
        // zoho doesnt allow http anymore: so just https link is allowed
        //$zohoLink = "https://export.writer.zoho.com/remotedoc.im";
        $postArr["skey"]=$skey;
        FB::log($mimetype);

        $this->_restClient->createRequest($zohoLink,"POST",$postArr);
        $this->_restClient->addPostFile("content",$contentFile);

        $this->_restClient->sendRequest();

        $result = $this->workWithResult($this->_restClient->getResponse());    
        unlink($contentFile);
        return $result;
    }
    
    public function CheckZohoDoc($documentId,$apiKey) {
        //https://<writer/sheet/show>.zoho.com/remotedocStatus.im?doc=<docid>&apikey=<apikey>    
        $zohoLink = "https://writer.zoho.com/remotedocStatus.im?doc=$documentId&apikey=$apiKey"; 
           
        $this->_restClient->createRequest($zohoLink,"GET");

        $this->_restClient->sendRequest();

        $result = $this->workWithResult($this->_restClient->getResponse(),"json");    

        return $result;  
    }
    
    public function CheckZohoSheet($documentId,$apiKey) {
        $zohoLink = "https://sheet.zoho.com/remotedocStatus.im?doc=$documentId&apikey=$apiKey"; 
           
        $this->_restClient->createRequest($zohoLink,"GET");

        $this->_restClient->sendRequest();

        $result = $this->workWithResult($this->_restClient->getResponse(),"json");    

        return $result;      
    }
    
    private function setRESTClient() {
        if ($this->_restClient == null) {
            $this->_restClient = new RESTClient();    
        }
    }
    
    private function workWithResult($resultGet,$format="") {
        switch ($format) {
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
