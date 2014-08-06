<?php

namespace Ifresco\ClientBundle\Controller;

use Ifresco\ClientBundle\Component\Alfresco\ContentData;
use Ifresco\ClientBundle\Component\Alfresco\Lib\NodeCache;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTContent;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTUpload;
use Ifresco\ClientBundle\Component\Alfresco\SpacesStore;
use Ifresco\ClientBundle\Security\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTQuickShare;
use Ifresco\ClientBundle\Component\Alfresco\Lib\Registry;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTTransport;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTocr;

class NodeActionController extends Controller
{
    public function indexAction(Request $request)
    {
    }
    
    public function moveToAction(Request $request) {
    	
    	$items = $request->get('items');
    	$destination = $request->get('destination');
    	
    	$data = array("success"=>false,"message"=>null);
    	
    	try {
    		$items = json_decode($items);
    		
	    	$user = $this->get('security.context')->getToken();
	    	$repository = $user->getRepository();
	    	$session = $user->getSession();
	    	$ticket = $user->getTicket();
	    	 
	    	$spacesStore = new SpacesStore($session);
	    	 
	    	 
	    	$RestTransport = new RESTTransport($repository,$spacesStore,$session);
	
	    	$return = $RestTransport->MoveTo($destination,$items);

	    	$data["success"] = true;
	    	$data = array_merge($data,(array)$return);
    	} 
    	catch (\Exception $e) {
    		$data["message"] = $e->getMessage();
    	}
    	
    	return new JsonResponse($data);
    }

    public function downloadAction($nodeId)
    {
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        $session = $user->getSession();
        $spacesStore = new SpacesStore($session);
        $node = NodeCache::getInstance()->getNode($session, $spacesStore, $nodeId);

        if ($node != null) {
            $contentData = $node->cm_content;
            if ($contentData != null && $contentData instanceof ContentData) {
                $url = $contentData->getUrl();
                $mime = $contentData->getMimetype();
                $size = $contentData->getSize();

                $name = $node->cm_name;
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header("Content-Type: " . $mime);
                header("Content-Length: " . $size);
                header('Content-Disposition: attachment; filename="' . $name . '"');
                readfile($url);
                die();
            }
        }

        return new JsonResponse($this->get('translator')->trans('Incorrect request.'));
    }

    public function createHtmlAction(Request $request)
    {
        $name = $request->get('name');
        $title = $request->get('title');
        $description = $request->get('description');
        $content = $request->get('content');
        $nodeId = $request->get('nodeId');

        $response = array("success" => false);

        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        $repository = $user->getRepository();
        $session = $user->getSession();
        $spacesStore = new SpacesStore($session);
        $tmp_file = $this->get('kernel')->getCacheDir() . "/" . time().".html";

        file_put_contents($tmp_file, $content);

        $restUpload = new RESTUpload($repository, $spacesStore, $session);

        $name = preg_replace('/\.html?$/i', '', $name) . '.html';
        try {
            $uploadResult = $restUpload->UploadNewContent($tmp_file, $name, 'cm:content', "workspace://SpacesStore/" . $nodeId);

            if (!isset($uploadResult->nodeRef) || $uploadResult->status->code != 200) {
                throw new \Exception();
            }

            $newNodeId = preg_replace('/^.*\//i', '', $uploadResult->nodeRef);

            $node = $session->getNode($spacesStore, $newNodeId);
            $node->addAspect('app_inlineeditable');
            $node->cm_name = $name;
            $node->cm_title = $title;
            $node->cm_description = $description;
            $node->getContent()->setMimetype('text/html');
            $session->save();
            $response["success"] = true;
        } catch (\Exception $e) {
            $response["success"] = false;
        }

        try {unlink($tmp_file);} catch (\Exception $e) {}

        $response = new JsonResponse($response);
        return $response;
    }

