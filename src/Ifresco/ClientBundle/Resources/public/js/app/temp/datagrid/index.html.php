<?php
$isAdmin = $app->getSecurity()->getToken()->isAdmin();
?><style type="text/css">
    div.toppicrow
    {
        border: 0px solid #000;
    }

    div#toppics
    {
        visibility : hidden;
        width:100%;
    }

    img.photo { cursor: pointer; }

</style>
<script type="text/javascript">

var lastParams<?php echo $containerName; ?> = null;
var currentColumnsetid<?php echo $containerName; ?> = 0;
var mainGrid<?php echo $containerName; ?> = null;
var columnStore<?php echo $containerName; ?> = null;
var mainThumbView<?php echo $containerName; ?> = null;
var versionList<?php echo $containerName; ?> = null;
var versionStore<?php echo $containerName; ?> = null;
var currentNodeId<?php echo $containerName; ?> = null;
var zipArchiveExists<?php echo $containerName; ?> = '<?php echo $zipArchiveExists; ?>';
var orgDetailUrl<?php echo $containerName; ?> = '<?php echo $DetailUrl; ?>';
var detailUrl<?php echo $containerName; ?> = orgDetailUrl<?php echo $containerName; ?>;

var PanelNodeId<?php echo $containerName; ?> = null;
var PanelNodeMimeType<?php echo $containerName; ?> = null;
var PanelNodeText<?php echo $containerName; ?> = null;
var PanelNodeType<?php echo $containerName; ?> = null;
var PanelNodeUrl<?php echo $containerName; ?> = null;
var PanelNodeIsCheckedOut<?php echo $containerName; ?> = null;
var PanelNodeCheckedOutId<?php echo $containerName; ?> = null;
var PanelNodeOrgId<?php echo $containerName; ?> = null;

var SelectedVersion<?php echo $containerName; ?> = null;
var isDblClick<?php echo $containerName; ?> = false;


var ZohoMimeDocs = ["application/msword","application/vnd.openxmlformats-officedocument.wordprocessingml.document","application/rtf","text/rtf","text/html","application/vnd.oasis.opendocument.text","application/vnd.sun.xml.writer","text/plain"];
var ZohoMimeSheet = ["application/vnd.ms-excel","application/vnd.openxmlformats-officedocument.spreadsheetml.sheet","application/vnd.oasis.opendocument.spreadsheet","application/vnd.sun.xml.calc","application/vnd.openxmlformats-officedocument.spreadsheetml.sheet","text/csv","text/comma-separated-values","text/tab-separated-values"];


