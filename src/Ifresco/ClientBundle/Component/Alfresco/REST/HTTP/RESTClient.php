<?php
namespace Ifresco\ClientBundle\Component\Alfresco\REST\HTTP;
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
set_include_path(realpath(dirname(__FILE__))."/PEAR_PACK/");
require_once "HTTP/Request2.php";
require_once 'PEAR.php';

class RESTClient {

    private $root_url = "";
    private $curr_url = "";
    private $user_name = "";
    private $password = "";
    private $ticket = "";
    private $response = "";
    private $responseBody = "";
    private $req = null;
    private $lang = "";

    public function __construct($ticket="",$lang="en-us",$username="",$password="") {
        $this->ticket = $ticket;
        $this->lang = $lang;
        $this->user_name = $username;
        $this->password = $password;
    }

    public function createRequest($url, $method, $arr = null, $arrFormat = null) {
        $this->curr_url = $url;
        if (!empty($this->ticket)) {
           if (preg_match("/\?/is",$this->curr_url))
            $this->curr_url .= "&alf_ticket=".$this->ticket;
           else
            $this->curr_url .= "?alf_ticket=".$this->ticket;
      
        }
        
    	$this->req = new \HTTP_Request2($this->curr_url);
        if ($this->user_name != "" && $this->password != "") {  

           $this->req->setAuth($this->user_name, $this->password, \HTTP_Request2::AUTH_BASIC);

        }  
//echo $this->curr_url;
        $this->req->setConfig(array(
		    'ssl_verify_peer'   => FALSE,
		    'ssl_verify_host'   => FALSE
		));

        switch($method) {
            case "GET":
                $this->req->setMethod(\HTTP_Request2::METHOD_GET);
                break;
            case "POST":
                $this->req->setMethod(\HTTP_Request2::METHOD_POST);
                if ($arrFormat == "json") {
                    $this->req->setHeader("Content-Type","application/json");
                    $this->req->setBody($arr);

                }
                else
                    $this->addPostData($arr);
                break;
            case "PUT":
                $this->req->setMethod(\HTTP_Request2::METHOD_PUT);
                if ($arrFormat == "json") {
                    $this->req->setHeader("Content-Type","application/json");
                    $this->req->setBody($arr);
                }
                else
                    $this->addPostData($arr);
                // to-do
                break;
            case "DELETE":
                $this->req->setMethod(\HTTP_Request2::METHOD_DELETE);
                // to-do
                break;
        }
    }
    
    /*public function addPostFile($inputName, $fileName, $contentType = 'application/octet-stream', $newFileName = null) {

        $this->req->addUpload($inputName, $fileName, $newFileName, $contentType);
    }*/
    
    public function addPostFile($inputName, $fileName, $sendFilename = null, $contentType = 'application/octet-stream') {
    
    	$this->req->addUpload($inputName, $fileName, $sendFilename, $contentType);
    }

    private function addPostData($arr) {
        if ($arr != null) {
            foreach ($arr as $key => $value) {
                $this->req->addPostParameter($key, $value);
            }
        }
    }

    public function sendRequest() {
        $this->req->setHeader("User-Agent","ifresco client");
        $langTmp = "";
        if (!preg_match("/[a-zA-Z]+\-[a-zA-Z]+/",$this->lang))
            $langTmp = $this->lang."-".$this->lang;
        if (!empty($langTmp))
            $langTmp .= ",";
        $langTmp .= $this->lang;
         
        $this->req->setHeader("Accept-Language",$langTmp);

       
        $this->response = $this->req->send();

        if (!$this->response) {
            echo $this->response->getReasonPhrase();
        } else {
            $this->responseBody = $this->response->getBody();
            
        }
    }

    public function getResponse() {
        return $this->responseBody;
    }

    public function getClearResponse() {
        return $this->response;
    }


}
?>