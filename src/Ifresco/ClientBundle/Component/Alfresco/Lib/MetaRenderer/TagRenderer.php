<?php
namespace Ifresco\ClientBundle\Component\Alfresco\Lib\MetaRenderer;

use Ifresco\ClientBundle\Component\Alfresco\Lib\InterfaceMetaRenderer;
use Ifresco\ClientBundle\Component\Alfresco\Lib\MetaRenderer;
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
class TagRenderer implements InterfaceMetaRenderer {
    private $PropertyNames = "cm:taggable";        
    
    public function getPropertyNames() {
        return $this->PropertyNames;
    }
    
    public function render($FieldValue) { 
        $session = MetaRenderer::$session;
        $spacesStore = MetaRenderer::$spacesStore;

        
        if (!empty($FieldValue)) {

            if (!is_array($FieldValue)) {
                $Tags = explode(",",$FieldValue);
                if (count($Tags) == 0)
                    $Tags = array($FieldValue);
            }
            else
                $Tags = $FieldValue;     

            $TagsValues = array();
            if (count($Tags) > 0) {

                foreach ($Tags as $Key=>$TagNodeRef) {
                    $TagUUId = str_replace("workspace://SpacesStore/","",$TagNodeRef);
                    $TagNode = $session->getNode($spacesStore, $TagUUId);       
                    
                    if ($TagNode != null) {
                        
                        //$TagsValues[$TagNode->getId()] = $TagNode->cm_name;
                        $TagsValues[] = '<a href="javascript:Ifresco.getApplication().getController(\'Index\').openTag(\''.$TagNode->cm_name.'\')">'.$TagNode->cm_name.'</a>';
                        //$TempFieldValue .= '<a href="javascript:openTag(\''.$TagNode->cm_name.'\')">'.$TagNode->cm_name.'</a>, ';                               
                    }   
                }     
                //$FieldValue = join(", ",$CategoriesValues);                  
                $FieldValue = join(", ",$TagsValues);
                               
            }  
        }  

        return $FieldValue;   
    }
}    
?>