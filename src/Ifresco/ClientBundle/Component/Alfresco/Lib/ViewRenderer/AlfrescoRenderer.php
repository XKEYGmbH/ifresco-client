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
class AlfrescoRenderer implements ViewRenderer
{
    private $description = 'Alfresco PDF Rendition';
    private $mimeTypes = 'default';
    
    public function getDescription() {
        return $this->description;
    }
    
    public function getMimetypes() {
        return $this->mimeTypes;
    }
    
    public function render($node, $userObj, $isVersioned = false)
    {
    	$file = $GLOBALS['kernel']->getContainer()->get('router')->generate('ifresco_client_viewer_preview', array(
    			'versioned' => $isVersioned,
    			'nodeId' => $node->getId()
    	), true);
    	
        //$file = $isVersioned ? 'nodeactions/versionpreview?nodeId=' : 'nodeactions/preview?nodeId=';
        return $node->cm_content != null ? $this->renderView($_REQUEST['height'], $file) : "";
    }

    public function shareView($file)
    {
        return $this->renderView(null, $file);
    }
    
    private function renderView($height,$swfFile)
    {
        $heightStyle = (!empty($height) ? 'height:' . $height . ';' : '');
        $heightReq = (!empty($height) ? $height : '600');
        $expressInstallSwf = '';
        
        return $GLOBALS['kernel']->getContainer()->get('twig')->render('IfrescoClientBundle:Viewer:AlfrescoRenderer.html.twig', array(
        		'swfFile' => $swfFile,
        		'expressInstallSwf' => $expressInstallSwf,
        		'heightReq' => $heightReq,
        		'heightStyle' => $heightStyle
        ));

    }
}