<?php
namespace Ifresco\ClientBundle\Component\Alfresco\Lib;

use Ifresco\ClientBundle\Component\Alfresco\SpacesStore;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTCategories;
use Ifresco\ClientBundle\Component\Alfresco\Lib\CacheObject;
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

class FunctionCache {
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
     }
     
     private function __clone(){}
     
     public function getCacheObj() {
         //$CacheDir = new sfFileCache(array('cache_dir' => sfConfig::get('sf_cache_dir').'/function/category','lifetime'=>600));
         $CacheDir = $this->container->get('cache');
         $CacheDir->setNamespace('function_cache.cache');

         $FunctionCache = new CacheObject($CacheDir);
         return $FunctionCache;
     }
     
     public function deleteCache($keyName="") {
         //FB::Log("remove");
         $this->getCacheObj()->remove('FunctionCache'.$keyName);
     }
     
     public function cleanDir() {
         if ($this->isCacheEnabled()) {
             $CacheDir = $this->container->get('cache');
             $CacheDir->setNamespace('function_cache.cache');
             $CacheDir->deleteAll();
         }
     }
     
     public function clean() {
         $this->cleanDir();
     }
     
     public function clear() {
         $this->cleanDir();
     }

     public function selectFromTable($pdo,$column,$table) {
        $sql = $pdo->query("SELECT $column FROM $table");      
        if ($sql) 
            $result = $this->getCacheObj()->call(array($sql,'fetchAll'), array(), 'FunctionCache.'.$table.'.'.$column);       
        else
            return false;  
       
        return $result;
     }

     public function selectDefinedQuery($pdo,$query) {
        $sql = $pdo->query($query);
         $filePost = str_replace(array('*'),'',$query);
        if ($sql)
            $result = $this->getCacheObj()->call(array($sql,'fetchAll'), array(), 'FunctionCache.'.$filePost);
        else
            return false;

        return $result;
     }

 }
?>