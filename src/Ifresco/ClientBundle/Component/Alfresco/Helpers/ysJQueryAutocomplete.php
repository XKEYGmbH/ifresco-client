<?php
namespace Ifresco\ClientBundle\Component\Alfresco\Helpers;
/*
 || JQUERY.PLUGIN.AUTOCOMPLETE
 */

if(!function_exists('ys_util_exist')){
  use_helper('ysUtil');
}                

set_autocomlete_configuration_files();

/**
 *
 * Add the context all the files needed to enable basic
 * ui.accordion and can be run the widget. View settings.yml
 * @param string $type default value: null; if $type = 'CSS' Add only stylesheets
 *        if $type = 'JS' Add only javascripts files .
 */
function set_autocomlete_configuration_files($type = null){
  set_jquery_plugins_configuration_files('auto_complete');
}

/**
 * Creates a input tag with autocomplete support
 * @param array $configuration Array options for the autocomplete function
 */
function jquery_autocomplete_input_tag($configuration){
  $pattern = '';
  $value = (isset($configuration['value'])) ? $configuration['value'] : '';
  $id = (isset($configuration['id'])) ? $configuration['id'] : time();
  $name = (isset($configuration['name'])) ? $configuration['name'] : $id;
  $attributes = (isset($configuration['attributes'])) ? $configuration['attributes'] : '';
  $pattern = (isset($configuration['label'])) ? sprintf('<label for="%s"> %s </label>', $id ,$configuration['label']) : $pattern;
  $pattern .= sprintf('<input type="text" value"%s" id="%s" name="%s" %s />', $value, $id , $name, $attributes);
  return $pattern . jquery_autocomplete_support_to('#' . $id, $configuration);
}

/**
 * Adds autocomplete support
 * @param string $selector A jQuery Selector
 * @param array $configuration Array options for the autocomplete function
 * @param boolean $isInternal If this function is inner a javascript body
 */
function jquery_autocomplete_support_to($selector, $configuration = array(), $isInternal= false){
  if(isset($configuration['focus']) && $configuration['focus'] == true){
    echo jquery_execute(jquery_support($selector, 'focus'));
  }
  $configuration = merge_plugin_defaults_options('auto_complete', $configuration);
  $urlOrData= '{}';
  $urlOrData = (isset($configuration['data'])) ? $configuration['data'] : $urlOrData;
  $urlOrData = (isset($configuration['url'])) ? sprintf("'%s'",$configuration['url']) : $urlOrData;
  //unset($configuration['data'],$configuration['url']);
  $pattern = _ui_autocomplete_pattern($configuration);
  $jquerySintax = (trim($pattern) == '') ? $urlOrData : $urlOrData . ',' . $pattern;
  if($isInternal){
    return jquery_support($selector,'autocomplete',$jquerySintax);
  }else{
    return add_jquery_support($selector,'autocomplete',$jquerySintax);
  }
}

/**
 * Destroy the autocomplete
 * @param string $selector A jQuery Selector
 */
function jquery_unautocomplete($selector){
  return jquery_support($selector,'unautocomplete', '');
}

/**
 * Trigger a search event.
 * @param string $selector A jQuery Selector
 */
function jquery_autocomplete_search($selector){
  return jquery_support($selector,'search', '');
}

/**
 * Handle the result of a search event.
 * @param string $selector A jQuery Selector
 * @param string $function The function to execute. Use the like_function() helper
 */
function jquery_autocomplete_result($selector, $function){
  return jquery_support($selector,'search', $function);
}

/**
 * Updates the options for the current autocomplete field.
 * @param string $selector A jQuery Selector
 * @param array $configuration Array options for the autocomplete function
 */
function jquery_autocomplete_set_options($selector, $configuration){
  $pattern = _ui_autocomplete_pattern($configuration);
  return jquery_support($selector,'setOptions', $pattern);
}

/**
 * Flush (empty) the cache of matched input's autocompleters.
 * @param string $selector A jQuery Selector
 */
function jquery_autocomplete_flush_cache($selector){
  return jquery_support($selector,'flushCache', '');
}

/**
 * Internal function
 * @param array() $configuration Configuration array
 * @return string JSON Array.
 * @see jquery_autocomplete_support_to()
 */
