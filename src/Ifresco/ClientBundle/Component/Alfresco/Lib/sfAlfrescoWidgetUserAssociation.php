<?php
namespace Ifresco\ClientBundle\Component\Alfresco\Lib;

use Ifresco\ClientBundle\Component\Alfresco\Lib\sfWidgetForm;

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
class sfAlfrescoWidgetUserAssociation
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


		/*sfLoader::loadHelpers('Url');
		 sfLoader::loadHelpers(array('Url', 'Tag'));
		sfLoader::loadHelpers('ysJQueryRevolutions');
		sfLoader::loadHelpers('ysJQueryAutocomplete');
		sfLoader::loadHelpers('ysJQueryUIDialog');*/

		/*    try {
		 use_helper('Url');
		use_helper('ysJQueryRevolutions');
		use_helper('ysJQueryAutocomplete');
		use_helper('ysJQueryUIDialog');
		}
		catch (Exception $e) {

		}*/

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
		//url_for('Association/autocompleteUserData')
		$inputName = preg_replace("/(.*?)_num_.*/is","$1",$name)."#person";

		$html .= sprintf(<<<EOF
    <div type="userassoc" style="display:none;"></div>

<style type="text/css">
#userAssociationBox$name {
    font-family: Trebuchet MS,Arial,Helvetica,sans-serif;
}
#userAssociationInput$name {
    width:300px;
    /*width:275px;  */
    border:1px solid #ccd4db;
    /*background:url(/images/icons/search.png) right center no-repeat; */
    background:url(/images/layer/searchUserLayer.png) left no-repeat;
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
    height:40px;
    margin:0;
    padding:0;
    font-size:12px;
    font-family: Trebuchet MS,Arial,Helvetica,sans-serif;
    /*background:url(/images/layer/footerbg.png);*/
    
}
</style>
$label <div id="userAssociationBox$name">
    <input type="hidden" value="%s" name="%s" id="userAssociationValues$name">
    <input type="text" id="userAssociationInput$name" name="%sField" />
    <div id="ResultListBox$name">
        <ul id="ResultList$name">
        </ul>
    </div>
</div>

<script type="text/javascript">

function setResult$name(data, value) {
    var value = new String(value);
    var myValue = value.split("/");
    var userMail = myValue[0];
    var nodeId = myValue[1];
    var firstName = myValue[2];
    var lastName = myValue[3];
    var userName = myValue[4];

    var found = false;
    $('#ResultList$name li').each(function(index) {
        if ($(this).attr('id') == "$name"+nodeId) {
            found = true;
            return false;
        }
    });

    if (found == false) {

        jQuery("#ResultList$name").append("<li id=\"$name"+ nodeId +"\" class=\"ui-state-default\"><img width=\"16\" height=\"16\" src=\"/images/icons/user_suit.png\" align=\"absmiddle\"> <span class=\"firstName\">"+ firstName + "</span> <span class=\"lastName\">"+ lastName + "</span> (<span class=\"userName\">"+ userName + "</span>)<br><img width=\"16\" height=\"16\" src=\"/images/icons/email.png\" align=\"absmiddle\"> <span class=\"email\">"+ userMail + "</span><img width=\"16\" height=\"16\" src=\"/images/icons/delete.png\" align=\"absmiddle\" style=\"float:right;cursor:pointer;\" onclick=\"removeResult$name(\'"+ nodeId +"\')\"></li>");
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
    var nodeArray = new Array();
    $('#ResultList$name li').each(function(index) {
        var nodeId = $(this).attr('id');
        var firstName = $(this).children(".firstName").text();
        var lastName = $(this).children(".lastName").text();
        var userName = $(this).children(".userName").text();
        var userMail = $(this).children(".email").text();

        nodeId = nodeId.replace('$name','');
        //var nodeValArray = new Array(userMail,nodeId,firstName,lastName,userName);

        var nodeItem = {
            mail:userMail,
            id:nodeId,
            firstName:firstName,
            lastName:lastName,
            userName:userName
        };

        //nodeArray.push(nodeValArray);
        nodeArray.push(nodeItem);
    });
    $('#userAssociationValues$name').val($.toJSON(nodeArray));
}

function htmlEncode(value){
  return $('<div/>').text(value).html();
}

function htmlDecode(value){
  return $('<div/>').html(value).text();
}


function loadFromValues$name() {
    var inputVal = htmlDecode($('#userAssociationValues$name').val());

    if (inputVal != null && inputVal != "") {
        var nodeArray = $.evalJSON(inputVal);
        for (i = 0; i < nodeArray.length; i++) {
            if (nodeArray[i] != null && typeof(nodeArray[i] != 'undefined')) {
                var nodeId = nodeArray[i].id;
                var firstName = nodeArray[i].firstName;
                var lastName = nodeArray[i].lastName;
                var userName = nodeArray[i].userName;
                var userMail = nodeArray[i].mail;

                setResult$name(null,userMail+"/"+nodeId+"/"+firstName+"/"+lastName+"/"+userName);
            }
        }
    }
}

function removeResult$name(objId) {
    jQuery("#$name"+objId).remove();
    setValues$name();
}

$(document).ready(function() {
    jQuery("#userAssociationInput$name").autocomplete(
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
                return '<img width=\"16\" height=\"16\" src=\"/images/icons/user_suit.png\" align=\"absmiddle\"/> ' + value.split('/')[2] + ' '+value.split('/')[3]+' ('+value.split('/')[4]+')<br><img width=\"16\" height=\"16\" src=\"/images/icons/email.png\" align=\"absmiddle\"/> '+ value.split('/')[0];
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
        jQuery("#userAssociationInput$name").val('');
        setResult$name(data,value);
    }),
    loadFromValues$name();
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