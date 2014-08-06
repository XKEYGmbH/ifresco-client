Ext.define('Ifresco.view.grid.TrashCan', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.ifrescoViewGridTrashCan',
    cls: 'ifresco-view-grid-trashcan',
    header: false,
    multiSelect: true,
    deferEmptyText: false,
    configData: null,
    menu: null,
    border: 0,

    initComponent: function () {
    	console.log("TRASG GRIKD")
        Ext.apply(this, {
            loadingText: Ifresco.helper.Translations.trans('Loading...'),
            emptyText: Ifresco.helper.Translations.trans('No documents in the Trash can.'),
            store: Ifresco.store.TrashCan.create({}),
            tbar: this.createCurrentToolBar(),
            columns: [{
                header: Ifresco.helper.Translations.trans('Name'),
                dataIndex: 'name',
                renderer: function(value, p, r) {
                	return '<img src="'+r.data['icon'] + '" width=16> ' + r.data['name'];
	            },
                flex: 3
            },{
                header: Ifresco.helper.Translations.trans('Path'),
                dataIndex: 'displayPath',
                flex: 2
            },{
                header: Ifresco.helper.Translations.trans('Description'),
                dataIndex: 'description',
                flex: 2
            },{
                header: Ifresco.helper.Translations.trans('Archived by'),
                dataIndex: 'archivedBy',
                renderer: function(value, p, r) {
                	return r.data['firstName'] + ' ' + r.data['lastName'] + ' ('+ r.data['archivedBy'] +')';
	            },
	            flex: 1
            },{
                header: Ifresco.helper.Translations.trans('Archived at'),
                xtype: 'datecolumn',
                format: this.configData.DateFormat + ' ' + this.configData.TimeFormat,
                dataIndex: 'archivedDate',
                flex: 1
            }]
        });

        this.callParent();
        console.log("TRASG GRIKD DONE")
    },
    listeners: {
    	beforeitemcontextmenu: function (grid, record, item, index, e, eOpts) {
            this.openContextMenu(grid, record, item, index, e, eOpts);
        },
        itemdblclick: function(grid, record) {
        	var nodeId = record.get("nodeRef");
        	nodeId = nodeId.replace(/workspace:\/\/SpacesStore\//,"");
        	console.log("dbl click titem")
        	if (record.get("isContentType"))
        		this.fireEvent('openDocumentDetail', nodeId, record.get("name"));
        	else
        		this.fireEvent('openDocument', nodeId, record.get("name"));
        }
    },
    
    createCurrentToolBar: function () {
    	var tBar = [{
            iconCls:'ifresco-icon-refresh',
            tooltip: Ifresco.helper.Translations.trans('Refresh'),
            handler: function(){
            	this.getStore().reload();
            	
            },
            scope: this
        }];
    	return tBar;
    },
    
    openContextMenu: function (grid, record, item, index, e, eOpts) {
    	var data = {},
    		me = this,
    		selection = grid.getSelectionModel().getSelection();
    	if (selection.length > 1) {
            var selectedObjects = [];
            
            for (var i = 0; i < selection.length; i++) {
                var selected = selection[i];
                
                var nodeId = selected.get("nodeRef");
            		nodeId = nodeId.replace(/workspace:\/\/SpacesStore\//,"");
            		
        		if (selected.get("isContentType"))
                    var nodeType = "file";
                else
                    var nodeType = "folder";
            		
                selectedObjects.push({
                    nodeId:nodeId,
                    nodeName:selected.get("name"),
                    DocName:selected.get("name"),
                    nodeType: nodeType,
                    record: selected
                });
                
            }
            data = {
            	isMultiple: true,
                records: selectedObjects,
                fromComponent: me
            }
            console.log("TRASHCAN MULTIPLE CONTEXT",data)
    	}
    	else {
    		var nodeId = record.get("nodeRef");
        	nodeId = nodeId.replace(/workspace:\/\/SpacesStore\//,"");
        	
        	if (record.get("isContentType"))
                var nodeType = "file";
            else
                var nodeType = "folder";
        	
    		data = {
                nodeId: nodeId,
                isMultiple: false,
                nodeName:record.get("name"),
                DocName:record.get("name"),
                nodeType: nodeType,
                record: record,
                fromComponent: me
            }
    	}

    	this.menu = Ifresco.menu.TrashCan.create(data);
    	e.stopEvent();
        this.menu.showAt(e.getXY());
    }
});