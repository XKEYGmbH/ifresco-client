<?php

namespace Ifresco\ClientBundle\Controller;

use Ifresco\ClientBundle\Component\Alfresco\Lib\NodeCache;
use Ifresco\ClientBundle\Component\Alfresco\Lib\Registry;
use Ifresco\ClientBundle\Component\Alfresco\Session;
use Ifresco\ClientBundle\Component\Alfresco\SpacesStore;
use Ifresco\ClientBundle\Entity\AlfrescoFavorite;
use Ifresco\ClientBundle\Security\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTArchive;

class UserController extends Controller
{
    public function addFavoriteAction(Request $request)
    {
        $returnArr = array("success" => "false");
        $nodeId = $request->get('nodeId');
        $nodeText = $request->get('nodeText');
        $nodeType = $request->get('nodeType');

        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        $session = $user->getSession();
        $userName = $user->getUsername();

        $spacesStore = new SpacesStore($session);

        $nodeFound = true;
        if ($nodeType != "category") {
            if ($nodeId == "root") {
                $nodeFound = false;
            } else {
                $node = $session->getNode($spacesStore, $nodeId);
                if ($node == null) {
                    $nodeFound = false;
                }
            }
        }

        if ($nodeFound==true ) {
            try {
                $em = $this->getDoctrine()->getManager();
                /**
                 * @var AlfrescoFavorite $alfrescoFavorites
                 */
                $alfrescoFavorites = $em->getRepository('IfrescoClientBundle:AlfrescoFavorite')->findOneBy(array(
                    'user_key' => $userName,
                    'node_id' => $nodeId
                ));


                if (!$alfrescoFavorites) {
                    $alfrescoFavorites = new AlfrescoFavorite();
                    $alfrescoFavorites->setNodeName($nodeText);
                    $alfrescoFavorites->setNodeId($nodeId);
                    $alfrescoFavorites->setNodeType($nodeType);
                    $alfrescoFavorites->setUserKey($userName);

                    $em->persist($alfrescoFavorites);
                    $em->flush();
                    $returnArr["success"] = true;
                }
            } 
            catch (\Exception $e) {
                $returnArr["errorMsg"] = $e->getMessage();
                $returnArr["success"] = false;
            }
        }

        $response = new JsonResponse($returnArr);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }
    
    public function getTrashCanAction(Request $request) {
    	$returnArr = array("success" => false);
    	try {
    		$user = $this->get('security.context')->getToken();
    		$repository = $user->getRepository();
    		$session = $user->getSession();
    		$ticket = $user->getTicket();
    		
    		$repoUrl = $session->repository->connectionUrl;
    		$repoUrl = str_replace("soapapi/","",$repoUrl);
    		$repoUrl = str_replace("soapapi","",$repoUrl);
    		
	    	$spacesStore = new SpacesStore($session);
	    	$restArchive = new RESTArchive($repository,$spacesStore,$session);
	    
	    	$Result = $restArchive->GetArchiveWorkspace();
	    	$returnArr["success"] = true;
	    	$Nodes = $Result->data;

	    	foreach ($Nodes->deletedNodes as $Node) {

	    		
	    		if (!$Node->isContentType) {
	    			$repoUrlShare = str_replace("alfresco","",$repoUrl);
	    			$icon = $repoUrlShare."share/res/components/documentlibrary/images/folder-32.png";
	    		}
	    		else {
	    			$nodeRefUrl = str_replace("://","/",$Node->nodeRef);
	    			$icon = $repoUrl."service/api/node/".$nodeRefUrl."/content/thumbnails/doclib?c=queue&ph=true&lastModified=1&alf_ticket=".$session->ticket;
	    		}
	    		$Node->icon = $icon;
	    	}
	    	$returnArr = array_merge($returnArr,(array)$Nodes);

    	} 
    	catch (\Exception $e) {
    		$returnArr["message"] = $e->getMessage();
    		$returnArr["success"] = false;
    	}
    	return new JsonResponse($returnArr);
    }
    
    public function deleteTrashCanNodeAction(Request $request)
    {
    	$returnArr = array("success" => "false");
    
    	$nodeId = $request->get('nodeId');
    
    	/**
    	 * @var User $user
    	*/
    	$user = $this->get('security.context')->getToken();
    	$repository = $user->getRepository();
    	$session = $user->getSession();
    	$spacesStore = new SpacesStore($session);
    	$restArchive = new RESTArchive($repository, $spacesStore, $session);

    	try {
    		$nodeId = str_replace("workspace://SpacesStore/","",$nodeId);
    		$nodeId = str_replace("archive://SpacesStore/","",$nodeId);
    		$Result = $restArchive->DeleteNode($nodeId);

    		$returnArr["success"] = true;
    
    		NodeCache::getInstance()->clean();
    	} catch (\Exception $e) {
    		$returnArr["errorMsg"] = $e->getMessage();
    		$returnArr["success"] = false;
    	}
    
    	$response = new JsonResponse($returnArr);
    	return $response;
    }
    
