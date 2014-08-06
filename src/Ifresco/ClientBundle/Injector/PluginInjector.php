<?php 
namespace Ifresco\ClientBundle\Injector;
use Ifresco\ClientBundle\Cache\CacheInterface;
use Ifresco\ClientBundle\Helper\PluginBoot;

class PluginInjector
{
	protected $factory;
	protected $cache;

	public function __construct($rootdir, CacheInterface $cache)
	{
		$this->rootDir = $rootdir;
		$this->cache = $cache;
	}

	public function inject(array &$bundles)
	{
		/*if ($this->cache->has('sylius_plugins.installed')) {
			$plugins = $this->cache->get('sylius_plugins.installed');

			foreach ($plugins as $plugin) {
				$bundles[] = $this->factory->create($plugin);
			}
		}*/
		
		$bundles = $this->readExtensions();
		
	}
	
	private function readExtensions() {
		$PluginBoot = new PluginBoot($this->rootDir);
		 
		// TODO CHECK IF BUNDLE WAS THERE IF NOT REFRESH VIA assets:install
		$Bundles = array();
		$ExtensionPath = $this->rootDir . '/../shared/Plugin';
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
			//print_R($ExtensionBundle);
			foreach ($ExtensionBundle as $MainFiles) {
				 
				if (is_array($MainFiles))
					continue;
				 
				if (preg_match("/^.+\Bundle.php$/i",$MainFiles)) {
					$BundlePathArray = $this->splitAtUpperCase($MainFiles);
					unset($BundlePathArray[count($BundlePathArray)-1]);
	
					$BundleNamespace = join($BundlePathArray, "\\");
					$RealBundleName = $BundleNamespace."Bundle";
					$BundleNamespace .= "Bundle\\".join($BundlePathArray, "")."Bundle";
	
	
	
					if (!$PluginBoot->inBootConfig($RealBundleName)) {
						$PluginBoot->writeInBootConfig($RealBundleName);
						/// INSTALL ASSETS HERE!
					}
					else {
						$enabled = $PluginBoot->getBundleBootConfig($RealBundleName);
						if ($enabled) {
							$Bundles[] = new $BundleNamespace();
						}
					}
				}
			}
		}
		 
		$PluginBoot->save();
		return $Bundles;
	}
	
	private function splitAtUpperCase($s) {
		return preg_split('/(?=[A-Z])/', $s, -1, PREG_SPLIT_NO_EMPTY);
	}
}

?>