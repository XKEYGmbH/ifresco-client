<title>ifresco client - document management by May Computer - powered by Alfresco</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <link rel="stylesheet" type="text/css" href="/css/main.css" />
        <link rel="stylesheet" type="text/css" href="/css/style.css" />

        <link rel="shortcut icon" href="/images/favicon.ico" type="image/vnd.microsoft.icon" />
        <link rel="icon" href="/images/favicon.ico" type="image/vnd.microsoft.icon" />
    <?php $view['settings']->load_helper('ysJQueryRevolutions'); ?>
    <?php $view['settings']->load_helper('ysJQueryUILayout'); ?>
    <?php $view['settings']->load_helper('ysJQueryUILayout'); ?>
    <?php $view['settings']->load_helper('ysJQueryUIMenu'); ?>
    <?php $view['settings']->load_helper('ysJQueryUIDraggable'); ?>
    <?php $view['settings']->load_helper('ysJQueryUIDialog'); ?>
    <?php $view['settings']->load_helper('ysJQueryUICore'); ?>
    <?php $view['settings']->load_helper('ysUtil') ?>
    <?php $view['settings']->load_helper('ysJQueryAutocomplete') ?>
    <?php $view['settings']->load_helper('ysJQueryUIAccordion')  ?>
    <?php $view['settings']->load_helper('ysJQueryUISortable') ?>

    <?php \ifrescoClient\AlfrescoBundle\Helpers\ui_add_effects_support(array('bounce','slide','drop','scale'))   ?>
        <link rel="stylesheet" href="<?php echo $view['assets']->getUrl('/js/extjs/shared/icons/silk.css') ?>" />
        <link rel="stylesheet" href="<?php echo $view['assets']->getUrl('/css/jquery.loadmask.css') ?>" />
        <link rel="stylesheet" href="<?php echo $view['assets']->getUrl('/css/metadata.view.css') ?>" />
        <link rel="stylesheet" href="<?php echo $view['assets']->getUrl('/css/imareaselect/imgareaselect-default.css') ?>" />
        <link rel="stylesheet" href="<?php echo $view['assets']->getUrl('/js/extjs4/ux/css/ItemSelector.css') ?>" />
        <script src="<?php echo $view['assets']->getUrl('/ysJQueryRevolutionsPlugin/js/jquery/jquery-1.3.2.min.js') ?>" type="text/javascript"></script>
        <script src="<?php echo $view['assets']->getUrl('/js/jquery.loadmask.min.js') ?>" type="text/javascript"></script>
        <script src="<?php echo $view['assets']->getUrl('/js/jquery.urlEncode.js') ?>" type="text/javascript"></script>
        <script src="<?php echo $view['assets']->getUrl('/js/jquery.ternElapse.js') ?>" type="text/javascript"></script>
        <script src="<?php echo $view['assets']->getUrl('/js/jquery-1.4.js') ?>" type="text/javascript"></script>
        <script src="<?php echo $view['assets']->getUrl('/js/jquery-1.6.2.min.js') ?>" type="text/javascript"></script>
        <script src="<?php echo $view['assets']->getUrl('/js/jquery.hashchange.min.js') ?>" type="text/javascript"></script>
        <script src="<?php echo $view['assets']->getUrl('/js/jquery.hashevents.min.js') ?>" type="text/javascript"></script>
        <script src="<?php echo $view['assets']->getUrl('/js/jquery.ajaxmanager.js') ?>" type="text/javascript"></script>
        <script src="<?php echo $view['assets']->getUrl('/js/jquery.imgareaselect.min.js') ?>" type="text/javascript"></script>
        <script src="<?php echo $view['assets']->getUrl('/js/extjs4/ext-all-debug.js') ?>" type="text/javascript"></script>
        <script src="<?php echo $view['assets']->getUrl('/js/extjs4/ux/TabScrollerMenu.js') ?>" type="text/javascript"></script>

        <link rel="stylesheet" href="<?php echo $view['assets']->getUrl('/js/BoxSelect/BoxSelect.css') ?>" />
        <script src="<?php echo $view['assets']->getUrl('/js/BoxSelect/BoxSelect.js') ?>" type="text/javascript"></script>
        <script src="<?php echo $view['assets']->getUrl('/js/extjs/ux/MetaForm.js') ?>" type="text/javascript"></script>
        <script src="<?php echo $view['assets']->getUrl('/js/extjs/ux/DateTime.js') ?>" type="text/javascript"></script>
        <script src="<?php echo $view['assets']->getUrl('/js/extjs/ux/MultipleEmail.js') ?>" type="text/javascript"></script>

        <script src="<?php echo $view['assets']->getUrl('/js/extjs4/locale/ext-lang-'.$CultureStr.'.js') ?>" type="text/javascript"></script>

        <script src="<?php echo $view['assets']->getUrl('/js/swfobject/swfobject.js') ?>" type="text/javascript"></script>
        <script src="<?php echo $view['assets']->getUrl('/js/jquery.asmselect.js') ?>" type="text/javascript"></script>

        <link rel="stylesheet" href="<?php echo $view['assets']->getUrl('/css/jquery.asmselect.css') ?>" />
        <link rel="stylesheet" href="<?php echo $view['assets']->getUrl('/js/extjs4/resources/css/ext-all.css') ?>" />

        <link rel="stylesheet" href="<?php echo $view['assets']->getUrl('/js/extjs-ux/extjs-ux.css') ?>" />
        <link rel="stylesheet" href="<?php echo $view['assets']->getUrl('/js/extjs-ux/ux/container/ButtonSegment.css') ?>" />
        <link rel="stylesheet" href="<?php echo $view['assets']->getUrl('/js/extjs-ux/ux/grid/feature/Tileview.css') ?>" />

        <script src="<?php echo $view['assets']->getUrl('/js/extjs-ux/ux/grid/feature/Tileview.js') ?>" type="text/javascript"></script>
        <script src="<?php echo $view['assets']->getUrl('/js/extjs-ux/ux/grid/plugin/DragSelector.js') ?>" type="text/javascript"></script>
        <script src="<?php echo $view['assets']->getUrl('/js/extjs-ux/ux/container/ButtonSegment.js') ?>" type="text/javascript"></script>
        <script src="<?php echo $view['assets']->getUrl('/js/extjs-ux/ux/container/SwitchButtonSegment.js') ?>" type="text/javascript"></script>

        <script src="<?php echo $view['assets']->getUrl('/js/extjs4-ux-gridprinter/ux/grid/Printer.js') ?>" type="text/javascript"></script>


        <link rel="stylesheet" href="<?php echo $view['assets']->getUrl('/js/ext_ux_pdf_panel/ux/util/PDF/TextLayerBuilder.css') ?>" />

        <script src="<?php echo $view['assets']->getUrl('/js/ext_ux_pdf_panel/lib/pdf.js/compatibility.js') ?>" type="text/javascript"></script>
        <script src="<?php echo $view['assets']->getUrl('/js/ext_ux_pdf_panel/lib/pdf.js/pdf.js') ?>" type="text/javascript"></script>
        <script src="<?php echo $view['assets']->getUrl('/js/ext_ux_pdf_panel/ux/panel/PDF.js') ?>" type="text/javascript"></script>
        <script src="<?php echo $view['assets']->getUrl('/js/ext_ux_pdf_panel/ux/util/PDF/TextLayerBuilder.js') ?>" type="text/javascript"></script>


        <link rel="stylesheet" href="<?php echo $view['assets']->getUrl('/css/plupload.queue.css') ?>" />

        <script src="<?php echo $view['assets']->getUrl('/js/browserplus-min.js') ?>" type="text/javascript"></script>
        <script src="<?php echo $view['assets']->getUrl('/js/plupload/plupload.js') ?>" type="text/javascript"></script>
        <script src="<?php echo $view['assets']->getUrl('/js/plupload/plupload.silverlight.js') ?>" type="text/javascript"></script>
        <script src="<?php echo $view['assets']->getUrl('/js/plupload/plupload.flash.swfobject.js') ?>" type="text/javascript"></script>
        <script src="<?php echo $view['assets']->getUrl('/js/plupload/plupload.browserplus.js') ?>" type="text/javascript"></script>
        <script src="<?php echo $view['assets']->getUrl('/js/plupload/plupload.html4.js') ?>" type="text/javascript"></script>
        <script src="<?php echo $view['assets']->getUrl('/js/plupload/plupload.html5.js') ?>" type="text/javascript"></script>

        <script src="<?php echo $view['assets']->getUrl('/js/plupload/i18n/'.$CultureStr.'.js') ?>" type="text/javascript"></script>

        <script src="<?php echo $view['assets']->getUrl('/js/plupload/jquery.plupload.queue/jquery.plupload.queue.js') ?>" type="text/javascript"></script>

        <script src="<?php echo $view['assets']->getUrl('/js/jquery.json-2.2.min.js') ?>" type="text/javascript"></script>
        <script src="<?php echo $view['assets']->getUrl('/js/jquery.simplejson.js') ?>" type="text/javascript"></script>
        <script src="<?php echo $view['assets']->getUrl('/js/form2object.js') ?>" type="text/javascript"></script>
        <script src="<?php echo $view['assets']->getUrl('/js/zeroclipboard/ZeroClipboard.js') ?>" type="text/javascript"></script>

        <link rel="stylesheet" href="<?php echo $view['assets']->getUrl('/js/tagit/css/jquery-ui/jquery.ui.autocomplete.custom.css') ?>" />

        <script src="<?php echo $view['assets']->getUrl('/js/tagit/js/tag-it.js') ?>" type="text/javascript"></script>
        <script src="<?php echo $view['assets']->getUrl('/js/base64.js') ?>" type="text/javascript"></script>
        <script src="<?php echo $view['assets']->getUrl('/js/date.format.js') ?>" type="text/javascript"></script>

        <link rel="stylesheet" href="<?php echo $view['assets']->getUrl('/css/datagrid.css') ?>" />

        <script src="<?php echo $view['assets']->getUrl('/js/ifresco/cookies.js') ?>" type="text/javascript"></script>
        <script src="<?php echo $view['assets']->getUrl('/js/ifresco/registry.js') ?>" type="text/javascript"></script>
        <script src="<?php echo $view['assets']->getUrl('/js/ifresco/upload_files.js') ?>" type="text/javascript"></script>
        <script src="<?php echo $view['assets']->getUrl('/js/ifresco/saneButton.js') ?>" type="text/javascript"></script>
        <script type="text/javascript" src="https://www.dropbox.com/static/api/1/dropbox.js" id="dropboxjs" data-app-key="<?php echo $view['settings']->getSetting("dropboxApiKey");?>"></script>

    <?php

    if(count(\ifrescoClient\AlfrescoBundle\Helpers\AssetsContainer::getInstance()->get_css()) > 0) {
        foreach(\ifrescoClient\AlfrescoBundle\Helpers\AssetsContainer::getInstance()->get_css() as $css_file) {
            ?>
        <link rel="stylesheet" href="<?php echo $view['assets']->getUrl($css_file) ?>" />
    <?
    }
}

