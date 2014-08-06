<?php

namespace Ifresco\ClientBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SearchTemplate
 */
class SearchTemplate
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
    private $is_default_view;

    /**
     * @var boolean
     */
    private $is_multicolumn;

    /**
     * @var boolean
     */
    private $is_full_text_child;

    /**
     * @var boolean
     */
    private $is_full_text_child_overwrite;

    /**
     * @var integer
     */
    private $column_set_id;

    /**
     * @var integer
     */
    private $saved_search_id;

    /**
     * @var string
     */
    private $show_doctype;

    /**
     * @var string
     */
    private $json_data;

    public function __construct() {
            $this->is_full_text_child = false;
            $this->is_full_text_child_overwrite = false;
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
     * Set name
     *
     * @param string $name
     * @return SearchTemplate
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
     * Set is_default_view
     *
     * @param boolean $isDefaultView
     * @return SearchTemplate
     */
    public function setIsDefaultView($isDefaultView)
    {
        $this->is_default_view = $isDefaultView;
    
        return $this;
    }

    /**
     * Get is_default_view
     *
     * @return boolean 
     */
    public function getIsDefaultView()
    {
        return $this->is_default_view;
    }

    /**
     * Set is_multicolumn
     *
     * @param boolean $isMulticolumn
     * @return SearchTemplate
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
     * Set is_full_text_child
     *
     * @param boolean $isFullTextChild
     * @return SearchTemplate
     */
    public function setIsFullTextChild($isFullTextChild)
    {
        $this->is_full_text_child = $isFullTextChild;
    
        return $this;
    }

    /**
     * Get is_full_text_child
     *
     * @return boolean 
     */
    public function getIsFullTextChild()
    {
        return $this->is_full_text_child;
    }

    /**
     * Set is_full_text_child_overwrite
     *
     * @param boolean $isFullTextChildOverwrite
     * @return SearchTemplate
     */
    public function setIsFullTextChildOverwrite($isFullTextChildOverwrite)
    {
        $this->is_full_text_child_overwrite = $isFullTextChildOverwrite;
    
        return $this;
    }

    /**
     * Get is_full_text_child_overwrite
     *
     * @return boolean 
     */
    public function getIsFullTextChildOverwrite()
    {
        return $this->is_full_text_child_overwrite;
    }

    /**
     * Set column_set_id
     *
     * @param integer $columnSetId
     * @return SearchTemplate
     */
    public function setColumnSetId($columnSetId)
    {
        $this->column_set_id = $columnSetId;
    
        return $this;
    }

    /**
     * Get column_set_id
     *
     * @return integer 
     */
    public function getColumnSetId()
    {
        return $this->column_set_id;
    }

    /**
     * Set saved_search_id
     *
     * @param integer $savedSearchId
     * @return SearchTemplate
     */
    public function setSavedSearchId($savedSearchId)
    {
        $this->saved_search_id = $savedSearchId;
    
        return $this;
    }

    /**
     * Get saved_search_id
     *
     * @return integer 
     */
    public function getSavedSearchId()
    {
        return $this->saved_search_id;
    }

    /**
     * Set show_doctype
     *
     * @param string $showDoctype
     * @return SearchTemplate
     */
    public function setShowDoctype($showDoctype)
    {
        $this->show_doctype = $showDoctype;
    
        return $this;
    }

    /**
     * Get show_doctype
     *
     * @return string 
     */
    public function getShowDoctype()
    {
        return $this->show_doctype;
    }

    /**
     * Set json_data
     *
     * @param string $jsonData
     * @return SearchTemplate
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
     * @var string
     */
    private $content_type;


    /**
     * Set content_type
     *
     * @param string $contentType
     * @return SearchTemplate
     */
    public function setContentType($contentType)
    {
        $this->content_type = $contentType;

        return $this;
    }

    /**
     * Get content_type
     *
     * @return string 
     */
    public function getContentType()
    {
        return $this->content_type;
    }
}
