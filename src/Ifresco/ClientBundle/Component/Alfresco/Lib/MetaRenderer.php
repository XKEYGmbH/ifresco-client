<?php
namespace Ifresco\ClientBundle\Component\Alfresco\Lib;

use Ifresco\ClientBundle\Component\Alfresco\SpacesStore;
use Ifresco\ClientBundle\Component\Alfresco\Lib\MetaRenderer\AssocContentRenderer as AssocContentRenderer;
use Ifresco\ClientBundle\Component\Alfresco\Lib\MetaRenderer\BooleanRenderer;
use Ifresco\ClientBundle\Component\Alfresco\Lib\MetaRenderer\CategoryRenderer;
use Ifresco\ClientBundle\Component\Alfresco\Lib\MetaRenderer\DateRenderer;
use Ifresco\ClientBundle\Component\Alfresco\Lib\MetaRenderer\DateTimeRenderer;
use Ifresco\ClientBundle\Component\Alfresco\Lib\MetaRenderer\PersonRenderer;
use Ifresco\ClientBundle\Component\Alfresco\Lib\MetaRenderer\TagRenderer;
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
class MetaRenderer
{
    private static $instance = NULL;
    public static $userObject = NULL;
    public static $session = NULL;
    public static $repository = NULL;
    public static $ticket = NULL;
    public static $spacesStore = NULL;
    private static $doctrine = NULL;
    private static $clickSearchFields = NULL;
    private function __clone() {}
    private $propRenderer = array();
    public $dirToScan = "";

    private function __construct()
    {
        if (empty($this->dirToScan)) {
            //$this->dirToScan = sfConfig::get('sf_app_lib_dir').DIRECTORY_SEPARATOR.'MetaRenderer';
            $this->dirToScan = __DIR__ . DIRECTORY_SEPARATOR . 'MetaRenderer';
            $this->getClickSearchFields();
        }
    }

    public static function getInstance($userObject)
    {
       if (self::$instance === NULL) {
           self::$doctrine = $GLOBALS['kernel']->getContainer()->get('doctrine');
           self::$instance = new self;
           self::$userObject = $userObject;
           self::$session = $userObject->getSession();
           self::$repository = $userObject->getRepository();                 
           self::$ticket = $userObject->getTicket();                             
           self::$spacesStore = new SpacesStore(self::$session);             
       }
       return self::$instance;
    }
    
    public static function getUserObject()
    {
        if (self::$userObject != NULL) {
            return self::$userObject;
        }
        return null;
    }

    public function addPropertyRenderer($PropName,$RenderClass)
    {
        if (!isset($this->propRenderer[$PropName])) {
            $this->propRenderer[$PropName] = $RenderClass;
        }
    }
    
    public function getPropertyRenderer($PropName)
    {
         if (isset($this->propRenderer[$PropName])) {
            return $this->propRenderer[$PropName];
         } else {
            return null;
         }
    }
    
    public function getAssocRenderer($Type)
    {
         if (isset($this->propRenderer["type=" . $Type])) {
            return $this->propRenderer["type=" . $Type];
         } elseif (isset($this->propRenderer["type=*"])) {
            return $this->propRenderer["type=*"];
         } else {
            return null;
         }
    }
    
    public function getDataRenderer($Type)
    {
         if (isset($this->propRenderer["datatype=" . $Type])) {
            return $this->propRenderer["datatype=" . $Type];
         } else {
            return null;
         }
    }
    
    public function getClickSearchRenderer($PropName,$Label)
    {
        if ($this->searchNestedArray(self::$clickSearchFields, $PropName)) {
            $Class = $this->propRenderer["clicksearchrenderer"];
            $Class->PropName = $PropName;
            $Class->PropLabel = $Label;
            return $Class;
        } else {
            return null;
        }
    }
    
    public function scanRenderers()
    {
        if ($handle = opendir($this->dirToScan)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != ".." && !empty($file)) {
                    if (preg_match("/(.*?)\..*/eis", $file, $fileMatch)) {
                        $className = $fileMatch[1];  
                        if (empty($className)) {
                            continue;
                        } else {
                            $className = '\\Ifresco\\ClientBundle\\Component\\Alfresco\\Lib\\MetaRenderer\\'.$className;
                        }

                        $helper = new $className();
                        if ($helper instanceof InterfaceMetaRenderer) {
                            $propertyNames = $helper->getPropertyNames();
                            if (is_array($propertyNames)) {
                                for ($i = 0; $i < count($propertyNames); $i++) {
                                    $this->addPropertyRenderer($propertyNames[$i],$helper);
                                }
                            } else {
                                $this->addPropertyRenderer($propertyNames,$helper);          
                            }

                        }   
                    }
                }
            }
        }
    }

    private function getClickSearchFields()
    {
        if (self::$clickSearchFields == null) {
            $em = self::$doctrine->getManager();
            $query = $em->createQueryBuilder()->select('s')->from('IfrescoClientBundle:Setting', 's')
                ->where('s.key_string = :key_string')
                ->setParameter('key_string', 'ClickSearch');
            $ClickSearchSetting = $query->getQuery()->getOneOrNullResult();

            if ($ClickSearchSetting != null) {
                $JsonData = $ClickSearchSetting->getValuestring();
                $JsonData = json_decode($JsonData);
                self::$clickSearchFields = $JsonData?$JsonData:array();
            } else {
                self::$clickSearchFields = array();
            }
        }
    }
    
    private function searchNestedArray(array $array, $search, $mode = 'value')
    {
        foreach (new \RecursiveIteratorIterator(new \RecursiveArrayIterator($array)) as $key => $value) {
            if ($search === ${${"mode"}}) {
                return true;
            }
        }
        return false;
    }
}