Ext.onReady(function(){

    if (Ifresco.Registry.get("ArrangeList")==="horizontal") {
        var docGridRegion = "north";
        var docGridWidth = "100%";
        var docGridHeight = getHalfSize();
        var tabPrevRegion = "center";
        var tabPrevWidth = "100%";
        var tabPrevHeight = getHalfSize();
        var tabPrevTabPos = "bottom";

        if ($.browser.msie) {
            mySize<?php echo $containerName; ?>();
        }

        var versionSize = getHalfSize()-25;
        var gridSize = getHalfSize();
    }
    else {
        var docGridRegion = "west";
        var docGridWidth = "50%";

        var tabPrevRegion = "center";
        var tabPrevWidth = "50%";
        //if ($.browser.msie) {
        if (1) {
            mySize<?php echo $containerName; ?>();
        }
        var docGridHeight = "100%";
        var tabPrevHeight = "100%";

        var tabPrevTabPos = "top";

        var versionSize = $("#documentGrid<?php echo $containerName; ?>").height()-30;
        var gridSize = $("#documentGrid<?php echo $containerName; ?>").height();
    }


    Ext.state.Manager.setProvider(
        Ext.create('Ext.state.CookieProvider', {
            expires: new Date(new Date().getTime()+(1000*60*60*24*365))
        }));


    Ext.QuickTips.init();

    var viewportToolbar<?php echo $containerName; ?> = Ext.createWidget('toolbar', {
        id:'viewportToolbar<?php echo $containerName; ?>',
        hidden:true,
        items: []
    });


    var reader<?php echo $containerName; ?> = Ext.create('Ext.data.JsonReader', {
        idProperty:'nodeRef',
        fields: [<?php echo html_entity_decode($fields,ENT_QUOTES); ?>],
        root:'data',
        remoteGroup:true,
        remoteSort: true,
        //remoteSort: false,
        totalProperty:'totalCount'
    });

    Ext.define('GridStoreModel', {
        extend: 'Ext.data.Model',
        fields: [<?php echo html_entity_decode($fields,ENT_QUOTES); ?>]
    });

    var store<?php echo $containerName; ?> = Ext.create('Ext.data.Store', {
        //reader: reader<?php echo $containerName; ?>,
        model: 'GridStoreModel',
        pageSize: 30,
        proxy : {
            type: 'ajax',
            url: '<?php echo $view['router']->generate('ifresco_client_grid_data') ?>',
            //method: 'GET',
            actionMethods: { // changed in extjs4
                read: 'POST'
            },
            timeout : 1200000,
            reader: {
                type: 'json',
                idProperty:'nodeId',
                remoteGroup:true,
                remoteSort:true,
                totalProperty: 'totalCount',
                root: 'data'
            }
        },
        <?php if (!empty($DefaultSort) && $DefaultSort != null) { ?>
        sortOnLoad: true,
        sortInfo: {field: '<?php echo $DefaultSort; ?>', direction: '<?php echo $DefaultSortDir; ?>'},
        sorters: [{
            property: '<?php echo $DefaultSort; ?>',
            direction:'<?php echo $DefaultSortDir; ?>'
        }],
        permEdit: false,
        perms: false,
        isSearchRequest: false,
        <?php } ?>
        listeners:{
            datachanged:function(store,e) {
                try {
                    var json = store.getProxy().reader.jsonData;
                    var breadcrumb = json.breadcrumb;
                    var folder_path = json.folder_path;
                    var perms = json.perms;
                    this.perms = json.perms;
                    var isSearch = json.isSearch?true:false;
                    this.isSearchRequest = isSearch;
                    var isClipBoard = <? echo $isClipBoard?'true':'false';?>;

                    if(perms) {
                        var uploadBtn       = Ext.getCmp("upload-content<?php echo $containerName; ?>");
                        var createFolderBtn = Ext.getCmp("create-folder<?php echo $containerName; ?>");
                        var createHtmlBtn = Ext.getCmp("create-html<?php echo $containerName; ?>");
                        var pasteCopyBtn = Ext.getCmp("pastecopy-clipboard<?php echo $containerName; ?>");
                        var pasteCutBtn = Ext.getCmp("pastecut-clipboard<?php echo $containerName; ?>");
                        var pasteLinkBtn = Ext.getCmp("pastelink-clipboard<?php echo $containerName; ?>");

                        uploadBtn.setDisabled(!perms.alfresco_perm_create);
                        createFolderBtn.setDisabled(!perms.alfresco_perm_create);
                        createHtmlBtn.setDisabled(!perms.alfresco_perm_create);
                        pasteCopyBtn.setDisabled(!perms.alfresco_perm_create || !ClipBoard.items.length);
                        pasteCutBtn.setDisabled(!perms.alfresco_perm_create || !ClipBoard.items.length);
                        pasteLinkBtn.setDisabled(!perms.alfresco_perm_create || !ClipBoard.items.length);

                        this.permEdit = perms.alfresco_perm_edit;

                    }

                    if(folder_path) {
                        detailUrl<?php echo $containerName; ?> = '<?php echo $ShareFolder; ?>'+folder_path;
                    }
                    else {
                        detailUrl<?php echo $containerName; ?> = '<?php echo $ShareFolder; ?>'+'/';
                    }

                    if(isSearch) {
                        var buttons = mainGrid<?php echo $containerName; ?>.dockedItems.items[1].items.items;
                        var skipNames = ['refresh-meta', 'column-sets', 'grid-print', 'export-csv', 'pdf-merge'];

                        for(var i = 0; i < buttons.length; i++) {
                            if(jQuery.inArray(buttons[i].iconCls, skipNames) < 0)
                                buttons[i].setVisible(false);
                        }
                    } else if(isClipBoard) {
                        var buttons = mainGrid<?php echo $containerName; ?>.dockedItems.items[1].items.items;
                        var skipNames = ['refresh-meta', 'column-sets', 'grid-print', 'remove-clipboard', 'export-csv', 'pdf-merge'];

                        for(var i = 0; i < buttons.length; i++) {
                            if(jQuery.inArray(buttons[i].iconCls, skipNames) < 0)
                                buttons[i].setVisible(false);
                        }
                    }
                    else {
                        var buttons = mainGrid<?php echo $containerName; ?>.dockedItems.items[1].items.items;
                        for(var i = 0; i < buttons.length; i++) {
                            buttons[i].setVisible(true);
                        }
                    }

                    var tbar =  Ext.getCmp("viewportToolbar<?php echo $containerName; ?>");
                    if (tbar) {
                        tbar.removeAll();

                        if (breadcrumb.length > 0) {
                            tbar.show();
                            $.each(breadcrumb,function(index,props) {
                                tbar.add({
                                    text: props.text,
                                    cls: (index == 0 ? 'x-btn-text' : 'x-btn-text-icon'),
                                    iconCls: (index == 0 ? '' : 'arrow'),
                                    handler: function() {
                                        openFolder(props.id,'<img src="'+props.icon+'" border=0 align=absmiddle> '+props.text);
                                    }
                                })
                            });
                        }
                        else {
                            tbar.hide();
                        }
                    }
                }
                catch (e) {
                }
            },
            exception:function(proxy,type,action,options,response) {
                Ext.MessageBox.show({
                    title: '<?php echo $view['translator']->trans('Too many results'); ?>',
                    msg: '<?php echo $view['translator']->trans('The result list was too big. Please add more arguments to get a good result.'); ?>',
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.WARNING,
                    buttons: {
                        ok: "<?php echo $view['translator']->trans('Search again'); ?>",
                        cancel: "<?php echo $view['translator']->trans('Cancel Search'); ?>"
                    },
                    fn:function(btn) {
                        if (btn == "ok") {
                            var params = options.params;
                            store<?php echo $containerName; ?>.load(params);
                        }
                        Ext.MessageBox.hide();
                    }

                });
            },
            beforeload: function(store, operation, eOpts) {
                store.loadMask = new Ext.LoadMask(grid<?php echo $containerName; ?>, {msg:'<?php echo $view['translator']->trans('Please wait...'); ?>'});
                store.loadMask.show();
            },
            load: function(store, records, successful, eOpts) {
                store.loadMask.hide();
            }
        }
    });


    var win<?php echo $containerName; ?>, winSpace<?php echo $containerName; ?>, winMetaData<?php echo $containerName; ?>;

    tBar<?php echo $containerName; ?> = [
        {
            iconCls:'upload-content',
            id:'upload-content<?php echo $containerName; ?>',
            tooltip:'<?php echo $view['translator']->trans('Upload File(s)'); ?>',
            cls: 'x-btn-icon',
            handler: function(){
                uploadPanel<?php echo $containerName; ?>.show();
                return;
                //if(!win<?php echo $containerName; ?>){

                win<?php echo $containerName; ?> = Ext.create('Ext.window.Window', {
                    modal:true,
                    id:'upload-window<?php echo $containerName; ?>',
                    layout:'fit',
                    width:700,
                    height:360,
                    closeAction:'destroy',
                    plain: true,
                    constrain: true,
                    resizable: {handles: 'w e'},
                    title: '<?php echo $view['translator']->trans('Upload File(s)'); ?>',

                    items: Ext.create('Ext.panel.Panel', {
                        //renderTo: 'upload-window-panel<?php echo $containerName; ?>',
                        id: 'upload-window-panel<?php echo $containerName; ?>',
                        layout:'fit',
                        border:false
                    }),

                    listeners:{
                        'beforeshow':{
                            fn:function() {
                                $(".PDFRenderer").hide();
                            }
                        },
                        'hide': {
                            fn:function() {
                                $(".PDFRenderer").show();
                                refreshGrid<?php echo $containerName; ?>();
                                $('#uploader<?php echo $containerName; ?>').pluploadQueue().destroy();
                            }
                        }
                    },

                    buttons: [{
                        text: '<?php echo $view['translator']->trans('Close'); ?>',
                        handler: function() {
                            win<?php echo $containerName; ?>.close(this);
                        }
                    }]
                });

                $.ajax({
                    cache: false,
                    url : "<?php echo $view['router']->generate('ifresco_client_upload') ?>",
                    data: ({'nodeId' : currentNodeId<?php echo $containerName; ?>, 'containerName':'<?php echo $containerName; ?>'}),

                    success : function (data) {
                        $("#upload-window-panel<?php echo $containerName; ?>").html(data);
                    },
                    beforeSend : function() {
                        $("#upload-window-panel<?php echo $containerName; ?>").html("");
                    }
                });
                //win<?php echo $containerName; ?>.show();
                //}
                //else {
                //  initSettings<?php echo $containerName; ?>();  
                //}     


                win<?php echo $containerName; ?>.show();
            },
            scope: this
        },
        {
            iconCls:'create-folder',
            id:'create-folder<?php echo $containerName; ?>',
            tooltip:'<?php echo $view['translator']->trans('Create Space'); ?>',
            cls: 'x-btn-icon',
            handler: function(){
                if(!winSpace<?php echo $containerName; ?>){
                    winSpace<?php echo $containerName; ?> = Ext.create('Ext.Window', {
                        modal:true,
                        id:'general-window<?php echo $containerName; ?>',
                        layout:'fit',
                        width:500,
                        height:350,
                        closeAction:'hide',
                        constrain: true,
                        title:'<?php echo $view['translator']->trans('Create Space'); ?>',
                        plain: true,
                        resizable: false,

                        items: Ext.create('Ext.panel.Panel', {
                            //renderTo: 'window-panel<?php echo $containerName; ?>',
                            id: 'window-panel<?php echo $containerName; ?>',
                            layout:'fit',
                            border:false
                        }),

                        listeners:{
                            'beforeshow':{
                                fn:function() {
                                    $(".PDFRenderer").hide();
                                    $("#general-window<?php echo $containerName; ?>").mask("<?php echo $view['translator']->trans('Loading...'); ?>",300);
                                    $.ajax({
                                        cache: false,
                                        url : "<?php echo $view['router']->generate('ifresco_client_folder_actions') ?>",

                                        success : function (data) {
                                            $("#general-window<?php echo $containerName; ?>").unmask();
                                            $("#window-panel<?php echo $containerName; ?>").html(data);
                                            $("#spaceCreateForm #cm_name").focus();
                                        }
                                    });
                                }
                            },
                            'hide': {
                                fn:function() {
                                    $(".PDFRenderer").show();
                                }
                            }
                        },

                        buttons: [{
                            text: '<?php echo $view['translator']->trans('Save'); ?>',
                            handler: function() {

                                $.post("<?php echo $view['router']->generate('ifresco_client_create_space_post') ?>", $("#spaceCreateForm").serialize()+"&nodeId="+currentNodeId<?php echo $containerName; ?>, function(data) {
                                    if (data.success === "true") {

                                        Ext.MessageBox.show({
                                            title: '<?php echo $view['translator']->trans('Success'); ?>',
                                            msg: data.message,
                                            buttons: Ext.MessageBox.OK,
                                            icon: Ext.MessageBox.INFO,
                                            fn:showRenderer
                                        });

                                        Ext.getCmp("alfrescoTree").getStore().load()
                                        //Ext.getCmp('alfrescoTree').render();

                                        winSpace<?php echo $containerName; ?>.hide();

                                        //grid<?php echo $containerName; ?>.getStore().reload();
                                        refreshGrid<?php echo $containerName; ?>();
                                        $("#window-panel<?php echo $containerName; ?>").html('');

                                    }
                                    else {

                                        Ext.MessageBox.show({
                                            title: '<?php echo $view['translator']->trans('Error'); ?>',
                                            msg: data.message,
                                            buttons: Ext.MessageBox.OK,
                                            icon: Ext.MessageBox.WARNING,
                                            fn:showRenderer
                                        });
                                    }
                                }, "json");
                            }
                        },
                            {
                                text: '<?php echo $view['translator']->trans('Close'); ?>',
                                handler: function() {
                                    $(".PDFRenderer").show();
                                    $("#window-panel<?php echo $containerName; ?>").html('');

                                    winSpace<?php echo $containerName; ?>.hide(this);
                                }
                            }]
                    });
                }
                winSpace<?php echo $containerName; ?>.show();
            }
        },
        {
            iconCls:'create-html',
            id:'create-html<?php echo $containerName; ?>',
            tooltip:'<?php echo $view['translator']->trans('Create HTML'); ?>',
            cls: 'x-btn-icon',
            handler: function(){
                createHTMLdoc(currentNodeId<?php echo $containerName; ?>, '<?php echo $containerName; ?>');

            }
        },
        <? if($view['settings']->getSetting("scanViaSane") == 'true'){ ?>
        {
            iconCls:'scan-doc',
            id:'scan-doc<?php echo $containerName; ?>',
            tooltip:'<?php echo $view['translator']->trans('Scan Document'); ?>',
            cls: 'x-btn-icon',
            containerName: '<?php echo $containerName; ?>',
            xtype: 'sanebutton'
        }
        <? } ?>
        ,'-',
        {
            tooltip: '<?php echo $view['translator']->trans('Load Column Set'); ?>',
            iconCls:'column-sets',
            id:'column-sets<?php echo $containerName; ?>',
            cls: 'x-btn-icon',
            disabled:<?php echo ($Columnsets == null ? 'true' : 'false'); ?>,
            handler: function() {

            },
            menu: new Ext.menu.Menu({
                items: [
                    <?php $start = false;
                    foreach ($Columnsets as $Column) { 
                        if ($start == true)
                            echo ",";
                        else
                            $start = true;
                        ?>
                    {
                        text: '<?php echo $Column->getName(); ?>',
                        handler: function(){
                            loadNewColumns<?php echo $containerName; ?>({name: '<?php echo $Column->getName(); ?>',id:'<?php echo $Column->getId(); ?>'});
                        }
                    }
                    <?php 
                    } 
                    ?>
                ]
            })
        },
        <? if($view['settings']->getSetting("PDFExport") == 'true' && $view['settings']->getSetting("CSVExport") == 'true') { ?>'-',<?}?>
        <? if($view['settings']->getSetting("CSVExport") == 'true'){ ?>
        {
            iconCls:'export-csv',
            hidden: <? if($view['settings']->getSetting("CSVExport") == 'true'){echo 'false';} else{echo 'true';}?>,
            tooltip:'<?php echo $view['translator']->trans('Export CSV'); ?>',
            cls: 'x-btn-icon',
            handler: function(){
                var params = lastParams<?php echo $containerName; ?>;
                params = object2string(params.params);
                params = params.split('&');


                var win = window.open('');
                win.document.write("<head></head><body>");
                win.document.write("<form action='<?php echo $view['router']->generate('ifresco_client_export_result_set') ?>' method='POST'>");
                for(var i = 0; i < params.length; i++) {
                    var param = params[i];

                    if(param != '') {
                        param = param.split('=');
                        if(param.length == 2) {
                            win.document.write("<input type='hidden' name='"+param[0]+"' value='"+param[1]+"' />");
                        }
                    }
                }
                win.document.write("</form>");
                win.document.write("<scr"+"ipt>");
                win.document.write("document.forms[0].submit();");

                win.document.write("</scr"+"ipt>");
                win.document.write("</body></html>");
            },
            scope: this
        },<?}?>
        <? if($view['settings']->getSetting("PDFExport") == 'true'){ ?>
        {
            iconCls:'pdf-merge',
            //hidden: <? if($view['settings']->getSetting("PDFExport") == 'true'){echo 'false';} else{echo 'true';}?>,
            tooltip:'<?php echo $view['translator']->trans('Export PDF'); ?>',
            cls: 'x-btn-icon',
            handler: function(){
                var params = lastParams<?php echo $containerName; ?>;
                var store = mainGrid<?php echo $containerName; ?>.store;

                var addparams = "&";
                if (store.sortInfo !== null && typeof store.sortInfo !== 'undefined') {
                    var sortdir = store.sortInfo.direction;
                    var sortfield = store.sortInfo.field;
                    addparams = "sort="+sortfield+"&dir="+sortdir+"&";
                }

                $.ajax({
                    cache: false,
                    url : '<?php echo $view['router']->generate('ifresco_client_grid_export_background_pdf_result_set') ?>',
                    timeout : 1200000,
                    type:"POST",
                    data:addparams + object2string(params.params),
                    success : function (data) {
                        $("#overAll").unmask();
                        jsonData = $.JSON.decode(data);
                        if (jsonData.success == true) {
                            var fileName = jsonData.fileName;
                            window.open('<?php echo $view['router']->generate('ifresco_client_grid_export_download_pdf_result_set') ?>?fileName='+fileName);
                        }
                        else {
                            Ext.MessageBox.show({
                                title: '<?php echo $view['translator']->trans('Generating PDF'); ?>',
                                msg: '<?php echo $view['translator']->trans('An error occured on the generating process! Please try it again later.'); ?>',
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.INFO
                            });
                        }
                    },
                    beforeSend: function(req) {
                        $("#overAll").mask("<?php echo $view['translator']->trans('Generating PDF'); ?>...",300);
                    }
                });
            },
            scope: this
        },<?}?>'-',
        <?php if ($isClipBoard == false) { ?>
        {
            iconCls:'pasteCopy-clipboard',
            tooltip:'<?php echo $view['translator']->trans('Paste clipboard (copy)'); ?>',
            id:'pastecopy-clipboard<?php echo $containerName; ?>',
            cls: 'x-btn-icon',
            handler: function(){
                pasteClipBoard<?php echo $containerName; ?>('copy');
            },
            scope: this
        },{
            iconCls:'pasteCut-clipboard',
            tooltip:'<?php echo $view['translator']->trans('Paste clipboard (cut)'); ?>',
            id:'pastecut-clipboard<?php echo $containerName; ?>',
            handler: function(){
                pasteClipBoard<?php echo $containerName; ?>('cut');
            },
            scope: this
        },{
            iconCls:'pasteLink-clipboard',
            tooltip:'<?php echo $view['translator']->trans('Paste clipboard (as link)'); ?>',
            id:'pastelink-clipboard<?php echo $containerName; ?>',
            cls: 'x-btn-icon',
            handler: function(){
                pasteClipBoard<?php echo $containerName; ?>('link');
            },
            scope: this
        }
        <?php } else { ?>
        {
            iconCls:'remove-clipboard',
            tooltip:'<?php echo $view['translator']->trans('Clear Clipboard'); ?>',
            id:'clear-clipboard<?php echo $containerName; ?>',
            cls: 'x-btn-icon',
            handler: function(){
                ClipBoard.clearItems();
                ClipBoard.reloadClip();
            },
            scope: this
        }

        <?php } ?>
        ,'-',{
            iconCls:'refresh-meta',
            tooltip:'<?php echo $view['translator']->trans('Refresh'); ?>',
            cls: 'x-btn-icon',
            handler: function(){
                refreshGrid<?php echo $containerName; ?>();
            },
            scope: this
        },

        '-',
        {
            tooltip: 'Print',
            iconCls: 'grid-print',
            cls: 'x-btn-icon',
            handler : function(){
                Ext.ux.grid.Printer.mainTitle = "<?php echo $view['translator']->trans('Printout'); ?>";
                Ext.ux.grid.Printer.stylesheetPath = "<?php echo $view['assets']->getUrl('/js/extjs4-ux-gridprinter/ux/grid/gridPrinterCss/print.css') ?>";
                Ext.ux.grid.Printer.printAutomatically = false;
                Ext.ux.grid.Printer.print(grid<?php echo $containerName; ?>);
            }
        },
        <?php if($view['settings']->getSetting("openInAlfresco") == "true" || $isAdmin) {?>
        {
            iconCls:'open-alfresco',
            id: 'open-alfresco<?php echo $containerName; ?>',
            tooltip:'<?php echo $view['translator']->trans('Open Folder in Alfresco'); ?>',
            cls: 'x-btn-icon',
            handler: function(){
                window.open(detailUrl<?php echo $containerName; ?>);
            },
            scope: this
        },
        <?php } ?>
        '->',
        /*{
         iconCls:'thumbnail-view',
         tooltip:'<?php echo $view['translator']->trans('Thumbnail-view'); ?>',
         cls: 'x-btn-icon',
         handler: function(){
         mainGrid<?php echo $containerName; ?>.hide();
         mainThumbView<?php echo $containerName; ?>.show();
         loadThumbnailView<?php echo $containerName; ?>();
         },
         scope: this
         },*/
        {
            xtype: 'switchbuttonsegment',
            activeItem: 0,
            scope: this,
            items: [{
                tooltip: '<?php echo $view['translator']->trans('Details'); ?>',
                viewMode: 'default',
                iconCls: 'icon-default'
            }, {
                tooltip: '<?php echo $view['translator']->trans('Tiles'); ?>',
                viewMode: 'tileIcons',
                iconCls: 'icon-tile'
            }, {
                tooltip: '<?php echo $view['translator']->trans('Icons'); ?>',
                viewMode: 'mediumIcons',
                iconCls: 'icon-medium'
            }],
            listeners: {
                change: function(btn, item)
                {
                    mainGrid<?php echo $containerName; ?>.features[0].setView(btn.viewMode);
                },
                scope: this
            }
        }];

    var grid<?php echo $containerName; ?> = Ext.create('Ext.grid.Panel', {
        //loadMask: {msg:'<?php echo $view['translator']->trans('Loading Documents...'); ?>'},
        loadMask: new Ext.LoadMask(this, {msg:'<?php echo $view['translator']->trans('Please wait...'); ?>'}),
        layout:'fit',
        deferEmptyText: false,
        emptyText: '<img src="/images/icons/information.png" align="absmiddle"> <?php echo $view['translator']->trans('No items to display.'); ?>',
        multiSelect: true,
        //renderTo: 'documentGrid',
        //height:'auto',
        listeners: {
            itemcontextmenu: contextMenuFunc<?php echo $containerName; ?>
        },
        store: store<?php echo $containerName; ?>,
        columns: [<?php echo html_entity_decode($columns,ENT_QUOTES); ?>],

        //features: Ext.create("Ext.grid.feature.Grouping", {}),
        tbar: tBar<?php echo $containerName; ?>,
        bbar: Ext.create('Ext.PagingToolbar', {
            store: store<?php echo $containerName; ?>,
            displayInfo: true,
            displayMsg: '{0} - {1} <?php echo $view['translator']->trans('of'); ?> {2}',
            emptyMsg: "",
            listeners: {
                beforechange:function(toolbar,pageData) {
                    var newbaseParams = lastParams<?php echo $containerName; ?>.params;
                    Ext.apply(store<?php echo $containerName; ?>.baseParams, newbaseParams);
                },
                change:function(toolbar,pageData) {

                    if (pageData && pageData.total <= this.pageSize && pageData.pages < 2) {

                        store<?php echo $containerName; ?>.remoteSort = <?php echo (empty($DefaultSort) || $DefaultSort == null ? 'false' : 'true'); ?>;
                    }
                    else
                        store<?php echo $containerName; ?>.remoteSort = true;
                    //store<?php echo $containerName; ?>.remoteSort = false;
                }
            }
        }),
        viewConfig: {
            forceFit:true,
            stripeRows: true,
            chunker: Ext.view.TableChunker
        },
        //collapsible: true,
        collapsible: false, // maybe this fix that the toolbar dont hide
        //animCollapse: true,
        animCollapse: false, // maybe this fix that the toolbar dont hide
        header: false,
        id: 'dataGrid<?php echo $containerName; ?>',
        iconCls: 'icon-grid',
        stateId :'documentGrid-stateid-'+currentColumnsetid<?php echo $containerName; ?>,
        //stateId :'documentGrid-stateid<?php echo $containerName; ?>',
        stateful : true,
        plugins: [Ext.create('Ext.ux.grid.plugin.DragSelector')],
        features: [ Ext.create('Ext.ux.grid.feature.Tileview', {
            viewMode: 'default',
            getAdditionalData: function(data, index, record, orig)
            {
                if(this.viewMode)
                {
                    var thumbnail_medium, thumbnail;
                    var nodeType = data.alfresco_type;
                    if (nodeType === "{http://www.alfresco.org/model/content/1.0}folder") {
                        thumbnail_medium = "/images/folder_thumbnail_medium.png";
                        thumbnail = "/images/folder_thumbnail.png";
                    }
                    else {
                        thumbnail_medium = data.alfresco_thumbnail_medium;
                        thumbnail = data.alfresco_thumbnail;
                    }
                    return {
                        thumbnail_medium:thumbnail_medium,
                        thumbnail:thumbnail,
                        name_blank: data.alfresco_name_blank,
                        name: data.alfresco_name
                    };
                }
                return {};
            },
            viewTpls:
            {
                mediumIcons: [
                    '<td class="{cls} ux-explorerview-medium-icon-row" title="{name_blank}">',
                    '<table class="x-grid-row-table">',
                    '<tbody>',
                    '<tr>',
                    //'<td class="x-grid-col x-grid-cell ux-explorerview-icon" style="background: url(&quot;/js/extjs-ux/example/grid/thumbnails/medium_{thumbnails}&quot;) no-repeat scroll 50% 100% transparent;">',
                    '<td class="x-grid-col x-grid-cell ux-explorerview-icon" style="background: url(&quot;{thumbnail}&quot;) no-repeat scroll transparent;">',
                    '</td>',
                    '</tr>',
                    '<tr>',
                    '<td class="x-grid-col x-grid-cell">',
                    '<div class="x-grid-cell-inner" unselectable="on">{name}</div>',
                    '</td>',
                    '</tr>',
                    '</tbody>',
                    '</table>',
                    '</td>'].join(''),

                tileIcons: [
                    '<td class="{cls} ux-explorerview-detailed-icon-row" title="{name_blank}">',
                    '<table class="x-grid-row-table">',
                    '<tbody>',
                    '<tr>',
                    //'<td class="x-grid-col x-grid-cell ux-explorerview-icon" style="background: url(&quot;/js/extjs-ux/example/grid/thumbnails/tile_{thumbnails}&quot;) no-repeat scroll 50% 50% transparent;">',
                    '<td class="x-grid-col x-grid-cell ux-explorerview-icon" style="background: url(&quot;{thumbnail_medium}&quot;) no-repeat scroll 50% 50% transparent;">',
                    '</td>',

                    '<td class="x-grid-col x-grid-cell">',
                    '<div class="x-grid-cell-inner" unselectable="on">{name}<br></div>',
                    '</td>',
                    '</tr>',
                    '</tbody>',
                    '</table>',
                    '</td>'].join('')

            }
        }), Ext.create("Ext.grid.feature.Grouping", {})]
    });

    mainGrid<?php echo $containerName; ?> = grid<?php echo $containerName; ?>;

    var thumbView<?php echo $containerName; ?> = Ext.create('Ext.panel.Panel', {
        header:false,
        frame:false,
        border:false,
        layout:'fit',
        height:'100%',
        id: 'thumbnailGrid<?php echo $containerName; ?>',
        tbar: ['->', {
            iconCls:'list-view',
            tooltip:'<?php echo $view['translator']->trans('Listview'); ?>',
            cls: 'x-btn-icon',
            handler: function(){
                mainThumbView<?php echo $containerName; ?>.hide();
                mainGrid<?php echo $containerName; ?>.show();
                //refreshGrid<?php echo $containerName; ?>();
            },
            scope: this
        }],
        autoScroll:false,
        html:'<div id="thumbnailContainer<?php echo $containerName; ?>" style="background-color:#000;height:100%;overflow:scroll;"><div id="toppics"></div><div class="toppicrow"></div><div class="toppicrow"></div><div class="toppicrow"></div></div>',
        hidden:true
    });

    mainThumbView<?php echo $containerName; ?> = thumbView<?php echo $containerName; ?>;


    // VERSIONS
    var VersionStore<?php echo $containerName; ?> = Ext.create('Ext.data.JsonStore', {
        proxy: {
            type:'ajax',
            url : "<?php echo $view['router']->generate('ifresco_client_versioning_get_json') ?>",
            reader: {root: 'versions'},
            //actionMethods: 'POST'
            actionMethods: { // changed in extjs4
                read: 'POST'
            }
        },
        fields: ['nodeRef', 'nodeId', 'version', 'description', {name:'date', type:'date', dateFormat:'timestamp'},'dateFormat', 'author'],
        listeners: {
            beforeload: function(store, operation, eOpts) {
                if(store.loadMask == undefined)
                    store.loadMask = new Ext.LoadMask(VersionlistView<?php echo $containerName; ?>, {msg:"Loading..."});
                store.loadMask.show();
            },
            load: function(store, records, successful, eOpts) {
                store.loadMask.hide();

                var versionPanel = Ext.getCmp('version-panel<?php echo $containerName; ?>');

                versionPanel.setDisabled(!store.data.items.length);
            }
        }
    });

    versionStore<?php echo $containerName; ?> = VersionStore<?php echo $containerName; ?>;

    var VersionlistView<?php echo $containerName; ?> = Ext.create('Ext.list.ListView', {
        store: VersionStore<?php echo $containerName; ?>,
        height:200,
        header:false,
        multiSelect: false,
        loadingText: '<?php echo $view['translator']->trans('Loading...'); ?>',
        deferEmptyText: false,
        emptyText: '<span style="font-size:12px;"><img src="/images/icons/information.png" align="absmiddle"> <?php echo $view['translator']->trans('This document has no version history.'); ?></span>',
        reserveScrollOffset: true,

        columns: [{
            header: '<?php echo $view['translator']->trans('Version'); ?>',
            dataIndex: 'version'
        },{
            header: '<?php echo $view['translator']->trans('Note'); ?>',
            dataIndex: 'description'
        },
            {
                header: '<?php echo $view['translator']->trans('Date'); ?>',
                xtype: 'datecolumn',
                format: '<?php echo $DateFormat; ?> <?php echo $TimeFormat; ?>',
                dataIndex: 'date'
            },
            {
                header: '<?php echo $view['translator']->trans('Author'); ?>',
                dataIndex: 'author'
            }],

        listeners: {
            itemcontextmenu: function(gridx, record, item, index, event, eOpts){
                var existingMenu = Ext.getCmp('version-ctx<?php echo $containerName; ?>');
                if (existingMenu !== null && typeof existingMenu !== 'undefined') {
                    existingMenu.destroy();
                }

                var selectedVersion = VersionStore<?php echo $containerName; ?>.getAt(index);
                var ParentNodeId = selectedVersion.data.nodeRef;
                var Version = selectedVersion.data.version;
                var VersionId = selectedVersion.data.nodeId;

                var selectedItem = Ext.getCmp('dataGrid<?php echo $containerName; ?>').getSelectionModel().getSelection()[0] || false;

                var editPerm = false;
                if(selectedItem)
                {
                    editPerm = selectedItem.data.alfresco_perm_edit;
                }

                event.stopEvent();
                var mnxContext = Ext.create('Ext.menu.Menu', {
                    id:'version-ctx<?php echo $containerName; ?>',
                    items: [{
                        iconCls: 'revert-version',
                        text: '<?php echo $view['translator']->trans('Revert to this Version'); ?>',
                        //disabled:(editRights === true ? false : true),  // TODO CHECK FOR RIGHTS HERE!
                        scope:this,
                        disabled: !editPerm,
                        handler: function(){
                            SelectedVersion<?php echo $containerName; ?> = {nodeId: ParentNodeId,versionNodeId:VersionId,version:Version};
                            Ext.MessageBox.show({
                                title: '<?php echo $view['translator']->trans('Revert to Version'); ?>',
                                msg: "<?php echo $view['translator']->trans('Are you sure you want to revert to Version:'); ?> <b>"+Version+"</b>",
                                buttons: Ext.MessageBox.YESNO,
                                icon: Ext.MessageBox.INFO,
                                fn:revertVersion<?php echo $containerName; ?>
                            });
                        }
                    },'-',{
                        iconCls: 'add-version',
                        text: '<?php echo $view['translator']->trans('New Version'); ?>',
                        scope: this,
                        disabled: !editPerm,
                        handler: function(){
                            SelectedVersion<?php echo $containerName; ?> = {nodeId: ParentNodeId,versionNodeId:VersionId,version:Version};
                            var data = {nodeId: ParentNodeId,versionNodeId:VersionId,version:Version};
                            createNewVersion<?php echo $containerName; ?>(data,false);
                        }
                    },
                        {
                            iconCls: 'upload-version',
                            text: '<?php echo $view['translator']->trans('Upload new Version'); ?>',
                            scope:this,
                            disabled: !editPerm,
                            handler: function(){
                                SelectedVersion<?php echo $containerName; ?> = {nodeId: ParentNodeId,versionNodeId:VersionId,version:Version};
                                var data = {nodeId: ParentNodeId,versionNodeId:VersionId,version:Version};
                                createNewVersion<?php echo $containerName; ?>(data,true);
                            }
                        },'-',{
                            iconCls: 'download-node',
                            text: '<?php echo $view['translator']->trans('Download this Version'); ?>',
                            scope: this,
                            handler: function(){
                                versionList<?php echo $containerName; ?>.fireEvent('itemdblclick', versionList<?php echo $containerName; ?>, null, null, index);
                            }
                        },'-',{
                            iconCls: 'version-panel',
                            cls: 'x-btn-icon',
                            text: '<?php echo $view['translator']->trans('Detailed Version Info'); ?>',
                            scope:this,
                            handler: function(){
                                SelectedVersion<?php echo $containerName; ?> = {nodeId: ParentNodeId};
                                var data = {nodeId: PanelNodeId<?php echo $containerName; ?>};
                                versionLookup<?php echo $containerName; ?>(data);
                            }
                        }]
                });
                mnxContext.showAt(event.xy);
            },
            itemdblclick: function(gridx, record, item, index, event, eOpts) {
                var nodeId = VersionStore<?php echo $containerName; ?>.getAt(index).data.nodeId;
                var dlUrl = '<?php echo $view['router']->generate('ifresco_client_versioning_download_version') ?>?nodeId='+nodeId;
                if ($.browser.msie) {
                    $(".PDFRenderer").hide();
                }
                window.open(dlUrl);
                $(".PDFRenderer").show();
            },
            selectionchange: function(t, selected, eOpts) {
                Ext.getCmp('download-selected-version<?php echo $containerName; ?>').setDisabled(!selected.length);

                var selectedItem = Ext.getCmp('dataGrid<?php echo $containerName; ?>').getSelectionModel().getSelection()[0] || false;

                var editPerm = false;
                if(selectedItem)
                {
                    editPerm = selectedItem.data.alfresco_perm_edit;
                }

                Ext.getCmp('revert-selected-version<?php echo $containerName; ?>').setDisabled(!selected.length || !editPerm);

            }

        }
    });


    var VersionPanel<?php echo $containerName; ?> = Ext.create('Ext.Panel', {
        id:'version-view<?php echo $containerName; ?>',
        width:'100%',
        height:versionSize,
        collapsible:false,
        header:false,
        layout:'fit',
        title:'<?php echo $view['translator']->trans('Versions'); ?>',
        tbar:[{
            iconCls: 'add-version',
            id: 'add-version<?php echo $containerName; ?>',
            cls: 'x-btn-icon',
            tooltip: '<?php echo $view['translator']->trans('New Version'); ?>',
            scope:this,
            handler: function(){
                SelectedVersion<?php echo $containerName; ?> = {nodeId: PanelNodeId<?php echo $containerName; ?>};
                var data = {nodeId: PanelNodeId<?php echo $containerName; ?>};
                createNewVersion<?php echo $containerName; ?>(data,false);
            }
        },
            {
                iconCls: 'upload-version',
                id: 'upload-version<?php echo $containerName; ?>',
                cls: 'x-btn-icon',
                tooltip: '<?php echo $view['translator']->trans('Upload new Version'); ?>',
                scope:this,
                handler: function(){
                    SelectedVersion<?php echo $containerName; ?> = {nodeId: PanelNodeId<?php echo $containerName; ?>};
                    var data = {nodeId: PanelNodeId<?php echo $containerName; ?>};
                    createNewVersion<?php echo $containerName; ?>(data,true);
                }
            },{
                iconCls: 'version-panel',
                id: 'version-panel<?php echo $containerName; ?>',
                cls: 'x-btn-icon',
                tooltip: '<?php echo $view['translator']->trans('Detailed Version Info'); ?>',
                scope:this,
                handler: function(){
                    SelectedVersion<?php echo $containerName; ?> = {nodeId: PanelNodeId<?php echo $containerName; ?>};
                    var data = {nodeId: PanelNodeId<?php echo $containerName; ?>};
                    versionLookup<?php echo $containerName; ?>(data);
                }
            },'-',{
                iconCls: 'download-node',
                id: 'download-selected-version<?php echo $containerName; ?>',
                tooltip: '<?php echo $view['translator']->trans('Download Selected Version'); ?>',
                scope: this,
                disabled: true,
                handler: function(){
                    versionList<?php echo $containerName; ?>.fireEvent('itemdblclick', versionList<?php echo $containerName; ?>, null, null, versionList<?php echo $containerName; ?>.getSelectionModel().getLastSelected().index);
                }
            },'-',{
                iconCls: 'revert-version',
                id: 'revert-selected-version<?php echo $containerName; ?>',
                tooltip: '<?php echo $view['translator']->trans('Revert to Selected Version'); ?>',
                scope: this,
                disabled: true,
                handler: function(){

                    var selectedVersion = versionList<?php echo $containerName; ?>.getSelectionModel().getLastSelected();

                    var ParentNodeId = selectedVersion.data.nodeRef;
                    var Version = selectedVersion.data.version;
                    var VersionId = selectedVersion.data.nodeId;

                    SelectedVersion<?php echo $containerName; ?> = {nodeId: ParentNodeId,versionNodeId:VersionId,version:Version};
                    Ext.MessageBox.show({
                        title: '<?php echo $view['translator']->trans('Revert to Version'); ?>',
                        msg: "<?php echo $view['translator']->trans('Are you sure you want to revert to Version:'); ?> <b>"+Version+"</b>",
                        buttons: Ext.MessageBox.YESNO,
                        icon: Ext.MessageBox.INFO,
                        fn:revertVersion<?php echo $containerName; ?>
                    });
                }
            }],
        items: VersionlistView<?php echo $containerName; ?>
    });

    versionList<?php echo $containerName; ?> = VersionlistView<?php echo $containerName; ?>;

    var previewTab<?php echo $containerName; ?> = {region: 'center',
        header:false,
        id:'previewContent<?php echo $containerName; ?>',
        maxSize: 150,
        html: '<div id="previewWindow<?php echo $containerName; ?>" style="height:100%;width:100%;"></div>'
    };

    var versionsTab<?php echo $containerName; ?> = {region: 'center',
        header:false,
        id:'versionsContent<?php echo $containerName; ?>',
        maxSize: 150,
        items:[VersionPanel<?php echo $containerName; ?>]
    };

    var relationsTab<?php echo $containerName; ?> = {region: 'center',
        header:false,
        id:'relationsContent<?php echo $containerName; ?>',
        maxSize: 150,
        html: '<div id="relationsWindow<?php echo $containerName; ?>" style="height:100%;width:100%;"></div>'
    };

    var metadataTab<?php echo $containerName; ?> = {region: 'center',
        header:false,
        id:'metadataContent<?php echo $containerName; ?>',
        maxSize: 150,
        html: '<div id="metadataWindow<?php echo $containerName; ?>" style="height:100%;width:100%;"></div>',
        tbar: [{
            iconCls:'view-metadata',
            id:'editMetadata<?php echo $containerName; ?>',
            tooltip:'<?php echo $view['translator']->trans('Edit Metadata'); ?>',
            disabled: true,
            cls: 'x-btn-icon',
            handler: function(){
                var nodeId = PanelNodeId<?php echo $containerName; ?>;
                var nodeName = PanelNodeText<?php echo $containerName; ?>;

                editMetadata(nodeId,nodeName);
            },
            scope: this
        },{
            iconCls:'manage-aspects',
            id:'manageAspects<?php echo $containerName; ?>',
            tooltip:'<?php echo $view['translator']->trans('Manage Aspects'); ?>',
            cls: 'x-btn-icon',
            disabled: true,
            handler: function(){
                var nodeId = PanelNodeId<?php echo $containerName; ?>;
                var nodeName = PanelNodeText<?php echo $containerName; ?>;

                manageAspects<?php echo $containerName; ?>(nodeId);
            },
            scope: this
        },{
            iconCls:'specify-type',
            tooltip:'<?php echo $view['translator']->trans('Specify Type'); ?>',
            cls: 'x-btn-icon',
            disabled: true,
            id:'specifyType<?php echo $containerName; ?>',
            handler: function(){
                var nodeId = PanelNodeId<?php echo $containerName; ?>;
                var specifyNode = [{nodeId:nodeId}];
                specifyType<?php echo $containerName; ?>(specifyNode);
            },
            scope: this
        },'-',{
            iconCls:'download-node',
            id:'downloadContent<?php echo $containerName; ?>',
            tooltip:'<?php echo $view['translator']->trans('Download'); ?>',
            cls: 'x-btn-icon',
            disabled: true,
            handler: function(){
                var nodeId = PanelNodeId<?php echo $containerName; ?>;
                var dlUrl = '<?php echo $view['router']->generate('ifresco_client_node_actions_download') ?>?nodeId='+nodeId;
                window.open(dlUrl);
            },
            scope: this
        },'-',{
            iconCls:'checkout-node',
            tooltip:'<?php echo $view['translator']->trans('Checkout'); ?>',
            cls: 'x-btn-icon',
            disabled: true,
            id:'checkout<?php echo $containerName; ?>',
            handler: function(){
                if (PanelNodeIsCheckedOut<?php echo $containerName; ?> === true) {
                    var nodeId = PanelNodeCheckedOutId<?php echo $containerName; ?>;
                    var mime = PanelNodeMimeType<?php echo $containerName; ?>;
                    //checkIn<?php echo $containerName; ?>(nodeId,mime);

                    SelectedVersion<?php echo $containerName; ?> = {nodeId: nodeId,mime:mime};
                    checkInWindow<?php echo $containerName; ?>(SelectedVersion<?php echo $containerName; ?>);
                }
                else {
                    var nodeId = PanelNodeId<?php echo $containerName; ?>;
                    var mime = PanelNodeMimeType<?php echo $containerName; ?>;
                    checkOut<?php echo $containerName; ?>(nodeId,mime);
                }


            },
            scope: this
        },{
            iconCls:'zoho-writer',
            tooltip:'<?php echo $view['translator']->trans('Checkout to Zoho Writer'); ?>',
            cls: 'x-btn-icon',
            disabled: true,
            id:'checkoutZoho<?php echo $containerName; ?>',
            hidden:<?php echo ((isset($OnlineEditing) && $OnlineEditing === "zoho") ? 'false' : 'true');?>,
            handler: function() {
                if (PanelNodeIsCheckedOut<?php echo $containerName; ?> === true) {
                    var nodeId = PanelNodeCheckedOutId<?php echo $containerName; ?>;
                    var mime = PanelNodeMimeType<?php echo $containerName; ?>;
                    editInZoho<?php echo $containerName; ?>(nodeId,mime);
                }
                else {
                    var nodeId = PanelNodeId<?php echo $containerName; ?>;
                    var mime = PanelNodeMimeType<?php echo $containerName; ?>;
                    checkOutZoho<?php echo $containerName; ?>(nodeId,mime);
                }
            },
            scope: this
        },{
            iconCls:'cancel-checkout',
            tooltip:'<?php echo $view['translator']->trans('Cancel Checkout'); ?>',
            cls: 'x-btn-icon',
            id:'cancel-checkout<?php echo $containerName; ?>',
            hidden:true,
            disabled: true,
            handler: function(){
                if (PanelNodeIsCheckedOut<?php echo $containerName; ?> === true) {
                    var nodeId = PanelNodeCheckedOutId<?php echo $containerName; ?>;
                    var orgNodeId = PanelNodeOrgId<?php echo $containerName; ?>;
                    var mime = PanelNodeMimeType<?php echo $containerName; ?>;
                    cancelCheckout<?php echo $containerName; ?>(nodeId,orgNodeId,mime);
                }
            },
            scope: this
        },'-',{
            iconCls:'refresh-meta',
            disabled: true,
            tooltip:'<?php echo $view['translator']->trans('Refresh'); ?>',
            cls: 'x-btn-icon',
            id:'refresh-meta<?php echo $containerName; ?>',
            handler: function(){
                var nodeId = PanelNodeId<?php echo $containerName; ?>;
                loadMetaData<?php echo $containerName; ?>(nodeId);
            },
            scope: this
        },'-',{
            /*xtype:'copybutton',
             renderTo: Ext.get('copy-button<?php echo $containerName; ?>'),  */
            iconCls:'copy-link',
            disabled: true,
            tooltip:'<?php echo $view['translator']->trans('Copy Link'); ?>',
            cls: 'x-btn-icon',
            id:'copy-link<?php echo $containerName; ?>',
            handler: function() {
                if (PanelNodeIsCheckedOut<?php echo $containerName; ?> === true) {
                    var nodeId = PanelNodeCheckedOutId<?php echo $containerName; ?>;
                }
                else {
                    var nodeId = PanelNodeId<?php echo $containerName; ?>;
                }

                if (PanelNodeType<?php echo $containerName; ?> == "{http://www.alfresco.org/model/content/1.0}folder")
                    ret = "<?php echo $view['router']->generate('ifresco_client_index',array(),true) ?>#folder/workspace://SpacesStore/"+nodeId;
                else
                    ret = "<?php echo $view['router']->generate('ifresco_client_index',array(),true) ?>#document/workspace://SpacesStore/"+nodeId;
                clip = new ZeroClipboard.Client();
                clip.setText( ret );
                clip.glue(this.getEl().dom);
            }
        }]
    };

    var parentMetadataTab<?php echo $containerName; ?> = {region: 'center',
        header:false,
        id:'parentMetadataContent<?php echo $containerName; ?>',
        maxSize: 150,
        html: '<div id="parentMetadataWindow<?php echo $containerName; ?>" style="height:100%;width:100%;"></div>'
    };

    var commentsTab<?php echo $containerName; ?> = {region: 'center',
        header:false,
        id:'commentsContent<?php echo $containerName; ?>',
        maxSize: 150,
        html: '<div id="commentsWindow<?php echo $containerName; ?>" style="height:100%;width:100%;"></div>'
    };

    var contentTab<?php echo $containerName; ?> = {};


    grid<?php echo $containerName; ?>.on('itemdblclick', function(model, view, e, rowIndex) {
        isDblClick<?php echo $containerName; ?> = true;

        jQuery14.manageAjax.abort('metadata'); // abort on dbl click

        var nodeId = view.data.nodeId;
        var type = view.data.alfresco_type;

        var nodeText = view.data.alfresco_name;

        if (type !== "{http://www.alfresco.org/model/content/1.0}folder") {
            var url = view.data.alfresco_url;

            window.open(url);
        }
        else {
            if (e.shiftKey == true) {
                var tabnodeid = nodeId.replace(/-/g,"");
                addTabDynamic('tab-'+tabnodeid,nodeText);
                $.ajax({
                    cache: false,
                    url : "<?php echo $view['router']->generate('ifresco_client_data_grid_index') ?>?containerName="+tabnodeid+"&addContainer=<?php echo $nextContainer; ?>&columnsetid="+currentColumnsetid<?php echo $containerName; ?>,
                    success : function (data) {
                        $("#overAll").unmask();
                        $("#tab-"+tabnodeid).html(data);

                        eval("reloadGridData"+tabnodeid+"({params:{'nodeId':nodeId,'columnsetid':currentColumnsetid<?php echo $containerName; ?>}});");
                    },
                    beforeSend: function(req) {
                        $("#overAll").mask("<?php echo $view['translator']->trans('Loading'); ?> "+nodeText+"...",300);
                    }
                });
            }
            else {
                eval("reloadGridData<?php echo $containerName; ?>({params:{'nodeId':nodeId,'columnsetid':currentColumnsetid<?php echo $containerName; ?>}});");
            }
        }

        window.setTimeout(function(){
            isDblClick<?php echo $containerName; ?> = false;
        }, 0)
    });


//itemclick
    grid<?php echo $containerName; ?>.on('select', function(model, view, rowIndex, e) {

        if(Ext.EventObject.button != 2)
            window.setTimeout(function(){
                if (!isDblClick<?php echo $containerName; ?>) {

                    var store<?php echo $containerName; ?> = mainGrid<?php echo $containerName; ?>.store;

                    var nodeId = view.data.nodeId;
                    var nodeRef = view.data.nodeRef;
                    var type = view.data.alfresco_type;
                    var nodeText = view.data.alfresco_name;
                    var nodeUrl = view.data.alfresco_url;

                    var mainPreviewTab = Ext.getCmp('mainPreviewTab<?php echo $containerName; ?>');
                    var mainVersionTab = Ext.getCmp('mainVersionsTab<?php echo $containerName; ?>');
                    var mainMetadataTab = Ext.getCmp('mainMetadataTab<?php echo $containerName; ?>');
                    var mainParentMetadataTab = Ext.getCmp('mainParentMetadataTab<?php echo $containerName; ?>');

                    var deletedSource = (nodeId == nodeRef ) && (type === "{http://www.alfresco.org/model/application/1.0}filelink");

                    // mimetype
                    var MimeType = view.data.alfresco_mimetype;

                    // RIGHTS
                    var editRights = view.data.alfresco_perm_edit;
                    var delRights = view.data.alfresco_perm_delete;
                    var cancelCheckoutRights = view.data.alfresco_perm_cancel_checkout;
                    var createRights = view.data.alfresco_perm_create;
                    var hasRights = view.data.alfresco_perm_permissions;

                    // CHECKOUT LOGIC
                    var isWorkingCopy = view.data.alfresco_isWorkingCopy;
                    var isCheckedOut = view.data.alfresco_isCheckedOut;
                    var originalId = view.data.alfresco_originalId;
                    var workingCopyId = view.data.alfresco_workingCopyId;

                    // BUTTONS
                    var editMetaDataBtn = Ext.getCmp('editMetadata<?php echo $containerName; ?>');
                    var manageAspectsBtn = Ext.getCmp('manageAspects<?php echo $containerName; ?>');
                    var specifyTypeBtn = Ext.getCmp('specifyType<?php echo $containerName; ?>');
                    var checkoutBtn = Ext.getCmp('checkout<?php echo $containerName; ?>');
                    var cancelCheckoutBtn = Ext.getCmp('cancel-checkout<?php echo $containerName; ?>');
                    var refreshMetaBtn = Ext.getCmp('refresh-meta<?php echo $containerName; ?>');
                    var copyLinkBtn = Ext.getCmp('copy-link<?php echo $containerName; ?>');
                    var checkoutZohoBtn = Ext.getCmp('checkoutZoho<?php echo $containerName; ?>');
                    var downloadContent = Ext.getCmp('downloadContent<?php echo $containerName; ?>');
                    var addVersionBtn = Ext.getCmp('add-version<?php echo $containerName; ?>');
                    var uploadVersionBtn = Ext.getCmp('upload-version<?php echo $containerName; ?>');
                    var uploadContentFolderBtn = Ext.getCmp('upload-content-folder<?php echo $containerName; ?>');
                    var createFolderFolderBtn = Ext.getCmp('create-folder-folder<?php echo $containerName; ?>');

                    refreshMetaBtn.enable();
                    copyLinkBtn.enable();


                    PanelNodeId<?php echo $containerName; ?> = nodeId;
                    PanelNodeMimeType<?php echo $containerName; ?> = MimeType;
                    PanelNodeText<?php echo $containerName; ?> = nodeText;
                    PanelNodeType<?php echo $containerName; ?> = type;
                    PanelNodeUrl<?php echo $containerName; ?> = nodeUrl;
                    PanelNodeIsCheckedOut<?php echo $containerName; ?> = (isWorkingCopy === true || isCheckedOut === true ? true : false);
                    if (isWorkingCopy === true || isCheckedOut === true) {
                        var tempid = nodeId;
                        var orgtempid = originalId;
                        if (isCheckedOut === true) {
                            tempid = workingCopyId;
                        }

                        PanelNodeOrgId<?php echo $containerName; ?> = orgtempid;
                        PanelNodeCheckedOutId<?php echo $containerName; ?> = tempid;
                    }
                    else
                        PanelNodeCheckedOutId<?php echo $containerName; ?> = null;

                    var tabPanel = Ext.getCmp("previewPanel<?php echo $containerName; ?>");
                    var activeTabId = "";
                    if (tabPanel) {
                        var activeTab = tabPanel.activeTab;
                        if (typeof activeTab !== 'undefined') {
                            //activeTabTitle = activeTab.title;
                            //activeTabId = activeTabId.toLowerCase();
                            activeTabId = activeTab.id
                        }
                    }

                    if(deletedSource) {
                        Ext.MessageBox.alert('<?php echo $view['translator']->trans('Checkout'); ?>', '<?php echo $view['translator']->trans('Source document has beed deleted. No data to represent.'); ?>');
                        mainPreviewTab.disable();
                        mainVersionTab.disable();
                        mainMetadataTab.disable();
                        if(mainParentMetadataTab)
                            mainParentMetadataTab.disable();
                        return;
                    }else if (type !== "{http://www.alfresco.org/model/content/1.0}folder"/* && nodeRef == nodeId*/) {

                        mainPreviewTab.enable();
                        mainVersionTab.enable();
                        mainMetadataTab.enable();
                        if(mainParentMetadataTab)
                            mainParentMetadataTab.enable();

                        downloadContent.enable();
                        // CHECK RIGTHS
                        if (editRights === true) {
                            editMetaDataBtn.enable();
                            manageAspectsBtn.enable();
                            specifyTypeBtn.enable();
                            checkoutBtn.enable();
                            addVersionBtn.enable();
                            uploadVersionBtn.enable();

                            <?php if (isset($OnlineEditing) && $OnlineEditing === "zoho") { ?>
                            if (jQuery.inArray(MimeType,ZohoMimeDocs)>=0) {
                                if (isWorkingCopy === true || isCheckedOut === true)
                                    checkoutZohoBtn.setTooltip("<?php echo $view['translator']->trans('Edit in Zoho Writer'); ?>");
                                else
                                    checkoutZohoBtn.setTooltip("<?php echo $view['translator']->trans('Checkout in Zoho Writer'); ?>");
                                checkoutZohoBtn.enable();
                                checkoutZohoBtn.setVisible(true);
                            }
                            else if (jQuery.inArray(MimeType,ZohoMimeSheet)>=0) {
                                if (isWorkingCopy === true || isCheckedOut === true)
                                    checkoutZohoBtn.setTooltip("<?php echo $view['translator']->trans('Edit in Zoho Sheet'); ?>");
                                else
                                    checkoutZohoBtn.setTooltip("<?php echo $view['translator']->trans('Checkout in Zoho Sheet'); ?>");
                                checkoutZohoBtn.enable();
                                checkoutZohoBtn.setVisible(true);
                            }
                            else {
                                checkoutZohoBtn.disable();
                                checkoutZohoBtn.setVisible(false);
                            }
                            <?php } ?>

                            if (isWorkingCopy === true || isCheckedOut === true) {
                                checkoutBtn.setTooltip("Checkin");
                                checkoutBtn.setIconCls("checkin-node");

                                specifyTypeBtn.disable();
                                if (isWorkingCopy !== true) {
                                    editMetaDataBtn.disable();
                                    manageAspectsBtn.disable();
                                    specifyTypeBtn.disable();
                                }

                                cancelCheckoutBtn.enable();
                                cancelCheckoutBtn.setVisible(true);
                            }
                            else {
                                checkoutBtn.setTooltip("Checkout");
                                checkoutBtn.setIconCls("checkout-node");

                                cancelCheckoutBtn.disable();
                                cancelCheckoutBtn.setVisible(false);
                            }
                        }
                        else {
                            editMetaDataBtn.disable();
                            addVersionBtn.disable();
                            uploadVersionBtn.disable();
                            manageAspectsBtn.disable();
                            specifyTypeBtn.disable();
                            checkoutBtn.disable();
                            checkoutZohoBtn.disable();

                            cancelCheckoutBtn.disable();
                            cancelCheckoutBtn.setVisible(false);
                        }

                        if (activeTabId === "mainPreviewTab<?php echo $containerName; ?>")
                            loadPreview<?php echo $containerName; ?>(nodeId);
                        else if (activeTabId === "mainVersionsTab<?php echo $containerName; ?>")
                            versionList<?php echo $containerName; ?>.store.load({params:{'nodeId':nodeId}});

                    }
                    else {
                        try {
                            mainPreviewTab.enable();
                            mainMetadataTab.enable();
                            mainVersionTab.disable();
                            if(mainParentMetadataTab)
                                mainParentMetadataTab.<? if($view['settings']->getSetting("ParentMetaDocumentOnly") == "true" ) {?>disable()<?} else {?>enable()<?}?>;




                            if (editRights === true) {
                                editMetaDataBtn.enable();
                                manageAspectsBtn.enable();
                                specifyTypeBtn.enable();
                            }
                            else {
                                editMetaDataBtn.disable();
                                manageAspectsBtn.disable();
                                specifyTypeBtn.disable();
                            }

                            checkoutBtn.disable();
                            checkoutZohoBtn.disable();
                            downloadContent.disable();
                        }
                        catch (err) {
                        }


                        $("#previewWindow<?php echo $containerName; ?>").html('');
                        //$("#metadataWindow<?php echo $containerName; ?>").html('');

                        if (activeTabId === "mainPreviewTab<?php echo $containerName; ?>") {
                            /*if(type == "{http://www.alfresco.org/model/content/1.0}content")
                             loadPreview<?php echo $containerName; ?>(nodeId);
                             else*/
                            loadFolderPreview<?php echo $containerName; ?>(nodeId);
                        } else
                        if (activeTabId === "mainVersionsTab<?php echo $containerName; ?>") {
                            //tabPanel.setActiveTab(0);
                            tabPanel.setActiveTab('mainMetadataTab<?php echo $containerName; ?>');
                        } else
                        if (activeTabId === "mainParentMetadataTab<?php echo $containerName; ?>") {
                            <? if($view['settings']->getSetting("ParentMetaDocumentOnly") == "true" ) {?>
                            tabPanel.setActiveTab('mainMetadataTab<?php echo $containerName; ?>');
                            activeTabId = "mainMetadataTab<?php echo $containerName; ?>";
                            <?} else {?>
                            loadParentMetaData<?php echo $containerName; ?>(nodeId);
                            <?}?>

                        } else if(activeTabId == '') {
                            //console.log('<?php echo $DefaultTab; ?>')
                        }

                    }

                    if (activeTabId === "mainMetadataTab<?php echo $containerName; ?>")
                        loadMetaData<?php echo $containerName; ?>(nodeId);
                    else if (activeTabId === "mainParentMetadataTab<?php echo $containerName; ?>")
                        loadParentMetaData<?php echo $containerName; ?>(nodeId);
                }
            }, 0);
    });


    var viewport<?php echo $containerName; ?> = Ext.create('Ext.panel.Panel', {
        layout:'border',
        header:false,
        width:'100%',
        id: 'viewport<?php echo $containerName; ?>',
        renderTo: 'documentGrid<?php echo $containerName; ?>',
        height:$("#documentGrid<?php echo $containerName; ?>").height(),
        split:true,
        items: [{
            region: docGridRegion,
            title: '<?php echo $view['translator']->trans('DocumentGrid'); ?>',
            id:'gridCenter<?php echo $containerName; ?>',
            header:false,
            layout:'fit',
            /*start breadcrumb*/
            tbar: viewportToolbar<?php echo $containerName; ?>,
            /*end breadcrumb*/
            items: [grid<?php echo $containerName; ?>,thumbView<?php echo $containerName; ?>],

            split: true,
            width:docGridWidth,
            height:docGridHeight
        },{
            border:false,
            xtype: 'tabpanel',
            id:'previewPanel<?php echo $containerName; ?>',
            plain: true,

            width:tabPrevWidth,
            height:tabPrevHeight,
            region: tabPrevRegion,

            tabPosition: tabPrevTabPos,
            activeTab: '<?php echo $DefaultTab; ?><?php echo $containerName; ?>',

            listeners: {
                'tabchange': function(tabPanel, tab){
                    if (typeof tab !== 'undefined') {
                        var title = tab.title;
                        var tabId = tab.id;
                        //title = title.toLowerCase();

                        var nodeId = PanelNodeId<?php echo $containerName; ?>;
                        var nodeType = PanelNodeType<?php echo $containerName; ?>;

                        <? if($view['settings']->getSetting("MetaOnTreeFolder") == 'true'){?>
                        if (nodeId == null || typeof nodeId == 'undefined')
                            nodeId = currentNodeId<?php echo $containerName; ?>;
                        <?}?>

                        if (typeof nodeId !== 'undefined' && nodeId !== null) {
                            switch(tabId) {
                                case "mainParentMetadataTab<?php echo $containerName; ?>":
                                    loadParentMetaData<?php echo $containerName; ?>(nodeId);
                                    break;
                                case "mainMetadataTab<?php echo $containerName; ?>":
                                    loadMetaData<?php echo $containerName; ?>(nodeId);
                                    break;
                                case "mainPreviewTab<?php echo $containerName; ?>":
                                    if (nodeType !== "{http://www.alfresco.org/model/content/1.0}folder")
                                        loadPreview<?php echo $containerName; ?>(nodeId);
                                    else {
                                        loadFolderPreview<?php echo $containerName; ?>(nodeId);
                                    }
                                    break;
                                case "mainVersionsTab<?php echo $containerName; ?>":
                                    //$("#versionsContent<?php echo $containerName; ?>").html('');
                                    if (nodeType === "{http://www.alfresco.org/model/content/1.0}folder") {
                                        var tabPanel = Ext.getCmp("previewPanel<?php echo $containerName; ?>");
                                        if (tabPanel)
                                            tabPanel.setActiveTab(0);
                                    }
                                    else {
                                        versionList<?php echo $containerName; ?>.store.load({params:{'nodeId':nodeId}});
                                    }
                                    break;
                            }
                        }
                    }
                }
            },

            items: [{
                title: '<?php echo $view['translator']->trans('Preview'); ?>',
                id:'mainPreviewTab<?php echo $containerName; ?>',
                cls: 'inner-tab-custom',
                layout: 'border',
                deferredRender:false,
                defaults: Ext.apply({},Ext.isGecko? {style:{position:'absolute'},hideMode:'visibility'}:false),
                items: [previewTab<?php echo $containerName; ?>]
            },
                {
                    title: '<?php echo $view['translator']->trans('Versions'); ?>',
                    id:'mainVersionsTab<?php echo $containerName; ?>',
                    cls: 'inner-tab-custom',
                    layout: 'border',
                    deferredRender:false,
                    defaults: Ext.apply({},Ext.isGecko? {style:{position:'absolute'},hideMode:'visibility'}:false),
                    items: [versionsTab<?php echo $containerName; ?>]

                }<? if($view['settings']->getSetting("ParentNodeMeta") == "true" ) {?> ,
                {
                    title: '<?php echo $view['translator']->trans('Parent Meta'); ?>',
                    id:'mainParentMetadataTab<?php echo $containerName; ?>',
                    cls: 'inner-tab-custom',
                    layout: 'border',
                    deferredRender:false,
                    defaults: Ext.apply({},Ext.isGecko? {style:{position:'absolute'},hideMode:'visibility'}:false),
                    items: [parentMetadataTab<?php echo $containerName; ?>]
                }<?}?>,
                {
                    title: '<?php echo $view['translator']->trans('Metadata'); ?>',
                    id:'mainMetadataTab<?php echo $containerName; ?>',
                    cls: 'inner-tab-custom',
                    layout: 'border',
                    deferredRender:false,
                    defaults: Ext.apply({},Ext.isGecko? {style:{position:'absolute'},hideMode:'visibility'}:false),
                    items: [metadataTab<?php echo $containerName; ?>]
                }
            ]
        }]
    });

    uploadPanel<?php echo $containerName; ?> = Ext.create('Ext.ux.upload.IfsWindow', {
        modal:true,
        layout:'fit',
        width:500,
        height:360,
        closeAction:'hide',
        title: '<?php echo $view['translator']->trans('Upload File(s)'); ?>',
        plain: true,
        constrain: true,
        uploader: {
            url: '<?php echo $view['router']->generate('ifresco_client_upload_rest'); ?>?nodeId='+currentNodeId<?php echo $containerName; ?>+'&overwrite=false&ocr=false',
            uploadpath: '<?php echo $view['router']->generate('ifresco_client_upload_rest'); ?>?nodeId='+currentNodeId<?php echo $containerName; ?>+'&overwrite=false&ocr=false',
            filters : [
                {title : "<?php echo $view['translator']->trans('General'); ?>", extensions : "<?php echo $view['settings']->getSetting("uploadAllowedTypes") ? implode(',', json_decode($view['settings']->getSetting("uploadAllowedTypes"))) : ''; ?>"}
            ]
        },
        listeners: {
            beforestart: function(uploader, files) {
                uploader.uploader.settings.uploadpath = uploader.uploader.settings.url = '<?php echo $view['router']->generate('ifresco_client_upload_rest'); ?>?nodeId='+currentNodeId<?php echo $containerName; ?>+'&overwrite=false&ocr=false';
            },
            uploadcomplete: function(uploader, files) {
                uploadPanel<?php echo $containerName; ?>.uploadComposed = false;
                if(uploadPanel<?php echo $containerName; ?>.autoCloseControl.getValue() && uploadPanel<?php echo $containerName; ?>._dropboxFiles.length == 0) {
                    uploadPanel<?php echo $containerName; ?>.hide();
                }

                if(uploadPanel<?php echo $containerName; ?>._dropboxFiles.length == 0)
                    refreshGrid<?php echo $containerName; ?>();
            }
        },
        buttons: [{
            text: '<?php echo $view['translator']->trans('Close'); ?>',
            handler: function(btn,e) {
                btn.ownerCt.ownerCt.hide();
            }
        }]
    });


    function loadFolderPreview<?php echo $containerName; ?>(nodeId) {
        Ext.define('FolderGridStoreModel', {
            extend: 'Ext.data.Model',
            fields: [<?php echo html_entity_decode($fields,ENT_QUOTES); ?>]
        });

        var folderStore<?php echo $containerName; ?> = Ext.create('Ext.data.Store', {
            model: 'FolderGridStoreModel',
            proxy : {
                type: 'ajax',
                url: '<?php echo $view['router']->generate('ifresco_client_grid_data') ?>',
                //method: 'GET',
                actionMethods: { // changed in extjs4
                    read: 'GET'
                },
                reader: {
                    type: 'json',
                    idProperty:'nodeId',
                    remoteGroup:true,
                    remoteSort: true,
                    root: 'data'
                }
            },
            listeners:{
                beforeload: function(store, operation, eOpts) {
                    store.loadMask = new Ext.LoadMask(foldergrid<?php echo $containerName; ?>, {msg:"<?php echo $view['translator']->trans('Loading Documents...'); ?>"});
                    store.loadMask.show();
                },
                load: function(store, records, successful, eOpts) {
                    store.loadMask.hide();
                },
                datachanged:function(store,e) {

                    var json = store.getProxy().reader.jsonData;
                    var perms = json.perms;

                    if(perms) {
                        var uploadBtn       = Ext.getCmp("upload-content-folder<?php echo $containerName; ?>");
                        var createFolderBtn = Ext.getCmp("create-folder-folder<?php echo $containerName; ?>");

                        uploadBtn.setDisabled(!perms.alfresco_perm_create);
                        createFolderBtn.setDisabled(!perms.alfresco_perm_create);
                    }
                }
            }
            <?php if (!empty($DefaultSort) && $DefaultSort != null) { ?>
            ,sortOnLoad: true,
            sortInfo: {field: '<?php echo $DefaultSort; ?>', direction: '<?php echo $DefaultSortDir; ?>'},
            sorters: [{
                property: '<?php echo $DefaultSort; ?>',
                direction:'<?php echo $DefaultSortDir; ?>'
            }]
            <?php } ?>
        });

        var foldergrid<?php echo $containerName; ?> = Ext.create('Ext.grid.Panel', {
            loadMask: {msg:'<?php echo $view['translator']->trans('Loading Documents...'); ?>'},
            layout:'fit',
            store: folderStore<?php echo $containerName; ?>,
            columns: [<?php echo html_entity_decode($columns,ENT_QUOTES); ?>],
            /*view: Ext.create('Ext.grid.Panel', {
             forceFit:true,
             features: [Ext.create('Ext.grid.feature.Grouping')],
             showGroupName: false,
             enableNoGroups:true,
             enableGroupingMenu:true,
             hideGroupedColumn: false,
             deferEmptyText: false,
             emptyText: '<img src="/images/icons/information.png" align="absmiddle"> <?php echo $view['translator']->trans('No items to display.'); ?>'
             }),*/

            tbar: [
                {
                    iconCls:'upload-content',
                    id:'upload-content-folder<?php echo $containerName; ?>',
                    tooltip:'<?php echo $view['translator']->trans('Upload File(s)'); ?>',
                    hidden: true,
                    handler: function(){
                        if(!win<?php echo $containerName; ?>){
                            win<?php echo $containerName; ?> = Ext.create('Ext.window.Window', {
                                modal:true,
                                id:'upload-window<?php echo $containerName; ?>',
                                layout:'fit',
                                width:500,
                                height:360,
                                closeAction:'hide',
                                plain: true,
                                constrain: true,
                                items: Ext.create('Ext.panel.Panel', {
                                    id: 'upload-window-panel<?php echo $containerName; ?>',
                                    layout:'fit',
                                    border:false
                                }),

                                buttons: [{
                                    text: '<?php echo $view['translator']->trans('Close'); ?>',
                                    handler: function() {
                                        win<?php echo $containerName; ?>.hide(this);
                                        foldergrid<?php echo $containerName; ?>.getStore().load({params:{'nodeId':nodeId,'columnsetid':currentColumnsetid<?php echo $containerName; ?>}});
                                    }
                                }]
                            });
                        }

                        $.ajax({
                            cache: false,
                            url : "<?php echo $view['router']->generate('ifresco_client_upload') ?>",
                            data: ({'nodeId' : nodeId, 'containerName':'<?php echo $containerName; ?>'}),

                            success : function (data) {
                                $("#upload-window-panel<?php echo $containerName; ?>").html(data);
                            }
                        });

                        win<?php echo $containerName; ?>.show();
                    },
                    scope: this
                },
                {
                    iconCls:'create-folder',
                    id:'create-folder-folder<?php echo $containerName; ?>',
                    tooltip:'<?php echo $view['translator']->trans('Create Space'); ?>',
                    hidden: true,
                    handler: function(){
                        if(!winSpace<?php echo $containerName; ?>){
                            winSpace<?php echo $containerName; ?> = Ext.create('Ext.window.Window', {
                                modal:true,
                                id:'general-window<?php echo $containerName; ?>',
                                layout:'fit',
                                width:500,
                                height:350,
                                closeAction:'hide',
                                constrain: true,
                                title:'<?php echo $view['translator']->trans('Create Space'); ?>',
                                plain: true,
                                resizable: false,

                                items: Ext.create('Ext.Panel', {
                                    id: 'window-panel<?php echo $containerName; ?>',
                                    layout:'fit',
                                    border:false
                                }),
                                listeners:{
                                    'beforeshow':function() {
                                        $("#general-window<?php echo $containerName; ?>").mask("<?php echo $view['translator']->trans('Loading...'); ?>",300);
                                        $.ajax({
                                            cache: false,
                                            url : "<?php echo $view['router']->generate('ifresco_client_folder_actions') ?>",

                                            success : function (data) {
                                                $("#general-window<?php echo $containerName; ?>").unmask();
                                                $("#window-panel<?php echo $containerName; ?>").html(data);
                                                $("#spaceCreateForm #cm_name").focus();
                                            }
                                        });
                                    }
                                },

                                buttons: [{
                                    text: '<?php echo $view['translator']->trans('Save'); ?>',
                                    handler: function() {

                                        $.post("<?php echo $view['router']->generate('ifresco_client_create_space_post') ?>", $("#spaceCreateForm").serialize()+"&nodeId="+nodeId, function(data) {
                                            if (data.success === "true") {
                                                $(".PDFRenderer").hide();
                                                Ext.MessageBox.show({
                                                    title: '<?php echo $view['translator']->trans('Success'); ?>',
                                                    msg: data.message,
                                                    buttons: Ext.MessageBox.OK,
                                                    icon: Ext.MessageBox.INFO,
                                                    fn:showRenderer
                                                });

                                                Ext.getCmp("alfrescoTree").getStore().load()
                                                //Ext.getCmp('alfrescoTree').render();

                                                foldergrid<?php echo $containerName; ?>.getStore().load({params:{'nodeId':nodeId,'columnsetid':currentColumnsetid<?php echo $containerName; ?>}});

                                                winSpace<?php echo $containerName; ?>.hide();
                                                $("#window-panel<?php echo $containerName; ?>").html('');

                                            }
                                            else {
                                                $(".PDFRenderer").hide();
                                                Ext.MessageBox.show({
                                                    title: '<?php echo $view['translator']->trans('Error'); ?>',
                                                    msg: data.message,
                                                    buttons: Ext.MessageBox.OK,
                                                    icon: Ext.MessageBox.WARNING,
                                                    fn:showRenderer
                                                });
                                            }
                                        }, "json");
                                    }
                                },
                                    {
                                        text: '<?php echo $view['translator']->trans('Close'); ?>',
                                        handler: function() {
                                            winSpace<?php echo $containerName; ?>.hide();
                                            $("#window-panel<?php echo $containerName; ?>").html('');
                                        }
                                    }]
                            });
                        }
                        winSpace<?php echo $containerName; ?>.show();
                    }
                },//'-',
                {
                    iconCls:'open-alfresco',
                    id: 'open-alfresco-folder<?php echo $containerName; ?>',
                    tooltip:'<?php echo $view['translator']->trans('Open Folder in Alfresco'); ?>',
                    hidden: true,
                    handler: function(){
                        window.open("<?php echo $ShareSpaceUrl; ?>"+nodeId);
                    },
                    scope: this
                },{
                    iconCls:'refresh-meta',
                    tooltip:'<?php echo $view['translator']->trans('Refresh'); ?>',
                    cls: 'x-btn-icon',
                    handler: function(){
                        loadFolderPreview<?php echo $containerName; ?>(PanelNodeId<?php echo $containerName; ?>);
                    },
                    scope: this
                }],

            viewConfig: {
                forceFit:true
            },

            collapsible: true,
            animCollapse: true,
            header: false,
            id: 'folderDataGrid<?php echo $containerName; ?>',
            iconCls: 'icon-grid',

            stateId :'folderDocumentGrid-stateid<?php echo $containerName; ?>',
            stateful : true,
            renderTo:'previewWindow<?php echo $containerName; ?>',
            height:400
        });

        foldergrid<?php echo $containerName; ?>.store.load({params:{'nodeId':nodeId,'columnsetid':currentColumnsetid<?php echo $containerName; ?>}});
        //foldergrid<?php echo $containerName; ?>.render();

        foldergrid<?php echo $containerName; ?>.on('select', function(model, view, rowIndex, e) {
            var folderNodeText = folderStore<?php echo $containerName; ?>.getAt(rowIndex).data.alfresco_name;

            var folderNodeId = folderStore<?php echo $containerName; ?>.getAt(rowIndex).data.nodeId;
            var folderType = folderStore<?php echo $containerName; ?>.getAt(rowIndex).data.alfresco_type;

            if (folderType !== "{http://www.alfresco.org/model/content/1.0}folder") {
                var tabnodeid = folderNodeId.replace(/-/g,"");
                addTabDynamic('tab-'+tabnodeid,folderNodeText);

                $.ajax({
                    cache: false,
                    url : "<?php echo $view['router']->generate('ifresco_client_grid_detail_view') ?>",
                    data: ({'nodeId' : folderNodeId}),

                    success : function (data) {
                        $("#overAll").unmask();
                        $("#tab-"+tabnodeid).html(data);
                    },
                    beforeSend: function(req) {
                        $("#overAll").mask("<?php echo $view['translator']->trans('Loading'); ?> "+folderNodeText+"...",300);
                    }
                });
            }
            else {
                var tabnodeid = folderNodeId.replace(/-/g,"");
                addTabDynamic('tab-'+tabnodeid,folderNodeText);
                $.ajax({
                    cache: false,
                    url : "<?php echo $view['router']->generate('ifresco_client_data_grid_index') ?>?containerName="+tabnodeid+"&addContainer=<?php echo $nextContainer; ?>&columnsetid="+currentColumnsetid<?php echo $containerName; ?>,

                    success : function (data) {
                        $("#overAll").unmask();
                        $("#tab-"+tabnodeid).html(data);

                        eval("reloadGridData"+tabnodeid+"({params:{'nodeId':folderNodeId}});");
                    },
                    beforeSend: function(req) {
                        $("#overAll").mask("<?php echo $view['translator']->trans('Loading'); ?> "+folderNodeText+"...",300);
                    }
                });
            }
        });
    }

    function object2string(obj) {
        var output = "";
        for (var i in obj) {
            val = obj[i];

            switch (typeof val) {
                case ("object"):
                    break;
                case ("string"):
                    output += i + "=" + val + "&";
                    break;
                default:
                    output += i + "=" + val + "&";
                    break;
            }
        }
        return output;
    }

    function loadPreview<?php echo $containerName; ?>(nodeId) {


        var previewHeight = $("#previewWindow<?php echo $containerName; ?>").height();


        jQuery14.manageAjax.add('preview', {
            cache: false,
            url : "<?php echo $view['router']->generate('ifresco_client_view_index') ?>",
            data: "nodeId="+nodeId+"&height="+previewHeight+"px",
            success : function (data) {
                $("#previewContent<?php echo $containerName; ?>").unmask();

                $("#previewWindow<?php echo $containerName; ?>").html(data);

                $(".PDFRenderer").show();
            },
            beforeSend: function(xhr) {
                if ($(".PDFRenderer").is(':visible')) {
                    $(".PDFRenderer").hide();

                }
                $("#previewWindow<?php echo $containerName; ?>").html('');
                $("#previewContent<?php echo $containerName; ?>").mask("<?php echo $view['translator']->trans('Loading...'); ?>",300);
            }
        });
    }

    function contextMenuFunc<?php echo $containerName; ?>(gridx, record, item, index ,e, eOpts) {
        var existingMenu = Ext.getCmp('row-grid-ctx<?php echo $containerName; ?>');
        if (existingMenu !== null && typeof existingMenu !== 'undefined') {
            existingMenu.destroy();
        }



        var selection = mainGrid<?php echo $containerName; ?>.getSelectionModel().getSelection();
        var me = this;
        var allFiles = true;
        var allFolders = true;
        var allPDF = true;
        var allImages = true;
        var allOCRable = true;
        if (selection.length > 1) {
            var selectedObjects = [];
            var allDisAllowedDelete = true;
            var allDisAllowedEdit = true;


            for (var i = 0; i < selection.length; i++) {
                var selected = selection[i];
                var mime = selected.data.alfresco_mimetype;
                var type = selected.data.alfresco_type;
                if(selected.data.nodeId != selected.data.nodeRef) {
                    var nodeType = "filelink";
                } else
                if (type !== "{http://www.alfresco.org/model/content/1.0}folder")
                    var nodeType = "file";
                else
                    var nodeType = "folder";

                selectedObjects.push({
                    nodeId:selected.data.nodeId,
                    nodeRef:selected.data.nodeRef,
                    perm_delete:selected.data.alfresco_perm_delete,
                    perm_edit:selected.data.alfresco_perm_edit,
                    nodeName:selected.data.alfresco_name,
                    type:type,
                    shortType:nodeType,
                    docName:selected.data.alfresco_name,
                    mime:mime
                });

                if (nodeType === "file" || nodeType === "filelink") {
                    allFolders = false;
                }

                if(selected.data.alfresco_perm_delete == true) {
                    allDisAllowedDelete = false;
                }

                if(selected.data.alfresco_perm_edit == true) {
                    allDisAllowedEdit = false;
                }

                if (nodeType === "folder") {
                    allFiles = false;
                    allPDF = false;
                    allOCRable = false;
                    allImages = false;
                    continue;
                }

                if (mime !== "application/pdf") {
                    allPDF = false;
                }

                if (mime != "application/pdf" && mime != "image/tif" && mime != "image/tiff" && mime != "image/jpg" && mime != "image/jpeg" && mime != "image/gif" && mime != "image/png" && mime != "image/bmp") {
                    allOCRable = false;
                }

                if (mime != "image/tif" && mime != "image/tiff" && mime != "image/jpg" && mime != "image/jpeg" && mime != "image/gif" && mime != "image/png" && mime != "image/bmp") {
                    allImages = false;
                }
            }



            this.menu = Ext.create('Ext.menu.Menu', {
                id:'row-grid-ctx<?php echo $containerName; ?>',
                items: [
                    <?php if ($isClipBoard == false) { ?>
                    {
                        iconCls: 'add-clipboard',
                        text: '<?php echo $view['translator']->trans('Add to clipboard'); ?>',
                        scope:this,
                        handler: function(){
                            for (var i=0; i < selectedObjects.length; ++i) {
                                //console.log("try to add - "+selectedObjects[i].nodeId +" - "+selectedObjects[i].docName);
                                ClipBoard.addItem(selectedObjects[i].nodeId,selectedObjects[i].docName);
                            }
                            ClipBoard.reloadClip();

                            enableClipBtns<?php echo $containerName; ?>();
                        }
                    },'-',{
                        iconCls: 'delete-node',
                        text: '<?php echo $view['translator']->trans('Delete'); ?>',
                        disabled: allDisAllowedDelete,
                        scope:this,
                        handler: function(){
                            var allAllowed = true, allowedNodes = [], nodeNames = '';

                            for (var i=0; i < selectedObjects.length; ++i) {
                                if(selectedObjects[i].perm_delete == false) {
                                    allAllowed = false;
                                    nodeNames += selectedObjects[i].nodeName + '<br />';
                                }
                                else {
                                    allowedNodes.push(selectedObjects[i]);
                                }
                            }
                            if(!allAllowed){
                                Ext.MessageBox.show({
                                    title:'<?php echo $view['translator']->trans(''); ?>',
                                    msg: '<?php echo $view['translator']->trans('Not all documents are allowed for this action. <br />Following are out from the scope:'); ?> <br><b>'+nodeNames+'</b>',
                                    fn:function(btn) {
                                        if (btn === "yes") {
                                            if(allowedNodes.length == 0)
                                            {
                                                Ext.MessageBox.show({
                                                    title: '',
                                                    msg: '<?php echo $view['translator']->trans('No files to process'); ?>',
                                                    buttons: Ext.MessageBox.OK,
                                                    icon: Ext.MessageBox.INFO
                                                });
                                            }else {
                                                deleteNodes<?php echo $containerName; ?>(allowedNodes);
                                            }
                                        }
                                        $(".PDFRenderer").show();
                                    },
                                    buttons: Ext.MessageBox.YESNO,
                                    icon: Ext.MessageBox.QUESTION
                                });
                            } else {
                                deleteNodes<?php echo $containerName; ?>(selectedObjects);
                            }

                        }
                    }
                    <?php } else { ?>
                    {
                        iconCls: 'remove-clipboard',
                        text: '<?php echo $view['translator']->trans('Remove from clipboard'); ?>',
                        scope:this,
                        handler: function(){
                            for (var i=0; i < selectedObjects.length; ++i) {
                                ClipBoard.removeItem(selectedObjects[i].nodeId);
                            }
                            ClipBoard.reloadClip();
                        }
                    }
                    <?php } ?>
                    ,'-',
                    {
                        iconCls:'download-node',
                        text:'<?php echo $view['translator']->trans('Download ZIP'); ?>',
                        disabled: !zipArchiveExists<?php echo $containerName; ?>,
                        handler: function() {
                            var files_to_download = [];
                            for (var i=0; i < selectedObjects.length; ++i) {
                                files_to_download.push(selectedObjects[i].nodeId);
                            }
                            files_to_download = JSON.stringify(files_to_download);

                            var win = window.open('');
                            win.document.write("<head></head><body>");
                            win.document.write("<form action='<?php echo $view['router']->generate('ifresco_client_node_actions_download_nodes') ?>' method='POST'>");
                            win.document.write("<input type='hidden' name='nodes' value='"+files_to_download+"' />");
                            win.document.write("</form>");
                            win.document.write("<scr"+"ipt>");
                            win.document.write("document.forms[0].submit();");

                            win.document.write("</scr"+"ipt>");
                            win.document.write("</body></html>");
                        }
                    }
                    ,'-',
                    {
                        iconCls: 'specify-type',
                        text: '<?php echo $view['translator']->trans('Specify type'); ?>',
                        disabled:!allFiles || allDisAllowedEdit,
                        scope:this,
                        handler: function(){
                            var allAllowed = true, allowedNodes = [], nodeNames = '';

                            for (var i=0; i < selectedObjects.length; ++i) {
                                if(selectedObjects[i].perm_edit == false) {
                                    allAllowed = false;
                                    nodeNames += selectedObjects[i].nodeName + '<br />';
                                }
                                else {
                                    allowedNodes.push(selectedObjects[i]);
                                }
                            }
                            if(!allAllowed){
                                Ext.MessageBox.show({
                                    title:'<?php echo $view['translator']->trans(''); ?>',
                                    msg: '<?php echo $view['translator']->trans('Not all documents are allowed for this action. <br />Following are out from the scope:'); ?> <br><b>'+nodeNames+'</b>',
                                    fn:function(btn) {
                                        if (btn === "yes") {
                                            if(allowedNodes.length == 0)
                                            {
                                                Ext.MessageBox.show({
                                                    title: '',
                                                    msg: '<?php echo $view['translator']->trans('No files to process'); ?>',
                                                    buttons: Ext.MessageBox.OK,
                                                    icon: Ext.MessageBox.INFO
                                                });
                                            }else {
                                                specifyType<?php echo $containerName; ?>(allowedNodes);
                                            }
                                        }
                                        $(".PDFRenderer").show();
                                    },
                                    buttons: Ext.MessageBox.YESNO,
                                    icon: Ext.MessageBox.QUESTION
                                });
                            } else {
                                specifyType<?php echo $containerName; ?>(selectedObjects);
                            }

                            //specifyType<?php echo $containerName; ?>(selectedObjects);
                        }
                    }
                    ,'-',
                    {
                        iconCls: 'add-favorite',
                        text: '<?php echo $view['translator']->trans('Add to favorites'); ?>',
                        scope:this,
                        handler: function(){
                            for (var i=0; i < selectedObjects.length; ++i) {
                                addFavorite(selectedObjects[i].nodeId,selectedObjects[i].nodeName,selectedObjects[i].shortType);
                            }
                        }
                    },'-',{
                        iconCls: 'send-email',
                        text: '<?php echo $view['translator']->trans('Send as Email'); ?>',
                        scope:this,
                        disabled: allFolders,
                        handler: function(){
                            sendMail<?php echo $containerName; ?>(selectedObjects);
                        }
                    },'-',{
                        iconCls: 'send-email',
                        text: '<?php echo $view['translator']->trans('Send as Email link'); ?>',
                        scope:this,
                        handler: function(){
                            sendMailLink(selectedObjects);
                        }
                    },'-',{
                        iconCls: 'pdf-merge',
                        text: '<?php echo $view['translator']->trans('PDF Merge'); ?>',
                        scope:this,
                        disabled:(allPDF === true ? false : true),
                        handler: function(){
                            var nodes = [];
                            for (var i=0; i < selectedObjects.length; ++i) {
                                if (selectedObjects[i].shortType === "file" && selectedObjects[i].mime === "application/pdf")
                                    nodes.push(selectedObjects[i].nodeId);
                            }

                            if (nodes.length > 0) {
                                var jsonNodes = $.toJSON(nodes);
                                var dlUrl = '<?php echo $view['router']->generate('ifresco_client_node_actions_pdf_merge') ?>?nodes='+jsonNodes;
                                window.open(dlUrl);
                            }
                        }
                    }
                    <?php if (isset($OCREnabled) && $OCREnabled == "true") { ?>
                    ,{
                        iconCls: 'ocr-files',
                        text: '<?php echo $view['translator']->trans('OCR Files'); ?>',
                        scope:this,
                        //disabled:(allOCRable === true ? false : true),
                        hidden:true,
                        handler: function(){
                            var nodes = [];
                            for (var i=0; i < selectedObjects.length; ++i) {
                                if (selectedObjects[i].shortType === "file")
                                    nodes.push(selectedObjects[i].nodeId);
                            }

                            if (nodes.length > 0) {
                                ocrFiles<?php echo $containerName; ?>(nodes);
                            }
                        }
                    }
                    <?php } ?>
                    <?php if (isset($OCREnabled) && $OCREnabled == "true") { ?>
                    ,'-',{
                        iconCls: 'ocr-files',
                        text: '<?php echo $view['translator']->trans('Transform'); ?>',
                        scope:this,
                        disabled: type == "{http://www.alfresco.org/model/content/1.0}folder",
                        handler: function(){
                            var ocrNodes = [];

                            for (var i=0; i < selectedObjects.length; ++i) {
                                if (selectedObjects[i].shortType === "file")
                                    ocrNodes.push(selectedObjects[i].nodeId);
                            }

                            ocrNodes.length > 0
                            transformFiles(ocrNodes, '<?php echo $containerName; ?>');
                        }
                    }
                    <?php } ?>
                    /*,'-',{
                     iconCls: 'ocr-files',
                     text: '<?php echo $view['translator']->trans('copy ifresco link'); ?>',
                     scope:this,
                     handler: function() {

                     }
                     }*/
                ]
            });
        }
        else {
            var selected = store<?php echo $containerName; ?>.getAt(index);

            var DocName = selected.data.alfresco_name;
            var nodeId = selected.data.nodeId;
            var nodeRef = selected.data.nodeRef;
            var nodeName = selected.data.alfresco_name;
            var type = selected.data.alfresco_type;

            var isFolder = (type === "{http://www.alfresco.org/model/content/1.0}folder" ? true : false );
            //var isLink = (type === "{http://www.alfresco.org/model/application/1.0}filelink" ? true : false );
            var isLink = (nodeId != nodeRef );
            var deletedSource = (nodeId == nodeRef ) && (type === "{http://www.alfresco.org/model/application/1.0}filelink");

            var folder_path = selected.raw.alfresco_node_path;

            var MimeType = selected.data.alfresco_mimetype;
            // RIGHTS 
            var editRights = selected.data.alfresco_perm_edit;
            var delRights = selected.data.alfresco_perm_delete;
            var cancelCheckoutRights = selected.data.alfresco_perm_cancel_checkout;
            var createRights = selected.data.alfresco_perm_create;
            var hasRights = selected.data.alfresco_perm_permissions;

            // CHECKOUT LOGIC 
            var isWorkingCopy = selected.data.alfresco_isWorkingCopy;
            var isCheckedOut = selected.data.alfresco_isCheckedOut;
            var originalId = selected.data.alfresco_originalId;
            var workingCopyId = selected.data.alfresco_workingCopyId;

            //SHARE FEATURE
            var sharedId = selected.raw.alfresco_sharedId;
            var isShared = !Ext.isEmpty(sharedId);

            // BUTTONS
            var editMetaDataBtn = Ext.getCmp('editMetadata<?php echo $containerName; ?>');
            var manageAspectsBtn = Ext.getCmp('manageAspects<?php echo $containerName; ?>');
            var specifyTypeBtn = Ext.getCmp('specifyType<?php echo $containerName; ?>');
            var checkoutBtn = Ext.getCmp('checkout<?php echo $containerName; ?>');
            var checkoutZohoBtn = Ext.getCmp('checkoutZoho<?php echo $containerName; ?>');

            this.menu = Ext.create('Ext.menu.Menu', {
                id:'row-grid-ctx<?php echo $containerName; ?>',
                items: [{
                    iconCls: 'preview-tab',
                    text: '<?php echo $view['translator']->trans('Preview in new tab'); ?>',
                    scope:this,
                    disabled: deletedSource,
                    handler: function(){
                        //var nodeId = store<?php echo $containerName; ?>.getAt(index).data.nodeId;
                        //var DocName = store<?php echo $containerName; ?>.getAt(index).data.name;

                        if (isFolder) {
                            openFolder<?php echo $containerName; ?>(nodeId,nodeName);
                        }
                        else {
                            var autoLoad = {
                                url: '<?php echo $view['router']->generate('ifresco_client_grid_detail_view') ?>',
                                scripts: true,
                                scope:this,
                                params: {nodeId: nodeId}
                            };

                            if (!tabExists('tab-preview-'+nodeId))
                                addTabDynamicLoad('tab-preview-'+nodeId,DocName,autoLoad);
                            setActive('tab-preview-'+nodeId);
                        }
                    }
                },'-',
                    <?php if ($isClipBoard == false) { ?>
                    {
                        iconCls: 'add-clipboard',
                        text: '<?php echo $view['translator']->trans('Add to clipboard'); ?>',
                        //disabled:(editRights === true ? false : true),
                        disabled: deletedSource,
                        scope:this,
                        handler: function(){
                            ClipBoard.addItem(nodeId,DocName);
                            ClipBoard.reloadClip();
                            enableClipBtns<?php echo $containerName; ?>();
                        }
                    }
                    <?php } else { ?>
                    {
                        iconCls: 'remove-clipboard',
                        text: '<?php echo $view['translator']->trans('Remove from clipboard'); ?>',
                        //disabled:(editRights === true ? false : true),
                        scope:this,
                        handler: function(){
                            ClipBoard.removeItem(nodeId);
                            ClipBoard.reloadClip();
                        }
                    }
                    <?php } ?>
                    ,{
                        iconCls: 'view-metadata',
                        text: '<?php echo $view['translator']->trans('Edit Metadata'); ?>',
                        disabled:(editRights === true ? false : true),
                        scope:this,
                        handler: function(){
                            editMetadata(nodeId,nodeName);
                        }
                    },{
                        iconCls: 'specify-type',
                        text: '<?php echo $view['translator']->trans('Specify type'); ?>',
                        disabled:(editRights === true ? false : true),
                        scope:this,
                        handler: function(){
                            var specifyNode = [{nodeId:nodeId}];
                            specifyType<?php echo $containerName; ?>(specifyNode);
                        }
                    },{
                        iconCls: 'manage-aspects',
                        text: '<?php echo $view['translator']->trans('Manage aspects'); ?>',
                        disabled:(editRights === true ? false : true),
                        scope:this,
                        handler: function(){
                            manageAspects<?php echo $containerName; ?>(nodeId,nodeName);
                        }
                    },{
                        iconCls: 'create-html-edit',
                        text: '<?php echo $view['translator']->trans('Inline Edit'); ?>',
                        disabled:(editRights === true ? false : true),
                        hidden: !selected.raw.alfresco_is_inlineeditable,
                        scope:this,
                        handler: function(){
                            editHTMLdoc(nodeId, '<?php echo $containerName; ?>');
                        }
                    },
                    {
                        iconCls:'download-node',
                        text:'<?php echo $view['translator']->trans('Download'); ?>',
                        disabled:isFolder || deletedSource,
                        handler: function() {
                            var dlUrl = '<?php echo $view['router']->generate('ifresco_client_node_actions_download') ?>?nodeId='+nodeId;
                            window.open(dlUrl);
                        }
                    },
                    {
                        iconCls:'download-node',
                        text:'<?php echo $view['translator']->trans('Download ZIP'); ?>',
                        disabled: deletedSource || !zipArchiveExists<?php echo $containerName; ?>,
                        handler: function() {
                            var files_to_download = [nodeId];
                            files_to_download = JSON.stringify(files_to_download);
                            var dlUrl = '<?php echo $view['router']->generate('ifresco_client_node_actions_download_nodes') ?>?nodes='+files_to_download;
                            window.open(dlUrl);
                        }
                    },
                    {
                        iconCls:'quick-aspects',
                        text:'<?php echo $view['translator']->trans('Quick add aspect'); ?>',
                        disabled:(editRights === true ? false : true),
                        menu: {
                            items:[{
                                iconCls:'quick-aspect-tag',
                                text: '<?php echo $view['translator']->trans('Taggable'); ?>',
                                group: 'quickAspects',
                                handler: function() {
                                    quickAddAspect<?php echo $containerName; ?>(nodeId,"cm:taggable");
                                }
                            }, {
                                iconCls:'quick-aspect-version',
                                text: '<?php echo $view['translator']->trans('Versionable'); ?>',
                                group: 'quickAspects',
                                handler: function() {
                                    quickAddAspect<?php echo $containerName; ?>(nodeId,"cm:versionable");
                                }
                            }, {
                                iconCls:'quick-aspect-category',
                                text: '<?php echo $view['translator']->trans('Classifiable'); ?>',
                                group: 'quickAspects',
                                handler: function() {
                                    quickAddAspect<?php echo $containerName; ?>(nodeId,"cm:generalclassifiable");
                                }
                            }]
                        }
                    },
                    <?php if($view['settings']->getSetting("shareEnabled") == "true") {?>
                    {
                        iconCls:'share-doc',
                        //id: 'new_share3',
                        text:isShared?'<?php echo $view['translator']->trans('Share file info'); ?>':'<?php echo $view['translator']->trans('Share file'); ?>',
                        //disabled:(editRights === true ? false : true),
                        hidden: isFolder,
                        listeners: {
                            click: function() {
                                var shareMenu = this;

                                $.ajax({
                                    cache: false,
                                    url : "<?php echo $view['router']->generate('ifresco_client_node_actions_share_doc') ?>?nodeId="+nodeId,

                                    success : function (data) {
                                        $("#gridCenter").unmask();

                                        data = $.evalJSON(data);
                                        var sharedBy = data.sharedBy || '';
                                        sharedId = data.sharedId || '';

                                        record.raw.alfresco_sharedId = record.raw.qshare_sharedId = sharedId;
                                        record.raw.qshare_sharedBy = sharedBy

                                        record.set('qshare_sharedId', sharedId);
                                        record.set('qshare_sharedBy', sharedBy);
                                        record.commit();

                                        shareMenu.setText('<?php echo $view['translator']->trans('Share file info'); ?>');
                                        //shareMenu.setMenu(shareMenu.shareMenu);

                                        setTimeout(function(){
                                            me.menu.show();
                                            shareMenu.fireEvent('afterrender', shareMenu);
                                        }, 10)

                                        isShared = true;
                                    },
                                    beforeSend: function(xhr) {
                                        $("#gridCenter").mask("<?php echo $view['translator']->trans('Loading...'); ?>",300);

                                    }
                                });

                            },
                            afterrender: function(t, e) {
                                if(isShared) {
                                    this.setMenu(this.shareMenu);
                                    var shareMenuEl = this;
                                    var docShareUrl = '<?php echo $QuickSharePath; ?>'+sharedId;
                                    var sharePanel = this.menu.items.items[0];
                                    var cont1 = sharePanel.items.items[0];
                                    var cont2 = sharePanel.items.items[1];
                                    var urlField = cont1.items.items[0];
                                    var shareView = cont1.items.items[1];
                                    var unShare = cont1.items.items[2];

                                    var eBtn = cont2.items.items[1];
                                    var fBtn = cont2.items.items[2];
                                    var tBtn = cont2.items.items[3];
                                    var gBtn = cont2.items.items[4];
                                    urlField.setValue(docShareUrl);
                                    shareView.href = docShareUrl;
                                    eBtn.href = 'mailto:?body='+docShareUrl;
                                    fBtn.href = 'https://www.facebook.com/sharer/sharer.php?u='+docShareUrl;
                                    tBtn.href = 'https://twitter.com/intent/tweet?url='+docShareUrl;
                                    gBtn.href = 'https://plus.google.com/share?url='+docShareUrl;

                                    unShare.on('click', function(){
                                        $.ajax({
                                            cache: false,
                                            url : "<?php echo $view['router']->generate('ifresco_client_node_actions_unshare_doc') ?>?nodeId="+nodeId,

                                            success : function (data) {
                                                $("#" + sharePanel.id).unmask();
                                                record.raw.alfresco_sharedId
                                                    = record.raw.qshare_sharedBy
                                                    = record.raw.qshare_sharedId
                                                    = null;

                                                record.set('qshare_sharedId', null);
                                                record.set('qshare_sharedBy', null);
                                                record.commit();
                                                shareMenuEl.setMenu(null);
                                                //shareMenuEl.removeChildEls(function(){return false});
                                                isShared = false;
                                            },
                                            beforeSend: function(xhr) {
                                                $("#" + sharePanel.id).mask("<?php echo $view['translator']->trans('Loading...'); ?>",300);

                                            }
                                        });
                                    });
                                    //http://10.10.9.202:8080/share/s/SGDd4WyYSJGkyYuN4JY96Q
                                }
                                else {

                                }
                            }
                        },
                        shareMenu: {
                            items:[{
                                //text: '<?php echo $view['translator']->trans('Taggable'); ?>',
                                //group: 'quickAspects',
                                xtype: 'panel',
                                width: 470,
                                height: 100,
                                items: [
                                    {
                                        xtype: 'container',
                                        layout: 'hbox',
                                        items: [
                                            {
                                                xtype: 'textfield',
                                                name: 'publicLink',
                                                padding: '10',
                                                fieldLabel: '<?php echo $view['translator']->trans('Public Link'); ?>',
                                                width: 300,
                                                labelWidth: 70,
                                                readOnly: true,
                                                listeners: {
                                                    focus: function() {
                                                        this.selectText(0);
                                                    }
                                                }
                                            },
                                            {
                                                margin: '12 10 10 10',
                                                xtype: 'button',
                                                baseCls: 'share-links',
                                                border: false,
                                                text: 'View',
                                                url: 'http://www.google.com'
                                            },
                                            {
                                                margin: '12 10 10 10',
                                                style: 'cursor: pointer',
                                                xtype: 'button',
                                                baseCls: 'share-links',
                                                border: false,
                                                text: '<span>Unshare</span>'
                                            }
                                        ]
                                    },
                                    {
                                        xtype: 'container',
                                        layout: 'hbox',
                                        items: [
                                            {
                                                xtype: 'label',
                                                text: '<?php echo $view['translator']->trans('Share with'); ?>:',
                                                margin: '10',
                                                width: 70
                                            },
                                            {
                                                iconCls:'send-email',
                                                style: {
                                                    background: 'none'
                                                },
                                                margin: '10 0',
                                                border: false,
                                                xtype: 'button',
                                                url: 'http://www.google.com'
                                            },
                                            {
                                                iconCls:'share-f',
                                                style: {
                                                    background: 'none'
                                                },
                                                margin: '10 0',
                                                border: false,
                                                xtype: 'button',
                                                url: 'http://www.google.com'
                                            },
                                            {
                                                iconCls:'share-twitter',
                                                style: {
                                                    background: 'none'
                                                },
                                                margin: '10 0',
                                                border: false,
                                                xtype: 'button',
                                                url: 'http://www.google.com'
                                            },
                                            {
                                                iconCls:'share-google',
                                                style: {
                                                    background: 'none'
                                                },
                                                margin: '10 0',
                                                border: false,
                                                xtype: 'button',
                                                url: 'http://www.google.com'
                                            }
                                        ]
                                    }

                                ]
                            }]
                        }
                    },'-',
                    <?php } ?>
                    <?php if ($isClipBoard == false) { ?>

                    {
                        iconCls: 'delete-node',
                        text: '<?php echo $view['translator']->trans('Delete'); ?>',
                        disabled: (deletedSource && !this.store.permEdit) || (!deletedSource && !delRights),
                        scope:this,
                        handler: function(){
                            if (type !== "{http://www.alfresco.org/model/content/1.0}folder")
                                var nodeType = "file";
                            else
                                var nodeType = "folder";

                            deleteNode<?php echo $containerName; ?>(nodeRef,nodeName,nodeType);

                        }
                    },'-',
                    <?php } ?>
                    {
                        iconCls:(isWorkingCopy === true || isCheckedOut === true ? 'checkin-node' : 'checkout-node'),
                        text:(isWorkingCopy === true || isCheckedOut === true ? '<?php echo $view['translator']->trans('Checkin'); ?>' : '<?php echo $view['translator']->trans('Checkout'); ?>'),
                        disabled:(isFolder === true || isLink === true ? true : false) || !editRights,
                        handler: function(){
                            if (isWorkingCopy === true || isCheckedOut === true) {
                                var tempid = nodeId;
                                if (isCheckedOut === true) {
                                    tempid = workingCopyId;
                                }

                                checkIn<?php echo $containerName; ?>(tempid,MimeType);

                            }
                            else {
                                checkOut<?php echo $containerName; ?>(nodeId,MimeType);
                            }
                        },
                        scope: this
                    },
                    {
                        iconCls:'cancel-checkout',
                        text:'<?php echo $view['translator']->trans('Cancel Checkout'); ?>',
                        hidden:(isWorkingCopy === true || isCheckedOut === true || isFolder === true || isLink === true ? false : true),
                        disabled:(isFolder === true || isLink === true ? true : false),
                        handler: function(){
                            if (isWorkingCopy === true || isCheckedOut === true) {
                                var tempid = nodeId;
                                if (isCheckedOut === true) {
                                    tempid = workingCopyId;
                                }

                                cancelCheckout<?php echo $containerName; ?>(tempid,originalId,MimeType);
                            }
                        },
                        scope: this
                    }
                    ,'-',{
                        iconCls: 'add-favorite',
                        text: '<?php echo $view['translator']->trans('Add to favorites'); ?>',
                        scope:this,
                        disabled: deletedSource,
                        handler: function(){
                            if (type !== "{http://www.alfresco.org/model/content/1.0}folder")
                                var nodeType = "file";
                            else
                                var nodeType = "folder";

                            addFavorite(nodeId,nodeName,nodeType);
                        }},'-',{
                        iconCls: 'send-email',
                        text: '<?php echo $view['translator']->trans('Send as Email'); ?>',
                        scope:this,
                        disabled:(type == "{http://www.alfresco.org/model/content/1.0}folder" || isLink || deletedSource ),
                        handler: function(){
                            var mailNodes = [{nodeId:nodeId,nodeName:nodeName,docName:nodeName,shortType:'file'}];
                            sendMail<?php echo $containerName; ?>(mailNodes);
                        }},'-',{
                        iconCls: 'send-email',
                        text: '<?php echo $view['translator']->trans('Send as Email link'); ?>',
                        scope:this,
                        disabled: deletedSource,
                        handler: function(){
                            var mailNodes = [{nodeId:nodeId,nodeName:nodeName,docName:nodeName,shortType:'file',type:type}];
                            sendMailLink(mailNodes);
                        }}
                    <?php if($view['settings']->getSetting("openInAlfresco") == "true" || $isAdmin) {?>
                    ,'-',{
                        iconCls: 'view-alfresco',
                        text: '<?php echo $view['translator']->trans('Open in Alfresco'); ?>',
                        scope:this,
                        disabled: deletedSource,
                        handler: function(){

                            if(isFolder)
                                window.open('<?php echo $ShareFolder; ?>'+folder_path);
                            else
                                window.open('<?php echo $ShareUrl; ?>'+nodeId);
                        }}
                    <?php } ?>
                    <?php if (isset($OCREnabled) && $OCREnabled == "true") { ?>
                    ,'-',{
                        iconCls: 'ocr-files',
                        text: '<?php echo $view['translator']->trans('Transform'); ?>',
                        scope:this,
                        disabled: type == "{http://www.alfresco.org/model/content/1.0}folder",
                        handler: function(){
                            var ocrNodes = [nodeId];
                            transformFiles(ocrNodes, '<?php echo $containerName; ?>');
                        }
                    }
                    <?php } ?>
                    /*,'-',
                     {
                     renderTo: Ext.get('copySimulate<?php echo $containerName; ?>'),
                     xtype:'copybutton',
                     text: '<?php echo $view['translator']->trans('copy ifresco link'); ?>',
                     value: 'This text has been copied to the clipboard'
                     }*/
                ]
            });
        }
        e.stopEvent();

        this.menu.showAt(e.getXY());
    }
    Ext.get('dataGrid<?php echo $containerName; ?>').addListener('dragenter', function(e, target, cont){

        uploadPanel<?php echo $containerName; ?>.show();
    });
    /*Ext.getDom('dataGrid<?php echo $containerName; ?>').ondragenter = function(e) {
     //console.log(e);
     };*/

});


