Ext.define('Ifresco.view.grid.Plugins', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.ifrescoViewGridPlugins',
    cls: 'ifresco-view-grid-plugins',
    border: 0,
    viewConfig: {
        forceFit:true,
        autoHeight: true
    },
    layout:'fit',
    collapsible: true,
    animCollapse: true,
    header: false,

    initComponent: function () {
    	this.statusStore = Ext.create('Ext.data.Store', {
            fields: ['status', 'value'],
            idProperty: 'value',
            data : [
                {"status":"enabled", "value": true},
                {"status":"disabled", "value": false}
            ]
        });
    	
        Ext.apply(this, {
            emptyText: Ifresco.helper.Translations.trans('No plugins avaiable.'),
            store: Ifresco.store.Plugins.create(),
            columns: [{
                header: Ifresco.helper.Translations.trans('Name'),
                width: 300, 
                sortable: true,
                dataIndex: 'name'
            },{
                header: Ifresco.helper.Translations.trans('Version'),
                width: 80, 
                sortable: true,
                dataIndex: 'version'
            },{
                header: Ifresco.helper.Translations.trans('Author'),
                width: 150,
                sortable: true,
                dataIndex: 'author'
            },{
                header: Ifresco.helper.Translations.trans('Description'),
                width: 150, 
                flex: 1,
                sortable: true,
                dataIndex: 'description'
            },{
                header: Ifresco.helper.Translations.trans('Status'),
                width: 100, 
                sortable: true,
                dataIndex: 'status',
                editor: {
                    xtype: 'combobox',
                    allowBlank : false,
                    typeAhead: false,
                    triggerAction: 'all',
                    selectOnTab: true,
                    forceSelection: true,
                    store: this.statusStore,
                    queryMode: 'local',
                    displayField: 'status',
                    valueField:'value'
                },
                renderer: function(value, metaData, record, row, col, store, view) {
                	console.log("RENDERER",this, value);
                	
                	var recordPos = this.statusStore.findBy(function(rec,id){
                	     return rec.data.value == value;
                	}, this);
                	if(recordPos > -1) {
                	   var record = this.statusStore.getAt(recordPos);
                	   console.log("RETURN STATUS",record.get('status'))
                	   return record.get('status'); 
                	}
                	return null;
                    //return this.statusStore.getById(value).get('status');
                },
                scope: this
            }],
            tbar: [{
            	iconCls: 'ifresco-icon-refresh',
                text: Ifresco.helper.Translations.trans('Refresh'),
                handler: function() {
                    this.getStore().reload();
                },
                scope: this
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
                        	console.log("FINISH EDITING",e,eOpts,e.value,e.record.get('status'));
                        	
                        	Ext.Ajax.request({
								method: 'POST',
								url: Routing.generate('ifresco_client_admin_plugins_save'),
								disableCaching: true,
								params: {
									status: e.record.get('status'),
									plugin: e.record.get('name')
								},
								success: function() {
									console.log("saved it",grid.getStore());

									grid.getStore().sync(); 
									grid.getStore().getAt(e.record.index).commit();
								}
							});
	                    }
                    }
                })
            ],
            listeners: {
                scope: this
            }
        });
        this.callParent();
    }

});
