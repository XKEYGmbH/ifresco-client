<?php
/*
 || UI.DROPPABLE
 */

/*
 * Initial functions
 * Starts adding the helper ysJQueryUICore core files jquery.ui
 * Call set_droppable_configuration_files function ();
 */
if(!function_exists('ui_core_exist')){
  use_helper('ysJQueryUICore');
}
set_droppable_configuration_files();

/**
 *
 * Add the context all the files needed to enable basic
 * ui.droppable and the effect can be run. View settings.yml
 * @param string $type default value: null; if $type = 'CSS' Add only stylesheets
 *        if $type = 'JS' Add only javascripts files .
 */
function set_droppable_configuration_files($type = null){
    set_ui_common_configuration_files('droppable', $type);
}

/**
 * Starts the droppable support
 * @param string $selector jQuery Selector
 * @param array() $configurations Array. See the options and events in
 *        http://jqueryui.com/demos/droppable/#options
 *        http://jqueryui.com/demos/droppable/#events
 */
function ui_droppable_support_to($selector , $configurations = array()){
    $configurations = get_default_widget_configuration('app_ys_jquery_ui_droppable_defaults', $configurations);
    $pattern = _ui_droppable_pattern($configurations);
    init_ui_sintax('droppable',$pattern,$selector ,$configurations);
}

/**
 * Build droppable and its configuration programmatically.
 * @param string $selector jQuery Selector
 * @param array() $configurations Array. See the options and events in
 *        http://jqueryui.com/demos/droppable/#options
 *        http://jqueryui.com/demos/droppable/#events
 */
function ui_droppable_build($selector, $configurations = array()){
  $configurations = get_default_widget_configuration('app_ys_jquery_ui_droppable_defaults', $configurations);
  $pattern = _ui_droppable_pattern($configurations);
  return jquery_support($selector,'droppable', $pattern);
}

/**
 * Remove the droppable functionality completely.
 * This will return the element back to its pre-init state
 * @param string $selector jQuery Selector
 */
function ui_droppable_destroy($selector){
  return add_method_support('droppable',$selector, 'destroy');
}


/**
 * Enable the droppable
 * @param string $selector jQuery Selector
 */
function ui_droppable_enable($selector){
  return add_method_support('droppable',$selector, 'enable');
}

/**
 * Disable the droppable
 * @param string $selector jQuery Selector
 */
function ui_droppable_disable($selector){
  return add_method_support('droppable',$selector, 'disable');
}

/**
 * Get any droppable option.
 * @param string $selector jQuery Selector
 * @param string $option The widget option to get
 */
function ui_droppable_get_option($selector,$option){
  return jquery_support($selector,'droppable', "'option' , '$option'");
}

/**
 * Set any droppable option.
 * @param string $selector jQuery Selector
 * @param string $option_map Options array to set
 * @return <type>
 */
function ui_droppable_set_options($selector,$option_map){
  $pattern = _ui_droppable_pattern($option_map);
  return ui_set_widget_options('droppable', $selector,$pattern);
}

/**
 * Internal function
 * @param array() $configuration Configuration array
 * @return string JSON Array.
 * @see ui_droppable_support_to()
 */
function _ui_droppable_pattern($configuration){
  $pattern = '';
  if(is_array($configuration) && sizeof($configuration) > 0){
    $pattern = '{';

    //OPTIONS SUPPORT
    if(isset($configuration['accept'])){          $pattern .= toJQueryOption('accept', $configuration['accept']); }
    if(isset($configuration['acceptFunction'])){  $pattern .= toJQueryOption('accept', $configuration['acceptFunction'], true); }
    if(isset($configuration['addClasses'])){      $pattern .= toJQueryOption('addClasses', $configuration['addClasses']); }
    if(isset($configuration['greedy'])){          $pattern .= toJQueryOption('greedy', $configuration['greedy']); }
    if(isset($configuration['hoverClass'])){      $pattern .= toJQueryOption('hoverClass', $configuration['hoverClass']); }
    if(isset($configuration['scope'])){           $pattern .= toJQueryOption('scope', $configuration['scope']); }
    if(isset($configuration['tolerance'])){       $pattern .= toJQueryOption('tolerance', $configuration['tolerance']); }

    //EVENTS SUPPORT
    if(isset($configuration['activate'])){        $pattern .= toJQueryOption('activate', $configuration['activate'], true); }
    if(isset($configuration['deactivate'])){      $pattern .= toJQueryOption('deactivate', $configuration['deactivate'], true); }
    if(isset($configuration['over'])){            $pattern .= toJQueryOption('over', $configuration['over'], true); }
    if(isset($configuration['out'])){             $pattern .= toJQueryOption('out', $configuration['out'], true); }
    if(isset($configuration['drop'])){            $pattern .= toJQueryOption('drop', $configuration['drop'], true); }

    if($pattern != '{')
      $pattern = substr($pattern,0,(strlen($pattern)) - 1);
    $pattern .= '}';
  }
  return $pattern;
}
/*
 || END UI.DROPPABLE
 */