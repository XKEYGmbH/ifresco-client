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
class sfWidgetFormSchemaFormatterAlfrescoMetadataExtJS extends sfWidgetFormSchemaFormatter
{
  protected
    $rowFormat       = "%start%
    %labelname%
    %name%
    %end%",

    $errorRowFormat  = "%errors%",
    $helpFormat      = '%help%',
    $decoratorFormat = "%content%";
    
  private $count = 0;
    
  public function formatRow($label, $field, $errors = array(), $help = '', $hiddenFields = null) {

    $row = parent::formatRow(
      $label,
      $field,
      $errors,
      $help,
      $hiddenFields
    );
    
    $name = "";
    $end = "";
    $start = "";
    if ($this->count != 0)
        $start = ",";
    
    $labelname = strip_tags($label);
    $labelfield = "fieldLabel: '".$labelname."',";
    
    if (preg_match("/name=\"(.*?)\"/eis",$field,$fieldMatch)) {
        $name = $fieldMatch[1];
        $name = str_replace("[","",$name);
        $name = str_replace("]","",$name);
    }
    
    $namefield = "name: '$name'";
    
    if (preg_match("/type=\"(.*?)\"/eis",$field,$fieldMatch)) {
        $type = $fieldMatch[1];
        switch ($type) {
            case "tags":
                $start .= "{el: 'tagBoxList$name',";
                $end .= "}";
            break;case "userassoc":
                $start .= "{el: 'userAssociationBox$name',";
                $end .= "}";
            break;
            case "contentassoc":
                $start .= "{el: 'contentAssociationBox$name',";
                $end .= "}";
            break;
            case "list":
            if (preg_match("/<div id=\"store\" count=\"(.*?)\">(.*?)<\/div>/eis",$field,$storeMatch)) {
                $storeCount = $storeMatch[1];
                $height = $storeCount*25;
                $storeValues = $storeMatch[2];
                    $start .= "{xtype: 'multiselect',
                fieldLabel: '$labelname',
                name: '$name',
                height: 'auto',
                allowBlank:false,
                store: [$storeValues],
                tbar:[{
                    text: 'clear',
                    handler: function(){
                        metaForm.getForm().findField('$name').reset();
                    }
                }],
                ddReorder: false";
                    $end = "}";
                    $name = "";

                    $labelfield = "";
                    $namefield = "";
            }
            break;
            case "long":
            case "double":
            case "float":
            case "int":
                $start .= "{xtype:'numberfield',";
                $end .= "}";
            break;
            case "date":
                $start .= "Ext.create('Ext.form.DateField', {fieldLabel: '$labelname',name: '$name'})";
                $end = "";
                $name = "";

                $labelfield = "";
                $namefield = "";
            break;
            case "checkbox":
                $start .= "{xtype: 'checkbox',";
                $end .= "}";
            break;
            default:

                $start .= "{";
                $end .= "}";
            break;
        }
        
        
    }
    else {
        $start .= "{";
        $end .= "}";
    }

    $this->count++;
    
    return strtr($row, array(
      '%start%'=>$start,
      '%end%'=>$end,
      '%name%'=>$namefield,
      '%labelname%'=>$labelfield,
    ));
  }

}

?>