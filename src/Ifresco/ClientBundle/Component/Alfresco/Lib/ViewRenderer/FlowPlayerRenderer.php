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
class FlowPlayerRenderer implements ViewRenderer
{
    private $description = "FLV/MP4 FlowPlayer (www.flowplayer.org)";
    private $mimeTypes = array(
        "video/mp4",
        "video/x-flv",
        "video/quicktime"
    );
    
    public function getDescription()
    {
        return $this->description;
    }
    
    public function getMimetypes()
    {
        return $this->mimeTypes;
    }
    
    public function render($node, $userObj)
    {
        $height = $_REQUEST["height"];
        $contentData = $node->cm_content;
        if ($contentData != null) {
        	$type = $contentData->getMimeType();
            return $this->renderView($height, $node->getId(), $contentData->getUrl(),$type);
        }
        
        return "";
    }

    public function shareView($file)
    {
        return $this->renderView(null, null, $file);
    }
    
    private function renderView($height, $nodeId, $file, $type = "video/mp4")
    {
        $playerID = str_replace("-", "", $nodeId);
        $flowPlayerId = "flowPlayer{$playerID}";        
        $height = (!empty($height) ? $height : '300px'); 
       
        return $GLOBALS['kernel']->getContainer()->get('twig')->render('IfrescoClientBundle:Viewer:FlowPlayerRenderer.html.twig', array(
        		'file' => $file,
        		'type' => $type
        ));
    }
}