function loadSitesAction(item) {
    switch (item.text) {
        case "Calendar":
            $.ajax({
                cache: false,
                url : "<?php echo $view['router']->generate('ifresco_client_login') ?>?Sites/Calendar",

                success : function (data) {
                    $("#sitesContent").unmask();

                    $("#sitesWindow").html(data);
                },
                beforeSend: function(xhr) {
                    $("#sitesWindow").html('');
                    $("#sitesContent").mask("<?php echo $view['translator']->trans('Loading...'); ?>",300);

                }
            });
            break;
        default:
            break;
    }
}



function mySize<?php echo $containerName; ?>() {
    $("#documentGrid<?php echo $containerName; ?>").css({'height': (($(window).height()) -98)+'px'});
    return $("#documentGrid<?php echo $containerName; ?>").height();
}



function getHalfSize() {
    //todo, try to get real height
    //if ($.browser.msie) {
    if (1) {
        var size = mySize<?php echo $containerName; ?>();
        //return $("#documentGrid<?php echo $containerName; ?>").parent().parent().height();
    }
    return (($("#documentGrid<?php echo $containerName; ?>").height() / 2)-4);
}

function refreshGrid<?php echo $containerName; ?>() {
    if (lastParams<?php echo $containerName; ?> !== null) {
        var tempLast = lastParams<?php echo $containerName; ?>;
        lastParams<?php echo $containerName; ?>.params.reload = true;
        mainGrid<?php echo $containerName; ?>.store.load(lastParams<?php echo $containerName; ?>);
        lastParams<?php echo $containerName; ?> = tempLast;
    }
}

