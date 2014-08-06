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
class sfAlfrescoWidgetTags //extends sfWidgetForm
{
    protected $urlfor;

  protected function configure($options = array(), $attributes = array())
  { 
    //parent::configure($options, $attributes);
  }
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $html = "";
    
    $optional = '';


    if (is_array($value)) {
        if (count($value) > 0)
            $value = json_encode($value);
        else
            $value = "[]";
            
    }
    else {
        $value = "[{}]";    
    }

    $label = "";
    /*$labelValue = $this->getLabel();
    if (!empty($labelValue)) {  
        $label = '<label for="'.$name.'Field">'.$labelValue.'</label>';
    }*/
    $url = $this->getUrlfor();
    
    $inputName = preg_replace("/(.*?)_num_.*/is","$1",$name)."#tags";                         
    $inputId = $name;                         
    
    $html .= <<<EOF
    <div type="tags" name="$name" style="display:none;"></div>
    
    <div id="tagBox$name">
        <ul id="tagBoxList$name"></ul>
    </div>
    
    <script type="text/javascript">
    $(document).ready(function() {
        $("#tagBoxList$name").tagit({
            url: '$url',
            tagsChanged: function (a, b) { if( typeof tagItCallback == 'function' ) { tagItCallback() }},
            submitname: '$inputName',
            submitid: '$inputId',
            values:$value
            
        });
    });
    </script>
EOF;
    //sfContext::getInstance()->getController()->genUrl($this->getOption('url')),
    
    return $html;
  }

  public function getJavascripts()
  {
    
  }

    public function setUrlfor($url) {
        $this->urlfor = $url;
    }

    public function getUrlfor() {
        return $this->urlfor;
    }

}