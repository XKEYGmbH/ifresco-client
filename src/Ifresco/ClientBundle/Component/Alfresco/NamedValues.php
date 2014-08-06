<?php
class NamedValues {
    private $_properties;
    private $_session;
    
    public function __construct($session) {
        $this->_session = $session;    
    }
    
    public function __get($name) {
        $fullName = $this->_session->namespaceMap->getFullName($name);
        if (array_key_exists($fullName, $this->_properties) == true)
        {
            return $this->_properties[$fullName];
        }    
        else
        {    
            return null;    
        }     
    }
    
    public function __set($name, $value)
    {
        $fullName = $this->_session->namespaceMap->getFullName($name);
        $this->_properties[$fullName] = $value;
    }
    
    public function __toArray() 
    {
        $tempArray = array();
        if (count($this->_properties) > 0) {
            foreach ($this->_properties as $key => $value) {
                $isMultiValue = false;
                if (is_array($value))
                    $isMultiValue = true;
                $tempArray[] = array("name"=>$key,"value"=>$value,"isMultiValue"=>$isMultiValue);    
            }
        }
        return $tempArray;
    }
}