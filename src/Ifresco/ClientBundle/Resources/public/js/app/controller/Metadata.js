Ext.define('Ifresco.controller.Metadata', {
    extend: 'Ext.app.Controller',
    refs: [{
        selector: 'viewport > ifrescoCenter',
        ref: 'tabPanel'
    }],
    
    init: function() {
        this.control({
            'ifrescoViewMetadataTab': {     	
                editMetadata: this.editMetadata,
                manageAspects: this.manageAspects,
                specifyType: this.specifyType,
                checkIn: this.checkIn,
                checkOut: this.checkOut,
                cancelCheckout: this.cancelCheckout
            },
            'ifrescoViewWindowManageAspects': {
                saveAspects: this.saveAspects
            },
            'ifrescoViewWindowSpecifyType': {
                saveSpecifiedType: this.saveSpecifiedType
            },
            'ifrescoMenu': {
                editMetadata: this.editMetadata,
                specifyType: this.specifyType,
                manageAspects: this.manageAspects,
                checkIn: this.checkIn,
                checkOut: this.checkOut,
                cancelCheckout: this.cancelCheckout,
                editGoogleDocs: this.editGoogleDocs
            },
            'ifrescoGoogleEditorTab': {
            	saveGoogleEditorDocs: this.saveGoogleEditorDocs,
            	discardGoogleEditorDocs: this.discardGoogleEditorDocs
            },
            'ifrescoEditMetadataTab': {
            	saveMetadata: this.saveMetadata
            }
            
        });
    },
    
    saveGoogleEditorDocs: function(tab, nodeId) {
    	
    	var window = Ifresco.view.window.NewVersion.create({
    		title: Ifresco.helper.Translations.trans('Save changes'),
    		data: {nodeId: nodeId},
    		upload: false,
    		googleDocs: true,
    		callback: function(w, data) {
    			w.close();
    			tab.setLoading(true);
    			Ext.Ajax.request({
    	            url: Routing.generate('ifresco_client_google_docs_save_changes'),
    	            params: data,
    	            success: function (res) {
    	            	var jsonData = Ext.decode(res.responseText);
    	            	tab.setLoading(false);
    	            	tab.close();
    	            },
    	            failure: function (data) {
    	                console.log(data.responseText);
    	            }
    	        });
    		}
        });
        
        window.show();
    },
    
    discardGoogleEditorDocs: function(tab, nodeId) {
    	Ext.Ajax.request({
            url: Routing.generate('ifresco_client_google_docs_discard_changes'),
            params: {
            	nodeId: nodeId
            },
            success: function (res) {
            	var jsonData = Ext.decode(res.responseText);
            	tab.close();
            },
            failure: function (data) {
                console.log(data.responseText);
            }
        });
    },
    
    editGoogleDocs: function(nodeId,nodeName) {
    	var me = this;
    	Ext.Ajax.request({
            url:Routing.generate('ifresco_client_google_docs_authurl'),
            disableCaching: true,
            params: {
                
            },
            success: function (res) {
            	var data = Ext.decode(res.responseText);
            	window.Alfresco = {GoogleDocs: {onOAuthReturn: me.googleAuthReturn, nodeId: nodeId, nodeName: nodeName, me: me, googleDocs: true}};
            	window.showModalDialog(data.authURL, {});
            }
        });
    },
    
    googleAuthReturn: function(success) {
    	if (success) {
    		var self = this.me;
	    	window.close();
	    	
	    	self.openGoogleEditor(this.nodeId,this.nodeName);
    	}
    },
    
    openGoogleEditor: function(nodeId, nodeName) {
    	var tabPanel = this.getTabPanel();
    	/*Ext.Ajax.request({
            url:Routing.generate('ifresco_client_google_docs_node_info'),
            params: {
                nodeId: nodeId
            },
            success: function (res) {
            	
            	var data = Ext.decode(res.responseText);
            	
            	var EditorTab = tabPanel.down('ifrescoGoogleEditorTab[ifrescoId=' + nodeId + ']');
            	
            	if (!EditorTab) {
	            	EditorTab = Ifresco.view.GoogleEditorTab.create({
	                    ifrescoId: nodeId,
	                    title: "Google Docs Editor: "+data.name,
	                    nodeId: nodeId,
	                    editorUrl: data.editorUrl,
	                    resourceID: data.resourceID,
	                    locked: data.locked
	                });
	            	
	                tabPanel.add(EditorTab);
            	}
            	else
            		tabPanel.setActiveTab(EditorTab);
            }
        });*/	
        var EditorTab = tabPanel.down('ifrescoGoogleEditorTab[ifrescoId=' + nodeId + ']');
        	
    	if (!EditorTab) {
        	EditorTab = Ifresco.view.GoogleEditorTab.create({
                ifrescoId: nodeId,
                title: "Google Docs Editor: "+nodeName,
                nodeId: nodeId
            });
        	
            tabPanel.add(EditorTab);
    	}
    	tabPanel.setActiveTab(EditorTab);            
    },
    
    saveMetadata: function(editMetadataTab, nodeId) {
    	var tabPanel = this.getTabPanel();
        
    	editMetadataTab.setLoading(true, true);
    	var form = editMetadataTab.down('form[cls~=ifresco-view-editmetadata-form]'),
    		submitData = form.getForm().getValues(),
    		categoryView = form.down("dataview[cls=ifresco-edit-metadata-categories-view]"),
    		categories = [];
    	
    	if (categoryView) {
    		var categoryStore = categoryView.getStore();
	    	categoryStore.each(function (r) {
	    		categories.push(r.data);
	        });
    	}
    	
    	submitData.categories = categories;
    	
    	console.log("SUBMIT DATA EDIT SAVE",categories,submitData);
        Ext.Ajax.request({
            url:Routing.generate('ifresco_client_metadata_save', {}),
            disableCaching: true,
            params: {
                nodeId: nodeId,
                data: Ext.encode(submitData)
            },
            success: function (res) {
                var data = Ext.decode(res.responseText);
                editMetadataTab.setLoading(false);
            }
        });
        
        var metadataTab = tabPanel.down('ifrescoViewMetadataTab[ifrescoId=' + nodeId + ']');
        console.log("SAVE METADATA , SEARCH FOR META TAB",metadataTab);
        if (metadataTab) {
        	metadataTab.loadCurrentData(nodeId);
        }
    },

    editMetadata: function (nodeId, nodeName) {
//        editMetadata(nodeId,nodeName);
console.log("EDIT METADATA",nodeId,nodeName);
		
        var tabPanel = this.getTabPanel(),
        	nodeName = Ext.util.Format.stripTags(nodeName);
        var editMetadataTab = tabPanel.down('ifrescoEditMetadataTab[ifrescoId=' + nodeId + ']'); // TODO check if works for categories

        if (!editMetadataTab) {
            editMetadataTab = Ifresco.view.EditMetadataTab.create({
                ifrescoId: nodeId,
                nodeId: nodeId,
                title: nodeName
            });
            tabPanel.add(editMetadataTab);
            tabPanel.setActiveTab(editMetadataTab);
//            contentTab.reloadGridData({params: {
//                nodeId: nodeId,
//                columnSetId: Ifresco.helper.Registry.get('ColumnsetId')
//            }});
        } else {
            tabPanel.setActiveTab(editMetadataTab);
//            contentTab.reloadGridData({params: {
//                nodeId: nodeId,
//                columnSetId: Ifresco.helper.Registry.get('ColumnsetId')
//            }});
        }

        editMetadataTab.setLoading(true, true);
        Ext.Ajax.request({
            url:Routing.generate('ifresco_client_metadata_node_get', {
                nodeId: nodeId,
                fieldTypeSeparator: 'true'
            }),
            disableCaching: true,
            params: {
                nodeId: nodeId
            },
            success: function (res) {
                var data = Ext.decode(res.responseText);
                editMetadataTab.loadForm(data);
                editMetadataTab.setLoading(false);
            }
        });


        console.log(nodeId);
        console.log(nodeName);
    },

    manageAspects: function (nodeId, parent) {
        if (nodeId !== null && typeof nodeId !== 'undefined') {
            Ext.Ajax.request({
                method: 'POST',
                url: Routing.generate('ifresco_client_metadata_aspects_get', {nodeId: nodeId}),
                success: function(res) {
                    var resData = Ext.decode(res.responseText);
                    var data = [];
                    var selectedData = [];

                    Ext.each(resData.data.aspectList, function (aspect) {
                        data.push([aspect.name, aspect.title]);
                    });
                    Ext.each(resData.data.currentAspectList, function (aspect) {
                        data.push([aspect.name, aspect.title]);
                        selectedData.push(aspect.name)
                    });

                    var win = Ext.create('Ifresco.view.window.ManageAspects', {
                        nodeId: resData.data.nodeId,
                        localStore: Ext.create('Ext.data.ArrayStore', {
                            data: data,
                            fields: ['value', 'text'],
                            sortInfo: {
                                field: 'text',
                                direction: 'ASC'
                            }
                        }),
                        selectedValues: selectedData,
                        parentEl: parent
                    });
                    win.show();
                }
            });
        }
    },

    specifyType: function (nodes, parent) {
    	
        var win = Ext.create('Ifresco.view.window.SpecifyType', {
        	nodes: nodes,
            parentEl: parent
        });

        win.show();
    },

    checkIn: function (nodeId, parent) {
        Ext.Ajax.request({
            method: 'POST',
            url: Routing.generate('ifresco_client_metadata_checkin', {}),
            params: {
            	nodeId: nodeId,
            	note: ''
            },
            success: function(res) {
                var resData = Ext.decode(res.responseText);
                Ext.MessageBox.alert(
                    Ifresco.helper.Translations.trans('Checkin'),
                    Ifresco.helper.Translations.trans('Document checked in successfully.')
                );
                
                if (parent != null) {
	                var localConfigData = parent.up('panel[cls~=ifresco-view-content-tab]').localConfigData;
	                localConfigData.PanelNodeCheckedOutId = '';
	                localConfigData.PanelNodeIsCheckedOut = false;
	
	                var checkoutBtn = parent.down('button[cls~=ifresco-metadata-checkout]');
	                checkoutBtn.setTooltip(Ifresco.helper.Translations.trans('Checkout'));
	                checkoutBtn.setIconCls("ifresco-checkout-node-button");
	
	                var cancelCheckoutBtn = parent.down('button[cls~=ifresco-metadata-cancel-checkout]');
	                cancelCheckoutBtn.disable();
	                cancelCheckoutBtn.setVisible(false);
	
	                parent.loadCurrentData(nodeId);
                }
            }
        });
    },

    checkOut: function (nodeId, parent) {
        Ext.Ajax.request({
            method: 'GET',
            url: Routing.generate('ifresco_client_metadata_checkout', {nodeId: nodeId}),
            success: function(res) {
                var resData = Ext.decode(res.responseText);
                Ext.MessageBox.alert(
                    Ifresco.helper.Translations.trans('Checkout'),
                    Ifresco.helper.Translations.trans('Document checked out successfully.')
                );
                
                if (parent != null) {
	                var localConfigData = parent.up('panel[cls~=ifresco-view-content-tab]').localConfigData;
	                localConfigData.PanelNodeCheckedOutId = resData.workingCopyId;
	                localConfigData.PanelNodeIsCheckedOut = true;
                
                
	                var checkoutBtn = parent.down('button[cls~=ifresco-metadata-checkout]');
	                checkoutBtn.setTooltip(Ifresco.helper.Translations.trans('Checkin'));
	                checkoutBtn.setIconCls("ifresco-checkin-node-button");
	
	                var cancelCheckoutBtn = parent.down('button[cls~=ifresco-metadata-cancel-checkout]');
	                cancelCheckoutBtn.enable();
	                cancelCheckoutBtn.setVisible(true);
	
	                parent.loadCurrentData(nodeId);
                }
            }
        });
    },

    cancelCheckout: function (nodeId, parent) {
        Ext.Ajax.request({
            method: 'GET',
            url: Routing.generate('ifresco_client_metadata_cancel_checkout', {nodeId: nodeId}),
            success: function(res) {
                var resData = Ext.decode(res.responseText);

                Ext.MessageBox.alert(
                    Ifresco.helper.Translations.trans('Cancel Checkout'),
                    Ifresco.helper.Translations.trans('Successfully canceled.')
                );
                
                if (parent != null) {
                	var localConfigData = parent.up('panel[cls~=ifresco-view-content-tab]').localConfigData;
                    localConfigData.PanelNodeCheckedOutId = '';
                    localConfigData.PanelNodeIsCheckedOut = false;
                    
	                var cancelCheckoutBtn = parent.down('button[cls~=ifresco-metadata-cancel-checkout]');
	                cancelCheckoutBtn.disable();
	                cancelCheckoutBtn.setVisible(false);
	
	                var checkoutBtn = parent.down('button[cls~=ifresco-metadata-checkout]');
	                checkoutBtn.setTooltip(Ifresco.helper.Translations.trans('Checkout'));
	                checkoutBtn.setIconCls("ifresco-checkout-node-button");
	
	                parent.loadCurrentData(nodeId);
                }
            },
            failure: function() {
                Ext.MessageBox.alert(
                    Ifresco.helper.Translations.trans('Error'),
                    Ifresco.helper.Translations.trans('An unknown problem occured at the check in process.')
                );
            }
        });
    },

    saveAspects: function (nodeId, selectedData, all, parentEl) {
        Ext.Ajax.request({
            method: 'POST',
            url: Routing.generate('ifresco_client_metadata_aspects_save'),
            params: {
                nodeId: nodeId,
                selectedAspects: Ext.encode(selectedData),
                aspects: Ext.encode(all)
            },
            success: function() {
                if (parentEl) {
                    parentEl.loadCurrentData(nodeId);
                }
            },
            failure: function() {}
        });
    },

    saveSpecifiedType: function (nodes, typeId, parentEl) {
    	console.log("COME TO SAVE ",nodes)
        Ext.Ajax.request({
            method: 'POST',
            url: Routing.generate('ifresco_client_metadata_content_type_save'),
            params: {
            	nodes: Ext.encode(nodes),
                typeId: typeId
            },
            success: function() {
                if (parentEl) {
                    parentEl.loadCurrentData(nodeId);
                }
            },
            failure: function() {}
        });
    }
});