<script>
Ext.onReady(function(){

    uploadPanel = Ext.create('Ext.ux.upload.IfsWindow', {
        modal:true,
        layout:'fit',
        width:500,
        height:360,
        closeAction:'hide',
        title: '<?php echo $view['translator']->trans('Upload File(s)'); ?>',
        plain: true,
        constrain: true,
        uploader: {
            url: '<?php echo $view['router']->generate('ifresco_client_upload_rest'); ?>?nodeId='+currentNodeId+'&overwrite=false&ocr=false',
            uploadpath: '<?php echo $view['router']->generate('ifresco_client_upload_rest'); ?>?nodeId='+currentNodeId+'&overwrite=false&ocr=false',
            filters : [
                {title : "<?php echo $view['translator']->trans('General'); ?>", extensions : "<?php echo $view['settings']->getSetting("uploadAllowedTypes") ? implode(',', json_decode($view['settings']->getSetting("uploadAllowedTypes"))) : ''; ?>"}
            ]
        },
        listeners: {
            beforestart: function(uploader, files) {
                uploader.uploader.settings.uploadpath = uploader.uploader.settings.url = '<?php echo $view['router']->generate('ifresco_client_upload_rest'); ?>?nodeId='+currentNodeId+'&overwrite=false&ocr=false';
            },
            uploadcomplete: function(uploader, files) {
                uploadPanel.uploadComposed = false;
                if(uploadPanel.autoCloseControl.getValue() && uploadPanel._dropboxFiles.length == 0) {
                    uploadPanel.hide();
                }

                if(uploadPanel._dropboxFiles.length == 0)
                    refreshGrid();
            }
        },
        buttons: [{
            text: '<?php echo $view['translator']->trans('Close'); ?>',
            handler: function(btn,e) {
                btn.ownerCt.ownerCt.hide();
            }
        }]
    });

    function loadFolderPreview(nodeId) {
        Ext.define('FolderGridStoreModel', {
            extend: 'Ext.data.Model',
            fields: [<?php echo html_entity_decode($fields,ENT_QUOTES); ?>]
        });

        var folderStore = Ext.create('Ext.data.Store', {
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
                    store.loadMask = new Ext.LoadMask(foldergrid, {msg:"<?php echo $view['translator']->trans('Loading Documents...'); ?>"});
                    store.loadMask.show();
                },
                load: function(store, records, successful, eOpts) {
                    store.loadMask.hide();
                },
                datachanged:function(store,e) {

                    var json = store.getProxy().reader.jsonData;
                    var perms = json.perms;

                    if(perms) {
                        var uploadBtn       = Ext.getCmp("upload-content-folder");
                        var createFolderBtn = Ext.getCmp("create-folder-folder");

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

        var foldergrid = Ext.create('Ext.grid.Panel', {
            loadMask: {msg:'<?php echo $view['translator']->trans('Loading Documents...'); ?>'},
            layout:'fit',
            store: folderStore,
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
                    id:'upload-content-folder',
                    tooltip:'<?php echo $view['translator']->trans('Upload File(s)'); ?>',
                    hidden: true,
                    handler: function(){
                        if(!win){
                            win = Ext.create('Ext.window.Window', {
                                modal:true,
                                id:'upload-window',
                                layout:'fit',
                                width:500,
                                height:360,
                                closeAction:'hide',
                                plain: true,
                                constrain: true,
                                items: Ext.create('Ext.panel.Panel', {
                                    id: 'upload-window-panel',
                                    layout:'fit',
                                    border:false
                                }),

                                buttons: [{
                                    text: '<?php echo $view['translator']->trans('Close'); ?>',
                                    handler: function() {
                                        win.hide(this);
                                        foldergrid.getStore().load({params:{'nodeId':nodeId,'columnsetid':currentColumnsetid}});
                                    }
                                }]
                            });
                        }

                        $.ajax({
                            cache: false,
                            url : "<?php echo $view['router']->generate('ifresco_client_upload') ?>",
                            data: ({'nodeId' : nodeId, 'containerName':''}),

                            success : function (data) {
                                $("#upload-window-panel").html(data);
                            }
                        });

                        win.show();
                    },
                    scope: this
                },
                {
                    iconCls:'create-folder',
                    id:'create-folder-folder',
                    tooltip:'<?php echo $view['translator']->trans('Create Space'); ?>',
                    hidden: true,
                    handler: function(){
                        if(!winSpace){
                            winSpace = Ext.create('Ext.window.Window', {
                                modal:true,
                                id:'general-window',
                                layout:'fit',
                                width:500,
                                height:350,
                                closeAction:'hide',
                                constrain: true,
                                title:'<?php echo $view['translator']->trans('Create Space'); ?>',
                                plain: true,
                                resizable: false,

                                items: Ext.create('Ext.Panel', {
                                    id: 'window-panel',
                                    layout:'fit',
                                    border:false
                                }),
                                listeners:{
                                    'beforeshow':function() {
                                        $("#general-window").mask("<?php echo $view['translator']->trans('Loading...'); ?>",300);
                                        $.ajax({
                                            cache: false,
                                            url : "<?php echo $view['router']->generate('ifresco_client_folder_actions') ?>",

                                            success : function (data) {
                                                $("#general-window").unmask();
                                                $("#window-panel").html(data);
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

                                                foldergrid.getStore().load({params:{'nodeId':nodeId,'columnsetid':currentColumnsetid}});

                                                winSpace.hide();
                                                $("#window-panel").html('');

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
                                            winSpace.hide();
                                            $("#window-panel").html('');
                                        }
                                    }]
                            });
                        }
                        winSpace.show();
                    }
                },//'-',
                {
                    iconCls:'open-alfresco',
                    id: 'open-alfresco-folder',
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
                        loadFolderPreview(PanelNodeId);
                    },
                    scope: this
                }],

            viewConfig: {
                forceFit:true
            },

            collapsible: true,
            animCollapse: true,
            header: false,
            id: 'folderDataGrid',
            iconCls: 'icon-grid',

            stateId :'folderDocumentGrid-stateid',
            stateful : true,
            renderTo:'previewWindow',
            height:400
        });

        foldergrid.store.load({params:{'nodeId':nodeId,'columnsetid':currentColumnsetid}});
        //foldergrid.render();

        foldergrid.on('select', function(model, view, rowIndex, e) {
            var folderNodeText = folderStore.getAt(rowIndex).data.alfresco_name;

            var folderNodeId = folderStore.getAt(rowIndex).data.nodeId;
            var folderType = folderStore.getAt(rowIndex).data.alfresco_type;

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
                    url : "<?php echo $view['router']->generate('ifresco_client_data_grid_index') ?>?containerName="+tabnodeid+"&addContainer=<?php echo $nextContainer; ?>&columnsetid="+currentColumnsetid,

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

    function loadPreview(nodeId) {


        var previewHeight = $("#previewWindow").height();


        jQuery14.manageAjax.add('preview', {
            cache: false,
            url : "<?php echo $view['router']->generate('ifresco_client_view_index') ?>",
            data: "nodeId="+nodeId+"&height="+previewHeight+"px",
            success : function (data) {
                $("#previewContent").unmask();

                $("#previewWindow").html(data);

                $(".PDFRenderer").show();
            },
            beforeSend: function(xhr) {
                if ($(".PDFRenderer").is(':visible')) {
                    $(".PDFRenderer").hide();

                }
                $("#previewWindow").html('');
                $("#previewContent").mask("<?php echo $view['translator']->trans('Loading...'); ?>",300);
            }
        });
    }

    function contextMenuFunc<?php echo $containerName; ?>(gridx, record, item, index ,e, eOpts) {

        if (selection.length > 1) {
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
                            //var dlUrl = '<?php echo $view['router']->generate('ifrescoClientNodeActionsBundle_downloadnodes') ?>?nodes='+files_to_download;

                            //window.open(dlUrl);

                            var win = window.open('');
                            win.document.write("<head></head><body>");
                            win.document.write("<form action='<?php echo $view['router']->generate('ifrescoClientNodeActionsBundle_downloadnodes') ?>' method='POST'>");
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
                                var dlUrl = '<?php echo $view['router']->generate('ifrescoClientNodeActionsBundle_pdfmerge') ?>?nodes='+jsonNodes;
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
        } else {
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
                                url: '<?php echo $view['router']->generate('ifrescoClientDataGridBundle_detailview') ?>',
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
                            var dlUrl = '<?php echo $view['router']->generate('ifrescoClientNodeActionsBundle_download') ?>?nodeId='+nodeId;
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
                            var dlUrl = '<?php echo $view['router']->generate('ifrescoClientNodeActionsBundle_downloadnodes') ?>?nodes='+files_to_download;
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
                                    url : "<?php echo $view['router']->generate('ifrescoClientNodeActionsBundle_sharedoc') ?>?nodeId="+nodeId,

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
                                            url : "<?php echo $view['router']->generate('ifrescoClientNodeActionsBundle_unsharedoc') ?>?nodeId="+nodeId,

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
    }
});
</script>