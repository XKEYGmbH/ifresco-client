<?php
namespace Ifresco\ClientBundle\Component\Alfresco\Lib;

use Ifresco\ClientBundle\Component\Alfresco\Lib\HTTP\AutoOCRRESTClient;

class AutoOCRService {
    private $_serviceUrl = "";
    private $_restClient = null;
    private $_sessionId = null;

    public function __construct($url="") {
        if (empty($url))
            $this->_serviceUrl = "http://localhost:8001/AutoOCRService/";
        else
        $this->_serviceUrl = $url;

        $this->setRESTClient();
    }

    private function setRESTClient() {
        if ($this->_restClient == null) {
            $this->_restClient = new AutoOCRRESTClient();
        }
    }

    private function sendRequestAndResponse($params="",$serviceUrl="") {
        if (empty($serviceUrl)) {
            $backtrace = debug_backtrace();
            $serviceUrl = trim($backtrace[1]['function']);
            if (empty($serviceUrl))
                throw new \Exception("cannot find serviceUrl - {$serviceUrl}");
        }

        if (is_array($params))
            $params = http_build_query($params);

        if (!empty($params))
            $url = $this->_serviceUrl."/".$serviceUrl."?".$params;
        else
            $url = $this->_serviceUrl."/".$serviceUrl;

        $this->_restClient->createRequest($url,"GET");


        $this->_restClient->sendRequest();

        $result = $this->workWithResult($this->_restClient->getResponse());
        return $result;
    }

    private function workWithResult($resultGet,$format="json") {
        switch ($format) {
            case "json":
                $result = json_decode($resultGet);
            break;
            case "download":
                $result = $resultGet;
            break;
            default:

            break;
        }
        return $result;
    }

    public function Auth($userName,$password) {
        $response = $this->sendRequestAndResponse(array("userName"=>$userName,"password"=>$password));

        return $response;
    }

    public function GetStatus($jobID) {
        $response = $this->sendRequestAndResponse(array("jobID"=>$jobID));

        return $response;
    }

    public function GetNrOfPages($jobID) {
        $response = $this->sendRequestAndResponse(array("jobID"=>$jobID));

        return $response;
    }

    public function GetJob($jobID) {
        $response = $this->sendRequestAndResponse(array("jobID"=>$jobID));

        return $response;
    }

    public function GetResultCount($jobID) {
        $response = $this->sendRequestAndResponse(array("jobID"=>$jobID));

        return $response;
    }

    public function GetResult($jobID,$removeFile=true) {
        $response = $this->sendRequestAndResponse(array("jobID"=>$jobID,"index"=>0,"removeFile"=>$removeFile));

        return $response;
    }

    public function GetResultEx($jobID) {
        $response = $this->sendRequestAndResponse(array("jobID"=>$jobID,"index"=>0));

        return $response;
    }

    public function RemoveJob($jobID) {
        $response = $this->sendRequestAndResponse(array("jobID"=>$jobID));

        return $response;
    }

    public function UploadFile($contentFile,$settingsName="") {
        $Upload = new \AutoOCR\Upload($this->_serviceUrl."UploadJobEx");
        $result = $Upload->UploadNewFile($contentFile,$settingsName);

        return $result->UploadJobExResult;
    }

    public function UploadContent($content,$fileName,$settingsName="") {
        $Upload = new \AutoOCR\Upload($this->_serviceUrl."UploadJobEx");
        $result = $Upload->UploadNewContent($content,$fileName,$settingsName);

        return $result->UploadJobExResult;
    }

    public function Download($jobId) {
        $url = $this->_serviceUrl."/GetResultEx?jobID=".$jobId."&index=0";

        $this->_restClient->createRequest($url,"GET");
        $this->_restClient->sendRequest();

        $contentResult = $this->workWithResult($this->_restClient->getResponse(),"download");
        return $contentResult;
    }

    public function GetSettingsCollection() {
        $response = $this->sendRequestAndResponse();
        return $response;
    }

    public function GetAvailablePages() {
        $response = $this->sendRequestAndResponse();
        return $response;
    }

    public function GetNrOfPagesInQueue() {
        $response = $this->sendRequestAndResponse();
        return $response;
    }

    public function GetNrOfDocumentsInQueue() {
        $response = $this->sendRequestAndResponse();
        return $response;
    }

    public function GetAvgSecPerPage() {
        $response = $this->sendRequestAndResponse();
        return $response;
    }
}
?>