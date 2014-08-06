<?php
namespace Ifresco\ClientBundle\Controller;

use Ifresco\ClientBundle\Component\Alfresco\Lib\NodeCache;
use Ifresco\ClientBundle\Component\Alfresco\Lib\CategoryCache;
use Ifresco\ClientBundle\Component\Alfresco\SpacesStore;
use Ifresco\ClientBundle\Security\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ifresco\ClientBundle\Component\Alfresco\Classification;

class TreeCategoriesController extends Controller
{
    public function getTreeAction(Request $request)
    {
        $user = $this->get('security.context')->getToken();
        if ($request->get('reload') && $request->get('reload') == 'true') {
            CategoryCache::getInstance($user)->clear();
        }

        $categoryName = $this->getRequest()->get('node');
        $categoryName = str_replace("%2520", "%20", $categoryName);

        if ($categoryName == "root" || empty($categoryName)) {
            $categoryName = "";
            $breadCrumb = "";
        } else {
            $breadCrumb = urldecode($categoryName) . "/";
        }

        $categories = CategoryCache::getInstance($user)->getCachedCategories($categoryName);
        $array = array();

        $iconClasses = array("tag_green", "tag_orange", "tag_pink", "tag_purple", "tag_yellow");

        if (count($categories->items) > 0) {
            $match = array();
            $count = preg_match_all("#/#eis", $breadCrumb, $match);
            $iconCls = $count > 4 ? 'tag_red' : $iconClasses[$count];
            foreach ($categories->items as $item) {
                $array[] = array(
                    "cls" => "folder",
                    "id" => str_replace(" ", "%20", $breadCrumb . $item->name),
                    "nodeId" => str_replace("workspace://SpacesStore/", "", $item->nodeRef),
                    "leaf" => !$item->hasChildren,
                    "iconCls" => 'ifresco-category-' . $iconCls,
                    "text" => $item->name,
                    "qtip" => $item->description
                );
            }
        }

        return new JsonResponse($array);
    }

    public function getTreeSoapAction(Request $request)
    {
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        if ($request->get('reload') == "true") {
            CategoryCache::getInstance($user)->clear();
        }

        $session = $user->getSession();
        $spacesStore = new SpacesStore($session);

        $dirVar = str_replace('/', '', $request->get('node'));
        if (empty($dirVar) || $dirVar == 'root') {
            $rootNode = $spacesStore->getCategoryRoot();
        } else {
            $rootNode = $session->getNode($spacesStore, $dirVar);
        }

        $array = array();

        if ($rootNode != null && count($rootNode->children) > 0) {
            foreach ($rootNode->children as $child) {
                $node = $session->getNode($spacesStore, $child->child->id);
                if ($node->type == "{http://www.alfresco.org/model/content/1.0}category") {
                    $array[] = array(
                        "cls" => "folder",
                        "id" => $node->getId(),
                        "checked" => false,
                        "leaf" => (count($node->children) > 0 ? false : true),
                        "expanded" => false,
                        "path" => $node->getFolderPath() . "/" . $node->cm_name . "/",
                        "iconCls" => 'category_tag_green',
                        "qpath" => $node->getRealPath(),
                        "text" => $node->cm_name,
                        "qtip" => $node->cm_description == null ? '' : $node->cm_description
                    );
                }
            }
        }

        return new JsonResponse($array);
    }
    
    public function addCategoryAction(Request $request) {
    	$nodeId = $request->get('nodeId');
    	$value = $request->get('value');
    
    	$return = array("success"=>false);
    
    	$user = $this->get('security.context')->getToken();
    	$repository = $user->getRepository();
    	$session = $user->getSession();
    	$ticket = $user->getTicket();
    
    
    	$spacesStore = new SpacesStore($session);
    	$catHome = $spacesStore->getCategoryRoot();
    	$Classification = new Classification($repository, $spacesStore, $session);
    	try {
    		if ($nodeId != "root") {
    			$Node = $session->getNode($spacesStore, $nodeId);
    
    			if ($Node != null) {
    				$CatNode = $Classification->addCategory($value,$Node);
    				if ($CatNode != false) {
    					$session->save();
    					$return["success"] = true;
    					$return["nodeId"] = $CatNode->getId();
    
    				}
    			}
    		}
    		else {
    			$CatNode = $Classification->addCategory($value);
    			if ($CatNode != false) {
    				$session->save();
    				$return["success"] = true;
    				$return["nodeId"] = $CatNode->getId();
    			}
    		}
    		CategoryCache::getInstance($user)->cleanDir();
    	}
    	catch (\Exception $e) {
    		$return["success"] = false;
    	}
    
    	return new JsonResponse($return);
    }
    
    public function removeCategoryAction(Request $request) {
    	$nodeId = $request->get('nodeId');
    
    	$return = array("success"=>false);
    
    	$user = $this->get('security.context')->getToken();
    	$repository = $user->getRepository();
    	$session = $user->getSession();
    	$ticket = $user->getTicket();
    
    
    	$spacesStore = new SpacesStore($session);
    	$Classification = new Classification($repository, $spacesStore, $session);
    	try {
    		$Node = $session->getNode($spacesStore, $nodeId);
    		if ($Node != null) {
    			$Classification->removeCategory($Node);
    			$session->save();
    			$return["success"] = true;
    			CategoryCache::getInstance($user)->cleanDir();
    		}
    	}
    	catch (\Exception $e) {
    		$return["success"] = false;
    	}
    
    	return new JsonResponse($return);
    }
    
    public function editCategoryAction(Request $request) {
    	$nodeId = $request->get('nodeId');
    	$value = $request->get('value');
    
    	$return = array("success"=>false);
    
    	$user = $this->get('security.context')->getToken();
    	$repository = $user->getRepository();
    	$session = $user->getSession();
    	$ticket = $user->getTicket();
    
    
    	$spacesStore = new SpacesStore($session);
    
    	try {
    		$Node = $session->getNode($spacesStore, $nodeId);
    		if ($Node != null) {
    			$Node->cm_name = $value;
    			$session->save();
    			$return["success"] = true;
    			CategoryCache::getInstance($user)->cleanDir();
    		}
    	}
    	catch (\Exception $e) {
    		$return["success"] = false;
    	}
    
    	return new JsonResponse($return);
    } 
}
