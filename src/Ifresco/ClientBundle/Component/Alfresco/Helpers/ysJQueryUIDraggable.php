<?php
namespace Ifresco\ClientBundle\Component\Alfresco\Helpers;
/*
 || UI.DRAGGABLE
 */

/*
 * Initial functions
 * Starts adding the helper ysJQueryUICore core files jquery.ui
 * Call set_draggable_configuration_files function ();
 */
if(!function_exists('ui_core_exist')){
  use_helper('ysJQueryUICore');
}
set_draggable_configuration_files();

/**
 *
 * Add the context all the files needed to enable basic
 * ui.draggable and the effect can be run. View settings.yml
 * @param string $type default value: null; if $type = 'CSS' Add only stylesheets
 *        if $type = 'JS' Add only javascripts files .
 */
function set_draggable_configuration_files($type = null){
    set_ui_common_configuration_files('draggable', $type);
}

/**
 * Starts the droppable and its configuration
 * @param string $selector jQuery Selector
 * @param array() $configurations Array. See the options and events in
 *        http://jqueryui.com/demos/droppable/#options
 *        http://jqueryui.com/demos/droppable/#events
 * @param string $html_attributes Additional options parameter (html attributes)
 */
function ui_draggable_support_to($selector , $configurations = array() , $html_attributes = null){
    $configurations = get_default_widget_configuration('app_ys_jquery_ui_draggable_defaults', $configurations);
    $pattern = _ui_draggable_pattern($configurations);
    init_ui_sintax('draggable',$pattern,$selector ,$configurations);
}

/**
 * Build draggable and its configuration programmatically.
 * @param string $selector jQuery Selector
 * @param array() $configurations Array. See the options and events in
 *        http://jqueryui.com/demos/draggable/#options
 *        http://jqueryui.com/demos/draggable/#events
 */
function ui_draggable_build($selector, $configurations = array()){
  $configurations = get_default_widget_configuration('app_ys_jquery_ui_draggable_defaults', $configurations);
  $pattern = _ui_draggable_pattern($configurations);
  return jquery_support($selector,'draggable', $pattern);
}

/**
 * Remove the draggable functionality completely.
 * This will return the element back to its pre-init state
 * @param string $selector jQuery Selector
 */
function ui_draggable_destroy($selector){
  return add_method_support('draggable',$selector, 'destroy');
}

/**
 * Enable the draggable
 * @param string $selector jQuery Selector
 */
function ui_draggable_enable($selector){
  return add_method_support('draggable',$selector, 'enable');
}

/**
 * Disable the draggable
 * @param string $selector Selector del widget
 */
function ui_draggable_disable($selector){
  return add_method_support('draggable',$selector, 'disable');
}

/**
 * Get any draggable option.
 * @param string $selector jQuery Selector
 * @param string $option The widget option to get
 */
function ui_draggable_get_option($selector,$option){
  return jquery_support($selector,'draggable', "'option' , '$option'");
}

/**
 * Set any draggable option.
 * @param string $selector jQuery Selector
 * @param string $option_map Options array to set
 * @return <type>
 */
function ui_draggable_set_options($selector,$option_map){
  $pattern = _ui_draggable_pattern($option_map);
  return ui_set_widget_options('draggable', $selector,$pattern);
}

/**
 * Internal function
 * @param array() $configuration Configuration array
 * @return string JSON Array.
 * @see ui_draggable_support_to()
 */
