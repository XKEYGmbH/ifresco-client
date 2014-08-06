<?php

namespace Ifresco\ClientBundle\Controller;

use Ifresco\ClientBundle\Component\Alfresco\Lib\Renderer;
use Ifresco\ClientBundle\Component\Alfresco\Lib\ViewRenderer\DefaultRenderer;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTShare;
use Ifresco\ClientBundle\Component\Alfresco\SpacesStore;
use Ifresco\ClientBundle\Component\Alfresco\VersionStore;
use Ifresco\ClientBundle\Security\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ifresco\ClientBundle\Component\Alfresco\Lib\NodeCache;
use Ifresco\ClientBundle\Component\Alfresco\ContentData;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTContent;
use Ifresco\ClientBundle\Component\Alfresco\Lib\ViewRenderer\AlfrescoRenderer;
use Ifresco\ClientBundle\Component\Alfresco\Lib\Registry;

class ViewerController extends Controller
{
    public function indexAction(Request $request)
    {
        $nodeId = $request->get('nodeId');
        $isVersioned = false;

        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        $session = $user->getSession();

        // Create a reference to the 'SpacesStore'
        try {
            $store = new SpacesStore($session);
            if (preg_match("#workspace://version2store/(.*)#eis", $nodeId, $match)) {
                $store = new VersionStore($session);
                $nodeId = $match[1];
                $isVersioned = true;
            }

            $node = $session->getNode($store, $nodeId);
            $renderer = Renderer::getInstance();
            $renderer->scanRenderers();


            $mimetype = "default";
            $contentData = $node->cm_content;

            if ($contentData != null) {
                $mimetype = $contentData->getMimetype();
            }

            $renderObj = $renderer->getMimetypeRenderer($mimetype);

            echo $renderObj->render($node, $user, $isVersioned);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        
        exit;
    }
    
    public function viewShareAction($shareId)
    {
        $URI = $this->container->getParameter('alfresco_repository_url');
        $RESTShare = new RESTShare($URI);
        $metaData = $RESTShare->getMetaData($shareId);
        $renderer = Renderer::getInstance();
        $renderer->scanRenderers();

        $previewMode = in_array('imgpreview', $metaData->thumbnails) ? 'imgpreview' : 'webpreview';;
        
        $RenderFinder = Renderer::getInstance();
        $renderObj = $RenderFinder->getMimetypeRenderer($metaData->mimetype);
        
        //$renderObj = new AlfrescoRenderer();

        $shareViewCode = $renderObj->shareView($this->get('router')->generate('ifresco_client_viewer_share_preview', array(
            'shareId' => $shareId,
            'preview' => $previewMode,
        	'content' => ($metaData->mimetype == "application/pdf" ? true : false)
        ), true));


        $fileIcon = $metaData->name;
        $fileIcon = preg_replace('/^.+\.(\w{2,})/i', "$1", $fileIcon);
        $fileIcon .= "-file-48.png";
        
        $LogoURL = Registry::getSetting('logoURL', 'http://www.ifresco.at/');

        $LogoPath ='bundles/ifrescoclient/images/logo94x50.png';
        if(file_exists('images/custom_logo94x50.png'))
        	$LogoPath ='/images/custom_logo94x50.png';
        
        return $this->render('IfrescoClientBundle:Share:ShareView.html.twig', array(
            'shareViewCode' => $shareViewCode,
            'shareURI' => str_replace('alfresco/api', '', $URI),
            'metaData' => $metaData,
        	'fileIcon' => $fileIcon,
        	'LogoURL' => $LogoURL,
        	'LogoPath' => $LogoPath
        ));
    }
    
    public function sharePreviewAction($shareId, $preview) {
    	$getContent = $this->getRequest()->get('content', false);
    	$response = new Response();
    
    	$URI = $this->container->getParameter('alfresco_repository_url');
    	$URI = str_replace("alfresco/api","",$URI);
    
    	$link = "{$URI}share/proxy/alfresco-noauth/api/internal/shared/node/{$shareId}/content";
    	if (!$getContent)
    		$link .= "/thumbnails/{$preview}";
    	$cont = file_get_contents($link);
    	if($cont) {
    		$response->setContent($cont);
    		$response->headers->set('Content-Type','application/pdf; charset=utf-8');
    		$response->headers->set('Cache-Control','no-store, no-cache, must-revalidate');
    		$response->headers->set('Cache-Control','post-check=0, pre-check=0',false);
    		$response->headers->set('Pragma','no-cache');
    		return $response;
    	}
    
    	exit;
    }
    
    public function imagePreviewAction(Request $request) {
    	$nodeId = $request->get('nodeId');
    	$type = $request->get('type');
    	if (empty($type))
    		$type = "imgpreview";
    
    	$user = $this->get('security.context')->getToken();
    	$repository = $user->getRepository();
    	$session = $user->getSession();
    	$ticket = $user->getTicket();
    
    	$spacesStore = new SpacesStore($session);
    
    	$response = new Response();
    
    	try {
    		$Node = NodeCache::getInstance()->getNode($session, $spacesStore, $nodeId);
    
    		if ($Node != null) {
    			$Content = $Node->cm_content;
    
    			if ($Content instanceof ContentData) {
    				$response->headers->set('Content-Type','image/png; charset=utf-8');
    				$response->headers->set('Cache-Control','no-store, no-cache, must-revalidate');
    				$response->headers->set('Cache-Control','post-check=0, pre-check=0',false);
    				$response->headers->set('Pragma','no-cache');
    
    				$restContent = new RESTContent($repository,$spacesStore,$session);
    
    				$fileContent = $restContent->GetWebImagePreview($nodeId,false,$type);
    
    				$response->setContent($fileContent);
    				return $response;
    
    			}
    			else {
    				$ImgPath ='bundles/ifrescoclient/images/';
    				$imagePath = $ImgPath.'ifrescoEmptyPreview.png';
    				$fileContent = file_get_contents($imagePath);
    
    				$response->headers->set('Content-Type','image/png; charset=utf-8');
    				$response->headers->set('Cache-Control','no-store, no-cache, must-revalidate');
    				$response->headers->set('Cache-Control','post-check=0, pre-check=0',false);
    				$response->headers->set('Pragma','no-cache');
    
    				$response->setContent($fileContent);
    				return $response;
    			}
    		}
    		throw new \Exception();
    	}
    	catch (\Exception $e) {
    		$response->headers->set('Content-Type','application/json; charset=utf-8');
    		$response->headers->set('Cache-Control','no-store, no-cache, must-revalidate');
    		$response->headers->set('Cache-Control','post-check=0, pre-check=0',false);
    		$response->headers->set('Pragma','no-cache');
    		$response->setContent(json_encode(array("success"=>false,"e"=>$e->getMessage())));
    		return $response;
    	}
    }
    
    public function previewAction($nodeId, $versioned = false) {
    	$type = $this->getRequest()->get('type');
    
    	if (empty($type))
    		$type = "webpreview";
    
    	$user = $this->get('security.context')->getToken();
    	$repository = $user->getRepository();
    	$session = $user->getSession();
    	$ticket = $user->getTicket();
    
    	if($versioned)
    		$Store = new VersionStore($session);
    	else
    		$Store = new SpacesStore($session);
    
    	$response = new Response();
    
    	try {
    		$Node = NodeCache::getInstance()->getNode($session, $Store, $nodeId);
  
    		if ($Node != null) {

    			$Content = $Node->cm_content;
    			if ($Content instanceof ContentData) {
    				if ($type == "content") {
    					$response->headers->set('Content-Type','application/pdf; charset=utf-8');
    					$response->headers->set('Cache-Control','no-store, no-cache, must-revalidate');
    					$response->headers->set('Cache-Control','post-check=0, pre-check=0',false);
    					$response->headers->set('Pragma','no-cache');
    					$fileContent = $Content->getContent();
    				}
    				else {
    					$response->headers->set('Content-Type','application/x-shockwave-flash; charset=utf-8');
    					$response->headers->set('Cache-Control','no-store, no-cache, must-revalidate');
    					$response->headers->set('Cache-Control','post-check=0, pre-check=0',false);
    					$response->headers->set('Pragma','no-cache');
    					$restContent = new RESTContent($repository,$Store,$session);
    					$fileContent = $restContent->GetWebPreview($nodeId, $versioned);
    				}
    				$response->setContent($fileContent);
    				return $response;
    			}
    		}
    		throw new \Exception();
    	}
    	catch (\Exception $e) {
    		die($e->getMessage());
    		$response->headers->set('Content-Type','application/json; charset=utf-8');
    		$response->headers->set('Cache-Control','no-store, no-cache, must-revalidate');
    		$response->headers->set('Cache-Control','post-check=0, pre-check=0',false);
    		$response->headers->set('Pragma','no-cache');
    		$response->setContent(json_encode(array("success"=>false)));
    		return $response;
    	}
    }
}
