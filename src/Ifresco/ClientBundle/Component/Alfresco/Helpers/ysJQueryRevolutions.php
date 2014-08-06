<?php
namespace Ifresco\ClientBundle\Component\Alfresco\Helpers;

use Symfony\Component\Templating\Helper\Helper;

class ysJQueryRevolutions extends Helper
{
    function __construct()
    {

    }

    public function getName()
    {
        return 'ysjqueryrevolutions';
    }
}

// Add to context the Jquery js library. See the app.yml
/*
 * //Hardcoded due to no such plugin for Symf 2. TODO: try to refactor it.
 * $jqueryPath = sfConfig::get('app_ys_jquery_path', null);
if($jqueryPath == null){
    $jqueryPath = core_get_jquery_path();
}



if(!function_exists('ys_util_exist')){
  use_helper('ysUtil');
}   

sfContext::getInstance()
           ->getResponse()
           ->addJavascript($jqueryPath, 'first');

*/
/*
 || AJAX------------------------------------------------------------------------
 */

/**
 * Load a remote page using an HTTP request
 * @param array $configurations A set of key/value pairs that configure the
 * default Ajax request. Important frequency value makes the ajax call is executed
 * periodically
 * @param boolean $isInternal If this function is inner a javascript body
 * @return string jQuery syntax
 */
function jquery_ajax($configurations = null, $isInternal = false){
  if(is_array($configurations) && sizeof($configurations) > 0){
    $suffix = '';
    $prefix = '';
    if (isset($configurations['frequency'])){
        $suffix ='setInterval(function(){';
        $prefix = '}, '. ((int)$configurations['frequency'] * 1000)  .')';
    }
    if (isset($configurations['condition']) && trim($configurations['condition']) != ''){
      $prefix = (isset($configurations['onFailureCondition'])) ? sprintf('} else {%s} ', $configurations['onFailureCondition']) . $prefix: '}'. $prefix;
      $suffix .= sprintf("if(%s){ ",$configurations['condition']);
    }
    if (isset($configurations['confirmation']) && trim($configurations['confirmation']) != ''){
      $prefix = (isset($configurations['onNoConfirmation'])) ? sprintf('} else {%s} ', $configurations['onNoConfirmation']) . $prefix: '}'. $prefix;
      $suffix .= sprintf("if(confirm('%s')){ ",$configurations['confirmation']);
    }
    if (isset($configurations['csrf']) && $configurations['csrf']){
      $sfForm = new BaseForm();
      if ($sfForm->isCSRFProtected()){
      $csrfArray = array ($sfForm->getCSRFFieldName() => "'".$sfForm->getCSRFToken() . "'");
      $configurations['data'] = (isset($configurations['data'])) ? array_merge($configurations['data'],$csrfArray) : $csrfArray ;
      }
    }
    if(isset($configurations['listener']) && is_array($configurations['listener']) ){
        $listener =  $configurations['listener'];
        $selector =  (isset($listener['selector'])) ? $listener['selector'] : 'document';
        $event =     (isset($listener['event'])) ? $listener['event'] : 'ready';
        $ajaxTemplate = ui_ajax_pattern($configurations);
        if($isInternal){
            return  $suffix. jquery_support(
                    $selector,
                    $event,like_function($suffix. jquery_support(null, 'ajax' , $ajaxTemplate) . $prefix)) ;
        }else{
            return add_jquery_support(
                    $selector,
                    $event,like_function($suffix . jquery_support(null, 'ajax' , $ajaxTemplate) . $prefix));
        }
    }else{
         $ajaxTemplate = ui_ajax_pattern($configurations);
         return $suffix . jquery_support(null, 'ajax' , $ajaxTemplate) . $prefix ;
    }
  }
}

function core_get_jquery_var($var = null){
  return 'jQuery';
}

/**
 * Setup global settings for AJAX requests
 * @param <type> $configurations A set of key/value pairs that configure the
 * default Ajax request
 * @return string jQuery syntax
 */
 function jquery_ajax_setup($configurations = null){
   return jquery_support(null , 'ajaxSetup', ui_ajax_pattern($configurations));
 }


