<?php
namespace Ifresco\ClientBundle\Component\Alfresco\Helpers;
/*
 || UI.DIALOG
 */

/*
 * Initial functions
 * Starts adding the helper ysJQueryUICore core files jquery.ui
 * Call set_dialog_configuration_files function ();
 */
if(!function_exists('ui_core_exist')){
  use_helper('ysJQueryUICore');
}
set_dialog_configuration_files();

/**
 *
 * Add the context all the files needed to enable basic
 * ui.dialog and can be run the widget. View settings.yml
 * @param string $type default value: null; if $type = 'CSS' Add only stylesheets
 *        if $type = 'JS' Add only javascripts files .
 */
function set_dialog_configuration_files($type = null){
  set_ui_common_configuration_files('dialog', $type);
}

/**
 * Starts the dialog and its configuration
 * @param string $id dialog Id
 * @param array() $configurations Array. See the options and events in
 *        http://jqueryui.com/demos/dialog/#options
 *        http://jqueryui.com/demos/dialog/#events
 * @param string $html_attributes Additional options parameter (html attributes)
 */
function ui_dialog_init($id , $configurations = array() , $html_attributes = null){
    $configurations = get_default_widget_configuration('app_ys_jquery_ui_dialog_defaults', $configurations);
    $pattern = _ui_dialog_pattern($configurations);
    init_ui_widget('dialog',$pattern,$id ,$configurations);
    if(isset($configurations['listener'])){
      $listener = $configurations['listener'];
      if((isset($listener['event']) || isset($listener['oneEvent'])) && isset($listener['selector']) ){
        $dialogId = '#' . $id;
        $event = (isset($listener['oneEvent'])) ? $listener['oneEvent'] : $listener['event'];
        echo jquery_execute(ui_dialog_open($dialogId),$listener['selector'], $event);
      }
    }
    echo '<div id="' . $id . '" '. $html_attributes .'>';
}

/**
 * Ends dialog
 */
function ui_dialog_end(){
   echo '</div>';
}

/**
 * Remove the dialog functionality completely.
 * This will return the element back to its pre-init state
 * @param string $selector jQuery Selector
 */
function ui_dialog_destroy($selector){
  return add_method_support('dialog',$selector, 'destroy');
}

/**
 * Build the dialog and its configuration programmatically
 * @param string $selector jQuery Selector
 * @param array() $configurations Array. See the options and events in
 *        http://jqueryui.com/demos/dialog/#options
 *        http://jqueryui.com/demos/dialog/#events
 */

function ui_dialog_build($selector, $configurations = array()){
  $configurations = get_default_widget_configuration('app_ys_jquery_ui_dialog_defaults', $configurations);
  $pattern = _ui_dialog_pattern($configurations);
  return jquery_support($selector,'dialog', $pattern);
}

/**
 * Enable the dialog
 * @param string $selector jQuery Selector
 * @param <type> $index Indeci del tab
 */
function ui_dialog_enable($selector){
  return add_method_support('dialog',$selector, 'enable');
}

/**
 * Disable the dialog.
 * @param string $selector jQuery Selector
 */
function ui_dialog_disable($selector){
  return add_method_support('dialog',$selector, 'disable');
}

/**
 * Close the dialog.
 * @param string $selector jQuery Selector
 */
function ui_dialog_close($selector){
  return add_method_support('dialog',$selector, 'close');
}

/**
 * Open the dialog.
 * @param string $selector jQuery Selector
 */
function ui_dialog_open($selector){
  return add_method_support('dialog',$selector, 'open');
}

/**
 * Returns true if the dialog is currently open.
 * @param string $selector jQuery Selector
 */
function ui_dialog_is_open($selector){
  return add_method_support('dialog',$selector, 'isOpen');
}

/**
 * Util function for open dialog
 * @param string $event The event opens
 * @param string $listener The jQuery Selector event listener
 * @param string $dialogId The Dialog Id
 * @param boolean $isInternal if the helper is executed within a javascript function
 */
function ui_dialog_open_on_event($event,$listener,$dialogId, $isInternal = false){
  if($isInternal){
    return jquery_support($listener, $event, like_function(ui_dialog_open($dialogId)));
  }else{
    return add_jquery_support($listener, 'click', like_function(ui_dialog_open($dialogId)));
  }
}

/**
 * Move the dialog to the top of the dialogs stack.
 * @param string $selector jQuery Selector
 */
function ui_dialog_move_to_top($selector){
  return add_method_support('dialog',$selector, 'moveToTop');
}

/**
 * Get any dialog option.
 * @param string $selector jQuery Selector
 * @param string $option The widget option to get
 */
function ui_dialog_get_option($selector,$option){
  return jquery_support($selector,'dialog', "'option' , '$option'");
}