//function loadNewColumns<?php echo $containerName; ?>(combo,record,index) {
function loadNewColumns<?php echo $containerName; ?>(record) {

    if (typeof record !== 'undefined') {
        //var ColumnsetId = record[0].data.id;
        var ColumnsetId = record.id;
        //var ColumnName = record[0].data.name;
        var ColumnName = record.name;
        var params = lastParams<?php echo $containerName; ?>;
        params.params.columnsetid = ColumnsetId;

        var date = new Date();
        //var dateString = date.getDate()+"/"+date.getDay()+"/"+date.getFullYear()+" "+date.getHours()+":"+date.getMinutes();
        var dateString = date.getDate()+"/"+date.getDay()+"/"+date.getFullYear()+" "+date.getHours()+":"+date.getMinutes();
        var timestamp = Date.parse(dateString);

        var tabPanel = Ext.getCmp('content-tabs');
        if (tabPanel) {
            var activeTab = tabPanel.getActiveTab();


            Ifresco.Registry.set("ColumnsetId",ColumnsetId);
            UserColumnsetId = ColumnsetId;
            Ifresco.Registry.save();

            $.ajax({
                cache: false,
                url: "<?php echo $view['router']->generate('ifresco_client_data_grid_index') ?>?containerName=<?php echo $containerName ?>&addContainer=<?php echo $addContainer; ?>&columnsetid="+ColumnsetId,

                success : function (data) {
                    $("#overAll").unmask();
                    $("#"+activeTab.id).html(data);
                    //grid.store.load({params:{'nodeId':node.id}});  

                    eval("reloadGridData<?php echo $containerName; ?>({'params':params.params});");
                },
                beforeSend: function(req) {
                    $("#overAll").mask("<?php echo $view['translator']->trans('Loading Results...'); ?>",300);
                }

            });


        }
    }
}


