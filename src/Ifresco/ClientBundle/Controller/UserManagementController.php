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
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTPerson;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTGroups;

class UserManagementController extends Controller
{
    public function getPersonsAction(Request $request) {
    	$filter = $request->get('filter','');
    	
    	$data = array("success"=>false,"message"=>null);
    	 
    	try {
    	
	    	$user = $this->get('security.context')->getToken();
	    	$repository = $user->getRepository();
	    	$session = $user->getSession();
	    	$ticket = $user->getTicket();
	    	
	    	$spacesStore = new SpacesStore($session); 
	
	    	$RestPersons = new RESTPerson($repository,$spacesStore,$session);
	    	$Result = $RestPersons->GetPersons($filter);

	    	$data["success"] = true;
	    	$data["people"] = $Result->people;
    	}
    	catch (\Exception $e) {
    		$data["message"] = $e->getMessage();
    	}
    	
    	return new JsonResponse($data);
    }
    
    public function updatePersonAction(Request $request) {
    	$fields = $request->get('data');
    	 
    	$data = array("success"=>false,"message"=>null);
    
    	try {
    		$fields = json_decode($fields);
    		$userName = $fields->userName;
    		if (!isset($fields->enabled))
    			$fields->enabled = false;
    		else if ($fields->enabled == "on")
    			$fields->enabled = true;
    		
    		$fields->disableAccount = !$fields->enabled;
    		
    		
    		$fields->addGroups = array();
    		$fields->removeGroups = array();
    		unset($fields->sizeCurrent);
    		
    		//print_R($fields);
    		$user = $this->get('security.context')->getToken();
    		$repository = $user->getRepository();
    		$session = $user->getSession();
    		$ticket = $user->getTicket();
    
    		$spacesStore = new SpacesStore($session);
    
    		$RestPersons = new RESTPerson($repository,$spacesStore,$session);
    		$Result = $RestPersons->UpdatePerson($userName,$fields);
    		
    		/*addGroups: []
disableAccount: false
email: "admin@alfresco.com"
firstName: "Administrator"
lastName: "Danninger"
quota: -1
removeGroups: []*/
    		

    		$data["success"] = true;
    	}
    	catch (\Exception $e) {
    		$data["message"] = $e->getMessage();
    	}
    	 
    	return new JsonResponse($data);
    }
    
    public function getGroupsAction(Request $request) {
    	$filter = $request->get('filter','');
    	$userName = $request->get('userName',null);
    	
    	$data = array("success"=>false,"message"=>null);
    
    	try {
    		 
    		$user = $this->get('security.context')->getToken();
    		$repository = $user->getRepository();
    		$session = $user->getSession();
    		$ticket = $user->getTicket();
    
    		$spacesStore = new SpacesStore($session);
    
    		$RestGroups = new RESTGroups($repository,$spacesStore,$session);
    		$Result = $RestGroups->GetGroups($filter);
    		$data["groups"] = $Result->data;
    		
    		
    		if ($userName != null) {
	    		$RESTPerson = new RESTPerson($repository,$spacesStore,$session);
	    		$Result = $RESTPerson->GetPerson($userName,true);
	    		
	    		$data["selectedGroups"] = $Result->groups;
    		}
    		
    		$data["success"] = true;
    	}
    	catch (\Exception $e) {
    		$data["message"] = $e->getMessage();
    	}
    	 
    	return new JsonResponse($data);
    }
    
    public function getUserGroupsAction(Request $request) {
    	$userName = $request->get('userName');
    
    	$data = array("success"=>false,"message"=>null);
    
    	try {
    		 
    		$user = $this->get('security.context')->getToken();
    		$repository = $user->getRepository();
    		$session = $user->getSession();
    		$ticket = $user->getTicket();
    
    		$spacesStore = new SpacesStore($session);
    
    		$RESTPerson = new RESTPerson($repository,$spacesStore,$session);
    		$Result = $RESTPerson->GetPerson($userName,true);
    
    		$data["success"] = true;
    		$data = array_merge($data,(array)$Result);
    	}
    	catch (\Exception $e) {
    		$data["message"] = $e->getMessage();
    	}
    
    	return new JsonResponse($data);
    }
}
