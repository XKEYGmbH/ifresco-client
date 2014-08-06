Ext.define('Ifresco.view.panel.lookup.SimpleSQL', {
    extend: 'Ext.form.Panel',
    alias: 'widget.ifrescoViewPanelLookupSimpleSQL',
    border: 1,
    margin: 5,
    bodyPadding: 10,
    cls: 'ifresco-view-panel-lookup',
    closable: true,
    layout: 'column',
    configData: null,
    loadingCount: 0,
    lookupConfig: null,

    initComponent: function () {
        var me = this;
        this.lookupId = this.lookupId || parseInt(Math.random() * 1000000, 10);

        Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('Simple SQL lookup'),
            titlePart: Ifresco.helper.Translations.trans('Simple SQL lookup'),
            scope: this,
            items: [{
                scope: this,
                xtype: 'container',
                columnWidth: 0.4,
                minWidth: 250,
                layout: 'anchor',
                items: [{
                    xtype: 'hiddenfield',
                    name: 'lookupType' + me.lookupId,
                    value: 'datasource',
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
                    valueField: 'name',
                    allowBlank: false,
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
                        value: 0,
                        checked: true,
                        inputValue: 0
                    },{
                        boxLabel: Ifresco.helper.Translations.trans('Singleselect'),
                        name: 'singleSelect' + me.lookupId,
                        value: 1,
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
                },{
                    xtype: 'fieldcontainer',
                    defaultType: 'radiofield',
                    layout: 'hbox',
                    width: 225,
                    items: [{
                        boxLabel: Ifresco.helper.Translations.trans('Use cache'),
                        name    : 'cacheSelect' + me.lookupId,
                        value: 0,
                        checked: true,
                        inputValue: 0
                    },{
                        boxLabel: Ifresco.helper.Translations.trans('Use remote select'),
                        name    : 'cacheSelect' + me.lookupId,
                        value: 1,
                        margin: '0 0 0 5',
                        inputValue: 1
                    }]
                }]
            },{
                scope: this,
                xtype: 'container',
                columnWidth: 0.5,
                minWidth: 375,
                layout: 'anchor',
                items: [{
                    xtype: 'combo',
                    fieldLabel: Ifresco.helper.Translations.trans('Select Data Source'),
                    labelWidth:  150,
                    width: 375,
                    name: 'datasource' + me.lookupId,
                    displayField: 'name',
                    valueField: 'id',
                    store: Ext.data.Store.create({
                        fields: ['id', 'name'],
                        data: this.configData.dataSources
                    }),
                    queryMode: 'local',
                    editable: false,
                    allowBlank: false,
                    listeners: {
                        select: function(combo, records) {
                            this.loadTables(records[0].data.id, null, true);
                        },
                        scope: this
                    }
                },{
                    xtype: 'combo',
                    fieldLabel: 'Select table',
                    labelWidth: 150,
                    width: 375,
                    name: 'datasourcetable' + me.lookupId,
                    queryMode: 'local',
                    store: {
                        fields: ['name'],
                        proxy: {
                            type: 'memory'
                        }
                    },
                    displayField: 'name',
                    valueField: 'name',
                    editable: false,
                    allowBlank: false,
                    disabled: true,
                    listeners: {
                        select: function(combo, records) {
                            var dataSourceId = this.down('combo[name=' + 'datasource' + me.lookupId + ']').getValue();
                            this.loadColumns(dataSourceId, records[0].data.name);
                        },
                        scope: this
                    }
                },{
                    xtype: 'combo',
                    fieldLabel: Ifresco.helper.Translations.trans('Select column'),
                    labelWidth: 150,
                    width: 375,
                    name: 'datasourcecolumn' + me.lookupId,
                    queryMode: 'local',
                    store: {
                        fields: ['name'],
                        proxy: {
                            type: 'memory'
                        }
                    },
                    editable: false,
                    allowBlank: false,
                    disabled: true,
                    displayField: 'name',
                    valueField: 'name'
                }]
            }],
            listeners: {
                afterrender: this.loadLookupConfig
            }
        });

        this.callParent();
    },

    loadTables: function(dataSourceId, tableName, hideColumn, mask) {
        var panel = this;
        var dataSourceTable = panel.down('combo[name=datasourcetable' + this.lookupId + ']');
        dataSourceTable.unsetActiveError();
        if (mask) {
            ++panel.loadingCount;
            panel.setLoading(true, true);
        }
        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_admin_data_sources_tables_get'),
            params: {
                dataSourceId: dataSourceId 
            },
            success: function(response) {
                var data = Ext.decode(response.responseText);
                dataSourceTable.getStore().loadData(data.data);
                dataSourceTable.enable();
                if (tableName) {
                    dataSourceTable.select(tableName);
                }
                if (mask) {
                    --panel.loadingCount;
                    if (panel.loadingCount == 0) {
                        panel.setLoading(false);
                    }
                }
            }
        });
        if (hideColumn) {
            panel.down('combo[name=datasourcecolumn' + this.lookupId + ']').disable();
        }
    },

    loadColumns: function(dataSourceId, tableName, columnName, mask) {
        var panel = this;
        var dataSourceColumn = panel.down('combo[name=datasourcecolumn' + this.lookupId + ']');
        dataSourceColumn.unsetActiveError();
        if (mask) {
            ++panel.loadingCount;
            panel.setLoading(true, true);
        }
        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_admin_data_sources_columns_get'),
            params: {
                dataSourceId: dataSourceId,
                tbl: tableName
            },
            success: function(response) {
                var data = Ext.decode(response.responseText);
                dataSourceColumn.getStore().loadData(data.data);
                dataSourceColumn.enable();
                if (columnName) {
                    dataSourceColumn.select(columnName);
                }
                if (mask) {
                    --panel.loadingCount;
                    if (panel.loadingCount == 0) {
                        panel.setLoading(false);
                    }
                }
            }
        });
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
        setValues['cacheSelect' + lookupId] = config.cacheSelect;
        setValues['singleSelect' + lookupId] = config.singleSelect;
        this.getForm().setValues(setValues);
        var fieldItem = this.down('combo[name=fieldItem]');
        fieldItem.fireEvent('select', fieldItem);

        this.down('combo[name=datasource' + this.lookupId + ']')
            .select(config.dataSource);

        this.loadTables(config.dataSource, config.dataSourceTable, false, true);
        this.loadColumns(
            config.dataSource, 
            config.dataSourceTable, 
            config.dataSourceColumn,
            true
        );
    }
});
