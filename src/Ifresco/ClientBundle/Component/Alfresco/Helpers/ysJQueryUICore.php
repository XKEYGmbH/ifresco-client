<?php
namespace Ifresco\ClientBundle\Component\Alfresco\Helpers;

/*
 || UI.CORE
 */

/*
 * Initial functions
 * Starts adding the helper ysJQueryUICore core files jquery.ui
 * Call set_ui_core_configuration_files function ();
 */
set_ui_core_configuration_files();
set_default_configuration_files();

/**
 *
 * Add the context all the files needed to enable basic
 * ui.accordion and can be run the widget. View settings.yml
 * @param string $type default value: null; if $type = 'CSS' Add only stylesheets
 *        if $type = 'JS' Add only javascripts files .
 */
function set_ui_core_configuration_files($type = null){
  set_ui_common_configuration_files('core', $type);
}

function set_default_configuration_files(){
    /*$styles = sfConfig::get('app_ys_jquery_ui_default_css', array());
    $jsFiles= sfConfig::get('app_ys_jquery_ui_default_js', array());*/

    //due to no sfConfig::get in Symfony 2
    $styles = array (
        0 => '/ysJQueryUIPlugin/css/ui.fgbutton.css',
        1 => '/ysJQueryUIPlugin/css/jquery.ui.css',
    );
    $jsFiles= array (
        0 => '/ysJQueryUIPlugin/js/jquery/ui.animations.js',
    );
    add_js_configuration_file($jsFiles, 'last' , '');
    add_css_configuration_file($styles, 'last' , '');
}

/**
 * TODO documentation
 * @param string $id
 * @param array() $type
 */
function set_ui_common_configuration_files($id,$type = null){

    //due to Symfony 2
    switch ($id) {
        case 'core':
            $ui_core_js = array (
                0 => 'ui.core.js',
            );

            $ui_core_css = array (
                0 => 'jquery-ui-1.7.2.custom.css',
            );
            break;

        default:
            $ui_js_index = sprintf('app_ys_jquery_ui_%s_js',$id);
            $ui_css_index = sprintf('app_ys_jquery_ui_%s_css',$id);
            $ui_core_css = get_prev_settings($ui_css_index, array());
            $ui_core_js = get_prev_settings($ui_js_index, array());
    }

  $css_path = get_ui_css_path();
  $js_path = get_ui_js_path();
  $type = strtolower(trim($type));
  switch ($type) {
    case 'css':
      foreach($ui_core_css as $core_styles){
        add_css_configuration_file($core_styles, 'last' , $css_path);
      }
      break;
    case 'js':
      foreach($ui_core_js as $core_js){
        add_js_configuration_file($core_js, 'last' , $js_path);
      }
      break;
    default:
      foreach($ui_core_css as $core_styles){
        add_css_configuration_file($core_styles, 'last' , $css_path);
      }
      foreach($ui_core_js as $core_js){
        add_js_configuration_file($core_js, 'last' , $js_path);
      }
      break;
  }
}

/**
 * .css
 * @return <type> jquery.ui Directory Configuration files. See app.yml
 */
function get_ui_css_path(){
    //due to Symfony 2
    /*$ui_theme_dir = sfConfig::get('app_ys_jquery_ui_theme_dir', '/ysJQueryUIPlugin/css/themes');
    $ui_theme = sfConfig::get('app_ys_jquery_ui_theme', 'redmond');*/
  $ui_theme_dir = '/ysJQueryUIPlugin/css/themes';
  $ui_theme = 'cupertino';
  $path = $ui_theme_dir . '/' . $ui_theme . '/';
  return $path;
}

/**
 * .js
 * @return <type> jquery.ui Directory Configuration files. See app.yml
 */
function get_ui_js_path(){
    //due to Symfony 2
  //$ui_js_dir = sfConfig::get('app_ys_jquery_ui_js_lib_dir', '/ysJQueryUIPlugin/js/jquery/ui');
  $ui_js_dir = '/ysJQueryUIPlugin/js/jquery/ui';
  $path = $ui_js_dir . '/';
  return $path;
}


/**
 * TODO documentation
 * @param string|array() $filesName The file name to add.
 * @param string $position The position ('first' or 'last')
 * @param string $path The file path (optional)
 */
