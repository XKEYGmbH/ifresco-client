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
class sfAlfrescoWidgetContentAssociation
{
  private $options = array();
  protected function configure()
  { 
    
  }
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $html = "";
    
    
    //set optional jQuery parameters
    $optional = '';

    if ($value == null)
        $value = "";
    else {
        if (is_array($value)) {
            $value = htmlspecialchars(json_encode($value));
            
        }
        $value = htmlspecialchars($value);
    } 
    
    $label = "";
    /*$labelValue = $this->getLabel();
    if (!empty($labelValue)) {  
        $label = '<label for="'.$name.'Field">'.$labelValue.'</label>';
    }*/

    $url = $this->getOption('urlfor');
    //url_for('Association/autocompleteContentData')  
    $inputName = preg_replace("/(.*?)_num_.*/is","$1",$name)."#content";                                  
    $html .= sprintf(<<<EOF
    <div type="contentassoc" style="display:none;"></div>
    
<script type="text/javascript" src="/js/jquery.json-2.2.min.js"></script>
<style type="text/css">
#contentAssociationBox$name {
    font-family: Trebuchet MS,Arial,Helvetica,sans-serif;
}
#contentAssociationInput$name {
    width:300px;  
    /*width:275px; */ 
    border:1px solid #ccd4db;     
    /*background:url(/images/icons/search.png) right center no-repeat; */
    background:url(/images/layer/searchDocumentLayer.png) left no-repeat;
    height:19px;
    padding-left:25px;
    font-weight: bold;
    color:#515d6b;
    font-family: Trebuchet MS,Arial,Helvetica,sans-serif;
              
}

#ResultListBox$name {
    margin-top:0px; 
    border:1px solid #ccd4db;      
    overflow:auto; 
    width:298px; 
    font-family: Trebuchet MS,Arial,Helvetica,sans-serif;
}

#ResultList$name {
    list-style: none;      
    margin:0;
    padding:0; 
           
    height:150px;   
    width:99%; 
    font-family: Trebuchet MS,Arial,Helvetica,sans-serif;
}

#ResultList$name li {
    list-style: none; 
    width:100%;
    display:block;
    height:20px;
    margin:0;
    padding:0;
    font-size:12px;
    font-family: Trebuchet MS,Arial,Helvetica,sans-serif;
    /*background:url(/images/layer/footerbg.png);*/
       
}
</style>
$label <div id="contentAssociationBox$name">
    <input type="hidden" value="%s" name="%s" id="contentAssociationValues$name">
    <input type="text" id="contentAssociationInput$name" name="%sField"  />
    <div id="ResultListBox$name">       
        <ul id="ResultList$name">       
        </ul>
    </div>
</div>

<script type="text/javascript">

function setResult$name(data, value) {
    var value = new String(value); 
    var myValue = value.split("/");
    var extension = myValue[0];
    var nodeId = myValue[1];
    var nodeName = myValue[2];
    var found = false;
    $('#ResultList$name li').each(function(index) {
        if ($(this).attr('id') == "$name"+nodeId) {   
            found = true;
            return false; 
        }
    });

    if (found == false) {
        jQuery("#ResultList$name").append("<li id=\"$name"+ nodeId +"\" class=\"ui-state-default\"><img class=\"extension\" width=\"16\" height=\"16\" src=\"/images/filetypes/16x16/" + extension + ".png\" align=\"absmiddle\"/><span class=\"name\"> "+ nodeName + "</span> <img width=\"16\" height=\"16\" src=\"/images/icons/delete.png\" align=\"absmiddle\" style=\"float:right;cursor:pointer;\" onclick=\"removeResult$name(\'"+ nodeId +"\')\"></li>");
        jQuery("#$name"+nodeId).mouseover(function(){
            $(this).removeClass().addClass("ui-state-active");
        }).mouseout(function(){
            $(this).removeClass().addClass("ui-state-default");        
        });
        setValues$name();
    }
    
    
    return false;    
}        

