Ext.define('Ifresco.view.panel.lookup.RelationSQL', {
    extend: 'Ext.form.Panel',
    alias: 'widget.ifrescoViewPanelLookupRelationSQL',
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
            title: Ifresco.helper.Translations.trans('Relation SQL lookup'),
            titlePart: Ifresco.helper.Translations.trans('Relation SQL lookup'),
            scope: this,
            items: [{
                xtype: 'container',
                columnWidth: 0.4,
                minWidth: 250,
                layout: 'anchor',
                items: [{
                    xtype: 'hiddenfield',
                    name: 'lookupType' + me.lookupId,
                    value: 'datasourcerel',
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
                        name: 'cacheSelect' + me.lookupId,
                        value: 0,
                        checked: true,
                        inputValue: 0
                    },{
                        boxLabel: Ifresco.helper.Translations.trans('Use remote select'),
                        name: 'cacheSelect' + me.lookupId,
                        value: 1,
                        margin: '0 0 0 5',
                        inputValue: 1,
                        listeners: {
                            change: function (combo, value) {
                                me.showMappings(value);
                            }
                        }
                    }]
                }]
	        },{
                scope: this,
                xtype: 'container',
                columnWidth: 0.6,
                minWidth: 500,
                layout: 'anchor',
                items: [{
                    xtype: 'combo',
                    fieldLabel: Ifresco.helper.Translations.trans('Select Data Source'),
                    labelWidth:  150,
                    width: 375,
                    name: 'datasource' + me.lookupId,
                    scope: this,
                    displayField: 'name',
                    valueField: 'id',
                    allowBlank: false,
                    store: Ext.data.Store.create({
                        fields: ['id', 'name'],
                        data: this.configData.dataSources
                    }),
                    queryMode: 'local',
                    editable: false,
                    listeners: {
                        select: function(combo, records) {
                            this.loadTables(records[0].data.id, true);
                            //panel.down('combo[name=datasourcecolumn]').hide();
                        },
                        scope: this
                    }
                },{
                    xtype: 'container',
                    layout: 'hbox',
                    items: [{
                        xtype: 'combo',
                        fieldLabel: Ifresco.helper.Translations.trans('Select Tables'),
                        labelWidth: 150,
                        queryMode: 'local',
                        store: {
                            fields: ['name'],
                            proxy: {
                                type: 'memory'
                            }
                        },
                        allowBlank: false,
                        displayField: 'name',
                        valueField: 'name',
                        name: 'datasourcetable' + me.lookupId,
                        disabled: true,
                        listeners: {
                            select: function(combo, records) {
                                var dataSourceId = this.down('combo[name=' + 'datasource' + me.lookupId + ']').getValue();
                                this.loadColumns1(dataSourceId, records[0].data.name);
                            },
                            scope: this
                        }
                    },{
                        xtype: 'combo',
                        name: 'datasourcetable2' + me.lookupId,
                        fieldLabel: '->',
                        labelSeparator: '',
                        labelWidth: 15,
                        margin: '0 0 5 5',
                        queryMode: 'local',
                        allowBlank: false,
                        store: {
                            fields: ['name'],
                            proxy: {
                                type: 'memory'
                            }
                        },
                        displayField: 'name',
                        valueField: 'name',
                        disabled: true,
                        listeners: {
                            select: function(combo, records) {
                                var dataSourceId = this.down('combo[name='+ 'datasource' + me.lookupId + ']').getValue();
                                this.loadColumns2(dataSourceId, records[0].data.name);
                            },
                            scope: this
                        }
                    }]
                },{
                    xtype: 'container',
                    layout: 'hbox',
                    items: [{
                        fieldLabel: Ifresco.helper.Translations.trans('Select Search Columns'),
                        labelWidth: 150,
                        xtype: 'combo',
                        name: 'datasourcecolumn' + me.lookupId,
                        queryMode: 'local',
                        allowBlank: false,
                        store: {
                            fields: ['name'],
                            proxy: {
                                type: 'memory'
                            }
                        },
                        displayField: 'name',
                        valueField: 'name',
                        disabled: true
                    },{
                        xtype: 'combo',
                        fieldLabel: '->',
                        labelSeparator: '',
                        labelWidth: 15,
                        margin: '0 0 5 5',
                        name: 'datasourcecolumn2' + me.lookupId,
                        allowBlank: false,
                        queryMode: 'local',
                        store: {
                            fields: ['name'],
                            proxy: {
                                type: 'memory'
                            }
                        },
                        displayField: 'name',
                        valueField: 'name',
                        disabled: true
                    }]
                },{
                    xtype: 'container',
                    layout: 'hbox',
                    items: [{
                        fieldLabel: Ifresco.helper.Translations.trans('Select link Columns'),
                        labelWidth: 150,
                        xtype: 'combo',
                        name: 'datasourcecolumnrel' + me.lookupId,
                        queryMode: 'local',
                        allowBlank: false,
                        store: {
                            fields: ['name'],
                            proxy: {
                                type: 'memory'
                            }
                        },
                        displayField: 'name',
                        valueField: 'name',
                        disabled: true
                    },{
                        xtype: 'combo',
                        name: "datasourcecolumnrel2" + me.lookupId,
                        fieldLabel: '->',
                        labelSeparator: '',
                        labelWidth: 15,
                        allowBlank: false,
                        margin: '0 0 5 5',
                        queryMode: 'local',
                        store: {
                            fields: ['name'],
                            proxy: {
                                type: 'memory'
                            }
                        },
                        displayField: 'name',
                        valueField: 'name',
                        disabled: true
                    }]
                },{
                    xtype: 'combo',
                    fieldLabel: Ifresco.helper.Translations.trans('Select Related field'),
                    labelWidth:  150,
                    width: 375,
                    name: 'datasourcerelatedcolumn' + me.lookupId,
                    allowBlank: false,                       
                    store: Ext.data.Store.create({
                        fields: ['name', 'showTitle'],
                        data: this.configData.fields
                    }),
                    queryMode: 'local',
                    displayField: 'showTitle',
                    valueField: 'name'
                },{
                    xtype: 'container',
                    cls: 'ifresco-lookup-fields-mapping',
                    hidden: true,
                    items: [{
                        xtype: 'button',
                        text: Ifresco.helper.Translations.trans('Map fields'),
                        margin: 5,
                        handler: function () {
                            me.updateMappingFields();
                        }
                    }]
                }]
            }],
            listeners: {
                afterrender: this.loadLookupConfig
            }
        });
        this.callParent();
    },

    loadTables: function(dataSourceId, hideColumns, tableName1, tableName2, mask) {
        var dataSourceTable = this.down('combo[name=datasourcetable' + this.lookupId + ']');
        var dataSourceTable2 = this.down('combo[name=datasourcetable2' + this.lookupId + ']');
        var dataSourceColumn = this.down('combo[name=datasourcecolumn' + this.lookupId + ']');
        var dataSourceColumn2 = this.down('combo[name=datasourcecolumn2' + this.lookupId + ']');
        var dataSourceColumnRel = this.down('combo[name=datasourcecolumnrel' + this.lookupId + ']');
        var dataSourceColumnRel2 = this.down('combo[name=datasourcecolumnrel2' + this.lookupId + ']');
        var panel = this;
        if (mask) {
            ++this.loadingCount;
            if (this.loadingCount == 1) {
                panel.setLoading(true);
            }
        }
        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_admin_data_sources_tables_get'),
            params: {
                dataSourceId: dataSourceId 
            },
            success: function(response) {
                if(mask) {
                    --panel.loadingCount;
                    if (panel.loadingCount == 0) {
                        panel.setLoading(false);
                    }
                }
                var data = Ext.decode(response.responseText);
                if (data.data.length) {
                    dataSourceTable.getStore().loadData(data.data);
                    dataSourceTable2.getStore().loadData(data.data);
                    dataSourceTable.enable();
                    dataSourceTable2.enable();
                } else {
                    dataSourceTable.getStore().removeAll();
                    dataSourceTable.setValue('');
                    dataSourceTable.disable();
                    dataSourceTable2.getStore().removeAll();
                    dataSourceTable2.setValue('');
                    dataSourceTable2.disable();
                }
                if (tableName1) {
                    dataSourceTable.select(tableName1);
                }
                if (tableName2) {
                    dataSourceTable2.select(tableName2);
                }
                if (hideColumns) {
                    dataSourceColumn.setValue('');
                    dataSourceColumn.getStore().removeAll();
                    dataSourceColumn.disable();
                    dataSourceColumn2.setValue('');
                    dataSourceColumn2.getStore().removeAll();
                    dataSourceColumn2.disable();
                    dataSourceColumnRel.setValue('');
                    dataSourceColumnRel.getStore().removeAll();
                    dataSourceColumnRel.disable();
                    dataSourceColumnRel2.setValue('');
                    dataSourceColumnRel2.getStore().removeAll();
                    dataSourceColumnRel2.disable();
                }
            }
        });
    },

    loadColumns1: function(dataSourceId, tableName, columnName, columnRelName, mask) {
        var dataSourceColumn = this.down('combo[name=datasourcecolumn' + this.lookupId + ']');
        var dataSourceColumnRel = this.down('combo[name=datasourcecolumnrel' + this.lookupId + ']');
        var panel = this;
        if (mask) {
            ++this.loadingCount;
            if (this.loadingCount == 1) {
                panel.setLoading(true);
            }
        }
        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_admin_data_sources_columns_get'),
            params: {
                dataSourceId: dataSourceId,
                tbl: tableName
            },
            success: function(response) {
                if(mask) {
                    --panel.loadingCount;
                    if (panel.loadingCount == 0) {
                        panel.setLoading(false);
                    }
                }
                var data = Ext.decode(response.responseText);
                dataSourceColumn.getStore().loadData(data.data);
                dataSourceColumnRel.getStore().loadData(data.data);
                dataSourceColumn.enable();
                dataSourceColumnRel.enable();
                if (columnName) {
                    dataSourceColumn.select(columnName);
                }
                if (columnRelName) {
                    dataSourceColumnRel.select(columnRelName);
                }
            }
        });
    },

    loadColumns2: function(dataSourceId, tableName, columnName, columnRelName, mask) {
        var dataSourceColumn = this.down('combo[name=datasourcecolumn2' + this.lookupId + ']');
        var dataSourceColumnRel = this.down('combo[name=datasourcecolumnrel2' + this.lookupId + ']');
        var panel = this;
        if (mask) {
            ++this.loadingCount;
            if (this.loadingCount == 1) {
                panel.setLoading(true);
            }
        }
        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_admin_data_sources_columns_get'),
            params: {
                dataSourceId: dataSourceId,
                tbl: tableName
            },
            success: function(response) {
                if(mask) {
                    --panel.loadingCount;
                    if (panel.loadingCount == 0) {
                        panel.setLoading(false);
                    }
                }
                var data = Ext.decode(response.responseText);
                dataSourceColumn.getStore().loadData(data.data);
                dataSourceColumnRel.getStore().loadData(data.data);
                dataSourceColumn.enable();
                dataSourceColumnRel.enable();
                if (columnName) {
                    dataSourceColumn.select(columnName);
                }
                if (columnRelName) {
                    dataSourceColumnRel.select(columnRelName);
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

        this.loadTables(
            config.dataSource, 
            false,
            config.dataSourceTable, 
            config.dataSourceTable2,
            true
        );

        this.loadColumns1(
            config.dataSource, 
            config.dataSourceTable, 
            config.dataSourceColumn, 
            config.dataSourceColumnRel,
            true
        );

        this.loadColumns2(
            config.dataSource, 
            config.dataSourceTable2, 
            config.dataSourceColumn2, 
            config.dataSourceColumnRel2,
            true
        );

        this.down('combo[name=datasourcerelatedcolumn' + this.lookupId + ']')
            .select(config.relatedField);

        Ext.iterate(config.fieldsMap, function (fieldKey,fieldValue) {
            this.createMappingField(fieldKey, fieldValue);
        }, this);
        
        //this.buildMappingFields(config.dataSource, config.dataSourceTable, config.dataSourceTable2, config.dataSourceColumn, config.dataSourceColumn2);
        
    },

    showMappings: function (isEnabled) {
        var container = this.down('container[cls~=ifresco-lookup-fields-mapping]');
        if (isEnabled) {
            container.show();
        } else {
            container.hide();
        }
    },
    
    buildMappingFields: function(dataSourceId, table1, table2, column1, column2) {
    	console.log("BUILD MAPPING");
    	var me = this;
    	var config = this.lookupConfig;
    	var container = this.down('container[cls~=ifresco-lookup-fields-mapping]');
    	
    	var queryStr = '* FROM ';
    	
    	queryStr += table1 + ' t1 ';
        queryStr += 'INNER JOIN ';
        queryStr += table2 + ' t2 ';
        queryStr += 'ON t1.' + column1;
        //queryStr += 'ON t1.' + column1 + ' = t2.' + column2;

        if (dataSourceId) {
            Ext.Ajax.request({
                useCache: false,
                url: Routing.generate('ifresco_client_admin_query_fields_get'),
                params: {
                    queryStr: queryStr,
                    datasource: dataSourceId
                },
                success: function (response) {
                    var columns = Ext.decode(response.responseText).cols;
                    Ext.each(container.query('combo, hiddenfield'), function (combo) {
                        combo.destroy();
                    });
                    Ext.each(columns, function (column) {
                    	
                        /*var value = '';
                        
                        Ext.iterate(config.fieldsMap, function (fieldKey,fieldValue) {

                            if (fieldKey == column) 
                            	value = fieldValue;
                        });*/
                        
                        me.createMappingField(column);
                    });
                }
            });
        }
    },

    updateMappingFields: function () {
    	console.log("UDATE MAPPING");
        var me = this;
        
        var dataSourceId = parseInt(this.down('combo[name=datasource' + this.lookupId + ']').getValue(), 10);
        var table1 = this.down('combo[name=datasourcetable' + this.lookupId + ']').getValue();
        if (!table1) {
            return;
        }
        var table2 = this.down('combo[name=datasourcetable2' + this.lookupId + ']').getValue();
        if (!table2) {
            return;
        }
        var column1 = this.down('combo[name=datasourcecolumn' + this.lookupId + ']').getValue();
        if (!column1) {
            return;
        }
        var column2 = this.down('combo[name=datasourcecolumn' + this.lookupId + ']').getValue();
        if (!column2) {
            return;
        }

        this.buildMappingFields(dataSourceId, table1, table2, column1, column2);
        
    },

    createMappingField: function (column, value) {
        var container = this.down('container[cls~=ifresco-lookup-fields-mapping]');
        container.add([{
            xtype: 'combo',
            fieldLabel: column,
            width: 375,
            name: 'fieldsMap' + this.lookupId,
            store: Ext.data.Store.create({
                fields: ['name', 'showTitle'],
                data: this.configData.fields
            }),
            queryMode: 'local',
            displayField: 'showTitle',
            valueField: 'name',
            value: value || ''
        },{
            xtype: 'hiddenfield',
            name: 'fieldsMapCols' + this.lookupId,
            value: column
        }]);
    }
});
