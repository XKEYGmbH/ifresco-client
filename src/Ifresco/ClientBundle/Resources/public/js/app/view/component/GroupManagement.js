Ext.define('Ifresco.view.GroupManagement', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.ifrescoViewGroupManagement',
    border: 0,
    cls: 'ifresco-view-group-management',
    layout: 'fit',
    autoScroll: true,
    

    initComponent: function() {
        Ext.apply(this, {
            tbar: [{
            	xtype: 'textfield',
            	name: 'searchGroup'
            },{
            	iconCls: 'ifresco-icon-magnifier',
            	tooltip: Ifresco.helper.Translations.trans('Search'),
                handler: function() {
                    var filter = this.down("textfield[name~=searchGroup]").getValue();
                    this.getStore().load({
                    	params:{
                    		filter: filter
                    	}
                    });
                },
                scope: this 
            }],
            border: 0,
    	    layout:'fit',
            columns: [{
                text: Ifresco.helper.Translations.trans('Name'),
                dataIndex: 'displayName',
	            flex: 1
            },{
                text: Ifresco.helper.Translations.trans('Identification'),
                dataIndex: 'shortName',
	            flex: 1
            }],
            minColumnWidth: 150,
            store: Ifresco.store.Groups.create({}),
            listeners: {
            	itemdblclick: function (grid, record, item, index, e, eOpts) {
            		/*var window = Ifresco.view.window.PersonDetail.create({
                		title: Ifresco.helper.Translations.trans('User Profile') + ': '+record.get("firstName") + ' ' +record.get("lastName"),
                		parent: this
            		});
            		
            		window.show();
            		window.loadRecord(record);*/
                },
                scope:this
            }
        });

        this.callParent();
    }
});