function add_css_configuration_file($filesName, $position = 'last',  $path = null ){
  if(is_null($path)){
    $path = get_ui_css_path();
  }
  \Ifresco\ClientBundle\Component\Alfresco\Helpers\add_css($filesName, $path, $position);
}

/**
 * TODO documentation
 * @param string|array() $filesName The file name to add.
 * @param string $position The position ('first' or 'last')
 * @param string $path The file path (optional)
 */
function add_js_configuration_file($filesName, $position = 'last', $path = null){
  if(is_null($path)){
    $path = get_ui_js_path();
  }
    \Ifresco\ClientBundle\Component\Alfresco\Helpers\add_js($filesName, $path, $position);
}


/**
 * Set any ui widget option.
 * @param string $selector jQuery Selector
 * @param string $pattern Options pattern
 * @return <type>
 */
function ui_set_widget_options($widget,$selector,$pattern){
  $support = '';
  $pattern = substr($pattern,1, (strlen($pattern)));
  $pattern = substr($pattern,0,(strlen($pattern)) - 1);
  $map_pattern = split(',' , $pattern);
  foreach($map_pattern as $options){
    $option = split(':', $options);
    $support .= jquery_support($selector,$widget, "'option' , '$option[0]' , $option[1]");
  }
  return $support;
}


/**
 * Add the configuration files needed to support effects on jquery.ui
 * @param <type> $effects The effect ('bounce, slide, blink, ...')
 */
function ui_add_effects_support($effects){
  $effectsSupport[0] = 'effects.core.js';
  $i=1;
  if(is_array($effects) && !is_null($effects)){
    foreach($effects as $effect){
      $effectsSupport[$i++] = sprintf('effects.%s.js',$effect);
    }
  }else{
    $effectsSupport[$i++] = sprintf('effects.%s.js',$effects);
  }
  add_js_configuration_file($effectsSupport);
}


/**
 * TODO documentation
 * @param string $widget
 * @param string $selector
 * @param string $method
 * @param string $params
 */
function add_method_support($widget, $selector,$method , $params = ''){
  if (trim($params) != '' ){
    $params = ',' . $params;
  }
  return jquery_support($selector, $widget , "'$method' $params");
}

/**
 * TODO documentation
 * @param array() $configuration
 * @param array() $defaultValues
 */
function check_configuration($configuration, $defaultValues = null){
  if(!is_null($defaultValues)){
    if(is_array($configuration)){
      if(!isset($configuration['listener'])){
      $configuration['listener'] = $defaultValues['listener'];
      }
    }else{
      $listener = $configuration['listener'];
      if(!isset($listener['event'])) $listener['event'] = $defaultValues['event'];
      if(!isset($listener['selector'])) $listener['selector'] = $defaultValues['selector'];
      $configuration['listener'] = $listener;
    }
  }
  return $configuration;
}

/**
 * TODO documentation
 * @param string $type
 * @param string $pattern
 * @param string $id
 * @param array() $configurations
 */
function init_ui_widget($type, $pattern, $id , $configurations = array()){
  $listenerConfiguration['listener'] = array('selector' => '#'. $id,'event' => 'ready');
  $configurations = check_configuration($configurations,$listenerConfiguration);
  echo return_jquery($configurations, jquery_support('#'. $id, $type , $pattern));
}

/**
 * TODO documentation
 * @param string $type
 * @param string $pattern
 * @param string $id
 * @param array() $configurations
 */
function init_ui_sintax($type, $pattern, $id , $configurations = array()){
  $listenerConfiguration['listener'] = array('selector' => $id,'event' => 'ready');
  $configurations = check_configuration($configurations,$listenerConfiguration);
  echo return_jquery($configurations, jquery_support($id, $type , $pattern));
}

/**
 * TODO documentation
 * @param string $type
 * @param string $pattern
 * @param string $id
 * @param array() $configurations
 */
function init_ui_effects($type, $pattern, $id , $configurations = array()){
  $return = 'return false;';
  if (isset($configurations['continue']) && $configurations['continue'] === true){
      $return = '';
  }
  return return_jquery($configurations, jquery_support($id, $type , $pattern). $return);
}

