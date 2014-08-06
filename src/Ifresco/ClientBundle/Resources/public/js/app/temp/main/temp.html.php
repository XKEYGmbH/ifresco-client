<?php
$isAdmin = $app->getSecurity()->getToken()->isAdmin();
$Culture = $app->getSession()->getLocale();

$CultureStr = preg_replace("/([a-zA-Z]+).*/","$1",$Culture);

// default nav
$mapOnValues = array("0"=>"folders","1"=>"categories","2"=>"favorites","3"=>"tags");
$ValueString = $view['settings']->getSetting("DefaultNav");
$MapKey = array_search($ValueString,$mapOnValues);
if (empty($MapKey))
    $MapKey = 0;
$DefaultNav = $MapKey;

$logoPath ='/images/logo94x50.png';
if(file_exists('images/custom_logo94x50.png'))
    $logoPath ='/images/custom_logo94x50.png';
?>
<html>
<head>
</head>
<body>
<div id="overAll">
<div id="north" class="x-hide-display">
    <div style="float:left;background-color:#FFFFFF;">
        <a id="page-logo" target="_blank" href="<? echo $view['settings']->getSetting("logoURL")?$view['settings']->getSetting("logoURL"):'http://www.ifresco.at/'?>">
            <img src="<? echo $logoPath?>" height="50" width="94" style="margin-left:10px;margin-top:4px;">
        </a>
    </div>

    <div id="clipBoardContainer" class="x-panel" style="display: none;">
        <div class="x-panel-header" style="float:left;height:49px;border-left:none;border-top:none;border-bottom:none;">
            <!--<div class="x-btn pasteClipboard"><img src="/images/toolbar/page_white_paste.png" title="Paste in active tab" border="0"></div>-->
            <div class="x-btn clearClipboard"><img src="/images/icons/cross.png" title="<?php echo $view['translator']->trans('Clear clipboard'); ?>" border="0" style="margin-top:5px;"></div>
        </div>
        <div id="clipBoardContent">
            <ul>

            </ul>
        </div>
    </div>

    <div style="float:right;width:700px;margin:2px;text-align:right;background-color:#FFFFFF;">
        <div id="topMenu">
            <ul>

                <li style="background-color:none;border:none;">

                    <div id="alfrescoSearch" style="margin:5px;float:left;">
                        <form action="" method="post" name="alfrescoSearchForm">
                            <a href="javascript:openTab('searchtab','<?php echo $view['translator']->trans('Advanced Search'); ?>','<?php echo $view['router']->generate('ifrescoClientSearchBundle_homepage'); ?>');">
                                <?php echo $view['translator']->trans('Advanced Search'); ?>
                            </a>
                            <input id="alfrescoSearchTerm" name="searchTerm" type="text">
                            <!--<button id="search_submit">Search</button>-->
                            <script type="text/javascript">
                            </script>

                            <input id="alfrescoSearchSubmit"
                                   class="fg-button ui-button ui-state-default"
                                   type="button"
                                   value="<?php echo $view['translator']->trans('Search'); ?>"
                                   style="cursor:pointer;font-size:10px;"
                                   title="<?php echo $view['translator']->trans('Search'); ?>">

                        </form>
                    </div>

                </li>
                <!--<li><a href="javascript:openTab('admintab','Administration','<?php echo $view['router']->generate('ifrescoClientAdminBundle_homepage') ?>');"><img align="absmiddle" src="/images/toolbar/computer.png" border="0" style="border:none;"/></a></li>
                    <li><a href="javascript:openTab('admintab','Administration','<?php echo $view['router']->generate('ifrescoClientAdminBundle_homepage') ?>');"><img align="absmiddle" src="/images/toolbar/user-icon.png" border="0" style="border:none;"/></a></li>-->

                <li id="horizontalBtn"><a href="javascript:arrangeWindows('horizontal');" title="<?php echo $view['translator']->trans('Horizontal Split'); ?>"><img align="absmiddle" src="/images/toolbar/split_bottom.png" border="0" style="border:none;"/></a></li>
                <li id="verticalBtn"><a href="javascript:arrangeWindows('vertical');" title="<?php echo $view['translator']->trans('Vertical Split'); ?>"><img align="absmiddle" src="/images/toolbar/split_left.png" border="0" style="border:none;"/></a></li>
                <li class="splitter"></li>
                <li id="clipboardBtn"><a href="javascript:openClipboard();" title="<?php echo $view['translator']->trans('Clipboard'); ?>"><img align="absmiddle" src="/images/toolbar/clipboard.png" border="0" style="border:none;"/></a></li>
                <li class="splitter"></li>
                <?php if ($isAdmin == true) { ?>
                    <li><a href="javascript:openTab('admintab','Administration','<?php echo $view['router']->generate('ifrescoClientAdminBundle_homepage') ?>');" title="<?php echo $view['translator']->trans('Administration'); ?>">
                            <img align="absmiddle" src="/images/toolbar/admin.png" border="0" style="border:none;"/>
                        </a></li>
                <?php } ?>
                <li class="splitter"></li>
                <li><a href="<?php echo $view['router']->generate('ifrescoClientLoginBundle_logout'); ?>" title="<?php echo $view['translator']->trans('Logout'); ?>"><img align="absmiddle" src="/images/toolbar/logout.png" border="0" style="border:none;"/></a></li>
            </ul>
        </div>
    </div>

