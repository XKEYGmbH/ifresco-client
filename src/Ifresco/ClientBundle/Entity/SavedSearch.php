<?php

namespace Ifresco\ClientBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SavedSearch
 */
class SavedSearch
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var boolean
     */
    private $is_privacy;

    /**
     * @var string
     */
    private $user;

    /**
     * @var integer
     */
    private $template;

    /**
     * @var string
     */
    private $data;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return SavedSearch
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set is_privacy
     *
     * @param boolean $isPrivacy
     * @return SavedSearch
     */
    public function setIsPrivacy($isPrivacy)
    {
        $this->is_privacy = $isPrivacy;
    
        return $this;
    }

    /**
     * Get is_privacy
     *
     * @return boolean 
     */
    public function getIsPrivacy()
    {
        return $this->is_privacy;
    }

    /**
     * Set user
     *
     * @param string $user
     * @return SavedSearch
     */
    public function setUser($user)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return string 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set template
     *
     * @param integer $template
     * @return SavedSearch
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    
        return $this;
    }

    /**
     * Get template
     *
     * @return integer 
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set data
     *
     * @param string $data
     * @return SavedSearch
     */
    public function setData($data)
    {
        $this->data = $data;
    
        return $this;
    }

    /**
     * Get data
     *
     * @return string 
     */
    public function getData()
    {
        return $this->data;
    }
}