/**
 * TODO configuration
 * @param string $file
 */
function _set_ui_i18n_file($file){
  $uiI18nDir = get_prev_settings('app_ys_jquery_ui_i18n_dir', '/ysJQueryUIPlugin/js/jquery/ui/i18n');
  add_js_configuration_file($file, 'last' , $uiI18nDir . '/');
}

/**
 * Change the jquery i18n file...
 * @param string $file The .js i18n file name ('es,en,fr')
 */
function ui_change_i18n($file){
   _set_ui_i18n_file($file);
}


/**
 * TODO documentation
 * @return boolean
 */
function ui_core_exist(){
  return true;
}

/*
 * WIDGETS
 */


/**
 * Starts a Content Panel
 * @param array() $configuration the class,style, or id widget
 * @param string $attributes html attributes and properties.
 */
function ui_init_content_panel($configuration = '', $attributes = ''){
  $defaultClass = 'ui-widget ui-widget-content ui-draggable ui-resizable';
  $defaultStyle = 'width:auto; height:auto; ';
  $id = '';
  if(is_array($configuration)){
     if(isset($configuration['class'])){$defaultClass=$configuration['class'];}
     if(isset($configuration['style'])){$defaultStyle .=$configuration['style'];}
     if(isset($configuration['id'])){$id=$configuration['id'];}
  }
  $pattern = sprintf('<div id="%s" class="%s" style="%s" %s>',$id, $defaultClass,$defaultStyle,$attributes);
  return $pattern;
}


/**
 * Ends a Content Panel
 */
function ui_end_content_panel(){
  return '</div>';
}


/**
 * Starts one section to add content to the page. It is recommended
 * always use this helper to add content to applications of
 * this way the content will be affected if we change the theme of jquery.ui
 * @param array() $configuration the class,style, or id widget
 * @param string $attributes html attributes and properties.
 */
function ui_init_content($configuration = '', $attributes = ''){
  $defaultClass = 'ui-widget-content';
  $defaultStyle = '';
  $id = '';
  if(is_array($configuration)){
     if(isset($configuration['class'])){$defaultClass=$configuration['class'];}
     if(isset($configuration['style'])){$defaultStyle=$configuration['style'];}
     if(isset($configuration['id'])){$id=$configuration['id'];}
  }
  $pattern = sprintf('<div id="%s" class="%s" style="%s" %s>',$id,$defaultClass,$defaultStyle,$attributes);
  return $pattern;
}

/**
 * Ends content
 */
function ui_end_content(){
  return '</div>';
}


/**
 * Start a section with jquery.ui styles for titles.
 * @param array() $configuration the class,style,,image,textAlign,icon or id widget
 * @param string $attributes html attributes and properties.
 */
function ui_init_title($configuration = '', $attributes = ''){
  $defaultClass = 'ui-widget-header ui-helper-clearfix';
  $defaultStyle = '';
  $id = '';
  $uiIcon = '';
  $image = '';
  $textAlign = 'left';
  if(is_array($configuration)){
     if(isset($configuration['class'])){$defaultClass=$configuration['class'];}
     if(isset($configuration['style'])){$defaultStyle=$configuration['style'];}
     if(isset($configuration['id'])){$id=$configuration['id'];}
     if(isset($configuration['image'])){$image=$configuration['image'];}
     if(isset($configuration['textAlign'])){$textAlign=$configuration['textAlign'];}
     if(isset($configuration['icon'])){
       $uiIcon =  ui_create_icon($configuration['icon']);
       $defaultStyle = ' position:absolute; top:3px; left:20px; display:block; overflow:hidden;' . $defaultStyle;
     }else{
       $uiIcon = '';
     }
  }
  $pattern = sprintf('<div style="position:relative"><div id="%s" class="%s" style="text-align:%s;">'.$uiIcon.$image.'<span style="%s" %s>',$id,$defaultClass,$textAlign,$defaultStyle,$attributes);
  return $pattern;
}

/**
 * Ends the section title
 */
function ui_end_title(){
  return '</span></div></div>';
}


/**
 * Create a jquery.ui icon
 * @param string $icon the icon ID. Ex: ('lightbulb, plusthick, ...')
 * @param string $properties Html properties
 */
