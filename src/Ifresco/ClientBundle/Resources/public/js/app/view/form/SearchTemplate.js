Ext.define('Ifresco.form.SearchTemplate', {
    extend: 'Ext.form.Panel',
    alias: 'widget.ifrescoFormSearchTemplate',
    layout: {
        type: 'vbox',
        align: 'stretch'
    },
    border: 0,
    defaults: {
        padding: '10 10 0 10'
    },
    customFieldIndex: 0,
    configData: null,
    loaded: false,

    initComponent: function() {
        var me = this;

        Ext.apply(this, {

            title: Ifresco.helper.Translations.trans('Search Template'),
            tbar: [{
                text: Ifresco.helper.Translations.trans('Save'),
                handler: function() {
                    this.fireEvent('save');
                },
                scope: this
            }],
            items: [{
                xtype: 'container',
                layout: 'hbox',
                maxWidth: 900,
                border: 0,
                items: [{
                    xtype: 'container',
                    flex: 1,
                    layout: {
                        type: 'vbox',
                        align: 'stretch'
                    },
                    items: [{
                        xtype: 'container',
                        flex: 1,
                        layout: {
                            type: 'hbox',
                            align: 'stretch'
                        },
                        items: [{
                            xtype: 'container',
                            layout: {
                                type: 'vbox',
                                align: 'stretch'
                            },
                            margin: '0 10 0 0',
                            flex: 1,
                            items: [{
                                xtype: 'textfield',
                                fieldLabel: Ifresco.helper.Translations.trans('Name'),
                                name: 'name',
                                labelWidth: 80
                            },{
                                xtype: 'combo',
                                store: {
                                    fields: ['id', 'name'],
                                    proxy: {
                                        type: 'memory'
                                    }
                                },
                                valueField: 'id',
                                displayField: 'name',
                                queryMode: 'local',
                                name: 'columnset',
                                fieldLabel: Ifresco.helper.Translations.trans('Column Set'),
                                labelWidth: 80
                            },{
                                xtype: 'combo',
                                store: {
                                    fields: ['id', 'name'],
                                    proxy: {
                                        type: 'memory'
                                    }
                                },
                                valueField: 'id',
                                displayField: 'name',
                                queryMode: 'local',
                                name: 'savedsearch',
                                fieldLabel: Ifresco.helper.Translations.trans('Saved search'),
                                labelWidth: 80
                            },{
                                xtype: 'combo',
                                store: {
                                    fields: ['name', 'title'],
                                    proxy: {
                                        type: 'memory'
                                    }
                                },
                                displayField: 'title',
                                valueField: 'name',
                                queryMode: 'local',
                                name: 'contenttype',
                                fieldLabel: Ifresco.helper.Translations.trans('Content type'),
                                labelWidth: 80
                            }]
                        },{
                            xtype: 'container',
                            layout: 'vbox',
                            width: 160,
                            items: [{
                                xtype: 'checkbox',
                                fieldLabel: Ifresco.helper.Translations.trans('Multi Columns'),
                                name: 'multiColumns',
                                checked: true,
                                labelWidth: 140,
                                listeners: {
                                    change: function(checkbox, newValue) {
                                        var propertySelector = this.down('propertyselector[name=col2]');
                                        var btn = propertySelector.up('container').down('button');
                                        if (newValue) {
                                            propertySelector.enable();
                                            propertySelector.dropZone.unlock();
                                            btn.enable();
                                        } else {
                                            propertySelector.dropZone.lock();
                                            propertySelector.disable();
                                            btn.disable();
                                        }
                                    },
                                    scope: this
                                }
                            },{
                                xtype: 'checkbox',
                                fieldLabel: Ifresco.helper.Translations.trans('Fulltext search on childs'),
                                name: 'fulltextChild',
                                labelWidth: 140
                            },{
                                xtype: 'checkbox',
                                fieldLabel: Ifresco.helper.Translations.trans('Fulltext search on childs override properties'),
                                name: 'fulltextChildOverwrite',
                                labelWidth: 140
                            }]
                        }]
                    },{
                        xtype: 'textfield',
                        fieldLabel: Ifresco.helper.Translations.trans('Lucene query (lucene syntax)'),
                        labelAlign: 'top',
                        name: 'lucene_query',
                        padding: '5 0 0 0',
                        flex: 1,
                        labelWidth: 180
                    }]
                },{
                    xtype: 'container',
                    layout: 'fit',
                    flex: 1,
                    padding: '0 5 0 5',
                    items: [{
                        xtype: 'itemselector',
                        name: 'showDoctype',
                        store: {
                            fields: ['id', 'name', 'title'],
                            proxy: {
                                type: 'memory'
                            }
                        },
                        displayField: 'title',
                        valueField: 'name',
                        allowBlank: true,
                        msgTarget: 'side',
                        buttons: ['top', 'up', 'add', 'remove', 'down', 'bottom'],
                        height: 160
                    }]
                }]
            },{
                xtype: 'container',
                layout: {
                    type: 'hbox',
                    align: 'stretch'
                },
                maxWidth: 900,
                flex: 1,
                items: [{
                    xtype: 'container',
                    layout: {
                        type: 'vbox',
                        align: 'stretch'
                    },
                    flex: 1,
                    padding: '0 10 0 0',
                    items: [{
                        xtype: 'button',
                        cls: 'ifresco-searchtemplate-button-add-property',
                        maxWidth: 100,
                        margin: '0 0 5 0',
                        text: Ifresco.helper.Translations.trans('Add property'),
                        disabledCls: 'ifresco-button-add-property-disabled',
                        disabled: true,
                        handler: function (btn) {
                            this.showAddPropertiesWindow(btn.up('container').down('propertyselector').getStore());
                        },
                        scope: this
                    },{
                        xtype: 'propertyselector',
                        name: 'col1',
                        cls: 'ifresco-propertyselector',
                        autoScroll: true,
                        // valueField: 'id',
                        layout: 'fit',
                        flex: 1,
                        listConfig: {
                            itemTpl: Ifresco.view.template.Property.create({})
                        },
                        store: Ifresco.store.Properties.create({}),
                        listeners: {
                        	drop: function(m, records) {
                        		var selModel = m.boundList.getSelectionModel();
                        		console.log("DROP PROPERTY TEMPLATE", m, records, selModel);
                        		selModel.deselectAll();
                        	}
                        }
                    }]
                },{
                    xtype: 'container',
                    layout: {
                        type: 'vbox',
                        align: 'stretch'
                    },
                    flex: 1,
                    padding: '0 10 0 0',
                    items: [{
                        xtype: 'button',
                        cls: 'ifresco-searchtemplate-button-add-property',
                        maxWidth: 100,
                        margin: '0 0 5 0',
                        text: Ifresco.helper.Translations.trans('Add property'),
                        disabledCls: 'ifresco-button-add-property-disabled',
                        disabled: true,
                        handler: function (btn) {
                            this.showAddPropertiesWindow(btn.up('container').down('propertyselector').getStore());
                        },
                        scope: this
                    },{
                        xtype: 'propertyselector',
                        name: 'col2',
                        cls: 'ifresco-propertyselector',
                        autoScroll: true,
                        layout: 'anchor',
                        flex: 1,
                        listConfig: {
                            itemTpl: Ifresco.view.template.Property.create({}),
                            forceFit: true
                        },
                        store: Ifresco.store.Properties.create({}),
                        listeners: {
                        	drop: function(m, records) {
                        		var selModel = m.boundList.getSelectionModel();
                        		console.log("DROP PROPERTY TEMPLATE", m, records, selModel);
                        		selModel.deselectAll();
                        	}
                        }
                    }]
                },{
                    xtype: 'container',
                    layout: {
                        type: 'vbox',
                        align: 'stretch'
                    },
                    flex: 1,
                    items: [{
                        xtype: 'combo',
                        queryMode: 'local',
                        name: 'contentTypes',
                        store: {
                            fields: ['name', 'title'],
                            proxy: {
                                type: 'memory'
                            }
                        },
                        displayField: 'title',
                        valueField: 'name',
                        margin: '0 0 5 0',
                        listeners: {
                            select: function(combo, records) {
                                var data;
                                Ext.Ajax.request({
                                    url: Routing.generate('ifresco_client_admin_template_metadata_get'),
                                    method: 'GET',
                                    params: {
                                        'class': records[0].data.name
                                    },
                                    success: function(response) {
                                        data = Ext.decode(response.responseText);
                                        var properties = [];
                                        var store = combo.up('container').down('propertyselector').getStore();
                                        Ext.each(data.Properties, function(property) {
                                            properties.push({
                                                title: property.title,
                                                name: property.name,
                                                dataType: property.dataType
                                            });
                                        });
                                        store.removeAll();

                                        var excludedProperties = me.getAllProperties();
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

                                        store.loadData(properties);
                                    }
                                });
                            }
                        }
                    },{
                        xtype: 'propertyselector',
                        cls: 'ifresco-propertyselector',
                        autoScroll: true,
                        layout: 'anchor',
                        flex: 1,
                        listConfig: {
                            itemTpl: Ifresco.view.template.Property.create({})
                        },
                        store: Ifresco.store.Properties.create({})
                    }]
                }]
            },{
                xtype: 'container',
                layout: {
                    type: 'hbox',
                    align: 'stretch'
                },
                flex: 1,
                maxWidth: 900,
                padding: 10,
                items: [{
                    xtype: 'container',
                    layout: {
                        type: 'vbox',
                        align: 'stretch'
                    },
                    flex: 1,
                    padding: '0 5 0 0',
                    items: [{
                        xtype: 'button',
                        name: 'addProperty',
                        maxWidth: 120,
                        margin: '0 0 5 0',
                        text: Ifresco.helper.Translations.trans('Add custom field'),
                        handler: function() {
                            var tab = this.createCustomFieldTab();
                            var tabs = this.down('tabpanel[name=customFields]');

                            tabs.add(tab).show();
                            if (tabs.items.items.length == 1) {
                                tabs.setActiveTab(0);
                            }
                        },
                        scope: this
                    },{
                        xtype: 'tabpanel',
                        disabled: true,
                        itemId: 'cutomFields',
                        name: 'customFields',
                        flex: 1,
                        dockedItems: [{ 
                            xtype: 'toolbar',
                            dock: 'left',
                            width: 110,
                            items: [{
                                xtype: 'textfield',
                                fieldLabel: Ifresco.helper.Translations.trans('Set field name'),
                                value: '',
                                labelAlign: 'top',
                                width: 100,
                                listeners: {
                                    change: function(field, newValue) {
                                        var tab = field.up('tabpanel').getActiveTab();
                                        if (tab) {
                                            tab.tabConfig.title = newValue;
                                            tab.customField.set('title', newValue);
                                            tab.tab.setText(newValue);
                                        }
                                    }
                                }
                            },{
                                xtype: 'button',
                                margin: '10 5 10 0',
                                cls: 'ifresco-searchtemplate-button-add-property',
                                text: Ifresco.helper.Translations.trans('Add property'),
                                disabledCls: 'ifresco-button-add-property-disabled',
                                disabled: true,
                                width: 100,
                                handler: function (btn) {
                                    this.showAddPropertiesWindow(btn.up('tabpanel').getActiveTab().getStore());
                                },
                                scope: this
                            },{
                                xtype: 'radiogroup',
                                layout: 'hbox',
                                defaults: {
                                    xtype: 'radiofield',
                                    margin: '0 0 0 5',
                                    name: 'customQueryMode'
                                },
                                items: [{
                                    boxLabel: Ifresco.helper.Translations.trans('AND'),
                                    inputValue: 'and',
                                    checked: true
                                },{
                                    boxLabel: Ifresco.helper.Translations.trans('OR'),
                                    inputValue: 'or'
                                }],
                                listeners: {
                                    change: function(radio, value) {
                                        var tab = radio.up('tabpanel').getActiveTab();
                                        tab.customQueryMode = value.customQueryMode;
                                    }
                                }
                            }]
                        }],
                        items: [],
                        listeners: {
                            tabchange: function(tabPanel, newTab) {
                                tabPanel.down('textfield').setValue(newTab.tabConfig.title);
                                tabPanel.down('radiogroup').setValue({customQueryMode: newTab.customQueryMode});
                            },
                            add: function(tab) {
                                tab.setDisabled(false);
                            },
                            remove: function(tab) {
                                if (tab.items.items.length == 0) {
                                    tab.setDisabled(true);
                                }
                            }
                        }
                    }]
                },{
                    xtype: 'container',
                    layout: {
                        type: 'vbox',
                        align: 'stretch'
                    },
                    flex: 1,
                    items: [{
                        xtype: 'button',
                        maxWidth: 120,
                        margin: '0 0 5 0',
                        text: Ifresco.helper.Translations.trans('Add tab'),
                        handler: function() {
                            var tab = this.createTab();
                            var tabs = this.down('tabpanel[name=tabs]');
                            tabs.add(tab).show();
                            if (tabs.items.items.length == 1) {
                                tabs.setActiveTab(0);
                            }
                        },
                        scope: this
                    },{
                        xtype: 'tabpanel',
                        name: 'tabs',
                        disabled: true,
                        flex: 1,
                        dockedItems: [{ 
                            xtype: 'toolbar',
                            dock: 'left',
                            width: 110,
                            items: [{
                                xtype: 'textfield',
                                fieldLabel: Ifresco.helper.Translations.trans('Set tab name'),
                                value: 'Tab2',
                                labelAlign: 'top',
                                width: 100,
                                listeners: {
                                    change: function(field, newValue) {
                                        var tab = field.up('tabpanel').getActiveTab();
                                        if (tab) {
                                            tab.tabConfig.title = newValue;
                                            tab.tab.setText(newValue);
                                        }
                                    }
                                }
                            },{
                                margin: '10 5 10 0',
                                cls: 'ifresco-searchtemplate-button-add-property',
                                text: Ifresco.helper.Translations.trans('Add property'),
                                disabledCls: 'ifresco-button-add-property-disabled',
                                disabled: true,
                                width: 100,
                                handler: function (btn) {
                                    this.showAddPropertiesWindow(btn.up('tabpanel').getActiveTab().getStore());
                                },
                                scope: this
                            }]
                        }],
                        items: [],
                        listeners: {
                            tabchange: function(tabPanel, newTab) {
                                tabPanel.down('textfield').setValue(newTab.tabConfig.title);
                            },
                            add: function(tab) {
                                tab.setDisabled(false);
                            },
                            remove: function(tab) {
                                if (tab.items.items.length == 0) {
                                    tab.setDisabled(true);
                                }
                            }
                        }
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
        this.configData = this.configData || {};
        this.configData.properties = properties;
        var buttons =this.query('button[cls~=ifresco-searchtemplate-button-add-property]');
        Ext.each(buttons, function(button) {
            button.enable();
            button.disabledCls = 'x-item-disabled';
        });
    },

    createCustomFieldTab: function(store, title, customQueryMode) {
        this.customFieldIndex++;
        customQueryMode = customQueryMode || 'and';
        var columnStore = this.down('propertyselector[name=col1]').getStore();
        title = title || 'Custom field ' + this.customFieldIndex;
        var tabPanel = this.down('tabpanel[name=customFields]');
        var record;
        var field;
        if (! store) { 
            store = Ifresco.store.Properties.create({});
            field = new Ifresco.model.Property({
                title: title, 
                name: 'custom-field-control' + this.customFieldIndex,
                'class': 'custom-field',
                dataType: 'd:text', 
                isCustom: true
            });
            columnStore.add(field);
        }
        else {
            Ext.each(this.query('propertyselector'), function() {
                record = this.getStore().findRecord('title', title);
                if (record) {
                    field = record;
                }
            });
            if (! field) {
                field = new Ifresco.model.Property({
                    title: title, 
                    name: 'custom-field-control' + this.customFieldIndex,
                    'class': 'custom-field',
                    dataType: 'd:text', 
                    isCustom: true
                });
                columnStore.add(field);
            }
        }
        field.raw.assigned = true;

        field.on('afterremove', function(record) {
            if (record.data.id == field.data.id) {
                Ext.each(tabPanel.items.items, function(tab){
                    if (tab && tab.customField === field) {
                        tab.destroy();
                    }
                });
            }
        });
        return {
            xtype: 'propertyselector',
            cls: 'ifresco-propertyselector',
            closable: true,
            tabConfig: {
                title: title
            },
            autoScroll: true,
            layout: 'anchor',
            customField: field,
            listConfig: {
                border: 0,
                itemTpl: Ifresco.view.template.Property.create({})
            },
            store: store,
            customQueryMode: customQueryMode,
            listeners: {
                beforeclose: function handler(tab) {
                    var panel = tab.up('tabpanel');
                    Ext.Msg.confirm(
                        Ifresco.helper.Translations.trans('Delete custom field?'),
                        Ifresco.helper.Translations.trans('Do you really want to delete this field?'),
                        function(btn) {
                            if (btn == 'yes'){
                                tab.customField.destroy();
                                tab.un('beforeclose', handler);
                                panel.remove(tab);
                            }
                        }
                    );
                    return false;                
                }
            }
        };
    },

    createTab: function(store, title) {
        store = store || Ifresco.store.Properties.create({});
        title = title || '-';
        return {
            xtype: 'propertyselector',
            cls: 'ifresco-propertyselector',
            closable: true,
            tabConfig: {
                title: title
            },
            autoScroll: true,
            layout: 'anchor',
            listConfig: {
                border: 0,
                itemTpl: Ifresco.view.template.Property.create({})
            },
            store: store,
            listeners: {
                beforeclose: function handler(tab) {
                    var panel = tab.up('tabpanel');
                    Ext.Msg.confirm(
                        Ifresco.helper.Translations.trans('Delete tab?'),
                        Ifresco.helper.Translations.trans('Do you really want to delete this tab?'),
                        function(btn) {
                            if (btn == 'yes'){
                                tab.un('beforeclose', handler);
                                panel.remove(tab);
                            }
                        }
                    );
                    return false;                
                }
            }
        };
    },

    loadTemplateData: function(template) {
        var me = this;
        this.edit = template.Id || null;
        this.down('textfield[name=name]').setValue(template.Name);
        this.down('checkbox[name=multiColumns]').setValue(template.Multicolumn);
        this.down('checkbox[name=fulltextChild]').setValue(template.Fulltextchild);
        this.down('checkbox[name=fulltextChildOverwrite]').setValue(template.Fulltextchildoverwrite);
        this.down('textfield[name=lucene_query]').setValue(template.LuceneQuery);
        var columnSetCombo = this.down('combo[name=columnset]');
        columnSetCombo.getStore().loadData(template.ColumnSets);
        var columnSetRecord = columnSetCombo.getStore().findRecord('id', template.ColumnsetId);
        if (columnSetRecord) {
            columnSetCombo.select(columnSetRecord);
        }
        var savedSearchCombo = this.down('combo[name=savedsearch]');
        savedSearchCombo.getStore().loadData(template.savedSearches);
        var savedSearchRecord = savedSearchCombo.getStore().findRecord('id', template.savedSearchId);
        if (savedSearchRecord) {
            savedSearchCombo.select(template.savedSearchId);
        }
        
        this.down('propertyselector[name=col1]').getStore().loadData(template.Column1);
        this.down('propertyselector[name=col2]').getStore().loadData(template.Column2);

        var tabPanel = this.down('tabpanel[name=tabs]');
        Ext.each(template.Tabs, function(tab) {
            var store = Ifresco.store.Properties.create();
            var properties = [];
            Ext.each(tab.items, function(property) {
                var temp = property.split('/');
                properties.push({
                    name: temp[0],
                    'class': temp[1],
                    title: temp[2],
                    dataType: temp[3],
                    type: temp[4]
                });
            });
            store.loadData(properties);
            tabPanel.add(me.createTab(store, tab.title)).show();
            if (tabPanel.items.items.length == 1) {
                tabPanel.setActiveTab(0);
            }
        });

        var customFieldsPanel = this.down('tabpanel[name=customFields]');
        Ext.each(template.customFields, function(field) {
            var store = Ifresco.store.Properties.create();
            store.loadData(field.customFieldValues);
            var tab = me.createCustomFieldTab(
                store, 
                field.custom_field_lable, 
                field.customQueryMode
            );
            if (tab) {
                customFieldsPanel.add(tab).show();
            }

            if (customFieldsPanel.items.items.length == 1) {
                customFieldsPanel.setActiveTab(0);
            }
        });

        var recordsToDestroy = [];
        Ext.each(this.query('propertyselector'), function(propertySelector) {
            propertySelector.getStore().each(function(record) {
                if (record.get('class') == 'custom-field' && !record.raw.assigned) {
                    recordsToDestroy.push(record);
                }
            });
        });
        Ext.each(recordsToDestroy, function(record) {
            record.destroy();
        });

        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_admin_templates_content_types'),
            disableCaching: true,
            method: 'GET',
            success: function(response) {
                var data = Ext.decode(response.responseText);
                var contentTypes = data.types;
                me.down('combo[name=contentTypes]').getStore().loadData(contentTypes);
                var itemSelector = me.down('itemselector[name=showDoctype]');
                itemSelector.getStore().loadData(contentTypes);
                itemSelector.bindStore(itemSelector.getStore());
                itemSelector.setValue(template.Showdoctype);
                
                
                
                
                var contentTypeCombo = me.down('combo[name=contenttype]');
                contentTypeCombo.getStore().loadData(contentTypes);
                var contentTypeRecord = contentTypeCombo.getStore().findRecord('name', template.contentType);
                if (contentTypeRecord) {
                	contentTypeCombo.select(template.contentType);
                }
                
            }
        });

        this.setLoading(false);
        this.loaded = true;
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