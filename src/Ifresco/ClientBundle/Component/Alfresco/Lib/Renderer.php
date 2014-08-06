<?php
namespace Ifresco\ClientBundle\Component\Alfresco\Lib;

use Doctrine\ORM\EntityManager;
use Ifresco\ClientBundle\Component\Alfresco\Lib\ViewRenderer;
use Ifresco\ClientBundle\Component\Alfresco\Lib\ViewRenderer\JavascriptPDFRenderer;
use Ifresco\ClientBundle\Entity\Setting;

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
class Renderer
{
    public $defaultRenderClass = "AlfrescoRenderer";
    public $dirToScan = "";
    
    private static $instance = NULL;
    private $mimeTypeRenderer = array();
    private $mimeTypeRendererList = array();
    private $restricted = array();
    
    private function __clone() {}

    private function __construct()
    {
        if (empty($this->dirToScan)) {
            //$this->dirToScan = sfConfig::get('sf_app_lib_dir').DIRECTORY_SEPARATOR.'ViewRenderer';
            $this->dirToScan = __DIR__ . DIRECTORY_SEPARATOR . 'ViewRenderer';
        }
    }

    public static function getInstance()
    {
       if (self::$instance === NULL) {
           self::$instance = new self;
       }
       return self::$instance;
    }

    public function addMimetypeRenderer($mimetype, $RenderClass)
    {
        if (!isset($this->mimeTypeRenderer[$mimetype]))
            $this->mimeTypeRenderer[$mimetype] = $RenderClass;
    }
    
    public function getMimetypeRenderer($mimetype)
    {
         if (isset($this->mimeTypeRenderer[$mimetype])) {
            return $this->mimeTypeRenderer[$mimetype];
         } else {
             if (isset($this->mimeTypeRenderer["default"])) {
                return $this->mimeTypeRenderer["default"];        
             } else {
                 throw new \Exception("No View Renderer avaible!");
             }    
         }
    }
    
    public function listRenderers()
    {
        return $this->mimeTypeRendererList;
    }
    
    private function restrictedRenderers()
    {
        /**
         * @var EntityManager $em
         * @var Setting $setting
         */
        $em = $GLOBALS['kernel']->getContainer()->get('doctrine')->getManager();
        $setting = $em->getRepository('IfrescoClientBundle:Setting')->findOneBy(array(
            'key_string' => 'Renderer'
        ));

        if ($setting) {
            $this->restricted = json_decode($setting->getValueString());
        }
        
        if (!is_array($this->restricted))
        	$this->restricted = array($this->restricted);
    }
    
    public function scanRenderers()
    {
        $this->restrictedRenderers();
        if ($handle = opendir($this->dirToScan)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != ".." && !empty($file)) {
                    if (preg_match("/(.*?)\..*/eis", $file, $fileMatch)) {
                        $classNameStr = $fileMatch[1];
                        if (empty($classNameStr)) {
                            continue;
                        } else {
                            $className = '\\Ifresco\\ClientBundle\\Component\\Alfresco\\Lib\\ViewRenderer\\' . $classNameStr;
                        }
                        
                        $helper = new $className();
                        if ($helper instanceof ViewRenderer) {
                            $mimetypes = $helper->getMimetypes();
                            
                            $helperStdClass = new \stdClass();

                            if (is_array($mimetypes)) {
                                if (!in_array($classNameStr,$this->restricted)) {
                                    for ($i = 0; $i < count($mimetypes); $i++) {
                                        $this->addMimetypeRenderer($mimetypes[$i], $helper);
                                    }
                                }
                                //$this->mimeTypeRendererList[$className] = join(",",$mimetypes);  
                                $helperStdClass->MimeTypes = join(",", $mimetypes);
                            } else {
                                if (!in_array($classNameStr,$this->restricted))
                                    $this->addMimetypeRenderer($mimetypes, $helper);
                                //$this->mimeTypeRendererList[$className] = $mimetypes;
                                $helperStdClass->MimeTypes = $mimetypes;
                            }
                            $helperStdClass->Description = $helper->getDescription();
                            $this->mimeTypeRendererList[$classNameStr] = $helperStdClass;
                        }
                    }
                }
            }
        }
    }
}