/**
 * Load a remote page using an HTTP GET request
 * @param array $arguments
 * array('url'      => The URL of the page to load
 *       'callback' => A function to be executed whenever the data is loaded successfully.
 *       'type'     => "xml", "html", "script", "json", "jsonp", or "text"
 *       'data'     => Key/value pairs that will be sent to the server )
 * @return string jQuery syntax
 */
 function jquery_ajax_get_request($arguments = null){
   return return_jquery($arguments, jquery_support(null , 'get', ui_remote_arguments_pattern($arguments)));
 }


/**
 * Load a remote page using an HTTP POST request
 * @param array $arguments
 * array('url'      => The URL of the page to load
 *       'callback' => A function to be executed whenever the data is loaded successfully.
 *       'type'     => "xml", "html", "script", "json", "jsonp", or "text"
 *       'data'     => Key/value pairs that will be sent to the server )
 * @return string jQuery syntax
 */
 function jquery_ajax_post_request($arguments = null){
   return return_jquery($arguments, jquery_support(null , 'post', ui_remote_arguments_pattern($arguments)));
 }

/**
 * Load HTML from a remote file and inject it into the DOM
 * @param string $selector jQuery Selector
 * @param array $arguments
 * array('url'      => The URL of the page to load
 *       'callback' => A function to be executed whenever the data is loaded successfully
 *       'data'     => Key/value pairs that will be sent to the server )
 * @return string jQuery syntax
 */
 function jquery_load($selector , $arguments = null){
   return return_jquery($arguments, jquery_support($selector , 'load', ui_remote_arguments_pattern($arguments)));
 }

/**
 * Load JSON data using an HTTP GET request
 * @param array $arguments
 * array('url'      => The URL of the page to load
 *       'callback' => A function to be executed whenever the data is loaded successfully
 *       'data'     => Key/value pairs that will be sent to the server )
 * @return string jQuery syntax
 */
 function jquery_get_json($arguments = null){
   return return_jquery($arguments, jquery_support(null , 'getJSON', ui_remote_arguments_pattern($arguments)));
 }


/**
 * Loads and executes a JavaScript file using an HTTP GET request
 * @param array $arguments
 * array('url'      => The URL of the page to load
 *       'callback' => A function to be executed whenever the data is loaded successfully.)
 * @return string jQuery syntax
 */
 function jquery_get_script($arguments = null){
   return return_jquery($arguments, jquery_support(null , 'getScript', ui_remote_arguments_pattern($arguments)));
 }


/**
 * Attach a function to be executed whenever an AJAX request completes , fails
 * is sent,request begins ,requests have ended and completes successfully
 * @param string $selector A jQuery Selector
 * @param string $event
 * for complete = 'ajaxComlete'
 * for fails = 'ajaxError'
 * for is sent = 'ajaxSend'
 * for request begins = 'ajaxStart'
 * for requests have ended = 'ajaxStop'
 * for completes successfully = 'ajaxSuccess'
 * @param string $callback  The function to execute.
 * @param boolean $isInternal If this function is inner a javascript body
 * @return string jQuery syntax
 */

 function jquery_ajax_event($selector , $event , $callback, $isInternal = false){
   if($isInternal){
    return jquery_support($selector , $event, $callback);
   }else{
    return add_jquery_support($selector , $event, $callback);
   }
 }

 /**
  * Serializes a set of input elements into a string of data. This function has
  * been used for serialize the ajax form
  * @param string $selector A jQuery Selector
  * @return string jQuery syntax
  */
 function jquery_serialize($selector){
    return jquery_support($selector , 'serialize', null);
 }

 /**
  * Serializes all forms and form elements (like the .serialize() method)
  * but returns a JSON data structure for you to work with
  * @param string $selector A jQuery Selector
  * @return string jQuery syntax
  */
 function jquery_serialize_array($selector){
    return jquery_support($selector , 'serializeArray', null);
 }


/**
 * Load periodically a remote page using an HTTP request periodically
 * @param array $configurations A set of key/value pairs that configure the
 * default Ajax request
 * @return string jQuery syntax
 */