function reloadGridData<?php echo $containerName; ?>(params) {
    /*mainThumbView<?php echo $containerName; ?>.hide();
     mainGrid<?php echo $containerName; ?>.show();*/
    // ABORT PREVIOUS REQUEST TO PREVENT CONCLUSION
    //if (!columnStore<?php echo $containerName; ?>.isLoading())
    Ext.Ajax.abort(mainGrid<?php echo $containerName; ?>.getStore().getProxy().activeRequest);

    if (typeof params.params.columnsetid !== 'undefined') {
        currentColumnsetid<?php echo $containerName; ?> = params.params.columnsetid;
    }
    else {
        var columnsetid = Ifresco.Registry.get("ColumnsetId");
        currentColumnsetid<?php echo $containerName; ?> = columnsetid;
        params.params.columnsetid = columnsetid;
    }

    mainGrid<?php echo $containerName; ?>.stateId = 'documentGrid-stateid-'+currentColumnsetid<?php echo $containerName; ?>;

    if (params.params.clipboarditems != null && typeof params.params.clipboarditems != 'undefined') {
        //mainGrid<?php echo $containerName; ?>.getStore().getProxy().method = "POST";
        mainGrid<?php echo $containerName; ?>.getStore().getProxy().actionMethods.read = "POST";
    }

    mainGrid<?php echo $containerName; ?>.store.currentPage=1;

    var tbar =  Ext.getCmp("viewportToolbar<?php echo $containerName; ?>");
    if (tbar) {
        tbar.removeAll();
    }

    <?php if (!empty($DefaultSort) && $DefaultSort != null) { ?>
    //mainGrid<?php echo $containerName; ?>.store.setDefaultSort('<?php echo $DefaultSort; ?>', '<?php echo $DefaultSortDir; ?>');
    mainGrid<?php echo $containerName; ?>.store.sortOnLoad = true;
    mainGrid<?php echo $containerName; ?>.getStore().sortInfo = {field: '<?php echo $DefaultSort; ?>', direction: '<?php echo $DefaultSortDir; ?>'};
    mainGrid<?php echo $containerName; ?>.getStore().sorters.clear();
    mainGrid<?php echo $containerName; ?>.getStore().sorters.add(new Ext.util.Sorter({
        property: '<?php echo $DefaultSort; ?>',
        direction:'<?php echo $DefaultSortDir; ?>'
    }));

    <?php } ?>

    lastParams<?php echo $containerName; ?> = params;
    if(mainThumbView<?php echo $containerName; ?>.isVisible())
        loadThumbnailView<?php echo $containerName; ?>();
    else {


        /*if(params.params.searchTerm || params.params.categories || params.params.advancedSearchFields || params.params.tag) {
         mainGrid<?php echo $containerName; ?>.store.proxy.extraParams.nodeId = null;

         }*/


        mainGrid<?php echo $containerName; ?>.store.proxy.extraParams = {};
        paramsToStore = ['subCategories', 'categories', 'categoryNodeId', 'nodeId', 'columnsetid', 'advancedSearchFields', 'advancedSearchOptions', 'containerName', 'clipboard', 'clipboarditems', 'searchTerm', 'clickSearch', 'clickSearchValue'];

        if (typeof paramsToStore !='undefined') {
            for(var i = 0; i < paramsToStore.length; i++) {
                if(params.params[paramsToStore[i]]) {
                    mainGrid<?php echo $containerName; ?>.store.proxy.extraParams[paramsToStore[i]] = params.params[paramsToStore[i]];
                }
            }
        }




        mainGrid<?php echo $containerName; ?>.store.load(params);
    }



    if (params.params.nodeId !== '') {
        /*if (params.params.nodeId === 'root')
         detailUrl<?php echo $containerName; ?> = '<?php echo $CompanyHomeUrl; ?>';
         else {
         detailUrl<?php echo $containerName; ?> = orgDetailUrl<?php echo $containerName; ?>+params.params.nodeId;
         }*/
        //console.log("change current node id to "+params.params.nodeId);
        currentNodeId<?php echo $containerName; ?> = params.params.nodeId;
    }
    //else {
    //    console.log("no node id to change");
    //}

    $("#previewWindow<?php echo $containerName; ?>").html('');

    if(typeof changeUploadId<?php echo $containerName; ?> === 'function') {
        changeUploadId<?php echo $containerName; ?>(params.params.nodeId);
    }

    if (params.params.nodeId === '' || typeof params.params.nodeId === 'undefined' || params.params.nodeId == null) {

        var openAlfresco = mainGrid<?php echo $containerName; ?>.dockedItems.items[1].getComponent('open-alfresco<?php echo $containerName; ?>');
        if (typeof openAlfresco !== 'undefined') {
            openAlfresco.disable();
            //openAlfresco.hide();
        }

        var uploadContent = mainGrid<?php echo $containerName; ?>.dockedItems.items[1].getComponent('upload-content<?php echo $containerName; ?>');
        if (typeof uploadContent !== 'undefined') {
            uploadContent.disable();
            //uploadContent.hide();
        }

        var createFolder = mainGrid<?php echo $containerName; ?>.dockedItems.items[1].getComponent('create-folder<?php echo $containerName; ?>');
        if (typeof createFolder !== 'undefined') {
            createFolder.disable();
            //createFolder.hide();
        }

        var pasteCopyClipboard = mainGrid<?php echo $containerName; ?>.dockedItems.items[1].getComponent('pastecopy-clipboard<?php echo $containerName; ?>');
        if (typeof pasteCopyClipboard !== 'undefined') {
            pasteCopyClipboard.disable();
            //pasteCopyClipboard.hide();
        }

        var pasteCutClipboard = mainGrid<?php echo $containerName; ?>.dockedItems.items[1].getComponent('pastecut-clipboard<?php echo $containerName; ?>');
        if (typeof pasteCutClipboard !== 'undefined') {
            pasteCutClipboard.disable();
            //pasteCutClipboard.hide();
        }

        var pasteLinkClipboard = mainGrid<?php echo $containerName; ?>.dockedItems.items[1].getComponent('pastelink-clipboard<?php echo $containerName; ?>');
        if (typeof pasteLinkClipboard !== 'undefined') {
            pasteLinkClipboard.disable();
        }
    }
    else {
        var openAlfresco = mainGrid<?php echo $containerName; ?>.dockedItems.items[1].getComponent('open-alfresco<?php echo $containerName; ?>');
        if (typeof openAlfresco !== 'undefined')
            openAlfresco.enable();

        var uploadContent = mainGrid<?php echo $containerName; ?>.dockedItems.items[1].getComponent('upload-content<?php echo $containerName; ?>');
        if (typeof uploadContent !== 'undefined')
            uploadContent.enable();

        var createFolder = mainGrid<?php echo $containerName; ?>.dockedItems.items[1].getComponent('create-folder<?php echo $containerName; ?>');
        if (typeof createFolder !== 'undefined')
            createFolder.enable();

        var pasteCopyClipboard = mainGrid<?php echo $containerName; ?>.dockedItems.items[1].getComponent('pastecopy-clipboard<?php echo $containerName; ?>');
        if (typeof pasteCopyClipboard !== 'undefined')
            pasteCopyClipboard.enable();

        var pasteCutClipboard = mainGrid<?php echo $containerName; ?>.dockedItems.items[1].getComponent('pastecut-clipboard<?php echo $containerName; ?>');
        if (typeof pasteCutClipboard !== 'undefined')
            pasteCutClipboard.enable();
    }

    <? if($view['settings']->getSetting("MetaOnTreeFolder") == 'true'){
        ?>
    PanelNodeId<?php echo $containerName; ?> = null;
    Ext.getCmp('mainPreviewTab<?php echo $containerName; ?>').disable();
    Ext.getCmp('mainVersionsTab<?php echo $containerName; ?>').disable();
    var tabPanel = Ext.getCmp("previewPanel<?php echo $containerName; ?>");
    var activeTabId = "";
    if (tabPanel) {
        var activeTab = tabPanel.activeTab;
        if (typeof activeTab !== 'undefined') {
            activeTabId = activeTab.id
        }
    }

    <? if($view['settings']->getSetting("ParentMetaDocumentOnly") == "true" ) {?>
    Ext.getCmp('mainParentMetadataTab<?php echo $containerName; ?>').disable();
    <?}?>


    if (activeTabId === "mainMetadataTab<?php echo $containerName; ?>")
        loadMetaData<?php echo $containerName; ?>(currentNodeId<?php echo $containerName; ?>);
    else if (activeTabId === "mainParentMetadataTab<?php echo $containerName; ?>") {
        <? if($view['settings']->getSetting("ParentMetaDocumentOnly") == "true" ) {?>
        tabPanel.setActiveTab('mainMetadataTab<?php echo $containerName; ?>');
        <?} else {?>
        loadParentMetaData<?php echo $containerName; ?>(currentNodeId<?php echo $containerName; ?>);
        <?}?>
    }
    <?
}
 ?>
}


