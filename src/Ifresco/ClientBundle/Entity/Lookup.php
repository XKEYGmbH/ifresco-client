<?php

namespace Ifresco\ClientBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Lookup
 */
class Lookup
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $field;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $field_data;

    /**
     * @var boolean
     */
    private $is_single;

    /**
     * @var boolean
     */
    private $use_cache;

    /**
     * @var string
     */
    private $params;

    /**
     * @var boolean
     */
    private $apply_to;

    public function __construct()
    {
        $this->use_cache = false;
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
     * Set field
     *
     * @param string $field
     * @return Lookup
     */
    public function setField($field)
    {
        $this->field = $field;
    
        return $this;
    }

    /**
     * Get field
     *
     * @return string 
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Lookup
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
     * Set field_data
     *
     * @param string $fieldData
     * @return Lookup
     */
    public function setFieldData($fieldData)
    {
        $this->field_data = $fieldData;
    
        return $this;
    }

    /**
     * Get field_data
     *
     * @return string 
     */
    public function getFieldData()
    {
        return $this->field_data;
    }

    /**
     * Set is_single
     *
     * @param boolean $isSingle
     * @return Lookup
     */
    public function setIsSingle($isSingle)
    {
        $this->is_single = $isSingle;
    
        return $this;
    }

    /**
     * Get is_single
     *
     * @return boolean 
     */
    public function getIsSingle()
    {
        return $this->is_single;
    }

    /**
     * Set use_cache
     *
     * @param boolean $useCache
     * @return Lookup
     */
    public function setUseCache($useCache)
    {
        $this->use_cache = $useCache;
    
        return $this;
    }

    /**
     * Get use_cache
     *
     * @return boolean 
     */
    public function getUseCache()
    {
        return $this->use_cache;
    }

    /**
     * Set params
     *
     * @param string $params
     * @return Lookup
     */
    public function setParams($params)
    {
        $this->params = $params;
    
        return $this;
    }

    /**
     * Get params
     *
     * @return string 
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set apply_to
     *
     * @param boolean $applyTo
     * @return Lookup
     */
    public function setApplyTo($applyTo)
    {
        $this->apply_to = $applyTo;
    
        return $this;
    }

    /**
     * Get apply_to
     *
     * @return boolean 
     */
    public function getApplyTo()
    {
        return $this->apply_to;
    }
}