function jquery_periodically_ajax($configurations = null){
  $frequency  = isset($configurations['frequency']) ? $configurations['frequency'] : 10; // default 10 seconds
	$intervalFunction = sprintf('setInterval(function() {%s}, %s)',jquery_ajax($configurations, true), ($frequency * 1000));
  return $intervalFunction;
}

/**
 * The method makes the form submit asynchronous
 * @param string $selector A jQuery Selector (form identifier)
 * @param array $configurations A set of key/value pairs that configure the
 * default Ajax request
 * @param boolean $submit refresh the page or not. Default false
 * @param boolean $isInternal If this function is inner a javascript body
 * @return string jQuery syntax
 */
function jquery_ajax_form($selector,$configurations, $submit = false , $isInternal = false){
    $configurations['data'] = (isset($configurations['data']))? 'dataForm + "&" +' .  $configurations['data'] : 'dataForm';
    return  add_jquery_support(
            $selector,
            'submit',
            like_function(
              sprintf(
                'var dataForm = %s %s return %s',
                 jquery_serialize($selector),
                 jquery_ajax($configurations, $isInternal),
                 boolean_for_javascript($submit))));
}

/*
 || END AJAX--------------------------------------------------------------------
 */

/*
 || EFFECTS---------------------------------------------------------------------
 */

/**
 * Execute a jquery effect ()
 * @param string $selector A jQuery Selector (form identifier)
 * @param string $effect The effect: show, hide, toggle , slideDown, slideUp,
 * slideToggle, fadeIn, fadeOut, fadeTo, animate, animate, stop
 * array('options'    => for options and params arguments
 *       'speed'      => A string representing one of the three predefined speeds
 *                       ("slow", "normal", or "fast") or the number of milliseconds
 *                       to run the animation (e.g. 1000)
 *       'callback'   => A function to be executed whenever the animation completes
 *       'duration'   => A string representing one of the three predefined speeds
 *                       ("slow", "normal", or "fast") or the number of milliseconds
 *                       to run the animation (e.g. 1000)
 *       'clearQueue' => A Boolean (true/false) that when set to true clears the animation queue
 *                       effectively stopping all queued animations
 *       'gotoEnd '   => A Boolean (true/false) that when set to true causes the currently playing animation to immediately complete
 *       'switch'     => A switch to toggle the display on
 *       'opacity'    => The opacity to fade to (a number from 0 to 1)
 *       'easing'     => There are two built-in values, "linear" and "swing"
 * Depending on the effect that it uses
 * @return string jQuery syntax
 */

function jquery_execute_effect($selector,$effect, $arguments = null){
 $jqueryEffectParams = '';
 if(sizeof($arguments) > 0 && !is_null($arguments)){
   $jqueryEffectParams .= (isset($arguments['options']))    ? toJsArgument($arguments['options']). ',' : '';
   $jqueryEffectParams .= (isset($arguments['speed']))      ? toJsArgument($arguments['speed']). ','  : '';
   $jqueryEffectParams .= (isset($arguments['opacity']))    ? toJsArgument($arguments['opacity']). ',' : '';
   $jqueryEffectParams .= (isset($arguments['duration']))   ? toJsArgument($arguments['duration']). ',' : '';
   $jqueryEffectParams .= (isset($arguments['easing']))     ? toJsArgument($arguments['easing']). ',' : '';
   $jqueryEffectParams .= (isset($arguments['queue']))      ? toJsArgument($arguments['queue']). ',' : '';
   $jqueryEffectParams .= (isset($arguments['switch']))   ? toJsArgument($arguments['switch'], true). ',' : '';
   $jqueryEffectParams .= (isset($arguments['callback']))   ? toJsArgument($arguments['callback'], true). ',' : '';
   $jqueryEffectParams .= (isset($arguments['clearQueue'])) ? toJsArgument($arguments['clearQueue']). ',' : '';
   $jqueryEffectParams .= (isset($arguments['gotoEnd']))    ? toJsArgument($arguments['gotoEnd']). ',' : '';
   $jqueryEffectParams  = substr($jqueryEffectParams , 0 , strlen($jqueryEffectParams) - 1);
 }
 return jquery_support($selector , $effect, $jqueryEffectParams);
}

