<?php
namespace Ifresco\ClientBundle\Component\Alfresco\REST;

use Ifresco\ClientBundle\Component\Alfresco\BaseObject;
use Ifresco\ClientBundle\Component\Alfresco\REST\HTTP\RESTClient;

class RESTShare extends BaseObject {
    private $_restClient = null;
    private $_connectionUrl;
    public $namespaceMap;

    public function __construct($connectionUrl) {
        $this->_connectionUrl = $connectionUrl;
        $this->_connectionUrl = str_replace("alfresco/api","",$this->_connectionUrl);
        $this->setRESTClient();
    }


    public function getMetaData($shareId="",$format="json") {
        $result = array();


        $this->_restClient->createRequest($this->_connectionUrl."share/proxy/alfresco-noauth/api/internal/shared/node/$shareId/metadata/?format=$format","GET");
        $this->_restClient->sendRequest();

        $result = $this->workWithResult($this->_restClient->getResponse(),$format);

        return $result;
    }
    
    

    private function setRESTClient() {
        if ($this->_restClient == null) {
            $this->_restClient = new RESTClient();
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