if(count(\ifrescoClient\AlfrescoBundle\Helpers\AssetsContainer::getInstance()->get_js()) > 0) {
    foreach(\ifrescoClient\AlfrescoBundle\Helpers\AssetsContainer::getInstance()->get_js() as $js_file) {
        ?>
        <script src="<?php echo $view['assets']->getUrl($js_file) ?>" type="text/javascript"></script>
    <?
    }
}

if ($isAdmin == true) {
    ?>
        <link rel="stylesheet" href="<?php echo $view['assets']->getUrl('/css/admin.settings.css') ?>" />
    <?php
    }
    ?>

    if(typeof console == 'undefined' || console==null){
        console = {
            log: function(s){return;}
    };
    }
    var hasDocumentOrFolder = false;
    var tagLoading = false;
    var zipArchiveExistsGen = <?php echo class_exists('\ZipArchive'); ?>;



    if (Ext.firefoxVersion >= 18) {
        var noArgs = [];
        Ext.override(Ext.Base, {
        callParent : function(args) {


        var method, superMethod = (method = this.callParent.caller) &&
        (method.$previous || ((method = method.$owner ?
        method :
        method.caller) && method.$owner.superclass[method.$name]));
        try {
        } catch (e) {
        }


    return superMethod.apply(this, args || noArgs);
    }
    });
    }

    Registry.getInstance().read();
    if (typeof Registry.getInstance().get("ColumnsetId") == 'undefined')
    Registry.getInstance().set("ColumnsetId",0);
    if (typeof Registry.getInstance().get("ArrangeList") == 'undefined')
    Registry.getInstance().set("ArrangeList","horizontal");
    if (typeof Registry.getInstance().get("BrowseSubCategories") == 'undefined')
    Registry.getInstance().set("BrowseSubCategories",false);

    var UserColumnsetId = Registry.getInstance().get("ColumnsetId");
    var ClickSearchColumnSet = <?php echo (int) $view['settings']->getSetting("ClickSearchColumnSet")?>;
    var ArrangeList = Registry.getInstance().get("ArrangeList");
    var BrowseSubCategories = Registry.getInstance().get("BrowseSubCategories");

    var intervalLoginCheck = 30000;
    var checkLoginInterval = null;
    $(document).ready(function() {

        // init ajax manager for metadata
        jQuery14.manageAjax.create('metadata', {queue: 'clear', cancelPrev: true, abortOld: true, maxRequests:1, cacheResponse:false, preventDoubleRequests: false, abort:function(xhr, status, o) {
            o.error.call(xhr,status);
        }});
    jQuery14.manageAjax.create('preview', {queue: 'clear', cancelPrev: true, abortOld: true, maxRequests:1, cacheResponse:false, preventDoubleRequests: false});
    jQuery14.manageAjax.create('tree', {queue: 'clear', cancelPrev: true, abortOld: true, maxRequests:1, cacheResponse:false, preventDoubleRequests: false});
    jQuery14.manageAjax.create('admin', {queue: 'clear', cancelPrev: true, abortOld: true, maxRequests:1, cacheResponse:false, preventDoubleRequests: false, abort:function(xhr, status, o) {
        o.error.call(xhr,status);
        }});

    if (ArrangeList == "horizontal")
    $("#verticalBtn").addClass("disabled");
    else
    $("#horizontalBtn").addClass("disabled");


    setTimeout(checkAuthPresence, 1000*60*5);
    });



    Ext.define('ScannerProfileStoreModel', {
        extend: 'Ext.data.Model',
        fields: ['name', 'title', 'description']
        });



    saneProfilesStore = Ext.create('Ext.data.Store', {
        model: 'ScannerProfileStoreModel',

        <? if($view['settings']->getSetting("scanViaSane") == 'true'){ ?>
    autoLoad: true,
    proxy : {
        type: 'ajax',
        url: '/nodeactions/getScannerProfiles',
        actionMethods: {
            read: 'GET'
        },
    timeout : 1200000,
    reader: {
        type: 'json',
        idProperty:'name',
        remoteGroup:true,
        remoteSort:true,
        root: 'data'
        }
    }
    <?php } else { ?>
    autoLoad:false
    <?php } ?>
    });

    Ext.ux.IFrameComponent = Ext.define('Ext.BoxComponent', {
        onRender : function(ct, position){
        this.el = ct.createChild({tag: 'iframe', id: 'iframe-'+ this.id, frameBorder: 0, src: this.url});
    }
    });

    Ext.onReady(function(){
        Ext.History.init();
        var tokenDelimiter = '/';
        Ext.Loader.setConfig({
        enabled:true,
        paths: {
        Ext: '.',
        ifresco: './ifresco',
        'Ext.ux.form': 'js/extjs4/ux/form'
        }
    });
    Ext.require(['widget.*', 'layout.*', 'Ext.data.*']);


    var xd = Ext.data;
    Ext.state.Manager.setProvider(Ext.create('Ext.state.CookieProvider'));
    Ext.QuickTips.init();


    var scrollerMenu = Ext.create('Ext.ux.TabScrollerMenu', {
        maxText  : 1000,
        pageSize : 5
        });



    <?php if ($view['settings']->getSetting("HomeScreen") == "false") { ?>
    if (hasDocumentOrFolder == false) {
        openFixedTab('searchtab','<?php echo $view['translator']->trans('Advanced Search'); ?>','<?php echo $view['router']->generate('ifrescoClientSearchBundle_homepage'); ?>?5');
        }
    <?php } ?>

    var viewport = Ext.create('Ext.Viewport', {
        layout: 'border',
        items: [

        Ext.create('Ext.Component', {
        region: 'north',
        height: 60,
        contentEl: 'north'
        }), {
        region: 'west',
        id: 'west-panel',
        title: '<?php echo $view['translator']->trans('Navigation'); ?>',
        split: true,
        width: 290,
        minSize: 175,
        maxSize: 400,
        collapsible: true,
        margins: '0 0 0 0',
        //activeItem:<?php echo $DefaultNav; ?>,
        layout: 'accordion',
        items: [{
            title: '<?php echo $view['translator']->trans('Tags'); ?>',
            border: false,
            iconCls: 'tagScope',
            contentEl: 'tagScope',
            listeners: {
            expand: function(){
            getTagScope();
            }
        },
        tbar:[
        '->',{
            iconCls:'refresh-icon',
            tooltip: '<?php echo $view['translator']->trans('Refresh'); ?>',
            handler: function(){
                getTagScope();
            },
            scope: this
        }]
    }]

    },
    contentTabs]
    });


    });