/*
 || END EFFECTS-----------------------------------------------------------------
 */

/*
 || EVENTS----------------------------------------------------------------------
 */

/**
 * Binds a handler to an event (like click) for all current
 * - and future - matched element. Can also bind custom events
 * @param string $selector A jQuery Selector
 * @param string $event An event type
 * @param string $function A function to bind to the event on each of the set of matched elements
 * @return string jQuery syntax
 */
function jquery_live_event($selector, $event, $function){
    return jquery_support($selector,'live', sprintf("'%s', %s",$event,$function));
}


/**
 * This does the opposite of live, it removes a bound live
 * event.
 * @param string $selector A jQuery Selector
 * @param string $event An event type to unbind
 * @param string $function A function to unbind to the event on each of the set of matched elements
 * @return string jQuery syntax
 */
function jquery_die_event($selector, $event = null , $function = null){
  $pattern = (!is_null($event))    ? sprintf("'%s'",$event) : '';
  $pattern .= (!is_null($function)) ? sprintf(", %s",$function) : '';
  return jquery_support($selector,'die', $pattern);
}

/**
 * Toggle among two or more function calls every other click
 * @param string $selector A jQuery Selector
 * @param array  $functions
 *   array(
 *     like_function('$(this).css({"list-style-type":"disc", "color":"blue"});'),
 *     like_function('$(this).css({"list-style-type":"disc", "color":"red"});'),
 *     like_function('$(this).css({"list-style-type":"", "color":""});'),
 * @return string jQuery syntax
 */
function jquery_toggle_event($selector, $functions){
  return dry_toggle_hover_events('toggle', $selector, $functions);
}

/**
 * Execute immediately a javascript function
 * @param string $function The function code
 * @param string $selector A jQuery Selector
 * @param string $selector A jQuery Event
 */
function jquery_execute($function, $selector = 'document', $event = 'ready'){
  return add_jquery_support($selector,$event, like_function($function));
}

/**
 * Simulates hovering (moving the mouse on, and off, an object).
 * This is a custom method which provides an 'in' to a frequent task
 * @param string $selector A jQuery Selector
 * @param array  $functions
 *   array(
 *     like_function('$(this).css({"list-style-type":"disc", "color":"blue"});'),
 *     like_function('$(this).css({"list-style-type":"disc", "color":"red"});')'),
 * @return string jQuery syntax
 */
function jquery_hover_event($selector, $functions ){
  return dry_toggle_hover_events('hover', $selector, $functions );
}


/**
 * Binds a handler to one or more events (like click) for each matched element.
 * Can also bind custom events
 * @param string $selector A jQuery Selector
 * @param string $event One or more event types separated by a space
 * @param string $dataOrFn Additional data passed to the event handler as event.data or
 *               - A function to bind to the event
 * @param string $function A function to bind to the event
 * @return string jQuery syntax
 */
function jquery_bind_event($selector, $event,$dataOrFn = null, $function = null){
  return dry_bind_one_events('bind', $selector, $event,$dataOrFn, $function);
}

/**
 * Binds a handler to one or more events to be executed once for each matched
 * element.
 * @param string $selector A jQuery Selector
 * @param string $event One event type
 * @param string $dataOrFn Additional data passed to the event handler as event.data or
 *               - A function to bind to the event
 * @param string $function A function to bind to the event
 * @return string jQuery syntax
 */
function jquery_one_event($selector, $event,$dataOrFn = null , $function = null){
  return dry_bind_one_events('one', $selector, $event,$dataOrFn, $function);
}

/**
 * This does the opposite of bind, it removes bound events from each of the
 * matched elements.
 * @param string $selector A jQuery Selector
 * @param string $event One event type to unbind
 * @param srting $function  A function to unbind to the event
 * @return string jQuery syntax
 */
function jquery_unbind_event($selector, $event = null, $function = null){
  $pattern = '';
  $pattern .= (!is_null($event)) ? sprintf("'%s'", $event)  : '' ;
  $pattern .= (!is_null($function)) ? sprintf(",%s", $function)  : '' ;
  return jquery_support($selector, 'unbind', $pattern);
}

