<?php
/*
 || UI.TABS
 */

/*
 * Initial functions
 * Starts adding the helper ysJQueryUICore core files jquery.ui
 * Call set_tabs_configuration_files() function ();
 */
if(!function_exists('ui_core_exist')){
  use_helper('ysJQueryUICore');
}

set_tabs_configuration_files();

/**
 *
 * Add the context all the files needed to enable basic
 * ui.tab and can be run the widget. View settings.yml
 * @param string $type default value: null; if $type = 'CSS' Add only stylesheets
 *        if $type = 'JS' Add only javascripts files .
 */
function set_tabs_configuration_files($type = null){
  set_ui_common_configuration_files('tabs', $type);
}

/**
 * Starts the tab panel and its configuration
 * @param string $id tab panel Id
 * @param array() $configurations Array. See the options and events in
 *        http://jqueryui.com/demos/tabs/#options
 *        http://jqueryui.com/demos/tabs/#events
 * @param string $html_attributes Additional options parameter (html attributes)
 */
function ui_tabs_init_panel($id , $configurations = array() , $html_attributes = null){
  $configurations = get_default_widget_configuration('app_ys_jquery_ui_tabs_defaults', $configurations);
  $pattern = _ui_tabs_pattern($configurations);
  init_ui_widget('tabs',$pattern,$id ,$configurations);
  echo '<div id="' . $id . '" '. $html_attributes .'>';
}

/**
 * Starts the tab. HTML
 */
function ui_tabs_init(){
   echo '<ul>';
}

/**
 * Starts the tab header
 * @param string $href  Section to load. Can be a remote page
 *                      that will be loaded via ajax
 * @param string $label Tab label
 * @param string $is_remote true If the page is not in the current document
 * @param string $template The header tabs elements
 *        default value <li><a href="%HREF%"><span>%LABEL%</span></a></li>
 */
function ui_tab($href, $label, $is_remote = false, $template = '<li><a href="%HREF%"><span>%LABEL%</span></a></li>'){
   if(!$is_remote){
     $href = '#' . $href;
   }
   $tabs_header = str_replace('%HREF%'  , $href  , $template);
   echo $tabs_header = str_replace('%LABEL%' , $label , $tabs_header);
}

/**
 * Starts content for a tab
 * @param string $id The tab id
 */
function ui_tabs_init_content_for($id){
  echo sprintf('<div id="%s">',$id);
}

/**
 * Ends the content tab section
 */
function ui_tabs_end_content(){
  echo sprintf('</div>');
}

/**
 * Ends the tab headers
 */
function ui_tabs_end(){
   echo '</ul>';
}

/**
 * Ends the tab panel
 */
function ui_tabs_end_panel(){
   echo '</div>';
}

/**
 * Starts the header sections
 * @param string $selector jQuery Selector
 */
function ui_tabs_destroy_panel($selector){
  return add_method_support('tabs',$selector, 'destroy');
}

/**
 * Starts the header sections
 * @param string $selector jQuery Selector
 * @param array() $configurations Array. See the options and events in
 *        http://jqueryui.com/demos/tabs/#options
 *        http://jqueryui.com/demos/tabs/#events
 */
function ui_tabs_build_panel($selector, $configurations = null){
  $configurations = get_default_widget_configuration('app_ys_jquery_ui_tabs_defaults', $configurations);
  $pattern = _ui_tabs_pattern($configurations);
  return jquery_support($selector,'tabs', $pattern);
}

/**
 * Enable the tabs.
 * @param string $selector jQuery Selector
 * @param string $index Tab index, is the zero-based index of the tab to be
 *               selected or the id selector of the panel the tab is associated
 *               with (the tab's href fragment identifier, e.g. hash,
 *               points to the panel's id).
 */
function ui_tabs_enable($selector, $index = ''){
  return add_method_support('tabs',$selector, 'enable', $index);
}

