<?php

namespace Ifresco\ClientBundle\Controller;

use Ifresco\ClientBundle\Component\Alfresco\Lib\NodeCache;
use Ifresco\ClientBundle\Component\Alfresco\Lib\Registry;
use Ifresco\ClientBundle\Component\Alfresco\Node;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTPerson;
use Ifresco\ClientBundle\Component\Alfresco\Session;
use Ifresco\ClientBundle\Component\Alfresco\SpacesStore;
use Ifresco\ClientBundle\Entity\Setting;
use Ifresco\ClientBundle\Security\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;
use Ifresco\ClientBundle\Component\Translation\Translator;
use Ifresco\ClientBundle\Helper\PluginBoot;

class IndexController extends Controller
{
    public function indexAction()
    {
    	$params = array();
    	$PluginBundles = array();
    	$kernel = $this->get('kernel');
    	$PluginBoot = new PluginBoot($kernel->getRootDir());

    	//foreach ($this->container->getParameter('kernel.bundles') as $BundleName => $BundleNamespace) {
    	foreach ($PluginBoot->getEnabledPlugins() as $BundleName => $BundleNamespace) {
    		//if (preg_match("/^Plugin.*/",$BundleName)) {
    			$hasJavascriptResource = false;
    			$hasCssResource = false;
    			$cssFiles = array();
    			try {
    				$javascripts = $kernel->locateResource('@'.$BundleName.'/Resources/public/js');
    				$hasJavascriptResource = true;
    			}
    			catch (\InvalidArgumentException $e) {} 
    			
    			try {
    				$css = $kernel->locateResource('@'.$BundleName.'/Resources/public/css');
    				$hasCssResource = true;
    				foreach (glob($css."/*.css") as $cssFile) {
    					$cssFiles[] = array("path"=>$cssFile,"filename"=>basename($cssFile));
    				}
    			}
    			catch (\InvalidArgumentException $e) {}
    			$assetName = $BundleName;
    			$assetName = strtolower($assetName);
    			$assetName = str_replace("bundle","",$assetName);
    			
    			$pluginName = $BundleName;
    			$pluginName = str_replace("Bundle","",$pluginName);
    			$pluginName = preg_replace("/^Plugin/","",$pluginName);

    			$BundleNs = $BundleName;
    			$BundleNs = preg_replace("/^Plugin/","Plugin.",$BundleNs);

    			$path = $kernel->locateResource('@'.$BundleName);
    			$Data = array("name"=>$BundleName,
    					"ns"=>$BundleNs,
    					"bundle"=>$BundleNamespace,
    					"path"=>$path,
    					"hasJavascripts"=>$hasJavascriptResource,
    					"hasCss"=>$hasCssResource,
    					"assetName"=>$assetName,
    					"pluginName"=>$pluginName,
    					"cssFiles"=>$cssFiles
    			);
    			$PluginBundles[$BundleName] = $Data;
    		//}
    	}

    	$params["PluginBundles"] = $PluginBundles;

        if(!$this->isTicketValid()) {
            return $this->redirect($this->generateUrl('ifresco_client_login'));
        }

        $versionCheckResult = $this->checkVersion();
        if ($versionCheckResult["needUpdate"]) {
            return $this->render('IfrescoClientBundle:Index:dataDictionaryUpdate.html.twig', array(
                'is_admin' => $this->get('security.context')->getToken()->isAdmin()
            ));
        }

        $defaultRootInfo = array(
            'text' => $this->get('translator')->trans("Repository"),
            'draggable' => false,
            'id' => 'root',
            'disabled' => true
        );

        //TODO: for what this? it should be in setting repository class
        // EXPLAIN: In the admin settings we can decide which foldder should be the root folder
        // And this foldder will be displayed then as root. 
        $treeRootFolder = Registry::getSetting('treeRootFolder', 'root');

        if($treeRootFolder != 'root' && trim($treeRootFolder) != '') {
            /**
             * @var User $user
             */
            $user = $this->get('security.context')->getToken();
            $session = $user->getSession();
            $spacesStore = new SpacesStore($session);

            $node = NodeCache::getInstance()->getNode($session, $spacesStore, $treeRootFolder);
            $rootInfo = array(
                'text' => $node->cm_name,
                'draggable' => false,
                'id' => $treeRootFolder,
                'disabled' => true
            );
        }
        else {
            $rootInfo = $defaultRootInfo;
        }

        $params['rootInfo'] = json_encode($rootInfo);

        $repoUrl = $this->container->getParameter('alfresco_repository_url');
        $repoUrl = preg_replace("/(https:\/\/.*?)\/.*/is", "$1", $repoUrl);
        $repoUrl = preg_replace("/(http:\/\/.*?)\/.*/is", "$1", $repoUrl);

        $params['ifrescoVersion'] = $this->container->getParameter("ifresco.client.version");
        $params['ShareUrl']= $repoUrl.'/share/page/document-details?nodeRef=workspace://SpacesStore/';
        $params['ShareFolder']= $repoUrl.'/share/page/repository?path=';
        $params['ifresco_settings'] = $this->getSettings();
        $params['zipArchiveExistsGen'] = class_exists('\ZipArchive');
		$params["Dropbox"] = Registry::getSetting('dropboxApiKey', '');
        return $this->render('IfrescoClientBundle::main.html.twig', $params);
    }
    
