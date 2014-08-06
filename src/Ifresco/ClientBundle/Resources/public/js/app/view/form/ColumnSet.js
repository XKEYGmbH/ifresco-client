Ext.define('Ifresco.form.ColumnSet', {
    extend: 'Ext.form.Panel',
    alias: 'widget.ifrescoFormColumnSet',
    border: 0,
    autoScroll: true,
    closeAction: 'hide',
    cls: 'ifresco-form-columnset',
    loaded: false,
    configData: null,
    layout: {
        type: 'vbox',
        align: 'stretch'
    },

    initComponent: function () {
        Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('ColumnSet'),
            tbar: [{
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
                layout: {
                    type: 'vbox',
                    align: 'stretch'
                },
                border: 0,
                items: [{
                    xtype: 'textfield',
                    name: 'name',
                    fieldLabel: Ifresco.helper.Translations.trans('Name'),
                    labelAlign: 'top'
                },{
                    xtype: 'checkboxfield',
                    name: 'hideInMenu', 
                    checked: false,
                    fieldLabel: Ifresco.helper.Translations.trans('Hide in Menu'),
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
                    cls: 'ifresco-propertyselector',
                    name: 'cols',
                    store: Ext.create('Ifresco.store.ColumnSetProperties'),
                    border: 1,
                    listConfig: {
                        border: 0,
                        itemTpl: Ext.create('Ifresco.view.template.ColumnSetProperty')
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
                        disabledCls: 'ifresco-button-add-property-disabled',
                        disabled: true,
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

    loadColumnSetData: function(columnSet) {
        this.loaded = true;
        this.setLoading(false);
        this.down('textfield[name=name]').setValue(columnSet.Name);
        this.down('checkboxfield[name=hideInMenu]').setValue(columnSet.HideInMenu);
        this.edit = columnSet.Id;
        var store = this.down('propertyselector[name=cols]').getStore();
        Ext.each(columnSet.Columns, function(property) {
            store.add({
                title: property.title,
                name: property.name,
                'class': property['class'],
                dataType: property.dataType,
                type: property.type,
                hidden: property.hide,
                sort: property.sort,
                ascending: property.asc
            });
        });
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
