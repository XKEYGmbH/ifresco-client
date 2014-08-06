<?php
namespace Ifresco\ClientBundle\Component\Alfresco\Helpers;
/*
 || UI.MENU
 */


/*
 * Initial functions
 * Starts adding the helper ysJQueryUICore core files jquery.ui
 * Call set_ui_menu_configuration_files function ();
 */
if(!function_exists('ui_core_exist')){
  use_helper('ysJQueryUICore');
}
set_ui_menu_configuration_files();

/**
 *
 * Add the context all the files needed to enable basic
 * fg-menu and can be run the widget. View settings.yml
 * @param string $type default value: null; if $type = 'CSS' Add only stylesheets
 *        if $type = 'JS' Add only javascripts files .
 */
function set_ui_menu_configuration_files($type = null){
  $defaultPath['js'] = '/ysJQueryUIPlugin/js/jquery/fg-menu';
  $defaultPath['css'] = '/ysJQueryUIPlugin/css';
  set_ui_extra_widgets_configuration_files('ui_fg_menu', $defaultPath);
}

/**
 *
 * @param string $id menu Id
 * @param string $value teh Menu-button value
 * @param array $items The menu items /
 * @param string $configuration The fg-menu configuration, See the options in
 *        http://www.filamentgroup.com/lab/jquery_ipod_style_and_flyout_menus/
 */
function ui_menu_init($id, $value, $items = array(), $configurations = array()){
  $pattern = '';
  $buttoProperties = sprintf('tabindex="0" href="#%s"', $id);
  $defaultButtons = array('value' => $value ,'id' => $id , 'align' => 'right' , 'icon' => 'triangle-1-e','corner' => 'all', 'properties' => $buttoProperties);
  if(isset($items['disabled'])){
    if($items['disabled'] == true){
      $button = (isset($items['button'])) ? $items['button'] : array('button' => null);
      $buttonDisabled = array('state' => 'disabled');
      $items['button'] = (is_array($button)) ? array_merge($buttonDisabled, $button) : $buttonDisabled ;
    }
    unset($items['disabled']);
  }
  if(isset($items['button'])){
    $buttonConfiguration = $items['button'];
    $builder = (isset($buttonConfiguration['builder'])) ? $buttonConfiguration['builder']: 'ui_link_button';
    $pattern .= $builder(array_merge($defaultButtons, $items['button']));
    unset($items['button']);
    if(isset($buttonConfiguration['state']) && strtolower($buttonConfiguration['state']) == 'disabled'){
      return $pattern;
    }
  }else{
    $pattern .= ui_link_button($defaultButtons);
  }
  
  $items = $items = _get_items_from_yml($items, 'menu');

  $configurations['content'] = (isset($configurations['content']))  ? $configurations['content'] : sprintf("$('#%s').next().html()", $id) ;
  $configurations = get_default_widget_configuration('app_ys_jquery_ui_fg_menu_defaults', $configurations);
  
  $pattern .= sprintf('<div id="%sContent" class="hidden">',$id);
  $pattern .= array_to_html_list($items, 'fg_menu_list_formatter');
  $pattern .= '</div>';
  $menuPattern = _ui_fg_menu_pattern($configurations);
  $pattern .= add_jquery_support('#'.$id , 'menu', $menuPattern);
  return $pattern;
}

function ui_context_menu_init($id, $items){
  $listConfiguration = array('ul_properties' => ' id="'. $id. '" class="contextMenu ui-widget-content" style="z-index:2000"');
  $items = _get_items_from_yml($items, 'contextMenu');
  if(isset($items['support_to']) && is_array($items['support_to'])){
    foreach ($items['support_to'] as $jquerySelector => $function){
      echo ui_context_menu_support_to($id, $jquerySelector, $function);
    }
    unset($items['support_to']);
  }
  return $pattern = array_to_html_list($items, 'ui_context_menu_list_formatter', $listConfiguration);
}

function ui_context_menu_support_to($menuId, $jQuerySelector, $function = 'function(){return false;}'){
  $pattern = sprintf("$('%s').contextMenu({menu: '%s'},%s)", $jQuerySelector, $menuId , $function);
  return jquery_execute($pattern);
}

