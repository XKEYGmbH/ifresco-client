Ext.define('Ifresco.view.Lookups', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.ifrescoViewLookups',
    border: 0,
    cls: 'ifresco-view-lookups',
    layout: {
        type: 'vbox',
        align: 'stretch'
    },
    autoScroll: true,
    loaded: false,

    configData: null,

    initComponent: function () {
        Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('Lookups'),
            tbar: [{
            	iconCls: 'ifresco-icon-save',
            	text: Ifresco.helper.Translations.trans('Save'),
                formBind: true,
                handler : function(){
                    this.fireEvent('save');
                },
                scope: this
            },'-',{
                xtype: 'button',
                text: 'Category lookup',
                handler: function() {
                    this.add({xtype: 'ifrescoViewPanelLookupCategory', configData: this.configData});
                },
                scope: this
            },{
                xtype: 'button',
                text: 'User lookup',
                handler: function() {
                    this.add({xtype: 'ifrescoViewPanelLookupUser', configData: this.configData});
                },
                scope: this
            },{
                xtype: 'button',
                text: 'SugarCRM lookup',
                handler: function() {
                    this.add({xtype: 'ifrescoViewPanelLookupSugarCRM', configData: this.configData});
                },
                scope: this
            },{
                xtype: 'button',
                text: 'SimpleSQL lookup',
                handler: function() {
                    this.add({xtype: 'ifrescoViewPanelLookupSimpleSQL', configData: this.configData});
                },
                scope: this
            },{
                xtype: 'button',
                text: 'AdvancedSQL lookup',
                handler: function() {
                    this.add({xtype: 'ifrescoViewPanelLookupAdvancedSQL', configData: this.configData});
                },
                scope: this
            },{
                xtype: 'button',
                text: 'RelationSQL lookup',
                handler: function() {
                    this.add({xtype: 'ifrescoViewPanelLookupRelationSQL', configData: this.configData});
                },
                scope: this
            }],
            items: this.loadLookups(),
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

    loadLookups: function() {
        var items = [];
        var lookups = this.configData.lookups;
        Ext.each(lookups, function(lookup) {
            var data, 
                params,
                lookupPanel;

            var values = {};
            var lookupId = parseInt(Math.random() * 1000000, 10);

            values.fieldItem = lookup.field;

            switch(lookup.type) {
            case 'user':
                values.applyTo = lookup.applyto + 0;
                values.singleSelect = lookup.single + 0;

                lookupPanel = {
                    xtype: 'ifrescoViewPanelLookupUser',
                    configData: this.configData,
                    lookupId: lookupId,
                    lookupConfig: values
                };

                break;
            case 'datasource': 
                if (lookup.params) {
                    values.applyTo = lookup.applyto + 0;
                    values.singleSelect = lookup.single + 0;
                    values.cacheSelect = lookup.usecache + 0;

                    data = lookup.data.split('/');
                    values.dataSource = parseInt(lookup.data[0], 10);
                    params = Ext.decode(lookup.params);
                    values.dataSourceSqlSelect = params.sql;
                    values.dataSourceSqlWhere = params.where;
                    values.fieldsMap = params.relMap;

                    lookupPanel = {
                        xtype: 'ifrescoViewPanelLookupAdvancedSQL',
                        configData: this.configData,
                        lookupId: lookupId,
                        lookupConfig: values
                    };
                }
                else {
                    values.applyTo = lookup.applyto + 0;
                    values.singleSelect = lookup.single + 0;
                    values.cacheSelect = lookup.usecache + 0;

                    data = lookup.data.split('/');
                    values.dataSource = parseInt(data[0], 10);
                    values.dataSourceTable = data[1];
                    values.dataSourceColumn = data[2];

                    lookupPanel = {
                        xtype: 'ifrescoViewPanelLookupSimpleSQL',
                        configData: this.configData,
                        lookupId: lookupId,
                        lookupConfig: values
                    };
                }
                break;
            case 'category':
                values.applyTo = lookup.applyto + 0;
                values.singleSelect = lookup.single + 0;
                values.categoryNodeId = lookup.data;

                lookupPanel = {
                    xtype: 'ifrescoViewPanelLookupCategory',
                    configData: this.configData,
                    lookupId: lookupId,
                    lookupConfig: values
                };
                break;
            case 'sugar':
                values.applyTo = lookup.applyto + 0;
                values.cacheSelect = lookup.usecache + 0;

                data = lookup.data.split('/');
                params = Ext.decode(lookup.params);

                values.dataSource = parseInt(data[0], 10);
                values.dataSourceEntity = data[1];
                values.dataSourceColumn = data[2];
                values.relatedField = params.relatedfield;
                values.relatedColumn = params.relatedcolumn;

                lookupPanel = {
                    xtype: 'ifrescoViewPanelLookupSugarCRM',
                    configData: this.configData,
                    lookupId: lookupId,
                    lookupConfig: values
                };

                break;
            case 'datasourcerel':
                values.applyTo = lookup.applyto + 0;
                values.singleSelect = lookup.single + 0;
                values.cacheSelect = lookup.usecache + 0;
                values.dataSource = parseInt(lookup.data, 10);

                params = Ext.decode(lookup.params);

                values.dataSourceTable = params.t1.table;
                values.dataSourceColumn = params.t1.col;
                values.dataSourceColumnRel = params.t1.colRel;
                values.dataSourceTable2 = params.t2.table;
                values.dataSourceColumn2 = params.t2.col;
                values.dataSourceColumnRel2 = params.t2.colRel;
                values.relatedField = params.relatedcolumn;
                values.fieldsMap = params.relMap;

                lookupPanel = {
                    xtype: 'ifrescoViewPanelLookupRelationSQL',
                    configData: this.configData,
                    lookupId: lookupId,
                    lookupConfig: values
                };
                break;
            }

            items.push(lookupPanel);
        }, this);

        this.loaded = true;
        this.setLoading(false);
        return items;
    }
});