</div>

<div id="west" class="x-hide-display">
<script type="text/javascript">
Ext.onReady(function(){

    var winSpaceFolder;
    var Tree = Ext.tree;

    treeStore = Ext.create('Ext.data.TreeStore', {
        proxy: {
            type: 'ajax',
            url: '<?php echo $view['router']->generate('ifrescoClientTreeBundle_getjson') ?>',
            actionMethods: 'POST',
            simpleSortMode:true
        },
        root: <?php echo $rootInfo?>,
        folderSort: true,
        sorters: [{
            property: 'text',
            direction: 'ASC'
        }]
    });



    var tree = Ext.create('Ext.tree.Panel', {
        title: '<?php echo $view['translator']->trans('Folders'); ?>',
        border: false,
        iconCls: 'foldersTree',
        store: treeStore,
        rootVisible:true,
        autoScroll:true,
        id: 'alfrescoTree',
        animate:true,
        ddConfig:false,
        height: 'auto',
        split: true,
        containerScroll: true,
        tbar:[
            '->',{
                iconCls: 'reload-tree',
                tooltip: '<?php echo $view['translator']->trans('Reload'); ?>',
                scope:this,
                handler: function(){
                    if(!tree.getStore().isLoading()) {
                        tree.getStore().getProxy().url = '<?php echo $view['router']->generate('ifrescoClientTreeBundle_getjson') ?>?reload=true';
                        tree.getStore().load();
                        tree.getStore().getProxy().url = '<?php echo $view['router']->generate('ifrescoClientTreeBundle_getjson') ?>';
                    }
                }
            }
        ],
        listeners: {
            itemcontextmenu: function(node, record, item, index ,e, eOpts) {
                var existingMenu = Ext.getCmp('foldersTree-ctx');
                if (existingMenu != null) {
                    existingMenu.destroy();
                }

                var editRights = record.raw?record.raw.alfresco_perm_edit:false;
                var delRights = record.raw?record.raw.alfresco_perm_delete:false;
                var cancelCheckoutRights = record.raw?record.raw.alfresco_perm_cancel_checkout:false;
                var createRights = record.raw?record.raw.alfresco_perm_create:false;
                var hasRights = record.raw?record.raw.alfresco_perm_permissions:false;
                var folder_path = record.raw?record.raw.alfresco_node_path:false;
                var type = record.raw?record.raw.alfresco_type:false;
                var DocName = record.raw?record.raw.text:false;

                var nodeId = record.data.id;


                var treeMenu = Ext.create('Ext.menu.Menu', {
                    id:'foldersTree-ctx',
                    items:[
                        {
                            iconCls:'upload-content',
                            id:'upload-tree-files',
                            disabled: !createRights,
                            text:'<?php echo $view['translator']->trans('Upload File(s)'); ?>',
                            cls: 'x-btn-icon',
                            handler: function(){
                                var nodeId = record.data.id;
                                uploadTreePanel.show();
                                uploadTreePanel.uploader.uploader.settings.uploadpath = uploadTreePanel.uploader.uploader.settings.url = '<?php echo $view['router']->generate('ifrescoClientUploadBundle_RESTUpload'); ?>?nodeId='+nodeId+'&overwrite=false&ocr=false';
                            },
                            scope: this
                        }

                        ,
                        {
                            iconCls: 'create-folder',
                            disabled: !createRights && nodeId != 'root',
                            text: '<?php echo $view['translator']->trans('Create Space'); ?>',
                            scope:this,
                            handler: function() {
                                var nodeId = record.data.id;
                                //if(!winSpaceFolder){
                                var winSpaceFolder = Ext.create('Ext.window.Window', {
                                    modal:true,
                                    id:'general-window-space',
                                    layout:'fit',
                                    width:500,
                                    height:350,
                                    closeAction:'destroy',
                                    constrain: true,
                                    title:'<?php echo $view['translator']->trans('Create Space'); ?>',
                                    plain: true,
                                    resizable: false,

                                    items: Ext.create('Ext.panel.Panel', {
                                        id: 'window-panel-space',
                                        layout:'fit',
                                        border:false
                                    }),

                                    listeners:{
                                        'beforeshow':{
                                            fn:function() {
                                                $(".PDFRenderer").hide();
                                                $("#general-window-space").mask("<?php echo $view['translator']->trans('Loading...'); ?>",300);
                                                $.ajax({
                                                    cache: false,
                                                    url : "<?php echo $view['router']->generate('ifrescoClientFolderActionsBundle_homepage') ?>",

                                                    success : function (data) {
                                                        $("#general-window-space").unmask();
                                                        $("#window-panel-space").html(data);
                                                        $('#spaceCreateForm #cm_name').focus();
                                                    }
                                                });
                                            }
                                        },
                                        'close': {
                                            fn:function() {
                                                $(".PDFRenderer").show();
                                            }
                                        }
                                    },

                                    buttons: [{
                                        text: '<?php echo $view['translator']->trans('Save'); ?>',
                                        handler: function() {

                                            $.post("<?php echo $view['router']->generate('ifrescoClientFolderActionsBundle_createspacepost') ?>", $("#spaceCreateForm").serialize()+"&nodeId="+nodeId, function(data) {
                                                if (data.success === "true") {

                                                    Ext.MessageBox.show({
                                                        title: '<?php echo $view['translator']->trans('Success'); ?>',
                                                        msg: data.message,
                                                        buttons: Ext.MessageBox.OK,
                                                        icon: Ext.MessageBox.INFO,
                                                        fn:showRenderer
                                                    });

                                                    Ext.getCmp("alfrescoTree").getStore().load();
                                                    //Ext.getCmp('alfrescoTree').render();

                                                    winSpaceFolder.close();

                                                    $("#window-panel-space").html('');

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
                                                $("#window-panel-space").html('');

                                                winSpaceFolder.close();
                                            }
                                        }]
                                });
                                //}
                                winSpaceFolder.show();
                            }
                        }
                        ,
                        {
                            iconCls: 'create-html',
                            disabled: !createRights,
                            text: '<?php echo $view['translator']->trans('Create HTML'); ?>',
                            scope:this,
                            handler: function() {
                                var nodeId = record.data.id;
                                createHTMLdoc(nodeId);

                            }
                        },
                        <? if($view['settings']->getSetting("scanViaSane") == 'true'){ ?>
                        {
                            iconCls:'scan-doc',
                            id:'scan-doc-tree',
                            text:'<?php echo $view['translator']->trans('Scan Document'); ?>',
                            cls: 'x-btn-icon',
                            xtype: 'sanebutton',
                            folder: record.data.text,
                            nodeId: record.data.id
                        }
                        <? } ?>
                        ,'-',
                        {
                            iconCls: 'add-tab',
                            text: '<?php echo $view['translator']->trans('Open in new tab'); ?>',
                            scope:this,
                            handler: function(){
                                //store.getAt(index).data.name;
                                var nodeId = record.data.id;
                                if (nodeId != "root")
                                    var nodeText = record.data.text;
                                else
                                    var nodeText = "<?php echo $view['translator']->trans('Repository'); ?>";

                                openFolder(nodeId,nodeText);

                            }
                        },
                        {
                            iconCls: 'add-clipboard',
                            text: '<?php echo $view['translator']->trans('Add to clipboard'); ?>',
                            //disabled:(editRights === true ? false : true),
                            disabled: nodeId == 'root',
                            scope:this,
                            handler: function(){
                                ClipBoard.addItem(nodeId,DocName);
                                ClipBoard.reloadClip();
                            }
                        },{
                            iconCls: 'view-metadata',
                            text: '<?php echo $view['translator']->trans('Edit Metadata'); ?>',
                            disabled:(editRights === true ? false : true),
                            scope:this,
                            handler: function(){
                                editMetadata(nodeId,DocName);
                            }
                        },{
                            iconCls: 'specify-type',
                            text: '<?php echo $view['translator']->trans('Specify type'); ?>',
                            disabled:(editRights === true ? false : true),
                            scope:this,
                            handler: function(){
                                var specifyNode = [{nodeId:nodeId}];
                                specifyType(specifyNode);
                            }
                        },{
                            iconCls: 'manage-aspects',
                            text: '<?php echo $view['translator']->trans('Manage aspects'); ?>',
                            disabled:(editRights === true ? false : true),
                            scope:this,
                            handler: function(){
                                manageAspects(nodeId,DocName);
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
                                        quickAddAspect(nodeId,"cm:taggable");
                                    }
                                }, {
                                    iconCls:'quick-aspect-version',
                                    text: '<?php echo $view['translator']->trans('Versionable'); ?>',
                                    group: 'quickAspects',
                                    handler: function() {
                                        quickAddAspect(nodeId,"cm:versionable");
                                    }
                                }, {
                                    iconCls:'quick-aspect-category',
                                    text: '<?php echo $view['translator']->trans('Classifiable'); ?>',
                                    group: 'quickAspects',
                                    handler: function() {
                                        quickAddAspect(nodeId,"cm:generalclassifiable");
                                    }
                                }]
                            }
                        },{
                            iconCls: 'download-node',
                            text: '<?php echo $view['translator']->trans('Download ZIP'); ?>',
                            disabled: !zipArchiveExistsGen,
                            scope:this,
                            handler: function() {
                                var files_to_download = [nodeId];
                                files_to_download = JSON.stringify(files_to_download);
                                var dlUrl = '<?php echo $view['router']->generate('ifrescoClientNodeActionsBundle_downloadnodes') ?>?nodes='+files_to_download;
                                window.open(dlUrl);
                            }
                        },'-',
                        {
                            iconCls: 'delete-node',
                            text: '<?php echo $view['translator']->trans('Delete'); ?>',
                            hidden:(record.data.id == 'root' ? true : false),
                            scope:this,
                            disabled: !delRights,
                            handler:function() {
                                var nodeId = record.data.id;
                                var nodeName = record.data.text;
                                var nodeType = "folder";
                                $(".PDFRenderer").hide();
                                Ext.MessageBox.show({
                                    title:'<?php echo $view['translator']->trans('Delete?'); ?>',
                                    msg: '<?php echo $view['translator']->trans('Do you really want to delete:'); ?> <br><b>'+nodeName+'</b>',
                                    fn:function(btn) {
                                        if (btn === "yes") {
                                            $("#overAll").mask("<?php echo $view['translator']->trans('Deleting'); ?> "+nodeName+" ...",300);
                                            $.post("<?php echo $view['router']->generate('ifrescoClientNodeActionsBundle_deletenode') ?>", "nodeId="+nodeId+"&nodeType="+nodeType, function(data) {
                                                var succes = data.success;
                                                $("#overAll").unmask();
                                                if (succes === true) {
                                                    Ext.getCmp("alfrescoTree").getStore().load()
                                                    //Ext.getCmp('alfrescoTree').render();
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
                        },'-',
                        {
                            iconCls: 'add-favorite',
                            text: '<?php echo $view['translator']->trans('Add to favorites'); ?>',
                            scope:this,
                            handler: function(){
                                //store.getAt(index).data.name;
                                var nodeId = record.data.id;
                                var nodeText = record.data.text;
                                var nodeType = "folder";

                                addFavorite(nodeId,nodeText,nodeType);

                            }
                        },'-',{
                            iconCls: 'send-email',
                            text: '<?php echo $view['translator']->trans('Send as Email link'); ?>',
                            scope:this,
                            handler: function(){
                                var mailNodes = [{nodeId:nodeId,nodeName:DocName,docName:DocName,shortType:'file',type:type}];
                                sendMailLink(mailNodes);
                            }
                        }
                        <?php if($view['settings']->getSetting("openInAlfresco") == "true" || $isAdmin) {?>
                        ,'-',{
                            iconCls: 'view-alfresco',
                            text: '<?php echo $view['translator']->trans('Open Folder in Alfresco'); ?>',
                            scope:this,
                            disabled: false,
                            handler: function(){
                                //window.open('<?php echo $ShareUrl; ?>'+nodeId);
                                window.open('<?php echo $ShareFolder; ?>'+folder_path);
                            }}
                        <?php } ?>
                    ]
                });
                e.stopEvent();
                treeMenu.showAt(e.getXY());
            },
            render: function() {
                //this.getRootNode().expand();
            },
            expand: function() {
                if(!this.getRootNode().isExpanded()) {
                    this.getRootNode().expand();
                }
            },

            itemclick: function(node, record, item, index ,event, eOpts){
                if (!tabExists('documentgrid-tab')) {
                    addTabDynamic('documentgrid-tab','<?php echo $view['translator']->trans('Documents'); ?>');

                    jQuery14.manageAjax.add('tree', {
                        isLocal: true,
                        url : "<?php echo $view['router']->generate('ifrescoClientDataGridBundle_homepage') ?>?columnsetid="+UserColumnsetId,
                        success : function (data) {
                            $("#overAll").unmask();
                            $("#documentgrid-tab").html(data);
                            reloadGridData({params:{'nodeId':record.data.id,'columnsetid':UserColumnsetId}});
                        },
                        beforeSend: function(xhr) {
                            $("#overAll").mask('<?php echo $view['translator']->trans('Loading Documents...'); ?>',300);
                        }
                    });

                    /*$.ajax({
                     cache: false,
                     url : "<?php echo $view['router']->generate('ifrescoClientDataGridBundle_homepage') ?>?columnsetid="+UserColumnsetId,

                     success : function (data) {
                     $("#overAll").unmask();
                     $("#documentgrid-tab").html(data);

                     reloadGridData({params:{'nodeId':record.data.id,'columnsetid':UserColumnsetId}});
                     },
                     beforeSend: function(req) {
                     $("#overAll").mask('<?php echo $view['translator']->trans('Loading Documents...'); ?>',300);
                     }
                     });*/
                }
                else {
                    setActive('documentgrid-tab');
                    reloadGridData({params:{'nodeId':record.data.id,'columnsetid':UserColumnsetId}});
                }
            },

            beforeitemdblclick: function(node, record, item, index ,e){
                e.preventDefault();
                return false; //avoid double load
            }
        }
    });
    Ext.getCmp('west-panel').insert(0, tree );

    var uploadTreePanel = Ext.create('Ext.ux.upload.IfsWindow', {
        modal:true,
        layout:'fit',
        width:500,
        height:360,
        closeAction:'hide',
        title: '<?php echo $view['translator']->trans('Upload File(s)'); ?>',
        plain: true,
        constrain: true,
        uploader: {
            filters : [
                {title : "<?php echo $view['translator']->trans('General'); ?>", extensions : "<?php echo implode(',', json_decode($view['settings']->getSetting("uploadAllowedTypes", '[]'))); ?>"}
            ]
        },
        listeners: {
            beforeupload: function(uploader, files) {

            },
            uploadcomplete: function(uploader, files) {

                if(uploadTreePanel.autoCloseControl.getValue() && uploadTreePanel._dropboxFiles.length == 0) {
                    uploadTreePanel.uploadComposed = false;
                    uploadTreePanel.hide();
                }
            }
        },
        buttons: [{
            text: '<?php echo $view['translator']->trans('Close'); ?>',
            handler: function(btn,e) {
                btn.ownerCt.ownerCt.hide();
            }
        }]
    });
});
</script>


<script type="text/javascript">
Ext.require(['*']);
Ext.onReady(function(){

    treeCatStore = Ext.create('Ext.data.TreeStore', {
        proxy: {
            type: 'ajax',
            url: '<?php echo $view['router']->generate('ifrescoClientCategoryTreeBundle_getjson') ?>',
            actionMethods: 'POST',
            simpleSortMode:true
        },
        root: {
            text: '<?php echo $view['translator']->trans('Categories'); ?>',
            draggable:false,
            id:'root',
            disabled:true
        },
        folderSort: true,
        sorters: [{
            property: 'text',
            direction: 'ASC'
        }]
    });

    Ext.override(Ext.data.AbstractStore,{
        indexOf: Ext.emptyFn
    });

    var treeCat = Ext.create('Ext.tree.Panel', {
        title: '<?php echo $view['translator']->trans('Categories'); ?>',
        border: false,
        iconCls: 'categoriesTree',
        alias : 'widget.pgTree',
        store: treeCatStore,
        rootVisible:true,
        autoScroll:true,
        animate:true,
        ddConfig:false,
        containerScroll: true,
        split: true,
        hideHeaders: true,
        plugins: [
            Ext.create('Ext.grid.plugin.CellEditing', {
                pluginId: 'treeEditPlugin',
                listeners: {
                    edit: function(editor, e) {
                        e.record.commit();
                        if(e.record.data.id == 'newNode') {
                            if(e.record.parentNode.raw)
                                var parentNode = e.record.parentNode.raw.nodeId;
                            else
                                var parentNode = e.record.parentNode.data.id

                            addTreeNode(parentNode, e.value)
                        }
                        else {
                            editTreeNode(e.record.raw.nodeId, e.value, e.originalValue)
                        }
                    }
                }
            })
        ],
        columns:[
            {
                xtype:'treecolumn',
                dataIndex:'text',
                flex:1,
                editor:
                {
                    xtype:'textfield',
                    allowBlank:false
                }
            }],
        tbar:[
            {
                xtype: 'checkbox',
                name: 'browsesub',
                checked: (BrowseSubCategories == true ? true : false),
                boxLabel: '<?php echo $view['translator']->trans('Browse items in sub-categories?'); ?>',
                listeners: {
                    change: function(component,checked, oldchecked, eOpts) {
                        Registry.getInstance().set("BrowseSubCategories",checked);

                        Registry.getInstance().save();
                        BrowseSubCategories = checked;
                    }
                }
            },
            '->',{
                iconCls:'refresh-icon',
                tooltip: '<?php echo $view['translator']->trans('Reload'); ?>',
                handler: function(){
                    if(!treeCat.getStore().isLoading()) {
                        treeCat.getStore().proxy.url = '<?php echo $view['router']->generate('ifrescoClientCategoryTreeBundle_getjson') ?>?reload=true';
                        treeCat.getStore().load();
                        //treeCat.render();
                        treeCat.getStore().proxy.url = '<?php echo $view['router']->generate('ifrescoClientCategoryTreeBundle_getjson') ?>';
                    }
                },
                scope: this
            }, '-',
            {
                iconCls: 'icon-expand-all',
                tooltip: '<?php echo $view['translator']->trans('Expand All'); ?>',
                handler: function(){ treeCat.getRootNode().expand(true); },
                scope: this
            },{
                iconCls: 'icon-collapse-all',
                tooltip: '<?php echo $view['translator']->trans('Collapse All'); ?>',
                handler: function(){ treeCat.getRootNode().collapse(true); },
                scope: this
            }],


        listeners: {
            itemcontextmenu: function(node, record, item, index ,e, eOpts) {
                var existingMenu = Ext.getCmp('catTree-ctx');
                if (existingMenu != null) {
                    existingMenu.destroy();
                }

                if (record.data.id == "root") {

                    var catMenu = Ext.create('Ext.menu.Menu', {
                        id:'catTree-ctx',
                        items:[
                            <?php if ($isAdmin == true) { ?>
                            {
                                iconCls: 'add-category',
                                text: '<?php echo $view['translator']->trans('Add a subcategory'); ?>',
                                scope:this,
                                handler: function(){
                                    catMenu.hide();
                                    /*if(record.data.leaf == false) {
                                     record.expand(false,finishExpandAdd);
                                     //treeCat.doLayout();
                                     //treeCat.render();
                                     return;
                                     }*/
                                    finishExpandAdd(record);
                                }
                            }
                            <?php } ?>
                        ]
                    });
                }
                else {
                    var catMenu = Ext.create('Ext.menu.Menu', {
                        id:'catTree-ctx',
                        items:[
                            {
                                iconCls: 'add-tab',
                                text: '<?php echo $view['translator']->trans('Open in new tab'); ?>',
                                scope:this,
                                handler: function(){
                                    var nodePath = record.data.id;
                                    var nodeId = record.raw.nodeId;
                                    var nodeText = record.data.text;
                                    openCategoryAdvanced(nodeId,nodePath,nodeText,BrowseSubCategories,"true");

                                }
                            },'-',
                            {
                                iconCls: 'add-favorite',
                                text: '<?php echo $view['translator']->trans('Add to favorites'); ?>',
                                scope:this,
                                handler: function(){
                                    //var nodeId = node.id;
                                    var nodeId = record.raw.nodeId;
                                    var nodeText = record.data.text;
                                    var nodeType = "category";

                                    addFavorite(nodeId,nodeText,nodeType);

                                }
                            }
                            <?php if ($isAdmin == true) { ?>
                            ,'-',{
                                iconCls: 'add-category',
                                text: '<?php echo $view['translator']->trans('Add a subcategory'); ?>',
                                scope:this,
                                handler: function(){
                                    catMenu.hide();
                                    if(record.data.leaf == false) {
                                        record.expand(false,finishExpandAdd);
                                        //treeCat.doLayout();
                                        //treeCat.render();
                                        return;
                                    }
                                    finishExpandAdd(record);

                                }
                            },{
                                iconCls: 'edit-category',
                                text: '<?php echo $view['translator']->trans('Rename this category'); ?>',
                                scope:this,
                                //hidden:(node.id == "root" ? true : false),
                                handler: function(){
                                    onTreeEditing(record);
                                }
                            },{
                                iconCls: 'delete-category',
                                text: '<?php echo $view['translator']->trans('Delete this category'); ?>',
                                scope:this,
                                handler: function(){
                                    var nodeId = record.raw.nodeId;
                                    var nodeText = record.raw.text;
                                    $.ajax({
                                        cache: false,
                                        url : "<?php echo $view['router']->generate('ifrescoClientCategoryTreeBundle_removecategory') ?>",
                                        data: {nodeId: nodeId},
                                        dataType:"json",

                                        success : function (data) {
                                            $("#categoriesTree").unmask();

                                            if (data.success == true) {
                                                record.remove();
                                            }
                                            else {
                                                return false;
                                            }
                                        },
                                        error: function() {
                                            return false;
                                            $("#categoriesTree").unmask();
                                        },
                                        beforeSend: function(req) {
                                            $("#categoriesTree").mask("Delete <b>"+nodeText+"</b>...",300);
                                        }
                                    });
                                }
                            }
                            <?php } ?>
                        ]
                    });
                }

                e.stopEvent();
                catMenu.showAt(e.getXY());
            },
            render: function() {
                //this.getRootNode().expand();
            },
            expand: function() {
                if(!this.getRootNode().isExpanded()) {
                    this.getRootNode().expand();
                }
            },
            itemclick: function(node, record, item, index ,event, eOpts){

                if (record.data.id == "root" || record.raw == undefined)
                    return false;
                if (!tabExists('documentgrid-tab')) {
                    addTabDynamic('documentgrid-tab','<?php echo $view['translator']->trans('Documents'); ?>');

                    var nodeId = record.raw.nodeId;
                    $.ajax({
                        cache: false,
                        url : "<?php echo $view['router']->generate('ifrescoClientDataGridBundle_homepage') ?>?columnsetid="+UserColumnsetId,

                        success : function (data) {
                            $("#overAll").unmask();
                            $("#documentgrid-tab").html(data);
                            reloadGridData({params:{'fromTree':"true",'subCategories':BrowseSubCategories,'categoryNodeId':record.data.nodeId,'categories':record.data.id,'columnsetid':UserColumnsetId}});
                        },
                        beforeSend: function(req) {
                            $("#overAll").mask('<?php echo $view['translator']->trans('Loading Documents...'); ?>',300);
                        }
                    });
                }
                else {
                    setActive('documentgrid-tab');
                    reloadGridData({params:{'fromTree':"true",'categoryNodeId':record.raw.nodeId,'subCategories':BrowseSubCategories,'categories':record.data.id,'columnsetid':UserColumnsetId}});
                }
            },
            beforeitemdblclick: function(node, record, item, index ,e){
                e.preventDefault();
                return false; //avoid double load
            }
        }
    });

    Ext.getCmp('west-panel').insert(1, treeCat );
});



</script>

<script type="text/javascript">
    Ext.onReady(function(){
        //var TreeFav = Ext.tree;

        treeFavStore = Ext.create('Ext.data.TreeStore', {
            proxy: {
                type: 'ajax',
                url: '<?php echo $view['router']->generate('ifrescoClientUserSpecificBundle_getfavorites') ?>',
                actionMethods: 'POST',
                simpleSortMode:true
            },
            root: {
                text: '<?php echo $view['translator']->trans('Favorites'); ?>',
                draggable:false,
                id:'root',
                expanded: false,
                disabled:true
            },
            folderSort: true,
            autoLoad: false,
            sorters: [{
                property: 'text',
                direction: 'ASC'
            }]
        });

        var treeFav = Ext.create('Ext.tree.Panel', {
            title: '<?php echo $view['translator']->trans('Favorites'); ?>',
            border: false,
            iconCls: 'favTree',
            id:'favTree',
            store: treeFavStore,
            rootVisible:false,
            autoScroll:true,
            animate:true,
            ddConfig:false,
            containerScroll: true,
            split: true,

            tbar:[
                '->',{
                    iconCls:'refresh-icon',
                    tooltip: '<?php echo $view['translator']->trans('Reload'); ?>',
                    handler: function(){
                        if(!treeFav.getStore().isLoading()) {
                            treeFav.getStore().load();
                            //treeFav.render();
                        }
                    },
                    scope: this
                }
            ],
            listeners: {
                itemcontextmenu: function(node, record, item, index ,e, eOpts) {

                    var existingMenu = Ext.getCmp('favTree-ctx');
                    if (existingMenu != null) {
                        existingMenu.destroy();
                    }

                    var favMenu = Ext.create('Ext.menu.Menu', {
                        id:'favTree-ctx',
                        items:[
                            {
                                iconCls: 'remove-favorite',
                                text: '<?php echo $view['translator']->trans('Remove of favorites'); ?>',
                                scope:this,
                                handler: function(){
                                    var nodeId = record.raw.id;
                                    var favId = record.raw.favId;
                                    if (nodeId.length > 0) {
                                        if (typeof favId != 'undefined')
                                            removeFavoriteById(favId);
                                        else
                                            removeFavoriteByNodeId(nodeId);
                                    }
                                }
                            }
                        ]
                    });
                    e.stopEvent();

                    favMenu.showAt(e.getXY());
                },
                render: function() {
                    //this.getRootNode().expand();
                },
                expand: function() {
                    if(!this.getRootNode().isExpanded()) {
                        this.getRootNode().expand();
                    }
                },
                itemclick: function(node, record, item, index ,event, eOpts){
                    var nodeType = record.raw.type;
                    var nodeImgText = record.raw.imageName;

                    //var nodeId = node.id;
                    if (nodeType !== "category")
                        var nodeId = record.raw.id;
                    else
                        var nodeId = record.raw.nodeId;
                    var favId = record.raw.favId;
                    var workId = record.raw.workId;
                    if (typeof record.raw.nodeId != 'undefined')
                        var tabnodeid = record.raw.nodeId.replace(/-/g,"");
                    else
                        var tabnodeid = nodeId.replace(/-/g,"");

                    if (typeof workId != 'undefined' && nodeType !== 'category')
                        var nodeId = workId;

                    alfDocument(nodeId,nodeType,nodeImgText,tabnodeid,'favorite_'+favId);

                },
                beforeitemdblclick: function(node, record, item, index ,e){
                    e.preventDefault();
                    return false; //avoid double load
                }
            }
        });

        Ext.getCmp('west-panel').insert(2, treeFav );
        Ext.getCmp('west-panel').items.items[<?echo $DefaultNav?>].expand();
        if (Ext.getCmp('west-panel').items.items[<?echo $DefaultNav?>].iconCls == "tagScope")
        {
            getTagScope()
        }
        else {
            Ext.getCmp('west-panel').items.items[<?echo $DefaultNav?>].getRootNode().expand();
        }

        // render the tree

    });
</script>




<div id="tagScope">
    <script type="text/javascript">
        $(document).ready(function() {
            //getTagScope();

        });

        function getTagScope() {
            if (!tagLoading) {
                tagLoading = true;
                $.getJSON("<?php echo $view['router']->generate('ifrescoClientTagsBundle_gettagscope') ?>", function(data) {
                    $("#tagCloud").html('');
                    $("<ul>").attr("id", "tagList").appendTo("#tagCloud");
                    //var maxCount = data.maxCount;
                    var maxCount = data.countMax;
                    //var minCount = data.minCount;
                    var minCount = data.countMin;
                    if (maxCount == minCount)
                        maxCount++;
                    var maxSize = 20;
                    var minSize = 12;

                    var spread = maxCount - minCount;
                    var step = (maxSize - minSize) / (spread);

                    //create tags
                    $.each(data.tags, function(i, val) {
                        var size = Math.round(minSize + ((val.count - minCount) * step));

                        //create item
                        var li = $("<li>");


                        //create link
                        $("<a>").text(val.name + " ("+val.count+")").attr({title:"<?php echo $view['translator']->trans('See all documents tagged with'); ?> " + val.name, href:'#', id:val.name}).click(function() {
                            $(".active").removeClass('active');
                            //reloadGridData({params:{'tag':val.name}});
                            //updateTab('dashboard-tab','TEST', '<?php echo $view['router']->generate('ifrescoClientDataGridBundle_homepage') ?>');

                            if (!tabExists('documentgrid-tab')) {
                                addTabDynamic('documentgrid-tab','<?php echo $view['translator']->trans('Documents'); ?>');

                                $.ajax({
                                    cache: false,
                                    url : "<?php echo $view['router']->generate('ifrescoClientDataGridBundle_homepage') ?>?columnsetid="+UserColumnsetId,

                                    success : function (data) {
                                        $("#overAll").unmask();
                                        $("#documentgrid-tab").html(data);
                                        //grid.store.load({params:{'nodeId':node.id}});
                                        reloadGridData({params:{'tag':val.name,'columnsetid':UserColumnsetId}});
                                    },
                                    beforeSend: function(req) {
                                        $("#overAll").mask('<?php echo $view['translator']->trans('Loading Documents...'); ?>',300);
                                    }

                                });
                            }
                            else {
                                setActive('documentgrid-tab');
                                reloadGridData({params:{'tag':val.name,'columnsetid':UserColumnsetId}});
                            }

                            $(this).addClass("active");
                        }).appendTo(li);

                        //li.children().css("fontSize", (val.count / 10 < 1) ? val.count / 10 + 1 + "em": (val.count / 10 > 2) ? "2em" : val.count / 10 + "em");
                        li.children().css("fontSize", size+"px");
                        //add to list
                        li.appendTo("#tagList");
                    });
                    tagLoading = false;
                });
            }
        }
    </script>
    <div id="tagCloud" style=""></div>
</div>


</div>


<div id="contentTabs">
    <div id="dashboardTab" class="x-hide-display">
        <div id="ContentBox">
            <?php //echo $sf_data->getRaw('sf_content') ?>
            <?php $view['slots']->output('body') ?>
        </div>


    </div>
</div>
</div>

<form id="history-form" class="x-hidden">
    <input type="hidden" id="x-history-field" />
    <iframe id="x-history-frame"></iframe>
</form>

<div id="d_clip_button" style=""></div>

<div id="specifytype-window" class="x-hidden x-body-masked" style="z-index:100;"></div>
<div id="metadata-window" class="x-hidden x-body-masked" style="z-index:100;">
    <div id="metadata-window-panel">

    </div>
</div>

</body>


</html>
