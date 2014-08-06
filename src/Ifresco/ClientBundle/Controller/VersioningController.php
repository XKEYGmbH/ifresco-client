<?php

namespace Ifresco\ClientBundle\Controller;

use Ifresco\ClientBundle\Component\Alfresco\REST\RESTVersion;
use Ifresco\ClientBundle\Component\Alfresco\SpacesStore;
use Ifresco\ClientBundle\Security\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTUpload;
use Ifresco\ClientBundle\Component\Alfresco\Lib\Mimetype\MimetypeHandler;
use Ifresco\ClientBundle\Component\Alfresco\VersionStore;
use Ifresco\ClientBundle\Component\Alfresco\ContentData;

class VersioningController extends Controller
{
    public function getJSONAction(Request $request)
    {
        $array = array();
        $array["versions"] = array();
        $nodeId = $request->get('nodeId');

        try {
            /**
             * @var User $user
             */
            $user = $this->get('security.context')->getToken();
            $repository = $user->getRepository();
            $session = $user->getSession();
            $spacesStore = new SpacesStore($session);
            $node = $session->getNode($spacesStore, $nodeId);
            
            if ($node != null) {
                $version = new RESTVersion($repository, '', $session);
                $versionResponse = $version->GetVersionInfo($node->getId());

                if (count($versionResponse) > 0 && $node->hasAspect("cm_versionable")) {
                    foreach ($versionResponse as $versionObj) {
                        $versionId = $versionObj->nodeRef;
                        $versionLabel = $versionObj->label;
                        $versionDescription = $versionObj->description;
                        $versionTimestamp = strtotime($versionObj->createdDate);
                        $versionCreator = $versionObj->creator->userName;
                        $versionId = preg_replace("#.*?://.*?/(.*)#is", "$1", $versionId);
                        $array["versions"][] = array(
                            "nodeRef" => $node->getId(),
                            "nodeId" => $versionId,
                            "version" => $versionLabel,
                            "description" => $versionDescription,
                            "date" => $versionTimestamp,
                            "dateFormat" => date($user->getDateFormat() . " " . $user->getTimeFormat(), $versionTimestamp),
                            "author" => $versionCreator
                        );

                    }
                } else {
                    $array["versions"] = array();
                }
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        $response = new JsonResponse($array);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }
    
    public function downloadVersionAction(Request $request) {
    	$nodeId = $request->get('nodeId');
    
    	$user = $this->get('security.context')->getToken();
    	$repository = $user->getRepository();
    	$session = $user->getSession();
    	$ticket = $user->getTicket();
    
    	$versionStore = new VersionStore($session);
    
    	$Node = $session->getNode($versionStore, $nodeId);
    
    	if ($Node != null) {
    		$contentData = $Node->cm_content;
    		$url = "";
    		if ($contentData != null && $contentData instanceof ContentData) {
    			$url = $contentData->getUrl();
    			$mime = $contentData->getMimetype();
    			$encod = $contentData->getEncoding();
    			$size = $contentData->getSize();
    
    			$name = $Node->cm_name;
    
    			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    			header("Content-Type: ".$mime);
    			header("Content-Length: ".$size);
    			header('Content-Disposition: attachment; filename="'.$name.'"');
    			readfile($url);
    
    			die();
    		}
    	}
    
    }
    
    public function viewVersionsAction() {
    
    }
    
    public function revertVersionAction(Request $request) {
    	$nodeId = $request->get('nodeId');
    	$versionNodeId = $request->get('versionNodeId');
    	$VersionLabel = $request->get('version');
    
    	$note = $request->get('note');
    	if (!empty($note)) {
    		//$note = urldecode($note);
    		$note = trim($note);
    	}
    
    	// Webscript has a bug - doesnt work right now - http://issues.alfresco.com/jira/browse/ALF-8225
    	$versionchange = $request->get('versionchange');
    	$majorChange = false;
    	if ($versionchange == "major")
    		$majorChange = true;
    
    	$user = $this->get('security.context')->getToken();
    	$repository = $user->getRepository();
    	$session = $user->getSession();
    	$ticket = $user->getTicket();
    
    	$spacesStore = new SpacesStore($session);
    
    	$array = array("success"=>false,"nodeId"=>$nodeId);
    
    	$Node = $session->getNode($spacesStore, $nodeId);
    	if ($Node != null) {
    		$Version = new RESTVersion($repository,null,$session);
    		$Response = $Version->RevertVersion($nodeId,$VersionLabel,$majorChange,($note));
    		$array["success"] = $Response->success;
    		$this->_serveAutoVersion($nodeId);
    
    	}
    
    	$response = new JsonResponse($array);
    	return $response;
    }
    
    public function createNewVersionAction(Request $request) {
    	$nodeId = $request->get('nodeId');
    	$note = $request->get('note');
    	if (!empty($note)) {
    		//$note = urldecode($note);
    		$note = trim($note);
    	}
    	$versionchange = $request->get('versionchange');
    	$majorChange = false;
    	if ($versionchange == "major")
    		$majorChange = true;
    
    	$user = $this->get('security.context')->getToken();
    	$repository = $user->getRepository();
    	$session = $user->getSession();
    	$ticket = $user->getTicket();
    
    	$spacesStore = new SpacesStore($session);
    
    	$array = array("success"=>false,"nodeId"=>$nodeId);
    	$Node = $session->getNode($spacesStore, $nodeId);
    	if ($Node != null) {
    		try {
    			$versionNode = $Node->createVersion(($note),$majorChange);
    			$session->save();
    			$this->_serveAutoVersion($nodeId);
    			$array["success"] = true;
    		}
    		catch (\Exception $e) {
    
    		}
    	}
    
    	$response = new JsonResponse($array);
    	return $response;
    }
    
    public function uploadNewVersionAction(Request $request) {
    	$nodeId = $request->get('nodeId');
    	$note = $request->get('note');
    	if (!empty($note)) {
    		$note = urldecode($note);
    		$note = trim($note);
    	}
    	$versionchange = $request->get('versionchange');
    	$majorChange = false;
    	if ($versionchange == "major")
    		$majorChange = true;
    
    	$user = $this->get('security.context')->getToken();
    	$repository = $user->getRepository();
    	$session = $user->getSession();
    	$ticket = $user->getTicket();
    
    	$spacesStore = new SpacesStore($session);
    
    	$json = array();
    	try {
    		$Node = $session->getNode($spacesStore, $nodeId);
    
    
    		if ($Node != null) {

    			$json['id'] = "id";
    			$json['jsonrpc'] = "2.0";
    
    			$fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';
    			$fileType = $_FILES['file']['type'];

    			$mimname = "php://input";
    
    			$chunk = isset($_REQUEST["chunk"]) ? $_REQUEST["chunk"] : 0;
    			$chunks = isset($_REQUEST["chunks"]) ? $_REQUEST["chunks"] : 0;
    
    			if(!file_exists($this->get('kernel')->getCacheDir() . '/Upload'))
    				mkdir($this->get('kernel')->getCacheDir() . '/Upload', 0777);
    
    			$tmpFile = $this->get('kernel')->getCacheDir() . '/Upload/'.$fileName;
    			if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
    				$contentType = $_SERVER["HTTP_CONTENT_TYPE"];
    
    			if (isset($_SERVER["CONTENT_TYPE"]))
    				$contentType = $_SERVER["CONTENT_TYPE"];
    
    			$out = fopen($tmpFile, $chunk == 0 ? "wb" : "ab");
    
    			if (strpos($contentType, "multipart") !== false) {
    				if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
    					if ($out) {
    						$in = fopen($_FILES['file']['tmp_name'], "rb");
    						if ($in) {
    							while ($buff = fread($in, 4096))
    								fwrite($out, $buff);
    						}
    						else
    							die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
    						fclose($in);
    						fclose($out);
    						@unlink($_FILES['file']['tmp_name']);
    					} else
    						die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
    				}
    			}
    			else {
    				$out = fopen($tmpFile, $chunk == 0 ? "wb" : "ab");
    				if ($out) {
    					$in = fopen("php://input", "rb");
    					if ($in) {
    						while ($buff = fread($in, 4096))
    							fwrite($out, $buff);
    					} else
    						die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
    					fclose($in);
    					fclose($out);
    				} else
    					die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
    			}
    
    
    			if ((($chunk+1) == $chunks && $chunks > 0) || $chunks == 0) {
    				$fileType = $this->_mime_content_type($fileName);
    
    				$RESTUpload = new RESTUpload($repository,$spacesStore,$session);
    				$UploadResponse = $RESTUpload->UploadNewVersion($tmpFile,$fileName,$fileType,$Node->getId(),$note,$majorChange);
    
    				if ($UploadResponse->status->code == 200) {
    					$json['result'] = "null";
    					$this->_serveAutoVersion($nodeId);
    				}
    				else
    					$json['result'] = "error";
    			}
    
    			$json['result'] = "null";
    		}
    	}
    	catch (\Exception $e) {
    
    	}
    
    	$response = new JsonResponse($json);
    	return $response;
    }
    
    private function _mime_content_type($filename) {
    
    	$mime = new MimetypeHandler();
    	return $mime->getMimetype($filename);
    }
    
    private function _serveAutoVersion($nodeId) {
    	$user = $this->get('security.context')->getToken();
    	$repository = $user->getRepository();
    	$session = $user->getSession();
    	$ticket = $user->getTicket();
    
    	$spacesStore = new SpacesStore($session);
    
    	$Node = $session->getNode($spacesStore, $nodeId);
    
    	if($Node->hasAspect('app_inlineeditable')) {
    		$Node->cm_autoVersion = false;
    		$session->save();
    	}
    }
}
