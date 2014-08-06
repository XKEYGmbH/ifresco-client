<?php
namespace Ifresco\ClientBundle\Component\Alfresco\Helpers;
/**
* Symfony 1.2.8
* converts the given PHP boolean to the corresponding javascript boolean.
* booleans need to be true or false (php would print 1 or nothing).
*
* @param bool (typically from option array)
* @return string javascript boolean equivalent
*/
if (!function_exists('boolean_for_javascript')){    
    function boolean_for_javascript($bool)
      {
        if (is_bool($bool))
        {
          return ($bool===true ? 'true' : 'false');
        }
        return $bool;
      }
}


if (!function_exists('json_encode'))
{
  /**
   * http://www.php.net/manual/en/function.json-encode.php#82904
   */
  function json_encode($a=false)
  {
    if (is_null($a)) return 'null';
    if ($a === false) return 'false';
    if ($a === true) return 'true';
    if (is_scalar($a))
    {
      if (is_float($a))
      {
        // Always use "." for floats.
        return floatval(str_replace(",", ".", strval($a)));
      }

      if (is_string($a))
      {
        static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
        return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
      }
      else
        return $a;
    }
    $isList = true;
    for ($i = 0, reset($a); $i < count($a); $i++, next($a))
    {
      if (key($a) !== $i)
      {
        $isList = false;
        break;
      }
    }
    $result = array();
    if ($isList)
    {
      foreach ($a as $v) $result[] = json_encode($v);
      return '[' . join(',', $result) . ']';
    }
    else
    {
      foreach ($a as $k => $v) $result[] = json_encode($k).':'.json_encode($v);
      return '{' . join(',', $result) . '}';
    }
  }
}

/**
 *
 * @param string $id The plugin id / key in the app.yml
 * @param string $type JS for js files / CSS for css files
 */
function set_jquery_plugins_configuration_files($id, $type = ''){
  $jqueryPlugins = get_prev_settings('app_ys_jquery_plugins', null);
  $jqueryPluginInfo = $jqueryPlugins[$id];
  if($jqueryPluginInfo != null){
    $jsDir = '';
    $cssDir = '';
    if($jqueryPluginInfo['local']){
      $jsDir =  get_prev_settings('app_ys_jquery_plugins_folder', null) . '/';
      $cssDir =  get_prev_settings('app_ys_jquery_css', null) . '/';
    }
    if($type == '' || strtolower($type) == 'js'){
      if(isset($jqueryPluginInfo['js_files']) && is_array($jqueryPluginInfo['js_files'])){
        foreach($jqueryPluginInfo['js_files'] as $jsFile){
          add_js($jsFile, $jsDir, 'last');
        }
      }
    }
    if($type == '' || strtolower($type) == 'css'){
      if(isset($jqueryPluginInfo['css_files']) && is_array($jqueryPluginInfo['css_files'])){
        foreach($jqueryPluginInfo['css_files'] as $cssFile){
          add_css($cssFile, $cssDir, 'last');
        }
      }
    }
  }
  return false;
}

/**
 * Merges the plugin default options defined in the app.yml file with the array $configuration.
 * @param string $id The plugin id / key in the app.yml
 * @param array $configuration
 */
function merge_plugin_defaults_options($id, $configuration = array()){
  $pluginInfo = get_prev_settings('app_ys_jquery_plugins', null);
  $pluginInfo = (isset($pluginInfo[$id])) ? $pluginInfo[$id] : array();
  $pluginDefaults = (isset($pluginInfo['default_options'])) ? $pluginInfo['default_options'] : array();
  if(is_array($pluginDefaults) && sizeof($pluginDefaults) > 0){
    $configuration = array_merge($configuration,$pluginDefaults);
  }
  return $configuration;
}

/**
 * Parse a php array to html a html list ('<ul><li>value</li></ul>')
 * @param array $items The array to parse.
 * @param string $formatter The formatter for the list value
 */
function array_to_html_list($items=array(), $formatter = 'default_list_formater', $configuration = array()){
  $response = '';
  $ulProperties = (isset($configuration['ul_properties'])) ? $configuration['ul_properties'] : '';
  $response .= sprintf('<ul %s>', $ulProperties)."\n";
  foreach ($items as $item){
    if (isset($item['credentials'])){
      $display = (sfContext::getInstance()->getUser()->hasCredential($item['credentials'])) ? true : false;
      if(isset($item['only_disable']) && $item['only_disable'] && !$display){
          $item['disabled'] = true;
          $display = true;
      }
    }else{
       $display = true;
    }
    $display = (isset($item['display'])) ? (Boolean) $item['display'] : $display;
    if($display){
      if(isset($item['disabled']) && $item['disabled'] == true){
        unset($item['items']);
      }
      if(isset($item['items'])){
        $response .= '<li>'. $formatter($item) . to_list($item, $formatter) . '</li>'."\n";
      }else{
        $response .= '<li>'. $formatter($item) . '</li>'."\n";
      }
    }
  }
  $response .= '</ul>'."\n";
  return $response;
}

/**
 * Recursive function
 * @param array $items The array to parse.
 * @param string $formatter The formatter for the list value
 * @return <type>
 */
function to_list($item=array(), $formatter = 'default_list_formater'){
  $recursion= __FUNCTION__;
  $response = '';
  $response .= '<ul>'."\n";
    foreach($item['items'] as $li){
      if(isset($li['disabled']) && $li['disabled'] == true){
        unset($li['items']);
      }
      if(isset($li['items'])){
        $response .= '<li>'. $formatter($li) . $recursion($li, $formatter).'</li>'."\n";
      }else{
        $response .= '<li>'. $formatter($li, $formatter) . '</li>'."\n";
      }
    }
    $response .= '</ul>'."\n";
  return $response;
}

/**
 * The default list formatter
 */
function default_list_formater($item){
  return $item;
}

/**
 * Internal function. don't use.
 */
function ys_util_exist(){}