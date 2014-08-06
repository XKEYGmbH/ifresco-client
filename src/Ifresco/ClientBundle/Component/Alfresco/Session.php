<?php
namespace Ifresco\ClientBundle\Component\Alfresco;

use Ifresco\ClientBundle\Component\Alfresco\WebService\WebServiceFactory;
use Ifresco\ClientBundle\Component\Alfresco\Lib\NodeCache;

/*
 * Copyright (C) 2005 Alfresco, Inc.
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
 
require_once 'Store.php';
require_once 'Node.php';
require_once 'UserDetail.php';
require_once 'WebService/WebServiceFactory.php';

class Session extends BaseObject
{
    public $authenticationService;
    public $repositoryService;
    public $contentService;

    private $_repository;
    private $_ticket;
    private $_stores;
    private $_namespaceMap;
    
    private $nodeCache;
    private $idCount = 0;

    /**
     * Constructor
     * 
     * @param userName the user name
     * @param ticket the currenlty authenticated users ticket
     */
    public function __construct($repository, $ticket)  
    {
        $this->nodeCache = array();
        
        $this->_repository = $repository;
        $this->_ticket = $ticket;
        
        $this->repositoryService = WebServiceFactory::getRepositoryService($this->_repository->connectionUrl, $this->_ticket);
        $this->contentService = WebServiceFactory::getContentService($this->_repository->connectionUrl, $this->_ticket);
    }
    
    private $lang = "en-us";
    public function setLanguage($langKey) {
        $langKey = str_replace("_","-",$langKey);
        $langKey = strtolower($langKey);
        $this->lang = $langKey;
    }
    
    public function getLanguage() {
        return $this->lang;
    }
    
    /**
     * Creates a new store in the current respository
     * 
     * @param $address the address of the new store
     * @param $scheme the scheme of the new store, default value of 'workspace'
     * @return Store the new store
     */
    public function createStore($address, $scheme="workspace")
    {
        // Create the store
        $result = $this->repositoryService->createStore(array(
                                                    "scheme" => $scheme,
                                                    "address" => $address));
        $store = new Store($this, $result->createStoreReturn->address, $result->createStoreReturn->scheme);                                            
        
        // Add to the cached list if its been populated
        if (isset($this->_stores) == true)
        {
            $this->_stores[] = $store;
        }    
        
        // Return the newly created store
        return $store;
    }
    
    /**
     * Get the store
     * 
     * @param $address the address of the store
     * @param $scheme the scheme of the store.  The default it 'workspace'
     * @return Store the store
     */
    public function getStore($address, $scheme="workspace")
    {
        return new Store($this, $address, $scheme);    
    }
    
    /**
     * Get the store from it string representation (eg: workspace://SpacesStore)
     * 
     * @param $value the stores string representation
     * @return Store the store
     */
    public function getStoreFromString($value)
    {
        list($scheme, $address) = split("://", $value);
        return new Store($this, $address, $scheme);        
    }    
    
    public function getUserDetail($store, $userDetails)
    {
        $userDetail = new UserDetail($this, $store, $userDetails);    
        return $userDetail;
    }
    
    public function getNode($store, $id)
    {
        $node = $this->getNodeImpl($store, $id);
        if ($node == null)
        {
            print_R($node);
            $node = new Node($this, $store, $id);
            $this->addNode($node);
        }        
        return $node;
    }
    
    public function getNodeFromString($value)
    {
        // TODO
        throw Exception("getNode($value) no yet implemented");
    }
    
    /**
     * Adds a new node to the session.
     */
    public function addNode($node)
    {
        $this->nodeCache[$node->__toString()] = $node;
    }
    
    private function getNodeImpl($store, $id)
    {        
        $result = null;
        
        $nodeRef = $store->scheme . "://" . $store->address . "/" . $id;
        
        if (array_key_exists($nodeRef, $this->nodeCache) == true)
        {
            $result = $this->nodeCache[$nodeRef];
        }
        return $result;
    }

    /**
     * Commits all unsaved changes to the repository
     */
    public function save($debug=false)
    {
        // Build the update statements from the node cache
        $statements = array();
        foreach ($this->nodeCache as $node)
        {
            $node->onBeforeSave($statements);
        }
        
        if ($debug == true)
        {
            var_dump($statements);
            echo ("<br><br>");
        }
        
        if (count($statements) > 0)
        {
            // Make the web service call
            $result = $this->repositoryService->update(array("statements" => $statements));
            //var_dump($result);

            // Update the state of the updated nodes
            foreach ($this->nodeCache as $node)
            {
                $node->onAfterSave($this->getIdMap($result));
            }
        }
    }
    
    /**
     * Clears the current session by emptying the node cache.
     * 
     * WARNING:  all unsaved changes will be lost when clearing the session.
     */
    public function clear()
    {
        // Clear the node cache
        $this->nodeCache = array();    
    }
    
    private function getIdMap($result)
    {
        $return = array();
        if(!$result) return array();

        $statements = $result->updateReturn;
        if (is_array($statements) == true)
        {
            foreach ($statements as $statement)
            {
                if ($statement->statement == "create")
                {
                    $id = $statement->sourceId;
                    $uuid = $statement->destination->uuid;
                    $return[$id] = $uuid;
                }
            }    
        }    
        else
        {
            if ($statements->statement == "create")
                {
                    $id = $statements->sourceId;
                    $uuid = $statements->destination->uuid;
                    $return[$id] = $uuid;
                }    
        }    
        return $return;    
    }
    
    private $querySession = null;
    public function getQuerySession() {
        return $this->querySession;
    }
    
    public function fetchMore($store, $querySession) {
        //FB::Log("SESSION ".$querySession);
        $result = $this->repositoryService->fetchMore(array(
                    "querySession" => $querySession));               
                    
        $resultSet = $result->queryReturn->resultSet;   
        $this->querySession =  $result->queryReturn->querySession;     
        return $this->resultSetToNodes($this, $store, $resultSet);
    }
    
    public function query($store, $query, $language='lucene', $maxArgs=0)
    {
        // TODO need to support paged queries
        /*$result = $this->repositoryService->query(array(
                    //"fetchSize" => $maxArgs,
                    "store" => $store->__toArray(),
                    "query" => array(
                        "language" => $language,
                        "statement" => $query),
                    "includeMetaData" => false));    */

        //$CacheDir = new sfFileCache(array('cache_dir' => sfConfig::get('sf_cache_dir').'/function/nodes/'));
        //$FunctionCache = new CacheObject($CacheDir);
        
        
        //$result = NodeCache::getInstance()->getFunctionCache('query/')->call(array($this->repositoryService,'query'), array(array(

        $result = NodeCache::getInstance()->query($this->repositoryService, array(
                //"fetchSize" => $maxArgs,
                "store" => $store->__toArray(),
                "query" => array(
                    "language" => $language,
                    "statement" => $query),
                "includeMetaData" => false), "query".md5($query)); 
                        
        $this->_lastQueryCount = isset($result->queryReturn->resultSet->rows)?count($result->queryReturn->resultSet->rows):0;
        // TODO for now do nothing with the score and the returned data               
        $resultSet = $result->queryReturn->resultSet;   
        //FB::Log($resultSet);   
        $this->querySession =  $result->queryReturn->querySession;   
        return $this->resultSetToNodes($this, $store, $resultSet);
        //return $this->cachedResultSetToNodes($this, $store, $resultSet);
    }
    
    private function sort_by_old($field, &$arr, $sorting=SORT_ASC, $case_insensitive=true){

        if(is_array($arr) && (count($arr)>0) && ( ( is_array($arr[0]) && isset($arr[0]["columns"][$field]) ) || ( is_object($arr[0]) && isset($arr[0]->columns->$field) ) ) ){

            if($case_insensitive==true) 
                $strcmp_fn = "strnatcasecmp";
            else 
                $strcmp_fn = "strnatcmp";

            if($sorting==SORT_ASC){
                $fn = create_function('$a,$b', '
                    if(is_object($a) && is_object($b)){
                        return '.$strcmp_fn.'($a->'.$field.', $b->'.$field.');
                    }else if(is_array($a) && is_array($b)){
                        return '.$strcmp_fn.'($a["'.$field.'"], $b["'.$field.'"]);
                    }else return 0;
                ');
            }else{
                $fn = create_function('$a,$b', '
                    if(is_object($a) && is_object($b)){
                        return '.$strcmp_fn.'($b->'.$field.', $a->'.$field.');
                    }else if(is_array($a) && is_array($b)){
                        return '.$strcmp_fn.'($b["'.$field.'"], $a["'.$field.'"]);
                    }else return 0;
                ');
            }
            usort($arr, $fn);
            return true;
        }else{
            return false;
        }
    }
    
    

    
    private function sort_by($field, &$arr, $sorting=SORT_ASC, $case_insensitive=true){
        if(is_array($arr) && (count($arr)>0)) {
            if($case_insensitive==true) 
                $strcmp_fn = "strnatcasecmp";
            else 
                $strcmp_fn = "strnatcmp";
            
            // TODO - isMultiValue - to check array
            if($sorting==SORT_ASC){
                $fn = create_function('$a,$b', '
                            $searchArrayA = \Ifresco\ClientBundle\Component\Alfresco\Session::search($a->columns,"name","'.$field.'");
                            $valueA = isset($searchArrayA[0])?$searchArrayA[0]->value:"";
                            $searchArrayB = \Ifresco\ClientBundle\Component\Alfresco\Session::search($b->columns,"name","'.$field.'");
                            $valueB = isset($searchArrayB[0])?$searchArrayB[0]->value:"";
                            if (($valueA == null || empty($valueA)) && ($valueB == null || empty($valueB))) {
                                return 0;
                            }
                            else if (($valueA == null || empty($valueA)) && ($valueB != null || !empty($valueB))) {
                                return 1;
                            }
                            else if (($valueA != null || !empty($valueA)) && ($valueB == null || empty($valueB))) {
                                return -1;
                            }
                            else
                                return '.$strcmp_fn.'($valueA, $valueB);
                    ');
            }
            else {
                $fn = create_function('$a,$b', '
                            $searchArrayA = \Ifresco\ClientBundle\Component\Alfresco\Session::search($a->columns,"name","'.$field.'");
                            $valueA = isset($searchArrayA[0])?$searchArrayA[0]->value:"";
                            $searchArrayB = \Ifresco\ClientBundle\Component\Alfresco\Session::search($b->columns,"name","'.$field.'");
                            $valueB = isset($searchArrayB[0])?$searchArrayB[0]->value:"";
                            return '.$strcmp_fn.'($valueB, $valueA);
                    ');    
            }

            usort($arr, $fn);
        }
    }
    
    public static function search($array, $key, $value)
    {
        $results = array();

        if (is_array($array))
        {
            if (isset($array[$key]) && $array[$key] == $value)
                $results[] = $array;

            foreach ($array as $subarray)
                $results = array_merge($results, Session::search($subarray, $key, $value));
        }
        else if (is_object($array))
        {
            if ($array->$key == $value)
                $results[] = $array;

            foreach ($array as $subarray)
                $results = array_merge($results, Session::search($subarray, $key, $value));
        }

        return $results;
    }
    
    private $_lastQueryCount = 0;
    public function filteredQuery($store, $query, $maxArgs=0, $start=0, $sortField="cm_name", $sorting="ASC", $language='lucene')
    {
        
        $sortField = $this->namespaceMap->getFullName($sortField);
        if ($sorting == "ASC")
            $sorting = SORT_ASC;
        else
            $sorting = SORT_DESC;

        $result = $this->repositoryService->query(array(
        //$result = NodeCache::getInstance()->getFunctionCache('query/')->call(array($this->repositoryService,'query'), array(array(
                    "store" => $store->__toArray(),
                    "query" => array(
                        "language" => $language,
                        "statement" => $query),
                    "includeMetaData" => true));
        //);     

        $resultSetRows = isset($result->queryReturn->resultSet->rows)?$result->queryReturn->resultSet->rows:null;

        $this->sort_by($sortField,$resultSetRows,$sorting);
        $this->_lastQueryCount = isset($result->queryReturn->resultSet->rows)?count($result->queryReturn->resultSet->rows):0;
        
        if ($maxArgs > 0) {
            if (count($resultSetRows) > $maxArgs) {
                
                $resultSetRows = array_splice($resultSetRows,$start,$maxArgs);
                
            }
        }
        if (isset($result->queryReturn->resultSet)) {
            $result->queryReturn->resultSet->rows = $resultSetRows;
        }   
        else {
            $result = new \stdClass();
            $result->queryReturn = new \stdClass();
            $result->queryReturn->resultSet = new \stdClass();
            $result->queryReturn->resultSet->rows = array();
        }
        $resultSet = $result->queryReturn->resultSet;     
        return $this->cachedResultSetToNodes($this, $store, $resultSet,true);
        //return $this->resultSetToNodes($this, $store, $resultSet,true);
    }
    
    public function getLastQueryCount() {
        return $this->_lastQueryCount;
    }

    public function getTicket()
    {
        return $this->_ticket;
    }

    public function getRepository()
    {
        return $this->_repository;
    }
    
    public function getNamespaceMap()
    {
        if ($this->_namespaceMap == null)
        {
            $this->_namespaceMap = NamespaceMap::getInstance();
        }
        return $this->_namespaceMap;
    }

    public function getStores()
    {
        if (isset ($this->_stores) == false)
        {
            $this->_stores = array ();
            $results = $this->repositoryService->getStores();

            foreach ($results->getStoresReturn as $result)
            {
                $this->_stores[] = new Store($this, $result->address, $result->scheme);
            }
        }

        return $this->_stores;
    }
    
    /** Want these methods to be package scope some how! **/
    
    public function nextSessionId()
    {
        $sessionId = "session".$this->_ticket.$this->idCount;
        $this->idCount ++;
        return $sessionId;
    }
}
?>
