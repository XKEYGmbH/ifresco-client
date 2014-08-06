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
use Ifresco\ClientBundle\Helper\PluginBoot;
use Symfony\Component\Process\Process;

class PluginController extends Controller
{
    public function getPluginsAction(Request $request) {
    	$PluginBoot = new PluginBoot($this->get('kernel')->getRootDir());
    	
    	$Bundles = array();
    	$ExtensionPath = $this->get('kernel')->getRootDir() . '/../shared/Plugin';
    	$ritit = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($ExtensionPath), \RecursiveIteratorIterator::CHILD_FIRST);
    	$r = array();
    	foreach ($ritit as $splFileInfo) {
    		$path = $splFileInfo->isDir()
    		? array($splFileInfo->getFilename() => array())
    		: array($splFileInfo->getFilename());
    			
    		for ($depth = $ritit->getDepth() - 1; $depth >= 0; $depth--) {
    			$path = array($ritit->getSubIterator($depth)->current()->getFilename() => $path);
    		}
    		$r = array_merge_recursive($r, $path);
    	}
    	
    	foreach ($r as $ExtensionBundle) {
    		foreach ($ExtensionBundle as $MainFiles) {
    			if (is_array($MainFiles))
    				continue;
    			
    			if (preg_match("/^.+\Bundle.php$/i",$MainFiles)) {
    				
    				$BundlePathArray = $this->splitAtUpperCase($MainFiles);
    				unset($BundlePathArray[count($BundlePathArray)-1]);
    			
    				$BundleName = join($BundlePathArray, "")."Bundle";
    				$BundleOnlyName = preg_replace("/^Plugin(.*)/","$1",$BundleName);
    			
    			
    				$BundleNamespace = join($BundlePathArray, "\\");
    				$RealBundleName = $BundleNamespace."Bundle";
    				$enabled = false;
    				if ($PluginBoot->inBootConfig($RealBundleName))
    					$enabled = $PluginBoot->getBundleBootConfig($RealBundleName);
    				
    				$config = (object)array("description"=>"","version"=>"","author");
    				$resource = "/config.json";
    				if (file_exists($ExtensionPath."/".$BundleOnlyName.$resource))
    					$config = json_decode(file_get_contents($ExtensionPath."/".$BundleOnlyName.$resource));
    				
    				$Bundles[] = array(
    						"name"=>$BundleOnlyName,
    						"status"=>$enabled,
    						"version"=>(isset($config->version) ? $config->version : ''),
    						"description"=>(isset($config->description) ? $config->description : ''),
    						"author"=>(isset($config->author) ? $config->author : '')
    				);
    				
    			}
    		}
    	}
    	
    	$response = new JsonResponse(array("success"=>true,"plugins"=>$Bundles));
    	return $response;
    }
    
    public function savePuginStatusAction(Request $request) {
    	$status = $request->get('status');
    	$bundle = $request->get('plugin');
    	
    	if ($status == "true") {
    		$status = true;
    	}
    	else if ($status == "false") {
    		$status = false;
    	}
    	
    	$PluginBoot = new PluginBoot($this->get('kernel')->getRootDir(),$this->get('kernel')->getEnvironment());
    	$PluginBoot->writeInBootConfig("Plugin\\".$bundle,$status,true);
    	$PluginBoot->save();
    	
    	$PluginBoot->cleanUpRouting();
    	error_reporting(E_ALL);
    	ini_set("display_errors", "E_ALL");
    	$this->executeSymfonyCommand("cache:clear","--env=".$this->get('kernel')->getEnvironment());
    	
    	$resp = array("success"=>true);
		/* // THIS DOESNT WORK IN PROD ENV FIX IT WITH DIRTY HACK
    	$response = new JsonResponse($resp);
    	return $response;*/
    	die(json_encode($resp));
    }
    
    private function splitAtUpperCase($s) {
    	return preg_split('/(?=[A-Z])/', $s, -1, PREG_SPLIT_NO_EMPTY);
    }
    
    private function executeSymfonyCommand($command,$add="") {
    	$kernel = $this->get('kernel');
    	$application = new \Symfony\Bundle\FrameworkBundle\Console\Application($kernel);
    	$application->setAutoExit(false);
    	 
    	$options = array('command' => $command, $add);
    	$application->run(new \Symfony\Component\Console\Input\ArrayInput($options));
    }
}
