<?php
namespace Ifresco\ClientBundle\Controller;

use Ifresco\ClientBundle\Component\Alfresco\Lib\NodeCache;
use Ifresco\ClientBundle\Component\Alfresco\Node;
use Ifresco\ClientBundle\Component\Alfresco\SpacesStore;
use Ifresco\ClientBundle\Security\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTTags;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTSites;

class SitesController extends Controller
{
    public function getSitesAction(Request $request) {
    	$user = $this->get('security.context')->getToken();
    	$repository = $user->getRepository();
    	$session = $user->getSession();
    	$ticket = $user->getTicket();
    	
    	$spacesStore = new SpacesStore($session); 

    	$RestSites = new RESTSites($repository,$spacesStore,$session);
    	
    	$filter = $request->get('filter', '');
    	$size = 25;

    	$Sites = $RestSites->GetSites($filter,$size);
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

    	$Result = array("sites"=>$SitesData);
    	
    	return new JsonResponse($Result);
    }
}
