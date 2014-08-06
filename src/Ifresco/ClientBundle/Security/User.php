<?php
namespace Ifresco\ClientBundle\Security;

use Doctrine\ORM\EntityManager;
use Ifresco\ClientBundle\Entity\Setting;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Ifresco\ClientBundle\Component\Alfresco\Repository;
use Ifresco\ClientBundle\Component\Alfresco\NamespaceMap;
use Ifresco\ClientBundle\Component\Alfresco\SpacesStore;
use Ifresco\ClientBundle\Component\Alfresco\REST\RESTPerson;

//TODO: REFACTORING for all is needed

class User extends UsernamePasswordToken
{
    /**
     * @return Repository
     */
    public function getRepository()
    {
        $container = $GLOBALS['kernel']->getContainer();
        $repositoryUrl = $container->getParameter('alfresco_repository_url');
        $repository = new Repository($repositoryUrl);

        $this->loadNamespaceMap();

        return $repository;
    }

    /**
     * @return null|Repository
     */
    public function getPassword()
    {
        $repository = $this->getRepository();
        return $repository ? $repository->getPassword() : null;
    }

    public function getSession()
    {
        $session = $this->getRepository()->createSession($this->getTicket());
        $session->setLanguage($GLOBALS['kernel']->getContainer()->get('session')->get('_locale'));
        $this->loadNamespaceMap();

        return $session;
    }

    public function getUsername()
    {
        if ($this->hasAttribute('AlfrescoUsername')) return $this->getAttribute('AlfrescoUsername');

        return null;
    }

    public function getUserDetails()
    {
        $session = $this->getSession();
        $spacesStore = new SpacesStore($session);
        $restPerson = new RESTPerson($this->getRepository(), $spacesStore, $session);
        $person = $restPerson->GetPerson($this->getUsername());

        return $person;
    }

    public function getPersonRest()
    {
        $session = $this->getSession();
        $spacesStore = new SpacesStore($session);
        $RestPerson = new RESTPerson($this->getRepository(), $spacesStore, $session);

        return $RestPerson;
    }

    public function isAdmin()
    {
        $person = $this->getUserDetails();
        return $person->capabilities->isAdmin;
    }

    public function getTicket()
    {
        return ($this->hasAttribute('AlfrescoTicket')) ? $this->getAttribute('AlfrescoTicket') : null;
    }

    public function loadNamespaceMap()
    {
        $em = $GLOBALS['kernel']->getContainer()->get('doctrine')->getManager();

        $query = $em->createQueryBuilder()->select('n')->from('IfrescoClientBundle:NamespaceMapping', 'n');

        $namespaceMapping = $query->getQuery()->getResult();

        if (count($namespaceMapping) > 0) {
            $NamespaceMap = NamespaceMap::getInstance();
            foreach ($namespaceMapping as $namespace) {
                $nsp = $namespace->getNamespace();
                $prefix = $namespace->getPrefix();
                $NamespaceMap->addNamespaceMap($prefix, $nsp);
            }
        }
    }

    public function getDateFormat()
    {
        return $this->GetSetting("DateFormat", "m/d/Y");
    }

    public function getTimeFormat()
    {
        return $this->GetSetting("TimeFormat", "H:i");
    }

    public function GetSetting($settingKey, $default = "")
    {
        /**
         * @var EntityManager $em
         * @var Setting $setting
         */
        $em = $GLOBALS['kernel']->getContainer()->get('doctrine')->getManager();
        $query = $em->createQueryBuilder()
            ->select('s')
            ->from('IfrescoClientBundle:Setting', 's')
            ->where('s.key_string = :key_string')
            ->setParameter('key_string', $settingKey)
        ;

        $setting = $query->getQuery()->getOneOrNullResult();
        $setting = $em->getRepository('IfrescoClientBundle:Setting')->findOneBy(array(
            'key_string' => $settingKey
        ));
        return $setting ? $setting->getValueString() : $default;

        if ($setting != null) {
            $valueString = $setting->getValueString();

            return $valueString;
        }

        return $default;
    }
}