    public function getIfrescoSettingsAction() {
    	return new JsonResponse(array(
    			'settings' => $this->getSettings()
    	));
    }

    public function homeAction()
    {
        return $this->render('IfrescoClientBundle:Index:home.html.twig');
    }

    public function checkAuthAction()
    {
        $success = $this->isTicketValid();
        return new JsonResponse(array("success" => $success));
    }

    public function installScriptsToDictionaryAction()
    {
        return $this->render('IfrescoClientBundle:Index:index.html.twig');
    }

    public function getShareSettingsAction()
    {
        $repoUrl = $this->container->getParameter('alfresco_repository_url');
        $repoUrl = preg_replace("/(https:\/\/.*?)\/.*/is", "$1", $repoUrl);
        $repoUrl = preg_replace("/(http:\/\/.*?)\/.*/is", "$1", $repoUrl);

        return new JsonResponse(array(
            'success' => true,
            'ShareUrl' => $repoUrl.'/share/page/document-details?nodeRef=workspace://SpacesStore/',
            'ShareFolder' => $repoUrl.'/share/page/repository?path='
        ));
    }

    private function isTicketValid()
    {
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken();
        $repository = $user->getRepository();
        $session = $user->getSession();
        $spacesStore = new SpacesStore($session);

        $restContent = new RESTPerson($repository, $spacesStore, $session);

        return $restContent->isTicketValid();
    }

    private function checkVersion()
    {
        /**
         * @var User $user
         * @var Session $session
         */
        $user = $this->get('security.context')->getToken();
        $session = $user->getSession();
        $spacesStore = new SpacesStore($session);

        $response = array(
            "exists"=>false,
            "version"=>"",
            "needUpdate"=>true
        );

        $path = "/app:company_home/app:dictionary/cm:ifresco/cm:Client/cm:info.json";

        $findNode = $session->query($spacesStore, 'PATH:"' . $path . '"');
        if ($findNode != null) {
            /**
             * @var Node $file
             */
            $file = $findNode[0];
            $content = $file->getContent();
            $content = $content->getContent();
            $info = json_decode($content);
            $response["exists"] = true;
            $response["version"] = $info->version;
            if ($info->version != $this->container->getParameter("ifresco.client.version"))
                $response["needUpdate"] = true;
            else
                $response["needUpdate"] = false;
        }

        return $response;
    }

    private function getSettings()
    {
        /**
         * @var Router $router
         */
        $router = $this->container->get('router');
        $prefix = $this->get('kernel')->getEnvironment() != 'prod' ? "/app_dev.php" : '';
        $routes = array();
        foreach ($router->getRouteCollection()->all() as $name => $route) {
            $routes[$name] = $prefix . $route->getPath();
        }


        /**
         * @var Translator $translations
         */
        $translations = $this->get('translator');
        $trans = $translations->getAllMessages();

        $result = array(
            'routes' => $routes,
            'translations' => $trans
        );

        $isAdmin = $this->get('security.context')->getToken()->isAdmin();
        $result['isAdmin'] = $isAdmin;


        $settings = $this->getDoctrine()->getManager()->getRepository('IfrescoClientBundle:Setting')->findAll();
        $result['settings'] = array();
        if ($settings) {
            /**
             * @var Setting $setting
             */
            foreach ($settings as $setting) {
            	if (!$isAdmin && !preg_match("/password/eis",$setting->getKeyString()) || $isAdmin) {
            		$value = $setting->getValueString();
            		if ($setting->getKeyString() == "DisableTab")
            			$value = json_decode($value);
               		$result['settings'][$setting->getKeyString()] = $value;
            	}
            	else {
            		//die($setting->getKeyString());
            	}
            }
        }

        return json_encode($result);
    }
}