/**
 * Disable the tabs.
 * @param string $selector jQuery Selector
 * @param string $index Tab index, is the zero-based index of the tab to be
 *               selected or the id selector of the panel the tab is associated
 *               with (the tab's href fragment identifier, e.g. hash,
 *               points to the panel's id).
 */
function ui_tabs_disable($selector, $index = ''){
  return add_method_support('tabs',$selector, 'disable', $index);
}

/**
 * Select a tab, as if it were clicked
 * @param string $selector jQuery Selector
 * @param string $index Tab index, is the zero-based index of the tab to be
 *               selected or the id selector of the panel the tab is associated
 *               with (the tab's href fragment identifier, e.g. hash,
 *               points to the panel's id).
 */
function ui_tabs_select($selector, $index){
  return add_method_support('tabs',$selector, 'select', $index);
}

/**
 * Reload the content of an Ajax tab programmatically
 * @param string $selector jQuery Selector
 * @param <type> $index Is the zero-based index of the tab to be reloaded.
 */
function ui_tabs_load($selector, $index){
  return add_method_support('tabs',$selector, 'load', $index);
}

/**
 * Remove a tab
 * @param string $selector jQuery Selector
 * @param <type> $index Is the zero-based index of the tab to be removed.
 */
function ui_tabs_remove($selector, $index){
  return add_method_support('tabs',$selector, 'remove', $index);
}

/**
 * Add a new tab
 * @param string $selector jQuery Selector
 * @param string $url consisting of a fragment identifier only to create an
 * in-page tab or a full url (relative or absolute, no cross-domain support)
 * to turn the new tab into an Ajax (remote) tab
 * @param string $label The tab label
 * @param string $index The zero-based position where to insert the new tab.
 */
function ui_tabs_add($selector, $url , $label , $index = 0){
  $params = sprintf("'%s','%s',%s",$url , $label , $index);
  return add_method_support('tabs',$selector, 'add', $params);
}

/**
 * Retrieve the number of tabs of the first matched tab pane.
 * @param string $selector jQuery Selector
 * @return <type> 
 */
function ui_tabs_length($selector){
  return add_method_support('tabs',$selector, 'length');
}


/**
 * Terminate all running tab ajax requests and animations.
 * @param string $selector jQuery Selector
 */
function ui_tabs_abort($selector){
  return add_method_support('tabs', $selector, 'abort');
}

/**
 * Set up an automatic rotation through tabs of a tab pane.
 * 
 * @param string $selector jQuery Selector
 * @param string $ms Is an amount of time in milliseconds until the next tab in
 *                   the cycle gets activated. Set value 0 to stop the rotation.
 * @param string $continuing Control whether or not to continue the rotation
 * after a tab has been selected by a user. Default: false
 * @return string
 */
function ui_tabs_rotate($selector,$ms = 0, $continuing = false){
  $continuingVal = false;
  if (is_bool($continuing) &&  $continuing == true){
    $continuingVal = true;
  }
  $params = sprintf("%s,%s", $ms * 1000, boolean_for_javascript($continuingVal));
  return add_method_support('tabs',$selector, 'rotate', $params);
}

/**
 * Make the tabs sortable.
 * @param string $selector jQuery Selector
 * @param string $axis If defined, the items can be dragged only horizontally or vertically,
 * default horizontally
 * @param boolean $add_configuration_file For add ui.sortable.js automatically
 * @return <type>
 */
function ui_tabs_sortable($selector , $axis = 'x' ,$add_configuration_file = true){
  if($add_configuration_file){
    add_js_configuration_file('ui.sortable.js');
  }
  return jquery_support($selector, 
                        'tabs',
                        null,
                        true ,
                        '.find(".ui-tabs-nav").sortable({axis:"'. $axis .'"})');
}


/**
 * Change the url from which an Ajax (remote) tab will be loaded
 * @param string $selector jQuery Selector
 * @param string $index The zero-based index of the tab of which its URL is to
 *               be updated
 * @param <type> $new_url The URL the content of the tab is loaded from
 * @return <type>
 */