function pasteClipBoard<?php echo $containerName; ?>(type) {
    if (ClipBoard.items.length > 0) {
        var clipBoardItems = $.JSON.encode(ClipBoard.items);
        var currentNode = currentNodeId<?php echo $containerName; ?>;
        $.ajax({
            cache: false,
            url : "<?php echo $view['router']->generate('ifresco_client_grid_paste_clipboard') ?>",
            data: ({'clipboardItems' : clipBoardItems, 'actionType' : type, 'destNodeId' : currentNode}),
            type:'POST',
            success : function (data) {
                $("#overAll").unmask();

                jsonData = $.JSON.decode(data);
                var totalCount = jsonData.totalResults;
                var successCount = jsonData.successCount;
                var failureCount = jsonData.failureCount;
                if(jsonData.success === true) {
                    $(".PDFRenderer").hide();
                    Ext.MessageBox.show({
                        title: '<?php echo $view['translator']->trans('Successfully pasted!'); ?>',
                        msg: '<?php echo $view['translator']->trans('Successfully pasted'); ?> '+successCount+' <?php echo $view['translator']->trans('node(s)'); ?>!',
                        buttons: Ext.MessageBox.OK,
                        icon: Ext.MessageBox.INFO,
                        fn:showRenderer
                    });

                    if (type=="cut") {
                        ClipBoard.clearItems();
                    }
                }
                else {
                    $(".PDFRenderer").hide();
                    Ext.MessageBox.show({
                        title: '<?php echo $view['translator']->trans('Pasting was not successful!'); ?>',
                        msg: successCount+' <?php echo $view['translator']->trans('of'); ?>  '+totalCount+' <?php echo $view['translator']->trans('node(s) pasted to the destination folder'); ?>!',
                        buttons: Ext.MessageBox.OK,
                        icon: Ext.MessageBox.WARNING,
                        fn:showRenderer
                    });
                }

                refreshGrid<?php echo $containerName; ?>();
            },
            beforeSend: function(req) {
                $("#overAll").mask("<?php echo $view['translator']->trans('Pasting documents'); ?>",300);
            }
        });
    }
}


function checkOut<?php echo $containerName; ?>(nodeId,MimeType) {
    $.ajax({
        cache: false,
        url : "<?php echo $view['router']->generate('ifresco_client_metadata_checkout') ?>?nodeId="+nodeId,


        success : function (data) {
            data = $.evalJSON(data);
            if (data.success === true) {
                var workingCopyId = data.workingCopyId;

                Ext.MessageBox.alert('<?php echo $view['translator']->trans('Checkout'); ?>', '<?php echo $view['translator']->trans('Document checked out successfully.'); ?>');

                mainGrid<?php echo $containerName; ?>.store.on('load', function() {

                        var rowIndex = mainGrid<?php echo $containerName; ?>.getStore().find("nodeId",workingCopyId);
                        if (rowIndex !== -1) {
                            mainGrid<?php echo $containerName; ?>.getSelectionModel().selectRow(rowIndex);
                            mainGrid<?php echo $containerName; ?>.fireEvent('rowclick', mainGrid<?php echo $containerName; ?>, rowIndex);
                            var checkoutBtn = Ext.getCmp('checkout<?php echo $containerName; ?>');
                            var checkoutZohoBtn = Ext.getCmp('checkoutZoho<?php echo $containerName; ?>');
                            var cancelCheckoutBtn = Ext.getCmp('cancel-checkout<?php echo $containerName; ?>');

                            var editMetaDataBtn = Ext.getCmp('editMetadata<?php echo $containerName; ?>');
                            var manageAspectsBtn = Ext.getCmp('manageAspects<?php echo $containerName; ?>');
                            var specifyTypeBtn = Ext.getCmp('specifyType<?php echo $containerName; ?>');

                            checkoutBtn.setTooltip("<?php echo $view['translator']->trans('Checkin'); ?>");
                            checkoutBtn.setIconCls("checkin-node");

                            <?php if (isset($OnlineEditing) && $OnlineEditing === "zoho") { ?>
                            if (jQuery.inArray(MimeType,ZohoMimeDocs)>=0) {
                                checkoutZohoBtn.setTooltip("<?php echo $view['translator']->trans('Edit in Zoho Writer'); ?>");
                                checkoutZohoBtn.enable();
                                checkoutZohoBtn.setVisible(true);
                            }
                            else if (jQuery.inArray(MimeType,ZohoMimeSheet)>=0) {
                                checkoutZohoBtn.setTooltip("<?php echo $view['translator']->trans('Edit in Zoho Sheet'); ?>");
                                checkoutZohoBtn.enable();
                                checkoutZohoBtn.setVisible(true);
                            }
                            else {
                                checkoutZohoBtn.disable();
                                checkoutZohoBtn.setVisible(false);
                            }
                            <?php } ?>

                            cancelCheckoutBtn.enable();
                            cancelCheckoutBtn.setVisible(true);

                            PanelNodeIsCheckedOut<?php echo $containerName; ?> = true;
                            PanelNodeCheckedOutId<?php echo $containerName; ?> = workingCopyId;
                        }
                    },
                    this,
                    {
                        single: true
                    });

                refreshGrid<?php echo $containerName; ?>();


            }
            else
                Ext.MessageBox.alert('<?php echo $view['translator']->trans('Error'); ?>', '<?php echo $view['translator']->trans('An unknown problem occured at the check out process.'); ?>');
        },
        beforeSend: function(req) {

        }
    });
}

function checkInWindow<?php echo $containerName; ?>(data) {
    showNewVersionWindow<?php echo $containerName; ?>(data,"<?php echo $view['translator']->trans('Checkin'); ?>","<?php echo $view['translator']->trans('Save'); ?>","checkInVersion<?php echo $containerName; ?>","");
}

function checkInVersion<?php echo $containerName; ?>(data) {
    checkIn<?php echo $containerName; ?>(data.nodeId,data.mime,true,data.note,data.versionchange);
}

function checkIn<?php echo $containerName; ?>(nodeId,MimeType,msgbox,note,versionchange) {
    if (typeof msgbox === 'undefined' || msgbox === null)
        msgbox = true;

    if (typeof note === 'undefined' || note === null)
        note = "";

    if (typeof versionchange === 'undefined' || versionchange === null)
        versionchange = "minor";

    $.ajax({
        cache: false,
        url : "<?php echo $view['router']->generate('ifresco_client_metadata_checkin') ?>?nodeId="+nodeId,
        data: "note="+note+"&versionchange="+versionchange,

        success : function (data) {
            data = $.evalJSON(data);
            if (data.success = true) {
                var origNodeId = data.origNodeId;

                if (msgbox===true)
                    Ext.MessageBox.alert('<?php echo $view['translator']->trans('Checkin'); ?>', '<?php echo $view['translator']->trans('Document checked in successfully.'); ?>');

                refreshGrid<?php echo $containerName; ?>();

                var rowIndex = mainGrid<?php echo $containerName; ?>.getStore().find("nodeId",origNodeId);
                if (rowIndex !== -1) {
                    mainGrid<?php echo $containerName; ?>.getSelectionModel().selectRow(rowIndex);
                    mainGrid<?php echo $containerName; ?>.fireEvent('rowclick', mainGrid<?php echo $containerName; ?>, rowIndex);

                    var checkoutBtn = Ext.getCmp('checkout<?php echo $containerName; ?>');
                    var checkoutZohoBtn = Ext.getCmp('checkoutZoho<?php echo $containerName; ?>');
                    var cancelCheckoutBtn = Ext.getCmp('cancel-checkout<?php echo $containerName; ?>');

                    var editMetaDataBtn = Ext.getCmp('editMetadata<?php echo $containerName; ?>');
                    var manageAspectsBtn = Ext.getCmp('manageAspects<?php echo $containerName; ?>');
                    var specifyTypeBtn = Ext.getCmp('specifyType<?php echo $containerName; ?>');

                    checkoutBtn.setTooltip("<?php echo $view['translator']->trans('Checkout'); ?>");
                    checkoutBtn.setIconCls("checkout-node");

                    <?php if (isset($OnlineEditing) && $OnlineEditing === "zoho") { ?>
                    if (jQuery.inArray(MimeType,ZohoMimeDocs)>=0) {
                        checkoutZohoBtn.setTooltip("<?php echo $view['translator']->trans('Checkout in Zoho Writer'); ?>");
                        checkoutZohoBtn.enable();
                        checkoutZohoBtn.setVisible(true);
                    }
                    else if (jQuery.inArray(MimeType,ZohoMimeSheet)>=0) {
                        checkoutZohoBtn.setTooltip("<?php echo $view['translator']->trans('Checkout in Zoho Sheet'); ?>");
                        checkoutZohoBtn.enable();
                        checkoutZohoBtn.setVisible(true);
                    }
                    else {
                        checkoutZohoBtn.disable();
                        checkoutZohoBtn.setVisible(false);
                    }
                    <?php } ?>


                    cancelCheckoutBtn.disable();
                    cancelCheckoutBtn.setVisible(false);

                    editMetaDataBtn.enable();
                    manageAspectsBtn.enable();
                    specifyTypeBtn.enable();

                    PanelNodeIsCheckedOut<?php echo $containerName; ?> = false;
                }
            }
            else
                Ext.MessageBox.alert('<?php echo $view['translator']->trans('Error'); ?>', '<?php echo $view['translator']->trans('An unknown problem occured at the check in process.'); ?>');
        },
        beforeSend: function(req) {
            if (typeof winNewVersion<?php echo $containerName; ?> != "undefined")
                winNewVersion<?php echo $containerName; ?>.close();
        }
    });
}

function cancelCheckout<?php echo $containerName; ?>(nodeId,origNodeId,MimeType,msgbox) {
    if (typeof msgbox === 'undefined' || msgbox === null)
        msgbox = true;

    $.ajax({
        cache: false,
        url : "<?php echo $view['router']->generate('ifresco_client_metadata_cancel_checkout') ?>?nodeId="+nodeId,

        success : function (data) {
            data = $.evalJSON(data);
            if (data.success = true) {

                if (msgbox===true)
                    Ext.MessageBox.alert('<?php echo $view['translator']->trans('Cancel Checkout'); ?>', '<?php echo $view['translator']->trans('Successfully canceled.'); ?>');

                refreshGrid<?php echo $containerName; ?>();

                var rowIndex = mainGrid<?php echo $containerName; ?>.getStore().find("nodeId",origNodeId);
                if (rowIndex !== -1) {
                    mainGrid<?php echo $containerName; ?>.getSelectionModel().selectRow(rowIndex);
                    mainGrid<?php echo $containerName; ?>.fireEvent('rowclick', mainGrid<?php echo $containerName; ?>, rowIndex);

                    var checkoutBtn = Ext.getCmp('checkout<?php echo $containerName; ?>');
                    var checkoutZohoBtn = Ext.getCmp('checkoutZoho<?php echo $containerName; ?>');
                    var cancelCheckoutBtn = Ext.getCmp('cancel-checkout<?php echo $containerName; ?>');

                    var editMetaDataBtn = Ext.getCmp('editMetadata<?php echo $containerName; ?>');
                    var manageAspectsBtn = Ext.getCmp('manageAspects<?php echo $containerName; ?>');
                    var specifyTypeBtn = Ext.getCmp('specifyType<?php echo $containerName; ?>');

                    checkoutBtn.setTooltip("<?php echo $view['translator']->trans('Checkout'); ?>");
                    checkoutBtn.setIconCls("checkout-node");

                    <?php if (isset($OnlineEditing) && $OnlineEditing === "zoho") { ?>
                    if (jQuery.inArray(MimeType,ZohoMimeDocs)>=0) {
                        checkoutZohoBtn.setTooltip("<?php echo $view['translator']->trans('Checkout in Zoho Writer'); ?>");
                        checkoutZohoBtn.enable();
                        checkoutZohoBtn.setVisible(true);
                    }
                    else if (jQuery.inArray(MimeType,ZohoMimeSheet)>=0) {
                        checkoutZohoBtn.setTooltip("<?php echo $view['translator']->trans('Checkout in Zoho Sheet'); ?>");
                        checkoutZohoBtn.enable();
                        checkoutZohoBtn.setVisible(true);
                    }
                    else {
                        checkoutZohoBtn.disable();
                        checkoutZohoBtn.setVisible(false);
                    }
                    <?php } ?>

                    cancelCheckoutBtn.disable();
                    cancelCheckoutBtn.setVisible(false);

                    editMetaDataBtn.enable();
                    manageAspectsBtn.enable();
                    specifyTypeBtn.enable();

                    PanelNodeIsCheckedOut<?php echo $containerName; ?> = false;
                }
            }
            else
                Ext.MessageBox.alert('<?php echo $view['translator']->trans('Error'); ?>', '<?php echo $view['translator']->trans('An unknown problem occured at the check in process.'); ?>');
        },
        beforeSend: function(req) {

        }
    });
}

function checkOutZoho<?php echo $containerName; ?>(nodeId,MimeType) {
    $.ajax({
        cache: false,
        url : "<?php echo $view['router']->generate('ifresco_client_metadata_checkout') ?>?nodeId="+nodeId,


        success : function (data) {
            data = $.evalJSON(data);
            if (data.success === true) {
                var workingCopyId = data.workingCopyId;

                mainGrid<?php echo $containerName; ?>.store.on('load', function() {

                        var rowIndex = mainGrid<?php echo $containerName; ?>.getStore().find("nodeId",workingCopyId);
                        if (rowIndex !== -1) {
                            mainGrid<?php echo $containerName; ?>.getSelectionModel().selectRow(rowIndex);
                            mainGrid<?php echo $containerName; ?>.fireEvent('rowclick', mainGrid<?php echo $containerName; ?>, rowIndex);
                            var checkoutBtn = Ext.getCmp('checkout<?php echo $containerName; ?>');
                            var checkoutZohoBtn = Ext.getCmp('checkoutZoho<?php echo $containerName; ?>');

                            var cancelCheckoutBtn = Ext.getCmp('cancel-checkout<?php echo $containerName; ?>');

                            checkoutBtn.setTooltip("<?php echo $view['translator']->trans('Checkin'); ?>");
                            checkoutBtn.setIconCls("checkin-node");

                            if (jQuery.inArray(MimeType,ZohoMimeDocs)>=0) {
                                checkoutZohoBtn.setTooltip("<?php echo $view['translator']->trans('Edit in Zoho Writer'); ?>");
                                checkoutZohoBtn.enable();
                                checkoutZohoBtn.setVisible(true);
                            }
                            else if (jQuery.inArray(MimeType,ZohoMimeSheet)>=0) {
                                checkoutZohoBtn.setTooltip("<?php echo $view['translator']->trans('Edit in Zoho Sheet'); ?>");
                                checkoutZohoBtn.enable();
                                checkoutZohoBtn.setVisible(true);
                            }
                            else {
                                checkoutZohoBtn.disable();
                                checkoutZohoBtn.setVisible(false);
                            }

                            cancelCheckoutBtn.enable();
                            cancelCheckoutBtn.setVisible(true);


                            PanelNodeIsCheckedOut<?php echo $containerName; ?> = true;
                            PanelNodeCheckedOutId<?php echo $containerName; ?> = workingCopyId;
                        }
                    },
                    this,
                    {
                        single: true
                    });

                $.ajax({
                    cache: false,
                    url : "<?php echo $view['router']->generate('ifresco_client_login') ?>?Zoho/ZohoUpload&nodeId="+workingCopyId,


                    success : function (dataZoho) {
                        $("#overAll").unmask();
                        dataZoho = $.evalJSON(dataZoho);
                        var zohoUrl = dataZoho.URL;
                        var success = dataZoho.RESULT;
                        var warning = dataZoho.WARNING;
                        if (success === true || success === "TRUE") {
                            refreshGrid<?php echo $containerName; ?>();
                            if (Ifresco.Registry.get("ArrangeList")=="horizontal") {
                                window.open(zohoUrl);
                            }
                            else {
                                var previewPanel = Ext.getCmp('previewPanel<?php echo $containerName; ?>');
                                if (previewPanel) {
                                    previewPanel.add({
                                        title: "Zoho",
                                        closable:true,
                                        items: [ Ext.create('Ext.ux.IFrameComponent', { id: 'zohoWriter<?php echo $containerName; ?>', url: zohoUrl, width:'100%', height:"100%" }) ],
                                        tbar:[{
                                            iconCls:'open-window',
                                            id: 'zohoWriterOpenBtn<?php echo $containerName; ?>',
                                            text:'<?php echo $view['translator']->trans('Open in new Window'); ?>',
                                            handler: function(){
                                                window.open(zohoUrl);
                                            },
                                            scope: this
                                        }]
                                    }).show();
                                }
                            }
                        }
                        else {
                            $("#overAll").unmask();
                            Ext.MessageBox.show({
                                title: '<?php echo $view['translator']->trans('Upload to Zoho Failed!'); ?>',
                                msg: warning,
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.ERROR
                            });
                            cancelCheckout<?php echo $containerName; ?>(workingCopyId,nodeId,MimeType,false);
                        }
                    },
                    beforeSend: function(req) {

                    }
                });
            }
            else {
                Ext.MessageBox.alert('<?php echo $view['translator']->trans('Error'); ?>', '<?php echo $view['translator']->trans('An unknown problem occured at the check out process.'); ?>');
            }
        },
        beforeSend: function(req) {
            $("#overAll").mask("<?php echo $view['translator']->trans('Checkout to Zoho'); ?>",300);
        }
    });
}

function editInZoho<?php echo $containerName; ?>(nodeId,MimeType) {
    $.ajax({
        cache: false,
        url : "<?php echo $view['router']->generate('ifresco_client_login') ?>?Zoho/ZohoUpload&nodeId="+nodeId,


        success : function (dataZoho) {
            $("#overAll").unmask();
            dataZoho = $.evalJSON(dataZoho);
            var zohoUrl = dataZoho.URL;
            var success = dataZoho.RESULT;
            var warning = dataZoho.WARNING;
            if (success === true || success === "TRUE") {
                refreshGrid<?php echo $containerName; ?>();
                if (Ifresco.Registry.get("ArrangeList")=="horizontal") {
                    window.open(zohoUrl);
                }
                else {
                    var previewPanel = Ext.getCmp('previewPanel<?php echo $containerName; ?>');
                    if (previewPanel) {
                        previewPanel.add({
                            title: "Zoho Writer",
                            closable:true,
                            items: [ Ext.create('Ext.ux.IFrameComponent', { id: 'zohoWriter<?php echo $containerName; ?>', url: zohoUrl, width:'100%', height:"100%" }) ],
                            tbar:[{
                                iconCls:'open-window',
                                id: 'zohoWriterOpenBtn<?php echo $containerName; ?>',
                                text:'<?php echo $view['translator']->trans('Open in new Window'); ?>',
                                handler: function(){
                                    window.open(zohoUrl);
                                },
                                scope: this
                            }]
                        }).show();
                    }
                }
            }
            else {
                Ext.MessageBox.show({
                    title: '<?php echo $view['translator']->trans('Upload to Zoho Failed!'); ?>',
                    msg: warning,
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.ERROR
                });
            }
        },
        beforeSend: function(req) {
            $("#overAll").mask("<?php echo $view['translator']->trans('Edit in Zoho Writer'); ?>",300);
        }
    });
}

function loadMetaData<?php echo $containerName; ?>(nodeId) {

    if (nodeId !== null & typeof nodeId !== 'undefined') {

        var metaHeight = $("#metadataWindow<?php echo $containerName; ?>").height();
        if ($.browser.msie) {
            $("#metadataWindow<?php echo $containerName; ?>").html('');
            metaHeight = $("#metadataWindow<?php echo $containerName; ?>").height();
        }

        jQuery14.manageAjax.add('metadata', {
            isLocal: true,
            url : "<?php echo $view['router']->generate('ifresco_client_metadata_view') ?>",
            data: "nodeId="+nodeId+"&containerName=<?php echo $addContainer; ?>&height="+metaHeight,
            success : function (data) {
                $("#metadataWindow<?php echo $containerName; ?>").html('');
                $("#metadataContent<?php echo $containerName; ?>").unmask();

                $("#metadataWindow<?php echo $containerName; ?>").html(data);
                Ext.getCmp('copy-link<?php echo $containerName; ?>').handler();
            },
            beforeSend: function(xhr) {
                $("#metadataContent<?php echo $containerName; ?>").mask("<?php echo $view['translator']->trans('Loading...'); ?>",300);
            },
            error:function(jqXHR, textStatus, errorThrown) {
                if (textStatus != 'abort')
                    $("#metadataWindow<?php echo $containerName; ?>").html('');
                $("#metadataContent<?php echo $containerName; ?>").unmask();
            }
        });
    }
}

