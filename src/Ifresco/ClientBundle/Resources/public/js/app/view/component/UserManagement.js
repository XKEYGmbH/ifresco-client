Ext.define('Ifresco.view.UserManagement', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.ifrescoViewUserManagement',
    border: 0,
    cls: 'ifresco-view-user-management',
    layout: 'fit',
    autoScroll: true,
    

    initComponent: function() {
        Ext.apply(this, {
            tbar: [{
            	xtype: 'textfield',
            	name: 'searchUser'
            },{
            	iconCls: 'ifresco-icon-magnifier',
            	tooltip: Ifresco.helper.Translations.trans('Search'),
                handler: function() {
                    var filter = this.down("textfield[name~=searchUser]").getValue();
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
                dataIndex: 'firstName',
                renderer: function(value, p, r) {
                	return r.data['firstName'] + ' ' + r.data['lastName'];
	            },
	            flex: 3
            },{
                text: Ifresco.helper.Translations.trans('Username'),
                dataIndex: 'userName',
                flex: 2
            },{
                text: Ifresco.helper.Translations.trans('Jobtitle'),
                dataIndex: 'jobtitle',
                flex: 1
            },{
                text: Ifresco.helper.Translations.trans('Email'),
                dataIndex: 'email',
                flex: 1
            },{
                text: Ifresco.helper.Translations.trans('Usage'),
                dataIndex: 'sizeCurrent',
                flex: 1
            },{
                text: Ifresco.helper.Translations.trans('Quota'),
                dataIndex: 'quota',
                flex: 1,
                renderer: function(value, p, r) {
                	if (r.data["quota"] == -1)
                		return "";
                	else
                		return r.data["quota"];
	            },
            }],
            minColumnWidth: 150,
            store: Ifresco.store.Persons.create({}),
            listeners: {
            	itemdblclick: function (grid, record, item, index, e, eOpts) {
            		var window = Ifresco.view.window.PersonDetail.create({
                		title: Ifresco.helper.Translations.trans('User Profile') + ': '+record.get("firstName") + ' ' +record.get("lastName"),
                		parent: this,
                		userName: record.get("userName")
            		});
            		
            		window.show();
            		window.loadRecord(record);
                },
                scope:this
            }
        });

        this.callParent();
    }
});