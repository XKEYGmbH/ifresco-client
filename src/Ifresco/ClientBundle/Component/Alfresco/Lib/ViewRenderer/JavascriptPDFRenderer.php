<?php
namespace Ifresco\ClientBundle\Component\Alfresco\Lib\ViewRenderer;

use Ifresco\ClientBundle\Component\Alfresco\Lib\ViewRenderer;
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
class JavascriptPDFRenderer implements ViewRenderer
{
    private $description = "Javascript PDF Viewer";
    private $mimeTypes = "application/pdf";
    
    public function getDescription()
    {
        return $this->description;
    }
    
    public function getMimetypes() {
        return $this->mimeTypes;
    }
    
    public function render($node, $userObj)
    {
        $nodeId = $node->getId();
        $height = $_REQUEST["height"];
        
        $file = "nodeactions/preview?type=content&nodeId=" . $nodeId;
        
        $contentData = $node->cm_content;
        if ($contentData != null) {
            return $this->renderView($height, $file);
        }
        return "";
    }

    public function shareView($file)
    {
        return $this->renderView(null, $file);
    }
    
    private function renderView($height, $file)
    {
        $heightStyle = (!empty($height) ? 'max-height:' . $height . ';' : 'max-height:300px');
        $heighNumber = str_replace("px", "", $height);

        $html = '<div id="pdfJsObject" style="width:100%;height:'.$height.';'.$heightStyle.'"></div><script>
            Ext.onReady(function(){
                Ext.tip.QuickTipManager.init();
                
                Ext.create("Ext.ux.panel.PDF", {
                    title    : "PDF Panel",
                    header   : false,
                    width    : "100%",
                    height   : '.$heighNumber.',
                    pageScale: 1.0,       
                    src      : "'.$file.'",
                    renderTo : "pdfJsObject"
                });
            });
        </script>';

        return $html;
    }
}