<?php
namespace Ifresco\ClientBundle\Component\Alfresco\Lib\widget;
 /**
 * @package    AlfrescoClient
 * @author Dominik Danninger 
 *
 * ifresco Client
 * 
 * Copyright (c) 2013 X.KEY GmbH
 * 
 * This file is part of "ifresco Client".
 * 
 * "ifresco Client" is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * "ifresco Client" is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with "ifresco Client".  If not, see <http://www.gnu.org/licenses/>. (http://www.gnu.org/licenses/gpl.html)
 */
class sfWidgetFormSchemaFormatterAlfrescoLogin extends sfWidgetFormSchemaFormatter
{
  protected
    $rowFormat       = "<li class=\"%row_class%\">
                        %label% \n %field% %errorimg%
                        %error% \n %hidden_fields%\n</li>\n",

    $errorRowFormat  = "",
    $helpFormat      = '',
    $decoratorFormat = "%content%";
    
    
  public function formatRow($label, $field, $errors = array(), $help = '', $hiddenFields = null) {
    $row = parent::formatRow(
      $label,
      $field,
      $errors,
      $help,
      $hiddenFields
    );
 
    return strtr($row, array(
      '%errorimg%' => (count($errors) > 0) ? '<img src="/images/icons/cross.png" align="absmiddle">' : '',
      '%row_class%' => (count($errors) > 0) ? 'form_row_error' : '',
    ));
  }

}

?>