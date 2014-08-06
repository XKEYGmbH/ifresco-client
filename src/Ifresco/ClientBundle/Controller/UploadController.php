<?php

namespace Ifresco\ClientBundle\Controller;

use Ifresco\ClientBundle\Component\Alfresco\Lib\AutoOCRService;
use Ifresco\ClientBundle\Component\Alfresco\Lib\Mimetype\MimetypeHandler;
use Ifresco\ClientBundle\Component\Alfresco\Lib\Registry;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTContent;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTDictionary;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTocr;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTUpload;
use Ifresco\ClientBundle\Component\Alfresco\SpacesStore;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Ifresco\ClientBundle\Security\User;

class UploadController extends Controller
{
    public function uploadRESTAction(Request $request)
    {
        /**
         * @var User $user
         */
        @set_time_limit(5 * 60);
        $user = $this->get('security.context')->getToken();
        $repository = $user->getRepository();
        $session = $user->getSession();
        $spacesStore = new SpacesStore($session);
        $companyHome = $spacesStore->companyHome;

        $nodeId = $request->get('nodeId');
        $overwrite = (bool)$request->get('overwrite');
        $ocr = (bool)$request->get('ocr');
        $type = (string)$request->get('type');
        $dropboxFile = (string)$request->get('dropboxFile');
        $transform = (string)$request->get('transform');
        $transform = trim($transform);
        if ($transform == null || $transform == 'null')
        	$transform = '';

        if ($type == null || empty($type) || $type == "null") {
            $type = "cm:content";
        }

        try {
            if (empty($nodeId) || $nodeId == "root") {
                $nodeId = $companyHome->getId();
            }

            $newNodeRef = false;
            $json['id'] = "id";
            $json['jsonrpc'] = "2.0";
            $fileName = $request->get("name") ? $request->get("name") : '';

            if($fileName != '') {
                $chunk = $request->get("chunk") ? $request->get("chunk") : 0;
                $chunks = $request->get("chunks") ? $request->get("chunks") : 0;
                $tmpFile = $this->get('kernel')->getCacheDir() . '/Upload/' . $fileName;

                if(!file_exists($this->get('kernel')->getCacheDir() . '/Upload')) {
                    mkdir($this->get('kernel')->getCacheDir() . '/Upload', 0777);
                }

                $contentType = '';
                if (isset($_SERVER["HTTP_CONTENT_TYPE"])) {
                    $contentType = $_SERVER["HTTP_CONTENT_TYPE"];
                }

                if (isset($_SERVER["CONTENT_TYPE"])) {
                    $contentType = $_SERVER["CONTENT_TYPE"];
                }

                $out = fopen($tmpFile, $chunk == 0 ? "wb" : "ab");

                if (strpos($contentType, "multipart") !== false) {
                    if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
                        if ($out) {
                            $in = fopen($_FILES['file']['tmp_name'], "rb");
                            if ($in) {
                                while ($buff = fread($in, 4096))
                                    fwrite($out, $buff);
                            } else {
                                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
                            }
                            fclose($in);
                            fclose($out);
                            @unlink($_FILES['file']['tmp_name']);
                        } else {
                            die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
                        }
                    }
                } else {
                    $out = fopen($tmpFile, $chunk == 0 ? "wb" : "ab");
                    if ($out) {
                        $in = fopen("php://input", "rb");
                        if ($in) {
                            while ($buff = fread($in, 4096))
                                fwrite($out, $buff);
                        } else {
                            die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
                        }
                        fclose($in);
                        fclose($out);
                    } else {
                        die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
                    }
                }
            }

            $RESTUpload = new RESTUpload($repository, $spacesStore, $session);

            if (isset($chunk) && ((($chunk+1) == $chunks && $chunks > 0) || $chunks == 0)) {
                $fileType = $this->getMimeContentType(strtolower($fileName));
                $allowedTypes = array(
                    "application/pdf",
                    "image/tiff",
                    "image/tif",
                    "image/png",
                    "image/jpg",
                    "image/jpeg",
                    "image/gif",
                    "image/bmp"
                );
                try {
                    /*if ($ocr == true && in_array($fileType, $allowedTypes)) {
                        try {
                            $this->AutoOCREndpoint = Registry::getSetting("AutoOCREndpoint","");
                            $this->AutoOCRProfile = Registry::getSetting("AutoOCRProfile","");
                            $this->AutoOCR = new AutoOCRService($this->AutoOCREndpoint);

                            if (empty($this->AutoOCRProfile)) {
                                $result = $this->AutoOCR->GetSettingsCollection();
                                if (isset($result->GetSettingsCollectionResult)) {
                                    $AutoOCRProfile = $result->GetSettingsCollectionResult;
                                }
                                if (count($AutoOCRProfile) > 0) {
                                    $this->AutoOCRProfile = $AutoOCRProfile[0]->SettingsName;
                                }
                            }

                            $Content = file_get_contents($tmpFile);
                            $QueueJobId = $this->addJob("UPLOADING File " . $fileName . " WITH OCR PROCESSING","UPLOADING TO OCR ENGINE");
                            $UploadResult = $this->AutoOCR->UploadContent($Content, $fileName, $this->AutoOCRProfile);

                            if (is_object($UploadResult)) {
                                $UploadResultId = $UploadResult->JobID;
                                $UploadResultGuid = $UploadResult->JobGuid;
                                if ($UploadResultId >= 0 && !empty($UploadResultGuid)) {
                                    $Status = (int)$UploadResult->Status;
                                    $this->lastStatus = $Status;

                                    $this->updateJob($QueueJobId,"PROCESSING");

                                    while ($this->lastStatus != 4 && $this->lastStatus != 6 && $this->lastStatus != 7 || $this->lastStatus == null) {
                                        $StatusResult = $this->AutoOCR->GetStatus($UploadResultGuid);
                                        $Status = (int)$StatusResult->GetStatusResult;
                                        $this->lastStatus = $Status;
                                    }

                                    if ($this->lastStatus == 6 || $this->lastStatus == 7) {
                                        switch ($this->lastStatus) {
                                            case 6:
                                                $this->updateJob($QueueJobId, "CONVERSION ERROR");
                                                break;
                                            case 7:
                                                $this->updateJob($QueueJobId, "EXPIRED");
                                                break;
                                            default:
                                                break;
                                        }
                                        throw new \Exception("ocr engine shows error status " . $this->lastStatus);
                                    } else {
                                        $this->updateJob($QueueJobId,"DOWNLOADING OF OCR ENGINE");
                                        $OCRedContent = $this->AutoOCR->Download($UploadResultGuid);
                                        $this->updateJob($QueueJobId,"DOWNLOADED OF OCR ENGINE");

                                        $fileName = preg_replace("#(.*)(\..*)#is","$1.pdf",$fileName);
                                        $tmpFile = sfConfig::get('sf_cache_dir') . '/Upload/'.$fileName;

                                        file_put_contents($tmpFile, $OCRedContent);

                                        $this->updateJob($QueueJobId, "UPLOADING TO ALFRESCO");

                                    }
                                }
                            }
                            else {
                                $this->updateJob($QueueJobId,"COULDNT UPLOAD FILE TO ENGINE!");
                                //TODO: WTF - code? no such variable at all
                                throw new \Exception("couldnt upload $FileName to OCR Engine");
                            }
                        }
                        catch (\Exception $e) {
                            echo $e->getMessage();
                        }
                    }*/

                    $UploadResult = $RESTUpload->UploadNewFile($tmpFile, $fileName, $type,
                        "workspace://SpacesStore/" . $nodeId, $overwrite);

                    if (!isset($UploadResult->nodeRef) || $UploadResult->status->code != 200) {
                        throw new \Exception();
                    } else {
                        $newNodeRef = $UploadResult->nodeRef;
                    }

                    //if ($ocr == true) {
                    //    $this->updateJob($QueueJobId, "DONE (OCR PROCESSED SUCCESSFULLY)", $UploadResult->nodeRef);
                    //}

                    @unlink($tmpFile);
                } catch (\Exception $e) {
                	$json['error'] = $e->getMessage();
                }
            }

            if($dropboxFile) {
                $dropFile = json_decode($dropboxFile);

                if(!file_exists($this->get('kernel')->getCacheDir() . '/Upload'))
                    mkdir($this->get('kernel')->getCacheDir() . '/Upload', 0777);


                $tmpFile = $this->get('kernel')->getCacheDir() . '/Upload/' . $dropFile->name;
                $tmpContent = file_get_contents($dropFile->link);
                file_put_contents($tmpFile, $tmpContent);

                $UploadResult = $RESTUpload->UploadNewFile($tmpFile, $dropFile->name, $type,
                    "workspace://SpacesStore/" . $nodeId , $overwrite);


                if (!isset($UploadResult->nodeRef) || $UploadResult->status->code != 200) {
                    throw new \Exception();
                    $json['error'] = 'Status code != "00 on upload';
                    $json['result'] = $UploadResult;
                } else {
                    $newNodeRef = $UploadResult->nodeRef;
                }

                @unlink($tmpFile);
            }

            if($newNodeRef && !empty($transform)) {
                $transform = json_decode($transform);

                $RESTocr = new RESTocr($repository, null, $session);
                $params = array(
                    'nodeRef' => $newNodeRef,
                    'additionals' => $transform->{'additionals[]'},
                    'overwriteSourceNode' => false,
                    'overwriteTargetNode' => false
                );

                $traRes = $RESTocr->doTransformation($params);

                $restContent = new RESTContent($repository, $spacesStore, $session);

                if($type && isset($traRes->transformationCount) && $traRes->transformationCount > 0) {
                    $restDictionary = new RESTDictionary($repository, $spacesStore, $session);

                    foreach($traRes->transformations as $transform) {
                        $transRef = $transform->nodeRef;
                        $restDictionary->SpecifyType(str_replace("workspace://SpacesStore/", '', $transRef), $type);
                    }
                }

                $restContent->DeleteNode(str_replace("workspace://SpacesStore/", '', $newNodeRef));
            }

            $json['result'] = "null";
            $json['success'] = true;
        } catch (\Exception $e) {
            $json['msg'] = $e->getMessage();
        }

