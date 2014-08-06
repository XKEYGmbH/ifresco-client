<?php
namespace Ifresco\ClientBundle\Component\Alfresco\Helpers;
/*
 || UI.SORTABLE
 */

/*
 * Initial functions
 * Starts adding the helper ysJQueryUICore core files jquery.ui
 * Call set_sortable_configuration_files function ();
 */
if(!function_exists('ui_core_exist')){
  use_helper('ysJQueryUICore');
}
set_sortable_configuration_files();

/**
 *
 * Add the context all the files needed to enable basic
 * ui.sortable and the effect can be run. View settings.yml
 * @param string $type default value: null; if $type = 'CSS' Add only stylesheets
 *        if $type = 'JS' Add only javascripts files .
 */
function set_sortable_configuration_files($type = null){
  set_ui_common_configuration_files('sortable', $type);
}

/**
 * Starts the sortable support and its configuration
 * @param string $selector jQuery Selector
 * @param array() $configurations Array. See the options and events in
 *        http://jqueryui.com/demos/sortable/#options
 *        http://jqueryui.com/demos/sortable/#events
 */
function ui_sortable_support_to($selector , $configurations = array()){
    $configurations = get_default_widget_configuration('app_ys_jquery_ui_sortable_defaults', $configurations);
    $pattern = _ui_sortable_pattern($configurations);
    init_ui_sintax('sortable',$pattern,$selector ,$configurations);
}

/**
 * Starts the sortable support and its configuration programmatically
 * @param string $selector jQuery Selector
 * @param array() $configurations Array. See the options and events in
 *        http://jqueryui.com/demos/sortable/#options
 *        http://jqueryui.com/demos/sortable/#events
 */
function ui_sortable_build($selector , $configurations = array() , $html_attributes = null){
    $configurations = get_default_widget_configuration('app_ys_jquery_ui_sortable_defaults', $configurations);
    $pattern = _ui_sortable_pattern($configurations);
    return jquery_support($selector,'sortable', $pattern);
}

/**
 * Remove the sortable functionality completely.
 * This will return the element back to its pre-init state.
 * @param string $selector jQuery Selector
 */
function ui_sortable_destroy($selector){
  return add_method_support('sortable',$selector, 'destroy');
}

/**
 * Enable the sortable
 * @param string $selector jQuery Selector
 */
function ui_sortable_enable($selector){
  return add_method_support('sortable',$selector, 'enable');
}

/**
 * Disable the sortable
 * @param string $selector jQuery Selector
 */
function ui_sortable_disable($selector){
  return add_method_support('sortable',$selector, 'disable');
}

/**
 * Refresh the sortable items.
 * Custom trigger the reloading of all sortable items,
 * causing new items to be recognized.
 * @param string $selector jQuery Selector
 */
function ui_sortable_refresh($selector){
  return add_method_support('sortable',$selector, 'refresh');
}

/**
 * Refresh the cached positions of the sortables' items.
 * Calling this method refreshes the cached item positions of all sortables.
 * This is usually done automatically by the script and slows down performance.
 * Use wisely.
 * @param string $selector jQuery Selector
 */
function ui_sortable_refresh_position($selector){
  return add_method_support('sortable',$selector, 'refreshPositions');
}

/**
 * Serializes the sortable's item id's into a form/ajax submittable string.
 * Calling this method produces a hash that can be appended to any url to easily
 * submit a new item order back to the server.
 * It works by default by looking at the id of each item in the format
 * 'setname_number', and it spits out a hash like
 * "setname[]=number&setname[]=number".
 * You can also give in a option hash as second argument to custom define
 * how the function works.
 * The possible options are: 'key' (replaces part1[] with whatever you want),
 * 'attribute' (test another attribute than 'id')
 * and 'expression' (use your own regexp). If serialize returns an empty string,
 * make sure the id attributes include an underscore.
 * They must be in the form: "set_number" For example, a 3 element list with id
 * attributes foo_1, foo_5, foo_2 will serialize to foo[]=1&foo[]=5&foo[]=2.
 * You can use an underscore, equal sign or hyphen to separate the set and number.
 * For example foo=1 or foo-1 or foo_1 all serialize to foo[]=1
 * @param string $selector jQuery Selector
 * @param array $options options Array: Ex. array('key' => 'item[]')
 */
function ui_sortable_serialize($selector, $options = array()){
  if(is_array($options) && sizeof($options) > 0){
    return add_method_support('sortable',$selector, 'serialize', json_encode($options));
  }else{
    return add_method_support('sortable',$selector, 'serialize');
  }
}

/**
 * Serializes the sortable's item id's into an array of string
 * @param string $selector jQuery Selector
 */
function ui_sortable_to_array($selector){
  return add_method_support('sortable',$selector, 'toArray');
}

/**
 * Cancels a change in the current sortable and reverts it back to how it was
 * before the current sort started.
 * Useful in the stop and receive callback functions
 * @param string $selector jQuery Selector
 */
function ui_sortable_cancel($selector){
  return add_method_support('sortable',$selector, 'cancel');
}

/**
 * Get any sortable option.
 * @param string $selector jQuery Selector
 * @param string $option The widget option to get
 */