/**
 * Trigger an event on every matched element.
 * @param string $selector A jQuery Selector
 * @param String $event An event object or type to trigger.
 * @param <type> $data Additional data to pass as arguments (after the event object)
 * to the event handler
 * @return string jQuery syntax
 */
function jquery_trigger_event($selector, $event = null, $data = null){
  return dry_bind_one_events('trigger', $selector, $event, $data);
}

/**
 * Triggers all bound event handlers on an element (for a specific event type)
 * WITHOUT executing the browser's default actions, bubbling, or live events.
 * @param string $selector A jQuery Selector
 * @param String $event An event object or type to trigger.
 * @param <type> $data Additional data to pass as arguments (after the event object)
 * to the event handler
 * @return string jQuery syntax
 */
function jquery_trigger_handler_event($selector, $event = null, $data = null){
  return dry_bind_one_events('trigger', $selector, $event, $data);
}

/*
 || END EVENTS------------------------------------------------------------------
 */

/*
 || DRY
 */
/**
 * Internal function don't use.
 * Don't repeat yourself
 */
function dry_bind_one_events($type, $selector, $event,$dataOrFn = null, $function = null){
  $pattern = '';
  if(is_array($dataOrFn)){
    $pattern = sprintf("'%s', %s, %s", $event, json_encode($dataOrFn), $function);
  }else{
    if(is_null($dataOrFn)){
      $pattern = sprintf("'%s'", $event);
    }else{
      $pattern = sprintf("'%s', %s", $event, $dataOrFn);
    }

  }
  return jquery_support($selector, $type, $pattern);
}

/**
 * Internal function don't use.
 * Don't repeat yourself
 */
function dry_toggle_hover_events($type, $selector, $functions){
    if(is_array($functions)){
    $pattern = '';
    foreach($functions as $function){
      $pattern .= (!is_null($function)) ? sprintf("%s ,",$function) : '';
    }
    if($pattern != ''){
      $pattern = substr($pattern,0,(strlen($pattern)) - 1);
    }
      return jquery_support($selector,$type, $pattern);
  }
}

/**
 * Internal function don't use.
 */
function jq_get_errors($code){
  $errors =
  array(
    'AJAX01' => 'ERROR: {Tipo: configuracion}, {mensaje : El parametro debe ser un array}');
  return $errors[$code];
}

/**
 * Internal function don't use.
 */
function core_get_jq_alert_message($message){
  return spritnf("alert('%s');", $message);
}

/**
 *
 * @param string $selector A jQuery Selector
 * @param string $events An event type to unbind
 * @param string $args Arguments or a javascript function sintax.
 * @param bollean $unescapeId The jQUery selector without "'"
 * @param string $accesors Accesor for the jQuery object
 * @return string jQuery syntax
 */
function jquery_support($selector , $events = 'ready' , $args ="", $unescapeId = true, $accesors = '', $addSeparator = true){
  if(is_array($events)){
    $jquery = '';
    foreach($events as $event => $arg){
      $jquery .= jquery_sintax_builder($selector,$event,$arg,$unescapeId,$accesors, $addSeparator);
    }
    return $jquery;
  }else{
    return jquery_sintax_builder($selector,$events,$args,$unescapeId,$accesors, $addSeparator);
  }
}

/**
 * Internal function don't use.
 */
