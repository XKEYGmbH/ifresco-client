<?php
namespace Ifresco\ClientBundle\Component\Alfresco;

use Ifresco\ClientBundle\Component\Alfresco\Lib\NodeCache;
/*
 * Copyright (C) 2005-2007 Alfresco Software Limited.
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

class BaseObject
{
    public function __get($name)
    {
        $methodName = $name;
        $methodName[0] = strtoupper($methodName[0]);
        $methodName = 'get' . $methodName;

        if (method_exists($this, $methodName) == true) {
            return $this->$methodName();
        }
    }

    public function __set($name, $value)
    {
        $methodName = $name;
        $methodName[0] = strtoupper($methodName[0]);
        $methodName = 'set' . $methodName;

        if (method_exists($this, $methodName) == true){
            return $this->$methodName($value);
        }
    }

    protected function resultSetToNodes($session, $store, $resultSet, $mapColumns = false)
    {
        //$CacheDir = new sfFileCache(array('cache_dir' => sfConfig::get('sf_cache_dir').'/function/nodes/','lifetime'=>600));
        //$FunctionCache = new CacheObject($CacheDir);

        $return = array();

        if (isset($resultSet->rows) == true) {
            if (is_array($resultSet->rows) == true) {
                foreach($resultSet->rows as $row) {
                    $id = $row->node->id;
                    //$Node = $FunctionCache->call(array($session,'getNode'), array($store,$id), $id);
                    $Node = $session->getNode($store, $id);

                    if (count($row->columns) > 0 && $mapColumns == true) {
                        $propArray = array();
                        foreach ($row->columns as $col) {
                            $name = $col->name;
                            $isMultiValue = $col->isMultiValue;
                            $value = null;
                            if ($isMultiValue == false) {
                                $value = $col->value;
                                if ($this->isContentData($value) == true) {
                                    $value = new ContentData($Node, $name);
                                }
                            } else {
                                $value = $col->values;
                            }
                            $propArray[$name] = $value;
                        }

                        $Node->setType($row->node->type);
                        $Node->setPropertyValues($propArray, false);
                    }
                    $return[] = $Node;
                }

            } else {
                $id = $resultSet->rows->node->id;
                $return[] = $session->getNode($store, $id);
                //$return[] = $FunctionCache->call(array($session,'getNode'), array($store,$id), $id);
            }
        }

        return $return;
    }
    
    protected function cachedResultSetToNodes($session, $store, $resultSet,$mapColumns=false)
    {
        //$CacheDir = new sfFileCache(array('cache_dir' => sfConfig::get('sf_cache_dir').'/function/nodes/','lifetime'=>600));
        //$CacheDir = new sfFileCache(array('cache_dir' => sfConfig::get('sf_cache_dir').'/function/nodes/'));
        //$FunctionCache = new CacheObject($CacheDir);
        
        $return = array();        
        
        if (isset($resultSet->rows) == true) {
            if (is_array($resultSet->rows) == true) {
                foreach($resultSet->rows as $row) {
                    $id = $row->node->id;
                    /*$Node = $FunctionCache->call(array($session,'getNode'), array($store,$id), $id);
                    $Node->setSession($session);
                    $Node->setStore($store);*/
                    $Node = NodeCache::getInstance()->getNode($session, $store, $id);
                    //var_dump($Node);
                    //$Node = $session->getNode($store, $id);            
                    
                    if (count($row->columns) > 0 && $mapColumns == true) {
                        $propArray = array();
                        foreach ($row->columns as $col) {
                            $name = $col->name;
                            $isMultiValue = $col->isMultiValue;
                            $value = null;
                            if ($isMultiValue == false) {
                                $value = $col->value;
                                if ($this->isContentData($value) == true) {
                                    $value = new ContentData($Node, $name);
                                }
                            } else {
                                $value = isset($col->values)?$col->values:'';
                            }

                            $propArray[$name] = $value;
                        }
                        //$Node->setType($row->node->type);
                        //$Node->setPropertyValues($propArray,false);
                    }
                    $return[] = $Node;
                }
            } else {
                $id = isset($resultSet->rows->node->id) ? $resultSet->rows->node->id : false;
                //$return[] = $session->getNode($store, $id);
                /*$Node = $FunctionCache->call(array($session,'getNode'), array($store,$id), $id);
                $Node->setSession($session);
                $Node->setStore($store);*/
                $Node = NodeCache::getInstance()->getNode($session, $store, $id);
                $return[] = $Node;
            }
        }
        
        return $return;
    }

    protected function resultSetToMap($resultSet)
    {
        $return = array();
        if (isset($resultSet->rows) == true) {
            if (is_array($resultSet->rows) == true) {
                foreach($resultSet->rows as $row) {
                    $return[] = $this->columnsToMap($row->columns);
                }
            } else {
                $return[] = $this->columnsToMap($resultSet->rows->columns);
            }
        }

        return $return;
    }
    
    protected function resultSetToUserMap($resultSet)
    {
        $return = array();    
        if (isset($resultSet->userDetails) == true) {
            if (is_array($resultSet->userDetails) == true) {
                foreach($resultSet->userDetails as $row) {
                    $return[] = $this->columnsToMap($row->properties);                
                }
            } else {
                $return[] = $this->columnsToMap($resultSet->userDetails->properties);
            }
        }
        
        return $return;    
    }
    
    protected function resultSetToUserDetails($session, $store, $resultSet)
    {
        $return = array();        
        if (isset($resultSet->userDetails) == true) {
            if (is_array($resultSet->userDetails) == true) {
                foreach($resultSet->userDetails as $row) {
                    //$id = $row->properties["{http://www.alfresco.org/model/content/1.0}name"];
                    $UserDetails = $session->getUserDetail($store,$row->properties);  
                    $return[] = $UserDetails;
                }
            } else {
                $return[] = $session->getUserDetail($store,$resultSet->userDetails->properties);
            }
        } else if (isset($resultSet->properties) == true) {
            if (is_array($resultSet->properties) == true) {
                $return = $session->getUserDetail($store,$resultSet->properties);    
            }  
        }
        
        return $return;
    }

    private function columnsToMap($columns)
    {
        $return = array();

        foreach ($columns as $column) {
            $return[$column->name] = $column->value;
        }

        return $return;
    }

    protected function remove_array_value($value, &$array)
    {
        if ($array != null) {
            if (in_array($value, $array) == true) {
                foreach ($array as $index=>$value2) {
                    if ($value == $value2) {
                        unset($array[$index]);
                    }
                }
            }
        }
    }

    protected function isContentData($value)
    {
        $index = strpos($value, "contentUrl=");
        if ($index === false) {
            return false;
        } else {
            if ($index == 0) {
                return true;
            } else {
                return false;
            }
        }
    }
}