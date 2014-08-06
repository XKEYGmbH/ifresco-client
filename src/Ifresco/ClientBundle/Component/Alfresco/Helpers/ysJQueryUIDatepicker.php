<?php
/*
 || UI.DATEPICKER
 */

/*
 * Initial functions
 * Starts adding the helper ysJQueryUICore core files jquery.ui
 * Call set_datepicker_configuration_files function ();
 */
if(!function_exists('ui_core_exist')){
  use_helper('ysJQueryUICore');
}

set_datepicker_configuration_files();

/**
 *
 * Add the context all the files needed to enable basic
 * ui.datepicker and can be run the widget. View settings.yml
 * @param string $type default value: null; if $type = 'CSS' Add only stylesheets
 *        if $type = 'JS' Add only javascripts files .
 */
function set_datepicker_configuration_files($type = null){
  _set_ui_i18n_file(sfConfig::get('app_ys_jquery_ui_datepicker_i18n_file', 'ui.datepicker-en.js'));
  set_ui_common_configuration_files('datepicker', $type);
}

/**
 * Starts the datepicker and its configuration
 * @param string $selector jQuery Selector
 * @param array() $configurations Array. See the options and events in
 *        http://jqueryui.com/demos/datepicker/#options
 *        http://jqueryui.com/demos/datepicker/#events
 * @param string $html_attributes Additional options parameter (html attributes)
 */
function ui_datepicker_create($selector , $configurations = array() , $html_attributes = null){
    $configurations = get_default_widget_configuration('app_ys_jquery_ui_datepicker_defaults', $configurations);
    $pattern = _ui_datepicker_pattern($configurations);
    init_ui_widget('datepicker',$pattern,$selector ,$configurations);
    if(isset($configurations['inLine']) && $configurations['inLine'] == true){
      echo '<div id="' . $selector . '" '. $html_attributes .'></div>';
    }else{
      echo '<input type="text" id="' . $selector . '" '. $html_attributes .'>';
    }
    if(is_array($configurations) && isset($configurations['i18n'])){
      echo ui_datepicker_regional('#'. $selector, $configurations['i18n'], false);
    }
}

/**
 * Set settings for all datepicker instances
 * @param array() $defaults The default values. see app.yml
 * ys_jquery_ui_datepicker_defaults index
 * @param boolean $isInternal if the helper is executed within a javascript function
 */
function ui_datepicker_set_defaults($defaults = array(), $isInternal = false){
  $pattern = '';
  $addDefaults = false;
  if(is_array($defaults) && sizeof($defaults) > 0){
    $pattern = _ui_datepicker_pattern($defaults);
    $addDefaults = true;
  }
  $appDefaults = sfConfig::get('app_ys_jquery_ui_datepicker_defaults', array());
  if(is_array($appDefaults) && sizeof($appDefaults) > 0){
    $appDefaults = array_merge($defaults,$appDefaults);
    $pattern = _ui_datepicker_pattern($appDefaults);
    $addDefaults = true;
  }
  if($addDefaults){
    if($isInternal){
      return jquery_support(null,'datepicker.setDefaults', $pattern);
    }else{
      return add_jquery_support('document','ready', like_function(jquery_support(null,'datepicker.setDefaults', $pattern)));
    }
  }
}

/**
 * Localize the datepicker calendar language and format
 * (English / Western formatting is the default).
 * The datepicker includes built-in support for languages that read right-to-left,
 * such as Arabic and Hebrew.
 * @param string $selector jQuery Selector
 * @param string $i18n The language ('es', 'en', 'fr' ...)
 * @param boolean $isInternal if the helper is executed within a javascript function
 * @return <type> 
 */
function ui_datepicker_regional($selector,$i18n, $isInternal = true){
  $pattern = sprintf(core_get_jquery_var() . ".datepicker.regional['%s']" , $i18n);
  if($isInternal){
    return jquery_support($selector,'datepicker', "'option' , $pattern");
  }else{
    return add_jquery_support($selector,'datepicker', "'option' , $pattern");
  }
}

/**
 * Change the internacionaltitation file for jquery ui.
 * @param string $file The file name.
 */
function ui_datepicker_change_i18n($file){
   _set_ui_i18n_file($file);
}

/**
 * Remove the datepicker functionality completely.
 * This will return the element back to its pre-init state.
 * @param string $selector jQuery Selector
 */
function ui_datepicker_destroy($selector){
  return jquery_support($selector,'datepicker', "'destroy'");
}

/**
 * Build the datepicker and its configuration programmatically.
 * @param string $selector jQuery Selector
 * @param array() $configurations Array. See the options and events in
 *        http://jqueryui.com/demos/datepicker/#options
 *        http://jqueryui.com/demos/datepicker/#events
 */
function ui_datepicker_build($selector, $configurations = array()){
  $configurations = get_default_widget_configuration('app_ys_jquery_ui_datepicker_defaults', $configurations);
  $pattern = _ui_datepicker_pattern($configurations);
  return jquery_support($selector,'datepicker', $pattern);
}

/**
 * Enable the datepicker
 * @param string $selector jQuery Selector
 */
