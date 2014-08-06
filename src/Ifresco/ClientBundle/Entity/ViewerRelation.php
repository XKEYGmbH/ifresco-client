<?php

namespace Ifresco\ClientBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ViewerRelation
 */
class ViewerRelation
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $node_id;

    /**
     * @var string
     */
    private $viewer_node;

    /**
     * @var string
     */
    private $viewer_url;

    /**
     * @var string
     */
    private $viewer_content;

    /**
     * @var string
     */
    private $md5_sum;


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
     * Set node_id
     *
     * @param string $nodeId
     * @return ViewerRelation
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
     * Set viewer_node
     *
     * @param string $viewerNode
     * @return ViewerRelation
     */
    public function setViewerNode($viewerNode)
    {
        $this->viewer_node = $viewerNode;
    
        return $this;
    }

    /**
     * Get viewer_node
     *
     * @return string 
     */
    public function getViewerNode()
    {
        return $this->viewer_node;
    }

    /**
     * Set viewer_url
     *
     * @param string $viewerUrl
     * @return ViewerRelation
     */
    public function setViewerUrl($viewerUrl)
    {
        $this->viewer_url = $viewerUrl;
    
        return $this;
    }

    /**
     * Get viewer_url
     *
     * @return string 
     */
    public function getViewerUrl()
    {
        return $this->viewer_url;
    }

    /**
     * Set viewer_content
     *
     * @param string $viewerContent
     * @return ViewerRelation
     */
    public function setViewerContent($viewerContent)
    {
        $this->viewer_content = $viewerContent;
    
        return $this;
    }

    /**
     * Get viewer_content
     *
     * @return string 
     */
    public function getViewerContent()
    {
        return $this->viewer_content;
    }

    /**
     * Set md5_sum
     *
     * @param string $md5Sum
     * @return ViewerRelation
     */
    public function setMd5Sum($md5Sum)
    {
        $this->md5_sum = $md5Sum;
    
        return $this;
    }

    /**
     * Get md5_sum
     *
     * @return string 
     */
    public function getMd5Sum()
    {
        return $this->md5_sum;
    }
}