function _ui_draggable_pattern($configuration){
  $pattern = '';
  if(is_array($configuration) && sizeof($configuration) > 0){
    $pattern = '{';

    //OPTIONS SUPPORT
    if(isset($configuration['addClasses'])){        $pattern .= toJQueryOption('addClasses', $configuration['addClasses']); }
    if(isset($configuration['appendTo'])){          $pattern .= toJQueryOption('appendTo', $configuration['appendTo']); }
    if(isset($configuration['appendToElement'])){   $pattern .= toJQueryOption('appendTo', $configuration['appendToElement'], true); }
    if(isset($configuration['axis'])){              $pattern .= toJQueryOption('axis', $configuration['axis']); }
    if(isset($configuration['cancel'])){            $pattern .= toJQueryOption('cancel', $configuration['cancel']); }
    if(isset($configuration['connectToSortable'])){ $pattern .= toJQueryOption('connectToSortable', $configuration['connectToSortable']);  }
    if(isset($configuration['containment'])){       $pattern .= toJQueryOption('containment', $configuration['containment']);  }
    if(isset($configuration['containmentElement'])){$pattern .= toJQueryOption('containment', $configuration['containmentElement'], true); }
    if(isset($configuration['cursor'])){            $pattern .= toJQueryOption('cursor', $configuration['cursor']); }
    if(isset($configuration['cursorAt'])){          $pattern .= toJQueryOption('cursorAt', $configuration['cursorAt']); }
    if(isset($configuration['delay'])){             $pattern .= toJQueryOption('delay', $configuration['delay']); }
    if(isset($configuration['distance'])){          $pattern .= toJQueryOption('distance', $configuration['distance']); }
    if(isset($configuration['grid'])){              $pattern .= toJQueryOption('grid', $configuration['grid']); }
    if(isset($configuration['handle'])){            $pattern .= toJQueryOption('handle', $configuration['handle']); }
    if(isset($configuration['handleElement'])){     $pattern .= toJQueryOption('handle', $configuration['handleElement'], true); }
    if(isset($configuration['helper'])){            $pattern .= toJQueryOption('helper', $configuration['helper']); }
    if(isset($configuration['helperFunction'])){    $pattern .= toJQueryOption('helper', $configuration['helperFunction'], true); }
    if(isset($configuration['iframeFix'])){         $pattern .= toJQueryOption('iframeFix', $configuration['iframeFix']); }
    if(isset($configuration['opacity'])){           $pattern .= toJQueryOption('opacity', $configuration['opacity']); }
    if(isset($configuration['refreshPositions'])){  $pattern .= toJQueryOption('refreshPositions', $configuration['refreshPositions']); }
    if(isset($configuration['revert'])){            $pattern .= toJQueryOption('revert', $configuration['revert']); }
    if(isset($configuration['revertDuration'])){    $pattern .= toJQueryOption('revertDuration', $configuration['revertDuration']); }
    if(isset($configuration['scope'])){             $pattern .= toJQueryOption('scope', $configuration['scope']); }
    if(isset($configuration['scroll'])){            $pattern .= toJQueryOption('scroll', $configuration['scroll']); }
    if(isset($configuration['scrollSensitivity'])){ $pattern .= toJQueryOption('scrollSensitivity', $configuration['scrollSensitivity']); }
    if(isset($configuration['scrollSpeed'])){       $pattern .= toJQueryOption('scrollSpeed', $configuration['scrollSpeed']); }
    if(isset($configuration['snap'])){              $pattern .= toJQueryOption('snap', $configuration['snap']); }
    if(isset($configuration['snapMode'])){          $pattern .= toJQueryOption('snapMode', $configuration['snapMode']); }
    if(isset($configuration['snapTolerance'])){     $pattern .= toJQueryOption('snapTolerance', $configuration['snapTolerance']); }
    if(isset($configuration['stack'])){             $pattern .= toJQueryOption('stack', $configuration['stack']); }
    if(isset($configuration['zIndex'])){            $pattern .= toJQueryOption('zIndex', $configuration['zIndex']); }

    //EVENTS SUPPORT
    if(isset($configuration['start'])){   $pattern .= toJQueryOption('start', $configuration['start'], true); }
    if(isset($configuration['drag'])){    $pattern .= toJQueryOption('drag', $configuration['drag'], true); }
    if(isset($configuration['stop'])){    $pattern .= toJQueryOption('stop', $configuration['stop'], true); }

    if($pattern != '{')
      $pattern = substr($pattern,0,(strlen($pattern)) - 1);
    $pattern .= '}';
  }
  return $pattern;
}
/*
 || END UI.DRAGGABLE
 */