function ui_create_icon($icon = 'notice', $properties = ''){
  if(is_array($properties)){
    $class = sprinf(' ui-icon ui-icon-%s ',$icon);
    $properties['class'] = (isset($properties['class'])) ? $class . $properties['class'] : $class ;
    $properties = arrayToString($properties);
    $pattern = '<span %s></span>';
  }else{
    $properties = ($properties === '') ? 'style="z-index:2000"' : $properties;
    $pattern = '<span class="ui-icon ui-icon-%s ui-datepicker-prev" %s></span>';
  }
  return sprintf($pattern,$icon, $properties);
}

/**
 * Create a input type button with jquery.ui styles
 * @param string $value The button value
 * @param string $id The buton Id
 * @param string $properties Html attributes and properties
 */
function ui_button($configuration){
  $corners = '';
  $title = '';
  $priority = '';
  $state = 'ui-state-default ';
  $stateHtml = '';
  $defaultStyle = (isset($configuration['show']) && !$configuration['show']) ? 'style="cursor:pointer; font-size:10px; display:none"': 'style="cursor:pointer;font-size:10px;"';
  if(isset($configuration['value']))
  {
    if(isset($configuration['noTitle']) && $configuration['noTitle']){
      $title = '';
    }else{
      $title = isset($configuration['title']) ? $configuration['title'] : $configuration['value'];
      $title = sprintf('title="%s"',$title);
    }
    $value = $configuration['value'];
  }else{
    $value =  '&nbsp;';
  }
  if(isset($configuration['corner'])){
    $corners = 'ui-corner-' .strtolower($configuration['corner']);
  }
  if(isset($configuration['priority'])){
    $priority = 'ui-priority-' .strtolower($configuration['priority']);
  }
  if(isset($configuration['state'])){
    $state .= 'ui-state-' .strtolower($configuration['state']);
    $stateHtml = sprintf(' disabled="%s"', $configuration['state']);
  }
  $defaultProperties =  sprintf('%s class="fg-button ui-button %s %s %s" %s', $title, $state, $priority ,$corners, $defaultStyle);
  $properties =  isset($configuration['properties']) ? $configuration['properties'] . $defaultProperties : $defaultProperties ;
  $id =  isset($configuration['id']) ? $configuration['id'] : '' ;
  $pattern = '<input type="button" id="%s" %s value="%s" />';
  return sprintf($pattern,$id,$properties . $stateHtml,$value);
}



/**
 * Create a link like a jquery.ui button
 * @param string $value The button value
 * @param string $id The buton Id
 * @param string $properties Html attributes and properties
 */
function ui_link_button($configuration){
  $corners = '';
  $title = isset($configuration['title']) ? $configuration['title'] : '';
  $defaultAlign = '';
  $priority = '';
  $href = (isset($configuration['url'])) ? sprintf(' href="%s" ',$configuration['url']) : '';
  $href .= (isset($configuration['target'])) ? sprintf(' target="%s" ',$configuration['target']) : '';
  $defaultStyle = (isset($configuration['show']) && !$configuration['show']) ? 'style="cursor:pointer; display:none"': 'style="cursor:pointer"';
  $uiIcon =  '';
  $stateHtml = '';
  $state = 'ui-state-default ';
  $id =  isset($configuration['id']) ? $configuration['id'] : '' ;
  if(isset($configuration['value']))
  {
    $title = $configuration['value'];
    $value = $configuration['value'];
  }else{
    $value =  '&nbsp;';
    $defaultAlign = 'fg-button-icon-solo';
  }
  if(isset($configuration['noTitle']) && $configuration['noTitle'] == true){
    $title = '';
  }else{
    if(isset($configuration['title'])){
      $title = $configuration['title'];
    }
  }
  if(isset($configuration['corner'])){
    $corners = 'ui-corner-' .strtolower($configuration['corner']);
  }
  if(isset($configuration['priority'])){
    $priority = 'ui-priority-' .strtolower($configuration['priority']);
  }
  if(isset($configuration['state'])){
    $href = '';
    $state .= 'ui-state-' .strtolower($configuration['state'] . ' ');
    if(strtolower($configuration['state']) == 'disabled'){
      echo add_jquery_support('#' . $id,'ready',like_function(jquery_unbind_event('#' . $id)));
      $stateHtml = sprintf(' disabled="%s"', $configuration['state']);
    }
  }
  if(isset($configuration['icon'])){
    if(isset($configuration['align']) && isset($configuration['value'])){
      $defaultAlign = 'fg-button-icon-' .strtolower($configuration['align']);
    }else{
      $defaultAlign = ($defaultAlign != '' ) ? $defaultAlign : 'fg-button-icon-left';
    }
    $uiIcon =  ui_create_icon($configuration['icon']);
  }
  $title = sprintf(' title="%s" ',$title);
  $defaultProperties =  sprintf('%s class="fg-button ui-button %s %s %s %s" %s %s', $title , $state, $priority ,$defaultAlign, $corners, $defaultStyle, $href . $stateHtml);
  $properties =  isset($configuration['properties']) ? $configuration['properties'] . $defaultProperties : $defaultProperties ;
  $pattern = '<a id="%s" %s >%s %s</a>';
  return sprintf($pattern,$id,$properties,$uiIcon,$value);
}

