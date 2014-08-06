<?php
/*
 || UI.SELECTABLE
 */

/*
 * Initial functions
 * Starts adding the helper ysJQueryUICore core files jquery.ui
 * Call set_selectable_configuration_files function ();
 */
if(!function_exists('ui_core_exist')){
  use_helper('ysJQueryUICore');
}
set_selectable_configuration_files();

/**
 *
 * Add the context all the files needed to enable basic
 * ui.selectable and the effect can be run. View settings.yml
 * @param string $type default value: null; if $type = 'CSS' Add only stylesheets
 *        if $type = 'JS' Add only javascripts files .
 */
function set_selectable_configuration_files($type = null){
    set_ui_common_configuration_files('selectable', $type);
}

/**
 * Starts the accordion and its configuration
 * @param string $selector jQuery Selector
 * @param array() $configurations Array. See the options and events in
 *        http://jqueryui.com/demos/selectable/#options
 *        http://jqueryui.com/demos/selectable/#events
 */
function ui_selectable_support_to($selector , $configurations = array()){
    $configurations = get_default_widget_configuration('app_ys_jquery_ui_selectable_defaults', $configurations);
    $pattern = _ui_selectable_pattern($configurations);
    init_ui_sintax('selectable',$pattern,$selector ,$configurations);
}

/**
 * Starts the accordion and its configuration programmatically
 * @param string $id accordion Id
 * @param array() $configurations Array. See the options and events in
 *        http://jqueryui.com/demos/selectable/#options
 *        http://jqueryui.com/demos/selectable/#events
 */
function ui_selectable_build($selector , $configurations = array()){
    $configurations = get_default_widget_configuration('app_ys_jquery_ui_selectable_defaults', $configurations);
    $pattern = _ui_selectable_pattern($configurations);
    return jquery_support($selector,'selectable', $pattern);
}

/**
 * Remove the selectable functionality completely.
 * This will return the element back to its pre-init state.
 * @param string $selector jQuery Selector
 */
function ui_selectable_destroy($selector){
  return add_method_support('selectable',$selector, 'destroy');
}

/**
 * Enable the selectable
 * @param string $selector jQuery Selector
 */
function ui_selectable_enable($selector){
  return add_method_support('selectable',$selector, 'enable');
}

/**
 * Refresh the position and size of each selectee element.
 * This method can be used to manually recalculate the position and
 * size of each selectee element. Very useful if autoRefresh is set to false
 * @param string $selector jQuery Selector
 */
function ui_selectable_refresh($selector){
  return add_method_support('selectable',$selector, 'refresh');
}

/**
 * Disable the selectable.
 * @param string $selector jQuery Selector
 */
function ui_selectable_disable($selector){
  return add_method_support('selectable',$selector, 'disable');
}

/**
 * Get any selectable option.
 * @param string $selector jQuery Selector
 * @param string $option The widget option to get
 */
function ui_selectable_get_option($selector,$option){
  return jquery_support($selector,'selectable', "'option' , '$option'");
}

/**
 * Set any selectable option.
 * @param string $selector jQuery Selector
 * @param string $option_map Options array to set
 * @return <type>
 */
function ui_selectable_set_options($selector,$option_map){
  $pattern = _ui_selectable_pattern($option_map);
  return ui_set_widget_options('selectable', $selector,$pattern);
}

/**
 * Internal function
 * @param array() $configuration Configuration array
 * @return string JSON Array.
 * @see ui_selectable_support_to()
 */
function _ui_selectable_pattern($configuration){
  $pattern = '';
  if(is_array($configuration) && sizeof($configuration) > 0){
    $pattern = '{';

    //OPTIONS SUPPORT
    if(isset($configuration['autoRefresh'])){   $pattern .= toJQueryOption('autoRefresh', $configuration['autoRefresh']); }
    if(isset($configuration['cancel'])){        $pattern .= toJQueryOption('cancel', $configuration['cancel']); }
    if(isset($configuration['delay'])){         $pattern .= toJQueryOption('delay', $configuration['delay']); }
    if(isset($configuration['distance'])){      $pattern .= toJQueryOption('distance', $configuration['distance']); }
    if(isset($configuration['filter'])){        $pattern .= toJQueryOption('filter', $configuration['filter']); }
    if(isset($configuration['tolerance'])){     $pattern .= toJQueryOption('tolerance', $configuration['tolerance']); }

    //EVENTS SUPPORT
    if(isset($configuration['selected'])){      $pattern .= toJQueryOption('selected', $configuration['selected'], true); }
    if(isset($configuration['selecting'])){     $pattern .= toJQueryOption('selecting', $configuration['selecting'], true); }
    if(isset($configuration['start'])){         $pattern .= toJQueryOption('start', $configuration['start'], true); }
    if(isset($configuration['stop'])){          $pattern .= toJQueryOption('stop', $configuration['stop'], true); }
    if(isset($configuration['unselected'])){    $pattern .= toJQueryOption('unselected', $configuration['unselected'], true); }
    if(isset($configuration['unselecting'])){   $pattern .= toJQueryOption('unselecting', $configuration['unselecting'], true); }

    if($pattern != '{')
      $pattern = substr($pattern,0,(strlen($pattern)) - 1);
    $pattern .= '}';
  }
  return $pattern;
}

/*
 || END UI.SELECTABLE
 */