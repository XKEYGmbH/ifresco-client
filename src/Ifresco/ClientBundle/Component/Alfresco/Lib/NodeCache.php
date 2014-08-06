<?php

namespace Ifresco\ClientBundle\Component\Alfresco\Lib;

use Ifresco\ClientBundle\Component\Alfresco\Lib\CacheObject;
use Ifresco\ClientBundle\Component\Alfresco\Node;

/**
 * @package    AlfrescoClient
 * @author Dominik Danninger 
 *
 * ifresco Client
 * 
 * Copyright (c) 2013 X.KEY GmbH
 * 
 * This file is part of "ifresco Client".
 * 
 * "ifresco Client" is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * "ifresco Client" is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with "ifresco Client".  If not, see <http://www.gnu.org/licenses/>. (http://www.gnu.org/licenses/gpl.html)
 */

 class NodeCache {
     private static $instance = null;
     private $FunctionCache = null;
     private $CacheDir = null;
     private $EnabledCache = null;
     private $container = null;
  
     public static function getInstance()
     {
         if (null === self::$instance) {             
             self::$instance = new self;
         }
         return self::$instance;
     }
 
     private function __construct(){
         $this->container = $GLOBALS['kernel']->getContainer();
         $this->EnabledCache = $this->isCacheEnabled();
     }
     
     private function __clone(){}
     
     public function getNode($session, $store, $id) {
        if ($this->EnabledCache == true) {
            $FunctionCache = $this->getFunctionCache();
            $Node = $FunctionCache->call(array($session,'getNode'), array($store,$id), $id);

            if ($Node != null && $Node instanceof Node) {
                
                $Node->setSession($session);
                $Node->setStore($store);
            }
        }
        else
            $Node = $session->getNode($store,$id);

        return $Node;
     }
     
     public function getFormMetadata($restDict, $Node) {
         if ($this->EnabledCache == true) {
            $FunctionCache = $this->getFunctionCache('forms/');
            //$MetaForm = $restDict->GetFormdefinitions($Node->__toString());
            $MetaForm = $FunctionCache->call(array($restDict,'GetFormdefinitions'), array($Node->__toString()), $Node->getId());
         }
         else
            $MetaForm = $restDict->GetFormdefinitions($Node->__toString());
            
         return $MetaForm;
     }
     
     public function getRestNode($RestNode, $id) {

         if ($this->EnabledCache == true) {
            $FunctionCache = $this->getFunctionCache('rest/');
            $Node = $FunctionCache->call(array($RestNode,'GetNode'), array($id), $id);
         }
         else
            $Node = $RestNode->getNode($id);
         
         return $Node;
     }

     public function getRestPeoples($RestPeople) {
         if ($this->EnabledCache == true) {
         //todo enable cache
         $FunctionCache = $this->getFunctionCache('rest/');
         $Peoples = $FunctionCache->call(array($RestPeople,'GetPersons'), array(), "peoples");
         }
         else
            $Peoples = $RestPeople->GetPersons();

         return $Peoples;
     }
     
     public function clearNodeCache($id) {
        if ($this->EnabledCache == true) {
            $FunctionCache = $this->getFunctionCache();
            $FunctionCache->remove($id);
            $FunctionCache->remove($id.'.childs');
            $FunctionCache->remove('rest/'.$id);
            $FunctionCache->remove('forms/'.$id);
            $FunctionCache->remove('childs/'.$id);
            $FunctionCache->remove('queryparents/'.$id);
            $FunctionCache->remove('query/'.$id);
            $FunctionCache->remove('get/get'.$id);
            $FunctionCache->remove('get/'.$id);
        }
     }
     
     public function clear() {
        if ($this->EnabledCache == true) {
            $CacheDir = $this->getCacheDir();
            $CacheDir->deleteAll();
        }
     }
     
     public function get($repoService, $values, $id) {
         if ($this->EnabledCache == true) {
            $result = $this->getFunctionCache('get/')->call(array($repoService, 'get'), array($values), 'get'.$id);
         }
         else
            $result = $repoService->get($values);
         return $result;
     }
     
     public function queryParents($repoService, $values, $id) {
         if ($this->EnabledCache == true) {
             $parents = $this->getFunctionCache('queryparents/')->call(array($repoService, 'queryParents'), array($values), $id);
         }
         else {
             $parents = $repoService->queryParents($values);        
         }
         return $parents;
     }
     
     public function query($repoService, $values, $key) {
         if ($this->EnabledCache == true) {
             $query = $this->getFunctionCache('query/')->call(array($repoService, 'query'), array($values), $key);
         }
         else {
             $query = $repoService->query($values);        
         }
         return $query;
     }
     
     public function getChildren($Node, $id) {
         if ($this->EnabledCache == true) {
             $childs = $this->getFunctionCache('childs/')->call(array($Node, 'getChildren'), array(), $id);
         }
         else {
             //don't know where from $value should be taken
             //$childs = $Node->getChildren($values);
             $childs = $Node->getChildren();
         }
         return $childs;
     }
     
     public function clean() {
        $this->clear();
     }
     
     public function getCacheDir($add="") {
         //$CacheDir = new sfFileCache(array('cache_dir' => sfConfig::get('sf_cache_dir').'/function/nodes/'.$add,'lifetime'=>600));

         $CacheDir = $this->container->get('cache');
         $CacheDir->setNamespace(str_replace('/','_',$add).'.cache');

         return $CacheDir;
     }

     public function getFunctionCache($add="") {
         $CacheDir = $this->getCacheDir($add);
         $FunctionCache = new CacheObject($CacheDir);
         return $FunctionCache;
     }
     

     public function isCacheEnabled() {

         $em = $this->container->get('doctrine')->getManager();

         $query = $em->createQueryBuilder()->select('s')->from('IfrescoClientBundle:Setting', 's')
             ->where('s.key_string = :key_string')
             ->setParameter('key_string', 'NodeCache');

         $Setting = $query->getQuery()->getOneOrNullResult();


         if ($Setting != null) {
             $Val = $Setting->getValuestring();
             if ($Val == "true")
                 $CategoryCache = true;
             else
                 $CategoryCache = false;
         }
         else
             $CategoryCache = false;

         // TODO - REMOVE LATER / Disable for now
         $CategoryCache = false;
         return $CategoryCache;
     }
 }
?>