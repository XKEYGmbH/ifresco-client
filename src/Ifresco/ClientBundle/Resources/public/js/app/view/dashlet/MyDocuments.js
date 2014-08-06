Ext.define('Ifresco.view.dashlet.MyDocuments', {
	extend: 'Ext.grid.Panel',
    alias: 'widget.ifrescoDashletMyDocuments',
    maxHeight: 300,
    
    initComponent: function(){
        Ext.apply(this, {
            autoScroll: true,
            store: Ifresco.store.MyDocuments.create({}), 
            stripeRows: true,
            columnLines: true,
            emptyText: Ifresco.helper.Translations.trans('No documents in this list'),
            tbar: [{
            	xtype: 'combobox',
            	fieldLabel: Ifresco.helper.Translations.trans('Select a Filter'),
                displayField: 'name',
                valueField: 'value',
                selectOnFocus:true,
                forceSelection: true,
                editable: false,
                store: Ext.data.Store.create({
                    proxy: {
                        type: 'memory'
                    },
                    fields: ['name', 'value'],
                    data: [
                		{name: Ifresco.helper.Translations.trans('Modified recently'), value: 'recentlyModifiedByMe'},
                		{name: Ifresco.helper.Translations.trans('Editing'), value: 'editingMe'},
                		{name: Ifresco.helper.Translations.trans('Favorites'), value: 'favourites'}
                    ]
                }),
                queryMode: 'local',
                typeAhead: false,
                listeners: {
                	scope: this,
                	afterrender: function(combo) {
                        var recordSelected = combo.getStore().getAt(0);                     
                        combo.setValue(recordSelected.get('value'));
                    },
                    change: function( t, newValue, oldValue, eOpts ) {
                    	this.getStore().load({params: {filter: newValue}});
                    }
                }
            }],
            columns: [{
                text: Ifresco.helper.Translations.trans('Name'),
                flex: 2,
                sortable : true,
                dataIndex: 'fileName'
            }],
            listeners: {
            	itemclick: function(view, record, item, index, e, eOpts) {
                	//Ifresco.getApplication().getController("Index").loadAdvancedSearchTabFromSaved(record);
            		console.log("MY DOCUMEN ",record);
            		var nodeId = record.get("nodeRef"),
            			nodeId = nodeId.replace(/workspace:\/\/SpacesStore\//,"");

            		console.log("MY DOCUMEN id ",nodeId);
            		Ifresco.getApplication().getViewport().fireEvent("openDocumentDetail", nodeId, record.get("fileName")); 
                }
            }
        });

        this.callParent(arguments);
    }
});
