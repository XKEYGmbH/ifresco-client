Ext.define('Ifresco.form.Template', {
    extend: 'Ext.form.Panel',
    alias: 'widget.ifrescoFormTemplate',
    bodyCls: 'ifresco-form-template',
    layout: {
        type: 'vbox',
        align : 'stretch',
        pack  : 'start'
    },
    border: 0,
    margin: 0,
    padding: 0,
    globalPropertyIndex: 0,
    configData: null,
    aspectIsAppend: true,
    edit: null,
    templateClass: null,
    isMulticolumn: true,
    selectedTemplateTypeId: null,

    initComponent: function() {
        if (this.configData.FoundTemplate) {
            this.aspectIsAppend = this.configData.Aspectsview !== 'tabs';
            this.edit = this.configData.Id;
            this.templateClass = this.configData.Class;
            this.isMulticolumn = this.configData.Multicolumn;
        }
        this.tbar = [{
                text: Ifresco.helper.Translations.trans('Save'),
                cls: 'ifresco-admin-template-save',
                handler: function () {
                    this.fireEvent('save', this.selectedTemplateTypeId, this.configData);
                },
                scope: this
        }];

        this.items = [{
            xtype: 'panel',
            layout: 'hbox',
            border: 0,
            padding: 5,
            height: 50,
            items:[{
                xtype: 'checkbox',
                name: 'multiColumns',
                boxLabel: Ifresco.helper.Translations.trans('Multi columns'),
                cls: 'ifresco-admin-template-multi-columns',
                margin: '0 20 0 0',
                checked: this.isMulticolumn,
                listeners: {
                    change: function(checkbox, value) {
                        var component = this.down('propertyselector[name=column2]');
                        component.setDisabled(! value);
                        if (value) {
                            component.dropZone.unlock();
                        }
                        else {
                            component.dropZone.lock();
                        }
                        if (this.configData && this.configData.properties) {
                            component.up('container').down('button').setDisabled(! value);

                        }
                    },
                    scope: this
                }
            },{
                xtype: 'radiogroup',
                labelWidth: 140,
                width: 280,
                fieldLabel: Ifresco.helper.Translations.trans('Aspects managed as'),
                items:[{
                    inputValue:'tabs',
                    name: 'aspectsView',
                    boxLabel: '<img src="' + adminTabsImg + '"alt="' + Ifresco.helper.Translations.trans('Tabs') + '"/>',
                    checked: !this.aspectIsAppend
                },{
                    inputValue:'append',
                    name: 'aspectsView',
                    boxLabel: '<img src="' + adminColumnsImg + '"alt="' + Ifresco.helper.Translations.trans('Append on column') + '"/>',
                    checked: this.aspectIsAppend
                }]
            }]
        },{
            xtype: 'panel',
            padding: 5,
            layout: {
                type: 'hbox',
                align: 'stretch'
            },
            flex: 1,
            border: 0,
            items: [{
                xtype: 'container',
                layout: {
                    type: 'vbox',
                    align: 'stretch'
                },
                maxWidth: 200,
                flex: 1,
                margin: '5 5 0 5',
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
                    cls: 'ifresco-propertyselector',
                    name: 'column1',
                    flex: 1,
                    listConfig: {
                        itemTpl: Ifresco.view.template.TemplateProperty.create({})
                    },
                    store: Ifresco.store.TemplateProperties.create({
                        data: this.configData.FoundTemplate ? this.configData.Column1 : this.configData.properties
                    }),
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
                maxWidth: 200,
                flex: 1,
                margin: '5 5 0 5',
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
                    cls: 'ifresco-propertyselector',
                    name: 'column2',
                    flex: 1,
                    disabled: !this.isMulticolumn,
                    listConfig: {
                        itemTpl: Ext.create('Ifresco.view.template.TemplateProperty')
                    },
                    store: Ifresco.store.TemplateProperties.create({
                        data: this.configData.Column2
                    }),
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
                margin: '5 5 5 5',
                flex: 1.7,
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
                    items: this.createTabsContent(),
                    listeners: {
                        tabchange: function(tabPanel, newTab) {
                            tabPanel.down('textfield').setValue(newTab.tabConfig.title);
                        },
                        add: function(tab) {
                            if (tab.rendered) {
                                tab.setDisabled(false);
                            }
                            else {
                                tab.on('afterrender', function(tab) {
                                    tab.setDisabled(false);
                                });
                            }
                        },
                        remove: function(tab) {
                            if (tab.items.items.length == 0) {
                                tab.setDisabled(true);
                            }
                        },
                        afterrender: function(tabPanel) {
                            var active = tabPanel.getActiveTab();
                            if (active) {
                                tabPanel.fireEvent('tabchange', tabPanel, active);
                            }
                        }
                    }
                }]
            }],
            listeners: {
                afterrender: function() {
                    if (! this.down('checkbox[name=multiColumns]').getValue()) {
                        this.down('propertyselector[name=column2]').dropZone.lock();
                    }
                },
                scope: this
            }           
        }];

        this.callParent();

        // this.down()
    },

    createTab: function(store, title) {
        store = store || Ifresco.store.TemplateProperties.create({});
        title = title || '-';
        return {
            xtype: 'propertyselector',
            cls: 'ifresco-propertyselector',
            closable: true,
            maxWidth: 200,
            tabConfig: {
                title: title
            },
            autoScroll: true,
            layout: 'anchor',
            listConfig: {
                border: '0 1 0 0',
                itemTpl: Ifresco.view.template.TemplateProperty.create({})
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

    loadProperties: function(properties) {
        this.configData = this.configData || {};
        this.configData.properties = properties;
        var buttons =this.query('button[cls~=ifresco-searchtemplate-button-add-property]');
        Ext.each(buttons, function(button) {
            var container = button.up('container');
            var component = container.down('propertyselector');
            if (component === null || component.disabled === false) {
                button.enable();
                button.disabledCls = 'x-item-disabled';
            }
        });
    },

    createTabsContent: function () {
        var tabs = [];
        if (this.configData.FoundTemplate && this.configData.Tabs && this.configData.Tabs.tabs) {
            Ext.each(this.configData.Tabs.tabs, function (tab){
                var title = tab.title;
                var store = Ifresco.store.TemplateProperties.create({
                    data: tab.items
                });
                tabs.push(this.createTab(store, title));

            }, this);
        }

        return tabs;
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