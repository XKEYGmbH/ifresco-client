<?php

require_once __DIR__.'/SmartKernel.php';

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Finder\Finder;
use Ifresco\ClientBundle\Helper\PluginBoot;
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new JMS\AopBundle\JMSAopBundle(),
            new JMS\DiExtraBundle\JMSDiExtraBundle($this),
            new JMS\SecurityExtraBundle\JMSSecurityExtraBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new Ifresco\ClientBundle\IfrescoClientBundle(),
        	new Avalanche\Bundle\ImagineBundle\AvalancheImagineBundle(),
        	new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
        	//new RedKiteLabs\RedKiteCms\BootstrapBundle\RedKiteLabsBootstrapBundle()
        	//new RedKiteLabs\BootstrapBundle\RedKiteLabsBootstrapBundle()
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle(); 
        }

		$ExtensionBundles = $this->readExtensions();
		$bundles = array_merge($bundles,$ExtensionBundles);

        return $bundles;
    }
    
    private function readExtensions() {
    	$PluginBoot = new PluginBoot(__DIR__);
    	
    	// TODO CHECK IF BUNDLE WAS THERE IF NOT REFRESH VIA assets:install
    	$Bundles = array();
    	$ExtensionPath = __DIR__ . '/../shared/Plugin';
    	$ritit = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($ExtensionPath), RecursiveIteratorIterator::CHILD_FIRST);
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
    				
    				if (empty($RealBundleName) || empty($BundleNamespace))
    					continue;
    				
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


    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/config_' . $this->getEnvironment() . '.yml');
    }
	
	/*protected function getExcludedBundles()
	{
		$bundles = parent::getExcludedBundles();
		$bundles[] = 'Symfony/Bundle/DoctrineBundle';
	
		return $bundles;
	}
	
	protected function getExcludedBundlesByEnv()
	{
		$excluded = $this->getExcludedBundles();
		$excluded[] = 'Symfony/Bundle/WebProfilerBundle';
		$bundles['prod'] = $excluded;
	
		return $bundles;
	}
	
	public function registerContainerConfiguration(LoaderInterface $loader)
	{
		$basename = __DIR__ . '/config/config_' . $this->getEnvironment();
	
		if (file_exists($basename . '_local.yml')) {
			$loader->load($basename . '_local.yml');
		} else {
			$loader->load($basename . '.yml');
		}
	}*/
}
