<?php
/*
 * Copyright (C) 2010 May Computer GmbH
 * 
 * Created by: Dominik Danninger <ddanninger@may.co.at>
 * 
 * Alfresco Inc 
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.

 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.

 * As a special exception to the terms and conditions of version 2.0 of 
 * the GPL, you may redistribute this Program in connection with Free/Libre 
 * and Open Source Software ("FLOSS") applications as described in Alfresco's 
 * FLOSS exception.  You should have recieved a copy of the text describing 
 * the FLOSS exception, and it is also available here: 
 * http://www.alfresco.com/legal/licensing"
 */
 
require_once 'WebService/WebServiceFactory.php';
require_once 'NamespaceMap.php';


class Dictionary extends BaseObject {
    
    public $dictionaryService;
    
    private $_repository;
    private $_session;
    private $_store;
    private $_ticket;
    
    public $namespaceMap;
    
    public function __construct($repository, $store, $session) {
        $this->_repository = $repository;
        $this->_store = $store;
        $this->_session = $session;
        $this->_ticket = $this->_session->getTicket();
        
        $this->namespaceMap = new NamespaceMap();
        
        try {
        $this->dictionaryService = WebServiceFactory::getDictionaryService($this->_repository->connectionUrl, $this->_ticket);
         }
       catch (SoapFault $e) {
           krumo($e);
       }
    }

   public function getClasses($types=false,$aspects=false) {
        $result = $this->dictionaryService->getClasses(array(
                            "types" => $types,
                            "aspects" => $aspects,
                            ));     
                                       
        $resultSet = $result->getClassesReturn;    
        return $resultSet; 
        //print_r($resultSet);
        //return $this->resultSetToUserDetails($this->_session,$this->_store,$resultSet);
   }
   
   public function getProperties($properties) {
       $properties = $this->checkFullNames($properties); 

       
       $result = $this->dictionaryService->getProperties(array(
                            "propertyNames" => $properties));     
       $resultSet = $result->getPropertiesReturn;  
                 
       return $resultSet; 
   }
   
   private function checkFullNames($properties) {
       
       if (count($properties) > 0) {
           foreach ($properties as $key => $value) { 
                
                if (!$this->namespaceMap->isShortName($value)) {
                    $value = str_replace("_", "-", $value);        
                    $properties[$key] = $this->namespaceMap->getShortName($value,":",false);   
                }
                else {
                    $value = str_replace("_", "-", $value);        
                    $properties[$key] = $value;       
                }    
           }          
       }
       return $properties;
   }

    
}
?>