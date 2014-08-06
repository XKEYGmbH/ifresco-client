<?php
/*
 || UI.EFFECTS
 */

/*
 * Initial functions
 * Starts adding the helper ysJQueryUICore core files jquery.ui
 */
if(!function_exists('ui_core_exist')){
  use_helper('ysJQueryUICore');
}

/**
 * Adds the specified class to each of the set of matched elements with an
 * optional transition between the states.
 * @param string $selector jQuery Selector
 * @param array() $configurations Array. See the options in
 *        http://jqueryui.com/demos/addClass/
 */

function ui_effects_add_class($selector, $configuration){
    $pattern = _ui_class_manipulation_pattern($configuration);
    return init_ui_effects('addClass',$pattern,$selector ,$configuration);
}

/**
 * Removes all or specified class from each of the set of matched elements with
 * an optional transition between the states.
 * @param string $selector jQuery Selector
 * @param array() $configurations Array. See the options in
 *        http://jqueryui.com/demos/removeClass/
 */
function ui_effects_remove_class($selector, $configuration){
    $pattern = _ui_class_manipulation_pattern($configuration);
    return init_ui_effects('removeClass',$pattern,$selector ,$configuration);
}

/**
 * Uses a specific effect on an element (without the show/hide logic).
 * @param string $selector jQuery Selector
 * @param array() $configurations Array. See the options in
 *        http://jqueryui.com/demos/effect/
 */
function ui_effects($selector, $configuration){
    $pattern = _ui_effects_pattern($configuration);
    return init_ui_effects('effect',$pattern,$selector ,$configuration);
}

/**
 * The enhanced hide method optionally accepts jQuery UI advanced effects.
 * Uses a specific effect on an element to hide the element if the
 * first argument is an effect string.
 * @param string $selector jQuery Selector
 * @param array() $configurations Array. See the options in
 *        http://jqueryui.com/demos/hide/
 */
function ui_effects_hide($selector, $configuration){
    $pattern = _ui_effects_pattern($configuration);
    return init_ui_effects('hide',$pattern,$selector ,$configuration);
}

/**
 * The enhanced show method optionally accepts jQuery UI advanced effects.
 * Uses a specific effect on an element to show the element if the first
 * argument is a effect string.
 * @param string $selector jQuery Selector
 * @param array() $configurations Array. See the options in
 *        http://jqueryui.com/demos/show/
 */
function ui_effects_show($selector, $configuration){
    $pattern = _ui_effects_pattern($configuration);
    return init_ui_effects('show',$pattern,$selector ,$configuration);
}

/**
 * Switches from the class defined in the first argument to the class defined
 * as second argument, using an optional transition.
 * @param string $selector jQuery Selector
 * @param array() $configurations Array. See the options in
 *        http://jqueryui.com/demos/switchClass/
 */
function ui_effects_switch_class($selector, $configuration){
    $pattern = _ui_switch_class_pattern($configuration);
    return init_ui_effects('switchClass',$pattern,$selector ,$configuration);
}

/**
 * The enhanced toggle method optionally accepts jQuery UI advanced effects.
 * Uses a specific effect on an element to toggle the element
 * if the first argument is an effect string.
 * @param string $selector jQuery Selector
 * @param array() $configurations Array. See the options in
 *        http://jqueryui.com/demos/toggle/
 */
function ui_effects_toggle($selector, $configuration){
    $pattern = _ui_effects_pattern($configuration);
    return init_ui_effects('toggle',$pattern,$selector ,$configuration);
}

/**
 * Adds the specified class if it is not present,
 * and removes the specified class if it is present,
 * using an optional transition.
 * @param string $selector jQuery Selector
 * @param array() $configurations Array. See the options in
 *        http://jqueryui.com/demos/toggleClass/
 */
function ui_effects_toggle_class($selector, $configuration){
    $pattern = _ui_class_manipulation_pattern($configuration);
    return init_ui_effects('toggleClass',$pattern,$selector ,$configuration);
}

/**
 * Internal function
 * @param array() $configuration Configuration array
 */
function _ui_class_manipulation_pattern($configuration){
   $args = "";
   if(is_array($configuration) && sizeof($configuration) > 0){
       if(isset($configuration['class'])){ $args = sprintf("'%s',",$configuration['class']); }
       if(isset($configuration['duration'])){
           $durationpattern = "'%s',";
           if(is_numeric($configuration['duration'])){
               $durationpattern = "%s,";
           }
           $args .= sprintf($durationpattern, $configuration['duration']) ;
       }else{
           $args .= "'normal',";
       }
       if(isset($configuration['callback'])){   $args .= sprintf("%s,", $configuration['callback']) ; }
   $args = substr($args,0,(strlen($args)) - 1);
   }
  return $args;
}

/**
 * Internal function
 * @param array() $configuration Configuration array
 */
function _ui_effects_pattern($configuration){
    $args = "";
    if(is_array($configuration) && sizeof($configuration) > 0){
        if(isset($configuration['effect'])){
            $args = sprintf("'%s',",$configuration['effect']);
            ui_add_effects_support($configuration['effect']);
        }
        if(isset($configuration['options'])){
            $args .= sprintf("'%s',", json_encode($configuration['options']));
        }
        else {
            $args .= '{},';
        }
        if(isset($configuration['speed'])){
            $durationpattern = "'%s',";
            if(is_numeric($configuration['speed'])){
                $durationpattern = "%s,";
            }
        }else{
             $args .= "'normal',";
        }
        if(isset($configuration['callback'])){   $args .= sprintf("%s,", $configuration['callback']) ; }
    $args = substr($args,0,(strlen($args)) - 1);
    }
    return $args;
}

/**
 * Internal function
 * @param array() $configuration Configuration array
 */
function _ui_switch_class_pattern($configuration){
   $args = "";
   if(is_array($configuration) && sizeof($configuration) > 0){
       if(isset($configuration['remove'])){ $args = sprintf("'%s',",$configuration['remove']); }
       if(isset($configuration['add'])){ $args .= sprintf("'%s',",$configuration['add']); }
       if(isset($configuration['duration'])){
           $durationpattern = "'%s',";
           if(is_numeric($configuration['duration'])){
               $durationpattern = "%s,";
           }
           $args .= sprintf($durationpattern, $configuration['duration']) ;
       }else{
           $args .= "'normal',";
       }
       if(isset($configuration['callback'])){   $args .= sprintf("%s,", $configuration['callback']) ; }
   $args = substr($args,0,(strlen($args)) - 1);
   }
  return $args;
}
/*
 || END UI.EFFECTS
 */