<?php

namespace Ifresco\ClientBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * CurrentJob
 */
class CurrentJob
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $json_data;

    /**
     * @var \DateTime
     */
    private $created_at;

    public function __construct() {
        $this->created_at = new DateTime();
    }

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
     * Set type
     *
     * @param string $type
     * @return CurrentJob
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return CurrentJob
     */
    public function setStatus($status)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set json_data
     *
     * @param string $jsonData
     * @return CurrentJob
     */
    public function setJsonData($jsonData)
    {
        $this->json_data = $jsonData;
    
        return $this;
    }

    /**
     * Get json_data
     *
     * @return string 
     */
    public function getJsonData()
    {
        return $this->json_data;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return CurrentJob
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
}