/**
 * Util function for add the jquery.ui themeroller. See the app.yml
 * @param string $id The themeroller <div> Id
 * @param array() $options Array. See the options and events in
 *                http://jqueryui.com/docs/Theming/ThemeSwitcher
 * @param string $properties Html attributes and properties
 * @return <type>
 */
function ui_theme_switcher_tool($id = 'themeroller', $options = array(), $htmlProperties= ''){
  $response = '';
  $themeRollerJs = '<script type="text/javascript" src="%s"></script>';
  $themeRollerUrl = get_prev_settings('ys_jquery_ui_theme_rolller','/ysJQueryUIPlugin/js/jquery/themeswitchertool.js');
  $themeRollerDiv = '<div id="%s" %s></div>';
  $themeRollerPattern = _ui_themeroller_pattern($options);
  $response .= add_jquery_support('#' . $id,'themeswitcher', $themeRollerPattern);
  $response .= sprintf($themeRollerJs,$themeRollerUrl);
  $response .= sprintf($themeRollerDiv,$id,$htmlProperties);
  return $response;
}

/**
 * All the buttons within a button pane will have the onhover effect
 * @param <type> $htmlProperties Html attributes and properties
 * @param <type> $tag The tag for the button pane
 * @return <type>
 */
function ui_button_pane_init($type, $buttons = array(), $htmlProperties = '', $tag = 'div'){
  $pattern = '<%s class="%s" %s>';
  if($type == 'single'){
    $defaultClass = 'fg-buttonset fg-buttonset-single ui-helper-clearfix';
  }elseif($type == 'multiple'){
    $defaultClass = 'fg-buttonset fg-buttonset-multi';
  }else{
    $defaultClass = 'fg-buttonset';
  }
  $defaultClass = isset($buttons['class']) ? $defaultClass . ' ' . $buttons['class'] : $defaultClass;

  $pattern = sprintf($pattern, $tag, $defaultClass, $htmlProperties);
  if (is_array($buttons)){
    $size = sizeof($buttons);
    $i = 0;
    foreach($buttons as $id => $button){
      $button['id'] = (isset($button['id'])) ? $button['id'] : $id;
      if((int) $size == 1 || $type == 'none'){
        $button['corner'] = (isset($button['corner'])) ? $button['corner'] : 'all';
      }
      if ($i == 0){
        $button['corner'] = (isset($button['corner'])) ? $button['corner'] : 'left';
      }
      if(++$i == $size){
        $button['corner'] = (isset($button['corner'])) ? $button['corner'] : 'right';
      }

      if((isset($button['buttonType']) && strtolower($button['buttonType']) == 'button')){
        $pattern .= ui_button($button);
      }else{
        $pattern .= ui_link_button($button);
      }
    }
  }
  return $pattern;
}

/**
 * Ends the button pane
 * @param string $tag The tag for the button pane
 */
function ui_button_pane_end($tag = 'div'){
  return sprintf('</%s>', $tag);
}

/**
 * Init the toolbar
 * @param string $isMax
 * @param string $htmlProperties Html Properties
 * @param string $tag The HTML tag
 */