    public function deleteTrashCanNodesAction(Request $request) {
    	$returnArr = array("success"=>"false","deleted"=>0,"count"=>0);
    
    	$nodes = $request->get('nodes');
    
    	$user = $this->get('security.context')->getToken();
    	$repository = $user->getRepository();
    	$session = $user->getSession();
    	$ticket = $user->getTicket();
    
    	$spacesStore = new SpacesStore($session);
    
    	$restArchive = new RESTArchive($repository,$spacesStore,$session);
    
    	if (!empty($nodes)) {
    		try {
    			$nodes = json_decode($nodes);
    			if (is_array($nodes)) {
    				$countSucces = 0;
    				$countFiles = count($nodes);
    
    				foreach ($nodes as $Node) {
    					$nodeId = $Node->nodeRef;
    					$nodeId = str_replace("workspace://SpacesStore/","",$nodeId);
    					$nodeId = str_replace("archive://SpacesStore/","",$nodeId);
    					try {
    						$restArchive->DeleteNode($nodeId);
    						$countSucces++;
    					}
    					catch (\Exception $e) {
    						$returnArr["errorMsg"] = $e->getMessage();
    					}
    				}
    				$returnArr["deleted"] = $countSucces;
    				$returnArr["count"] = $countFiles;
    				if ($countSucces == $countFiles) {
    					$returnArr["success"] = true;
    				}
    
    
    				NodeCache::getInstance()->clean();
    			}
    		}
    		catch (\Exception $e) {
    			$returnArr["errorMsg"] = $e->getMessage();
    			$returnArr["success"] = false;
    		}
    	}
    
    	$response = new JsonResponse($returnArr);
    	return $response;
    }
    
    public function restoreTrashCanNodeAction(Request $request)
    {
    	$returnArr = array("success" => "false");
    
    	$nodeId = $request->get('nodeId');
    
    	/**
    	 * @var User $user
    	*/
    	$user = $this->get('security.context')->getToken();
    	$repository = $user->getRepository();
    	$session = $user->getSession();
    	$spacesStore = new SpacesStore($session);
    	$restArchive = new RESTArchive($repository, $spacesStore, $session);
    
    	try {
    		$nodeId = str_replace("workspace://SpacesStore/","",$nodeId);
    		$nodeId = str_replace("archive://SpacesStore/","",$nodeId);
    		$Result = $restArchive->RestoreNode($nodeId);
    
    		$returnArr["success"] = true;
    
    		NodeCache::getInstance()->clean();
    	} catch (\Exception $e) {
    		$returnArr["errorMsg"] = $e->getMessage();
    		$returnArr["success"] = false;
    	}
    
    	$response = new JsonResponse($returnArr);
    	return $response;
    }
    
    public function restoreTrashCanNodesAction(Request $request) {
    	$returnArr = array("success"=>"false","deleted"=>0,"count"=>0);
    
    	$nodes = $request->get('nodes');
    
    	$user = $this->get('security.context')->getToken();
    	$repository = $user->getRepository();
    	$session = $user->getSession();
    	$ticket = $user->getTicket();
    
    	$spacesStore = new SpacesStore($session);
    
    	$restArchive = new RESTArchive($repository,$spacesStore,$session);
    
    	if (!empty($nodes)) {
    		try {
    			$nodes = json_decode($nodes);
    			if (is_array($nodes)) {
    				$countSucces = 0;
    				$countFiles = count($nodes);
    
    				foreach ($nodes as $Node) {
    					$nodeId = $Node->nodeRef;
    					$nodeId = str_replace("workspace://SpacesStore/","",$nodeId);
    					$nodeId = str_replace("archive://SpacesStore/","",$nodeId);
    					try {
    						$restArchive->RestoreNode($nodeId);
    						$countSucces++;
    					}
    					catch (\Exception $e) {
    						$returnArr["errorMsg"] = $e->getMessage();
    					}
    				}
    				$returnArr["deleted"] = $countSucces;
    				$returnArr["count"] = $countFiles;
    				if ($countSucces == $countFiles) {
    					$returnArr["success"] = true;
    				}
    
    
    				NodeCache::getInstance()->clean();
    			}
    		}
    		catch (\Exception $e) {
    			$returnArr["errorMsg"] = $e->getMessage();
    			$returnArr["success"] = false;
    		}
    	}
    
    	$response = new JsonResponse($returnArr);
    	return $response;
    }
}