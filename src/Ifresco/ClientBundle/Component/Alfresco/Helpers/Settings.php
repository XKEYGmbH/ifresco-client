<?php

namespace Ifresco\ClientBundle\Component\Alfresco\Helpers;

use Symfony\Component\Templating\Helper\Helper;
use Ifresco\ClientBundle\Component\Alfresco\Lib\Registry;

class Settings extends Helper
{
    function __construct()
    {

    }

    function getSetting($SettingName, $default = null) {

        return Registry::getSetting($SettingName, $default);
    }

    public function getName()
    {
        return 'settings';
    }

    function load_helper($helper = null)
    {
        new HelperLoader($helper);
    }
}


class HelperLoader
{
    public function __construct($helper = null)
    {
        if(null !== $helper)
        {
            if(file_exists(__DIR__.'/'.$helper.'.php'))
            {
                include_once(__DIR__.'/'.$helper.'.php');
            }
            else {
                echo $helper;
                exit;
            }
        }
    }
}

function load_helper($helper = null)
{
   new HelperLoader($helper);
}

function use_helper($helper = null) {
    load_helper($helper);
}

class AssetsContainer {
    private static $instance = null;
    private $js = array();
    private $css = array();

    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct(){

    }

    function add_js($file) {
        $this->js[] = $file;
    }

    function get_js() {
        return $this->js;
    }

    function add_css($file) {
        $this->css[] = $file;
    }

    function get_css() {
        return $this->css;
    }
}

