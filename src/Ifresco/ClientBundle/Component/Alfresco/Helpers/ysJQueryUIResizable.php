<?php
/*
 || UI.RESIZABLE
 */

/*
 * Initial functions
 * Starts adding the helper ysJQueryUICore core files jquery.ui
 * Call set_resizable_configuration_files function ();
 */
if(!function_exists('ui_core_exist')){
  use_helper('ysJQueryUICore');
}
set_resizable_configuration_files();

/**
 *
 * Add the context all the files needed to enable basic
 * ui.accordion and the effect can be run. View settings.yml
 * @param string $type default value: null; if $type = 'CSS' Add only stylesheets
 *        if $type = 'JS' Add only javascripts files .
 */
function set_resizable_configuration_files($type = null){
    set_ui_common_configuration_files('resizable', $type);
}

/**
 * Starts the resizable support and its configuration
 * @param string $selector jQuery Selector
 * @param array() $configurations Array. See the options and events in
 *        http://jqueryui.com/demos/resizable/#options
 *        http://jqueryui.com/demos/resizable/#events
 */
function ui_resizable_support_to($selector , $configurations = array()){
    $configurations = get_default_widget_configuration('app_ys_jquery_ui_resizable_defaults', $configurations);
    $pattern = _ui_resizable_pattern($configurations);
    init_ui_sintax('resizable',$pattern,$selector ,$configurations);
}

/**
 * Starts the resizable support and its configuration programmatically
 * @param string $selector jQuery Selector
 * @param array() $configurations Array. See the options and events in
 *        http://jqueryui.com/demos/resizable/#options
 *        http://jqueryui.com/demos/resizable/#events
 */
function ui_resizable_build($selector , $configurations = array()){
    $configurations = get_default_widget_configuration('app_ys_jquery_ui_resizable_defaults', $configurations);
    $pattern = _ui_resizable_pattern($configurations);
    return jquery_support($selector,'resizable', $pattern);
}

/**
 * Remove the resizable functionality completely.
 * This will return the element back to its pre-init state
 * @param string $selector jQuery Selector
 */
function ui_resizable_destroy($selector){
  return add_method_support('resizable',$selector, 'destroy');
}

/**
 * Enable the resizable
 * @param string $selector jQuery Selector
 */
function ui_resizable_enable($selector){
  return add_method_support('resizable',$selector, 'enable');
}

/**
 * Disable the resizable
 * @param string $selector jQuery Selector
 */
function ui_resizable_disable($selector){
  return add_method_support('resizable',$selector, 'disable');
}

/**
 * Get any resizable option.
 * @param string $selector jQuery Selector
 * @param string $option The widget option to get
 */
function ui_resizable_get_option($selector,$option){
  return jquery_support($selector,'resizable', "'option' , '$option'");
}

/**
 * Set any resizable option.
 * @param string $selector jQuery Selector
 * @param string $option_map Options array to set
 * @return <type>
 */
function ui_resizable_set_options($selector,$option_map){
  $pattern = _ui_resizable_pattern($option_map);
  return ui_set_widget_options('resizable', $selector,$pattern);
}

/**
 * Internal function
 * @param array() $configuration Configuration array
 * @return string JSON Array.
 * @see ui_resizable_support_to()
 */
function _ui_resizable_pattern($configuration){
  $pattern = '';
  if(is_array($configuration) && sizeof($configuration) > 0){
    $pattern = '{';

    //OPTIONS SUPPORT
    if(isset($configuration['alsoResize'])){          $pattern .= toJQueryOption('alsoResize', $configuration['alsoResize']); } 
    if(isset($configuration['alsoResizeElement'])){   $pattern .= toJQueryOption('alsoResize', $configuration['alsoResizeElement'], true); }
    if(isset($configuration['animate'])){             $pattern .= toJQueryOption('animate', $configuration['animate']); }
    if(isset($configuration['animateDuration'])){     $pattern .= toJQueryOption('animateDuration', $configuration['animateDuration']); }
    if(isset($configuration['animateEasing'])){       $pattern .= toJQueryOption('animateEasing', $configuration['animateEasing']); }
    if(isset($configuration['aspectRatio'])){         $pattern .= toJQueryOption('aspectRatio', $configuration['aspectRatio']); }
    if(isset($configuration['autoHide'])){            $pattern .= toJQueryOption('autoHide', $configuration['autoHide']); }
    if(isset($configuration['cancel'])){              $pattern .= toJQueryOption('cancel', $configuration['cancel']); }
    if(isset($configuration['containment'])){         $pattern .= toJQueryOption('containment', $configuration['containment']); }
    if(isset($configuration['containmentElement'])){  $pattern .= toJQueryOption('containment', $configuration['containmentElement'], true); }
    if(isset($configuration['delay'])){               $pattern .= toJQueryOption('delay', $configuration['delay']); }
    if(isset($configuration['distance'])){            $pattern .= toJQueryOption('distance', $configuration['distance']); }
    if(isset($configuration['ghost'])){               $pattern .= toJQueryOption('ghost', $configuration['ghost']); }
    if(isset($configuration['grid'])){                $pattern .= toJQueryOption('grid', $configuration['grid']); }
    if(isset($configuration['handles'])){             $pattern .= toJQueryOption('handles', $configuration['handles']); }
    if(isset($configuration['helper'])){              $pattern .= toJQueryOption('helper', $configuration['helper']); }
    if(isset($configuration['maxHeight'])){           $pattern .= toJQueryOption('maxHeight', $configuration['maxHeight']); }
    if(isset($configuration['maxWidth'])){            $pattern .= toJQueryOption('maxWidth', $configuration['maxWidth']); }
    if(isset($configuration['minHeight'])){           $pattern .= toJQueryOption('minHeight', $configuration['minHeight']); }
    if(isset($configuration['minWidth'])){            $pattern .= toJQueryOption('minWidth', $configuration['minWidth']); }

    //EVENTS SUPPORT
    if(isset($configuration['start'])){   $pattern .= toJQueryOption('start', $configuration['start']); }
    if(isset($configuration['resize'])){  $pattern .= toJQueryOption('resize', $configuration['resize']); }
    if(isset($configuration['stop'])){    $pattern .= toJQueryOption('stop', $configuration['stop']); }

    if($pattern != '{')
      $pattern = substr($pattern,0,(strlen($pattern)) - 1);
    $pattern .= '}';
  }
  return $pattern;
}
/*
 || END UI.RESIZABLE
 */