<?php

namespace Ifresco\ClientBundle\Component\Alfresco;

class AlfrescoUtil {
    public static function arrayify(&$maybeArray)
    {
        return is_array($maybeArray) ? $maybeArray : array($maybeArray);
    }
}