function ui_toolbar_init($isMax = false, $htmlProperties = '', $tag = 'div'){
  if($isMax){
    $pattern = '<%s class="fg-toolbar ui-widget-header ui-state-default fg-buttonset ui-corner-all ui-helper-clearfix" %s >';
  }else{
    $pattern = '<%s class="fg-toolbar ui-widget-header ui-state-default ui-corner-all ui-helper-clearfix" %s >';
  }
  $pattern = sprintf($pattern, $tag ,$htmlProperties);
  return $pattern;
}

/**
 * Ends the toolbar
 * @param string $tag The HTML tag
 */
function ui_toolbar_end($tag = 'div'){
  return sprintf('</%s>',$tag);
}

/**
 * Animation for the button pane
 * @param string $selector jQuery Selector
 * @param string $class1 The 1st hover class
 * @param string $class2 The 2nd hover class
 */
function ui_core_hover_animation_to($selector, $class1 = 'ui-state-hover', $class2 = 'ui-state-active'){
  return add_jquery_support(
              "$selector",
              'hover',
              like_function(
                jquery_support('this','addClass',"'$class1'"))
              . ',' .
              like_function(
                jquery_support('this','removeClass',"'$class1'")))
        .
        add_jquery_support(
                    "$selector",
                    'mousedown',
                    like_function(jquery_support('this','addClass',"'$class2'")))
        .
        add_jquery_support(
                    "$selector",
                    'mouseup',
                    like_function(jquery_support('this','removeClass',"'$class2'")))
        .
        add_jquery_support(
                    "$selector",
                    'mouseout',
                    like_function(jquery_support('this','removeClass',"'$class2'")));
}

/**
 *
 * Disable a button
 * @param String $selector The jQuery Selector
 * @param boolean $isInternal
 */
function ui_disable_button($selector, $isInternal = true){
  if(!function_exists('ui_effects'))
    use_helper('ysJQueryUIEffects');
  $sintax = jquery_set($selector, 'attr', array('disabled' => 'disabled'), $unescapeId = true, $accesors = '', $addSeparator = true);
  return dry_ui_disable_button($sintax, $selector, $isInternal = true);
}

/**
 * Disable a link button
 * @param String $selector The jQuery Selector
 * @param boolean $all If true disable all events else disable only click event.
 * @param boolean $isInternal
 */
function ui_disable_link_button($selector, $all = false, $isInternal = true){
  if(!function_exists('ui_effects'))
    use_helper('ysJQueryUIEffects');
  if($all){
    $sintax =  jquery_unbind_event($selector);
  }else{
    $sintax =  jquery_unbind_event($selector, 'click');
  }
  return dry_ui_disable_button($sintax, $selector, $isInternal = true);
}

/**
 * Internal function for DRY.
 * @param String $sintax Initial sintax
 * @param String $selector The jQuery Selector
 * @param boolean $isInternal
 */
function dry_ui_disable_button($sintax = '', $selector = '', $isInternal = true){
  $sintax .=';';
  $sintax .= ui_effects_add_class($selector, array('class' => 'ui-state-disabled'));
  if($isInternal){
    return $sintax;
  }else{
    return add_jquery_support($selector, 'ready', like_function($sintax));
  }
}

/**
 * Enable a link_button or button
 * @param String $selector The jQuery Selector
 * @param String $funtion The function to bind. Needed to enable a link_button
 * @param boolean $isInternal
 */
function ui_enable_button($selector, $funtion = '', $isInternal = true){
  if(!function_exists('ui_effects'))
    use_helper('ysJQueryUIEffects');
  $sintax = '';
  $sintax = jquery_set($selector, 'attr', array('disabled' => ''), $unescapeId = true, $accesors = '', $addSeparator = true);
  if($funtion != ''){
    $sintax =  jquery_bind_event($selector, 'click', $funtion);
  }
  $sintax .= ui_effects_remove_class($selector, array('class' => 'ui-state-disabled'));
  $sintax .= ui_effects_add_class($selector, array('class' => 'ui-state-default'));
  if($isInternal){
    return $sintax;
  }else{
    return add_jquery_support($selector, 'ready', like_function($sintax));
  }
}