function ui_datepicker_enable($selector){
  return jquery_support($selector,'datepicker', "'enable'");
}

/**
 * Disable the datepicker
 * @param string $selector jQuery Selector
 */
function ui_datepicker_disable($selector){
  return jquery_support($selector,'datepicker', "'disable'");
}

/**
 * Open a datepicker in a "dialog" box
 * @param string $selector jQuery Selector
 * @param string $dateText The initial date for the date picker
 * @param string $onSelect A callback function when a date is selected
 * @param array() $settings The new settings for the date picker
 * @param array() $pos The position of the top/left of the dialog as [x, y]
 *                     or a MouseEvent that contains the coordinates
 */
function ui_datepicker_dialog($selector,$dateText,$onSelect = null,$settings = array(), $pos = array()){
  $pattern =  "'dialog' , '$dateText'";
  if(!is_null($onSelect) && trim($onSelect) !== ''){
    $pattern.=  ", $onSelect";
  }elseif(is_array($settings) && sizeof($settings) > 0){
    $datepickerPattern = _ui_datepicker_pattern($settings);
    $pattern.=  "null, $datepickerPattern";
  }elseif(is_array($pos) && sizeof($pos) > 0){
    $position = json_encode($pos);
    $pattern.=  ",null, {}, $position";
  }

  return jquery_support($selector,'datepicker', $pattern);
}

/**
 * Determine whether a date picker has been disabled
 * @param string $selector jQuery Selector
 */
function ui_datepicker_is_disabled($selector){
  return jquery_support($selector,'datepicker', "'isDisabled'");
}

/**
 * Close a previously opened date picker
 * @param string $selector jQuery Selector
 * @param string $speed The speed at which to close the date picker
 */
function ui_datepicker_hide($selector, $speed = 0){
  if(is_numeric($speed)){
    return jquery_support($selector,'datepicker', "'hide', $speed");
  }else
  {
    return jquery_support($selector,'datepicker', "'hide', '$speed'");
  }
}

/**
 * Call up a previously attached date picker
 * @param string $selector jQuery Selector
 */
function ui_datepicker_show($selector){
  return jquery_support($selector,'datepicker', "'show'");
}

/**
 * Returns the current date for the datepicker
 * @param string $selector jQuery Selector
 */
function ui_datepicker_get_date($selector){
  return jquery_support($selector,'datepicker', "'getDate'");
}

/**
 * Sets the current date for the datepicker. The new date may also be a number
 * of days from today (e.g. +7) or a string of values and periods
 * ('y' for years, 'm' for months, 'w' for weeks, 'd' for days, e.g. '+1m +7d'),
 * or null for today.
 * @param string $selector jQuery Selector
 * @param string $date The new date.
 */
function ui_datepicker_set_date($selector, $date){
  if(is_numeric($date)){
     return jquery_support($selector,'datepicker', "'setDate' , $date");
  }else{
     return jquery_support($selector,'datepicker', "'setDate' , '$date'");
  }
}

/**
 * Get any datepicker option.
 * @param string $selector jQuery Selector
 * @param string $option The widget option to get
 */
function ui_datepicker_get_option($selector,$option){
  return jquery_support($selector,'datepicker', "'option' , '$option'");
}

/**
 * Set any datepicker option.
 * @param string $selector jQuery Selector
 * @param string $option_map Options array to set
 */
function ui_datepicker_set_options($selector,$option_map){
  $pattern = _ui_datepicker_pattern($option_map);
  return ui_set_widget_options('datepicker', $selector,$pattern);
}

/**
 * Internal function
 * @param array() $configuration Configuration array
 * @return string JSON Array.
 * @see ui_accordion_init()
 */
