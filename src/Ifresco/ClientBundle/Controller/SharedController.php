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
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTDoclib;

class SharedController extends Controller
{
    public function getSharedFilesAction(Request $request) {
    	$nodeId = $request->get('nodeId');
    	$data = array("success"=>false,"message"=>null);
    	
    	try {
	    	$user = $this->get('security.context')->getToken();
	    	$repository = $user->getRepository();
	    	$session = $user->getSession();
	    	$spacesStore = new SpacesStore($session);
	    	
	    	$RestDoclib = new RESTDoclib($repository,$spacesStore,$session);
	    	$Docs = $RestDoclib->GetDocLibShared();

	    	$data["success"] = true;
	    	$data = array_merge($data,(array)$Docs);
    	}
    	catch (\Exception $e) {
    		$data["message"] = $e->getMessage();
    	}
    	
    	return new JsonResponse($data);
    }
}