/**
 * Enable a link_button or button
 * @param String $selector The jQuery Selector
 * @param String $funtion The function to bind. Needed to enable a link_button
 * @param boolean $isInternal
 */
function ui_enable_link_button($selector, $funtion = '', $isInternal = true){
  return ui_enable_button($selector, $funtion, $isInternal);
}

/**
 * Disable to select a section
 * @param string $selector jQuery Selector
 */
function ui_disable_selection($selector, $isInternal = false){
    if($isInternal){
      return jquery_support($selector, 'disableSelection');
    }else{
      return add_jquery_support($selector, 'disableSelection');
    }
}

/**
 * Init the grid
 * @param string $title The drid title
 */
function ui_grid_init($title = ''){
  $pattern = '<div class="ui-grid ui-widget ui-widget-content ui-corner-all">
                <div class="ui-grid-header ui-widget-header ui-corner-top">%s</div>
                  <table class="ui-table ui-grid-content ui-widget-content">';
  echo sprintf($pattern,$title);
}

/**
 * Init the header
 * @param string $htmlProperties the HTML properties
 */
function ui_grid_head_init($htmlProperties = ''){
  $pattern = '<thead>
                <tr %s>';
  echo sprintf($pattern,$htmlProperties);
}

/**
 * Init a column header
 * @param string The header column value
 * @param string $htmlProperties the HTML properties
 */
function ui_grid_th($value,$htmlProperties = ''){
  $pattern = '<th class="ui-state-default" style="text-align:center" %s>%s</th>';
  echo sprintf($pattern,$htmlProperties,$value);
}

/**
 * End the column header
 */
function ui_grid_head_end(){
  echo $pattern = '  </tr>
                  </thead>';
}

/**
 * Init the body
 * @param string $htmlProperties the HTML properties
 */
function ui_grid_body_init($htmlProperties = ''){
  $pattern = '<tbody %s>';
  echo sprintf($pattern,$htmlProperties);
}

/**
 * Init a row
 * @param string $htmlProperties the HTML properties
 */
function ui_grid_tr_init($htmlProperties = ''){
  $pattern = '<tr %s>';
  echo sprintf($pattern,$htmlProperties);
}

/**
 * Ends the row
 */
function ui_grid_tr_end(){
	echo $pattern = '</tr>';
}

/**
 * Init a column
 * @param string $htmlProperties the HTML properties
 */
function ui_grid_td_init($htmlProperties = ''){
  $pattern = '<td class="ui-widget-content" %s>';
  echo sprintf($pattern,$htmlProperties);
}

/**
 * Ends the column
 */
function ui_grid_td_end(){
	echo $pattern = '</td>';
}

/**
 * Ends the body
 */
function ui_grid_body_end(){
	echo $pattern = '  </tbody>
                  </table>';
}

/**
 * Init the footer
 * @param string $htmlProperties the HTML properties
 */
function ui_grid_foot_init($htmlProperties = ''){
  $pattern = '<div class="ui-grid-footer ui-widget-header ui-corner-bottom ui-helper-clearfix" %s>';
  echo sprintf($pattern,$htmlProperties);
}

/**
 * Ends the footer
 */
function ui_grid_foot_end(){
  echo $pattern = '</div>';
}

/**
 * Ends the grid
 */
function ui_grid_end(){
  echo $pattern = '</div>';
}

/**
 * Internal function
 * @param array() $configuration Configuration array
 * @return string JSON Array.
 * @see ui_themeroller()
 */
