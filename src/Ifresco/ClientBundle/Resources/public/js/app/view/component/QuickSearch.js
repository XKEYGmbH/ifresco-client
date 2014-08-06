Ext.define('Ifresco.view.QuickSearch', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.ifrescoViewQuickSearch',
    border: 0,
    autoScroll: true,
    closeAction: 'hide',
    cls: 'ifresco-view-quicksearch',
    configData: null,
    loaded: false,
    layout: {
        type: 'vbox',
        align: 'stretch'
    },

    initComponent: function () {
        Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('Quick Search'),

            tbar: [{
            	iconCls: 'ifresco-icon-save',
            	text: Ifresco.helper.Translations.trans('Save'),
                handler : function(){ 
                    this.fireEvent('save');
                },
                scope: this
            }],
            items: [{
                xtype: 'form',
                layout: 'fit',
                padding: '5 10 5 10',
                border: 0,
                maxWidth: 300,
                items: [{
                    xtype: 'textfield',
                    name: 'lucene_query',
                    fieldLabel: Ifresco.helper.Translations.trans('Lucene query (lucene syntax)'),
                    labelAlign: 'top'
                }]
            },{
                xtype: 'panel',
                border: 1,
                layout: 'fit',
                padding: '5 10 5 10',
                flex: 1,
                maxWidth: 300,
                items: [{
                    xtype: 'propertyselector',
                    itemId: 'propertyColumn',
                    cls: 'ifresco-propertyselector',
                    store: Ext.create('Ifresco.store.Properties'),
                    border: 1,
                    listConfig: {
                        border: 0,
                        itemTpl: Ext.create('Ifresco.view.template.Property')
                    },
                    listeners: {
                    	drop: function(m, records) {
                    		var selModel = m.boundList.getSelectionModel();
                    		console.log("DROP PROPERTY TEMPLATE", m, records, selModel);
                    		selModel.deselectAll();
                    	}
                    }
                }],
                dockedItems: [{
                    xtype: 'container',
                    dock: 'top',
                    items: [{
                        xtype: 'button',
                        margin: 5,
                        name: 'addProperty',
                        disabled: true,
                        disabledCls: 'ifresco-button-add-property-disabled',
                        text: Ifresco.helper.Translations.trans('Add property'),
                        handler: function (btn) {
                            this.showAddPropertiesWindow(btn.up('panel').down('propertyselector').getStore());
                        },
                        scope: this
                    }]
                }]
            }],
            listeners: {
                afterrender: function(el) {
                    if (! el.loaded) {
                        el.setLoading(true, true);
                    }
                }
            }
        });
        this.callParent();
    },

    loadProperties: function(properties) {
        if (this.configData == null) {
            this.configData = {};
        }
        this.configData.properties = properties;
        
        var btn = this.down('button[name=addProperty]');
        if (btn) {
            btn.enable();
        }
    },

    loadData: function(data) {
        this.loaded = true;
        this.setLoading(false);
        if (! data) {
            return;
        }
        var luceneQuery = this.down('textfield[name=lucene_query]');
        luceneQuery.setValue(data.luceneQuery);
        this.down('propertyselector').getStore().loadRawData(data.fields);
    },

    getAllProperties: function () {
        var stores = [];
        var properties = [];
        Ext.each(this.query('propertyselector'), function (propertyselector) {
            stores.push(propertyselector.getStore());
        });
        Ext.each(stores, function (store) {
            store.each(function (record) {
                properties.push(record.getData());
            });
        });
        return properties;
    },

    showAddPropertiesWindow: function (toStore) {
        var properties = this.configData.properties.slice(0);
        var excludedProperties = this.getAllProperties();
        var toRemove = [];
        Ext.each(excludedProperties, function (excludedProperty) {
            Ext.each(properties, function (property, index) {
                if (excludedProperty.name == property.name &&
                    excludedProperty.dataType == property.dataType
                    ) {
                    toRemove.push(property);
                return false;
            }
        });
        }, this);
        Ext.each(toRemove, function (property) {
            var index = properties.indexOf(property);
            if (~index) {
                properties.splice(index, 1);
            }
        });
        var fromStore = Ext.create('Ifresco.store.Properties', {
            data: properties 
        });
        var win = Ext.create('Ifresco.view.window.Properties', {
            configData: {
                fromStore: fromStore,
                toStore: toStore 
            }
        });
        win.show();
    }

});