function ui_tabs_url($selector, $index , $new_url){
  $params = sprintf("'%s','%s'", $index , $new_url);
  return add_method_support('tabs',$selector, 'url', $params);
}

/**
 * Get any tab option.
 * @param string $selector jQuery Selector
 * @param string $option The widget option to get
 * @return <type> 
 */
function ui_tabs_get_option($selector,$option){
  return jquery_support($selector,'tabs', "'option' , '$option'");
}

/**
 * Set any tab options.
 * @param string $selector jQuery Selector
 * @param string $option_map Options array to set
 */
function ui_tabs_set_options($selector,$option_map){
  $pattern = _ui_tabs_pattern($option_map);
  return ui_set_widget_options('tabs', $selector,$pattern);
}




/**
 * Internal function
 * @param array() $configuration Configuration array
 * @return string JSON Array.
 * @see ui_tabs_init_panel()
 */
function _ui_tabs_pattern($configuration){
  $pattern = '';
  if(is_array($configuration) && sizeof($configuration) > 0){
    $pattern = '{';

    //OPTIONS SUPPORT http://jqueryui.com/demos/tabs/#options
    if(isset($configuration['event'])){         $pattern .= toJQueryOption('event', $configuration['event']); }
    if(isset($configuration['ajaxOptions'])){   $pattern .= toJQueryOption('ajaxOptions', $configuration['ajaxOptions']); }
    if(isset($configuration['cookie'])){        $pattern .= toJQueryOption('cookie', $configuration['cookie']); }
    if(isset($configuration['cache'])){         $pattern .= toJQueryOption('cache', $configuration['cache']); }
    if(isset($configuration['disabled'])){      $pattern .= toJQueryOption('disabled', $configuration['disabled']); }
    if(isset($configuration['idPrefix'])){      $pattern .= toJQueryOption('idPrefix', $configuration['idPrefix']); }
    if(isset($configuration['fx'])){            $pattern .= toJQueryOption('fx', $configuration['fx']); }
    if(isset($configuration['panelTemplate'])){ $pattern .= toJQueryOption('panelTemplate', $configuration['panelTemplate']); }
    if(isset($configuration['selected'])){      $pattern .= toJQueryOption('selected', $configuration['selected']); }
    if(isset($configuration['spinner'])){       $pattern .= toJQueryOption('spinner', $configuration['spinner']); }
    if(isset($configuration['tabTemplate'])){   $pattern .= toJQueryOption('tabTemplate', $configuration['tabTemplate']); }
    if(isset($configuration['unselect'])){      $pattern .= toJQueryOption('unselect', $configuration['unselect']); }
    if(isset($configuration['collapsible'])){   $pattern .= toJQueryOption('collapsible', $configuration['collapsible']); }
    if(isset($configuration['deselectable'])){  $pattern .= toJQueryOption('deselectable', $configuration['deselectable']); }
    
    //EVENTS SUPPORT  http://jqueryui.com/demos/tabs/#events
    if(isset($configuration['select'])){        $pattern .= toJQueryOption('select', $configuration['select'], true); }
    if(isset($configuration['load'])){          $pattern .= toJQueryOption('load', $configuration['load'], true); }
    if(isset($configuration['show'])){          $pattern .= toJQueryOption('show', $configuration['show'], true); }
    if(isset($configuration['add'])){           $pattern .= toJQueryOption('add', $configuration['add'], true); }
    if(isset($configuration['remove'])){        $pattern .= toJQueryOption('remove', $configuration['remove'], true); }
    if(isset($configuration['enable'])){        $pattern .= toJQueryOption('enable', $configuration['enable'], true); }
    if(isset($configuration['disable'])){       $pattern .= toJQueryOption('disable', $configuration['disable'], true); }

    if($pattern != '{')
      $pattern = substr($pattern,0,(strlen($pattern)) - 1);
    $pattern .= '}';
  }
  return $pattern;
}
/*
 || END UI.TABS
 */