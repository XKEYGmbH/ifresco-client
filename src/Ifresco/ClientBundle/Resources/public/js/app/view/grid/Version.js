Ext.define('Ifresco.view.grid.Version', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.ifrescoViewGridVersion',
    cls: 'ifresco-view-grid-version',
    requires: ['Ifresco.store.Version'],
    header: false,
    multiSelect: false,
    deferEmptyText: false,
    configData: null,
    columns: null,
    menu: null,
    nodeId: null,
    border: 0,
//    emptyText: '<span style="font-size:12px;"><img src="/images/icons/information.png" align="absmiddle"> Ifresco.helper.Translations.trans('This document has no version history.')</span>',

    initComponent: function () {
    	var store;
    	if (this.nodeId != null) {
    		store = Ifresco.store.Version.create({params: {
                'nodeId': this.nodeId
            }});
    	}
    	else
    		store = Ifresco.store.Version.create({});
    	
    	if (this.columns == null) {
	        this.columns = [{
	                header: Ifresco.helper.Translations.trans('Version'),
	                dataIndex: 'version'
	            },{
	                header: Ifresco.helper.Translations.trans('Note'),
	                dataIndex: 'description'
	            },{
	                header: Ifresco.helper.Translations.trans('Date'),
	                xtype: 'datecolumn',
	                format: this.configData.DateFormat + ' ' + this.configData.TimeFormat,
	                dataIndex: 'date'
	            },{
	                header: Ifresco.helper.Translations.trans('Author'),
	                dataIndex: 'author'
	        }]
        }
    	
        Ext.apply(this, {
            loadingText: Ifresco.helper.Translations.trans('Loading...'),
            emptyText: Ifresco.helper.Translations.trans('This document has no version history.'),
            store: store,
            columns: this.columns
        });
        
        

        this.callParent();
    },
    listeners: {
    	beforeitemcontextmenu: function (grid, record, item, index, e, eOpts) {
            this.openContextMenu(grid, record, item, index, e, eOpts);
        },
        itemdblclick: function(grid, record) {
        	console.log("dbl click titem")
        	this.fireEvent('download', record.data.nodeId);
        },
        selectionchange: function(t, selected, eOpts) {
        	this.up("ifrescoViewVersionsTab").down("[iconCls~=ifresco-icon-revert]").setDisabled(!selected.length);
        	this.up("ifrescoViewVersionsTab").down("[iconCls~=ifresco-icon-download]").setDisabled(!selected.length);
//            Ext.getCmp('download-selected-version').setDisabled(!selected.length);
//
//            var selectedItem = Ext.getCmp('dataGrid').getSelectionModel().getSelection()[0] || false;
//
//            var editPerm = false;
//            if(selectedItem)
//            {
//                editPerm = selectedItem.data.alfresco_perm_edit;
//            }
//
//            Ext.getCmp('revert-selected-version').setDisabled(!selected.length || !editPerm);
        }
    },
    
    openContextMenu: function (grid, record, item, index, e, eOpts) {
    	var data = {
    		nodeId: record.data.nodeRef,
    		version: record.data.version,
    		versionNodeId: record.data.nodeId
        }
    	
        this.menu = Ifresco.menu.Version.create(data);
    	e.stopEvent();
        this.menu.showAt(e.getXY());
    }
});