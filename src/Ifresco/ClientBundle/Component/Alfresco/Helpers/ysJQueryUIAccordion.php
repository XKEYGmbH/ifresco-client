<?php
namespace Ifresco\ClientBundle\Component\Alfresco\Helpers;
/*
 || UI.ACCORDION
 */

/*
 * Initial functions
 * Starts adding the helper ysJQueryUICore core files jquery.ui
 * Call set_accordion_configuration_files function ();
 */
if(!function_exists('ui_core_exist')){
  use_helper('ysJQueryUICore');
}
set_accordion_configuration_files();

/**
 *
 * Add the context all the files needed to enable basic
 * ui.accordion and can be run the widget. View settings.yml
 * @param string $type default value: null; if $type = 'CSS' Add only stylesheets
 *        if $type = 'JS' Add only javascripts files .
 */
function set_accordion_configuration_files($type = null){
  set_ui_common_configuration_files('accordion', $type);
}

/**
 * Starts the accordion and its configuration
 * @param string $selector jQuery Selector
 * @param array() $configurations Array. See the options and events in
 *        http://jqueryui.com/demos/accordion/#options
 *        http://jqueryui.com/demos/accordion/#events
 * @param string $html_attributes Additional options parameter (html attributes)
 */
function ui_accordion_init($selector , $configurations = array() , $html_attributes = null){
  $configurations = get_default_widget_configuration('app_ys_jquery_ui_accordion_defaults', $configurations);
  $pattern = _ui_accordion_pattern($configurations);
  init_ui_widget('accordion',$pattern,$selector ,$configurations);
  echo '<div id="' . $selector . '" '. $html_attributes .'>';
}

/**
 * Starts the accordion section and its configuration
 * @param string $label Section label
 * @param string $html_attributes Additional options parameter (html attributes)
 * @param string $template The header element.
 *        default value <h3 %HTML_ATTRIBUTES%><a href="#">%LABEL%</a></h3>
 */
function ui_accordion_init_section($label, $html_attributes = '', $template = '<h3 %HTML_ATTRIBUTES%><a href="#">%LABEL%</a></h3>'){
   $accordion_header = str_replace('%LABEL%' , $label , $template);
   echo $accordion_header = str_replace('%HTML_ATTRIBUTES%' , $html_attributes , $accordion_header);
   echo '<div>';
}

/**
 * Ends accordion section
 */
function ui_accordion_end_section(){
   echo '</div>';
}

/**
 * Ends accordion
 */
function ui_accordion_end(){
   echo '</div>';
}

/**
 * Remove the accordion functionality completely.
 * This will return the element back to its pre-init state.
 * @param string $selector jQuery Selector
 */
function ui_accordion_destroy($selector){
  return jquery_support($selector,'accordion', '"destroy"');
}

/**
 * Build the accordion and its configuration programmatically.
 * @param string $selector jQuery Selector
 * @param array() $configurations Array. See the options and events in
 *        http://jqueryui.com/demos/accordion/#options
 *        http://jqueryui.com/demos/accordion/#events
 */

function ui_accordion_build($selector, $configurations = array()){
  $configurations = get_default_widget_configuration('app_ys_jquery_ui_accordion_defaults', $configurations);
  $pattern = _ui_accordion_pattern($configurations);
  return jquery_support($selector,'accordion', $pattern);
}


/**
 * Enable the accordion.
 * @param string $selector jQuery Selector
 */
function ui_accordion_enable($selector){
  return jquery_support($selector,'accordion', '"enable"');
}

/**
 * Disable the accordion.
 * @param string $selector jQuery Selector
 */
function ui_accordion_disable($selector){
  return jquery_support($selector,'accordion', '"disable"');
}

/**
 * Activate a content part of the Accordion programmatically.
 * @param string $selector jQuery Selector
 */
function ui_accordion_activate($selector , $index){
  return add_method_support('accordion' , $selector, 'activate', $index);
}

/**
 * Get any accordion option.
 * @param string $selector jQuery Selector
 * @param string $option The widget option to get
 */
function ui_accordion_get_option($selector,$option){
  return jquery_support($selector,'accordion', "'option' , '$option'");
}

/**
 * Set any accordion option.
 * @param string $selector jQuery Selector
 * @param string $option_map Options array to set
 * @return <type> 
 */
function ui_accordion_set_options($selector,$option_map){
  $pattern = _ui_accordion_pattern($option_map);
  return ui_set_widget_options('accordion', $selector,$pattern);
}


/**
 * Internal function
 * @param array() $configuration Configuration array
 * @return string JSON Array.
 * @see ui_accordion_init()
 */
function _ui_accordion_pattern($configuration){
  $pattern = '';
  if(is_array($configuration) && sizeof($configuration) > 0){
    $pattern = '{';
    
    //OPTIONS SUPPORT http://jqueryui.com/demos/accordion/#options
    if(isset($configuration['active'])){           $pattern .= toJQueryOption('active', $configuration['active']); }
    if(isset($configuration['alwaysOpen'])){       $pattern .= toJQueryOption('alwaysOpen', $configuration['alwaysOpen']); }
    if(isset($configuration['animated'])){         $pattern .= toJQueryOption('animated', $configuration['animated']); }
    if(isset($configuration['autoHeight'])){       $pattern .= toJQueryOption('autoHeight', $configuration['autoHeight']); }
    if(isset($configuration['clearStyle'])){       $pattern .= toJQueryOption('clearStyle', $configuration['clearStyle']); }
    if(isset($configuration['collapsible'])){      $pattern .= toJQueryOption('collapsible', $configuration['collapsible']); }
    if(isset($configuration['event'])){            $pattern .= toJQueryOption('event', $configuration['event']); }
    if(isset($configuration['fillSpace'])){        $pattern .= toJQueryOption('fillSpace', $configuration['fillSpace']); }
    if(isset($configuration['header'])){           $pattern .= toJQueryOption('header', $configuration['header']); }
    if(isset($configuration['icons'])){            $pattern .= toJQueryOption('icons', $configuration['icons']); }
    if(isset($configuration['navigation'])){       $pattern .= toJQueryOption('navigation', $configuration['navigation']);}
    if(isset($configuration['navigationFilter'])){ $pattern .= toJQueryOption('navigationFilter', $configuration['navigationFilter'], true); }
    if(isset($configuration['selectedClass'])){    $pattern .= toJQueryOption('selectedClass', $configuration['selectedClass']); }

    //EVENTS SUPPORT http://jqueryui.com/demos/accordion/#events
    if(isset($configuration['change'])){           $pattern .= toJQueryOption('change', $configuration['change'], true); }
    if(isset($configuration['changestart'])){      $pattern .= toJQueryOption('changestart', $configuration['changestart'], true); }

    if($pattern != '{')
      $pattern = substr($pattern,0,(strlen($pattern)) - 1);
    $pattern .= '}';
  }
  return $pattern;
}
/*
 || END UI.ACCORDION
 */