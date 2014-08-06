<?php
namespace Ifresco\ClientBundle\Component\Alfresco;
require_once 'Node.php';

class UserDetail extends BaseObject {
    
    /*public $userName = null;
    public $firstName = null;
    public $lastName = null;
    public $email = null;
    public $homeFolder = null;
    public $organizationId = null;   
    public $organization = null;   
    public $jobtitle = null;   
    public $owner = null;  
    public $location = null;  
    
    public $preferenceValues = null;   
    public $presenceProvider = null;   
    public $sizeCurrent = null;   
    public $sizeQuota = null;   */
    private $_session;
    private $_id;
    private $_userDetails;
    private $_properties;
    private $_node;
    private $_store;
     
    
    public function __construct($session, $store, $userDetails, $node=null, $id=null) {
        $this->_session = $session;
         
        $this->_userDetails = $userDetails; 
        $this->_properties = array(); 
        $this->_store = $store;
        
        if (is_array($this->_userDetails)) {
            $this->fillProperties();
        }
        
        if ($id == null)
            $this->_id = $this->cm_name;
        else
            $this->_id = $id;

        if ($node == null ) {
            if (!empty($this->_id))
                $this->_node = $this->_session->getNode($store, $this->_id);
            else
                $this->_node = null;
        }
        else
            $this->_node = $node;
    }
    
    public function __get($name) {
        $fullName = $this->_session->namespaceMap->getFullName($name);
        //if ($fullName != $name)
        //{
            if (array_key_exists($fullName, $this->_properties) == true)
            {
                return $this->_properties[$fullName];
            }    
            else
            {    
                return null;    
            }     
        //}    
       // else
        //{
        //    return parent::__get($name);
        //}
    }
    
    public function __set($name, $value)
    {
        $fullName = $this->_session->namespaceMap->getFullName($name);
        //if ($fullName != $name)
        //{
            $this->_properties[$fullName] = $value;
        //}
        //else
        //{
        //    parent::__set($name, $value);
        //}
    }
    
    public function fillProperties() {
        if (is_array($this->_userDetails)) {
            foreach ($this->_userDetails as $prop) {
                $this->{$prop->name} = $prop->value;
                echo "{$prop->name} => {$prop->value}<br>";
                
            }
        }   
    }
    
    public static function cast(Node $nodeObject) {
        if ($nodeObject instanceof Node) {
            $properties = $nodeObject->getProperties();
            $UserDetail = new UserDetail($nodeObject->getSession(),$nodeObject->getStore(),array(),$nodeObject,$nodeObject->getId());

            foreach ($properties as $key => $value) {
                $UserDetail->{$key} = $value;
            }
            return $UserDetail;
        }        
    }
    
    public function getSession()
    {
        return $this->_session;
    }
  
    public function getStore() 
    {
        return $this->_store;
    }

    public function getId() 
    {
        return $this->_id;
    }
    
    public function getNode() 
    {
        return $this->_node;
    }   
    
    public function __toArray() 
    {
        return $this->_properties;
    } 
}    
    
    
?>