function loadParentMetaData<?php echo $containerName; ?>(nodeId) {

    if (nodeId !== null & typeof nodeId !== 'undefined') {

        var metaHeight = $("#parentMetadataContent<?php echo $containerName; ?>").height();
        if ($.browser.msie) {
            $("#metadataWindow<?php echo $containerName; ?>").html('');
            metaHeight = $("#parentMetadataContent<?php echo $containerName; ?>").height();
        }

        jQuery14.manageAjax.add('metadata', {
            isLocal: true,
            url : "<?php echo $view['router']->generate('ifresco_client_metadata_view') ?>",
            data: "ofParent=true&nodeId="+nodeId+"&containerName=<?php echo $addContainer; ?>&height="+metaHeight,
            success : function (data) {
                $("#parentMetadataWindow<?php echo $containerName; ?>").html('');
                $("#parentMetadataContent<?php echo $containerName; ?>").unmask();

                $("#parentMetadataWindow<?php echo $containerName; ?>").html(data);
            },
            beforeSend: function(xhr) {
                $("#parentMetadataContent<?php echo $containerName; ?>").mask("<?php echo $view['translator']->trans('Loading...'); ?>",300);
            },
            error:function(jqXHR, textStatus, errorThrown) {
                if (textStatus != 'abort')
                    $("#parentMetadataWindow<?php echo $containerName; ?>").html('');
                $("#parentMetadataContent<?php echo $containerName; ?>").unmask();
            }
        });
    }
}

//function fillAspectsWindow<?php echo $containerName; ?>(myNodeId) {
function fillAspectsWindow<?php echo $containerName; ?>(myNodeId) {

    $.ajax({
        cache: false,
        url : "<?php echo $view['router']->generate('ifresco_client_metadata_add_aspects') ?>",
        data: 'nodeId='+myNodeId,

        success : function (data) {
            $("#aspects-window<?php echo $containerName; ?>").unmask();
            $("#aspects-window-panel<?php echo $containerName; ?>").html(data);
        },
        beforeSend : function() {

            $("#aspects-window<?php echo $containerName; ?>").mask("<?php echo $view['translator']->trans('Loading...'); ?>",300);
            $("#aspects-window-panel<?php echo $containerName; ?>").html('');
        }
    });
}


function quickAddAspect<?php echo $containerName; ?>(myNodeId,aspect) {
    var postData = "nodeId="+myNodeId+"&aspects="+aspect;
    $.post("<?php echo $view['router']->generate('ifresco_client_metadata_save_aspects') ?>", postData, function(data) {
        var succes = data.success;
        var nodeId = data.nodeId;

        if (PanelNodeId<?php echo $containerName; ?> == nodeId)
            loadMetaData<?php echo $containerName; ?>(nodeId);
    }, "json");
}

var winAspects<?php echo $containerName; ?> = null;

function manageAspects<?php echo $containerName; ?>(myNodeId) {

    if (myNodeId !== null & typeof myNodeId !== 'undefined') {
        if(!winAspects<?php echo $containerName; ?>) {
            winAspects<?php echo $containerName; ?> = Ext.create('Ext.window.Window', {
                modal:true,
                //renderTo:'aspects-window<?php echo $containerName; ?>',
                layout:'fit',
                width:516,
                height:417,
                closeAction:'hide',
                title:'<?php echo $view['translator']->trans('Add Aspect'); ?>',
                plain: true,
                constrain: true,
                id:'aspects-window<?php echo $containerName; ?>',

                items: Ext.create('Ext.panel.Panel', {
                    id: 'aspects-window-panel<?php echo $containerName; ?>',
                    layout:'fit',
                    border:false
                }),
                listeners:{
                    'beforeshow':{
                        fn:function() {
                            $(".PDFRenderer").hide();
                        }
                    },
                    'hide': {
                        fn:function() {
                            $(".PDFRenderer").show();

                            $("#aspects-window-panel<?php echo $containerName; ?>").html('');
                        }
                    }
                },

                buttons: [{
                    text: '<?php echo $view['translator']->trans('Save'); ?>',
                    handler: function() {
                        var postData = getSelectedAspects();

                        $("#aspects-window<?php echo $containerName; ?>").mask("<?php echo $view['translator']->trans('Loading...'); ?>",300);
                        $.post("<?php echo $view['router']->generate('ifresco_client_metadata_save_aspects') ?>", postData, function(data) {
                            var succes = data.success;
                            var nodeId = data.nodeId;

                            loadMetaData<?php echo $containerName; ?>(nodeId);

                            $("#aspects-window<?php echo $containerName; ?>").unmask();
                        }, "json");

                        winAspects<?php echo $containerName; ?>.hide(this);

                    }
                },
                    {
                        text: '<?php echo $view['translator']->trans('Close'); ?>',
                        handler: function() {
                            $("#aspects-window-panel<?php echo $containerName; ?>").html('');
                            winAspects<?php echo $containerName; ?>.hide(this);
                        }
                    }]
            });

        }
        else {

        }

        fillAspectsWindow<?php echo $containerName; ?>(myNodeId);
        winAspects<?php echo $containerName; ?>.show();

    }
}

