Ext.define('Ifresco.menu.Menu',{
    extend: 'Ext.menu.Menu',
    alias: 'widget.ifrescoMenu',
    nodeId: null,
    editRights: false,
    delRights: false,
    cancelCheckoutRights: false,
    createRights: false,
    hasRights: false,
    folder_path: false,
    type: false,
    DocName: false,
    isFolder: false,
    isClipboard: false,
    record: null,
    fromComponent: null,
    
    // MULTIPLE ONLY
    isMultiple: false,
    allFolders: false,
	allImages: false,
    allOCRable: false,
    allPDF: false,
    allDisAllowedEdit: false,
    allDisAllowedDelete: false,
    records: [],

    initComponent: function () {
        Ext.apply(this, {
            items: this.getItems()
        });

        this.callParent();
    },
    
    isOfficeDocument: function(mimetype) {
    	var mimetypes = ["application/msword", 
    	             "application/vnd.openxmlformats-officedocument.wordprocessingml.document", 
    	             "application/vnd.ms-excel", 
    	             "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    	             "application/vnd.ms-powerpoint",
    	             "application/vnd.openxmlformats-officedocument.presentationml.presentation"
    	             ]
    	console.log("check office doc",mimetype,Ext.Array.contains(mimetypes, mimetype));
    	return Ext.Array.contains(mimetypes, mimetype);
    },

    getItems: function () {
    	var self = this;
    	console.log("FROM COMPONENT",this.fromComponent);
    	if (!this.isFolder && !this.isMultiple) {
    		var nodeName = this.record.data.alfresco_name;
	    	var nodeRef = this.record.data.nodeRef;
	    	
	        var MimeType = this.record.data.alfresco_mimetype;
	        
	        var isLink = (this.nodeId != nodeRef );
	        
	        var deletedSource = (this.nodeId == nodeRef ) && (this.type === "{http://www.alfresco.org/model/application/1.0}filelink");
	        
	    	// RIGHTS 
	        var editRights = this.record.data.alfresco_perm_edit;
	        var delRights = this.record.data.alfresco_perm_delete;
	        var cancelCheckoutRights = this.record.data.alfresco_perm_cancel_checkout;
	        var createRights = this.record.data.alfresco_perm_create;
	        var hasRights = this.record.data.alfresco_perm_permissions;
	        
	        var isWorkingCopy = this.record.data.alfresco_isWorkingCopy;
            var isCheckedOut = this.record.data.alfresco_isCheckedOut;
            var originalId = this.record.data.alfresco_originalId;
            var workingCopyId = this.record.data.alfresco_workingCopyId;
            
            if (this.type !== "{http://www.alfresco.org/model/content/1.0}folder")
                var nodeType = "file";
            else
                var nodeType = "folder";
    	}
    	else if (this.isMultiple) {
    		var isLink = false,
    			deletedSource = false;
    	}
    	
        var items = [{
            iconCls:'ifresco-icon-upload',
            disabled: !this.createRights,
            text: Ifresco.helper.Translations.trans('Upload File(s)'),
            hidden: !this.isFolder,
            handler: function(){
            	console.log("before upload ",this.fromComponent.uploadTreePanel.uploader)
                var url = Routing.generate('ifresco_client_upload_REST') + '?nodeId=' + this.nodeId + '&overwrite=false&ocr=false';
            	this.fromComponent.uploadTreePanel.show();
                this.fromComponent.uploadTreePanel.uploader.uploader.settings.uploadpath = this.fromComponent.uploadTreePanel.uploader.uploader.settings.url = url;
                
            },
            scope: this
        },{
            iconCls: 'ifresco-create-folder-button',
            disabled: !this.createRights && this.nodeId != 'root',
            text: Ifresco.helper.Translations.trans('Create Space'),
            hidden: !this.isFolder || this.isMultiple,
            handler: function() {
                var window = Ifresco.view.window.CreateSpace.create({nodeId: this.nodeId, parent: this.fromComponent });
                window.show();
            },
            scope:this
        },{
            iconCls: 'ifresco-create-html-button',
            disabled: !this.createRights,
            text: Ifresco.helper.Translations.trans('Create HTML'),
            hidden: !this.isFolder || this.isMultiple,
            handler: function() {
                var window = Ifresco.view.window.CreateHtmlDocument.create({nodeId: this.nodeId});
                window.show();
            },
            scope: this
        }];

        if (Ifresco.helper.Settings.get('scanViaSane') == 'true') {
            items.push({
                iconCls:'ifresco-icon-scan',
                hidden: !this.isFolder || this.isMultiple,
                text:Ifresco.helper.Translations.trans('Scan Document'),
                handler: function() {
                	var btn = Ifresco.button.Sane.create({
                		folder: self.DocName,
                        nodeId: self.nodeId
                	});
                	
                	btn.scannerForm();
                },
                scope: this
            });
        }

        if (this.isFolder)
        	items.push('-');

        items.push({ // TODO - add for document preview
            iconCls: 'ifresco-icon-add-tab-button',
            text: Ifresco.helper.Translations.trans('Open in new tab'),
            hidden:this.isMultiple,
            handler: function(){
                var title = this.nodeId != "root" ? this.DocName : Ifresco.helper.Translations.trans('Repository');
                console.log("open in new tab",this.isFolder,self.isFolder,title,this.DocName);
                if (this.isFolder) {
                	this.fireEvent('openDocument', this.nodeId, title);
                }
                else {
                	this.fireEvent('openDocumentDetail', this.nodeId, title);
                }
            },
            scope:this
        });
        
        if (!this.isFolder && !this.isMultiple)
        	items.push('-');

        items.push({ // SINGLE & MULTIPLE
            iconCls: 'ifresco-icon-cut',
            text: Ifresco.helper.Translations.trans('Add to clipboard'),
            disabled: this.nodeId == 'root',
            hidden: this.isClipboard,
            handler: function(){
                if (this.isMultiple) {
                	var nodeIds = [];
            		for (var i=0; i < this.records.length; ++i) {
            			nodeIds.push(this.records[i].nodeId);
                    }
            		
            		this.fireEvent('addToClipboard', nodeIds);
            	}
                else 
                	this.fireEvent('addToClipboard', this.nodeId); // TODO CHECK IF INSIDE THEN SET REMOVE CLIPBOARD
            },
            scope:this
        });

        items.push({ 
            iconCls: 'ifresco-metadata-edit-button',
            text: Ifresco.helper.Translations.trans('Edit Metadata'),
            disabled:(this.editRights === true ? false : true),
            hidden:this.isMultiple,
            handler: function(){
                this.fireEvent('editMetadata', this.nodeId, this.DocName);
            },
            scope:this
        });
        
        if (!this.isFolder && !this.isMultiple) {
	        items.push({ 
	            iconCls: 'ifresco-google-drive-button',
	            text: Ifresco.helper.Translations.trans('Edit in Google Docs'),
	            disabled:(this.editRights === true ? false : true),
	            hidden: !this.isOfficeDocument(MimeType),
	            handler: function(){
	                this.fireEvent('editGoogleDocs', this.nodeId, this.DocName);
	            },
	            scope:this
	        });
        }

        items.push({ // SINGLE & MULTIPLE
            iconCls: 'ifresco-specify-type-button',
            text: Ifresco.helper.Translations.trans('Specify type'),
            disabled:(this.editRights === true ? false : true),
            handler: function(){
            	if (this.isMultiple) {
            		var allAllowed = true, allowedNodes = [], nodeNames = '';

                    for (var i=0; i < this.records.length; ++i) {
                        if(this.records[i].editRights == false) {
                            allAllowed = false;
                            nodeNames += this.records[i].nodeName + '<br />';
                        }
                        else {
                            allowedNodes.push(this.records[i].nodeId);
                        }
                    }
                    
                    if(!allAllowed) {
                        Ext.MessageBox.show({
                            title: Ifresco.helper.Translations.trans('Error'),
                            msg: Ifresco.helper.Translations.trans('Not all documents are allowed for this action. <br />Following are out from the scope:')+ '<br><b>'+nodeNames+'</b>',
                            fn:function(btn) {
                                if (btn === "yes") {
                                    if(allowedNodes.length == 0)
                                    {
                                        Ext.MessageBox.show({
                                            title: Ifresco.helper.Translations.trans('Error'),
                                            msg: Ifresco.helper.Translations.trans('No files to process'),
                                            buttons: Ext.MessageBox.OK,
                                            icon: Ext.MessageBox.INFO
                                        });
                                    } else {
                                    	this.fireEvent('specifyType', allowedNodes, null);
                                    }
                                }
                                //$(".PDFRenderer").show();
                            },
                            buttons: Ext.MessageBox.YESNO,
                            icon: Ext.MessageBox.QUESTION
                        });
                    } else {
                    	this.fireEvent('specifyType', allowedNodes, null);
                    }
            	}
            	else {
            		this.fireEvent('specifyType', [this.nodeId], null);
            	}
                
            },
            scope: this
        });

        items.push({
            iconCls: 'ifresco-manage-aspects-button',
            text: Ifresco.helper.Translations.trans('Manage aspects'),
            disabled: (this.editRights === true ? false : true),
            hidden:this.isMultiple,
            handler: function(){
                this.fireEvent('manageAspects', this.nodeId, null);
            },
            scope: this
        });
        
        
        items.push({
            iconCls: 'create-html-edit', // TODO add icon to css
            text: Ifresco.helper.Translations.trans('Inline Edit'),
            disabled:(editRights === true ? false : true),
            hidden: this.isFolder || this.isMultiple || !this.record.raw.alfresco_is_inlineeditable,
            scope:this,
            handler: function(){
            	this.fireEvent('editHTMLdoc', this.nodeId); // TODO implement function
                //editHTMLdoc(nodeId, '<?php echo $containerName; ?>');
            }
        });

        items.push({
            iconCls: 'ifresco-icon-selector',
            text: Ifresco.helper.Translations.trans('Quick add aspect'),
            disabled:(this.editRights === true ? false : true),
            hidden:this.isMultiple,
            menu: {
                items:[{
                    iconCls:'ifresco-icon-tag',
                    text: Ifresco.helper.Translations.trans('Taggable'),
                    group: 'quickAspects',
                    handler: function() {
                        this.quickAddAspect(this.nodeId, "cm:taggable");
                    },
                    scope: this
                }, {
                    iconCls:'ifresco-icon-attach',
                    text: Ifresco.helper.Translations.trans('Versionable'),
                    group: 'quickAspects',
                    handler: function() {
                        this.quickAddAspect(this.nodeId, "cm:versionable");
                    },
                    scope: this
                }, {
                    iconCls:'ifresco-icon-tag-green',
                    text: Ifresco.helper.Translations.trans('Classifiable'),
                    group: 'quickAspects',
                    handler: function() {
                        this.quickAddAspect(this.nodeId, "cm:generalclassifiable");
                    },
                    scope: this
                }]
            }
        });

        
        items.push({
            iconCls: 'ifresco-download-node-button',
            text: Ifresco.helper.Translations.trans('Download'),
            hidden: this.isFolder || this.isMultiple,
            handler: function() {
            	Ifresco.app.getController("Index").openWindow(Routing.generate('ifresco_client_node_actions_download', {nodeId: this.nodeId}));
            },
            scope:this
        });
        
        items.push({ // SINGLE & MULTIPLE
            iconCls: 'ifresco-download-node-button',
            text: Ifresco.helper.Translations.trans('Download ZIP'),
            disabled: !zipArchiveExistsGen,
            handler: function() {
            	var nodes = [];
            	if (this.isMultiple) {
            		for (var i=0; i < this.records.length; ++i) {
            			nodes.push(this.records[i].nodeId);
                    }
            	}
            	else
            		nodes.push(this.nodeId);
            	
            	Ifresco.app.getController("Index").openWindow(Routing.generate('ifresco_client_node_actions_nodes_download') + '?nodes=' + Ext.encode(nodes));
            },
            scope:this
        });

        items.push('-');

        if (!this.isMultiple && Ifresco.helper.Settings.get('shareEnabled') == 'true') { // TODO - LATER - change name in grid add or remove shared.png - ???? - meaning: icon remove in name
        	var sharedId = this.record.raw.alfresco_sharedId;
            var isShared = !Ext.isEmpty(sharedId);
	        items.push({
	            iconCls:'ifresco-share-doc', // TODO add icon to css
	            text: (isShared ? Ifresco.helper.Translations.trans('Share file info') : Ifresco.helper.Translations.trans('Share file')),
	            hidden: this.isFolder || this.isMultiple,
	            listeners: {
	                click: function() {
	                    var shareMenu = this;
	                    Ext.Ajax.request({
	                    	url : Routing.generate('ifresco_client_node_actions_share_doc') + '?nodeId='+self.nodeId,
	                        loadMask: true,
	                        disableCaching: true,
	                        success: function (response) {
	                        	var data = Ext.decode(response.responseText);
	                        	var sharedBy = data.sharedBy || '';
	                            sharedId = data.sharedId || '';
	                            console.log("DATA ",data,sharedBy,sharedId)
	
	                            self.record.raw.alfresco_sharedId = self.record.raw.qshare_sharedId = sharedId;
	                            self.record.raw.qshare_sharedBy = sharedBy
	
	                            self.record.set('qshare_sharedId', sharedId);
	                            self.record.set('qshare_sharedBy', sharedBy);
	                            self.record.commit();
	
	                            shareMenu.setText(Ifresco.helper.Translations.trans('Share file info'));
	
	                            isShared = true;
	                            
	                            setTimeout(function(){
	                                self.show();
	                            	
	                                shareMenu.fireEvent('afterrender', shareMenu);
	                            }, 10)
	                        }
	                    });	
	                },
	                afterrender: function(t, e) {
	                    if(isShared) {
	                        this.setMenu(this.shareMenu);
	                        var shareMenuEl = this;
	                        var docShareUrl = Routing.generate('ifresco_client_viewer_view_share', {shareId: sharedId});
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
	                        	Ext.Ajax.request({
	                        		url : Routing.generate('ifresco_client_node_actions_unshare_doc') + '?nodeId='+self.nodeId,
	    	                        loadMask: true,
	    	                        disableCaching: true,
	    	                        success: function (response) {
	    	                        	var data = Ext.decode(response.responseText);
	    	                        	self.record.raw.alfresco_sharedId
		                                        = self.record.raw.qshare_sharedBy
		                                        = self.record.raw.qshare_sharedId
		                                        = null;
		
	    	                        	self.record.set('qshare_sharedId', null);
	    	                        	self.record.set('qshare_sharedBy', null);
	    	                        	self.record.commit();
		                                shareMenuEl.setMenu(null);
		                                
		                                shareMenuEl.setText(Ifresco.helper.Translations.trans('Share file'));
		
		                                isShared = false;
	    	                        }
	    	                    });	
	                        });
	                    }
	                    else {
	
	                    }
	                }
	            },
	            shareMenu: {
	                items:[{
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
	                                    fieldLabel: Ifresco.helper.Translations.trans('Public Link'),
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
	                                    text: Ifresco.helper.Translations.trans('Share with'),
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
	        });
	        
	        if (!this.isFolder)
	        	items.push('-');
	    }
        

        items.push({ // SINGLE & MULTIPLE
            iconCls: 'ifresco-icon-cancel',
            text: Ifresco.helper.Translations.trans('Delete'),
            hidden: this.nodeId == 'root' || this.isClipboard,
            disabled: !this.delRights,
            handler:function() {
            	if (this.isMultiple) {
            		this.fireEvent('deleteNodes', this.records, this.fromComponent);
            	}
            	else {
            		this.fireEvent('deleteNode', this.record, this.nodeId, this.DocName, nodeType, this.fromComponent);
            	}
            },
            scope: this
        });

        if (this.isClipboard) {
	        items.push({ // SINGLE & MULTIPLE
	            iconCls: 'ifresco-icon-cancel',
	            text: Ifresco.helper.Translations.trans('Remove from Clipboard'),
	            handler:function() {
	            	var nodes = [];
	            	if (this.isMultiple) {    		
	                    Ext.each(this.records, function (record) {
	                        nodes.push(record.get('nodeId'));
	                    }, this);
	            	}
	            	else {
	            		 nodes.push(this.record.get('nodeId'));
	            	}
	            	
	            	this.fireEvent('removeFromClipboard', nodes);
	            },
	            scope: this
	        });
        }

        
        items.push('-');

        
        if (!this.isFolder && !this.isMultiple) {
	        // TODO checkin / checkout
	        
	        items.push({
                iconCls:(isWorkingCopy === true || isCheckedOut === true ? 'ifresco-checkin-node-button' : 'ifresco-checkout-node-button'), // TODO add icon to css
                text:(isWorkingCopy === true || isCheckedOut === true ? Ifresco.helper.Translations.trans('Checkin') : Ifresco.helper.Translations.trans('Checkout')),
                disabled:(this.isFolder === true || isLink === true ? true : false) || !editRights,
                handler: function(){
                	var tempid = this.nodeId;
                    if (isWorkingCopy === true || isCheckedOut === true) {
                        if (isCheckedOut === true && workingCopyId.length > 0) {
                            tempid = workingCopyId;
                        }
                        console.log("FIRE CHECKIN",tempid,this.nodeId,workingCopyId,isWorkingCopy,isCheckedOut)
                        this.fireEvent('checkIn', tempid, null);
                        //checkIn<?php echo $containerName; ?>(tempid,MimeType);
                        // TODO correct logic
                    }
                    else {
                    	console.log("FIRE CHECKOUT",tempid,this.nodeId,workingCopyId,isWorkingCopy,isCheckedOut)
                    	this.fireEvent('checkOut', tempid, null);
                        //checkOut<?php echo $containerName; ?>(nodeId,MimeType);
                    	// TODO correct logic
                    }
                },
                scope: this
            },{
                iconCls:'ifresco-cancel-checkout-button', // TODO add icon to css
                text:Ifresco.helper.Translations.trans('Cancel Checkout'),
                hidden:(isWorkingCopy === true || isCheckedOut === true || this.isFolder === true || isLink === true ? false : true),
                disabled:(this.isFolder === true || isLink === true ? true : false),
                handler: function(){
                    if (isWorkingCopy === true || isCheckedOut === true) {
                        var tempid = this.nodeId;
                        if (isCheckedOut === true && workingCopyId.length > 0) {
                            tempid = workingCopyId;
                        }
                        console.log("FIRE CANCEL CHECKOUT",tempid,this.nodeId,workingCopyId,isWorkingCopy,isCheckedOut)
                        this.fireEvent('cancelCheckout', tempid, null);
                        //cancelCheckout<?php echo $containerName; ?>(tempid,originalId,MimeType);
                     // TODO correct logic
                    }
                },
                scope: this
            });
	        
	        items.push('-');
        }
        
        items.push({ // SINGLE & MULTIPLE
            iconCls: 'ifresco-icon-star',
            text: Ifresco.helper.Translations.trans('Add to favorites'),
            handler: function() {
                this.fireEvent('addFavorite', this.nodeId, this.DocName, 'folder');
            },
            scope: this
        });

        items.push('-');

        if (!this.isFolder) {
	        items.push({ // SINGLE & MULTIPLE
	            iconCls: 'ifresco-icon-email',
	            text: Ifresco.helper.Translations.trans('Send as email'),
	            disabled:(isLink || deletedSource),
	            scope: this,
	            handler: function(){
	            	var mailNodes = [];
	            	if (this.isMultiple) {
	            		for (var i=0; i < this.records.length; ++i) {
	                         if (this.records[i].shortType === "file" && this.records[i].mime === "application/pdf")
	                        	 mailNodes.push({nodeId: this.records[i].nodeId, nodeName: this.records[i].nodeName, docName: this.records[i].nodeName, shortType: this.records[i].shortType});
	                     }
	            	}
	            	else
	            		mailNodes = [{nodeId:this.nodeId,nodeName:nodeName,docName:nodeName,shortType:'file'}];
	            	
	            	this.fireEvent('sendMail', mailNodes);
	            }
	        });
        }
        
        items.push({ // SINGLE & MULTIPLE
            iconCls: 'ifresco-icon-email',
            text: Ifresco.helper.Translations.trans('Send as Email link'),
            scope: this,
            handler: function(){
                var body = [];
                var urlOf = '';
                var mailNodes = [];
                var startUrl = Routing.generate('ifresco_client_index', {}, true);
            	if (this.isMultiple) {
            		for (var i=0; i < this.records.length; ++i) {
            			urlOf = '';
                         if (this.records[i].shortType === "file")
                        	 urlOf = startUrl + "#document/workspace://SpacesStore/" + this.records[i].nodeId;
                         else
                        	 urlOf = startUrl +"#folder/workspace://SpacesStore/" + this.records[i].nodeId;
                         body.push(urlOf);
                    }
            		
            	}
            	else {
	                if(this.type == "{http://www.alfresco.org/model/content/1.0}folder") {
	                    urlOf = startUrl +"#folder/workspace://SpacesStore/" + this.nodeId;
	                } else {
	                    urlOf = startUrl + "#document/workspace://SpacesStore/" + this.nodeId;
	                }
	                body.push(urlOf);
            	}
                body = body.join('%0d%0a');
                document.location.href = 'mailto:?body=' + body;
            }
        });

        if (Ifresco.helper.Settings.get('openInAlfresco') || Ifresco.helper.Settings.isAdmin) {
            items.push('-');
            items.push({
                iconCls:'ifresco-open-alfresco-button',
                text: Ifresco.helper.Translations.trans('Open in Alfresco'),
                hidden: this.isMultiple,
                handler: function(){
                    var folderPath = this.folder_path;
                    Ext.Ajax.request({
                        method: 'GET',
                        url: Routing.generate('ifresco_client_shared_settings_get'),
                        success: function (response) {
                            var data = Ext.decode(response.responseText);
                            if (self.isFolder)
                            	Ifresco.app.getController("Index").openWindow(data.ShareFolder + folderPath);
                            else
                            	Ifresco.app.getController("Index").openWindow(data.ShareUrl + self.nodeId);
                        },
                        failure: function () {}
                    });
                },
                scope: this
            });
        }
        
        if (Ifresco.helper.Settings.get('OCREnabled') == "true") { // TODO - recheck this
        	items.push('-');
        	items.push({
	            iconCls: 'ifresco-ocr-files', // TODO add icon to css
	            text: Ifresco.helper.Translations.trans('Transform'),
	            scope:this,
	            disabled: this.type == "{http://www.alfresco.org/model/content/1.0}folder",
	            handler: function(){
	            	// TODO add logic
	            }
	        });
        }

        if (this.isMultiple) {
        	items.push({
                iconCls: 'ifresco-pdf-merge', // TODO add icon to css
                text: Ifresco.helper.Translations.trans('PDF Merge'),
                disabled:!this.allPDF,
                scope:this,
                handler: function(){
                	// TODO implement function
                	 var nodes = [];
                     for (var i=0; i < this.records.length; ++i) {
                         if (this.records[i].shortType === "file" && this.records[i].mime === "application/pdf")
                             nodes.push({node: this.records[i].nodeId, alfresco_mimetype: this.records[i].mime});
                     }
console.log("MENU nodes",nodes,this.records)
                     if (nodes.length > 0) {
                         Ifresco.app.getController("Grid").pdfMerge(nodes);
                     }
                }
            });
        }

        return items;
    },

    quickAddAspect: function (nodeId, aspect) {
        Ext.Ajax.request({
            method: 'POST',
            url: Routing.generate('ifresco_client_metadata_aspects_save'),
            params: {
                nodeId: nodeId,
                selectedAspects: Ext.encode([aspect]),
                aspects: Ext.encode([])
            },
            success: function() {
                Ext.MessageBox.alert(
                    Ifresco.helper.Translations.trans('Quick add aspect'),
                    Ifresco.helper.Translations.trans('Aspect added successfully.')
                );
            },
            failure: function() {}
        });
    }
});
