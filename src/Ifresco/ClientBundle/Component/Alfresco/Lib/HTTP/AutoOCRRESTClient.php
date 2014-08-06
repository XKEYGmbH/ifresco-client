<?php

namespace Ifresco\ClientBundle\Component\Alfresco\Lib\HTTP;


class AutoOCRRESTClient {

    private $root_url = "";
    private $curr_url = "";
    private $user_name = "";
    private $password = "";
    private $response = "";
    private $responseBody = "";
    private $responseCookies = array();
    private $req = null;

    public function __construct($user_name="",$password="") {
    	if ($this->user_name != "" && $this->password != "") {
    		$this->user_name = $user_name;
    		$this->password = $password;	
    	}	
    }

    public function createRequest($url, $method, $arr = null, $arrFormat = null) {
        $this->curr_url = $url;

        $this->req = new \HTTP_Request2($this->curr_url);
        $this->req->setConfig(array(
            'ssl_verify_peer'   => FALSE,
            'ssl_verify_host'   => FALSE
        ));
        $this->req->_allowRedirects = true;

        if ($this->user_name != "" && $this->password != "") {
           $this->req->setBasicAuth($this->user_name, $this->password);
        }        
        
        switch($method) {
            case "GET":
                $this->req->setMethod('GET');
                break;
            case "POST":
                $this->req->setMethod('POST');
                if ($arrFormat == "json") {
                    $this->req->addHeader("Content-Type","application/json");
                    $this->req->addRawPostData($arr);

                }
                else
                    $this->addPostData($arr);
                break;
            case "PUT":
                $this->req->setMethod(HTTP_REQUEST_METHOD_PUT);
                if ($arrFormat == "json") {
                    $this->req->addHeader("Content-Type","application/json");
                    $this->req->addRawPostData($arr);
                }
                else {
                    if (is_array($arr)) {
                        $this->addPostData($arr);
                    }
                    else 
                        $this->req->addRawPostData($arr);
                }
                // to-do
                break;
            case "DELETE":
                $this->req->setMethod(HTTP_REQUEST_METHOD_DELETE);
                // to-do
                break;
        }
    }
    
    public function addPostFile($inputName, $fileName, $contentType = null) {
        $this->req->addFile($inputName, $fileName, $contentType);
    }

    private function addPostData($arr) {
        if ($arr != null) {
            foreach ($arr as $key => $value) {
                $this->req->addPostData($key, $value);
            }
        }
    }

    public function sendRequest() {
        $this->response = $this->req->send();

        if (\PEAR::isError($this->response)) {
            throw new \Exception($this->response->getMessage());
        } else {
            $header = $this->req->getHeaders();
        	$this->responseCookies = isset($header['cookie'])?$header['cookie']:array();
            $this->responseBody = $this->req->getBody();

        }
    }
    
    public function addCookie($name,$value) {
    	$this->req->addCookie($name,$value);
    }
    
    public function getResponseCookies() {
    	return $this->responseCookies;
    }

    public function getResponse() {
        return $this->responseBody;
    }


}
?>