function jquery_sintax_builder($selector , $events = 'ready' , $args ="", $unescapeId = true, $accesors = '', $addSeparator = true){
    $separator = ($addSeparator == true) ? ';' : '';
    if(!is_null($selector)){
      if(is_array($selector) && sizeof($selector) > 1){
        $trimSelector = strtolower(trim($selector));
        if ($trimSelector === 'this' || $trimSelector === 'document' || $trimSelector === 'window' )
          $unescapeId = false;
        if($unescapeId){
          return sprintf("%s('%s', %s).%s(%s)%s%s",core_get_jquery_var(),$selector[0],$selector[1],$events,$args,$accesors, $separator);
        }else{
          return sprintf("%s(%s, %s).%s(%s)%s%s",core_get_jquery_var(),$selector[0],$selector[1],$events,$args,$accesors, $separator);
        }
      }else{
        $trimSelector = strtolower(trim($selector));
        if ($trimSelector === 'this' || $trimSelector === 'document' || $trimSelector === 'window' )
          $unescapeId = false;
        if($unescapeId){
          return sprintf("%s('%s').%s(%s)%s%s",core_get_jquery_var(),(string) $selector,$events,$args,$accesors, $separator);
        }else{
          return sprintf("%s(%s).%s(%s)%s%s",core_get_jquery_var(),(string) $selector,$events,$args,$accesors, $separator);
        }
      }
    }else{
      return sprintf("%s.%s(%s)%s%s",core_get_jquery_var(),$events,$args,$accesors,$separator);
    }
}

function jquery_get($selector , $events , $args = null, $unescapeId = true, $accesors = '', $addSeparator = false){
  return jquery_support($selector, $events, $args , $unescapeId , $accesors, $addSeparator);
}

function jquery_set($selector , $events , $args = null, $unescapeId = true, $accesors = '', $addSeparator = false){
  $args  = (is_array($args)) ? json_encode($args) : $args ;
  return jquery_support($selector, $events, $args , $unescapeId , $accesors, $addSeparator);
}

/**
 *
 * @param string $selector A jQuery Selector
 * @param string $events An event type to unbind
 * @param string $args Arguments or a javascript function sintax.
 * @param bollean $unescapeId The jQUery selector without "'"
 * @param string $accesors Accesor for the jQuery object
 * @return string jQuery syntax
 */
function add_jquery_support($selector , $event = 'ready' , $args = "function(){return false;}", $unescapeId = true, $accesors = '', $addSeparator = true){
  $support  = core_init_javasacript_tag();
  if($event === 'ready'){
    $support .= jquery_support($selector,'ready',$args);
  }else{
    $support .= jquery_support($selector,'ready',like_function(jquery_support($selector, $event , $args)));
  }
  $support .= core_end_javasacript_tag();
  return $support;
}

/**
 * Internal function don't use.
 */
function core_get_jquery_path(){
    return $pathSeparattor .sfConfig::get('app_ys_jquery_web_dir', 'ysJQueryRevolutionsPlugin')
           . $pathSeparattor . sfConfig::get('ys_jquery_js_folder', 'js')
           . $pathSeparattor . sfConfig::get('app_ys_jquery_js_dir', 'jquery')
           . $pathSeparattor . sfConfig::get('app_ys_jquery_core', 'jquery-1.3.2.min.js');
}

/*
 || PATTERNS
 */

/**
 * Internal function don't use.
 */
function ui_ajax_pattern($configuration){
  $pattern = '';
  if(is_array($configuration) && sizeof($configuration) > 0){
    $pattern = '{';
    //OPTIONS SUPPORT
    if(isset($configuration['async'])){         $pattern .= toJQueryOption('async', $configuration['async']); }
    if(isset($configuration['beforeSend'])){    $pattern .= toJQueryOption('beforeSend', $configuration['beforeSend'], true); }
    if(isset($configuration['cache'])){         $pattern .= toJQueryOption('cache', $configuration['cache']); }
    if(isset($configuration['complete'])){      $pattern .= toJQueryOption('complete', $configuration['complete'], true); }
    if(isset($configuration['contentType'])){   $pattern .= toJQueryOption('contentType', $configuration['contentType']); }
    if(isset($configuration['data'])){          $pattern .= toJQueryOption('data', $configuration['data'], true); }
    if(isset($configuration['dataFilter'])){    $pattern .= toJQueryOption('dataFilter', $configuration['dataFilter'], true); }
    if(isset($configuration['dataType'])){      $pattern .= toJQueryOption('dataType', $configuration['dataType']); }
    if(isset($configuration['error'])){         $pattern .= toJQueryOption('error', $configuration['error'], true); }
    if(isset($configuration['global'])){        $pattern .= toJQueryOption('global', $configuration['global']); }
    if(isset($configuration['jsonp'])){         $pattern .= toJQueryOption('jsonp', $configuration['jsonp']); }
    if(isset($configuration['password'])){      $pattern .= toJQueryOption('password', $configuration['password']); }
    if(isset($configuration['processData'])){   $pattern .= toJQueryOption('processData', $configuration['processData']); }
    if(isset($configuration['success'])){       $pattern .= toJQueryOption('success', $configuration['success'], true); }
    if(isset($configuration['timeout'])){       $pattern .= toJQueryOption('timeout', $configuration['timeout']); }
    if(isset($configuration['type'])){          $pattern .= toJQueryOption('type', $configuration['type']); }
    if(isset($configuration['url'])){           $pattern .= toJQueryOption('url', $configuration['url']); }
    if(isset($configuration['urlVar'])){        $pattern .= toJQueryOption('url', $configuration['urlVar'], true); }
    if(isset($configuration['username'])){      $pattern .= toJQueryOption('username', $configuration['username']); }
    if(isset($configuration['xhr'])){           $pattern .= toJQueryOption('xhr', $configuration['xhr'], true); }

    if($pattern != '{')
      $pattern = substr($pattern,0,(strlen($pattern)) - 1);
    $pattern .= '}';
  }
  return $pattern;
}

