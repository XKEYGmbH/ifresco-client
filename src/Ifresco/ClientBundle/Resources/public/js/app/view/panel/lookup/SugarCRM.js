Ext.define('Ifresco.view.panel.lookup.SugarCRM', {
    extend: 'Ext.form.Panel',
    alias: 'widget.ifrescoViewPanelLookupSugarCRM',
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
            title: Ifresco.helper.Translations.trans('SugarCRM lookup'),
            titlePart: Ifresco.helper.Translations.trans('SugarCRM lookup'),
            scope: this,
            items: [{
                xtype: 'container',
                columnWidth: 0.4,
                minWidth: 250,
                layout: 'anchor',
                items: [{
                    xtype: 'hiddenfield',
                    name: 'lookupType' + me.lookupId,
                    value: 'sugar',
                    margin: 0
                },{
                    xtype: 'hiddenfield',
                    name: 'singleSelect' + me.lookupId,
                    value: 0,
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
                    }, {
                        boxLabel: Ifresco.helper.Translations.trans('Meta'),
                        name: 'applyTo' + me.lookupId,
                        margin: '0 0 0 5',
                        inputValue: 1
                    }, {
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
                        inputValue: 1
                    }]
                }]
	        },{
                xtype: 'container',
                columnWidth: 0.5,
                minWidth: 375,
                layout: 'anchor',
                items: [{
                    xtype: 'combo',
                    fieldLabel: Ifresco.helper.Translations.trans('Select Data Source'),
                    labelWidth:  150,
                    width: 375,
                    allowBlank: false,
                    queryMode: 'local',
                    name: 'sugarsource' + me.lookupId,
                    displayField: 'name',
                    valueField: 'id',
                    store: Ext.data.Store.create({
                        fields: ['id', 'name'],
                        data: this.configData.dataSources
                    })
                },{
                    xtype: 'textfield',
                    fieldLabel: Ifresco.helper.Translations.trans('Select Entity'),
                    labelWidth:  150,
                    width: 375,
                    allowBlank: false,
                    name: 'sugarentity' + me.lookupId
                },{
                    xtype: 'textfield',
                    fieldLabel: Ifresco.helper.Translations.trans('Select Column'),
                    labelWidth:  150,
                    width: 375,
                    allowBlank: false,
                    name: 'sugarfield' + me.lookupId
                },{
                    xtype: 'combo',
                    fieldLabel: Ifresco.helper.Translations.trans('Select Related Field'),
                    labelWidth:  150,
                    width: 375,
                    name: 'sugarrelated' + me.lookupId,
                    store: Ext.data.Store.create({
                        fields: ['name', 'showTitle'],
                        data: this.configData.fields
                    }),
                    queryMode: 'local',
                    allowBlank: false,
                    displayField: 'showTitle',
                    valueField: 'name'
                },{
                    xtype: 'textfield',
                    fieldLabel: Ifresco.helper.Translations.trans('Select Related Column'),
                    labelWidth:  150,
                    width: 375,
                    allowBlank: false,
                    name: 'sugarrelatedcolumn' + me.lookupId
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
        setValues['sugarentity' + lookupId] = config.dataSourceEntity;
        setValues['sugarfield' + lookupId] = config.dataSourceColumn;
        setValues['sugarrelatedcolumn' + lookupId] = config.relatedColumn;
        setValues['sugarrelated' + lookupId] = config.relatedField;
        this.getForm().setValues(setValues);
        var fieldItem = this.down('combo[name=fieldItem]');
        fieldItem.fireEvent('select', fieldItem);

        this.down('combo[name=sugarsource' + this.lookupId + ']')
            .select(config.dataSource);

    }
});