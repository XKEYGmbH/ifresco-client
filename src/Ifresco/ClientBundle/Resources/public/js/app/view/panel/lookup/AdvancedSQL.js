Ext.define('Ifresco.view.panel.lookup.AdvancedSQL', {
    extend: 'Ext.form.Panel',
    alias: 'widget.ifrescoViewPanelLookupAdvancedSQL',
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
            title: Ifresco.helper.Translations.trans('Advanced SQL lookup'),
            titlePart: Ifresco.helper.Translations.trans('Advanced SQL lookup'),
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
                    name: 'datasourcetable' + me.lookupId,
                    value: 'sql',
                    margin: 0
                },{
                    xtype: 'hiddenfield',
                    name: 'datasourcecolumn' + me.lookupId,
                    value: 'sql',
                    margin: 0
                },{
                    xtype: 'hiddenfield',
                    name: 'lookupNum',
                    type: 'auto',
                    value: this.lookupId,
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
                    xtype      : 'fieldcontainer',
                    defaultType: 'radiofield',
                    layout: 'hbox',
                    width: 225,
                    items: [{
                        boxLabel: Ifresco.helper.Translations.trans('Multiselect'),
                        name    : 'singleSelect' + me.lookupId,
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
                    fieldLabel : Ifresco.helper.Translations.trans('Apply to'),
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
                columnWidth: 0.5,
                minWidth: 375,
                layout: 'anchor',

                items: [{
                    xtype: 'combo',
                    fieldLabel: Ifresco.helper.Translations.trans('Select Data Source'),
                    labelWidth:  150,
                    width: 375,
                    queryMode: 'local',
                    name: 'datasource' + me.lookupId,
                    displayField: 'name',
                    valueField: 'id',
                    allowBlank: false,
                    store: Ext.data.Store.create({
                        fields: ['id', 'name'],
                        data: this.configData.dataSources
                    })
                },{
                    xtype: 'textfield',
                    fieldLabel: 'SELECT',
                    labelAlign: 'top',
                    width: 375,
                    allowBlank: false,
                    name: 'datasourcesql' + me.lookupId,
                    value: 'column1 FROM table1',
                    scope:this
                },{
                    xtype: 'textfield',
                    fieldLabel: 'WHERE',
                    labelAlign: 'top',
                    width: 375,
                    allowBlank: false,
                    name: 'datasourcesqlwhere' + me.lookupId,
                    cls: 'ifresco-view-form-lookup-advancedsql-where',
                    value: "column1 LIKE '%{0}%'",
				    scope:this
                },{
                	xtype: 'text',
                	text: Ifresco.helper.Translations.trans('- use {0} as define parameter'),
                	cls: 'ifresco-lookup-field-tooltip'
                },{
                    xtype: 'container',
                    cls: 'ifresco-lookup-fields-mapping',
                    hidden: true,
                    items: [{
                        xtype: 'button',
                        margin: 5,
                        text: Ifresco.helper.Translations.trans('Map fields'),
                        handler: function (btn) {
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
        setValues['datasourcesql' + lookupId] = config.dataSourceSqlSelect;
        setValues['datasourcesqlwhere' + lookupId] = config.dataSourceSqlWhere;
        this.getForm().setValues(setValues);
        var fieldItem = this.down('combo[name=fieldItem]');
        fieldItem.fireEvent('select', fieldItem);

        this.down('combo[name=datasource' + this.lookupId + ']')
            .select(config.dataSource);

        /*Ext.each(config.fieldsMap, function (field) {
            this.createMappingField(field.key, field.value);
        }, this);*/
        
        Ext.iterate(config.fieldsMap, function (fieldKey,fieldValue) {
            this.createMappingField(fieldKey, fieldValue);
        }, this);
    },

    showMappings: function (isEnabled) {
        var container = this.down('container[cls~=ifresco-lookup-fields-mapping]');
        if (isEnabled) {
            container.show();
        } else {
            container.hide();
        }
    },

    updateMappingFields: function () {
        var me = this;
        var container = this.down('container[cls~=ifresco-lookup-fields-mapping]');
        var dataSourceId = parseInt(this.down('combo[name=datasource' + this.lookupId + ']').getValue(), 10);
        var queryStr = '';
        queryStr += this.down('textfield[name=datasourcesql' + this.lookupId + ']').getValue();
        //queryStr += ' WHERE ';
        //queryStr += this.down('textfield[name=datasourcesqlwhere' + this.lookupId + ']').getValue();

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
                        me.createMappingField(column);
                    });
                }
            });
        }
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
