<?php

namespace Ifresco\ClientBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AlfrescoFavorite
 */
class AlfrescoFavorite
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $node_name;

    /**
     * @var string
     */
    private $node_id;

    /**
     * @var string
     */
    private $node_type;

    /**
     * @var string
     */
    private $user_key;


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
     * Set node_name
     *
     * @param string $nodeName
     * @return AlfrescoFavorite
     */
    public function setNodeName($nodeName)
    {
        $this->node_name = $nodeName;
    
        return $this;
    }

    /**
     * Get node_name
     *
     * @return string 
     */
    public function getNodeName()
    {
        return $this->node_name;
    }

    /**
     * Set node_id
     *
     * @param string $nodeId
     * @return AlfrescoFavorite
     */
    public function setNodeId($nodeId)
    {
        $this->node_id = $nodeId;
    
        return $this;
    }

    /**
     * Get node_id
     *
     * @return string 
     */
    public function getNodeId()
    {
        return $this->node_id;
    }

    /**
     * Set node_type
     *
     * @param string $nodeType
     * @return AlfrescoFavorite
     */
    public function setNodeType($nodeType)
    {
        $this->node_type = $nodeType;
    
        return $this;
    }

    /**
     * Get node_type
     *
     * @return string 
     */
    public function getNodeType()
    {
        return $this->node_type;
    }

    /**
     * Set user_key
     *
     * @param string $userKey
     * @return AlfrescoFavorite
     */
    public function setUserKey($userKey)
    {
        $this->user_key = $userKey;
    
        return $this;
    }

    /**
     * Get user_key
     *
     * @return string 
     */
    public function getUserKey()
    {
        return $this->user_key;
    }
}