/**
 * Internal function don't use.
 */
function ui_remote_arguments_pattern($configuration){
   $jqueryEffectParams = '';
   if(sizeof($configuration) > 0 && !is_null($configuration)){
     $jqueryEffectParams .= (isset($configuration['url']))   ? toJsArgument($configuration['url']). ',': '';
     $jqueryEffectParams .= (isset($configuration['data'])) ? toJsArgument($configuration['data']).  ',': '';
     $jqueryEffectParams .= (isset($configuration['callback'])) ? toJsArgument($configuration['callback'],true).  ',': '';
     $jqueryEffectParams .= (isset($configuration['type'])) ? toJsArgument($configuration['type']). ',' : '';
     $jqueryEffectParams  = substr($jqueryEffectParams , 0 , strlen($jqueryEffectParams) - 1);
   }
   return $jqueryEffectParams;
}

/**
 * @return init javascript tag
 */
function core_get_javasacript_tag(){
  return <<<EOF
         <script language="javascript" type="text/javascript">/*\n <![CDATA[ */\n\t %s \n/* ]]> */</script>
        ';
EOF;
}

/**
 * @return end javascript tag
 */
function core_init_javasacript_tag(){
  return <<<EOF
        <script language="javascript" type="text/javascript">\n/* <![CDATA[ */\n\t
EOF;
}

/**
 * @return jQuery javascript tags
 */
function core_end_javasacript_tag(){
  return <<<EOF
         \n/* ]]> */
        </script>
EOF;
}

/*
 || UTIL
 */


/**
 *
 * @param string $bodyFuntion The javascript body function
 * @param string $arguments The javascript arguments function
 * @return string javascript syntax
 */
function return_jquery($configurations, $sintax){
  $finalSintax = $sintax;
  if(is_array($configurations) && isset($configurations['listener']) ){
      $listener = $configurations['listener'];
      if((isset($listener['event']) || isset($listener['oneEvent'])) && isset($listener['selector'])){
        $listener['event'] = (isset($listener['oneEvent'])) ? $listener['oneEvent'] : $listener['event'];
        $finalSintax = '';
        if(isset($listener['oneEvent'])){
          if(isset($listener['before'])){
            $finalSintax .= jquery_execute(jquery_one_event($listener['selector'],$listener['event'],$listener['before']));
          }
            $finalSintax .= jquery_execute(jquery_one_event($listener['selector'], $listener['event'] , like_function($sintax)));
          if(isset($listener['after'])){
            $finalSintax .= jquery_execute(jquery_one_event($listener['selector'],$listener['event'],$listener['after']));
          }
        }else{
          if(isset($listener['before'])){
            $finalSintax .= add_jquery_support($listener['selector'],$listener['event'],$listener['before']);
          }
            $finalSintax .= add_jquery_support($listener['selector'],$listener['event'],like_function($sintax));
          if(isset($listener['after'])){
            $finalSintax .= add_jquery_support($listener['selector'],$listener['event'],$listener['after']);
          }
        }
      }
  }
  return $finalSintax;
}

