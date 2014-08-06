<?php
namespace Ifresco\ClientBundle\Controller;

use Ifresco\ClientBundle\Component\Alfresco\Lib\NodeCache;
use Ifresco\ClientBundle\Component\Alfresco\Node;
use Ifresco\ClientBundle\Component\Alfresco\SpacesStore;
use Ifresco\ClientBundle\Security\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTDoclib;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTifrescoScripts;

class TreeFolderController extends Controller
{
    public function getTreeAction()
    {
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        $session = $user->getSession();
        $spacesStore = new SpacesStore($session);

        if (isset($_GET["reload"]) && $_GET["reload"] == "true") {
            NodeCache::getInstance()->clear();
        }

        $dirVar = $this->getRequest()->get('node');
        $dirVar = str_replace("/", "", $dirVar);
        $rootNode = empty($dirVar) || $dirVar == "root" ? $spacesStore->companyHome : NodeCache::getInstance()->getNode($session, $spacesStore, $dirVar);
        $array = array();

        if ($rootNode != null) {
            $children = NodeCache::getInstance()->getChildren($rootNode, $dirVar);
            if (count($children) > 0) {
                foreach ($children as $child) {
                    /**
                     * @var Node $node
                     */
                    $node = $session->getNode($spacesStore, $child->child->id);
                    $permissions = $node->getPermissions();

                    if (
                        $node->type == "{http://www.alfresco.org/model/content/1.0}folder" //||
                        //$node->type == "{http://www.alfresco.org/model/site/1.0}sites" ||
                        //$node->type == "{http://www.alfresco.org/model/site/1.0}site"
                    ) {
                        $imageText = '<img src="' . $node->getIconUrl() . '" border="0" align="absmiddle"> <b>' . $node->cm_name . '</b>';
                        $arrVal = array(
                            "cls" => "folder",
                            "id" => $node->getId(),
                            "leaf" => false,
                            "imagetext" => $imageText,
                            "text" => $node->cm_name,
                            "qtip" => $node->cm_title
                        );

                        $arrVal["alfresco_perm_edit"] = $permissions ? $permissions->userAccess->edit : false;
                        $arrVal["alfresco_perm_delete"] = $permissions ? $permissions->userAccess->delete : false;
                        $arrVal["alfresco_perm_cancel_checkout"] = $permissions ? $permissions->userAccess->{"cancel-checkout"} : false;
                        $arrVal["alfresco_perm_create"] = $permissions ? $permissions->userAccess->create : false;
                        $arrVal["alfresco_perm_permissions"] = $permissions ? $permissions->userAccess->permissions : false;
                        $arrVal["alfresco_node_path"] = str_replace('/Company Home', '', $node->getFolderPath(true, true));

                        if ($node->type == "{http://www.alfresco.org/model/site/1.0}sites") {
                            $arrVal["iconCls"] = "sites-icon";
                        }

                        $array[] = $arrVal;
                    } else {
                        // NO FILES IN TREE
                    }
                }
            }
        }

        $this->sortByOld("text", $array, SORT_ASC);

        return new JsonResponse($array);
    }
    
