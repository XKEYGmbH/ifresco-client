<?php

namespace Ifresco\ClientBundle\Component\Alfresco;
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
 
use Ifresco\ClientBundle\Component\Alfresco\WebService\WebServiceFactory;
require_once 'WebService/WebServiceFactory.php';

class Administration extends BaseObject
{
    public $administrationService;
    private $_repository;
    private $_session;
    private $_store;
    private $_ticket;
    
    public function __construct($repository, $store, $session)
    {
        $this->_repository = $repository;
        $this->_store = $store;
        $this->_session = $session;
        $this->_ticket = $this->_session->getTicket();
        
        $this->administrationService = WebServiceFactory::getAdministrationService($this->_repository->connectionUrl, $this->_ticket);
    }

   public function queryUsers($user_name=null)
   {
        $filter = null;
        if ($user_name != null) {
            $filter = array("userName" => $user_name);   
        }

        $result = $this->administrationService->queryUsers(array("filter" => $filter));
        $resultSet = $result->result;     
        print_r($result);
        return $this->resultSetToUserDetails($this->_session, $this->_store, $resultSet);
   }
   
   
   public function getUser($user_name)
   {
        $result = $this->administrationService->getUser(array("userName" => $user_name));
        $resultSet = $result->result;   
        return $this->resultSetToUserDetails($this->_session, $this->_store, $resultSet);
   }
   
   
   public function createUser($userDetails)
   {
        $result = $this->administrationService->createUsers(array("newUsers" => $userDetails));
        $resultSet = $result->result;   
        return $this->resultSetToUserDetails($this->_session, $this->_store, $resultSet);
   }
   
   
   public function deleteUsers($user_name)
   {
        $result = $this->administrationService->deleteUsers(array("userNames" => $user_name));
        $resultSet = $result->result;   
        return $this->resultSetToUserDetails($this->_session, $this->_store, $resultSet);
   }
}