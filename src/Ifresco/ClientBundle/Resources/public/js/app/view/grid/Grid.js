Ext.define('Ifresco.view.grid.Grid', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.ifrescoViewGridGrid',
    cls: 'ifresco-view-grid',
    requires: ['Ifresco.store.Grid'],
    layout:'fit',
    deferEmptyText: false,
    multiSelect: true,
    collapsible: false, // maybe this fix that the toolbar don't hide
    animCollapse: false, // maybe this fix that the toolbar don't hide
    header: false,
    configData: null,
    localConfigData: null,
    
    isDblClick: false,
    stateId :'documentGrid-stateid-' + this.itemId,
    stateful : true,
    uploadPanel: null,
    winSpace: null,
    menu: null,
    currentNodeId:null,
    html5Connector: null,
    hoverPanel: null,
    //ddGroup: 'ifrescoDocuments',
    //isTarget : true,
    imgStamp: null,
    initComponent: function () {
    	this.imgStamp = new Date().getTime();
    	console.log("IMAGE STAMP",this.imgStamp)
    	console.log("GRID CONFIG DATA",this.localConfigData,this.configData);
        var contentPanel = this.up('ifrescoCenter');
        var store = Ifresco.store.Grid.create({configData: this.configData});
        Ext.apply(this, {
        	viewConfig: {
                forceFit: true,
                stripeRows: true,
                chunker: Ext.view.TableChunker,
                
                plugins: {
                    ptype: 'gridviewdragdrop',
                    dragGroup: 'ifrescoDocuments',
                    dropGroup: 'ifrescoGridDrop',
                    enableDrop: false
                },
        		listeners: {
        			render: this.createTooltip,
        			scope: this
        		}
            },
            
        	stateId: 'columnsetid-'+this.localConfigData.currentColumnSetId,
//            emptyText: '<img src="/images/icons/information.png" align="absmiddle">' + Ifresco.helper.Translations.trans('No items to display.'),
            emptyText: Ifresco.helper.Translations.trans('No items to display.'),
//            loadMask: new Ext.LoadMask(this, {msg: Ifresco.helper.Translations.trans('Please wait...')}),
            store: store,
            dockedItems: [{
                    xtype: 'toolbar',
                    dock: 'top',
                    cls: 'ifresco-view-grid-toolbar-breadcrumb',
                    items: [],
                    border: 0,
                    hidden: true
                },{
                    xtype: 'toolbar',
                    dock: 'top',
                    cls: 'ifresco-view-grid-toolbar-buttons',
                    items: this.createCurrentToolBar()
            }],
            bbar: this.createCurrentBottomBar(store),
            columns: this.configData.columns,
            buttonsConfig: {
                search: [
                    'ifresco-icon-refresh',
                    'ifresco-column-sets-button',
                    'ifresco-icon-print',
                    'ifresco-export-csv-button',
                    'ifresco-export-pdf-button'
                ],
                clipboard: [
                    'ifresco-icon-refresh', 
                    'ifresco-column-sets-button', 
                    'ifresco-icon-print', 
                    'ifresco-remove-clipboard-button',
                    'ifresco-export-csv-button', 
                    'ifresco-export-pdf-button'
                ]
            },
            features: [
                Ext.create("Ext.grid.feature.Grouping", {}),
                Ext.create('Ext.ux.grid.feature.Tileview', {
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
                })
            ],
            listeners: {
                beforeitemcontextmenu: function (grid, record, item, index, e, eOpts) {
                    //this.openContextMenu(grid, record, item, index, e, eOpts);
                	this.menu = this.fireEvent('openContextMenu', this, this.configData, grid, record, item, index, e, eOpts);
                },
                select: function (gridView, record) {
                    this.fireEvent('loadDataBySelect', this, record);
                },
                itemdblclick: function (grid, record, item, index, e, eOpts) {
                    //this.loadDataByDoubleClick(grid, record, item, index, e, eOpts );
                	this.fireEvent('loadDataByDoubleClick', this, this.configData, grid, record, item, index, e, eOpts );
                },
                dragenter: function (e, target, conf) {
                    this.uploadPanel = this.createUploadPanel();
                    this.uploadPanel.show();
                },
                afterRender: function() {
                    this.html5Connector = Ext.create('Ifresco.listener.Html5Connector', {
                        el: this.body,
                        listeners:{
                            scope: this
                        }
                    });

                    this.relayEvents(this.html5Connector,["dragenter", "dragstart", "dragstop"]);
                }
            },
            scope: this
        });
        
        
        this.callParent();
    },
    tooltipTipEl: null,
    createTooltip: function(view) {
    	var self = this;
    	if (Ifresco.helper.Settings.get('thumbnailHover') == 'true') {
    		// TODO - remove for folder
	    	console.log("TOOLTIP CREATED");
	        view.tip = Ext.create('Ext.tip.ToolTip', {
	            // The overall target element.
	            target: view.el,
	            // Each grid row causes its own seperate show and hide.
	            delegate: view.itemSelector,
	            // Moving within the row should not hide the tip.
	            trackMouse: true,
	            //html: '',
	            items:[{
	            	xtype: 'image',
	            	src: '',
	            	maxWidth: 300,
	            	listeners : {
            	       load : {
            	           element : 'el',  //the rendered img element
            	           fn : function() {
            	        	   if (self.tooltipTipEl != null) {
            	        		   console.log("found tip",self.tooltipTipEl)
	            	        	   self.tooltipTipEl.update(),
	            	        	   self.tooltipTipEl.setHeight('auto');
            	        	   }
            	           }
            	         }
            	     }
	            }],
	            minHeight: 200,
	            
	            // Render immediately so that tip.body can be referenced prior to the first show.
	            renderTo: Ext.getBody(),
	            showDelay: 1200,
	            listeners: {
	                // Change content dynamically depending on which element triggered the show.
	                beforeshow: function (tip) {
	                	var record = view.getRecord(tip.triggerElement),
	                		thumb = record.get("alfresco_thumbnail");
	                	
	                	self.tooltipTipEl = tip;
	                	console.log("tooltip recored",record);
	                	//tip.update('<img src="'+thumb+'">');
	                	tip.down("image").setSrc(thumb+"&_dc="+self.imgStamp);

	                    /*var tooltip = view.getRecord(tip.triggerElement).get('tooltip');
	                    if(tooltip){
	                        tip.update(tooltip);
	                    } else {
	                         tip.on('show', function(){
	                             Ext.defer(tip.hide, 10, tip);
	                         }, tip, {single: true});
	                    }*/
	                }
	            }
	        });
    	}
    },
    
    createCurrentToolBar: function () {
        var tBar = [{
            iconCls:'ifresco-icon-upload',
            tooltip: Ifresco.helper.Translations.trans('Upload File(s)'),
            handler: function(){
                this.uploadPanel = this.createUploadPanel();
                this.uploadPanel.show();
            },
            scope: this
        },{
            iconCls:'ifresco-create-folder-button',
            tooltip: Ifresco.helper.Translations.trans('Create Space'),
            handler: function(){
                var window = Ifresco.view.window.CreateSpace.create({
                    nodeId: this.localConfigData.nodeId,
                    parent: this
                });
                window.show();
            },
            scope: this
        },{
            iconCls:'ifresco-create-html-button',
            tooltip: Ifresco.helper.Translations.trans('Create HTML'),
            handler: function(){
                var window = Ifresco.view.window.CreateHtmlDocument.create({
                    nodeId: this.localConfigData.nodeId,
                    parent: this
                });
                window.show();
            },
            scope: this
        }];

        if (Ifresco.helper.Settings.get('scanViaSane') == 'true') {
            tBar.push({
                iconCls:'ifresco-icon-scan',
                tooltip:Ifresco.helper.Translations.trans('Scan Document'),
                xtype: 'ifrescoButtonSane',
                handler: function(btn) {
                	var toolbar = this.down("toolbar[cls~=ifresco-view-grid-toolbar-breadcrumb]"),
                		nodeId = this.up("ifrescoContentTab").localConfigData.nodeId;
                		folder = "";
                	
                	
                	if(toolbar) {
                		var tLen = toolbar.items.length;
                		
                		folder = tLen > 0 ? toolbar.items.getAt(tLen-1).text : '';
                	}

                	btn.nodeId = nodeId;
                	btn.folder = folder;
                	btn.scannerForm();
                },
                scope: this
            });
        }

        tBar.push('-');

        if (this.configData.columnSets != null) {
            var isDisabled = false;
            var menu = this.getColumnSetsMenu();
        } else {
            var isDisabled = true;
            var menu = new Ext.menu.Menu.create({});
        }
console.log("COLUMNSET NAME IS",this.configData.columnSetName);
        tBar.push({
            tooltip: Ifresco.helper.Translations.trans('Load Column Set'),
            iconCls:'ifresco-column-sets-button',
            text: this.configData.columnSetName,
            disabled: isDisabled,
            menu: menu
        });

        if (Ifresco.helper.Settings.get('CSVExport') && Ifresco.helper.Settings.get('PDFExport')) {
            tBar.push('-');
        }

        if (Ifresco.helper.Settings.get('CSVExport')) {
            tBar.push({
                iconCls: 'ifresco-export-csv-button',
                tooltip:Ifresco.helper.Translations.trans('Export CSV'),
                handler: function(){
                	var contentTab = this.up('panel[cls~=ifresco-view-content-tab]');
                	var params = contentTab.localConfigData.lastParams;
                	this.fireEvent('csvExport', this, params);
                },
                scope: this
            });
        }

        if (Ifresco.helper.Settings.get('PDFExport')) {
            tBar.push({
                iconCls: 'ifresco-export-pdf-button',
                tooltip: Ifresco.helper.Translations.trans('Export PDF'),
                handler: function(){
                	var contentTab = this.up('panel[cls~=ifresco-view-content-tab]');
                	var params = contentTab.localConfigData.lastParams;
                	this.fireEvent('pdfExport', this, params);
                },
                scope: this
            });
        }

        tBar.push('-');

        if (!this.configData.isClipBoard) {
            tBar.push({
                iconCls:'ifresco-copy-clipboard-button',
                tooltip: Ifresco.helper.Translations.trans('Paste clipboard (copy)'),
                handler: function(){
                	var self = this;
                	this.fireEvent('pasteClipboard', 'copy', this.localConfigData.nodeId, function() {
                		self.getStore().reload();
                	});
                	
                },
                scope: this
            });

            tBar.push({
                iconCls:'ifresco-cut-clipboard-button',
                tooltip: Ifresco.helper.Translations.trans('Paste clipboard (cut)'),
                handler: function(){
                	var self = this;
                	this.fireEvent('pasteClipboard', 'cut', this.localConfigData.nodeId, function() {
                		self.getStore().reload();
                	});
                },
                scope: this
            });

            tBar.push({
                iconCls:'ifresco-link-clipboard-button',
                tooltip: Ifresco.helper.Translations.trans('Paste clipboard as (link)'),
                handler: function(){
                	var self = this;
                	this.fireEvent('pasteClipboard', 'link', this.localConfigData.nodeId, function() {
                		self.getStore().reload();
                	});
                },
                scope: this
            });
        } else {
            tBar.push({
                iconCls:'ifresco-remove-clipboard-button',
                tooltip: Ifresco.helper.Translations.trans('Clear Clipboard'),
                handler: function(){
                    /*var records = this.getSelectionModel().getSelection();
                    var nodes = [];
                    Ext.each(records, function (record) {
                        nodes.push(record.get('nodeId'));
                    }, this);
                    this.fireEvent('removeFromClipboard', nodes);*/
                	this.fireEvent('clearClipboard');
                },
                scope: this
            });
        }

        tBar.push('-');

        tBar.push({
            iconCls:'ifresco-icon-refresh',
            tooltip: Ifresco.helper.Translations.trans('Refresh'),
            handler: function(){
            	this.getStore().reload();
            	
            },
            scope: this
        });

        tBar.push('-');

        tBar.push({
            tooltip: Ifresco.helper.Translations.trans('Print'),
            iconCls: 'ifresco-icon-print',
            handler : function(){
            	Ext.ux.grid.Printer.mainTitle = Ifresco.helper.Translations.trans('Printout');
                Ext.ux.grid.Printer.stylesheetPath = printerCss;
                Ext.ux.grid.Printer.printAutomatically = false;
                Ext.ux.grid.Printer.print(this.up("grid"));
            }
        });

        if (Ifresco.helper.Settings.get('openInAlfresco') == "true" || Ifresco.helper.Settings.isAdmin) {
            tBar.push({
                iconCls:'ifresco-open-alfresco-button',
                tooltip: Ifresco.helper.Translations.trans('Open Folder in Alfresco'),
                handler: function(){
                	
                	Ifresco.app.getController("Index").openWindow(this.up('panel[cls~=ifresco-view-content-tab]').localConfigData.ShareSpaceUrl + this.up('panel[cls~=ifresco-view-content-tab]').localConfigData.FolderPath);
                },
                scope: this
            });
        }

        tBar.push('->');

        tBar.push({
            xtype: 'switchbuttonsegment',
            activeItem: 0,
            scope: this,
            items: [{
                tooltip: Ifresco.helper.Translations.trans('Details'),
                viewMode: 'default',
                iconCls: 'icon-default'
            }, {
                tooltip: Ifresco.helper.Translations.trans('Tiles'),
                viewMode: 'tileIcons',
                iconCls: 'icon-tile'
            }, {
                tooltip: Ifresco.helper.Translations.trans('Icons'),
                viewMode: 'mediumIcons',
                iconCls: 'icon-medium'
            }],
            listeners: {
                change: function(btn, item) {
                    console.log('Tile changes');
                    this.features[1].setView(btn.viewMode);
                },
                scope: this
            }
        });

        return tBar;
    },

    createCurrentBottomBar: function (store) {
    	var self = this;
        return [{
            xtype: 'pagingtoolbar',
            border: 0,
            store: store,
            displayInfo: true,
            displayMsg: '{0} - {1} ' + Ifresco.helper.Translations.trans('of') + ' {2}',
            emptyMsg: "",
            listeners: {
                beforechange: function (toolbar, pageData) {
                	console.log("BEFORE CHANGE PAGINGTOOLBAR");
                	var contentTab = self.up('panel[cls~=ifresco-view-content-tab]');
                	var params = contentTab.localConfigData.lastParams;
                	
                	Ext.apply(params.params, {
                        //nodeId: self.localConfigData.nodeId,
                        columnSetId: Ifresco.helper.Registry.get('ColumnsetId')
                    });
                	console.log("PARAMS are ",params);
                    //var newbaseParams = lastParams.params;
                    Ext.apply(store.baseParams, params);
                    Ext.apply(store.getProxy().extraParams, params.params);
                    
                },
                change: function (toolbar, pageData) {
                    if (pageData && pageData.total <= this.pageSize && pageData.pages < 2) {
                        store.remoteSort = !this.configData.DefaultSort || this.configData.DefaultSort == null ? false : true;
                    } else {
                        store.remoteSort = true;
                    }
                }
            }
        }];
    },

    createUploadPanel: function () {
    	console.log("create upload panel");
        var url = Routing.generate('ifresco_client_upload_REST') + '?nodeId=' + this.localConfigData.nodeId + '&overwrite=false&ocr=false';
        var grid = this,
        	filters = Ext.decode(Ifresco.helper.Settings.get('uploadAllowedTypes'));
        
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
                url: url,
                uploadpath: url,
                filters : [{
                    title : Ifresco.helper.Translations.trans('General'),
                    extensions : filters.join(",")
                }]
            },
            listeners: {
                beforestart: function(uploader, files) {
                    uploader.uploader.settings.uploadpath = uploader.uploader.settings.url = url;
                },
                uploadcomplete: function(uploader, files) {
                	console.log("uploadcomplete")
                    var uploadPanel = this;
                    console.log(this);
                    uploadPanel.uploadComposed = false;
                    if(uploadPanel.autoCloseControl.getValue() && uploadPanel._dropboxFiles.length == 0) {
                        uploadPanel.hide();
                    }

                    if(uploadPanel._dropboxFiles.length == 0) {
                        grid.getStore().reload();
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
    },

    getColumnSetsMenu: function () {
        var items = [];
        var grid = this;

        Ext.each(this.configData.columnSets, function (columnSet) {
            items.push({
                text: columnSet.name,
                handler: function () {
                    this.loadNewColumns(columnSet.id);
                },
                scope: grid
            });
        });

        return Ext.menu.Menu.create({items: items});
    },

    loadNewColumns: function (columnId) {
    	var self = this;
        Ifresco.helper.Registry.set("ColumnsetId", columnId);
        Ifresco.helper.Registry.save();
        
        Ext.Ajax.request({
            loadMask: true,
            disableCaching: true,
            url: Routing.generate('ifresco_client_grid_get_column_set', {id : columnId}),
            params: {
            },
            success: function(response){
                var data = Ext.decode(response.responseText);
                if (data.success) {
                	self.stateId = "columnsetid-"+columnId;
                	self.configData.columns = data.columns;
                	self.configData.fields = data.fields;
                	console.log("RECONFIGURE COLUMNS", data.columns);
        	        self.reconfigure(self.store, data.columns); // undefined because we dont reconfigure store               	
        	        self.store.model.setFields(data.fields);
        	        var contentTab = self.up('panel[cls~=ifresco-view-content-tab]');
                	var params = contentTab.localConfigData.lastParams;
        	        Ext.apply(params.params, {
                        //nodeId: self.localConfigData.nodeId,
                        //columnSetId: Ifresco.helper.Registry.get('ColumnsetId')
        	        	columnSetId: columnId
                    });
        	        
        	        
        	        
        	        console.log("LOAD NEW COLUMNS WITH PARAMS",params);
        	        
                	self.up('ifrescoContentTab').reloadGridData(params);

                	self.down("button[iconCls~=ifresco-column-sets-button]").setText(data.name)
                }
            }
        });
        
        
    }
});