function _ui_autocomplete_pattern($configuration){
  $pattern = '';
  if(is_array($configuration) && sizeof($configuration) > 0){
    $pattern = '{';

    //OPTIONS SUPPORT http://docs.jquery.com/Plugins/Autocomplete/autocomplete#url_or_dataoptions
    if(isset($configuration['focus'])){              $pattern .= toJQueryOption('focus', $configuration['focus']); }
    if(isset($configuration['inputClass'])){         $pattern .= toJQueryOption('inputClass', $configuration['inputClass']); }
    if(isset($configuration['resultsClass'])){       $pattern .= toJQueryOption('resultsClass', $configuration['resultsClass']); }
    if(isset($configuration['loadingClass'])){       $pattern .= toJQueryOption('loadingClass', $configuration['loadingClass']); }
    if(isset($configuration['oddClass'])){           $pattern .= toJQueryOption('oddClass', $configuration['oddClass']); }
    if(isset($configuration['evenClass'])){          $pattern .= toJQueryOption('evenClass', $configuration['evenClass']); }
    if(isset($configuration['overClass'])){          $pattern .= toJQueryOption('overClass', $configuration['overClass']); }
    if(isset($configuration['minChars'])){           $pattern .= toJQueryOption('minChars', $configuration['minChars']); }
    if(isset($configuration['delay'])){              $pattern .= toJQueryOption('delay', $configuration['delay']); }
    if(isset($configuration['matchCase'])){          $pattern .= toJQueryOption('matchCase', $configuration['matchCase']); }
    if(isset($configuration['matchSubset'])){        $pattern .= toJQueryOption('matchSubset', $configuration['matchSubset']); }
    if(isset($configuration['matchContains'])){      $pattern .= toJQueryOption('matchContains', $configuration['matchContains']); }
    if(isset($configuration['cacheLength'])){        $pattern .= toJQueryOption('cacheLength', $configuration['cacheLength']); }
    if(isset($configuration['max'])){                $pattern .= toJQueryOption('max', $configuration['max']); }
    if(isset($configuration['mustMatch'])){          $pattern .= toJQueryOption('mustMatch', $configuration['mustMatch']); }
    if(isset($configuration['extraParams'])){        $pattern .= toJQueryOption('extraParams', $configuration['extraParams']); }
    if(isset($configuration['selectFirst'])){        $pattern .= toJQueryOption('selectFirst', $configuration['selectFirst']); }
    if(isset($configuration['autoFill'])){           $pattern .= toJQueryOption('autoFill', $configuration['autoFill']); }
    if(isset($configuration['width'])){              $pattern .= toJQueryOption('width', $configuration['width']); }
    if(isset($configuration['multiple'])){           $pattern .= toJQueryOption('multiple', $configuration['multiple']); }
    if(isset($configuration['multipleSeparator'])){  $pattern .= toJQueryOption('multipleSeparator', $configuration['multipleSeparator']); }
    if(isset($configuration['scroll'])){             $pattern .= toJQueryOption('scroll', $configuration['scroll']); }
    if(isset($configuration['scrollHeight'])){       $pattern .= toJQueryOption('scrollHeight', $configuration['scrollHeight']); }
    if(isset($configuration['highlight'])){          $pattern .= toJQueryOption('highlight', $configuration['highlight']); }
    if(isset($configuration['dataType'])){           $pattern .= toJQueryOption('dataType', $configuration['dataType']); }
    if(isset($configuration['data'])){               $pattern .= toJQueryOption('data', $configuration['data'], true); }

    if(isset($configuration['formatItem'])){        $pattern .= toJQueryOption('formatItem', $configuration['formatItem'], true); }
    if(isset($configuration['formatMatch'])){       $pattern .= toJQueryOption('formatMatch', $configuration['formatMatch'], true); }
    if(isset($configuration['formatResult'])){      $pattern .= toJQueryOption('formatResult', $configuration['formatResult'], true); }
    if(isset($configuration['highlightFunction'])){ $pattern .= toJQueryOption('highlight', $configuration['highlightFunction'], true); }
    if($pattern != '{')
      $pattern = substr($pattern,0,(strlen($pattern)) - 1);
    $pattern .= '}';
  }
  return $pattern;
}
/*
 || END JQUERY.PLUGIN.AUTOCOMPLETE
 */