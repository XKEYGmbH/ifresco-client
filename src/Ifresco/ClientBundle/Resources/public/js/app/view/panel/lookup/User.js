Ext.define('Ifresco.view.panel.lookup.User', {
    extend: 'Ext.form.Panel',
    alias: 'widget.ifrescoViewPanelLookupUser',
    border: 1,
    margin: 5,
    bodyPadding: 10,
    cls: 'ifresco-view-panel-lookup',
    closable: true,
    closeAction: 'destroy',
    layout: 'column',
    configData: null,
    lookupConfig: null,

    initComponent: function () {
        var me = this;
    	this.lookupId = this.lookupId || parseInt(Math.random() * 1000000, 10);
        
    	Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('User lookup'),
            titlePart: Ifresco.helper.Translations.trans('User lookup'),
		    items: [{
	            xtype: 'container',
	            columnWidth: 0.5,
                minWidth: 250,
	            layout: 'anchor',
	            items: [{
                    xtype: 'hiddenfield',
                    name: 'lookupType' + me.lookupId,
                    value: 'user',
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
                    allowBlank: false,
                    queryMode: 'local',
                    displayField: 'showTitle',
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
                        try {
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
                        }
                        catch (message){
                            console.log(this);
                        }
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
    }

});
