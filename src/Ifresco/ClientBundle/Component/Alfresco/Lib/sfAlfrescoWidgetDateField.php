<?php
namespace Ifresco\ClientBundle\Component\Alfresco\Lib;

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
class sfAlfrescoWidgetDateField extends sfWidgetForm
{

  protected function configure($options = array(), $attributes = array())
  { 
    parent::configure($options, $attributes);
  }
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $html = "";

    $optional = '';

    use_helper('Url');

    if ($value == null)
        $value = "";
    else {
        if (is_array($value)) {
            $value = htmlspecialchars(json_encode($value));
        }
        $value = htmlspecialchars($value);
    }
    
    $label = "";

    $html .= sprintf(<<<EOF

<div id="$name"></div>


<script type="text/javascript"> 
Ext.onReady(function() {
    var myDP$name = Ext.create('Ext.form.DateField', {
        fieldLabel: '$label',
        name: '$name',
        width:190,
        allowBlank:true,
        renderTo:'$name'
    });
});
</script>
EOF
      ,
      "",
      "",
      $value,
      $name,
      $name,
      url_for('Association/autocompleteUserData')
    );
    //sfContext::getInstance()->getController()->genUrl($this->getOption('url')),
    
    return $html;
  }

  public function getJavascripts()
  {
    
  }
}