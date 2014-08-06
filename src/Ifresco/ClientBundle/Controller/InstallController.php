<?php

namespace Ifresco\ClientBundle\Controller;

use Exception;
use Ifresco\ClientBundle\Component\Alfresco\Lib\Mimetype\MimetypeHandler;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTifrescoScripts;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTUpload;
use Ifresco\ClientBundle\Component\Alfresco\SpacesStore;
use Ifresco\ClientBundle\Security\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class InstallController extends Controller
{
    private $_repository = null;
    private $_session = null;
    private $_spacesStore = null;
    private $_ifrescoScripts = null;
    /**
     * @var RESTUpload null
     */
    private $_restUpload = null;
    private $_dictionary = null;

    public function indexAction()
    {
        return $this->render('IfrescoClientBundle:Install:index.html.twig');
    }

    public function installScriptsToDictionaryAction()
    {
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        $this->_repository = $user->getRepository();
        $this->_session = $user->getSession();

        $this->_spacesStore = new SpacesStore($this->_session);
        $this->_ifrescoScripts = new RESTifrescoScripts($this->_repository, $this->_spacesStore, $this->_session);
        $this->_restUpload = new RESTUpload($this->_repository, $this->_spacesStore, $this->_session);

        $this->_dictionary = $this->_spacesStore->getDataDictionary();

        $response = array(
            "success" => false,
            "version" => $this->container->getParameter("ifresco.client.version"),
            "upToDate"=>false
        );

        try {
            $this->uploadDictionaryFolder();
            $this->_ifrescoScripts->RefreshWebScripts();
            $this->writeNewVersion();
            $response["success"] = true;
        }
        catch (Exception $e) {

        }

        $return = json_encode($response);
        $response = new Response($return);
        $response->headers->set('Content-Type','application/json; charset=utf-8');
        $response->headers->set('Cache-Control','no-store, no-cache, must-revalidate');
        $response->headers->set('Cache-Control','post-check=0, pre-check=0',false);
        $response->headers->set('Pragma','no-cache');

        return $response;
    }

    private function uploadDictionaryFolder() {

        $WebScriptsNode = $this->createFolder($this->_dictionary, "Web Scripts");
        $WebScriptsATNode = $this->createFolder($WebScriptsNode, "at");
        $WebScriptsXKEYNode = $this->createFolder($WebScriptsATNode, "xkey");
        $WebScriptsIfrescoNode = $this->createFolder($WebScriptsXKEYNode, "ifresco");

        $dir = __DIR__."/../Resources/webscripts/Scripts";
        $this->readDirRecursive($dir, $WebScriptsIfrescoNode);
    }

    private function readDirRecursive($dir, $parentNode) {
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file == "." || $file == ".." || $file == ".svn" || $file == ".git")
                        continue;

                    $path = $dir . "/" . $file;
                    if (is_dir($path)) {
                        $Node = $this->createFolder($parentNode, $file);
                        $this->readDirRecursive($path . "/", $Node);
                    }
                    else {
                        $this->createFile($parentNode, $file, $path);
                    }
                }
                closedir($dh);
            }
        }
    }

    private function createFolder($parent, $name) {
        $checkChild = $parent->hasChild($name);
        if ($checkChild != null) {
            return $checkChild->getChild();
        }

        $node = $parent->createChild("cm_folder", "cm_contains", "cm_" . $name);
        $node->cm_name = $name;
        $this->_session->save();

        return $node;
    }

    private function writeNewVersion() {
        $jsonContent = json_encode(array("version" => $this->container->getParameter("ifresco.client.version")));

        $ifrescoNode = $this->createFolder($this->_dictionary, "ifresco");
        $ifrescoClientNode = $this->createFolder($ifrescoNode, "Client");

        $infoFile = $this->container->getParameter('kernel.root_dir') . '/../tmp/info.json';
        file_put_contents($infoFile, $jsonContent);
        $this->createFile($ifrescoClientNode, "info.json", $infoFile);
        @unlink($infoFile);
    }

    private function createFile($parent, $fileName, $realFile, $fileType = null) {
        $checkChild = $parent->hasChild($fileName);
        $file = null;
        if ($checkChild != null) {
            $file = $checkChild->getChild();
        }

        if ($fileType == null) {
       		//$fileType = $this->mimeContentType(strtolower($fileName));
        	$fileType = "cm:content";
        }
        $parentNodeId = $parent->getId();

        if ($file != null) {
            $nodeId = $file->getId();
            $this->_restUpload->UploadNewVersion(
                $realFile, $fileName, $fileType, "workspace://SpacesStore/" . $nodeId, "", false
            );
        }
        else {
            $this->_restUpload->UploadNewFile(
                $realFile, $fileName, $fileType, "workspace://SpacesStore/" . $parentNodeId, true
            );
        }
    }

    private function mimeContentType($filename) {
        $mime = new MimetypeHandler();
        return $mime->getMimetype($filename);
    }
}
