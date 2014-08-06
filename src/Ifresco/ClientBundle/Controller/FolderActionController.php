<?php

namespace Ifresco\ClientBundle\Controller;

use Ifresco\ClientBundle\Component\Alfresco\SpacesStore;
use Ifresco\ClientBundle\Component\Alfresco\Lib\NodeCache;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Ifresco\ClientBundle\Security\User;

class FolderActionController extends Controller
{
    public function viewShareAction(Request $request)
    {
    }

    public function createSpaceAction(Request $request)
    {
        $json = array();
        $json['jsonrpc'] = "2.0";
        $json["success"] = "false";
        $json["message"] = $this->get('translator')->trans("Something went wrong. Please contact the Administrator!");

        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        $session = $user->getSession();
        $spacesStore = new SpacesStore($session);
        $companyHome = $spacesStore->companyHome;

        $nodeId = $request->get('nodeId');

        if (empty($nodeId) || $nodeId=="root") {
            $mainNode = $companyHome;
        } else {
            $mainNode = NodeCache::getInstance()->getNode($session, $spacesStore, $nodeId);
        }

        try {
            $properties = $request->get('properties');
                if (count($properties) > 0) {
                    $folderName = $properties["cm_name"];
                    if (!empty($folderName)) {
                        $contentNode = $mainNode->createChild("cm_folder", "cm_contains", "cm_" . $folderName);
                        foreach ($properties as $key => $value) {
                            $contentNode->{$key} = $value;
                        }
                        $session->save();
                        $json["success"] = "true";
                        $json["data"] = array("nodeId" => $contentNode->getId(), "text" => $properties["cm_name"], "title"=>$properties["cm_title"]);
                        $json["message"] = $this->get('translator')->trans("Successfully created the Space %1%" , array("%1%"=>$folderName));

                        NodeCache::getInstance()->clearNodeCache($nodeId);
                    }
                }
        } catch (\Exception $e) {}

        $response = new JsonResponse($json);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }
}