function ui_sortable_get_option($selector,$option){
  return jquery_support($selector,'sortable', "'option' , '$option'");
}

/**
 * Set any sortable option.
 * @param string $selector jQuery Selector
 * @param string $option_map Options array to set
 * @return <type>
 */
function ui_sortable_set_options($selector,$option_map){
  $pattern = _ui_sortable_pattern($option_map);
  return ui_set_widget_options('sortable', $selector,$pattern);
}

/**
 * Internal function
 * @param array() $configuration Configuration array
 * @return string JSON Array.
 * @see ui_sortable_support_to()
 */
function _ui_sortable_pattern($configuration){
  $pattern = '';
  if(is_array($configuration) && sizeof($configuration) > 0){
    $pattern = '{';

    //OPTIONS SUPPORT
    if(isset($configuration['appendTo'])){              $pattern .= toJQueryOption('appendTo', $configuration['appendTo']); }
    if(isset($configuration['axis'])){                  $pattern .= toJQueryOption('axis', $configuration['axis']); }
    if(isset($configuration['cancel'])){                $pattern .= toJQueryOption('cancel', $configuration['cancel']); }
    if(isset($configuration['connectWith'])){           $pattern .= toJQueryOption('connectWith', $configuration['connectWith']); }
    if(isset($configuration['containment'])){           $pattern .= toJQueryOption('containment', $configuration['containment']); }
    if(isset($configuration['cursor'])){                $pattern .= toJQueryOption('cursor', $configuration['cursor']); }
    if(isset($configuration['cursorAt'])){              $pattern .= toJQueryOption('cursorAt', $configuration['cursorAt']); }
    if(isset($configuration['delay'])){                 $pattern .= toJQueryOption('delay', $configuration['delay']); }
    if(isset($configuration['distance'])){              $pattern .= toJQueryOption('distance', $configuration['distance']); }
    if(isset($configuration['forcePlaceholderSize'])){  $pattern .= toJQueryOption('forcePlaceholderSize', $configuration['forcePlaceholderSize']); }
    if(isset($configuration['grid'])){                  $pattern .= toJQueryOption('grid', $configuration['grid']); }
    if(isset($configuration['handle'])){                $pattern .= toJQueryOption('handle', $configuration['handle']); }
    if(isset($configuration['handleElement'])){         $pattern .= toJQueryOption('handle', $configuration['handleElement'], true); }
    if(isset($configuration['helper'])){                $pattern .= toJQueryOption('helper', $configuration['helper']); }
    if(isset($configuration['helperFunction'])){        $pattern .= toJQueryOption('helper', $configuration['helperFunction'], true); }
    if(isset($configuration['items'])){                 $pattern .= toJQueryOption('items', $configuration['items']); }
    if(isset($configuration['opacity'])){               $pattern .= toJQueryOption('opacity', $configuration['opacity']); }
    if(isset($configuration['placeholder'])){           $pattern .= toJQueryOption('placeholder', $configuration['placeholder']); }
    if(isset($configuration['revert'])){                $pattern .= toJQueryOption('revert', $configuration['revert']); }
    if(isset($configuration['scroll'])){                $pattern .= toJQueryOption('scroll', $configuration['scroll']); }
    if(isset($configuration['scrollSensitivity'])){     $pattern .= toJQueryOption('scrollSensitivity', $configuration['scrollSensitivity']); }
    if(isset($configuration['scrollSpeed'])){           $pattern .= toJQueryOption('scrollSpeed', $configuration['scrollSpeed']); }
    if(isset($configuration['tolerance'])){             $pattern .= toJQueryOption('tolerance', $configuration['tolerance']); }
    if(isset($configuration['zIndex'])){                $pattern .= toJQueryOption('zIndex', $configuration['zIndex']); }

    //EVENTS SUPPORT
    if(isset($configuration['start'])){         $pattern .= toJQueryOption('start', $configuration['start'], true); }
    if(isset($configuration['sort'])){          $pattern .= toJQueryOption('sort', $configuration['sort'], true); }
    if(isset($configuration['change'])){        $pattern .= toJQueryOption('change', $configuration['change'], true); }
    if(isset($configuration['beforeStop'])){    $pattern .= toJQueryOption('beforeStop', $configuration['beforeStop'], true); }
    if(isset($configuration['stop'])){          $pattern .= toJQueryOption('stop', $configuration['stop'], true); }
    if(isset($configuration['update'])){        $pattern .= toJQueryOption('update', $configuration['update'], true); }
    if(isset($configuration['remove'])){        $pattern .= toJQueryOption('remove', $configuration['remove'], true); }
    if(isset($configuration['over'])){          $pattern .= toJQueryOption('over', $configuration['over'], true); }
    if(isset($configuration['out'])){           $pattern .= toJQueryOption('out', $configuration['out'], true); }
    if(isset($configuration['activate'])){      $pattern .= toJQueryOption('activate', $configuration['activate'], true); }
    if(isset($configuration['deactivate'])){    $pattern .= toJQueryOption('deactivate', $configuration['deactivate'], true); }

    if($pattern != '{')
      $pattern = substr($pattern,0,(strlen($pattern)) - 1);
    $pattern .= '}';
  }
  return $pattern;
}
/*
 || END UI.SORTABLE
 */