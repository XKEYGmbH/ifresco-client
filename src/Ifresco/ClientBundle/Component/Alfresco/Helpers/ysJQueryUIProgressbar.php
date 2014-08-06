<?php
/*
 || UI.PROGRESSBAR
 */

/*
 * Initial functions
 * Starts adding the helper ysJQueryUICore core files jquery.ui
 * Call set_progressbar_configuration_files function ();
 */
if(!function_exists('ui_core_exist')){
  use_helper('ysJQueryUICore');
}
set_progressbar_configuration_files();

/**
 *
 * Add the context all the files needed to enable basic
 * ui.accordion and can be run the widget. View settings.yml
 * @param string $type default value: null; if $type = 'CSS' Add only stylesheets
 *        if $type = 'JS' Add only javascripts files .
 */
function set_progressbar_configuration_files($type = null){
    set_ui_common_configuration_files('progressbar', $type);
}

/**
 * Starts the progressbar and its configuration
 * @param string $id progressbar Id
 * @param array() $configurations Array. See the options and events in
 *        http://jqueryui.com/demos/progressbar/#options
 *        http://jqueryui.com/demos/progressbar/#events
 * @param string $html_attributes Additional options parameter (html attributes)
 */
function ui_progressbar_create($id , $configurations = array() , $html_attributes = null){
    $configurations = get_default_widget_configuration('app_ys_jquery_ui_progressbar_defaults', $configurations);
    $pattern = _ui_progressbar_pattern($configurations);
    init_ui_widget('progressbar',$pattern,$id ,$configurations);
    echo '<div id="' . $id . '" '. $html_attributes .'></div>';
}


/**
 * Remove the progressbar functionality completely.
 * This will return the element back to its pre-init state
 * @param string $selector jQuery Selector
 */
function ui_progressbar_destroy($selector){
  return add_method_support('progressbar',$selector, 'destroy');
}


/**
 * Enable the progressbar.
 * @param string $selector jQuery Selector
 */
function ui_progressbar_enable($selector){
  return add_method_support('progressbar',$selector, 'enable');
}

/**
 * Disable the progressbar.
 * @param string $selector jQuery Selector
 */
function ui_progressbar_disable($selector){
  return add_method_support('progressbar',$selector, 'disable');
}

/**
 * Shows the progress in the progressbar.
 * @param string $selector jQuery Selector
 * @param integer $delay Delay in seconds. Default 5 sec.
 */
function ui_progressbar_init_animation_now($selector, $delay = 5){
  $percentageChar = '%';
  $pattern =  _ui_progressbar_animation_pattern();
  return add_jquery_support($selector, 'ready', like_function(sprintf($pattern,$selector,$percentageChar,$delay)));
}

/**
 * Shows the progress in the progressbar. For use in an event.
 * @param string $selector jQuery Selector
 * @param integer $delay Delay in seconds. Default 5 sec.
 */
function ui_progressbar_init_animation($selector, $delay = 5){
  $percentageChar = '%';
  $pattern =  _ui_progressbar_animation_pattern();
  return jquery_support($selector, 'ready', like_function(sprintf($pattern,$selector,$percentageChar,$delay)));
}

/**
 * Shows the progress in the progressbar. For use in an event
 * @param string $selector jQuery Selector
 * @param integer $delay Delay in seconds. Default 5 sec
 */
function ui_progressbar_animate($selector, $delay = 5){
  $percentageChar = '%';
  $pattern =  _ui_progressbar_animation_pattern();
  return sprintf($pattern,$selector,$percentageChar,$delay);
}

/**
 * This method sets the current value of the progressbar
 * @param string $selector jQuery Selector
 * @param integer $value progressbar value
 */
function ui_progressbar_set_value($selector , $value){
  return add_method_support('progressbar',$selector, 'value' , $value);
}

/**
 * Get any progressbar option.
 * @param string $selector jQuery Selector
 * @param string $option The widget option to get
 */
function ui_progressbar_get_option($selector,$option){
  return jquery_support($selector,'progressbar', "'option' , '$option'");
}

/**
 * Set any progressbar option.
 * @param string $selector jQuery Selector
 * @param string $option_map Options array to set
 * @return <type>
 */
function ui_progressbar_set_options($selector,$option_map){
  $pattern = _ui_progressbar_pattern($option_map);
  return ui_set_widget_options('progressbar', $selector,$pattern);
}


/**
 * Internal function
 * @param array() $configuration Configuration array
 * @return string JSON Array.
 * @see ui_progressbar_create()
 */
function _ui_progressbar_pattern($configuration){
  $pattern = '';
  if(is_array($configuration) && sizeof($configuration) > 0){
    $pattern = '{';
    
    //OPTIONS SUPPORT
    if(isset($configuration['value'])){  $pattern .= toJQueryOption('value', $configuration['value']); }

    //EVENTS SUPPORT
    if(isset($configuration['change'])){ $pattern .= toJQueryOption('change', $configuration['change'] , true); }

    if($pattern != '{')
      $pattern = substr($pattern,0,(strlen($pattern)) - 1);
    $pattern .= '}';
  }
  return $pattern;
}

/**
 * Internal function
 */
function _ui_progressbar_animation_pattern(){
  $pattern =  "var i = 0;
              setInterval(function(){
                          widget = $('%s');
                          progressValue = widget.progressbar('option', 'value');
                          support = (i <= progressValue) ? widget.children().css('width' , i + '%s') : false;
                          i++;}
                          , %s);";
  return $pattern;
}


/*
 || END UI.PROGRESSBAR
 */
