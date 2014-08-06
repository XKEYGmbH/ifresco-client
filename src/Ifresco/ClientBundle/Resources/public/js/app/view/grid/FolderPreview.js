Ext.define('Ifresco.view.grid.FolderPreview', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.ifrescoViewGridFolderPreview',
    layout:'fit',
    border: 0,
    columns: [],

    viewConfig: {
        forceFit: true
    },

    collapsible: true,
    animCollapse: true,
    header: false,
    // id: 'folderDataGrid<?php echo $containerName; ?>',
    iconCls: 'icon-grid',

    // stateId: 'folderDocumentGrid-stateid<?php echo $containerName; ?>',
    stateful: true,
    // renderTo: 'previewWindow<?php echo $containerName; ?>',
    height: 400,

    initComponent: function () {
        console.log('init');
        var store = Ifresco.store.Grid.create({configData: this.configData});
        store.getProxy().setExtraParam('nodeId', this.nodeId);
        store.load();

        this.columns = this.configData.columns;

        Ext.apply(this, {
            tbar: this.createToolbar(),
            store: store,
            listeners: {
                beforeitemcontextmenu: function (grid, record, item, index, e, eOpts) {
                    //this.openContextMenu(grid, record, item, index, e, eOpts);
                	this.menu = this.fireEvent('openContextMenu', this, this.configData, grid, record, item, index, e, eOpts);
                },
                itemdblclick: function (grid, record, item, index, e, eOpts) {
                    //this.loadDataByDoubleClick(grid, record, item, index, e, eOpts );
                	this.fireEvent('loadDataByDoubleClick', this, this.configData, grid, record, item, index, e, eOpts );
                },
            }
        });

        this.callParent();
    },

    loadDataByDoubleClick: function (grid, record, item, index, e, eOpts ) {
        this.isDblClick = true;

        var nodeId = record.data.nodeId;
        var type = record.data.alfresco_type;

        var nodeText = record.data.alfresco_name;

        if (type !== "{http://www.alfresco.org/model/content/1.0}folder") {
            var url = record.data.alfresco_url;
            console.log(record);
            Ifresco.app.getController("Index").openWindow(url);
        }
        else {
            var contentTab = this.up('panel[cls~=ifresco-view-content-tab]');
            if (e.shiftKey == true) {
                var tabnodeid = nodeId.replace(/-/g,"");
                Ext.Ajax.request({
                    loadMask: true,
                    disableCaching: true,
                    url: Routing.generate('ifresco_client_data_grid_index'),
                    params: {
                        columnsetid: contentTab.localConfigData.currentColumnSetId,
                        containerName: tabnodeid,
                        addContainer: contentTab.configData.nextContainer

                    },
                    success: function(response){
                        var newContentTab = Ifresco.view.ContentTab.create({
                            title: nodeText,
                            configData: Ext.decode(response.responseText)
                        });
                        contentTab.up('panel[cls~=ifresco-view-center]').add(newContentTab).show();
                        newContentTab.reloadGridData({params: {
                            nodeId: nodeId,
                            columnSetId: Ifresco.helper.Registry.get('ColumnsetId')
                        }});
                    }
                });
            }
            else {
                contentTab.reloadGridData({params: {
                    nodeId: nodeId,
                    columnSetId: Ifresco.helper.Registry.get('ColumnsetId')
                }});
            }
        }

        isDblClick = false;
    },
    
    createToolbar: function () {
    	return [{
            iconCls:'ifresco-icon-refresh',
            tooltip: Ifresco.helper.Translations.trans('Refresh'),
            handler: function(){
            	this.getStore().reload();
            },
            scope: this
        }];
        /*return [{
            iconCls:'upload-content',
            tooltip:'Upload File(s)',
            hidden: true,
            handler: function(){
                var win;
                if(!win) {
                    win = Ext.create('Ext.window.Window', {
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
                            text: 'Close',
                            handler: function() {
                                // win<?php echo $containerName; ?>.hide(this);
                                // foldergrid<?php echo $containerName; ?>.getStore().load({params:{'nodeId':nodeId,'columnsetid':currentColumnsetid<?php echo $containerName; ?>}});
                            }
                        }]
                    });
                }

                // $.ajax({
                //     cache: false,
                //     url : "<?php echo $view['router']->generate('ifrescoClientUploadBundle_upload') ?>",
                //     data: ({'nodeId' : nodeId, 'containerName':'<?php echo $containerName; ?>'}),

                //     success : function (data) {
                //         $("#upload-window-panel<?php echo $containerName; ?>").html(data);
                //     }
                // });

                // win<?php echo $containerName; ?>.show();
            },
            scope: this
        },{
            iconCls:'create-folder',
            id:'create-folder-folder<?php echo $containerName; ?>',
            tooltip:'Create Space',
            hidden: true,
            handler: function(){
                // if(!winSpace<?php echo $containerName; ?>){
                //     winSpace<?php echo $containerName; ?> = Ext.create('Ext.window.Window', {
                //         modal:true,
                //         id:'general-window<?php echo $containerName; ?>',
                //         layout:'fit',
                //         width:500,
                //         height:350,
                //         closeAction:'hide',
                //         constrain: true,
                //         title:'<?php echo $view['translator']->trans('Create Space'); ?>',
                //         plain: true,
                //         resizable: false,

                //         items: Ext.create('Ext.Panel', {
                //             id: 'window-panel<?php echo $containerName; ?>',
                //             layout:'fit',
                //             border:false
                //         }),
                //         listeners:{
                //             'beforeshow':function() {
                //                 $("#general-window<?php echo $containerName; ?>").mask("<?php echo $view['translator']->trans('Loading...'); ?>",300);
                //                 $.ajax({
                //                     cache: false,
                //                     url : "<?php echo $view['router']->generate('ifrescoClientFolderActionsBundle_homepage') ?>",

                //                     success : function (data) {
                //                         $("#general-window<?php echo $containerName; ?>").unmask();
                //                         $("#window-panel<?php echo $containerName; ?>").html(data);
                //                         $("#spaceCreateForm #cm_name").focus();
                //                     }
                //                 });
                //             }
                //         },

                //         buttons: [{
                //             text: '<?php echo $view['translator']->trans('Save'); ?>',
                //             handler: function() {


                //                 $.post("<?php echo $view['router']->generate('ifrescoClientFolderActionsBundle_createspacepost') ?>", $("#spaceCreateForm").serialize()+"&nodeId="+nodeId, function(data) {
                //                     if (data.success === "true") {
                //                         $(".PDFRenderer").hide();
                //                         Ext.MessageBox.show({
                //                             title: '<?php echo $view['translator']->trans('Success'); ?>',
                //                             msg: data.message,
                //                             buttons: Ext.MessageBox.OK,
                //                             icon: Ext.MessageBox.INFO,
                //                             fn:showRenderer
                //                         });

                //                         Ext.getCmp("alfrescoTree").getStore().load()
                //                         //Ext.getCmp('alfrescoTree').render();

                //                         foldergrid<?php echo $containerName; ?>.getStore().load({params:{'nodeId':nodeId,'columnsetid':currentColumnsetid<?php echo $containerName; ?>}});

                //                         winSpace<?php echo $containerName; ?>.hide();
                //                         $("#window-panel<?php echo $containerName; ?>").html('');

                //                     }
                //                     else {
                //                         $(".PDFRenderer").hide();
                //                         Ext.MessageBox.show({
                //                             title: '<?php echo $view['translator']->trans('Error'); ?>',
                //                             msg: data.message,
                //                             buttons: Ext.MessageBox.OK,
                //                             icon: Ext.MessageBox.WARNING,
                //                             fn:showRenderer
                //                         });
                //                     }
                //                 }, "json");
                //             }
                //         },
                //             {
                //                 text: '<?php echo $view['translator']->trans('Close'); ?>',
                //                 handler: function() {
                //                     winSpace<?php echo $containerName; ?>.hide();
                //                     $("#window-panel<?php echo $containerName; ?>").html('');
                //                 }
                //             }]
                //     });
                // }
                // winSpace<?php echo $containerName; ?>.show();
            }
        },{
            iconCls: 'open-alfresco',
            // id: 'open-alfresco-folder<?php echo $containerName; ?>',
            // tooltip:'<?php echo $view['translator']->trans('Open Folder in Alfresco'); ?>',
            hidden: true,
            handler: function(){
                // window.open("<?php echo $ShareSpaceUrl; ?>"+nodeId);
            },
            scope: this
        },{
            iconCls:'refresh-meta',
            // tooltip:'<?php echo $view['translator']->trans('Refresh'); ?>',
            cls: 'x-btn-icon',
            handler: function(){
                // loadFolderPreview<?php echo $containerName; ?>(PanelNodeId<?php echo $containerName; ?>);
            },
            scope: this
        }];*/
    }
});
