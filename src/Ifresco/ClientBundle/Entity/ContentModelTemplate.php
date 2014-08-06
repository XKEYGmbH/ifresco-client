<?php

namespace Ifresco\ClientBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ContentModelTemplate
 */
class ContentModelTemplate
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $class;

    /**
     * @var boolean
     */
    private $is_multicolumn;

    /**
     * @var string
     */
    private $aspect_view;

    /**
     * @var string
     */
    private $json_data;


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
     * Set class
     *
     * @param string $class
     * @return ContentModelTemplate
     */
    public function setClass($class)
    {
        $this->class = $class;
    
        return $this;
    }

    /**
     * Get class
     *
     * @return string 
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set is_multicolumn
     *
     * @param boolean $isMulticolumn
     * @return ContentModelTemplate
     */
    public function setIsMulticolumn($isMulticolumn)
    {
        $this->is_multicolumn = $isMulticolumn;
    
        return $this;
    }

    /**
     * Get is_multicolumn
     *
     * @return boolean 
     */
    public function getIsMulticolumn()
    {
        return $this->is_multicolumn;
    }

    /**
     * Set aspect_view
     *
     * @param string $aspectView
     * @return ContentModelTemplate
     */
    public function setAspectView($aspectView)
    {
        $this->aspect_view = $aspectView;
    
        return $this;
    }

    /**
     * Get aspect_view
     *
     * @return string 
     */
    public function getAspectView()
    {
        return $this->aspect_view;
    }

    /**
     * Set json_data
     *
     * @param string $jsonData
     * @return ContentModelTemplate
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
}