        $response = new JsonResponse($json);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma','no-cache');
        return $response;
    }

    public function getContentTypesAction()
    {
        /**
         * @var User $user
         */
        $types = array();
        $user = $this->get('security.context')->getToken();
        $repository = $user->getRepository();
        $session = $user->getSession();
        $spacesStore = new SpacesStore($session);

        $restDictionary = new RESTDictionary($repository, $spacesStore, $session);
        $typesFetch = $this->getContentTypesList($restDictionary, null);
        if (count($typesFetch) > 0) {
            foreach ($typesFetch as $type) {
                $name = isset($type->name) ? $type->name : '';
                $title = isset($type->title) ? $type->title : '';
                $description = isset($type->description) ? $type->description : '';

                $title = empty($title) ? $name : $title;

                if($title != '') {
                    $types[] = (object)array(
                        "name" => $name,
                        "title" => $title,
                        "description" => $description
                    );
                }
            }
        }

        if(count($types) == 0) {
            $types[] = array(
                "name" => 'cm:content',
                "title" => 'Content',
                "description" => ''
            );
        }

        $response = new JsonResponse(array('data' => $types));
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }

    private function getMimeContentType($filename) {

        $mime = new MimetypeHandler();
        return $mime->getMimetype($filename);
    }

    private function getContentTypesList($restDictionary, $currentType) {
        $em = $this->getDoctrine()->getManager();
        $types = $em->getRepository('IfrescoClientBundle:AllowedType')->findAll();

        $typeList = array();

        if (count($types) == 0) {

            $subClasses = $restDictionary->GetSubClassDefinitions("cm_content");

            if ($subClasses != null) {
                foreach ($subClasses as $type) {
                    $name = $type->name;
                    if ($currentType != $name) {
                        $typeList[] = $type;
                    }
                }
            }
        } else {
            foreach ($types as $type) {
                try {
                    if ($currentType != $type) {
                        $name = str_replace(":", "_", $type->getName());
                        $TypeInfo = $restDictionary->GetClassDefinitions($name);
                        $typeList[] = $TypeInfo;
                    }
                } catch (\Exception $e) {
                    echo $e->getMessage();
                }
            }
        }
        
        return $typeList;
    }
}