    public function deleteNodeAction(Request $request)
    {
        $returnArr = array("success" => "false");

        $nodeId = $request->get('nodeId');
        $type = $request->get('nodeType');

        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        $repository = $user->getRepository();
        $session = $user->getSession();
        $spacesStore = new SpacesStore($session);
        $restContent = new RESTContent($repository, $spacesStore, $session);

        if (empty($type)) {
            $type = "file";
        }

        try {
            if ($type == "file") {
                $restContent->DeleteNode($nodeId);
            } else {
                $restContent->DeleteSpace($nodeId);
            }
            $returnArr["success"] = true;

            NodeCache::getInstance()->clean();
        } catch (\Exception $e) {
            $returnArr["errorMsg"] = $e->getMessage();
            $returnArr["success"] = false;
        }

        $response = new JsonResponse($returnArr);
        return $response;
    }
    
    public function deleteNodesAction(Request $request) {
    	$returnArr = array("success"=>"false","deleted"=>0,"count"=>0);
    
    	$nodes = $request->get('nodes');
    
    	$user = $this->get('security.context')->getToken();
    	$repository = $user->getRepository();
    	$session = $user->getSession();
    	$ticket = $user->getTicket();
    
    	$spacesStore = new SpacesStore($session);
    
    	$RestContent = new RESTContent($repository,$spacesStore,$session);
    
    	if (!empty($nodes)) {
    		try {
    			$nodes = json_decode($nodes);
    			if (is_array($nodes)) {
    				$countSucces = 0;
    				$countFiles = count($nodes);
    
    				foreach ($nodes as $Node) {
    					$type = $Node->shortType;
    					$nodeId = $Node->nodeRef;

    					try {
    						if ($type == "file") {
    							$RestContent->DeleteNode($nodeId);
    						}
    						else {
    							$RestContent->DeleteSpace($nodeId);
    						}
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
    

    public function downloadNodesAction(Request $request)
    {
        $nodes = json_decode($request->get('nodes'));
        $zip = new \ZipArchive();

        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        $session = $user->getSession();
        $spacesStore = new SpacesStore($session);
        $zip_name = time() . '.zip';
        $zip_file = $this->get('kernel')->getCacheDir() . "/" . $zip_name;

        if ($zip->open($zip_file, \ZipArchive::CREATE) === true) {
            if(is_array($nodes) && count($nodes) > 0) {
                foreach($nodes as $key=>$nodeId) {
                    $node = NodeCache::getInstance()->getNode($session, $spacesStore, $nodeId);
                    if($key == 0) {
                        $zip_name = $node->cm_name . '.zip';
                    }

                    if ($node != null) {
                        $this->addZipContent($zip, $node);
                    }
                }
            }

            $zip->close();

            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Content-Type: application/octet-stream");
            header("Content-Length: " . filesize($zip_file));
            header('Content-Disposition: attachment; filename="' . $zip_name . '"');
            readfile($zip_file);
            unlink($zip_file);
        }

        die();
    }

    /**
     * @param \ZipArchive $zip
     * @param NodeCache $node
     * @param string $path
     */
    private function addZipContent($zip, $node, $path = '') {

        if($node->getType() == '{http://www.alfresco.org/model/content/1.0}folder') {
            $this->addZipFolder($zip, $node, $path);
        }

        $contentData = $node->cm_content;
        if ($contentData != null && $contentData instanceof ContentData) {
            $name = $node->cm_name;
            $zip->addFromString($path . iconv("UTF-8", "cp850", $name), $contentData->getContent());
        }
    }

    /**
     * @param \ZipArchive $zip
     * @param  $node
     * @param string $path
     */
    private function addZipFolder($zip, $node, $path = '') {
        $children = $node->getChildren();
        $path .= iconv("UTF-8", "cp850", $node->cm_name) . '/';
        $zip->addEmptyDir($path);

        if(count($children) > 0) {
            foreach($children as $child) {
                $this->addZipContent($zip, $child->getChild(), $path);
            }
        }
    }
    
    
    public function unshareDocAction(Request $request) {
    
    	$nodeId = $request->get('nodeId');
    
    	$user = $this->get('security.context')->getToken();
    	$repository = $user->getRepository();
    	$session = $user->getSession();
    	$ticket = $user->getTicket();
    
    	$spacesStore = new SpacesStore($session);
    
    	$Node = NodeCache::getInstance()->getNode($session, $spacesStore, $nodeId);
    	$response = array();
    	$response["success"] = true;
    
    	if ($Node != null) {
    		$RESTQuickShare = new RESTQuickShare($repository, $spacesStore, $session);
    		$RESTQuickShare->unshareNode($Node->qshare_sharedId);
    	}
    
    	$response = new JsonResponse($response);
    	return $response;
    }
    
    public function shareDocAction(Request $request) {
    
    	$nodeId = $request->get('nodeId');
    
    	$user = $this->get('security.context')->getToken();
    	$repository = $user->getRepository();
    	$session = $user->getSession();
    	$ticket = $user->getTicket();
    
    	$spacesStore = new SpacesStore($session);
    
    	$Node = NodeCache::getInstance()->getNode($session, $spacesStore, $nodeId);
    	$response = array();
    	$response["success"] = true;
    
    	if ($Node != null) {
    		$RESTQuickShare = new RESTQuickShare($repository, $spacesStore, $session);
    		$shareResult = $RESTQuickShare->shareNode($Node->getId());
    		$response['sharedId'] = isset($shareResult->sharedId)?$shareResult->sharedId:'';
    		$response['sharedBy'] = isset($shareResult->sharedId)?$user->getUsername():'';
    	}
    
    	$response = new JsonResponse($response);
    	return $response;
    }

    public function mailNodeAction(Request $request) {
    	$returnArr = array("success"=>"false","errorMsg"=>"unknown");
    
    	$nodes = $request->get('nodes');
    	$emailTo = $request->get('to');
    	$emailCC = $request->get('cc');
    	$emailBCC = $request->get('bcc');
    	$emailBody = $request->get('body');
    	$emailSubject = $request->get('subject');
    
    	try {
    		if (preg_match("/,/eis",$emailTo))
    			$emailTo = explode(",",$emailTo);
    
    		if (preg_match("/,/eis",$emailCC))
    			$emailCC = explode(",",$emailCC);
    
    		if (preg_match("/,/eis",$emailBCC))
    			$emailBCC = explode(",",$emailBCC);
    
    		$user = $this->get('security.context')->getToken();
    		$repository = $user->getRepository();
    		$session = $user->getSession();
    		$ticket = $user->getTicket();
    
    		$spacesStore = new SpacesStore($session);
    
    		if (empty($emailTo)) {
    			throw new \Exception("Missing parameters (Required: To)");
    		}
    
    
    		$SMTP_HOST = Registry::getSetting("SMTP_HOST");
    		$SMTP_PORT = Registry::getSetting("SMTP_PORT");
    
    		if (empty($SMTP_HOST) || $SMTP_HOST == null || empty($SMTP_PORT) || $SMTP_PORT == null)
    			throw new \Exception("No SMTP Server is set");
    
    		$SMTP_AUTH = Registry::getSetting("SMTP_AUTH");
    		$SMTP_USERNAME = Registry::getSetting("SMTP_USERNAME");
    		$SMTP_PASSWORD = Registry::getSetting("SMTP_PASSWORD");
    
    		if ($SMTP_AUTH == "true")
    		if (empty($SMTP_USERNAME) || empty($SMTP_PASSWORD) || $SMTP_USERNAME == null || $SMTP_PASSWORD == null)
    			throw new \Exception("SMTP Authentication active but no Username or Password is set");
    
    		$FROM_EMAIL = Registry::getSetting("FROM_EMAIL");
    		if (empty($FROM_EMAIL) || $FROM_EMAIL == null)
    			$FROM_EMAIL = "noreplay@localhost.com";
    
    		$FROM_NAME = Registry::getSetting("FROM_NAME");
    		if (empty($FROM_NAME) || $FROM_NAME == null)
    			$FROM_NAME = "ifresco client";
    
    		if (empty($emailSubject))
    			$emailSubject = $this->get('translator')->trans("ifresco - send files");
    
    		if (!empty($nodes)) {
    
    			$nodes = json_decode($nodes);
    
    			if (is_array($nodes)) {
    				$countFiles = count($nodes);
    				

    				$transport = \Swift_SmtpTransport::newInstance($SMTP_HOST, $SMTP_PORT);
    				if ($SMTP_AUTH == "true") {
    					$transport->setUsername($SMTP_USERNAME)
  								  ->setPassword($SMTP_PASSWORD);
    				}

    				$mailer = \Swift_Mailer::newInstance($transport);
    				
    				$message = \Swift_Message::newInstance()
    						->setSubject(utf8_decode($emailSubject))
    						->setFrom(array($FROM_EMAIL => $FROM_NAME))
    				;
    				
    				$tmpFile = array();
    
    				foreach ($nodes as $Node) {
    					$type = $Node->shortType;
    					$nodeId = $Node->nodeId;
    
    					try {
    						if ($type == "file") {
    							$Node = NodeCache::getInstance()->getNode($session, $spacesStore, $nodeId);
    
    							if ($Node!=null) {
    								$Content = $Node->cm_content;
    
    								if ($Content instanceof ContentData && !empty($Node->cm_name)) {
    									$fileName = $this->get('kernel')->getCacheDir()."/".$Node->cm_name;
    
    									if (file_exists($fileName))
    										$fileName = $this->get('kernel')->getCacheDir()."/".date("d.m.Y")."-".$Node->cm_name;
    									$Content->readContentToFile($fileName);
    									
    									$message->attach(
										  \Swift_Attachment::fromPath($fileName)->setFilename($Node->cm_name)
										);

    									$tmpFile[] = $fileName;
    								}
    							}
    						}
    					}
    					catch (\Exception $e) {
    						$returnArr["errorMsg"] = $e->getMessage();
    					}
    				}
    
    				if (empty($emailBody))
    					$emailBody = "<p></p>";
    
    				/*$message->setBody(strip_tags(utf8_decode($emailBody)))
    						->addPart(utf8_decode($emailBody), 'text/html');*/
    				$message->setBody(utf8_decode($emailBody));
    
    				if (is_array($emailTo)) {
    					foreach ($emailTo as $emailAddy) {
    						if (!empty($emailAddy))
    							$message->addTo($emailAddy);
    					}
    				}
    				else
    					$message->addTo($emailTo);
    
    				
    
    				if (!empty($emailCC)) {
    					if (is_array($emailCC)) {
    						foreach ($emailCC as $emailAddy) {
    							if (!empty($emailAddy))
    								$message->addCc($emailAddy);
    						}
    					}
    					else
    						$message->addCc($emailCC);
    				}
    
    				if (!empty($emailBCC)) {
    					if (is_array($emailBCC)) {
    						foreach ($emailBCC as $emailAddy) {
    							if (!empty($emailAddy))
    								$message->addBcc($emailAddy);
    						}
    					}
    					else
    						$message->addBcc($emailBCC);
    				}
    
    				$send = $mailer->send($message);

    				if (count($tmpFile) > 0) {
    					foreach ($tmpFile as $file) {
    						@unlink($file);
    					}
    				}
    
    				$returnArr["success"] = ($send == 1 ? true : false);
    				if ($send == true)
    					unset($returnArr["errorMsg"]);
    			}
    		}
    
    	}
    	catch (\Exception $e) {
    		$returnArr["errorMsg"] = $e->getMessage();
    		$returnArr["success"] = false;
    	}
    
    	$response = new JsonResponse($returnArr);
    	return $response;
    }
    
    public function listAvailableTransformationsAction(Request $request) {
    	$nodeIds = $request->get('nodeId');
    	$files = $request->get('files');
    
    	if($nodeIds && !is_array($nodeIds)) $nodeIds = array($nodeIds);
    	if($files && !is_array($files)) $files = array($files);
    
    	$user = $this->get('security.context')->getToken();
    	$repository = $user->getRepository();
    	$session = $user->getSession();
    	$spacesStore = new SpacesStore($session);
    	$RESTocr = new RESTocr($repository, null, $session);
    	$config = $RESTocr->fetchConfig();
    	$transfor = isset($config->transformations)?$config->transformations:false;
    
    	$generalList = false;
    
    	$initTypes = array();
    
    	if($nodeIds)
    	foreach($nodeIds as $nodeId) {
    		$Node = NodeCache::getInstance()->getNode($session, $spacesStore, $nodeId);
    
    		$initTypes[] = $Node->getContent()->getMimetype();
    	}
    
    	if($files)
    	foreach($files as $file) {
    	
    		$initTypes[] = $this->getMimeTypeByFile($file);
    	}

    	foreach($initTypes as $mimeType) {
    
    		$responseList = $RESTocr->getAvailableTransformations($mimeType);
    
    		$mimetypes = isset($responseList->mimetypes)?$responseList->mimetypes:array();

    		if(count($mimetypes) > 0) {
    			usort($mimetypes, function ($a, $b) {
    				return strnatcmp($a->extension, $b->extension);
    			});
    
    				if ($generalList === false) {
    					$generalList = $mimetypes;
    				}
    				else {
    					$newList = array();
    					foreach($generalList as $type) {
    						//$type->mimetype
    						$found = false;
    						array_walk($mimetypes, function ($item) use (&$found, $type) {
    							if ($item->mimetype == $type->mimetype) {
    								$found = true;
    							}
    						});
    						if($found) $newList[] = $type;
    					}
    					$generalList = $newList;
    				}
    		}
    	}

    	if (count($generalList) > 0 && is_array($generalList)) {
	    	foreach($generalList as $k=>$type) {
	    		$type->fullName = '<b>' . $type->extension . '</b> - ' . $type->name; // . ' - ' . $type->mimetype;
	    		$type->engine = $this->_getEngineByTargetSource($type->mimetype, $mimeType, $transfor);
	    		
	 
	    		if($type->engine) {
	    			$img = $this->container->get('templating.helper.assets')->getUrl("bundles/ifrescoclient/images/icon/server.png");
	    			$type->fullName = '<img src="'.$img.'" border="0" align="absmiddle"> '.$type->fullName . ' - ' . $type->engine;
	    		}
	    		else {
	    			$img = $this->container->get('templating.helper.assets')->getUrl("bundles/ifrescoclient/images/icon/alfresco.png");
	    			$type->fullName = '<img src="'.$img.'" border="0" align="absmiddle"> '.$type->fullName;
	    		}
	    	}
    	}
    
    
    	$response = new JsonResponse(array('mimetypes' => $generalList));
    	return $response;
    }
    
    public function doNodeTransformationAction(Request $request) {
    	$nodeIds = $request->get('nodeId');
    	$targetMimetype = $request->get('targetMimetype');
    	$overwriteSourceNode = $request->get('overwriteSourceNode');
    	$overwriteTargetNode = $request->get('overwriteTargetNode');
    	$additionals = $request->get('additionals', array());
    
    	$user = $this->get('security.context')->getToken();
    	$repository = $user->getRepository();
    	$session = $user->getSession();
    	$spacesStore = new SpacesStore($session);
    
    	if(!is_array($nodeIds)) $nodeIds = array($nodeIds);
    
    	$result = array(
    			'transformationCount' => 0,
    			'transformations' => array()
    	);
    
    	foreach ($nodeIds as $nodeId) {
    
    		$Node = NodeCache::getInstance()->getNode($session, $spacesStore, $nodeId);
    
    		$RESTocr = new RESTocr($repository, null, $session);
    		$params = array(
    				'nodeRef' => 'workspace://SpacesStore/'.$Node->getId(),
    				//'targetMimetype' => $targetMimetype,
    				'additionals' => $additionals,
    				'overwriteSourceNode' => $overwriteSourceNode,
    				'overwriteTargetNode' => $overwriteTargetNode
    		);
    
    		$response = $RESTocr->doTransformation($params);
    
    		if(isset($response->transformationCount)) {
    			$result['transformationCount'] += $response->transformationCount;
    		}
    
    		if(isset($response->transformations)) {
    			$result['transformations'] = array_merge($response->transformations, $result['transformations']);
    		}
    	}
    
    	$response = new JsonResponse($result);
    	return $response;
    }
    
    private function _getEngineByTargetSource($target, $source, $transform) {
    
    	foreach($transform as $v) {
    		if($v->targetMimetype == $target && $v->sourceMimetype == $source) {
    			return $v->settingsName;
    		}
    	}
    
    	return false;
    }
    
    private function getMimeTypeByFile($file) {
    	$ext = pathinfo($file, PATHINFO_EXTENSION);
    
    	$types = $this->_buildMimeArray();
    	return isset($types[$ext]) ? $types[$ext] : '';
    }
    
    private function _buildMimeArray() {
    	return array(
    			"ez" => "application/andrew-inset",
    			"hqx" => "application/mac-binhex40",
    			"cpt" => "application/mac-compactpro",
    			"doc" => "application/msword",
    			"bin" => "application/octet-stream",
    			"dms" => "application/octet-stream",
    			"lha" => "application/octet-stream",
    			"lzh" => "application/octet-stream",
    			"exe" => "application/octet-stream",
    			"class" => "application/octet-stream",
    			"so" => "application/octet-stream",
    			"dll" => "application/octet-stream",
    			"oda" => "application/oda",
    			"pdf" => "application/pdf",
    			"ai" => "application/postscript",
    			"eps" => "application/postscript",
    			"ps" => "application/postscript",
    			"smi" => "application/smil",
    			"smil" => "application/smil",
    			"wbxml" => "application/vnd.wap.wbxml",
    			"wmlc" => "application/vnd.wap.wmlc",
    			"wmlsc" => "application/vnd.wap.wmlscriptc",
    			"bcpio" => "application/x-bcpio",
    			"vcd" => "application/x-cdlink",
    			"pgn" => "application/x-chess-pgn",
    			"cpio" => "application/x-cpio",
    			"csh" => "application/x-csh",
    			"dcr" => "application/x-director",
    			"dir" => "application/x-director",
    			"dxr" => "application/x-director",
    			"dvi" => "application/x-dvi",
    			"spl" => "application/x-futuresplash",
    			"gtar" => "application/x-gtar",
    			"hdf" => "application/x-hdf",
    			"js" => "application/x-javascript",
    			"skp" => "application/x-koan",
    			"skd" => "application/x-koan",
    			"skt" => "application/x-koan",
    			"skm" => "application/x-koan",
    			"latex" => "application/x-latex",
    			"nc" => "application/x-netcdf",
    			"cdf" => "application/x-netcdf",
    			"sh" => "application/x-sh",
    			"shar" => "application/x-shar",
    			"swf" => "application/x-shockwave-flash",
    			"sit" => "application/x-stuffit",
    			"sv4cpio" => "application/x-sv4cpio",
    			"sv4crc" => "application/x-sv4crc",
    			"tar" => "application/x-tar",
    			"tcl" => "application/x-tcl",
    			"tex" => "application/x-tex",
    			"texinfo" => "application/x-texinfo",
    			"texi" => "application/x-texinfo",
    			"t" => "application/x-troff",
    			"tr" => "application/x-troff",
    			"roff" => "application/x-troff",
    			"man" => "application/x-troff-man",
    			"me" => "application/x-troff-me",
    			"ms" => "application/x-troff-ms",
    			"ustar" => "application/x-ustar",
    			"src" => "application/x-wais-source",
    			"xhtml" => "application/xhtml+xml",
    			"xht" => "application/xhtml+xml",
    			"zip" => "application/zip",
    			"au" => "audio/basic",
    			"snd" => "audio/basic",
    			"mid" => "audio/midi",
    			"midi" => "audio/midi",
    			"kar" => "audio/midi",
    			"mpga" => "audio/mpeg",
    			"mp2" => "audio/mpeg",
    			"mp3" => "audio/mpeg",
    			"aif" => "audio/x-aiff",
    			"aiff" => "audio/x-aiff",
    			"aifc" => "audio/x-aiff",
    			"m3u" => "audio/x-mpegurl",
    			"ram" => "audio/x-pn-realaudio",
    			"rm" => "audio/x-pn-realaudio",
    			"rpm" => "audio/x-pn-realaudio-plugin",
    			"ra" => "audio/x-realaudio",
    			"wav" => "audio/x-wav",
    			"pdb" => "chemical/x-pdb",
    			"xyz" => "chemical/x-xyz",
    			"bmp" => "image/bmp",
    			"gif" => "image/gif",
    			"ief" => "image/ief",
    			"jpeg" => "image/jpeg",
    			"jpg" => "image/jpeg",
    			"jpe" => "image/jpeg",
    			"png" => "image/png",
    			"tiff" => "image/tiff",
    			"tif" => "image/tiff", // FIX alfresco only knows image/tiff
    			"djvu" => "image/vnd.djvu",
    			"djv" => "image/vnd.djvu",
    			"wbmp" => "image/vnd.wap.wbmp",
    			"ras" => "image/x-cmu-raster",
    			"pnm" => "image/x-portable-anymap",
    			"pbm" => "image/x-portable-bitmap",
    			"pgm" => "image/x-portable-graymap",
    			"ppm" => "image/x-portable-pixmap",
    			"rgb" => "image/x-rgb",
    			"xbm" => "image/x-xbitmap",
    			"xpm" => "image/x-xpixmap",
    			"xwd" => "image/x-windowdump",
    			"igs" => "model/iges",
    			"iges" => "model/iges",
    			"msh" => "model/mesh",
    			"mesh" => "model/mesh",
    			"silo" => "model/mesh",
    			"wrl" => "model/vrml",
    			"vrml" => "model/vrml",
    			"css" => "text/css",
    			"html" => "text/html",
    			"htm" => "text/html",
    			"asc" => "text/plain",
    			"txt" => "text/plain",
    			"rtx" => "text/richtext",
    			"rtf" => "text/rtf",
    			"sgml" => "text/sgml",
    			"sgm" => "text/sgml",
    			"tsv" => "text/tab-seperated-values",
    			"wml" => "text/vnd.wap.wml",
    			"wmls" => "text/vnd.wap.wmlscript",
    			"etx" => "text/x-setext",
    			"xml" => "text/xml",
    			"xsl" => "text/xml",
    			"mpeg" => "video/mpeg",
    			"mpg" => "video/mpeg",
    			"mpe" => "video/mpeg",
    			"qt" => "video/quicktime",
    			"mov" => "video/quicktime",
    			"mxu" => "video/vnd.mpegurl",
    			"avi" => "video/x-msvideo",
    			"movie" => "video/x-sgi-movie",
    			"ice" => "x-conference-xcooltalk",
    			"docx"=>"application/msword",
    			"xls"=>"application/vnd.ms-excel",
    			"ppt"=>"application/vnd.ms-powerpoint",
    			"3gp"=>"video/3gpp",
    			"jsc"=>"application/javascript",
    			"php"=>"text/html"
    	);
    }
}
