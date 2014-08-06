<?php

namespace Ifresco\ClientBundle\Controller;

use Ifresco\ClientBundle\Component\Alfresco\REST\RESTShare;
use Ifresco\ClientBundle\Component\Alfresco\SpacesStore;
use Ifresco\ClientBundle\Component\Alfresco\VersionStore;
use Ifresco\ClientBundle\Security\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Ifresco\ClientBundle\Component\Alfresco\Lib\NodeCache;
use Ifresco\ClientBundle\Component\Alfresco\ContentData;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTContent;
use Ifresco\ClientBundle\Component\Alfresco\Lib\ViewRenderer\AlfrescoRenderer;
use Ifresco\ClientBundle\Component\Alfresco\Lib\Registry;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTComments;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTDashlets;

class DashboardController extends Controller
{
    public function getMySitesAction(Request $request)
    {
    	$data = array("success"=>false,"message"=>null);
    	
    	try {
	    	$user = $this->get('security.context')->getToken();
	    	$repository = $user->getRepository();
	    	$session = $user->getSession();
	    	$spacesStore = new SpacesStore($session);

			$RESTDashlets = new RESTDashlets($repository, $spacesStore, $session);
			
			$Sites = $RESTDashlets->GetMySites($user->getUsername());
			$SitesData = array();
			if (count($Sites) > 0) {
				foreach ($Sites as $Site) {
					$node = $Site->node;
					$Site->nodeId = preg_replace("/.*SpacesStore\/(.*)/",'$1',$node);
					$RealNode = $session->getNode($spacesStore, $Site->nodeId);
					$foundDoclib = false;
					foreach ($RealNode->children as $Child) {
			
						if ($foundDoclib)
							continue;
						if ($Child->st_componentId) {
							$ChildNode = $Child->getChild();
							$Site->docLib = $ChildNode->getId();
							$foundDoclib = true;
							continue;
						}
					}
					 
					if ($foundDoclib) {
						$SitesData[] = $Site;
					}
				}
			}
			
	    	$data["success"] = true;
	    	$data["sites"] = $SitesData;
    	}
    	catch (\Exception $e) {
    		$data["message"] = $e->getMessage();
    	}
    	
    	return new JsonResponse($data);
    }
    
    public function getMyDocumentsAction(Request $request)
    {
    	$data = array("success"=>false,"message"=>null);
    	
    	$filter = $request->get('filter','recentlyModifiedByMe');
    	 
    	try {
    		$user = $this->get('security.context')->getToken();
    		$repository = $user->getRepository();
    		$session = $user->getSession();
    		$spacesStore = new SpacesStore($session);
    
    		$RESTDashlets = new RESTDashlets($repository, $spacesStore, $session);
    		$Documents = $RESTDashlets->GetMyDocuments($filter);
    		
    			
    		$data["success"] = true;
    		$data = array_merge($data,(array)$Documents);
    	}
    	catch (\Exception $e) {
    		$data["message"] = $e->getMessage();
    	}
    	 
    	return new JsonResponse($data);
    }
}
