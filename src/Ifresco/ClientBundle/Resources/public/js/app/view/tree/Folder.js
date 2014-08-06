Ext.define('Ifresco.tree.Folder',{
    extend: 'Ext.tree.Panel',
    alias: 'widget.ifrescoTreeFolder',
    itemId: 'ifrescoTreeFolder',
    rootVisible:true,
    border: false,
    autoScroll:true,
    animate:true,
    ddConfig:false,
    height: 'auto',
    split: true,
    containerScroll: true,
    iconCls: 'ifresco-icon-tree-folder',
    uploadTreePanel: null,
    isSite: false,
    siteId: null,
    siteDocLib: null,
    siteName: null,
    initLoad: true,
    droppedRecords: null,
    
    viewConfig: {
        plugins: {
            ptype: 'treeviewdragdrop',
            //dragGroup: 'ifrescoDocuments',
            enableDrag: false,
            dropGroup: 'ifrescoDocuments',
            appendOnly: true
        },
        listeners: {
        	beforedrop: function(node, data, overModel, dropPos, dropHandlers) {
        		var nodeId = "";
        		if (typeof data.view.panel.localConfigData.nodeId != "undefined")
        			nodeId = data.view.panel.localConfigData.nodeId;

        		console.log(node, data, overModel, dropPos, dropHandlers);

        		if (overModel.get('id') == nodeId) {
        			dropHandlers.cancelDrop();
        		}
        		else {
	                this.droppedRecords = data.records;
	                data.records = []; // fix updateinfo message
	                dropHandlers.processDrop();
        		}
            },
            drop: function(node, data, overModel, dropPos, opts) {
                /*var str = '';
                Ext.iterate(this.droppedRecords, function(record) {
                    str += record.get('title') + ' (id = ' + record.get('id') + ') dropped on ' + overModel.get('id') + '\n';
                });
                console.log(str);*/

                this.up('ifrescoViewport').fireEvent('moveTo', this.droppedRecords, overModel);
                this.droppedRecords = null;
            }
        }
    },
    
    initComponent: function () {
    	console.log("rootNf",rootInfo)
    	if (!this.isSite) {
	    	Ext.apply(this, {
	            store: new Ifresco.store.TreeFolders({root: rootInfo})
	    	});
    	}
    	else {
    		Ext.apply(this, {
	            store: new Ifresco.store.SiteTree({root: {
	            	text: Ifresco.helper.Translations.trans('Documents'), 
	            	draggable: false, 
	            	id: this.siteDocLib, // TODO - needs to be changed with the documentLibrary id somehow
	            	disabled: false
	            }})
	    	});
    		
    		this.getStore().load({params: {isRootSite: "true"}});
    	}

        Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('Folders'),
            tbar: [{
	                xtype: 'combo',
	                mode: 'remote', 
	                value: '',
	                //triggerAction: 'all',
	                typeAhead: false,
	                hideLabel: true,
	                hideTrigger:true,
	                name: 'folderSearch',
	                displayField: 'path',
	                valueField: 'nodeRef',
	                store: new Ifresco.store.FolderSearch({}),
	                //pageSize: 10,
	                matchFieldWidth: false,
	                listConfig: {
	                    loadingText: Ifresco.helper.Translations.trans('Searching Folder...'),
	                    emptyText: Ifresco.helper.Translations.trans('No folder found.'),
	                    //resizable: true
	                },
	                hideMode: 'offsets',
	                enableKeyEvents: true,
	                shiftPressed: false,
	                listeners: {
                        render : {
                            single : true,
                            buffer : 100,
                            fn     :function() {
                                this.el.setWidth(this.up('toolbar').el.getWidth() - 30);
                            }
                        },
                        
                        keydown: function(combo, e){
                            if (e.getKey() == e.SHIFT) {
                            	console.log("set key down shiftpressed");
                            	combo.shiftPressed = true;
                            }
                        },
                        
                        keyup: function(combo, e){
                        	combo.shiftPressed = false;
                        },
                        
	                    select: function(combo, selection, eOpts) {
	                    	console.log("SELECT FOLDDER",eOpts,combo.shiftPressed);
	                    	var tree = this.up("treepanel");
	                    	var folder = selection[0];
	                    	console.log(tree);
	                    	
	                    	if (folder) {
	                    		
	                    		var path = folder.get("path"),
	                    			path = path.replace(/\/Company Home/,""),
	                    			path = path.replace(/^\/(.*)/,"$1"),
	                    			path = path.replace(/^(.*)\/$/,"$1"),
	                    			id = folder.get('nodeRef'),
	                    			id = id.replace(/workspace:\/\/SpacesStore\//,"");
	                    		
	                    		if (!combo.shiftPressed) {
	                    			this.up('ifrescoViewport').fireEvent('openDocument', id);
	                    		}
	                    		else {
		                    		console.log("select path ", "/Repository/"+ path)
		                    		tree.selectPath("/Repository/"+ path,"text","/",function(bSuccess) { 
		                    			console.log("CALLBACK",bSuccess)
		                    		});
	                    		}
	                    	}
	                    	
	                    	
	                    	
	                    	this.reset();
	                    	return false;
	                    	
	                    	// http://www.sencha.com/forum/showthread.php?142704-Show-Hide-of-the-tree-node-in-ExtJS-4
	                        /*var post = selection[0];
	                        if (post) {
	                            window.location =
	                                Ext.String.format('http://www.sencha.com/forum/showthread.php?t={0}&p={1}', post.get('topicId'), post.get('id'));
	                        }*/
	                    	/*var tree = this;
	                    	
	                    	var re = new RegExp(Ext.escapeRe(path), 'i');
	                    	
                            var visibleSum = 0;

                            var filter = function(node) { // descends into child nodes
                                if(node.hasChildNodes()) {
                                    visibleSum = 0
                                    node.eachChild(function(childNode) {
                                        if(childNode.isLeaf()) {
                                            if(!re.test(childNode.data.text)) {
                                                filteredNodes.push(childNode);
                                            } else {
                                                visibleSum++;
                                            }
                                        } else if(!childNode.hasChildNodes() && re.test(childNode.data.text)) {// empty folder, but name matches
                                            visibleSum++;
                                        } else {
                                            filter(childNode);
                                        }
                                    });
                                    if(visibleSum == 0 && !re.test(node.data.text)) {
                                        filteredNodes.push(node);
                                    }
                                } else if(!re.test(node.data.text)) {
                                    filteredNodes.push(node);
                                }
                                console.log(filteredNodes);
                            }
                            tree.getRootNode().cascadeBy(filter);
                            Ext.each(filteredNodes, function(n) {
                                var el = Ext.fly(tree.getView().getNodeByRecord(n));
                                if (el != null) {
                                    el.setDisplayed(false);
                                }
                            });*/
	                    }
	                }
	            }/*,{
	            	iconCls: 'ifresco-icon-cancel',
	            	tooltip: Ifresco.helper.Translations.trans('Clear'),
	            	handler: function() {
	            		this.down("combo[name~=folderSearch]").reset();
	            	},
	            	scope: this
	            }*/,
                '->',{
                iconCls: 'ifresco-icon-refresh',
                tooltip: Ifresco.helper.Translations.trans('Reload'),
                scope:this,
                handler: function(){
                    if(!this.getStore().isLoading()) {
                    	if (!this.isSite) {
                    		this.getStore().load();
                    	}
                    	else {
                    		this.initLoad = true;
                    		this.getStore().load({params: {isRootSite: "true"}});
                    	}
                    }
                }
            }],
            uploadTreePanel: this.createUploadTreePanel()
        });
console.log("after init folder tree",this.uploadTreePanel);
        this.callParent();
    },

    listeners: {
    	load: function() {
    		if (this.isSite) {
	    		if (this.initLoad == true) {
	    			this.initLoad = false;
	    			this.getStore().getRootNode().expand();
	    		}
    		}
    	},
        itemclick: function(node, record) {
        	if (!this.isSite)
        		this.up('ifrescoViewport').fireEvent('openDocument', record.get('id'));
        	else
        		this.fireEvent('openSiteDocument', record.get('id'), this.siteName, this.siteId, this.siteDocLib);
        },
        beforeitemdblclick: function(node, record, item, index ,e){
            e.preventDefault();
            return false; //avoid double load
        },
        itemcontextmenu: function (node, record, item, index ,e) {
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
                    DocName: record.raw.text,
                    parent: this,
                    record: record,
                    fromComponent: this
                }
            }

            var menu = Ifresco.menu.Folder.create(data);
            e.stopEvent();
            menu.showAt(e.getXY());
        }
    },

    createUploadTreePanel: function () {
        var filters = Ext.decode(Ifresco.helper.Settings.get('uploadAllowedTypes'));
        
        return Ifresco.window.Upload.create({
            modal:true,
            layout:'fit',
            width:500,
            height:360,
            closeAction:'hide',
            title: Ifresco.helper.Translations.trans('Upload File(s)'),
            plain: true,
            constrain: true,
            uploader: {
                filters : [{
                    title : Ifresco.helper.Translations.trans('General'),
                    extensions : filters.join(",")
                }]
            },
            listeners: {
                beforestart: function(uploader, files) {

                },
                uploadcomplete: function(uploader, files) {
                    var uploadPanel = this;
                    console.log(this);
                    uploadPanel.uploadComposed = false;
                    if(uploadPanel.autoCloseControl.getValue() && uploadPanel._dropboxFiles.length == 0) {
                        uploadPanel.hide();
                    }
                }
            },
            buttons: [{
                text: Ifresco.helper.Translations.trans('Close'),
                handler: function(btn) {
                    btn.ownerCt.ownerCt.hide();
                }
            }]
        });
        //TODO: implement uploading
//        var uploadTreePanel = Ext.create('Ext.ux.upload.IfsWindow', {
//            modal:true,
//            layout:'fit',
//            width:500,
//            height:360,
//            closeAction:'hide',
//            title: '<?php echo $view['translator']->trans('Upload File(s)'); ?>',
//            plain: true,
//            constrain: true,
//            uploader: {
//                filters : [
//                    {title : "<?php echo $view['translator']->trans('General'); ?>", extensions : "<?php echo implode(',', json_decode($view['settings']->getSetting("uploadAllowedTypes", '[]'))); ?>"}
//                ]
//            },
//            listeners: {
//                beforeupload: function(uploader, files) {
//
//                },
//                uploadcomplete: function(uploader, files) {
//
//                    if(uploadTreePanel.autoCloseControl.getValue() && uploadTreePanel._dropboxFiles.length == 0) {
//                        uploadTreePanel.uploadComposed = false;
//                        uploadTreePanel.hide();
//                    }
//                }
//            },
//            buttons: [{
//                text: '<?php echo $view['translator']->trans('Close'); ?>',
//                handler: function(btn,e) {
//                    btn.ownerCt.ownerCt.hide();
//                }
//            }]
//        });
    }
});