function setValues$name() {
    var nodeArray = [];
    $('#ResultList$name li').each(function(index) {
        var nodeId = $(this).attr('id');
        var nodeName = $(this).children(".name").text();
        var nodeImg = $(this).children(".extension").attr('src');
        nodeId = nodeId.replace('$name','');
        nodeImg = nodeImg.replace(/.*\/(.*?)\.png/,'$1');
        //var nodeValArray = new Array(nodeImg,nodeId,nodeName);
        var nodeItem = {
            type:nodeImg,
            id:nodeId,
            name:nodeName
        };
        
        //nodeArray.push(nodeValArray);
        nodeArray.push(nodeItem);
    });   
    $('#contentAssociationValues$name').val($.toJSON(nodeArray));     
}

function htmlEncode(value){ 
  return $('<div/>').text(value).html(); 
} 

function htmlDecode(value){ 
  return $('<div/>').html(value).text(); 
}


function loadFromValues$name() {
    var inputVal = htmlDecode($('#contentAssociationValues$name').val());
    
    /*if (inputVal != null && inputVal != "") {
        var nodeArray = $.evalJSON(inputVal);     
        for (i = 0; i < nodeArray.length; i++) {
            if (nodeArray[i].length > 0) {
                var undef = false;
                for (x = 0; x < nodeArray[i].length; x++) {
                    if (typeof(nodeArray[i][x]) == "undefined")
                        undef = true;    
                }
                if (undef == false) {
                    setResult$name(null,nodeArray[i][0]+"/"+nodeArray[i][1]+"/"+nodeArray[i][2]);
                }
            }    
        }
    }  */
    if (inputVal != null && inputVal != "") {
        var nodeArray = $.evalJSON(inputVal);  
        for (i = 0; i < nodeArray.length; i++) {
            if (nodeArray[i] != null && typeof(nodeArray[i] != 'undefined')) {
                
                var type = nodeArray[i].type;
                var id = nodeArray[i].id;         
                var name = nodeArray[i].name;

                setResult$name(null,type+"/"+id+"/"+name);
            }    
        }
    }    
}

function removeResult$name(objId) {
    jQuery("#$name"+objId).remove();      
}
    
$(document).ready(function() {
    jQuery("#contentAssociationInput$name").autocomplete(
        "%s", 
        {
            oddClass: 'ui-state-default',
            evenClass: 'ui-state-hover',
            overClass: 'ui-state-active',
            focus:false,
            width: 600,
            max: 20,
            highlight: false,
            scroll: true,
            delay: 250,
            multiple:true,
            scrollHeight: 300,
            formatItem: function(data, i, n, value) {
                return '<img width=\"32\" height=\"32\" src=\"/images/filetypes/32x32/' + value.split('/')[0] + '.png\" align=\"absmiddle\"/> ' + value.split('/')[2];
                /*return '<img width=\"32\" height=\"32\" src=\"/images/filetypes/32x32/' + value.split('/')[0] + '.png\" align=\"absmiddle\"/>' + value.split('/')[2] + '<img width=\"16\" height=\"16\" src=\"/images/icons/add.png\" align=\"absmiddle\" style=\"float:right;\" onclick="setResult$name(\''+data+'\',\''+value+'\')">';*/   
                
                
            },
            formatResult: function(data, value) {
                /*var nodeId=value.split("/")[1];
                var nodeName=value.split("/")[2];
                return nodeName;    */
                
                return "";
                //return false;
            }
        }
        
    ).result(function(data, value){
        jQuery("#contentAssociationInput$name").val('');
        setResult$name(data,value);    
    }),
    loadFromValues$name()
});
</script>
EOF
      ,
      "",
      "",
      $value,
      $inputName,
      $name,
      $url
    );
    //sfContext::getInstance()->getController()->genUrl($this->getOption('url')),
    
    return $html;
  }

  public function getJavascripts()
  {
    
  }

  public function getOption($opt)
  {
      return isset($this->options[$opt])?$this->options[$opt]:false;
  }

  public function addOption($opt, $val)
  {
      $this->options[$opt] = $val;
  }
}