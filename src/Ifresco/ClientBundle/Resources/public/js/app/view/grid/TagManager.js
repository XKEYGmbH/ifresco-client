Ext.define('Ifresco.view.grid.TagManager', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.ifrescoViewGridTagManager',
    cls: 'ifresco-view-grid-tagmanager',
    multiSelect: true,
    border: 0,
    viewConfig: {
        forceFit:true,
        autoHeight: true
    },
    layout:'fit',
    header: false,

    initComponent: function () {
        Ext.apply(this, {
            emptyText: Ifresco.helper.Translations.trans('No tags added yet.'),
            store: Ifresco.store.TagManager.create({}),
            tbar: [{
            	xtype: 'textfield',
            	name: 'search',
            	width: 130
            },{
                text: Ifresco.helper.Translations.trans('Search'),
                handler: function() {
                    var filter = this.down("textfield[name~=search]").getValue();
                    this.getStore().load({
                    	params:{
                    		filter: filter
                    	}
                    });
                },
                scope: this
            },'-',{
            	iconCls: 'ifresco-icon-delete-button',
                text: Ifresco.helper.Translations.trans('Remove selected'),
                handler: function() {
                	var grid = this;
                    var selection = grid.getSelectionModel().getSelection();
                    if (selection.length > 0) {
                    	var tag = selection[0];
                    	console.log(tag);
	                    Ext.MessageBox.show({
	                        buttons: Ext.MessageBox.YESNO,
	                        icon: Ext.MessageBox.QUESTION,
	                        title: Ifresco.helper.Translations.trans('Delete Tag?'),
	                        msg: Ifresco.helper.Translations.trans('Do you really want to delete this tag?'),
	                        fn: function(btn) {
	                            if (btn == "yes") {
	                            	Ext.Ajax.request({
	    								method: 'POST',
	    								url: Routing.generate('ifresco_client_admin_tag_manager_delete'),
	    								disableCaching: true,
	    								params: {
	    									name: tag.get("name")
	    								},
	    								success: function() {
	    									grid.getStore().remove(tag);
	    									grid.getStore().sync(); 
	    								}
	    							});
	                            	
	                            }
	                        }
	                    });
                    }
                },
                scope: this
            }],
            columns: [{
                header: Ifresco.helper.Translations.trans('Tag'),
                flex: 2,
                dataIndex: 'name',
                editor: 'textfield'
            },{
                header: Ifresco.helper.Translations.trans('Modified by'),
                flex: 1,
                dataIndex: 'modifier'
            },{
                header: Ifresco.helper.Translations.trans('Modified at'),
                flex: 1,
                dataIndex: 'modified',
                renderer: Ext.util.Format.dateRenderer('d m Y H:i') // TODO - CHANGE WITH ADMIN SETTING FORMAT
            }],
            selType: 'rowmodel',
            plugins: [
                Ext.create('Ext.grid.plugin.RowEditing', {
                    clicksToEdit: 2,
                    pluginId: 'RowEditing',
                    listeners: {
                    	scope: this,
                        canceledit: function(editor, context) {
                            
                        },
                        afteredit: function(roweditor, e, eOpts) {
                        	var grid = this;
                        	console.log("FINISH EDITING",e,eOpts,e.record.get('nodeRef'),e.value);
							Ext.Ajax.request({
								method: 'POST',
								url: Routing.generate('ifresco_client_admin_tag_manager_edit'),
								disableCaching: true,
								params: {
									name: e.value,
									change: e.record.get('name')
								},
								success: function() {
									console.log("saved it",grid.getStore());
									/*this.getStore().load();*/
									
									grid.getStore().sync(); 
									grid.getStore().getAt(e.record.index).commit();
								}
							});
	                    }
                    }
                })
            ]
        });

        this.callParent();
    }
});