    public function getSiteTreeAction(Request $request)
    {
    	/**
    	 * @var User $user
    	 */
    	$user = $this->get('security.context')->getToken();
    	$session = $user->getSession();
    	$spacesStore = new SpacesStore($session);
    
    	if (isset($_GET["reload"]) && $_GET["reload"] == "true") {
    		NodeCache::getInstance()->clear();
    	}
    
    	$isRootSite = $request->get('isRootSite', "false");
    	$node = $request->get('node', '');
    	$array = array();

    	$rootNode = NodeCache::getInstance()->getNode($session, $spacesStore, $node);
    	$children = null;
    	if ($isRootSite == "true") {
    		$RootChilds = $rootNode->getChildren();
    		if (count($RootChilds) > 0) {
    			$found = false;
    			foreach ($RootChilds as $child) {
    				if ($found)
    					continue;
    				if ($child->cm_name == "documentLibrary") {
    					$rootNode = $child->getChild();
    					$found = true;
    					$node = $rootNode->getId();
    				}
    			}
    		}
    	}
    	
    	if ($rootNode != null) {
    		$children = NodeCache::getInstance()->getChildren($rootNode, $node);
    		if (count($children) > 0) {
    			foreach ($children as $child) {
    				/**
    				 * @var Node $node
    				 */
    				$node = $session->getNode($spacesStore, $child->child->id);
    				$permissions = $node->getPermissions();
    
    				if (
    				$node->type == "{http://www.alfresco.org/model/content/1.0}folder" ||
    				$node->type == "{http://www.alfresco.org/model/site/1.0}sites" ||
    				$node->type == "{http://www.alfresco.org/model/site/1.0}site"
    						) {
    					$imageText = '<img src="' . $node->getIconUrl() . '" border="0" align="absmiddle"> <b>' . $node->cm_name . '</b>';
    					$arrVal = array(
    							"cls" => "folder",
    							"id" => $node->getId(),
    							"leaf" => false,
    							"imagetext" => $imageText,
    							"text" => $node->cm_name,
    							"qtip" => $node->cm_title
    					);
    
    					$arrVal["alfresco_perm_edit"] = $permissions ? $permissions->userAccess->edit : false;
    					$arrVal["alfresco_perm_delete"] = $permissions ? $permissions->userAccess->delete : false;
    					$arrVal["alfresco_perm_cancel_checkout"] = $permissions ? $permissions->userAccess->{"cancel-checkout"} : false;
    					$arrVal["alfresco_perm_create"] = $permissions ? $permissions->userAccess->create : false;
    					$arrVal["alfresco_perm_permissions"] = $permissions ? $permissions->userAccess->permissions : false;
    					$arrVal["alfresco_node_path"] = str_replace('/Company Home', '', $node->getFolderPath(true, true));
    
    					if ($node->type == "{http://www.alfresco.org/model/site/1.0}sites") {
    						$arrVal["iconCls"] = "sites-icon";
    					}
    
    					$array[] = $arrVal;
    				} else {
    					// NO FILES IN TREE
    				}
    			}
    		}
    	}
    
    	$this->sortByOld("text", $array, SORT_ASC);
    
    	return new JsonResponse($array);
    }
    
    
    public function searchFolderAction(Request $request) {
    	$query = $this->getRequest()->get('query',"");
    	$user = $this->get('security.context')->getToken();
    	$repository = $user->getRepository();
    	$session = $user->getSession();
    	$store = new SpacesStore($session);
    	
    	$return = array("success"=>false);
  		try {
  			$Folders = array();
  			if (!empty($query)) {
	  			$RESTDoclib = new RESTifrescoScripts($repository, $store, $session);
	  			$Data = $RESTDoclib->FolderSearch($query);
	  			$Folders = $Data->data;
	  			foreach ($Folders as $Folder) {
	  				$Folder->path = str_replace("/Company Home","",$Folder->path);
	  			}
  			}
  			$return["folders"] = $Folders;
  			$return["success"] = true;
  		}
  		catch (\Exception $e) {
  			$return["message"] = $e->getMessage();
  		}
    	return new JsonResponse($return);
    }
    
    
    /*PRIVATE*/
    
    private function sortByOld($field, &$arr, $sorting = SORT_ASC, $case_insensitive = true)
    {

        if (is_array($arr) && (count($arr) > 0)) {

            if ($case_insensitive == true) $strcmp_fn = "strnatcasecmp"; else
                $strcmp_fn = "strnatcmp";

            if ($sorting == SORT_ASC) {
                $fn = create_function('$a,$b', '
                    if(is_object($a) && is_object($b)){
                        return ' . $strcmp_fn . '($a->' . $field . ', $b->' . $field . ');
                    }else if(is_array($a) && is_array($b)){
                        return ' . $strcmp_fn . '($a["' . $field . '"], $b["' . $field . '"]);
                    }else return 0;
                ');
            } else {
                $fn = create_function('$a,$b', '
                    if(is_object($a) && is_object($b)){
                        return ' . $strcmp_fn . '($b->' . $field . ', $a->' . $field . ');
                    }else if(is_array($a) && is_array($b)){
                        return ' . $strcmp_fn . '($b["' . $field . '"], $a["' . $field . '"]);
                    }else return 0;
                ');
            }
            usort($arr, $fn);

            return true;
        } else {
            return false;
        }
    }
}