function _ui_datepicker_pattern($configuration){
  $pattern = '';
  if(is_array($configuration) && sizeof($configuration) > 0){
    $pattern = '{';

    //OPTIONS SUPPORT
    if(isset($configuration['altField'])){                $pattern .= toJQueryOption('altField', $configuration['altField']); }
    if(isset($configuration['altFormat'])){               $pattern .= toJQueryOption('altFormat', $configuration['altFormat']); }
    if(isset($configuration['appendText'])){              $pattern .= toJQueryOption('appendText', $configuration['appendText']); }
    if(isset($configuration['buttonImage'])){             $pattern .= toJQueryOption('buttonImage', $configuration['buttonImage']); }
    if(isset($configuration['buttonImageOnly'])){         $pattern .= toJQueryOption('buttonImageOnly', $configuration['buttonImageOnly']); }
    if(isset($configuration['buttonText'])){              $pattern .= toJQueryOption('buttonText', $configuration['buttonText']); }
    if(isset($configuration['changeMonth'])){             $pattern .= toJQueryOption('changeMonth', $configuration['changeMonth']); }
    if(isset($configuration['changeYear'])){              $pattern .= toJQueryOption('changeYear', $configuration['changeYear']); }
    if(isset($configuration['closeText'])){               $pattern .= toJQueryOption('closeText', $configuration['closeText']); }
    if(isset($configuration['constrainInput'])){          $pattern .= toJQueryOption('constrainInput', $configuration['constrainInput']); }
    if(isset($configuration['currentText'])){             $pattern .= toJQueryOption('currentText', $configuration['currentText']); }
    if(isset($configuration['dateFormat'])){              $pattern .= toJQueryOption('dateFormat', $configuration['dateFormat']); }
    if(isset($configuration['dayNames'])){                $pattern .= toJQueryOption('dayNames', $configuration['dayNames']); }
    if(isset($configuration['dayNamesMin'])){             $pattern .= toJQueryOption('dayNamesMin', $configuration['dayNamesMin']); }
    if(isset($configuration['dayNamesShort'])){           $pattern .= toJQueryOption('dayNamesShort', $configuration['dayNamesShort']); }
    if(isset($configuration['defaultDate'])){             $pattern .= toJQueryOption('defaultDate', $configuration['defaultDate']); }
    if(isset($configuration['firstDay'])){                $pattern .= toJQueryOption('firstDay', $configuration['firstDay']); }
    if(isset($configuration['gotoCurrent'])){             $pattern .= toJQueryOption('gotoCurrent', $configuration['gotoCurrent']); }
    if(isset($configuration['hideIfNoPrevNext'])){        $pattern .= toJQueryOption('hideIfNoPrevNext', $configuration['hideIfNoPrevNext']); }
    if(isset($configuration['isRTL'])){                   $pattern .= toJQueryOption('isRTL', $configuration['isRTL']); }
    if(isset($configuration['maxDate'])){                 $pattern .= toJQueryOption('maxDate', $configuration['maxDate']); }
    if(isset($configuration['minDate'])){                 $pattern .= toJQueryOption('minDate', $configuration['minDate']); }
    if(isset($configuration['monthNames'])){              $pattern .= toJQueryOption('monthNames', $configuration['monthNames']); }
    if(isset($configuration['monthNamesShort'])){         $pattern .= toJQueryOption('monthNamesShort', $configuration['monthNamesShort']); }
    if(isset($configuration['navigationAsDateFormat'])){  $pattern .= toJQueryOption('navigationAsDateFormat', $configuration['navigationAsDateFormat']); }
    if(isset($configuration['nextText'])){                $pattern .= toJQueryOption('nextText', $configuration['nextText']); }
    if(isset($configuration['numberOfMonths'])){          $pattern .= toJQueryOption('numberOfMonths', $configuration['numberOfMonths']); }
    if(isset($configuration['prevText'])){                $pattern .= toJQueryOption('prevText', $configuration['prevText']); }
    if(isset($configuration['shortYearCutoff'])){         $pattern .= toJQueryOption('shortYearCutoff', $configuration['shortYearCutoff']); }
    if(isset($configuration['showAnim'])){
      $pattern .= toJQueryOption('showAnim', $configuration['showAnim']);
      ui_add_effects_support($configuration['showAnim']);
      }
    if(isset($configuration['showButtonPanel'])){         $pattern .= toJQueryOption('showButtonPanel', $configuration['showButtonPanel']); }
    if(isset($configuration['showCurrentAtPos'])){        $pattern .= toJQueryOption('showCurrentAtPos', $configuration['showCurrentAtPos']); }
    if(isset($configuration['showMonthAfterYear'])){      $pattern .= toJQueryOption('showMonthAfterYear', $configuration['showMonthAfterYear']); }
    if(isset($configuration['showOn'])){                  $pattern .= toJQueryOption('showOn', $configuration['showOn']); }
    if(isset($configuration['showOptions'])){             $pattern .= toJQueryOption('showOptions', $configuration['showOptions']); }
    if(isset($configuration['showOtherMonths'])){         $pattern .= toJQueryOption('showOtherMonths', $configuration['showOtherMonths']); }
    if(isset($configuration['stepMonths'])){              $pattern .= toJQueryOption('stepMonths', $configuration['stepMonths']); }
    if(isset($configuration['yearRange'])){               $pattern .= toJQueryOption('yearRange', $configuration['yearRange']); }
    //EVENTS SUPPORT
    if(isset($configuration['beforeShow'])){              $pattern .= toJQueryOption('beforeShow', $configuration['beforeShow']); }
    if(isset($configuration['beforeShowDay'])){           $pattern .= toJQueryOption('beforeShowDay', $configuration['beforeShowDay']); }
    if(isset($configuration['onChangeMonthYear'])){       $pattern .= toJQueryOption('onChangeMonthYear', $configuration['onChangeMonthYear']); }
    if(isset($configuration['onClose'])){                 $pattern .= toJQueryOption('onClose', $configuration['onClose']); }
    if(isset($configuration['beforeShow'])){              $pattern .= toJQueryOption('beforeShow', $configuration['beforeShow']); }
    if(isset($configuration['onSelect'])){                $pattern .= toJQueryOption('onSelect', $configuration['onSelect']); }
    
    if($pattern != '{'){
      $pattern = substr($pattern,0,(strlen($pattern)) - 1);
    }
    $pattern .= '}';
  }
  return $pattern;
}

/*
 || END UI.DATEPICKER
 */
