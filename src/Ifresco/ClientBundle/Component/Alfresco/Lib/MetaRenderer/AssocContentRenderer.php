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
class AssocContentRenderer implements InterfaceMetaRenderer {
    private $PropertyNames = "type=*";        
    
    public function getPropertyNames() {
        return $this->PropertyNames;
    }
    
    public function render($FieldValue) { 
        $session = MetaRenderer::$session;
        $spacesStore = MetaRenderer::$spacesStore;
        if (!empty($FieldValue)) {               
            $Content = explode(",",$FieldValue);
            if (count($Content) == 0)
                $Content = array($FieldValue);
            
            $ContentValues = array();
            if (count($Content) > 0) {
                foreach ($Content as $Key=>$ContentNodeRef) {
                    $ContentUUId = str_replace("workspace://SpacesStore/","",$ContentNodeRef);
                    $ContentNode = $session->getNode($spacesStore, $ContentUUId);       
                    
                    if ($ContentNode != null) {
                        //$ContentValues[$ContentNode->getId()] = array("name"=>$ContentNode->cm_name,"img"=>'<img src="'.$ContentNode->getIconUrl().'" border="0" align="absmiddle">');
                        if ($ContentNode->getType() == "{http://www.alfresco.org/model/content/1.0}folder" || $ContentNode->getType() == "{http://www.alfresco.org/model/content/1.0}site" || $ContentNode->getType() == "{http://www.alfresco.org/model/content/1.0}sites") {                
                              $ContentValues[] = '<a href="javascript:Ifresco.getApplication().getController(\'Index\').openDocument(\''.$ContentNode->getId().'\',\''.$ContentNode->cm_name.'\')"><img src="'.$ContentNode->getIconUrl().'" border="0" align="absmiddle"> '.$ContentNode->cm_name.'</a>';
                        }
                        else {
                            $ContentValues[] = '<a href="javascript:Ifresco.getApplication().getController(\'Index\').openDocumentDetail(\''.$ContentNode->getId().'\',\''.$ContentNode->cm_name.'\')"><img src="'.$ContentNode->getIconUrl().'" border="0" align="absmiddle"> '.$ContentNode->cm_name.'</a>';
                        }
                    }  
                }  
                //$FieldValue = $ContentValues;   
                $FieldValue = join(", ",$ContentValues);                  
            }
        }

        return $FieldValue;   
    }
}    
?>