<?php
namespace Ifresco\ClientBundle\Controller;

use Ifresco\ClientBundle\Component\Alfresco\Lib\NodeCache;
use Ifresco\ClientBundle\Component\Alfresco\Node;
use Ifresco\ClientBundle\Component\Alfresco\SpacesStore;
use Ifresco\ClientBundle\Security\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTTags;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTGoogleDocs;

class GoogleDocsController extends Controller
{
    public function getAuthUrlAction(Request $request) {
    	$user = $this->get('security.context')->getToken();
    	$repository = $user->getRepository();
    	$session = $user->getSession();
    	$ticket = $user->getTicket();
    	
    	$spacesStore = new SpacesStore($session); 

    	$GoogleApi = new RESTGoogleDocs($repository,$spacesStore,$session);
    	$baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . "/";
    	if ($this->get('kernel')->getEnvironment() == "dev") {
    		$baseurl .= "app_dev.php/";
    	}
    	//
    	$Response = $GoogleApi->AuthUrl($baseurl);

    	return new JsonResponse($Response);
    }
    
    public function completeAuthAction(Request $request) {
    	$user = $this->get('security.context')->getToken();
    	$repository = $user->getRepository();
    	$session = $user->getSession();
    	$ticket = $user->getTicket();
    	 
    	$spacesStore = new SpacesStore($session);
    	
    	$token = $request->get('access_token');
    
    	$GoogleApi = new RESTGoogleDocs($repository,$spacesStore,$session);
    	$Result = $GoogleApi->CompleteAuth($token);
    	return new Response($Result);
    }
    
    public function getNodeInfoAction(Request $request) {
    	$user = $this->get('security.context')->getToken();
    	$repository = $user->getRepository();
    	$session = $user->getSession();
    	$ticket = $user->getTicket();
    	
    	$nodeId = $request->get('nodeId');
    	 
    	$spacesStore = new SpacesStore($session);
    	
    	$GoogleApi = new RESTGoogleDocs($repository,$spacesStore,$session);
    	$Upload = $GoogleApi->uploadContent($nodeId);
    
    	$Node = $session->getNode($spacesStore, $nodeId);
    	
    	$Response = array("editorUrl"=>$Node->gd2_editorURL,"resourceID"=>$Node->gd2_resourceID,"locked"=>$Node->gd2_locked,"name"=>$Node->cm_name);

    	return new JsonResponse($Response);
    }
    
    public function saveChangesAction(Request $request) {

    	$user = $this->get('security.context')->getToken();
    	$repository = $user->getRepository();
    	$session = $user->getSession();
    	$ticket = $user->getTicket();
    	 
    	$nodeId = $request->get('nodeId');
    	$versionchange = $request->get('versionchange', 'major');
    	$note = $request->get('note', '');
    	
    	$majorVersion = false;
    	if ($versionchange == "major")
    		$majorVersion = true;
    
    	$spacesStore = new SpacesStore($session);
    	 
    	$GoogleApi = new RESTGoogleDocs($repository,$spacesStore,$session);
    	$Response = $GoogleApi->saveContent($nodeId, $note, $majorVersion);

    	return new JsonResponse($Response);
    }
    
    public function discardChangesAction(Request $request) {
    
    	$user = $this->get('security.context')->getToken();
    	$repository = $user->getRepository();
    	$session = $user->getSession();
    	$ticket = $user->getTicket();
    
    	$nodeId = $request->get('nodeId');
    	
    	$spacesStore = new SpacesStore($session);
    	$GoogleApi = new RESTGoogleDocs($repository,$spacesStore,$session);
    	$Response = $GoogleApi->discardContent($nodeId);
    
    	return new JsonResponse($Response);
    }
}