var winVersionLookup<?php echo $containerName; ?> = null;
function versionLookup<?php echo $containerName; ?>(data) {
    if (versionStore<?php echo $containerName; ?> !== null) {
        if (versionStore<?php echo $containerName; ?>.getCount() > 0) {
            var rowNode = versionStore<?php echo $containerName; ?>.getAt(0);

            var versionLookupTpl<?php echo $containerName; ?> = new Ext.Template('<div><div style="float:left;width:40%;"><label><b>Version:</b></label> <i>{version}</i></div><div style="float:left;width:40%;"><label><b><?php echo $view['translator']->trans('Date:'); ?></b></label> <i>{dateFormat}</i></div><div style="float:left;width:40%;"><label><b><?php echo $view['translator']->trans('Author:'); ?></b></label> <i>{author}</i></div><div style="float:left;"><label><b><?php echo $view['translator']->trans('Note:'); ?></b></label></div><div style="float:left;">&nbsp;<i>{description}</i></div></div>');
            var versionLookupHtml<?php echo $containerName; ?> = versionLookupTpl<?php echo $containerName; ?>.apply(rowNode.data);


            winVersionLookup<?php echo $containerName; ?> = Ext.create('Ext.window.Window', {
                modal:true,
                id:'version-lookup-window<?php echo $containerName; ?>',
                layout:'fit',
                width:650,
                height:630,
                boxMinWidth:650,
                boxMinHeight:630,
                boxMaxHeight:630,
                closeAction:'destroy',
                constrain: true,
                title:"<?php echo $view['translator']->trans('Detailed Version Information'); ?>",
                plain: true,

                items: Ext.create('Ext.panel.Panel', {
                    //id:'version-lookup-window-panel<?php echo $containerName; ?>',
                    layout:{
                        type: 'hbox',
                        align : 'stretch',
                        pack  : 'start'
                    },
                    height:'100%',
                    bodyStyle:'background-color:#e0e8f6;',
                    border:false,
                    tbar:[{
                        iconCls: 'add-version',
                        id: 'add-version-det<?php echo $containerName; ?>',
                        cls: 'x-btn-icon',
                        tooltip: '<?php echo $view['translator']->trans('New Version'); ?>',
                        scope:this,
                        handler: function(){
                            SelectedVersion<?php echo $containerName; ?> = {nodeId: PanelNodeId<?php echo $containerName; ?>};
                            var data = {nodeId: PanelNodeId<?php echo $containerName; ?>};
                            createNewVersion<?php echo $containerName; ?>(data,false);
                        }
                    },
                        {
                            iconCls: 'upload-version',
                            id: 'upload-version-det<?php echo $containerName; ?>',
                            cls: 'x-btn-icon',
                            tooltip: '<?php echo $view['translator']->trans('Upload new Version'); ?>',
                            scope:this,
                            handler: function(){
                                SelectedVersion<?php echo $containerName; ?> = {nodeId: PanelNodeId<?php echo $containerName; ?>};
                                var data = {nodeId: PanelNodeId<?php echo $containerName; ?>};
                                createNewVersion<?php echo $containerName; ?>(data,true);
                            }
                        },'-',{
                            iconCls: 'download-node',
                            id: 'download-selected-version-det<?php echo $containerName; ?>',
                            tooltip: '<?php echo $view['translator']->trans('Download Selected Version'); ?>',
                            scope: this,
                            disabled: true,
                            handler: function(){
                                versionList<?php echo $containerName; ?>.fireEvent('itemdblclick', versionList<?php echo $containerName; ?>, null, null, Ext.getCmp('version-lookup-view<?php echo $containerName; ?>').getSelectionModel().getLastSelected().index);
                            }
                        },'-',{
                            iconCls: 'revert-version',
                            id: 'revert-selected-version-det<?php echo $containerName; ?>',
                            tooltip: '<?php echo $view['translator']->trans('Revert to Selected Version'); ?>',
                            scope: this,
                            disabled: true,
                            handler: function(){

                                var selectedVersion = Ext.getCmp('version-lookup-view<?php echo $containerName; ?>').getSelectionModel().getLastSelected();

                                var ParentNodeId = selectedVersion.data.nodeRef;
                                var Version = selectedVersion.data.version;
                                var VersionId = selectedVersion.data.nodeId;

                                SelectedVersion<?php echo $containerName; ?> = {nodeId: ParentNodeId,versionNodeId:VersionId,version:Version};
                                Ext.MessageBox.show({
                                    title: '<?php echo $view['translator']->trans('Revert to Version'); ?>',
                                    msg: "<?php echo $view['translator']->trans('Are you sure you want to revert to Version:'); ?> <b>"+Version+"</b>",
                                    buttons: Ext.MessageBox.YESNO,
                                    icon: Ext.MessageBox.INFO,
                                    fn:revertVersion<?php echo $containerName; ?>
                                });
                            }
                        }],
                    items: [

                        Ext.create('Ext.list.ListView', {
                            store: versionStore<?php echo $containerName; ?>,
                            header:false,
                            multiSelect: false,
                            singleSelect: true,
                            height:562,
                            width: 170,
                            id:'version-lookup-view<?php echo $containerName; ?>',
                            loadingText: '<?php echo $view['translator']->trans('Loading...'); ?>',
                            deferEmptyText: false,
                            emptyText: '<span style="font-size:12px;"><img src="/images/icons/information.png" align="absmiddle"> <?php echo $view['translator']->trans('This document has no version history.'); ?></span>',
                            reserveScrollOffset: true,

                            columns: [{
                                header: '<?php echo $view['translator']->trans('Version'); ?>',
                                dataIndex: 'version',
                                width: 60
                            },
                                {
                                    header: '<?php echo $view['translator']->trans('Date'); ?>',
                                    xtype: 'datecolumn',
                                    format: '<?php echo $DateFormat; ?> <?php echo $TimeFormat; ?>',
                                    dataIndex: 'date',
                                    width: 110
                                }],

                            listeners: {
                                itemcontextmenu: function(gridx, record, item, index, event, eOpts){
                                    var existingMenu = Ext.getCmp('version-detail-ctx<?php echo $containerName; ?>');
                                    if (existingMenu !== null && typeof existingMenu !== 'undefined') {
                                        existingMenu.destroy();
                                    }

                                    var selectedVersion = versionStore<?php echo $containerName; ?>.getAt(index);
                                    var ParentNodeId = selectedVersion.data.nodeRef;
                                    var Version = selectedVersion.data.version;
                                    var VersionId = selectedVersion.data.nodeId;

                                    var selectedItem = Ext.getCmp('dataGrid<?php echo $containerName; ?>').getSelectionModel().getSelection()[0] || false;

                                    var editPerm = false;
                                    if(selectedItem)
                                    {
                                        editPerm = selectedItem.data.alfresco_perm_edit;
                                    }

                                    event.stopEvent();
                                    var mnxContext = Ext.create('Ext.menu.Menu', {
                                        id:'version-detail-ctx<?php echo $containerName; ?>',
                                        items: [{
                                            iconCls: 'revert-version',
                                            text: '<?php echo $view['translator']->trans('Revert to this Version'); ?>',
                                            scope:this,
                                            disabled: !editPerm,
                                            handler: function(){
                                                SelectedVersion<?php echo $containerName; ?> = {nodeId: ParentNodeId,versionNodeId:VersionId,version:Version};
                                                Ext.MessageBox.show({
                                                    title: '<?php echo $view['translator']->trans('Revert to Version'); ?>',
                                                    msg: "<?php echo $view['translator']->trans('Are you sure you want to revert to Version:'); ?> <b>"+Version+"</b>",
                                                    buttons: Ext.MessageBox.YESNO,
                                                    icon: Ext.MessageBox.INFO,
                                                    fn:revertVersion<?php echo $containerName; ?>
                                                });
                                            }
                                        },'-',{
                                            iconCls: 'add-version',
                                            text: '<?php echo $view['translator']->trans('New Version'); ?>',
                                            scope: this,
                                            disabled: !editPerm,
                                            handler: function(){
                                                SelectedVersion<?php echo $containerName; ?> = {nodeId: ParentNodeId,versionNodeId:VersionId,version:Version};
                                                var data = {nodeId: ParentNodeId,versionNodeId:VersionId,version:Version};
                                                createNewVersion<?php echo $containerName; ?>(data,false);
                                            }
                                        },
                                            {
                                                iconCls: 'upload-version',
                                                text: '<?php echo $view['translator']->trans('Upload new Version'); ?>',
                                                scope:this,
                                                disabled: !editPerm,
                                                handler: function(){
                                                    SelectedVersion<?php echo $containerName; ?> = {nodeId: ParentNodeId,versionNodeId:VersionId,version:Version};
                                                    var data = {nodeId: ParentNodeId,versionNodeId:VersionId,version:Version};
                                                    createNewVersion<?php echo $containerName; ?>(data,true);
                                                }
                                            },'-',{
                                                iconCls: 'download-node',
                                                text: '<?php echo $view['translator']->trans('Download this Version'); ?>',
                                                scope: this,
                                                handler: function(){
                                                    versionList<?php echo $containerName; ?>.fireEvent('itemdblclick', versionList<?php echo $containerName; ?>, null, null, index);
                                                }
                                            }]
                                    });
                                    mnxContext.showAt(event.xy);
                                },

                                itemclick: function() {
                                    var listView = Ext.getCmp('version-lookup-view<?php echo $containerName; ?>');
                                    var infoPanel = Ext.getCmp('version-lookup-info-panel<?php echo $containerName; ?>');
                                    if (infoPanel && listView) {
                                        var selNode = listView.getView().selModel.getSelection();
                                        versionLookupTpl<?php echo $containerName; ?>.overwrite(infoPanel.body, selNode[0].data);
                                        var versionTabPanel = Ext.getCmp('version-lookup-tab<?php echo $containerName; ?>');
                                        if (versionTabPanel) {
                                            var activeTab = versionTabPanel.activeTab;
                                            if (typeof activeTab !== 'undefined') {
                                                if (activeTab.title === "<?php echo $view['translator']->trans('Preview'); ?>")
                                                    loadVersionPreview<?php echo $containerName; ?>(selNode[0].data.nodeId);
                                                else if (activeTab.title === "<?php echo $view['translator']->trans('Metadata'); ?>") {
                                                    loadVersionMetaData<?php echo $containerName; ?>(selNode[0].data.nodeId);
                                                }
                                            }
                                        }
                                    }
                                },
                                selectionchange: function(t, selected, eOpts) {
                                    Ext.getCmp('download-selected-version-det<?php echo $containerName; ?>').setDisabled(!selected.length);

                                    var selectedItem = Ext.getCmp('dataGrid<?php echo $containerName; ?>').getSelectionModel().getSelection()[0] || false;

                                    var editPerm = false;
                                    if(selectedItem)
                                    {
                                        editPerm = selectedItem.data.alfresco_perm_edit;
                                    }

                                    Ext.getCmp('revert-selected-version-det<?php echo $containerName; ?>').setDisabled(!selected.length || !editPerm);

                                }
                            }
                        })
                        ,
                        {
                            flex:1,
                            layout: {
                                type: 'vbox',
                                align : 'stretch',
                                pack  : 'start'
                            },
                            bodyStyle:'background-color:#e0e8f6;',
                            items: [
                                {
                                    bodyStyle:'background-color:#e0e8f6;padding:5px;',
                                    height:80,
                                    id:'version-lookup-info-panel<?php echo $containerName; ?>',
                                    html:versionLookupHtml<?php echo $containerName; ?>,
                                    border:false,
                                    frame:false
                                },
                                {
                                    flex:1,
                                    xtype: 'tabpanel',
                                    border:false,
                                    plain: true,
                                    id:'version-lookup-tab<?php echo $containerName; ?>',
                                    defaults:{autoHeight: true},
                                    activeTab: 0,
                                    items: [{
                                        title: '<?php echo $view['translator']->trans('Preview'); ?>',
                                        cls: 'inner-tab-custom',
                                        layout:'fit',
                                        id:'version-lookup-preview<?php echo $containerName; ?>'
                                    }
                                        /*,{
                                         title: '<?php echo $view['translator']->trans('Metadata'); ?>',
                                         cls: 'inner-tab-custom',
                                         layout:'fit',
                                         id:'version-lookup-metadata<?php echo $containerName; ?>'
                                         }*/
                                    ],
                                    listeners: {
                                        tabchange: function(tabPanel, tab){
                                            if (typeof tab !== 'undefined') {
                                                var listView = Ext.getCmp('version-lookup-view<?php echo $containerName; ?>');
                                                if (listView) {
                                                    var selNode = listView.getSelectedRecords();
                                                    if (typeof selNode === 'undefined' || typeof selNode[0] === 'undefined')
                                                        return;
                                                    var title = tab.title;
                                                    title = title.toLowerCase();

                                                    var nodeId = selNode[0].data.nodeId;
                                                    if (typeof nodeId !== 'undefined' && nodeId !== null) {
                                                        switch(title) {
                                                            case "metadata":
                                                                loadVersionMetaData<?php echo $containerName; ?>(nodeId);
                                                                break;
                                                            case "preview":
                                                                loadVersionPreview<?php echo $containerName; ?>(nodeId);
                                                                break;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            ]

                        }
                    ]
                }),
                listeners:{
                    'beforeshow':{
                        fn:function() {

                        }
                    },
                    'close': {
                        fn:function() {
                            //$("#version-lookup-window-panel<?php echo $containerName; ?>").html('');                        
                        }
                    }
                },

                buttons: [{
                    text: '<?php echo $view['translator']->trans('Close'); ?>',
                    handler: function() {
                        //$("#version-lookup-window-panel<?php echo $containerName; ?>").html('');                    
                        winVersionLookup<?php echo $containerName; ?>.close(this);
                    }
                }]
            });


            winVersionLookup<?php echo $containerName; ?>.show();

            var listView = Ext.getCmp('version-lookup-view<?php echo $containerName; ?>');
            if (listView) {
                //listView.getStore().load({params: data });
                listView.getView().select(0);

                loadVersionPreview<?php echo $containerName; ?>(rowNode.data.nodeId);
            }
        }
    }
}

function loadVersionPreview<?php echo $containerName; ?>(nodeId) {
    /*var previewHeight = $("#version-lookup-tab<?php echo $containerName; ?>").height()-30;*/
    var previewHeight = "450";
    $.ajax({
        cache: false,
        url : "<?php echo $view['router']->generate('ifresco_client_view_index') ?>",
        data: "nodeId=workspace://version2Store/"+nodeId+"&height="+previewHeight+"px",
        success : function (data) {
            $("#version-lookup-tab<?php echo $containerName; ?>").unmask();
            $("#version-lookup-preview<?php echo $containerName; ?>").html(data);
        },
        beforeSend: function(xhr) {
            $("#version-lookup-preview<?php echo $containerName; ?>").html('');
            $("#version-lookup-tab<?php echo $containerName; ?>").mask("<?php echo $view['translator']->trans('Loading...'); ?>",300);

        }
    });
}

function loadVersionMetaData<?php echo $containerName; ?>(nodeId) {
    if (nodeId !== null & typeof nodeId !== 'undefined') {
        /*var metaHeight =  $("#version-lookup-tab<?php echo $containerName; ?>").height();  */
        var metaHeight = "480";

        $.ajax({
            cache: false,
            url : "<?php echo $view['router']->generate('ifresco_client_metadata_view') ?>",
            data: "nodeId=workspace://version2Store/"+nodeId+"&containerName=<?php echo $addContainer; ?>&height="+metaHeight,
            success : function (data) {
                $("#version-lookup-tab<?php echo $containerName; ?>").unmask();
                $("#version-lookup-metadata<?php echo $containerName; ?>").html(data);
            },
            beforeSend: function(xhr) {
                $("#version-lookup-metadata<?php echo $containerName; ?>").html('');
                $("#version-lookup-tab<?php echo $containerName; ?>").mask("<?php echo $view['translator']->trans('Loading...'); ?>",300);
            }
        });
    }
}


function showNewVersionWindow<?php echo $containerName; ?>(data,title,btnText,saveFunc,addData) {
    //if(!winNewVersion<?php echo $containerName; ?>) {
    winNewVersion<?php echo $containerName; ?> = Ext.create('Ext.window.Window', {
        modal:true,
        id:'newversion-window<?php echo $containerName; ?>',
        layout:'fit',
        width:516,
        height:216,
        resizable: false,
        constrain: true,
        closeAction:'destroy',
        title:title,
        plain: true,

        items: Ext.create('Ext.panel.Panel', {
            id:'newversion-window-panel<?php echo $containerName; ?>',
            layout:'fit',
            bodyStyle:'background-color:#e0e8f6;',
            border:false
        }),
        listeners:{
            'beforeshow':{
                fn:function() {
                    $(".plupload").show();
                }
            },
            'close': {
                fn:function() {
                    $(".plupload").hide();
                    $("#newversion-window-panel<?php echo $containerName; ?>").html('');
                }
            }
        },

        buttons: [{
            id:'uploadNewVersionBtn', // no containerName here!!!
            text: btnText,
            handler: function() {
                var postData = getVersionWindowInfo(SelectedVersion<?php echo $containerName; ?>.nodeId);
                var SelectedVersionData = jQuery.extend(data, postData);
                SelectedVersionData.nodeId = SelectedVersion<?php echo $containerName; ?>.nodeId;
                eval(saveFunc+"(SelectedVersionData)");

            }
        },
            {
                text: '<?php echo $view['translator']->trans('Close'); ?>',
                handler: function() {
                    $("#newversion-window-panel<?php echo $containerName; ?>").html('');
                    winNewVersion<?php echo $containerName; ?>.close();
                }
            }]
    });
    //}
    winNewVersion<?php echo $containerName; ?>.show();

    $.ajax({
        cache: false,
        url : "<?php echo $view['router']->generate('ifresco_client_versioning_new_version') ?>"+addData,
        data: data,

        success : function (data) {
            $("#newversion-window<?php echo $containerName; ?>").unmask();
            $("#newversion-window-panel<?php echo $containerName; ?>").html(data);
        },
        beforeSend : function() {
            $("#newversion-window<?php echo $containerName; ?>").mask("<?php echo $view['translator']->trans('Loading...'); ?>",300);
            $("#newversion-window-panel<?php echo $containerName; ?>").html('');
        }
    });


}

function createNewVersion<?php echo $containerName; ?>(data,upload) {
    if (data !== null) {
        if (upload === true)
            showNewVersionWindow<?php echo $containerName; ?>(data,"<?php echo $view['translator']->trans('Upload a new Version'); ?>","<?php echo $view['translator']->trans('Save & Upload'); ?>","saveUploadNewVersion<?php echo $containerName; ?>","?enableUpload&filter="+SelectedVersion<?php echo $containerName; ?>.nodeId);
        else
            showNewVersionWindow<?php echo $containerName; ?>(data,"<?php echo $view['translator']->trans('Create a new Version'); ?>","<?php echo $view['translator']->trans('Save'); ?>","saveNewVersion<?php echo $containerName; ?>","");
    }
}

function saveUploadNewVersion<?php echo $containerName; ?>(data) {
    uploadNewVersion(data.nodeId, winNewVersion<?php echo $containerName; ?>,versionList<?php echo $containerName; ?>);
}

function revertVersion<?php echo $containerName; ?>(btn) {
    if (btn === "yes") {
        if (SelectedVersion<?php echo $containerName; ?> !== null) {
            showNewVersionWindow<?php echo $containerName; ?>(SelectedVersion<?php echo $containerName; ?>,"<?php echo $view['translator']->trans('Revert Version'); ?>","<?php echo $view['translator']->trans('Save'); ?>","saveRevertVersion<?php echo $containerName; ?>","?hideVersionNumber");
        }
    }
}


function saveNewVersion<?php echo $containerName; ?>(data) {
    if (SelectedVersion<?php echo $containerName; ?> !== null) {
        $.ajax({
            cache: false,
            url : "<?php echo $view['router']->generate('ifresco_client_versioning_create_new_version') ?>",
            data: data,
            error:function() {
                $("#newversion-window<?php echo $containerName; ?>").unmask();
                winNewVersion<?php echo $containerName; ?>.close();
            },
            success : function (data) {
                $("#newversion-window<?php echo $containerName; ?>").unmask();
                var jsonData = $.JSON.decode(data);
                var nodeIdOrg = jsonData.nodeId;
                versionList<?php echo $containerName; ?>.store.load({params:{'nodeId':nodeIdOrg}});
                winNewVersion<?php echo $containerName; ?>.close();
            },
            beforeSend : function() {
                $("#newversion-window<?php echo $containerName; ?>").mask("<?php echo $view['translator']->trans('Loading...'); ?>",300);
            }
        });
    }
    else
        winNewVersion<?php echo $containerName; ?>.close();
}

function saveRevertVersion<?php echo $containerName; ?>(data) {
    if (SelectedVersion<?php echo $containerName; ?> !== null) {
        $.ajax({
            cache: false,
            //type:"POST",
            url : "<?php echo $view['router']->generate('ifresco_client_versioning_revert_version') ?>",
            data: data,
            error:function() {
                $("#newversion-window<?php echo $containerName; ?>").unmask();
                winNewVersion<?php echo $containerName; ?>.close();
            },
            success : function (data) {
                $("#newversion-window<?php echo $containerName; ?>").unmask();
                var jsonData = $.JSON.decode(data);
                var nodeIdOrg = jsonData.nodeId;
                if (jsonData.success === true) {
                    Ext.MessageBox.show({
                        title: '<?php echo $view['translator']->trans('Success'); ?>',
                        msg: "<?php echo $view['translator']->trans('Successfully reverted to the Version:'); ?> <b>"+SelectedVersion<?php echo $containerName; ?>.version+"</b>",
                        buttons: Ext.MessageBox.OK,
                        icon: Ext.MessageBox.INFO
                    });
                }
                else {
                    Ext.MessageBox.show({
                        title: '<?php echo $view['translator']->trans('Error'); ?>',
                        msg: "<?php echo $view['translator']->trans('Something went wrong at the revert process'); ?>",
                        buttons: Ext.MessageBox.OK,
                        icon: Ext.MessageBox.WARNING,
                        fn:showRenderer
                    });
                }
                versionList<?php echo $containerName; ?>.store.load({params:{'nodeId':nodeIdOrg}});

                winNewVersion<?php echo $containerName; ?>.close();
            },
            beforeSend : function() {
                $("#newversion-window<?php echo $containerName; ?>").mask("<?php echo $view['translator']->trans('Loading...'); ?>",300);
            }
        });
    }
    else
        winNewVersion<?php echo $containerName; ?>.close();
}

var winSpecifyType<?php echo $containerName; ?> = null;
var SpecifyTypeStore<?php echo $containerName; ?> = null;
var SpecifyTypeCombo<?php echo $containerName; ?> = null;
var SpecifyTypeSelectedId<?php echo $containerName; ?> = null;

function specifyType<?php echo $containerName; ?>(myNodeId) {
    if (myNodeId !== null & typeof myNodeId !== 'undefined') {
        if (myNodeId.length > 0) {
            var multiNodes = false;
            if (myNodeId.length > 1)
                multiNodes = true;

            if (SpecifyTypeStore<?php echo $containerName; ?> === null) {
                SpecifyTypeStore<?php echo $containerName; ?> = Ext.create('Ext.data.JsonStore', {
                    autoDestroy: true,
                    proxy: {
                        type: 'ajax',
                        url: '<?php echo $view['router']->generate('ifresco_client_metadata_content_type_list') ?>',
                        reader: {
                            root: 'types',
                            idProperty: 'name'
                        }
                    },
                    storeId: 'SpecifyTypeStore<?php echo $containerName; ?>',
                    fields: ['name', 'title','description'],
                    listeners: {
                        'beforeload':{
                            fn:function() {
                                $("#specifytype-window<?php echo $containerName; ?>").mask("<?php echo $view['translator']->trans('Loading...'); ?>",300);
                            }
                        },
                        'load':{
                            fn:function() {
                                $("#specifytype-window<?php echo $containerName; ?>").unmask();
                            }
                        }
                    }
                });

                SpecifyTypeCombo<?php echo $containerName; ?> = Ext.create('Ext.form.ComboBox', {
                    store: SpecifyTypeStore<?php echo $containerName; ?>,
                    displayField:'title',
                    typeAhead: true,
                    id:'SpecifyTypeCombo<?php echo $containerName; ?>',
                    queryMode: 'local',
                    triggerAction: 'all',
                    deferEmptyText: false,
                    emptyText:'<?php echo $view['translator']->trans('Select a content type...'); ?>',
                    selectOnFocus:true,
                    listeners:{
                        'select': function(combo,record,index) {

                            if (typeof record !== 'undefined')
                                SpecifyTypeSelectedId<?php echo $containerName; ?> = record[0].data.name;
                            else
                                SpecifyTypeSelectedId<?php echo $containerName; ?> = null;
                        }
                    }
                });
            }

            winSpecifyType<?php echo $containerName; ?> = Ext.create('Ext.window.Window', {

                modal:true,
                contentEl:'specifytype-window<?php echo $containerName; ?>',
                layout:'fit',
                width:350,
                height:100,
                closeAction:'hide',
                constrain: true,
                title:'<?php echo $view['translator']->trans('Specify Type'); ?>',
                plain: true,

                listeners:{
                    'beforeshow':{
                        fn:function() {
                            $(".PDFRenderer").hide();
                        }
                    },
                    'hide': {
                        fn:function() {
                            $(".PDFRenderer").show();
                        }
                    }
                },

                items: [
                    SpecifyTypeCombo<?php echo $containerName; ?>
                ],
                buttons: [{
                    text:'<?php echo $view['translator']->trans('Save'); ?>',
                    disabled:false,
                    handler:function() {
                        var jsonNodes = $.toJSON(myNodeId);

                        $("#documentGrid<?php echo $containerName; ?>").mask("<?php echo $view['translator']->trans('Loading...'); ?>",300);
                        var type = SpecifyTypeSelectedId<?php echo $containerName; ?>;

                        if (type !== null && typeof type !== 'undefined') {
                            $("#specifytype-window<?php echo $containerName; ?>").mask("<?php echo $view['translator']->trans('Loading...'); ?>",300);
                            $.post("<?php echo $view['router']->generate('ifresco_client_metadata_save_content_type') ?>", "nodes="+jsonNodes+"&type="+type, function(data) {
                                var succes = data.success;
                                if (!multiNodes) {
                                    var nodeId = data.nodeId;
                                    loadMetaData<?php echo $containerName; ?>(nodeId);
                                }

                                if (succes === false) {
                                    $(".PDFRenderer").hide();
                                    Ext.MessageBox.show({
                                        title: '<?php echo $view['translator']->trans('Specify content type error!'); ?>',
                                        msg: '<?php echo $view['translator']->trans('An error occured please try it later again.'); ?>',
                                        buttons: Ext.MessageBox.OK,
                                        icon: Ext.MessageBox.ERROR,
                                        fn:showRenderer
                                    });
                                }
                                $("#documentGrid<?php echo $containerName; ?>").unmask();
                                $("#specifytype-window<?php echo $containerName; ?>").unmask();
                            }, "json");
                            winSpecifyType<?php echo $containerName; ?>.hide(this);
                        }
                    }
                },{
                    text: '<?php echo $view['translator']->trans('Close'); ?>',
                    handler: function(){
                        winSpecifyType<?php echo $containerName; ?>.hide(this);

                    }
                }]
            });

            //}
            //else {
            //
            //}   
            //SpecifyTypeSelectedId<?php echo $containerName; ?> = null; 
            SpecifyTypeStore<?php echo $containerName; ?>.load({params:{'nodeId':myNodeId}});
            winSpecifyType<?php echo $containerName; ?>.show();
        }

    }
}

function openFolder<?php echo $containerName; ?>(nodeId,nodeText) {
    var tabnodeid = nodeId.replace(/-/g,"");
    addTabDynamic('tab-'+tabnodeid,nodeText);

    $.ajax({
        cache: false,
        url : "<?php echo $view['router']->generate('ifresco_client_data_grid_index') ?>?containerName="+tabnodeid+"&addContainer=<?php echo $nextContainer; ?>&columnsetid="+currentColumnsetid<?php echo $containerName; ?>,


        success : function (data) {
            $("#overAll").unmask();
            $("#tab-"+tabnodeid).html(data);

            eval("reloadGridData"+tabnodeid+"({params:{'nodeId':nodeId,'columnsetid':currentColumnsetid<?php echo $containerName; ?>}});");
        },
        beforeSend: function(req) {
            $("#overAll").mask("<?php echo $view['translator']->trans('Loading'); ?> "+nodeText+"...",300);
        }
    });
}

function deleteNode<?php echo $containerName; ?>(nodeId,nodeName,nodeType) {
    $(".PDFRenderer").hide();
    Ext.MessageBox.show({
        title:'<?php echo $view['translator']->trans('Delete?'); ?>',
        msg: '<?php echo $view['translator']->trans('Do you really want to delete:'); ?> <br><b>'+nodeName+'</b>',
        fn:function(btn) {
            if (btn === "yes") {
                $("#documentGrid<?php echo $containerName; ?>").mask("<?php echo $view['translator']->trans('Deleting'); ?> "+nodeName+" ...",300);
                $.post("<?php echo $view['router']->generate('ifresco_client_node_actions_delete_node') ?>", "nodeId="+nodeId+"&nodeType="+nodeType, function(data) {
                    var succes = data.success;
                    $("#documentGrid<?php echo $containerName; ?>").unmask();
                    if (succes === true) {

                        //Ext.MessageBox.alert('Deleted', 'Document <b>'+nodeName+'</b> deleted successfully.');
                        refreshGrid<?php echo $containerName; ?>();

                    }
                    else {
                        Ext.MessageBox.show({
                            title: '<?php echo $view['translator']->trans('Error'); ?>',
                            msg: data.message,
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.WARNING
                        })
                    }
                }, "json");
            }
            $(".PDFRenderer").show();
        },
        buttons: Ext.MessageBox.YESNO,
        icon: Ext.MessageBox.QUESTION
    });
}


function getLastParams<?php echo $containerName; ?>() {
    return lastParams<?php echo $containerName; ?>;
}

function deleteNodes<?php echo $containerName; ?>(nodes) {
    $(".PDFRenderer").hide();
    var jsonNodes = encodeURIComponent($.toJSON(nodes));
    var nodeNames = "";
    var nodeNamesLoad = "";
    for (var i = 0; i < nodes.length; ++i) {
        nodeNames += nodes[i].nodeName+"<br>";
        nodeNamesLoad += "<i>"+nodes[i].nodeName+"</i><br>";
    }
    Ext.MessageBox.show({
        title:'<?php echo $view['translator']->trans('Delete?'); ?>',
        msg: '<?php echo $view['translator']->trans('Do you really want to delete:'); ?> <br><b>'+nodeNames+'</b>',
        fn:function(btn) {
            if (btn === "yes") {
                $("#documentGrid<?php echo $containerName; ?>").mask("<?php echo $view['translator']->trans('Deleting'); ?> <br>"+nodeNamesLoad,300);
                $.post("<?php echo $view['router']->generate('ifresco_client_node_actions_delete_nodes') ?>", "nodes="+jsonNodes, function(data) {
                    var succes = data.success;
                    $("#documentGrid<?php echo $containerName; ?>").unmask();
                    if (succes === true) {
                        //Ext.MessageBox.alert('Deleted', 'Document <b>'+nodeName+'</b> deleted successfully.');
                        refreshGrid<?php echo $containerName; ?>();
                    }
                    else {
                        Ext.MessageBox.show({
                            title: '<?php echo $view['translator']->trans('Error'); ?>',
                            msg: data.message,
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.WARNING
                        })
                    }
                }, "json");
            }
            $(".PDFRenderer").show();
        },
        buttons: Ext.MessageBox.YESNO,
        icon: Ext.MessageBox.QUESTION
    });
}

function ocrFiles<?php echo $containerName; ?>(nodes) {
    var encodedNodes = $.toJSON(nodes);
    // 
    //console.log(encodedNodes);
    $.ajax({
        cache: false,
        type:"POST",
        url: "<?php echo $view['router']->generate('ifresco_client_node_actions_ocr_files') ?>",
        data: "nodes="+encodedNodes,
        success : function (data) {
            $("#overAll").unmask();
        },
        beforeSend: function(req) {
            $("#overAll").mask("<?php echo $view['translator']->trans('Send files to OCR Engine...'); ?>",300);
        }

    });
}

var lastWidth = 0;
var photo_array = [];
var initHeight = 400;

function manageThumbData() {
    lastWidth = $("div#toppics").innerWidth()-20;

    $("div.toppicrow").width(lastWidth);
    processPhotos(photo_array);
}

function loadThumbnailView<?php echo $containerName; ?>() {
    var container = $("#thumbnailContainer<?php echo $containerName; ?>");
    if (container != null && mainGrid<?php echo $containerName; ?>.store.data.items.length > 0) {
        container.height($("#thumbnailGrid<?php echo $containerName; ?>").height()-40);

        var lastStoreParams = lastParams<?php echo $containerName; ?>.params;
        lastStoreParams["thumbs"] = true;

        $("#thumbnailContainer<?php echo $containerName; ?>").mask("<?php echo $view['translator']->trans('Loading Thumbnails...'); ?>",300);
        mainGrid<?php echo $containerName; ?>.store.on('load', function(){
            photo_array = mainGrid<?php echo $containerName; ?>.store.data.items;

            manageThumbData();
            $("#thumbnailContainer<?php echo $containerName; ?>").unmask();
        });

        mainGrid<?php echo $containerName; ?>.store.load({params:lastStoreParams});

        $(window).resize(function() {
            var nowWidth = $("div#toppics").innerWidth();
            if( nowWidth * 1.1 < lastWidth || nowWidth * 0.9 > lastWidth )
            {
                manageThumbData();
            }
        });
    }
}

function processPhotos(photos) {

    var d = jQuery16("div.toppicrow");
    var w = d.eq(0).innerWidth();
    // initial height - effectively the maximum height +/- 10%;
    var h = initHeight;
    var border = 1;

    var ws = [];
    $.each(photos, function(key, val) {
        //var wt = parseInt(val.width_n, 10);
        //var ht = parseInt(val.height_n, 10);
        var wt = parseInt(val.data.alfresco_thumbnail_width, 10);
        var ht = parseInt(val.data.alfresco_thumbnail_height, 10);
        if( ht != h ) { wt = Math.floor(wt * (h / ht)); }
        ws.push(wt);
    });

    var baseLine = 0;
    var rowNum = 0;
    var total = 0;
    var currentRow = 0;
    while(rowNum++ != d.length)
    {
        var d = jQuery16("div.toppicrow");
        var d_row = d.eq(rowNum-1);
        d_row.empty();

        // number of images appearing in this row
        var c = 0;
        // total width of images in this row - including margins
        var tw = 0;

        // calculate width of images and number of images to view in this row.
        while( tw * 1.1 < w)
        {
            tw += ws[baseLine + c++] + border * 2;
        }

        // Ratio of actual width of row to total width of images to be used.
        var r = w / tw;

        // image number being processed
        var i = 0;
        // reset total width to be total width of processed images
        tw = 0;
        // new height is not original height * ratio
        var ht = Math.floor(h * r);
        while( i < c )
        {
            var photo = photos[baseLine + i];
            // Calculate new width based on ratio
            var wt = Math.floor(ws[baseLine + i] * r);
            // add to total width with margins
            tw += wt + border * 2;
            // Create image, set src, width, height and margin
            (function() {
                if (typeof photo != 'undefined' && photo != null) {
                    if(photo.data.alfresco_type == '{http://www.alfresco.org/model/content/1.0}folder')
                    {
                        var img = jQuery16('<div data-index="'+currentRow+'" title="'+photo.data.alfresco_thumbname+'" style="width: '+wt+'px; height:'+ht+'px; line-height:'+ht+'px;" class="thumb-folder-block">'+photo.data.alfresco_thumbname+'</div>').css("margin", border + "px");
                    }
                    else {
                        var img = jQuery16('<img src="'+ photo.data.alfresco_thumbnail +'" style="cursor:pointer;" data-index="'+currentRow+'" width="'+wt+'" height="'+ht+'" alt="'+photo.data.alfresco_thumbname+'" title="'+photo.data.alfresco_thumbname+'" style="float:left;"/>').css("margin", border + "px");
                    }
                    //var url = photo.url;
                    img.click(function() { var index = $(this).attr("data-index");  mainGrid<?php echo $containerName; ?>.getSelectionModel().selectRow(parseInt(index)); });
                    d_row.append(img);
                    currentRow++;
                }
            })();
            i++;
            total++;
        }

        // if total width is slightly smaller than
        // actual div width then add 1 to each
        // photo width till they match
        i = 0;
        while( tw < w )
        {
            var img1 = d_row.find("img:nth-child(" + (i + 1) + ")");
            img1.width(img1.width() + 1);
            i = (i + 1) % c;
            tw++;
        }
        // if total width is slightly bigger than
        // actual div width then subtract 1 from each
        // photo width till they match
        i = 0;
        while( tw > w )
        {
            var img2 = d_row.find("img:nth-child(" + (i + 1) + ")");
            img2.width(img2.width() - 1);
            i = (i + 1) % c;
            tw--;
        }

        // set row height to actual height + margins
        d_row.height(ht + border * 2);

        baseLine += c;

        if (total < photos.length) {
            $("#thumbnailContainer<?php echo $containerName; ?>").append('<div class="toppicrow"></div>');
        }
    }
}

function enableClipBtns<?php echo $containerName; ?>() {
    var pasteCopyBtn = Ext.getCmp("pastecopy-clipboard<?php echo $containerName; ?>");
    var pasteCutBtn = Ext.getCmp("pastecut-clipboard<?php echo $containerName; ?>");
    var pasteLinkBtn = Ext.getCmp("pastelink-clipboard<?php echo $containerName; ?>");

    var perms = mainGrid<?php echo $containerName; ?>.store.perms;
    var alfresco_perm_create = false;
    if(perms !== false) {
        alfresco_perm_create = perms.alfresco_perm_create;
    }

    pasteCopyBtn.setDisabled(!alfresco_perm_create);
    pasteCutBtn.setDisabled(!alfresco_perm_create);
    pasteLinkBtn.setDisabled(!alfresco_perm_create);
}


var winEmail<?php echo $containerName; ?> = null;
var emailForm<?php echo $containerName; ?> = null;

function sendMail<?php echo $containerName; ?>(nodes) {
    var attachments = "";
    var clearNodes = [];
    $.each(nodes,function(index,node) {
        if(node.shortType == 'file') {
            attachments += node.docName+"&nbsp;&nbsp;&nbsp;";
            clearNodes.push(node);
        }
    });
    nodes = clearNodes;
    var encodedNodes = $.toJSON(nodes);
    emailForm<?php echo $containerName; ?> = Ext.create('Ext.FormPanel', {
        labelAlign: 'left',
        frame:true,
        bodyStyle:'padding:5px 5px 0',
        height:300,
        items: [{
            layout: 'form',
            items: [
                {
                    xtype:'textfield',
                    fieldLabel: '<?php echo $view['translator']->trans('To'); ?>',
                    name: 'to',
                    anchor:'100%',
                    vtype:'multiemail',
                    allowBlank:false
                },{
                    xtype:'textfield',
                    fieldLabel: '<?php echo $view['translator']->trans('Cc'); ?>',
                    name: 'cc',
                    anchor:'100%',
                    vtype:'multiemail'
                },{
                    xtype:'textfield',
                    fieldLabel: '<?php echo $view['translator']->trans('Bcc'); ?>',
                    name: 'bcc',
                    anchor:'100%',
                    vtype:'multiemail'
                },{
                    xtype:'textfield',
                    fieldLabel: '<?php echo $view['translator']->trans('Subject'); ?>',
                    name: 'subject',
                    anchor:'100%'
                },{
                    xtype:'panel',
                    fieldLabel: '<?php echo $view['translator']->trans('Attachments'); ?>',
                    anchor:'100%',
                    html:attachments
                },{
                    xtype:'hidden',
                    name:'nodes',
                    value:encodedNodes
                }
            ]
        },{
            xtype:'htmleditor',
            id:'body',
            //fieldLabel:'Body',
            height:150,
            name: 'body',
            anchor:'100%',
            hideLabel: true,
            anchor: '100% -130'
        }]
    });

    winEmail<?php echo $containerName; ?> = Ext.create('Ext.Window', {
        modal:true,
        id:'email-window<?php echo $containerName; ?>',
        title:'<?php echo $view['translator']->trans('Send Attachment(s) via Email'); ?>',
        plain: true,
        constrain: true,
        buttonAlign: 'right', //buttons aligned to the right  
        //bodyStyle:'background-color:#fff;padding: 10px',   
        width:540,
        height:400,
        closeAction:'destroy',
        plain: true,
        resizable:true,

        items: emailForm<?php echo $containerName; ?>,
        listeners:{
            'beforeshow':{
                fn:function() {
                    $(".PDFRenderer").hide();
                }
            },
            'hide': {
                fn:function() {
                    $(".PDFRenderer").show();
                }
            }
        },
        buttons:[
            {
                text:'<?php echo $view['translator']->trans('Send'); ?>',
                handler:function() {
                    if(emailForm<?php echo $containerName; ?>.getForm().isValid()) {
                        emailForm<?php echo $containerName; ?>.getForm().submit({
                            url: '<?php echo $view['router']->generate('ifresco_client_node_actions_mail_node') ?>',
                            waitMsg: '<?php echo $view['translator']->trans('Sending Email'); ?>',
                            success: function(form, result) {
                                Ext.Msg.alert('<?php echo $view['translator']->trans('Success'); ?>', "<?php echo $view['translator']->trans('We could send your email successfully!'); ?>");
                                winEmail<?php echo $containerName; ?>.close();
                            },
                            failure: function(form, result) {
                                Ext.Msg.alert('<?php echo $view['translator']->trans('Error'); ?>', result.result.errorMsg);
                            }
                        });
                    }
                }
            },
            {
                text:'<?php echo $view['translator']->trans('Cancel'); ?>',
                handler: function() {
                    winEmail<?php echo $containerName; ?>.close(this);
                }
            }
        ]
    });

    winEmail<?php echo $containerName; ?>.show();
}

</script>
<style type="text/css">
    #documentGrid<?php echo $containerName; ?> {
        width:100%;
        padding:0;
        height:100%;
        margin:0;
    }

    #documentGrid<?php echo $containerName; ?> td, #documentGrid<?php echo $containerName; ?> th, #documentGrid<?php echo $containerName; ?> table {
        padding:0;
        margin:0;
    }

    #documentGrid<?php echo $containerName; ?> ul, #documentGrid<?php echo $containerName; ?> ul li {
        padding:0;
        margin:0;
    }

</style>


<div id="documentGrid<?php echo $containerName; ?>" containerId="<?php echo $containerName; ?>" addContainer="<?php echo $addContainer; ?>">
</div>

<div id="specifytype-window<?php echo $containerName; ?>" class="x-hidden x-body-masked" style="z-index:100;">

</div>


<div id="metadata-window<?php echo $containerName; ?>" class="x-hidden x-body-masked" style="z-index:100;">
    <div id="metadata-window-panel<?php echo $containerName; ?>">

    </div>
</div>
<div id="copy-button<?php echo $containerName; ?>"></div>
