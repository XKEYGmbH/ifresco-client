<?php 

namespace Ifresco\ClientBundle\Helper;

class PluginBoot {
	public $bootConfig = array("plugins"=>array());
	private $kernelPath = "";
	private $env = "";
	private $isDirty = false;
	public function __construct($kernelPath,$env="prod") {
		$this->kernelPath = $kernelPath;
		$this->env = $env;
		$this->loadBootConfig();
	}
	
	public function getEnabledPlugins() {
		$Bundles = array();
		$ExtensionPath = $this->kernelPath . '/../shared/Plugin';
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
					if ($this->inBootConfig($RealBundleName))
						$enabled = $this->getBundleBootConfig($RealBundleName);
		
					if ($enabled) {
						$Bundles[$BundleName] = $BundleNamespace;
					}

				}
			}
		}
		
		return $Bundles;
	}
	
	public function cleanUpRouting() {
		$routingCachePath = $this->kernelPath . '/cache/'.$this->env.'/fosJsRouting';
		if (file_exists($routingCachePath)) {
			if (file_exists($routingCachePath."/data.json"))
				unlink($routingCachePath."/data.json");
			if (file_exists($routingCachePath."/data.json.meta"))
				unlink($routingCachePath."/data.json.meta");
		}
	}
	
	public function inBootConfig($bundle) {
		return property_exists($this->bootConfig->plugins,$bundle);
	}
	
	public function getBundleBootConfig($bundle) {
		return $this->bootConfig->plugins->{$bundle};
	}
	
	public function writeInBootConfig($bundle,$value=false,$forced=false) {
		$this->isDirty = true;
		if (!$forced) {
			if (!$this->inBootConfig($bundle))
				$this->bootConfig->plugins->{$bundle} = $value;
		}
		else
			$this->bootConfig->plugins->{$bundle} = $value;
	}

	public function save() {
		if ($this->isDirty)
			$this->saveBootConfig();
	}
	
	private function loadBootConfig() {
		 
		$bootFile = $this->kernelPath . '/../shared/boot.json';
		if (!file_exists($bootFile)) {
			$config = array("plugins"=>new \stdClass());
		}
		else
			$config = json_decode(file_get_contents($bootFile));
		 
		$this->bootConfig = (object)$config;
	}
	
	private function saveBootConfig() {
		if (!$this->isDirty)
			return;
		$bootFile = $this->kernelPath . '/../shared/boot.json';
		file_put_contents($bootFile,json_encode($this->bootConfig));
	}	
	
	private function splitAtUpperCase($s) {
		return preg_split('/(?=[A-Z])/', $s, -1, PREG_SPLIT_NO_EMPTY);
	}
	
	
}