Ext.define('Ifresco.view.panel.lookup.Category', {
    extend: 'Ext.form.Panel',
    alias: 'widget.ifrescoViewPanelLookupCategory',
    border: 1,
    margin: 5,
    bodyPadding: 10,
    cls: 'ifresco-view-panel-lookup',
    closable: true,
    layout: 'column',
    configData: null,
    lookupConfig: null,

    initComponent: function () {
        var me = this;
        this.lookupId = this.lookupId || parseInt(Math.random() * 1000000, 10);
        
        Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('Category lookup'),
            titlePart: Ifresco.helper.Translations.trans('Category lookup'),
            items: [{
                xtype: 'container',
                columnWidth: 0.4,
                layout: 'anchor',
                minWidth: 250,
                items: [{
                    xtype: 'hiddenfield',
                    name: 'lookupType' + me.lookupId,
                    value: 'category',
                    margin: 0
                },{
                    xtype: 'hiddenfield',
                    name: 'lookupNum',
                    type: 'auto',
                    value: me.lookupId,
                    margin: 0
                },{
                    xtype: 'combo',
                    name: 'fieldItem',
                    width: 225,
                    store: Ext.data.Store.create({
                        fields: ['name', 'showTitle'],
                        data: this.configData.fields
                    }),
                    queryMode: 'local',
                    displayField: 'showTitle',
                    allowBlank: false,
                    valueField: 'name',
                    listeners: {
                        select: function(combo) {
                            var panel = this.up('panel[cls~=ifresco-view-panel-lookup]');
                            panel.setTitle(panel.titlePart + ' | ' + combo.getValue());
                            Ext.each(panel.up('ifrescoViewLookups').query('combo[name=fieldItem]'), function (field) {
                                if (field.getValue() !== null) {
                                    field.validate();
                                }
                            });
                        }
                    },
                    validator: function() {
                        var returnValue = true;
                        var fieldLookup = this.up('panel[cls~=ifresco-view-panel-lookup]');
                        Ext.each(this.up('ifrescoViewLookups').query('panel[cls~=ifresco-view-panel-lookup]'), function (lookup) {
                            if (fieldLookup === lookup) {
                                return;
                            }
                            var field = lookup.down('combo[name=fieldItem]');
                            if (field.getValue() == this.getValue()) {
                                returnValue = Ifresco.helper.Translations.trans('One property per one lookup');
                                return false;
                            }
                        }, this);
                        return returnValue;
                    }
                },{
                    xtype: 'fieldcontainer',
                    defaultType: 'radiofield',
                    layout: 'hbox',
                    width: 225,
                    items: [{
                        boxLabel: Ifresco.helper.Translations.trans('Multiselect'),
                        name: 'singleSelect' + me.lookupId,
                        checked: true,
                        inputValue: 0
                    },{
                        boxLabel: Ifresco.helper.Translations.trans('Singleselect'),
                        name    : 'singleSelect' + me.lookupId,
                        margin: '0 0 0 5',
                        inputValue: 1
                    }]
                },{
                    xtype: 'fieldcontainer',
                    fieldLabel: Ifresco.helper.Translations.trans('Apply to'),
                    labelWidth: 50,
                    defaultType: 'radiofield',
                    width: 225,
                    layout: 'hbox',
                    items: [{
                        boxLabel: Ifresco.helper.Translations.trans('All'),
                        name: 'applyTo' + me.lookupId,
                        checked: true,
                        inputValue: 0
                    },{
                        boxLabel: Ifresco.helper.Translations.trans('Meta'),
                        name: 'applyTo' + me.lookupId,
                        margin: '0 0 0 5',
                        inputValue: 1
                    },{
                        boxLabel: Ifresco.helper.Translations.trans('Search'),
                        name: 'applyTo' + me.lookupId,
                        margin: '0 0 0 5',
                        inputValue: 2
                    }]
                }]
            },{
                xtype: 'container',
                columnWidth: 0.5,
                minWidth: 250,
                layout: 'anchor',
                items: [{
                    xtype: 'treepanel',
                    height: 200,
                    rootVisible: false,
                    categoryNodeId: null,
                    loaded: false,
                    isFormField: true,
                    activeError: null,
                    isValid: function() {
                        return this.disabled || Ext.isEmpty(this.getErrors());
                    },
                    isDirty: function() {
                        return false;
                    }, 
                    validate: function() {
                        var isValid = this.isValid();
                        if (isValid !== me.wasValid) {
                            this.wasValid = isValid;
                            this.fireEvent('validitychange', this, isValid);
                        }
                        return isValid;
                    },
                    getErrors: function() {
                        var msg;
                        var fieldValue = this.getChecked();
                        this.errors = [];

                        if (Ext.isFunction(this.validator)) {
                            msg = this.validator.call(this, fieldValue);
                            if (msg !== true) {
                                this.errors.push(msg);
                            }
                        }
                        return this.errors;
                    },
                    validator: function(value) {
                        if (this.categoryNodeId === null && value.length !== 1) {
                            return Ifresco.helper.Translations.trans('At least one category is required!');
                        }
                        return true;
                    },
                    isFileUpload: function() {return false;},
                    getSubmitData: function() {
                        var key = 'categoryNodeId' + me.lookupId,
                            returnValue = {},
                            values;
                        values = this.getChecked();
                        if (values.length) {
                            returnValue[key] = values[0].raw.nodeId;
                        } else if (this.categoryNodeId !== null) {
                            returnValue[key] = this.categoryNodeId;
                        } else {
                            return false;
                        }
                        return returnValue;
                    },
                    unsetActiveError: function() {
                        this.activeError = null;
                    },
                    setActiveError: function(error) {
                        this.activeError = error;
                    },
                    getActiveErrors: function() {
                        return [this.activeError];
                    },
                    store: {
                        proxy: {
                            url: Routing.generate('ifresco_client_admin_categories_tree_get'),
                            type: 'ajax',
                            actionMethods: 'POST'
                        },
                        root: {
                            nodeType: 'async',
                            id: 'root',
                            draggable: false,
                            text: 'CategoryTree',
                            expanded: true,
                            visible: false,
                            border: false
                        },
                        listeners: {
                            load: function(store, parentNode) {
                                var treePanel = me.down('treepanel');
                                if (me.lookupConfig && me.lookupConfig.categoryNodeId) {
                                    if (treePanel.getChecked().length == 0) {
                                        parentNode.eachChild(function(node) {
                                            if (node.raw.nodeId == me.lookupConfig.categoryNodeId) {
                                                node.set('checked', true);
                                            }
                                        });
                                    }
                                }
                                treePanel.loaded = true;
                                treePanel.setLoading(false);
                            }
                        }
                    },
                    listeners: {
                        afterrender: function(el) {
                            if (! el.loaded) {
                                el.setLoading(true, true);
                            }
                        },
                        checkchange: function(node, checked) {
                            if (this.categoryNodeId !== null) {
                                this.categoryNodeId = null;
                            }
                            if (checked == true) {
                                var treeChecked = this.getView().getChecked();
                                Ext.each(treeChecked, function(checkedNode) {
                                    if (node != checkedNode) {
                                        checkedNode.set('checked', false);
                                    }
                                });
                            }
                            this.validate();
                        },
                        validitychange: function(treepanel, isValid) {
                            if (isValid) {
                                this.removeCls('ifresco-treepanel-invalid');
                            } else {
                                this.addCls('ifresco-treepanel-invalid');
                            }

                        }
                    },
                    scope: this
                }]
            }],
            listeners: {
                afterrender: this.loadLookupConfig
            }
        });
        this.callParent();
    },

    loadLookupConfig: function() {
        var config = this.lookupConfig;
        var lookupId = this.lookupId;
        var setValues = {};
        if (config === null) {
            return;
        }

        setValues.fieldItem = config.fieldItem;
        setValues['applyTo' + lookupId] = config.applyTo;
        setValues['singleSelect' + lookupId] = config.singleSelect;
        this.getForm().setValues(setValues);
        var fieldItem = this.down('combo[name=fieldItem]');
        fieldItem.fireEvent('select', fieldItem);

        this.down('treepanel').categoryNodeId = config.categoryNodeId;
    }
});
