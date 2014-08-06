<?php
/*
 || UI.SLIDER
 */

/*
 * Initial functions
 * Starts adding the helper ysJQueryUICore core files jquery.ui
 * Call set_slider_configuration_files function ();
 */
if(!function_exists('ui_core_exist')){
  use_helper('ysJQueryUICore');
}
set_slider_configuration_files();

/**
 *
 * Add the context all the files needed to enable basic
 * slider and can be run the widget. View settings.yml
 * @param string $type default value: null; if $type = 'CSS' Add only stylesheets
 *        if $type = 'JS' Add only javascripts files .
 */
function set_slider_configuration_files($type = null){
    set_ui_common_configuration_files('slider', $type);
}

/**
 * Starts the slider and its configuration
 * @param string $id accordion Id
 * @param array() $configurations Array. See the options and events in
 *        http://jqueryui.com/demos/slider/#options
 *        http://jqueryui.com/demos/slider/#events
 * @param string $html_attributes Additional options parameter (html attributes)
 */
function ui_slider_create($id , $configurations = array() , $html_attributes = null){
  $configurations = get_default_widget_configuration('app_ys_jquery_ui_slider_defaults', $configurations);
  $pattern = _ui_slider_pattern($configurations);
  init_ui_widget('slider',$pattern,$id ,$configurations);
  echo '<div id="' . $id . '" '. $html_attributes .'></div>';
}


/**
 * Remove the slider functionality completely.
 * This will return the element back to its pre-init state
 * @param string $selector jQuery Selector
 */
function ui_slider_destroy($selector){
  return add_method_support('slider',$selector, 'destroy');
}

/**
 * Build the alider and its configuration programmatically.
 * @param string $selector jQuery Selector
 * @param array() $configurations Array. See the options and events in
 *        http://jqueryui.com/demos/slider/#options
 *        http://jqueryui.com/demos/slider/#events
 */

function ui_slider_build($selector, $configurations = array()){
  $configurations = get_default_widget_configuration('app_ys_jquery_ui_slider_defaults', $configurations);
  $pattern = _ui_slider_pattern($configurations);
  return jquery_support($selector,'alider', $pattern);
}


/**
 * Enable the slider.
 * @param string $selector jQuery Selector
 */
function ui_slider_enable($selector){
  return add_method_support('slider',$selector, 'enable');
}

/**
 * Disable the slider
 * @param string $selector jQuery Selector
 */
function ui_slider_disable($selector){
  return add_method_support('slider',$selector, 'disable');
}

/**
 * Gets or sets the value of the slider. For single handle sliders
 * @param string $selector jQuery Selector
 * @param integer $value value of the slider.
 */
function ui_slider_value($selector , $value){
  return add_method_support('slider',$selector, 'value' , $value);
}

/**
 * Gets or sets the values of the slider. For multiple handle or range sliders
 * @param string $selector jQuery Selector
 * @param integer $value value of range sliders.
 */
function ui_slider_values($selector , $index ,$value){
  if(is_array($value)){
      $value = json_encode($value);
  }
  return add_method_support('slider',$selector, 'values' , $index . ',' . $value);
}

/**
 * Get any slider option.
 * @param string $selector jQuery Selector
 * @param string $option The widget option to get
 */
function ui_slider_get_option($selector,$option){
  return jquery_support($selector,'slider', "'option' , '$option'");
}

/**
 * Set any slider option.
 * @param string $selector jQuery Selector
 * @param string $option_map Options array to set
 * @return <type>
 */
function ui_slider_set_options($selector,$option_map){
  $pattern = _ui_accordion_pattern($option_map);
  return ui_set_widget_options('slider', $selector,$pattern);
}

/**
 * Internal function
 * @param array() $configuration Configuration array
 * @return string JSON Array.
 * @see ui_accordion_init()
 */
function _ui_slider_pattern($configuration){
  $pattern = '';
  if(is_array($configuration) && sizeof($configuration) > 0){
    $pattern = '{';

    //OPTIONS SUPPORT
    if(isset($configuration['animate'])){     $pattern .= toJQueryOption('animate', $configuration['animate']); }
    if(isset($configuration['max'])){         $pattern .= toJQueryOption('max', $configuration['max']); }
    if(isset($configuration['min'])){         $pattern .= toJQueryOption('min', $configuration['min']); }
    if(isset($configuration['orientation'])){ $pattern .= toJQueryOption('orientation', $configuration['orientation']); }
    if(isset($configuration['range'])){       $pattern .= toJQueryOption('range', $configuration['range']); }
    if(isset($configuration['step'])){        $pattern .= toJQueryOption('step', $configuration['step']); }
    if(isset($configuration['value'])){       $pattern .= toJQueryOption('value', $configuration['value']); }
    if(isset($configuration['values'])){      $pattern .= toJQueryOption('values', $configuration['values']); }

    //EVENTS SUPPORT
    if(isset($configuration['start'])){   $pattern .= toJQueryOption('start', $configuration['start'], true); }
    if(isset($configuration['slide'])){   $pattern .= toJQueryOption('slide', $configuration['slide'], true); }
    if(isset($configuration['change'])){  $pattern .= toJQueryOption('change', $configuration['change'], true); }
    if(isset($configuration['stop'])){    $pattern .= toJQueryOption('stop', $configuration['stop'], true); }

    if($pattern != '{')
      $pattern = substr($pattern,0,(strlen($pattern)) - 1);
    $pattern .= '}';
  }
  return $pattern;
}

/*
 || END UI.SLIDER
 */
