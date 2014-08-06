Ext.define('Ifresco.controller.Clipboard', {
    extend: 'Ext.app.Controller',
    refs: [{
        selector: 'viewport > ifrescoCenter',
        ref: 'tabPanel'
    }, {
        selector: 'ifrescoCenter > #ifrescoClipboard',
        ref: 'clipboard'
    }],
    
    init: function() {
        Ifresco.helper.Registry.set('clipboardItems', []);
        this.control({
            // 'ifrescoViewGridGrid': {
            //     treefolderclick: this.treeFolderClick,
            //     treefoldercontextmenu: this.treeFolderContextMenu
            // }
            'ifrescoNorth': {
                loadClipboard: this.loadClipboard
            },
            'ifrescoViewGridGrid': {
                removeFromClipboard: this.removeItem,
                pasteClipboard: this.pasteClipboard,
                clearClipboard: this.clearClipboard
            },
            'ifrescoMenu': {
                addToClipboard: this.addItem,
                removeFromClipboard: this.removeItem
            }
        });
    },
    
    pasteClipboard: function(type,nodeId,callback) {
    	var self = this;
    	console.log("PASTE CLIPBOARD TRIGGERED",type,nodeId);
    	var clipboardItems = Ifresco.helper.Registry.get('clipboardItems');
    	
    	if (clipboardItems.length > 0) {
    		Ext.Ajax.request({
                url: Routing.generate('ifresco_client_grid_paste_clipboard'),
                method: 'POST',
                params: {
                	clipboardItems: Ext.encode(clipboardItems),
                	actionType : type,
                	destNodeId: nodeId
                },
                success: function (res) {
                    var jsonData = Ext.decode(res.responseText);
                    var totalCount = jsonData.totalResults;
                    var successCount = jsonData.successCount;
                    var failureCount = jsonData.failureCount;
                    if (jsonData.success == true) {
	                    Ext.MessageBox.show({
	                        title: Ifresco.helper.Translations.trans('Successfully pasted!'),
	                        msg: Ifresco.helper.Translations.trans('Successfully pasted') +' ' +successCount+' '+Ifresco.helper.Translations.trans('node(s)')+'!',
	                        buttons: Ext.MessageBox.OK,
	                        icon: Ext.MessageBox.INFO
	                    });
	                    
	                    if (type=="cut") {
                            self.clearClipboard();
                        }
                    }
                    else {
                        Ext.MessageBox.show({
                            title: Ifresco.helper.Translations.trans('Pasting was not successful!'),
                            msg: successCount+' '+Ifresco.helper.Translations.trans('of')+' '+totalCount+' '+Ifresco.helper.Translations.trans('node(s) pasted to the destination folder')+'!',
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.WARNING
                        });
                    }
                    
                    if (typeof callback != 'undefined') { // TODO - REFRESH GRID!
                    	callback(jsonData);
                    }
                }
            });
    		
        }
    },

    loadClipboard: function () {
        console.log('loadClipboard');
        var clipboardTab = this.getClipboard();
        var tabPanel = this.getTabPanel();
        var columnSetId = Ifresco.helper.Settings.get('columnSetId');
        var clipboardItems = Ifresco.helper.Registry.get('clipboardItems');

        if (clipboardTab) {
                clipboardTab.reloadGridData({params: {
                    columnSetId: columnSetId,
                    clipboard: true,
                    clipboarditems: Ext.encode(clipboardItems)
                }});
                tabPanel.setActiveTab(clipboardTab);
                return;
        }

        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_data_grid_index'),
            params: {
                columnsetid: Ifresco.helper.Registry.get('ColumnsetId'),
                clipboard: true
            },
            success: function (res) {
                clipboardTab = Ifresco.view.ContentTab.create({
                    title: Ifresco.helper.Translations.trans('Clipboard'),
                    itemId: 'ifrescoClipboard',
                    configData: Ext.decode(res.responseText)
                });
                tabPanel.add(clipboardTab);
                tabPanel.setActiveTab(clipboardTab);
                clipboardTab.reloadGridData({params: {
                    columnSetId: columnSetId,
                    clipboard: true,
                    clipboarditems: Ext.encode(clipboardItems)
                }});
            }
        });
    },

    addItem: function (nodes) {
    	console.log("add item ",nodes);
        var clipboardItems = Ifresco.helper.Registry.get('clipboardItems');
        if (Ext.isArray(nodes)) {
            Ext.each(nodes, function (node) {
                Ext.Array.include(clipboardItems, node);
            });
        } else if (Ext.isString(nodes)) {
            Ext.Array.include(clipboardItems, nodes);
        }
        Ifresco.helper.Registry.set('clipboardItems', clipboardItems);  	
        this.loadClipboard();
    },

    removeItem: function (nodes) {
        var clipboardItems = Ifresco.helper.Registry.get('clipboardItems');
        if (Ext.isArray(nodes)) {
            Ext.each(nodes, function (node) {
                Ext.Array.remove(clipboardItems, node);
            });
        } else if (Ext.isString(nodes)) {
            Ext.Array.remove(clipboardItems, nodes);
        }
        Ifresco.helper.Registry.set('clipboardItems', clipboardItems);  	
        this.loadClipboard();
    },
    
    clearClipboard: function() {
    	Ifresco.helper.Registry.set('clipboardItems', []);  	
    	this.loadClipboard();
    }
});