function ui_context_menu_disable_items($selector,$items,$isInternal = true){
  $items = sprintf("'%s'",$items);
  $response = ($isInternal) ? jquery_support($selector, 'disableContextMenuItems',$items) : jquery_execute(jquery_support($selector, 'disableContextMenuItems',$items));
  return $response;
}

function ui_context_menu_disable($selector,$isInternal = true){
  $response = ($isInternal) ? jquery_support($selector, 'disableContextMenu') : jquery_execute(jquery_support($selector, 'disableContextMenu'));
  return $response;
}

function ui_context_menu_enable_items($selector,$items,$isInternal = true){
  $items = sprintf("'%s'",$items);
  $response = ($isInternal) ? jquery_support($selector, 'enableContextMenuItems',$items) : jquery_execute(jquery_support($selector, 'disableContextMenuItems',$items));
  return $response;
}

function ui_context_menu_enable($selector,$isInternal = true){
  $response = ($isInternal) ? jquery_support($selector, 'enableContextMenu') : jquery_execute(jquery_support($selector, 'enableContextMenu'));
  return $response;
}

function ui_context_menu_list_formatter($item){
  if(isset($item['type']) && strtolower($item['type']) == 'separator'){
    return '<li class="separator"></li>';
  }
  $value = (isset($item['value'])) ? $item['value'] : 'undefined[value]' ;
  $id = (isset($item['id'])) ? $item['id'] : '' ;
  $href = (isset($item['url'])) ?  $item['url'] : '#' . $id ;
  $icon = (isset($item['icon'])) ? $item['icon'] : '' ;
  $url = (isset($item['icon'])) ? $item['icon'] : '' ;
  $pattern = '<a id="%s" href="%s" %s>%s %s</a>';
  $actions = _get_actions($item);
  $valuePattern = sprintf('<span style="float:left;padding:0 1px 1px 2px;">%s</span>', $value);
  $icon = ui_create_icon($icon, 'style="float:left;margin-left:2px;margin-top:2px;"');
  return sprintf($pattern,$id,$href,$actions,$icon,$valuePattern);
}


function _get_actions($item){
  $properties = '';
  $actionPattern = 'on%s="%s"';
  if(isset($item['url'])){
    $hrefAction =  "var newWindow = window.open($(this).attr('href'), $(this).attr('target'));";
    if(isset($item['actions'])){
      $actions = $item['actions'];
      if(isset($actions['click'])){
        $test = $actions['click'];
        $actions['click'] =  $test . ';' .$hrefAction;
      }
      $item['actions'] = $actions;
    }else{
      $item['actions'] = $hrefAction;
    }
  }
  $actions = '';
  if(isset($item['actions'])){
   if (is_array($item['actions'])){
      foreach($item['actions'] as $event => $function ){
        $actions .= sprintf($actionPattern,$event,$function);
      }
   }else{
       $actions .= sprintf($actionPattern,'click',$item['actions']);
   }
  }
  $properties .= (isset($item['properties'])) ? $item['properties'] . ' ' : $properties ;
  $properties .= (isset($item['target']))
             ? arrayToString(array('target' => $item['target'])) . ' ' . $properties
             : arrayToString(array('target' => '_self')). ' ' .$properties ;
  $properties .= $actions;
  return $properties;
}

function _get_items_from_yml($items, $ymlKey = 'undefined'){
  if(isset($items['yml'])){
    $ymlItems = sfYaml::load($items['yml']);
    $ymlIndex = (isset($items['ymlKey']) ? $items['ymlKey'] : $ymlKey );
    $items = (isset($ymlItems[$ymlIndex])) ? $ymlItems[$ymlIndex] : array('items' => array('value' => 'Error on yml index'));
  }
  return $items;
}

/**
 * Formatter for ui.fg-menu
 * @param array $items The array to Format.
 */
