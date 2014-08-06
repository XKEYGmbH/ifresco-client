<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Config\ConfigCache;
use Ifresco\ClientBundle\Helper\PluginBoot;

/**
 * The SmartKernel class is able to register bundles
 * based on some conventions and some explicit lists
 * of include/exclude patterns
 **/
abstract class SmartKernel extends Kernel
{
    public function registerBundles()
    {

        // TODO handle cache
        $filename = sprintf('%s/%s/%s', __DIR__.'/cache/', $this->getEnvironment(), 'bundles.php');
        $cache = new ConfigCache($filename, true);

        if (!$cache->isFresh()) {
            $bundleNames = array();
            foreach ($this->getRegisterableBundles($this->getEnvironment()) as $bundle) {
                $bundleNames[] = sprintf('new %s', $bundle);
            }
            $content = sprintf('<?php $bundles = array(
                %s
            );', implode(", \n", $bundleNames));
            $cache->write($content);
        }
        require (string)$cache; // this defines a $bundles variable in this scope

        return $bundles;
    }

    protected function getRegisterableBundles($env)
    {
        $finder = new Finder;
        $finder
            ->files()
            ->name('*Bundle.php')
            ->exclude($this->getFinalExcludedBundles())
            ->in($this->getBundlesSearchDirs())
        ;

        $bundles = array();
        $bundles = array('Symfony\Bundle\FrameworkBundle\FrameworkBundle',
        		'Symfony\Bundle\SecurityBundle\SecurityBundle',
        		'Symfony\Bundle\TwigBundle\TwigBundle',
        		'Symfony\Bundle\MonologBundle\MonologBundle',
        		'Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle',
        		'Symfony\Bundle\AsseticBundle\AsseticBundle',
        		'Doctrine\Bundle\DoctrineBundle\DoctrineBundle',
        		'Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle',
        		'JMS\AopBundle\JMSAopBundle',
        		'JMS\DiExtraBundle\JMSDiExtraBundle($this)',
        		'JMS\SecurityExtraBundle\JMSSecurityExtraBundle',
        		'FOS\JsRoutingBundle\FOSJsRoutingBundle',
        		'Avalanche\Bundle\ImagineBundle\AvalancheImagineBundle',        		
        		'Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle');
        
        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = 'Symfony\Bundle\WebProfilerBundle\WebProfilerBundle';
            $bundles[] = 'Sensio\Bundle\DistributionBundle\SensioDistributionBundle';
            $bundles[] = 'Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle'; 
        }
        
        foreach ($finder as $file) {
            $bundle = $this->trim($file->getRealpath());
            $bundles[] = $bundle; 
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
    
    
    
    				if (!$PluginBoot->inBootConfig($RealBundleName)) {
    					$PluginBoot->writeInBootConfig($RealBundleName);
    					/// INSTALL ASSETS HERE!
    				}
    				else {
    					$enabled = $PluginBoot->getBundleBootConfig($RealBundleName);
    					if ($enabled) {
    						$Bundles[] = $BundleNamespace;
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

    protected function trim($path)
    {
        foreach ($this->getBundlesSearchDirs() as $dir) {
            $path = ltrim($path, realpath($dir));
        }
        $path = rtrim($path, '.php');
        $path = str_replace('/', '\\', $path);

        return $path;
    }

    protected function getExcludedBundles()
    {
        $bundles = array(
            'Symfony/Bundle/FrameworkBundle/Tests',
            'Symfony/Bundle/DoctrineBundle/Tests',
            'Symfony/Bundle/SecurityBundle/Tests',
            'Symfony/Component/HttpKernel/Bundle',
        );

        return $bundles;
    }

    protected function getFinalExcludedBundles()
    {
        $env = $this->getEnvironment();
        $bundles = $this->getExcludedBundlesByEnv();
        if( isset($bundles[$env])) {
            return $bundles[$env];
        }

        return $this->getExcludedBundles();
    }

    protected function getBundlesSearchDirs()
    {
        return array(
            __DIR__.'/../src',
           // __DIR__.'/../vendor/symfony/src',
           // __DIR__.'/../src/vendor/bundles',
        );
    }
}