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

class TagController extends Controller
{
    public function getTagCloudAction(Request $request) {
    	$user = $this->get('security.context')->getToken();
    	$repository = $user->getRepository();
    	$session = $user->getSession();
    	$ticket = $user->getTicket();
    	
    	$spacesStore = new SpacesStore($session); 
    	
    	
    	$RestTags = new RESTTags($repository,$spacesStore,$session);

    	$TagCloud = $RestTags->GetTagQuery();
	
    	return new JsonResponse($TagCloud);
    }
}
