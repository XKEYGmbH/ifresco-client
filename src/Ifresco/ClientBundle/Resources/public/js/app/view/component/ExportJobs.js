Ext.define('Ifresco.view.ExportJobs', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.ifrescoViewExportJobs',
    border: 0,
    autoScroll: true,
    closeAction: 'hide',
    cls: 'ifresco-view-exportjobs',
    configData: null,
    layout: {
        type: 'hbox',
        align: 'stretch'
    },

    initComponent: function () {
        Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('Export Jobs'),

            tbar: [{
                text: Ifresco.helper.Translations.trans('Export'),
                handler : function(){ 
                    this.fireEvent('export');
                },
                scope: this
            }],
            items: [{
                xtype: 'container',
                maxWidth: 300,
                flex:1, 
                layout: {
                    type: 'vbox',
                    align: 'stretch'
                },
                items: [{
                    xtype: 'form',
                    layout: 'fit',
                    padding: '5 10 5 10',
                    border: 0,
                    items: [{
                        xtype: 'combo',
                        queryMode: 'local',
                        store: Ext.data.Store.create({
                            fields: ['id', 'name', 'jsonFields'],
                            proxty: {
                                type: 'memory'
                            }
                        }),
                        displayField: 'name',
                        valueField: 'id',
                        name: 'columnset',
                        fieldLabel: Ifresco.helper.Translations.trans('Column Set'),
                        listeners: {
                            select: function(combo, record) {
                                var panel = this.down('panel[cls~=ifresco-view-property-container]');
                                var store = Ifresco.store.Properties.create();
                                var fields = Ext.decode(record[0].data.jsonFields);
                                store.loadData(fields);
                                panel.removeAll();
                                panel.add(this.createPropertiesPanel(store));
                            },
                            scope: this
                        }
                    }]
                },{
                    xtype: 'panel',
                    border: 1,
                    cls: 'ifresco-view-property-container',
                    layout: 'fit',
                    padding: '5 10 5 10',
                    flex: 1,
                    maxWidth: 300,
                    items: [],
                    dockedItems: [{
                        xtype: 'container',
                        dock: 'top',
                        items: [{
                            xtype: 'button',
                            margin: 5,
                            name: 'addProperty',
                            disabledCls: 'ifresco-button-add-property-disabled',
                            disabled: true,
                            text: Ifresco.helper.Translations.trans('Add property'),
                            handler: function() {
                                this.showAddPropertiesWindow(this.down('propertyselector').getStore());
                            },
                            scope: this
                        }]
                    }]
                }]
            },{
                xtype: 'container',
                flex: 1,
                maxWidth: 200,
                padding: '5 10 5 10',
                layout: {
                    type: 'vbox',
                    align: 'stretch'
                },
                border: 1,
                items: [{
                    xtype: 'checkbox',
                    labelWidth: 180,
                    name: 'use_email',
                    boxLabel: Ifresco.helper.Translations.trans('Send via Email'),
                    listeners: {
                        change: function(checkbox, value) {
                            var textField = checkbox.up('container').down('textfield[name=email]');
                            if (value) {
                                textField.enable();
                            }
                            else {
                                textField.disable();
                            }
                        }
                    }

                },{
                    xtype: 'textfield',
                    name: 'email',
                    disabled: true,
                    labelWidth: 50,
                    fieldLabel: Ifresco.helper.Translations.trans('Email')
                },{
                    xtype: 'checkbox',
                    name: 'folders',
                    boxLabel: Ifresco.helper.Translations.trans('Export folder nodes')
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

    createPropertiesPanel: function(store) {
        return {
            xtype: 'propertyselector',
            itemId: 'propertyColumn',
            cls: 'ifresco-propertyselector',
            store: store,
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
        };
    },

    loadProperties: function(properties) {
        this.configData = this.configData || {};
        this.configData.properties = properties;
        var btn = this.down('button[name=addProperty]');
        if (btn) {
            btn.enable();
        }
    },

    loadExportFields: function(data) {
        this.loaded = true;
        this.setLoading(false);
        this.configData = this.configData || {};
        var configData = this.configData;
        configData.columnSets = [];
        var columnSets = [];
        Ext.each(data.ColumnSets, function(columnSet) {
            columnSets.push({
                id: columnSet.id,
                name: columnSet.name,
                jsonFields: columnSet.jsonFields
            });
        });
        this.down('combo[name=columnset]').getStore().loadData(columnSets);
        var store = Ifresco.store.Properties.create({data: data.Fields});
        var panel = this.down('panel[cls~=ifresco-view-property-container]');
        panel.add(this.createPropertiesPanel(store));
        if(data.FoundEmail) {
            this.down('checkbox[name=use_email]').setValue(true);
            this.down('textfield[name=email]').setValue(data.Email);
        }
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
