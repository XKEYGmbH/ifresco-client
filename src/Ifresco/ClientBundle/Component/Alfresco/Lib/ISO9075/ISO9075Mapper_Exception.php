<?php
namespace Ifresco\ClientBundle\Component\Alfresco\Lib\ISO9075;
/**
 * Only XML_Query2XML_ISO9075Mapper will throw this exception.
 * It does not extend XML_Query2XML_Exception because the
 * class XML_Query2XML_ISO9075Mapper should be usable without
 * XML_Query2XML. XML_Query2XML itself will never throw this
 * exception.
 *
 * @category XML
 * @package  XML_Query2XML
 * @author   Lukas Feiler <lukas.feiler@lukasfeiler.com>
 * @license  http://www.gnu.org/copyleft/lesser.html  LGPL Version 2.1
 * @link     http://pear.php.net/package/XML_Query2XML
 */
class ISO9075Mapper_Exception extends \Exception
{
    /**
     * Constructor method
     *
     * @param string $message The error message.
     */
    public function __construct($message)
    {
        parent::__construct($message);
    }
}