/**
 *
 * @param string $bodyFuntion The javascript body function
 * @param string $arguments The javascript arguments function
 * @return string javascript syntax
 */
function like_function($bodyFuntion, $arguments = null){
  return sprintf('function(%s){%s}',$arguments, $bodyFuntion);
}

/**
 *
 * @param string $funtion The function to evaluate
 * @return string javascript syntax
 */
function eval_function($funtion){
  return sprintf("eval(%s)",$funtion);
}


/**
 * Internal function don't use.
 */
function toJQueryOption($key, $value, $isFunction = false){
    $pattern = $key . ": '%s',";
    if($isFunction){
        $pattern = $key . ": %s,";
        if(is_array($value)){
            $arrayFunctionPattern = '{';
            foreach($value as $arrayKey => $arrayValue){
                $arrayFunctionPattern .= sprintf("'%s': %s,",$arrayKey,$arrayValue);
            }
            $arrayFunctionPattern = ($arrayFunctionPattern != '{') ? substr($arrayFunctionPattern,0,strlen($arrayFunctionPattern)-1) : $arrayFunctionPattern ;
            $arrayFunctionPattern .= '}';
            $value = $arrayFunctionPattern;
        }
        $pattern = sprintf($pattern, $value);
        return $pattern;
    }
    if(!is_null($value)){
        if(is_array($value)){
            $pattern = $key . ": %s,";
            $pattern = sprintf($pattern, json_encode($value));
            return $pattern;
        }
        if(is_numeric($value)){
            $pattern = $key . ": %s,";
            $pattern = sprintf($pattern, $value);
            return $pattern;
        }
        if(is_string($value)){
            $pattern = $key . ": '%s',";
            $pattern = sprintf($pattern, $value);
            return $pattern;
        }
        if(is_bool($value)){
            $pattern = $key . ": %s,";
            $pattern = sprintf($pattern,boolean_for_javascript($value));
            return $pattern;
        }
    }
    $pattern = sprintf($pattern,$value);
}

/**
 * Internal function don't use.
 */
function toJsArgument($value, $isFunction = false){
   $pattern = '%s';
   if($isFunction){
        $pattern = sprintf($pattern, $value);
   }else{
       if(!is_null($value)){
            if(is_array($value)){
                $pattern = sprintf($pattern, json_encode($value));
            }
            if(is_numeric($value)){
                $pattern = sprintf($pattern, $value);
            }
            if(is_string($value)){
                $pattern = "'%s'";
                $pattern = sprintf($pattern, $value);
            }
            if(is_bool($value)){
                $pattern = sprintf($pattern,boolean_for_javascript($value));
            }
        }
    }
  return $pattern;
}

function add_js($filesName, $path, $position='last'){
  if(!is_array($filesName)){
    //sfContext::getInstance()->getResponse()->addJavascript($path . $filesName, $position);
      AssetsContainer::getInstance()->add_js($path . $filesName);
  }else{
    foreach ($filesName as $files){
      //sfContext::getInstance()->getResponse()->addJavascript($path . $files, $position);
        AssetsContainer::getInstance()->add_js($path . $files);
    }
  }
}

function add_css($filesName, $path, $position='last'){
  if(!is_array($filesName)){
    //sfContext::getInstance()->getResponse()->addStylesheet($path . $filesName, $position);
      AssetsContainer::getInstance()->add_css($path . $filesName);
  }else{
    foreach ($filesName as $files){
      //sfContext::getInstance()->getResponse()->addStylesheet($path . $files, $position);
        AssetsContainer::getInstance()->add_css($path . $files);
    }
  }
}


/**
 * Internal function don't use.
 */
function arrayToString($array){
  $parseString = '';
  if(is_array($array)){
    foreach($array as $key => $value){
      $parseString .= sprintf(' %s="%s"',$key,$value);
    }
  }
  return $parseString;
}
