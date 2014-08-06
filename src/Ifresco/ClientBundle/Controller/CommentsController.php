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

class CommentsController extends Controller
{
    public function getCommentsAction(Request $request)
    {
    	$nodeId = $request->get('nodeId');
    	$data = array("success"=>false,"message"=>null);
    	
    	try {
	    	$user = $this->get('security.context')->getToken();
	    	$repository = $user->getRepository();
	    	$session = $user->getSession();
	    	
	    	$RESTComments = new RESTComments($repository, null, $session);
	    	$Comments = $RESTComments->GetCommentsForNode($nodeId);

	    	$data["success"] = true;
	    	$data = array_merge($data,(array)$Comments);
    	}
    	catch (\Exception $e) {
    		$data["message"] = $e->getMessage();
    	}
    	
    	return new JsonResponse($data);
    }
    
    public function addCommentAction(Request $request)
    {
    	$params = array();
    	$content = $this->get("request")->getContent();
    	if (!empty($content))
    	{
    		$params = json_decode($content);
    	}

    	$data = array("success"=>false,"message"=>null);
    	$nodeId = $request->get('nodeId');
    	 
    	try {
    		$user = $this->get('security.context')->getToken();
    		$repository = $user->getRepository();
    		$session = $user->getSession();
    
    		$RESTComments = new RESTComments($repository, null, $session);
    		$Comment = $RESTComments->AddComment($nodeId,$params->content);

    		$Comment = $Comment->item;
    		$data["success"] = true;
    		$data["items"] = array($Comment);
    	}
    	catch (\Exception $e) {
    		$data["message"] = $e->getMessage();
    	}
    	 
    	return new JsonResponse($data);
    }
    
    public function updateCommentAction(Request $request)
    {
    	$params = array();
    	$content = $this->get("request")->getContent();
    	if (!empty($content))
    	{
    		$params = json_decode($content);
    	}

    	$data = array("success"=>false,"message"=>null);
    
    	try {
    		$user = $this->get('security.context')->getToken();
    		$repository = $user->getRepository();
    		$session = $user->getSession();
    
    		$RESTComments = new RESTComments($repository, null, $session);
    		$commentId = str_replace("workspace://SpacesStore/","",$params->id);
    		$Comment = $RESTComments->UpdateComment($commentId,$params->content);

    		$Comment = $Comment->item;
    		$data["success"] = true;
    		$data["items"] = array($Comment);
    	}
    	catch (\Exception $e) {
    		$data["message"] = $e->getMessage();
    	}
    
    	return new JsonResponse($data);
    }
    
    public function removeCommentAction(Request $request)
    {
    	
    	$params = array();
    	$content = $this->get("request")->getContent();
    	if (!empty($content))
    	{
    		$params = json_decode($content);
    	}
    	
    	$data = array("success"=>false,"message"=>null);
    
    	try {
    		$user = $this->get('security.context')->getToken();
    		$repository = $user->getRepository();
    		$session = $user->getSession();
    
    		$RESTComments = new RESTComments($repository, null, $session);
    		$commentId = str_replace("workspace://SpacesStore/","",$params->id);
    		$RESTComments->DeleteComment($commentId);
    
    		$data["success"] = true;
    	}
    	catch (\Exception $e) {
    		$data["message"] = $e->getMessage();
    	}
    
    	return new JsonResponse($data);
    }
}
