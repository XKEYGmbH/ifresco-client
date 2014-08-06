<?php

namespace Ifresco\ClientBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\KernelInterface;
//use Ifresco\ClientBundle\Cache\FilesystemCache;
//use Ifresco\ClientBundle\Injector\PluginInjector;

class IfrescoClientBundle extends Bundle
{
	/*private $kernel;
	
	public function __construct(KernelInterface $kernel, array &$bundles)
	{
		$this->kernel = $kernel;
		$injector = new PluginInjector($kernel->getRootdir(), new FilesystemCache($kernel->getCacheDir()));
	
		$injector->inject($bundles);
	}*/
}
