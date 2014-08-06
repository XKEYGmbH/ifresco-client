Ext.define('Ifresco.controller.Grid', {
    extend: 'Ext.app.Controller',
    requires: ['Ifresco.view.ContentTab'],
    refs: [{
        selector: 'viewport > ifrescoCenter',
        ref: 'tabPanel'
    },{
        selector: 'ifrescoCenter > ifrescoContentTab[ifrescoId=documents]',
        ref: 'documentsTab'
    },{
        selector: 'ifrescoViewGridGrid > toolbar[cls~=ifresco-view-grid-toolbar-buttons]',
        ref: 'gridToolbar'
    }],
    init: function() {
        this.control({
            'ifrescoCenter': {
//                openDocumentDetail: this.openDocumentDetail
                // treefoldercontextmenu: this.treeFolderContextMenu
            },
            'ifrescoViewGridGrid': {
                afterrender: this.setupGridStoreListeners,
                loadDataBySelect: this.loadGridDataBySelect,
                csvExport: this.csvExport,
            	pdfExport: this.pdfExport,
            	pdfMerge: this.pdfMerge,
            	openContextMenu: this.openContextMenu,
            	loadDataByDoubleClick: this.loadDataByDoubleClick,
            	switchToThumbnailView: this.switchToThumbnailView,
            	switchToGridView: this.switchToGridView
            },
            'ifrescoViewGridFolderPreview': {
            	openContextMenu: this.openContextMenu,
            	loadDataByDoubleClick: this.loadDataByDoubleClick
            }
        });
    },
    
    switchToThumbnailView: function(me) {
    	var contentTab = me.up("ifrescoContentTab");
    	contentTab.down("ifrescoViewPanelGrid").hide();
    	contentTab.down("ifrescoViewPanelThumbnails").show();
    },
    
    switchToGridView: function(me) {
    	var contentTab = me.up("ifrescoContentTab");
    	contentTab.down("ifrescoViewPanelGrid").show();
    	contentTab.down("ifrescoViewPanelThumbnails").hide();
    },
    
    loadDataByDoubleClick: function (me, configData, grid, record, item, index, e, eOpts ) {
    	me.isDblClick = true;

        var nodeId = record.data.nodeId;
        var type = record.data.alfresco_type;

        var nodeText = record.data.alfresco_name;

        if (type !== "{http://www.alfresco.org/model/content/1.0}folder") {
            var url = record.data.alfresco_url;
            console.log(record);
            Ifresco.app.getController("Index").openWindow(url);
        }
        else {
            var contentTab = me.up('panel[cls~=ifresco-view-content-tab]');
            if (e.shiftKey == true) {
                var tabnodeid = nodeId.replace(/-/g,"");
                Ext.Ajax.request({
                    loadMask: true,
                    disableCaching: true,
                    url: Routing.generate('ifresco_client_data_grid_index'),
                    params: {
                        columnSetId: contentTab.localConfigData.currentColumnSetId,
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

        me.isDblClick = false;
    },
    
    openContextMenu: function (me, configData, grid, record, item, index, e, eOpts) {
    	var menu = null;       
        console.log('menu');

        
        /*if (grid.menu !== null && typeof grid.menu !== 'undefined') {
            grid.menu.destroy();
        }*/

        var selection = grid.getSelectionModel().getSelection();
        var allFiles = true;
        var allFolders = true;
        var allPDF = true;
        var allImages = true;
        var allOCRable = true;
        console.log(selection.length,selection)
        if (selection.length > 1) {
            var selectedObjects = [];
            var allDisAllowedDelete = true;
            var allDisAllowedEdit = true;

            for (var i = 0; i < selection.length; i++) {
                var selected = selection[i];
                console.log("GET SELECTED",selected)
                var mime = selected.raw.alfresco_mimetype;
                var type = selected.raw.alfresco_type;
                if(selected.data.id != selected.raw.nodeRef) {
                    var nodeType = "filelink";
                } else
                if (type !== "{http://www.alfresco.org/model/content/1.0}folder")
                    var nodeType = "file";
                else
                    var nodeType = "folder";

                
                selectedObjects.push({
                    nodeId:selected.data.id,
                    nodeRef:selected.raw.nodeRef,
                    nodeName:selected.raw.alfresco_name,
                    editRights: selected.raw.alfresco_perm_edit,
                    delRights: selected.raw.alfresco_perm_delete,
                    cancelCheckoutRights: selected.raw.alfresco_perm_cancel_checkout,
                    createRights: selected.raw.alfresco_perm_create,
                    hasRights: selected.raw.alfresco_perm_permissions,
                    folder_path: selected.raw.alfresco_node_path,
                    type:type,
                    shortType:nodeType,
                    DocName:selected.raw.alfresco_name,
                    parent: me,
                    record: selected,
                    mime: mime,
                    isClipboard: configData.isClipBoard
                });

                if (nodeType === "file" || nodeType === "filelink") {
                    allFolders = false;
                }

                if(selected.raw.alfresco_perm_delete == true) {
                    allDisAllowedDelete = false;
                }

                if(selected.raw.alfresco_perm_edit == true) {
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
            console.log("selected object",selectedObjects)
            data = {
            	isMultiple: true,
            	allFolders: allFolders,
            	allImages: allImages,
                allOCRable: allOCRable,
                allPDF: allPDF,
                allDisAllowedEdit: allDisAllowedEdit,
                allDisAllowedDelete: allDisAllowedDelete,
                editRights: !allDisAllowedEdit,
                delRights: !allDisAllowedDelete,
                records: selectedObjects,
                fromComponent: me
            }
            menu = Ifresco.menu.Grid.create(data);
            
        } else {
            var DocName = record.data.alfresco_name;
            var nodeId = record.data.nodeId;
            var nodeRef = record.data.nodeRef;
            var nodeName = record.data.alfresco_name;
            var type = record.data.alfresco_type;

            var isFolder = (type === "{http://www.alfresco.org/model/content/1.0}folder" ? true : false );
            var isLink = (nodeId != nodeRef );
            var deletedSource = (nodeId == nodeRef ) && (type === "{http://www.alfresco.org/model/application/1.0}filelink");

            var folder_path = record.raw.alfresco_node_path;

            var MimeType = record.data.alfresco_mimetype;
            // RIGHTS
            var editRights = record.data.alfresco_perm_edit;
            var delRights = record.data.alfresco_perm_delete;
            var cancelCheckoutRights = record.data.alfresco_perm_cancel_checkout;
            var createRights = record.data.alfresco_perm_create;
            var hasRights = record.data.alfresco_perm_permissions;

            // CHECKOUT LOGIC
            var isWorkingCopy = record.data.alfresco_isWorkingCopy;
            var isCheckedOut = record.data.alfresco_isCheckedOut;
            var originalId = record.data.alfresco_originalId;
            var workingCopyId = record.data.alfresco_workingCopyId;

            //SHARE FEATURE
            var sharedId = record.raw.alfresco_sharedId;
//            var isShared = !Ext.isEmpty(sharedId);//TODO: WTF?

            // BUTTONS
//            var editMetaDataBtn = Ext.getCmp('editMetadata');
//            var manageAspectsBtn = Ext.getCmp('manageAspects');
//            var specifyTypeBtn = Ext.getCmp('specifyType');
//            var checkoutBtn = Ext.getCmp('checkout');
//            var checkoutZohoBtn = Ext.getCmp('checkoutZoho');
            var data = {};
            if (record.raw) {
                data = {
                    nodeId: record.data.id,
                    editRights: record.raw.alfresco_perm_edit,
                    delRights: record.raw.alfresco_perm_delete,
                    cancelCheckoutRights: record.raw.alfresco_perm_cancel_checkout,
                    createRights: record.raw.alfresco_perm_create,
                    hasRights: record.raw.alfresco_perm_permissions,
                    folder_path: record.raw.alfresco_node_path,
                    type: record.raw.alfresco_type,
                    nodeName: record.raw.alfresco_name,
                    DocName: record.raw.alfresco_name,
                    parent: me,
                    record: record,
                    isFolder: isFolder,
                    fromComponent: me,
                    isClipboard: configData.isClipBoard
                }
            }
            menu = Ifresco.menu.Grid.create(data);
        }

        e.stopEvent();
        menu.showAt(e.getXY());
        return menu;
    },
    
    csvExport: function(grid, params) {
    	console.log("CSV EXPORT TRIGGERED");

    	Ext.Ajax.request({
    		timeout: 0,
            url: Routing.generate('ifresco_client_export_result_set'),
            params: params.params,
            success: function (response) {
            	var data = Ext.decode(response.responseText);
            	if (data.success == true) {
            		var fileName = data.fileName;
            		Ifresco.app.getController("Index").openWindow(Routing.generate('ifresco_client_grid_export_download_csv_result_set') + '?fileName=' + fileName, "_self");
            	}
            	else {
            		Ext.MessageBox.show({
                      title: Ifresco.helper.Translations.trans('Export CSV'),
                      msg: Ifresco.helper.Translations.trans('An error occured on the generating process! Please try it again later.'),
                      buttons: Ext.MessageBox.OK,
                      icon: Ext.MessageBox.INFO
                  });
            	}
            }
        });
    },
    
    pdfExport: function(grid, params) {
    	console.log("PDF EXPORT TRIGGERED");
    	var sort = "",
    		store = grid.getStore();

    	if (grid.getStore().sortInfo !== null && typeof grid.getStore().sortInfo !== 'undefined') {
            var sortdir = grid.getStore().sortInfo.direction;
            var sortfield = grid.getStore().sortInfo.field;

            params = Ext.apply(params, {sort: sortfield, dir: sortdir});
        }
    	
    	Ext.Ajax.request({
    		timeout: 0,
    		method: 'POST',
            url: Routing.generate('ifresco_client_grid_export_background_pdf_result_set'),
            params: {
            	data: Ext.encode(params)
            },
            success: function (response) {
            	var data = Ext.decode(response.responseText);
            	if (data.success == true) {
            		var fileName = data.fileName;
            		Ifresco.app.getController("Index").openWindow(Routing.generate('ifresco_client_grid_export_download_pdf_result_set') + '?fileName=' + fileName, "_self");
            	}
            	else {
            		Ext.MessageBox.show({
                      title: Ifresco.helper.Translations.trans('Generating PDF'),
                      msg: Ifresco.helper.Translations.trans('An error occured on the generating process! Please try it again later.'),
                      buttons: Ext.MessageBox.OK,
                      icon: Ext.MessageBox.INFO
                  });
            	}
            }
        });
    },
    
    pdfMerge: function(nodes) {
    	console.log("PDF MERGE",nodes);
    	Ext.Ajax.request({
    		timeout: 0,
    		method: 'POST',
            url: Routing.generate('ifresco_client_grid_export_background_pdf_result_set'),
            params: {
            	data: Ext.encode(nodes)
            },
            success: function (response) {
            	var data = Ext.decode(response.responseText);
            	if (data.success == true) {
            		var fileName = data.fileName;
            		Ifresco.app.getController("Index").openWindow(Routing.generate('ifresco_client_grid_export_download_pdf_result_set') + '?fileName=' + fileName, "_self");
            	}
            	else {
            		Ext.MessageBox.show({
                      title: Ifresco.helper.Translations.trans('Generating PDF'),
                      msg: Ifresco.helper.Translations.trans('An error occured on the generating process! Please try it again later.'),
                      buttons: Ext.MessageBox.OK,
                      icon: Ext.MessageBox.INFO
                  });
            	}
            }
        });
    },

    setupGridStoreListeners: function (gridView) {
        var gridStore = gridView.getStore();
        var contentTab = gridView.up('ifrescoContentTab');
        var previewTabPanel = contentTab.down('ifrescoViewPanelPreview');

        gridStore.on('datachanged', function (store) {
            var gridData = store.getProxy().getReader().rawData || {};
            var breadcrumb = gridData.breadcrumb || [];
            // var folderPath = gridData.folder_path;
            // var perms = gridData.perms;
            var isSearch = gridData.isSearch ? true : false;
            var isClipBoard = gridView.configData.isClipBoard; 
            var isCategoryList = gridData.isCategoryList ? true : false;
            var isTagList = gridData.isTagList ? true : false;
            var nodeId = contentTab.localConfigData.nodeId;
            console.log("GRID DATA CHANGED",gridData);
            contentTab.localConfigData.FolderPath = gridData.folder_path;
            
            gridView.isSearchRequest = isSearch;
            gridView.perms = gridData.perms;

            
            console.log("GRIDSTORE AFTER LOAD");
        	var records = gridView.getSelectionModel().getSelection(), record = null;
            console.log("GET RECORS LENGTH",records.length);
        	if (records.length > 0) {
        		record = records[0];
        		
        		previewTabPanel.items.each(function (tab) {
                	tab.enable();
                });
        		
        		/*this.loadGridDataBySelect(gridView, record);*/
        	}
            
            //TODO: user access
            // if(perms) {
            //     grid.down('button[iconCls=ifresco-icon-upload]').setDisabled(!perms.alfresco_perm_create);
            //     grid.down('button[iconCls=ifresco-create-folder-button]').setDisabled(!perms.alfresco_perm_create);
            //     grid.down('button[iconCls=ifresco-create-html-button]').setDisabled(!perms.alfresco_perm_create);
            //     grid.down('button[iconCls=ifresco-copy-clipboard-button]')
            //         .setDisabled(!perms.alfresco_perm_create || !ClipBoard.items.length);
            //     grid.down('button[iconCls=ifresco-cut-clipboard-button]')
            //         .setDisabled(!perms.alfresco_perm_create || !ClipBoard.items.length);
            //     grid.down('button[iconCls=ifresco-link-clipboard-button]')
            //         .setDisabled(!perms.alfresco_perm_create || !ClipBoard.items.length);

            //     this.permEdit = perms.alfresco_perm_edit;
            // }

            // detailUrl = config.configData.ShareFolder + (folderPath ? folderPath : '/');

            var topToolbar = gridView.down('toolbar[cls~=ifresco-view-grid-toolbar-buttons]');
            var skipNames = [];
            if (isSearch || isCategoryList || isTagList) {
                skipNames = gridView.buttonsConfig.search;
                topToolbar.items.each(function (button) {
                    if (Ext.Array.contains(skipNames, button.iconCls)) {
                        return;
                    }
                    button.setVisible(false);
                });
            } else if (isClipBoard) {
                console.log('cll');
                skipNames = gridView.buttonsConfig.clipboard;
                topToolbar.items.each(function (button) {
                    if (Ext.Array.contains(skipNames, button.iconCls)) {
                        return;
                    }
                    button.setVisible(false);
                });
            } else {
                topToolbar.items.each(function (button) {
                    button.setVisible(true);
                });
            }

            var breadcrumbToolbar = gridView.down('toolbar[cls~=ifresco-view-grid-toolbar-breadcrumb]');
            if (breadcrumbToolbar) {
                breadcrumbToolbar.removeAll();
                if (breadcrumb.length > 0) {
                    breadcrumbToolbar.show();
                    Ext.each(breadcrumb, function (crumb, index) {
                    	console.log("crumb ",crumb);
                        breadcrumbToolbar.add({
                            text: Ifresco.helper.Translations.trans(crumb.text),
                            cls: (index == 0 ? 'x-btn-text' : 'x-btn-text-icon'),
                            iconCls: (index == 0 ? '' : 'ifresco-icon-arrow'),
                            disabled: !crumb.clickable,
                            handler: function () {
                            	if (crumb.isSite) {
                            		console.log("fire event sites");
                            		Ifresco.getApplication().getController("Sites").openSiteDocument(crumb.id, crumb.siteName, crumb.siteId, crumb.siteDocLib);
                            	}
                            	else
                            		this.up('ifrescoViewport').fireEvent('openDocument', crumb.id, crumb.text);
                            }
                        });
                    });
                } else {
                    breadcrumbToolbar.hide();
                }
            }
        });

        
        
        gridStore.on('beforeload', function (store) {
            previewTabPanel.items.each(function (tab) {
                tab.disable();
            });
        });
    },

    loadGridDataBySelect: function (gridView, record) {
        // TODO: fix this check
        // if (gridView.isDblClick) {
            // return;
        // }

        // var store = gridView.getStore();
        var contentTab = gridView.up('panel[cls~=ifresco-view-content-tab]');
        var previewTabPanel = contentTab.down('panel[cls~=ifresco-view-preview-panel]');
        var localConfigData = gridView.up('panel[cls~=ifresco-view-content-tab]').localConfigData;

        var nodeId = record.data.nodeId;
        var nodeRef = record.data.nodeRef;
        var type = record.data.alfresco_type;
        var nodeText = record.data.alfresco_name;
        var nodeUrl = record.data.alfresco_url;

        var mainPreviewTab = previewTabPanel.down('panel[cls~=ifresco-preview-tab]');
        var mainVersionTab = previewTabPanel.down('panel[cls~=ifresco-versions-tab]');
        var mainMetadataTab = previewTabPanel.down('panel[cls~=ifresco-metadata-tab]');
        var mainParentMetadataTab = previewTabPanel.down('panel[cls~=ifresco-parent-metadata-tab]');
        var mainCommentsTab = previewTabPanel.down('panel[cls~=ifresco-comments-tab]');

        var deletedSource = (nodeId == nodeRef ) && (type === "{http://www.alfresco.org/model/application/1.0}filelink");

        var MimeType = record.data.alfresco_mimetype;

        // RIGHTS
        var editRights = record.data.alfresco_perm_edit;
        // var delRights = record.data.alfresco_perm_delete;
        // var cancelCheckoutRights = record.data.alfresco_perm_cancel_checkout;
        // var createRights = record.data.alfresco_perm_create;
        // var hasRights = record.data.alfresco_perm_permissions;

        // CHECKOUT LOGIC
        var isWorkingCopy = record.data.alfresco_isWorkingCopy;
        var isCheckedOut = record.data.alfresco_isCheckedOut;
        var originalId = record.data.alfresco_originalId;
        var workingCopyId = record.data.alfresco_workingCopyId;

        // BUTTONS
        if (mainMetadataTab) {
	        var editMetaDataBtn = mainMetadataTab.down('button[cls~=ifresco-metadata-edit]');
	        var manageAspectsBtn = mainMetadataTab.down('button[cls~=ifresco-metadata-manage-aspects]');
	        var specifyTypeBtn = mainMetadataTab.down('button[cls~=ifresco-metadata-specify-type]');
	        var checkoutBtn = mainMetadataTab.down('button[cls~=ifresco-metadata-checkout]');
	        var cancelCheckoutBtn = mainMetadataTab.down('button[cls~=ifresco-metadata-cancel-checkout]');
	        var refreshMetaBtn = mainMetadataTab.down('button[cls~=ifresco-metadata-refresh]');
	        var copyLinkBtn = mainMetadataTab.down('button[cls~=ifresco-metadata-copy-link]');
	        var checkoutZohoBtn = mainMetadataTab.down('button[cls~=ifresco-metadata-zoho-writer]');
	        var downloadContent = mainMetadataTab.down('button[cls~=ifresco-metadata-download-content]');
	        
	        refreshMetaBtn.enable();
	        copyLinkBtn.enable();
        }
        
        if (mainVersionTab) {
	        var addVersionBtn = mainVersionTab.down('button[cls~=ifresco-version-add]');
	        var uploadVersionBtn = mainVersionTab.down('button[cls~=ifresco-version-upload]');
        }

        

        localConfigData.selectedNodeId = nodeId;
        localConfigData.PanelNodeId = nodeId;
        localConfigData.PanelNodeMimeType = MimeType;
        localConfigData.PanelNodeText = nodeText;
        localConfigData.PanelNodeType = type;
        localConfigData.PanelNodeUrl = nodeUrl;
        localConfigData.PanelNodeIsCheckedOut = (isWorkingCopy === true || isCheckedOut === true ? true : false);

        if (isWorkingCopy === true || isCheckedOut === true) {
            var tempid = nodeId;
            var orgtempid = originalId;
            if (isCheckedOut === true) {
                tempid = workingCopyId;
            }

            localConfigData.PanelNodeOrgId = orgtempid;
            localConfigData.PanelNodeCheckedOutId = tempid;
        } else {
            localConfigData.PanelNodeCheckedOutId = null;
        }

        var activePreviewTab = previewTabPanel.getActiveTab();

        if (deletedSource) {
            Ext.MessageBox.alert(Ifresco.helper.Translations.trans('Checkout'),
                Ifresco.helper.Translations.trans('Source document has beed deleted. No data to represent.')
            );
            if(mainPreviewTab)
            	mainPreviewTab.disable();
            if(mainVersionTab)
            	mainVersionTab.disable();
            if(mainMetadataTab)
            	mainMetadataTab.disable();
            if(mainCommentsTab)
            	mainCommentsTab.disable();
            if(mainParentMetadataTab)
                mainParentMetadataTab.disable();

            return;
        }

        if (type !== "{http://www.alfresco.org/model/content/1.0}folder") {
        	if(mainPreviewTab)
        		mainPreviewTab.enable();
        	if(mainVersionTab)
        		mainVersionTab.enable();
        	if(mainMetadataTab)
        		mainMetadataTab.enable();
        	if(mainCommentsTab)
        		mainCommentsTab.enable();
            if (mainParentMetadataTab)
                mainParentMetadataTab.enable();

            if(mainMetadataTab)
            	downloadContent.enable();

            // CHECK RIGTHS
            if (editRights === true) {
            	if(mainMetadataTab) {
	                editMetaDataBtn.enable();
	                manageAspectsBtn.enable();
	                specifyTypeBtn.enable();
	                checkoutBtn.enable();
            	}
            	if(mainVersionTab) {
	                addVersionBtn.enable();
	                uploadVersionBtn.enable();
            	}

                if (isWorkingCopy === true || isCheckedOut === true) {
                	if(mainMetadataTab) {
	                    checkoutBtn.setTooltip("Checkin");
	                    checkoutBtn.setIconCls("ifresco-checkin-node-button");
	
	                    specifyTypeBtn.disable();
	                    if (isWorkingCopy !== true) {
	                        editMetaDataBtn.disable();
	                        manageAspectsBtn.disable();
	                        specifyTypeBtn.disable();
	                    }
	
	                    cancelCheckoutBtn.enable();
	                    cancelCheckoutBtn.setVisible(true);
                	}
                } else {
                	if(mainMetadataTab) {
	                    checkoutBtn.setTooltip("Checkout");
	                    checkoutBtn.setIconCls("ifresco-checkout-node-button");
	
	                    cancelCheckoutBtn.disable();
	                    cancelCheckoutBtn.setVisible(false);
                	}
                }
            } else {
            	if(mainMetadataTab) {
	                editMetaDataBtn.disable();
	                manageAspectsBtn.disable();
	                specifyTypeBtn.disable();
	                checkoutBtn.disable();
	                checkoutZohoBtn.disable();
            	
	                cancelCheckoutBtn.disable();
	                cancelCheckoutBtn.setVisible(false);
            	}
            	
            	if(mainVersionTab) {
	            	addVersionBtn.disable();
	                uploadVersionBtn.disable();
            	}
            }

            if (activePreviewTab) {
                previewTabPanel.reloadTabData(activePreviewTab);
            }
        } else {
            try {
            	if(mainPreviewTab)
            		mainPreviewTab.enable();
            	if(mainVersionTab)
            		mainVersionTab.disable();
            	if(mainMetadataTab)
            		mainMetadataTab.enable();
            	if(mainCommentsTab)
            		mainCommentsTab.enable();

                if (activePreviewTab === mainVersionTab) {
                    previewTabPanel.setActiveTab(mainMetadataTab);
                } else {
                    if (activePreviewTab) {
                        previewTabPanel.reloadTabData(activePreviewTab);
                    }
                }

                if(mainParentMetadataTab) {
                    if (Ifresco.helper.Settings.get('ParentMetaDocumentOnly') == "true") {
                        mainParentMetadataTab.disable();
                    } else {
                    	mainParentMetadataTab.enable();
                    }
                }

                if (editRights === true) {
                	if(mainMetadataTab) {
	                    editMetaDataBtn.enable();
	                    manageAspectsBtn.enable();
	                    specifyTypeBtn.enable();
                	}
                } else {
                	if(mainMetadataTab) {
	                    editMetaDataBtn.disable();
	                    manageAspectsBtn.disable();
	                    specifyTypeBtn.disable();
                	}
                }

                if(mainMetadataTab) {
	                checkoutBtn.disable();
	                checkoutZohoBtn.disable();
	                downloadContent.disable();
                }
            } catch (err) {
                console.log(err);
            }

        }
    }
});