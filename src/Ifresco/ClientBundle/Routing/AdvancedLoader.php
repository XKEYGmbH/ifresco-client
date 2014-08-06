<?php 
namespace Ifresco\ClientBundle\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;
use Ifresco\ClientBundle\Helper\PluginBoot;

class AdvancedLoader extends Loader
{
	private $kernelRootPath = null;
	public function __construct($root) {
		$this->kernelRootPath = $root;
	}
	
	public function load($resource, $type = null)
	{
		$collection = new RouteCollection();
		$PluginBoot = new PluginBoot($this->kernelRootPath);
		
		try {
			$Bundles = array();
			$ExtensionPath = $this->kernelRootPath . '/../shared/Plugin';
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
						if (!$PluginBoot->inBootConfig($RealBundleName))
							continue;
							
						$enabled = $PluginBoot->getBundleBootConfig($RealBundleName);
						if (!$enabled)
							continue;
						
						$resource = "/Resources/config/routing.yml";
						if (!file_exists($ExtensionPath."/".$BundleOnlyName.$resource))
							continue;

						$importedRoutes = $this->import("@".$BundleName.$resource, 'yaml');
						$collection->addCollection($importedRoutes);
					}
				}
			}
		}catch(\Exception $e) {
			echo $e->getMessage();
		}

		return $collection;
	}

	public function supports($resource, $type = null)
	{
		return $type === 'custom';
	}

	private function splitAtUpperCase($s) {
		return preg_split('/(?=[A-Z])/', $s, -1, PREG_SPLIT_NO_EMPTY);
	}
}

?>