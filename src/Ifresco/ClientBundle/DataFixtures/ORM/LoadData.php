<?php 
namespace Ifresco\ClientBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ifresco\ClientBundle\Entity\Setting;

class LoadData implements FixtureInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function load(ObjectManager $manager)
	{
		$Settings = file_get_contents(__DIR__."/../Data/Settings.json");
		if (!empty($Settings)) {
			$Settings = json_decode($Settings);
			foreach ($Settings as $DefaultSetting) {
				$settingKey = $DefaultSetting->key_string;
				$settingVal = $DefaultSetting->value_string;
				
				$Setting = $manager->getRepository('IfrescoClientBundle:Setting')->findOneBy(array(
						'key_string' => $settingKey
				));
			
				if ($Setting == null) {
					$Setting = new Setting();
					
					$Setting->setKeyString($settingKey);
					$Setting->setValueString($settingVal);
					$manager->persist($Setting);
				}
			}
			$manager->flush();
		}
	}
}

?>