/**
 * Set any accordion option.
 * @param string $selector jQuery Selector
 * @param string $option_map Options array to set
 * @return <type>
 */
function ui_dialog_set_options($selector,$option_map){
  $pattern = _ui_dialog_pattern($option_map);
  return ui_set_widget_options('dialog', $selector,$pattern);
}

/**
 * Internal function
 * @param array() $configuration Configuration array
 * @return string JSON Array.
 * @see ui_dialog_init()
 */
function _ui_dialog_pattern($configuration){
  $pattern = '';
  if(is_array($configuration) && sizeof($configuration) > 0){
    $pattern = '{';
    //OPTIONS SUPPORT  http://jqueryui.com/demos/dialog/#options

    if(isset($configuration['autoOpen'])){      $pattern .= toJQueryOption('autoOpen', $configuration['autoOpen']); }
    if(isset($configuration['bgiframe'])){      $pattern .= toJQueryOption('bgiframe', $configuration['bgiframe']); }
    if(isset($configuration['buttons'])){       $pattern .= toJQueryOption('buttons', $configuration['buttons'], true);   }
    if(isset($configuration['closeOnEscape'])){ $pattern .= toJQueryOption('closeOnEscape', $configuration['closeOnEscape']); }
    if(isset($configuration['dialogClass'])){   $pattern .= toJQueryOption('dialogClass', $configuration['dialogClass']); }
    if(isset($configuration['draggable'])){     $pattern .= toJQueryOption('draggable', $configuration['draggable']); }
    if(isset($configuration['height'])){        $pattern .= toJQueryOption('height', $configuration['height']); }
    if(isset($configuration['hide'])){          $pattern .= toJQueryOption('hide', $configuration['hide']); }
    if(isset($configuration['maxHeight'])){     $pattern .= toJQueryOption('maxHeight', $configuration['maxHeight']); }
    if(isset($configuration['maxWidth'])){      $pattern .= toJQueryOption('maxWidth', $configuration['maxWidth']); }
    if(isset($configuration['minHeight'])){     $pattern .= toJQueryOption('minHeight', $configuration['minHeight']); }
    if(isset($configuration['minWidth'])){      $pattern .= toJQueryOption('minWidth', $configuration['minWidth']); }
    if(isset($configuration['modal'])){         $pattern .= toJQueryOption('modal', $configuration['modal']); }
    if(isset($configuration['position'])){      $pattern .= toJQueryOption('position', $configuration['position']); }
    if(isset($configuration['resizable'])){     $pattern .= toJQueryOption('resizable', $configuration['resizable']); }
    if(isset($configuration['show'])){          $pattern .= toJQueryOption('show', $configuration['show']); }
    if(isset($configuration['stack'])){         $pattern .= toJQueryOption('stack', $configuration['stack']); }
    if(isset($configuration['title'])){         $pattern .= toJQueryOption('title', $configuration['title']); }
    if(isset($configuration['width'])){         $pattern .= toJQueryOption('width', $configuration['width']); }
    if(isset($configuration['zIndex'])){        $pattern .= toJQueryOption('zIndex', $configuration['zIndex']); }

    //EVENTS SUPPORT  http://jqueryui.com/demos/dialog/#events
    if(isset($configuration['beforeclose'])){   $pattern .= toJQueryOption('beforeclose', $configuration['beforeclose'] , true); }
    if(isset($configuration['open'])){          $pattern .= toJQueryOption('open', $configuration['open'] , true); }
    if(isset($configuration['focus'])){         $pattern .= toJQueryOption('focus', $configuration['focus'] , true); }
    if(isset($configuration['drag'])){          $pattern .= toJQueryOption('drag', $configuration['drag'] , true); }
    if(isset($configuration['dragStart'])){     $pattern .= toJQueryOption('dragStart', $configuration['dragStart'] , true); }
    if(isset($configuration['dragStop'])){      $pattern .= toJQueryOption('dragStop', $configuration['dragStop'] , true); }
    if(isset($configuration['resizeStart'])){   $pattern .= toJQueryOption('resizeStart', $configuration['resizeStart'] , true); }
    if(isset($configuration['resize'])){        $pattern .= toJQueryOption('resize', $configuration['resize'] , true); }
    if(isset($configuration['resizeStop'])){    $pattern .= toJQueryOption('resizeStop', $configuration['resizeStop'] , true); }
    if(isset($configuration['close'])){         $pattern .= toJQueryOption('close', $configuration['close'] , true); }
    
    if($pattern != '{')
      $pattern = substr($pattern,0,(strlen($pattern)) - 1);
    $pattern .= '}';
  }
  return $pattern;
}

/*
 || END UI.DIALOG
 */