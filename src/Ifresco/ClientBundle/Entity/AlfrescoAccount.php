<?php

namespace Ifresco\ClientBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AlfrescoAccount
 */
class AlfrescoAccount
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $user_token;

    /**
     * @var \DateTime
     */
    private $last_login;

    /**
     * @var \DateTime
     */
    private $created_at;

    /**
     * @var \DateTime
     */
    private $updated_at;


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
     * Set user_token
     *
     * @param string $userToken
     * @return AlfrescoAccount
     */
    public function setUserToken($userToken)
    {
        $this->user_token = $userToken;
    
        return $this;
    }

    /**
     * Get user_token
     *
     * @return string 
     */
    public function getUserToken()
    {
        return $this->user_token;
    }

    /**
     * Set last_login
     *
     * @param \DateTime $lastLogin
     * @return AlfrescoAccount
     */
    public function setLastLogin($lastLogin)
    {
        $this->last_login = $lastLogin;
    
        return $this;
    }

    /**
     * Get last_login
     *
     * @return \DateTime 
     */
    public function getLastLogin()
    {
        return $this->last_login;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return AlfrescoAccount
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;
    
        return $this;
    }

    /**
     * Get created_at
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set updated_at
     *
     * @param \DateTime $updatedAt
     * @return AlfrescoAccount
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;
    
        return $this;
    }

    /**
     * Get updated_at
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }
}
