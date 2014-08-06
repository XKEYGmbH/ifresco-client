<?php
namespace Ifresco\ClientBundle\Component\Alfresco\Lib;

use Ifresco\ClientBundle\Component\Alfresco\SpacesStore;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTCategories;
use Ifresco\ClientBundle\Component\Alfresco\Lib\CategoryCacheObject;
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

 class CategoryCache {
     private static $instance = null;
     private $userObj = null;
     private $container = null;

     public static function getInstance($userObj)
     {
         if (null === self::$instance) {             
             self::$instance = new self($userObj);
         }
         return self::$instance;
     }
 
     private function __construct($userObj){
         $this->userObj = $userObj;
         $this->container = $GLOBALS['kernel']->getContainer();
         $this->container->get('session')->set('userObj', $this->userObj);
     }
     
     private function __clone(){}
     
     public function getCacheObj() {
         //$CacheDir = new sfFileCache(array('cache_dir' => sfConfig::get('sf_cache_dir').'/function/category','lifetime'=>600));
         $CacheDir = $this->container->get('cache');
         $CacheDir->setNamespace('function_category.cache');

         $FunctionCache = new CategoryCacheObject($CacheDir);
         return $FunctionCache;
     }
     
     public function deleteCache($keyName="") {
         //FB::Log("remove");
         $this->getCacheObj()->remove('CategoryCache'.$keyName);
     }
     
     public function cleanDir() {
         if ($this->isCacheEnabled()) {
             $CacheDir = $this->container->get('cache');
             $CacheDir->setNamespace('function_category.cache');
             $CacheDir->deleteAll();
         }
     }
     
     public function clean() {
         $this->cleanDir();
     }
     
     public function clear() {
         $this->cleanDir();
     }

     private $restCategories = null;
     public function getCachedCategories($categoryName) {
         if ($this->restCategories == null) {
             $user = $this->container->get('session')->get('userObj');
             $repository = $user->getRepository();
             $session = $user->getSession();
             $ticket = $user->getTicket();
             
             $spacesStore = new SpacesStore($session);
             
             $this->restCategories = new RESTCategories($repository,$spacesStore,$session);
         }
         
         if ($this->isCacheEnabled())
            $categories = $this->getCacheObj()->call(array($this->restCategories,'GetCategories'), array($categoryName), 'CategoryCache'.$categoryName);
         else
            $categories = $this->restCategories->GetCategories($categoryName);
         return $categories;
     }
     
     public function getCachedNode($store, $uuid) {
         $user = $this->container->get('session')->get('userObj');
         $repository = $user->getRepository();
         $session = $user->getSession();
         $ticket = $user->getTicket();
         
         if ($this->isCacheEnabled()) {
            $CategoryNode = $this->getCacheObj()->call(array($session,'getNode'), array($store, $uuid), 'CategoryNode/'.$uuid);
         }
         else {
            $CategoryNode = $session->getNode($store, $uuid);
         }
         return $CategoryNode;
     }
     
     public function getCachedChildren($store, $uuid) {
         $user = $this->container->get('session')->get('userObj');
         $repository = $user->getRepository();
         $session = $user->getSession();
         $ticket = $user->getTicket();
         
         //$CategoryNode = $session->getNode($store, $uuid);
         $CategoryNode = $this->getCachedNode($store, $uuid);
         
         if ($this->isCacheEnabled()) {
            $Childs = $this->getCacheObj()->call(array($CategoryNode,'getChildren'), array(), 'CategoryNode/'.$uuid.'children');
         }
         else {
            $Childs = $CategoryNode->getChildren();
         }
         return $Childs;
     }
     
     /*public function getCategories($categoryName) {
         FB::Log("get categories");
         $user = sfContext::getInstance()->get('userObj');
         $repository = $user->getRepository();
         $session = $user->getSession();
         $ticket = $user->getTicket();

         $spacesStore = new SpacesStore($session);

         $categoryName = str_replace("%2520","%20",$categoryName);

         if ($categoryName == "root" || empty($categoryName)) {
            $categoryName = "";
            $breadCrumb = "";
         }
         else
            $breadCrumb = urldecode($categoryName)."/";     
        
         //FB::log($categoryName);
         $restCategories = new RESTCategories($repository,$spacesStore,$session);
         
         //$function_cache_dir = new sfFileCache(array('cache_dir' => sfConfig::get('sf_cache_dir').'/function'));
         //$FunctionCache = new sfFunctionCache($function_cache_dir);
         $categories = $this->getCacheObj()->call(array($restCategories,'GetCategories'), array($categoryName), 'CategoryCache'.$categoryName);
         
         
         //$categories = $restCategories->GetCategories($categoryName);
         $content = ""; 
         $array = array(); 
         
         $iconClasses = array("tag_green","tag_orange","tag_pink","tag_purple","tag_yellow");
        
        if (count($categories->items) > 0) {
            $match = array();
            $count = preg_match_all("#/#eis",$breadCrumb,$match);
            if ($count > 4)
                $iconCls = "tag_red";
            else
                $iconCls = $iconClasses[$count];  
            foreach ($categories->items as $item) {
                $arrVal = array("cls"=>"folder",
                 "id"=>str_replace(" ","%20",$breadCrumb.$item->name), 
                 "nodeId"=>str_replace("workspace://SpacesStore/","",$item->nodeRef),
                 "leaf"=>($item->hasChildren == true ? false : true),
                 "iconCls"=>'category_'.$iconCls,
                 "text"=>$item->name,
                 "qtip"=>$item->description);
                $array[] = $arrVal;
            }    
        }
        return $array;
     }
     
     public function readCategories($categoryName) {
         
         $function_cache_dir = new sfFileCache(array('cache_dir' => sfConfig::get('sf_cache_dir').'/function'));
         $FunctionCache = new sfFunctionCache($function_cache_dir);
         //$CategoryResult = $FunctionCache->call(array('CategoryCache','getCategories'), array($categoryName));
         $CategoryResult = $this->getCategories($categoryName);
         return $CategoryResult;
     }
     

     public function cacheRecursive() {
         sfContext::getInstance()->set('userObj', $this->userObj);
         $function_cache_dir = new sfFileCache(array('cache_dir' => sfConfig::get('sf_cache_dir').'/function'));
         $fc = new sfFunctionCache($function_cache_dir);
         $CategoryResult = $fc->call(array('CategoryCache','readRecursive'), array());
         FB::Log($CategoryResult);
return;
        $user = $this->userObj;
        $repository = $user->getRepository();
        $session = $user->getSession();
        $ticket = $user->getTicket();

        $spacesStore = new SpacesStore($session);
        $categoryName = "";
        $breadCrumb = "";
        
        $this->restCategories = new RESTCategories($repository,$spacesStore,$session);
        $categories = $this->restCategories->GetCategories($categoryName);
        $content = ""; 
        $array = array(); 
        
        $iconClasses = array("tag_green","tag_orange","tag_pink","tag_purple","tag_yellow");
        
        if (count($categories->items) > 0) {
            $match = array();
            $count = preg_match_all("#/#eis",$breadCrumb,$match);
            if ($count > 4)
                $iconCls = "tag_red";
            else
                $iconCls = $iconClasses[$count];  
                
            foreach ($categories->items as $item) {
                 if ($item->hasChildren == true) {
                     $children = $this->readRecursiveCategory($item->name,$breadCrumb.$item->name,$DefaultValues);     
                     $arrVal["children"] = $children["items"];  
                     if ($children["found"] == true)   
                        $arrVal["expanded"] = true;          
                 }

                
                $arrayChilds[] = $arrVal;
            }    
        }
        //sfContext::getInstance()->set('Category->Root', $array);
        //sfContext::getInstance()->set('Category->RootChilds', $arrayChilds);
        FB::Log(sfContext::getInstance()->get('Category->RootChilds'));
        FB::log($array);
     }
     
     public function readRecursive() {
         FB::Log("read rec");
        //$user = $this->userObj;
        $user = sfContext::getInstance()->get('userObj');
        $repository = $user->getRepository();
        $session = $user->getSession();
        $ticket = $user->getTicket();

        $spacesStore = new SpacesStore($session);
        $categoryName = "";
        $breadCrumb = "";
        
        $restCategories = new RESTCategories($repository,$spacesStore,$session);
        sfContext::getInstance()->set('RestCategories',$restCategories);
        $categories = $restCategories->GetCategories($categoryName);
        $content = ""; 
        $array = array(); 
        
        $iconClasses = array("tag_green","tag_orange","tag_pink","tag_purple","tag_yellow");
        
        if (count($categories->items) > 0) {
            $match = array();
            $count = preg_match_all("#/#eis",$breadCrumb,$match);
            if ($count > 4)
                $iconCls = "tag_red";
            else
                $iconCls = $iconClasses[$count];  
                
            foreach ($categories->items as $item) {
                $nodeId = str_replace("workspace://SpacesStore/","",$item->nodeRef);
                $checked = false;
                //if (in_array($item->nodeRef,$DefaultValues)) {
                //    FB::Log("Found -> ".$item->name);
                //    $checked = true;    
                //}
                 
                $arrVal = array("cls"=>"folder",
                 "id"=>str_replace(" ","%20",$breadCrumb.$item->name),
                 "nodeId"=>$nodeId,
                 //"checked"=>$checked,
                 //"expanded"=>$checked,
                 "leaf"=>($item->hasChildren == true ? false : true),
                 "iconCls"=>'category_'.$iconCls,
                 "text"=>$item->name,
                 "qtip"=>$item->description);
                 
                 $array[] = $arrVal;
                 if ($item->hasChildren == true) {
                     //$children = $this->readRecursiveCategory($item->name,$breadCrumb.$item->name,$DefaultValues);     
                     $children = CategoryCache::getInstance($user)->readRecursiveCategory($item->name,$breadCrumb.$item->name,$DefaultValues);
                     $arrVal["children"] = $children["items"];  
                     if ($children["found"] == true)   
                        $arrVal["expanded"] = true;          
                 }

                
                $arrayChilds[] = $arrVal;
            }    
        }
        return $array;
     }
     
     public function readRecursiveCategory($categoryName,$breadCrumb,$DefaultValues) {
        $searchCat = $breadCrumb;
        $searchCat = str_replace(" ","%20",$searchCat);        
        $breadCrumb = urldecode($breadCrumb)."/";

        //$restCategories = $this->restCategories;
        $restCategories = sfContext::getInstance()->get('RestCategories');
        $categories = $restCategories->GetCategories($searchCat);    
        
        
        $array = array("items"=>array(),"found"=>false); 
            
        $iconClasses = array("tag_green","tag_orange","tag_pink","tag_purple","tag_yellow");
        if (count($categories->items) > 0) {
            $match = array();
            $count = preg_match_all("#/#eis",$breadCrumb,$match);
            if ($count > 4)
                $iconCls = "tag_red";
            else
                $iconCls = $iconClasses[$count];  

            foreach ($categories->items as $item) {
                    $nodeId = str_replace("workspace://SpacesStore/","",$item->nodeRef);
                    $checked = false;

                    $arrVal = array("cls"=>"folder",
                     "id"=>str_replace(" ","%20",$breadCrumb.$item->name),
                     "nodeId"=>$nodeId,
                     //"checked"=>$checked,
                     //"expanded"=>$checked,
                     "leaf"=>($item->hasChildren == true ? false : true),
                     "iconCls"=>'category_'.$iconCls,
                     "text"=>$item->name,
                     "qtip"=>$item->description);
                     
                     if ($item->hasChildren == true) {
                         $children = $this->readRecursiveCategory($item->name,$breadCrumb.$item->name,$DefaultValues);     
                         $arrVal["children"] = $children["items"];  
                         if ($children["found"] == true) {
                            $arrVal["expanded"] = true;   
                            $array["found"] = true;
                         }       
                     }

                    $array["items"][] = $arrVal;
            }    
        }  

        sfContext::getInstance()->set('Category->'.$breadCrumb, $array);
        //FB::Log(sfContext::getInstance()->get('Category->'.$breadCrumb));
        return $array;            
     }
     */
     
     public function isCacheEnabled() {

         $em = $this->container->get('doctrine')->getManager();

         $query = $em->createQueryBuilder()->select('s')->from('IfrescoClientBundle:Setting', 's')
             ->where('s.key_string = :key_string')
             ->setParameter('key_string', 'CategoryCache');

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