function _ui_themeroller_pattern($configuration){
  $pattern = '';
  if(is_array($configuration) && sizeof($configuration) > 0){
    $pattern = '{';

    //OPTIONS SUPPORT
    if(isset($configuration['loadTheme'])){     $pattern .= toJQueryOption('loadTheme', $configuration['loadTheme']); }
    if(isset($configuration['height'])){        $pattern .= toJQueryOption('height', $configuration['height']); }
    if(isset($configuration['width'])){         $pattern .= toJQueryOption('width', $configuration['width']); }
    if(isset($configuration['initialText'])){   $pattern .= toJQueryOption('initialText', $configuration['initialText']); }
    if(isset($configuration['buttonPreText'])){ $pattern .= toJQueryOption('buttonPreText', $configuration['buttonPreText']); }
    if(isset($configuration['closeOnSelect'])){ $pattern .= toJQueryOption('closeOnSelect', $configuration['closeOnSelect']); }
    if(isset($configuration['buttonHeight'])){  $pattern .= toJQueryOption('buttonHeight', $configuration['buttonHeight']); }
    if(isset($configuration['cookieName'])){    $pattern .= toJQueryOption('cookieName', $configuration['cookieName']); }

    //EVENTS SUPPORT
    if(isset($configuration['onOpen'])){        $pattern .= toJQueryOption('onOpen', $configuration['onOpen'], true); }
    if(isset($configuration['onClose'])){       $pattern .= toJQueryOption('onClose', $configuration['onClose'], true); }
    if(isset($configuration['onSelect'])){      $pattern .= toJQueryOption('onSelect', $configuration['onSelect'], true); }

    if($pattern != '{')
      $pattern = substr($pattern,0,(strlen($pattern)) - 1);
    $pattern .= '}';
  }
  return $pattern;
}

/**
 * .css
 * @return <type> jquery.layout Directory Configuration files. See app.yml
 */

function get_ui_extra_widgets_css_path($id, $default){
    //due to Symfony 2
    switch ($id) {
        case 'ui_layout':
            $ui_css_dir = '/ysJQueryUIPlugin/css';
            break;

        default:
            $ui_css_dir = get_prev_settings(sprintf('app_ys_jquery_%s_css_dir', $id), $default);
    }

  $path = $ui_css_dir . '/';
  return $path;
}

/**
 * .js
 * @return <type> jquery.layout Directory Configuration files. See app.yml
 */
function get_ui_extra_widgets_js_path($id, $default){
    //due to Symfony 2
    switch ($id) {
        case 'ui_layout':
            $ui_js_dir = '/ysJQueryUIPlugin/js/jquery/layout';
            break;

        default:
            $ui_js_dir = get_prev_settings(sprintf('app_ys_jquery_%s_js_dir', $id), $default);
    }

  $path = $ui_js_dir . '/';
  return $path;
}

/**
 *
 * @param <type> $id
 * @param <type> $defaultPath
 * @param <type> $type
 */
function set_ui_extra_widgets_configuration_files($id,$defaultPath, $type = null){

    //due to Symfony 2
  switch ($id) {
      case 'ui_layout':
          $ui_css = array();
          $ui_js = array (
              0 => 'jquery.layout.min.js',
              1 => 'jquery.layout.state.js',
              2 => 'json2.js',
          );
          break;

      default:

          $ui_css = get_prev_settings(sprintf('app_ys_jquery_%s_css', $id), array());
          $ui_js = get_prev_settings(sprintf('app_ys_jquery_%s_js', $id), array());
  }




  $css_path = get_ui_extra_widgets_css_path($id, $defaultPath['css']);
  $js_path  = get_ui_extra_widgets_js_path($id, $defaultPath['js']);
  $type = strtolower(trim($type));
  switch ($type) {
    case 'css':
      foreach($ui_css as $style){
        add_css_configuration_file($style, 'last' , $css_path);
      }
      break;
    case 'js':
      foreach($ui_js as $js){
        add_js_configuration_file($js, 'last' , $js_path);
      }
      break;
    default:
      foreach($ui_css as $style){
        add_css_configuration_file($style, 'last' , $css_path);
      }
      foreach($ui_js as $js){
        add_js_configuration_file($js, 'last' , $js_path);
      }
      break;
  }
}

/**
 * Merges the widgets default options defined in the app.yml file with the array $configuration.
 * @param <type> $index
 * @param <type> $configuration
 * @return <type>
 */
function get_default_widget_configuration($index, $configuration= array()){
  $defaultConfiguration = get_prev_settings($index, 'error');
  if(is_array($configuration) && is_array($defaultConfiguration)){
    $defaultConfiguration = array_merge($defaultConfiguration, $configuration);
  }else{
    $defaultConfiguration = (is_array($configuration)) ? $configuration : $defaultConfiguration;
  }
  return $defaultConfiguration;
}
/*
 || END UI.CORE
 */