function get_prev_settings($param, $default) {
    $params = array(
        'app_ys_jquery_path' => '/ysJQueryRevolutionsPlugin/js/jquery/jquery-1.3.2.min.js',
        'app_ys_jquery_css' => '/ysJQueryRevolutionsPlugin/css',
        'app_ys_jquery_images' => '/ysJQueryRevolutionsPlugin/images',
        'app_ys_jquery_plugins_folder' => '/ysJQueryRevolutionsPlugin/js/jquery/plugins',
        'app_ys_jquery_plugins' => array (
            'auto_complete' =>
            array (
                'local' => true,
                'js_files' =>
                array (
                    0 => 'jquery.autocomplete.pack.js',
                    1 => 'jquery.bgiframe.min.js',
                ),
                'css_files' =>
                array (
                    0 => 'jquery.autocomplete.css',
                ),
                'default_options' =>
                array (
                    'oddClass' => 'ui-state-default',
                    'evenClass' => 'ui-state-hover',
                    'overClass' => 'ui-state-active',
                ),
            ),
        ),
        'app_ys_jquery_ui_web_dir' => '/ysJQueryUIPlugin',
        'app_ys_jquery_ui_css_dir' => '/ysJQueryUIPlugin/css',
        'app_ys_jquery_ui_theme_dir' => '/ysJQueryUIPlugin/css/themes',
        'app_ys_jquery_ui_js_dir' => '/ysJQueryUIPlugin/js/',
        'app_ys_jquery_ui_js_lib_dir' => '/ysJQueryUIPlugin/js/jquery/ui',
        'app_ys_jquery_ui_theme' => 'cupertino',
        'app_ys_jquery_ui_i18n_dir' => '/ysJQueryUIPlugin/js/jquery/ui/i18n',
        'app_ys_jquery_ui_theme_rolller' => '/ysJQueryUIPlugin/js/jquery/themeswitchertool.js',
        'app_ys_jquery_ui_core_js' => array (
            0 => 'ui.core.js',
        ),
        'app_ys_jquery_ui_core_css' => array (
            0 => 'jquery-ui-1.7.2.custom.css',
        ),
        'app_ys_jquery_ui_default_js' => array (
            0 => '/ysJQueryUIPlugin/js/jquery/ui.animations.js',
        ),
        'app_ys_jquery_ui_default_css' => array (
            0 => '/ysJQueryUIPlugin/css/ui.fgbutton.css',
            1 => '/ysJQueryUIPlugin/css/jquery.ui.css',
        ),
        'app_ys_jquery_ui_accordion_js' => array (
            0 => 'ui.accordion.js',
        ),
        'app_ys_jquery_ui_accordion_css' => array (
        ),
        'app_ys_jquery_ui_accordion_defaults' => NULL,
        'app_ys_jquery_ui_tabs_js' => array (
            0 => 'ui.tabs.js',
        ),
        'app_ys_jquery_ui_tabs_css' => array (
        ),
        'app_ys_jquery_ui_tabs_defaults' => NULL,
        'app_ys_jquery_ui_dialog_js' => array (
            0 => 'ui.dialog.js',
            1 => 'ui.draggable.js',
            2 => 'ui.resizable.js',
        ),
        'app_ys_jquery_ui_dialog_css' => array (
        ),
        'app_ys_jquery_ui_dialog_defaults' => NULL,
        'app_ys_jquery_ui_progressbar_js' => array (
            0 => 'ui.progressbar.js',
        ),
        'app_ys_jquery_ui_progressbar_css' => array (
        ),
        'app_ys_jquery_ui_progressbar_defaults' => NULL,
        'app_ys_jquery_ui_slider_js' => array (
            0 => 'ui.slider.js',
        ),
        'app_ys_jquery_ui_slider_css' => array (
        ),
        'app_ys_jquery_ui_slider_defaults' => NULL,
        'app_ys_jquery_ui_datepicker_js' => array (
            0 => 'ui.datepicker.js',
        ),
        'app_ys_jquery_ui_datepicker_css' => array (
        ),
        'app_ys_jquery_ui_datepicker_i18n_file' => 'ui.datepicker-en.js',
        'app_ys_jquery_ui_datepicker_defaults' => NULL,
        'app_ys_jquery_ui_droppable_js' => array (
            0 => 'ui.droppable.js',
        ),
        'app_ys_jquery_ui_droppable_css' => array (
        ),
        'app_ys_jquery_ui_droppable_defaults' => NULL,
        'app_ys_jquery_ui_draggable_js' => array (
            0 => 'ui.draggable.js',
        ),
        'app_ys_jquery_ui_draggable_css' => array (
        ),
        'app_ys_jquery_ui_draggable_defaults' => NULL,
        'app_ys_jquery_ui_resizable_js' => array (
            0 => 'ui.resizable.js',
        ),
        'app_ys_jquery_ui_resizable_css' => array (
        ),
        'app_ys_jquery_ui_resizable_defaults' => NULL,
        'app_ys_jquery_ui_sortable_js' => array (
            0 => 'ui.sortable.js',
        ),
        'app_ys_jquery_ui_sortable_css' => array (
        ),
        'app_ys_jquery_ui_sortable_defaults' => NULL,
        'app_ys_jquery_ui_selectable_js' => array (
            0 => 'ui.selectable.js',
        ),
        'app_ys_jquery_ui_selectable_css' => array (
        ),
        'app_ys_jquery_ui_selectable_defaults' => NULL,
        'app_ys_jquery_ui_layout_js_dir' => '/ysJQueryUIPlugin/js/jquery/layout',
        'app_ys_jquery_ui_layout_css_dir' => '/ysJQueryUIPlugin/css',
        'app_ys_jquery_ui_layout_js' => array (
            0 => 'jquery.layout.min.js',
            1 => 'jquery.layout.state.js',
            2 => 'json2.js',
        ),
        'app_ys_jquery_ui_layout_css' => array (
        ),
        'app_ys_jquery_ui_layout_defaults' => array (
            'defaults' =>
            array (
                'minSize' => 50,
                'applyDefaultStyles' => false,
                'scrollToBookmarkOnLoad' => false,
                'paneClass' => 'ui-layout-pane',
                'resizerClass' => 'ui-state-default',
                'togglerClass' => 'ui-widget-content',
                'buttonClass' => 'button',
                'contentSelector' => '.content',
                'contentIgnoreSelector' => 'span',
                'togglerLength_open' => 35,
                'togglerLength_closed' => 35,
                'hideTogglerOnSlide' => true,
                'togglerTip_open' => 'Close This Pane',
                'togglerTip_closed' => 'Open This Pane',
                'resizerTip' => 'Resize This Pane',
                'resizeContent' => true,
                'fxName' => 'slide',
                'fxSpeed_open' => 750,
                'fxSpeed_close' => 1500,
                'fxSettings_open' =>
                array (
                    'easing' => 'easeInQuint',
                ),
                'fxSettings_close' =>
                array (
                    'easing' => 'easeOutQuint',
                ),
            ),
        ),
        'app_ys_jquery_ui_fg_menu_js_dir' => '/ysJQueryUIPlugin/js/jquery/fg-menu',
        'app_ys_jquery_ui_fg_menu_css_dir' => '/ysJQueryUIPlugin/css',
        'app_ys_jquery_ui_fg_menu_defaults' => array (
            'showSpeed' => 400,
            'flyOut' => true,
        ),
        'app_ys_jquery_ui_fg_menu_js' => array (
            0 => 'fg.menu.js',
            1 => 'jquery.context.menu.js',
        ),
        'app_ys_jquery_ui_fg_menu_css' => array (
            0 => 'fg.menu.css',
            1 => 'jquery.context.menu.css',
        ),
    );

    if(isset($params[$param])) {
        return $params[$param];
    }
    else {
        return $default;
    }
}

?>