function fg_menu_list_formatter($item){
  $properties = '';
  if(isset($item['type']) && strtolower($item['type']) == 'separator'){
    return '<hr class="ui-state-default"/>';
  }
  if(isset($item['disabled']) && $item['disabled'] == true){
    $properties = ' class="ui-state-disabled fg-button fg-button-icon-left" ';
    unset($item['url'], $item['actions']);
  }
  $pattern = '<a id="%s" %s %s class="fg-button fg-button-icon-left"+>%s</a>';
  $value = (isset($item['value'])) ? $item['value'] : 'undefined[value]' ;
  if(isset($item['icon'])){
    $icon = ui_create_icon($item['icon']);
    $value .= $icon;
  }
  $id = (isset($item['id'])) ? $item['id'] : '' ;
  $href = (isset($item['url'])) ? sprintf('href="%s"',$item['url']) : 'href="#"' ;
  $properties .= _get_actions($item);
  return sprintf($pattern, $id, $href, $properties, $value);
}

/**
 * Internal function
 * @param array() $configuration Configuration array
 * @return string JSON Array.
 * @see ui_menu_init()
 */
function _ui_fg_menu_pattern($configuration){
  $pattern = '';
  if(is_array($configuration) && sizeof($configuration) > 0){
    $pattern = '{';
    //OPTIONS SUPPORT  http://www.filamentgroup.com/lab/jquery_ipod_style_and_flyout_menus/

    if(isset($configuration['content'])){             $pattern .= toJQueryOption('content', $configuration['content'], true); }
    if(isset($configuration['width'])){               $pattern .= toJQueryOption('width', $configuration['width']); }
    if(isset($configuration['autoOpen'])){            $pattern .= toJQueryOption('autoOpen', $configuration['autoOpen']); }
    if(isset($configuration['maxHeight'])){           $pattern .= toJQueryOption('maxHeight', $configuration['maxHeight']); }
    if(isset($configuration['positionOpts '])){       $pattern .= toJQueryOption('positionOpts ', $configuration['positionOpts ']); }
    if(isset($configuration['showSpeed'])){           $pattern .= toJQueryOption('showSpeed', $configuration['showSpeed']); }
    if(isset($configuration['callerOnState'])){       $pattern .= toJQueryOption('callerOnState', $configuration['callerOnState']); }
    if(isset($configuration['loadingState'])){        $pattern .= toJQueryOption('loadingState', $configuration['loadingState']); }
    if(isset($configuration['linkHover'])){           $pattern .= toJQueryOption('linkHover', $configuration['linkHover']); }
    if(isset($configuration['linkHoverSecondary'])){  $pattern .= toJQueryOption('linkHoverSecondary', $configuration['linkHoverSecondary']); }
    if(isset($configuration['crossSpeed'])){          $pattern .= toJQueryOption('crossSpeed', $configuration['crossSpeed']); }
    if(isset($configuration['crumbDefaultText'])){    $pattern .= toJQueryOption('crumbDefaultText', $configuration['crumbDefaultText']); }
    if(isset($configuration['backLink'])){            $pattern .= toJQueryOption('backLink', $configuration['backLink']);}
    if(isset($configuration['backLinkText'])){        $pattern .= toJQueryOption('backLinkText', $configuration['backLinkText']); }
    if(isset($configuration['flyOut'])){              $pattern .= toJQueryOption('flyOut', $configuration['flyOut']); }
    if(isset($configuration['flyOutOnState'])){       $pattern .= toJQueryOption('flyOutOnState', $configuration['flyOutOnState']); }
    if(isset($configuration['nextMenuLink'])){        $pattern .= toJQueryOption('nextMenuLink', $configuration['nextMenuLink']); }
    if(isset($configuration['topLinkText'])){         $pattern .= toJQueryOption('topLinkText', $configuration['topLinkText']); }
    if(isset($configuration['nextCrumbLink'])){       $pattern .= toJQueryOption('nextCrumbLink', $configuration['nextCrumbLink']); }

    if($pattern != '{')
      $pattern = substr($pattern,0,(strlen($pattern)) - 1);
    $pattern .= '}';
  }
  return $pattern;
}

/*
 || END UI.MENU
 */