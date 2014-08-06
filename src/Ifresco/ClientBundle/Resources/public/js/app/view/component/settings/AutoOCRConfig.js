Ext.define('Ifresco.view.settings.AutoOCRConfig', {
    extend: 'Ext.form.Panel',
    alias: 'widget.ifrescoViewSettingsAutoOCRConfig',
    border: 0,
    defaults: {
        margin: 5
    },
    autoScroll: true,
    cls: 'ifresco-view-settings-autoocrconfig',
    initComponent: function () {
        Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('ifresco Transformer Config'),
            tbar: [{
            	iconCls: 'ifresco-icon-save',
                text: Ifresco.helper.Translations.trans('Save'),
                handler : function(){
                    this.fireEvent('save');
                },
                scope: this
            }],
            items: [{
                xtype: 'panel',
                layout: {
                    type: 'table',
                    columns: 2
                },
                defaults: {
                    padding: 5
                },
                border: 0,
                items: [{
                    xtype: 'container',
                    html: Ifresco.helper.Translations.trans('REST endpoint') + ':',
                    cellCls: 'ifresco-view-settings-row-left'
                },{
                    xtype: 'container',
                    layout: {
                        type: 'table',
                        columns: 2
                    },
                    width: '100%',
                    anchor: '100%',
                    border: 0,
                    cellCls: 'ifresco-view-settings-row-right',
                    items: [{
                        xtype: 'textfield',
                        name: 'endpoint'
                    }]
                },{
                    xtype: 'container',
                    border: 0,
                    colspan: 2,
                    anchor: '100%'
                },{
                    xtype: 'container',
                    html: Ifresco.helper.Translations.trans('HTTP connection timeout (ms)') + ':',
                    cellCls: 'ifresco-view-settings-row-left'
                },{
                    xtype: 'container',
                    layout: {
                        type: 'table',
                        columns: 2
                    },
                    width: '100%',
                    anchor: '100%',
                    border: 0,
                    cellCls: 'ifresco-view-settings-row-right',
                    items: [{
                        xtype: 'textfield',
                        name: 'connectiontimeout'
                    }]
                },{
                    xtype: 'container',
                    border: 0,
                    colspan: 2,
                    anchor: '100%'
                },{
                    xtype: 'container',
                    html: Ifresco.helper.Translations.trans('Username') + ':',
                    cellCls: 'ifresco-view-settings-row-left'
                },{
                    xtype: 'container',
                    layout: {
                        type: 'table',
                        columns: 2
                    },
                    width: '100%',
                    anchor: '100%',
                    border: 0,
                    cellCls: 'ifresco-view-settings-row-right',
                    items: [{
                        xtype: 'textfield',
                        name: 'username'
                    }]
                },{
                    xtype: 'container',
                    border: 0,
                    colspan: 2,
                    anchor: '100%'
                },{
                    xtype: 'container',
                    html: Ifresco.helper.Translations.trans('Password') + ':',
                    cellCls: 'ifresco-view-settings-row-left'
                },{
                    xtype: 'container',
                    layout: {
                        type: 'table',
                        columns: 2
                    },
                    width: '100%',
                    anchor: '100%',
                    border: 0,
                    cellCls: 'ifresco-view-settings-row-right',
                    items: [{
                        xtype: 'textfield',
                        inputType: 'password',
                        name: 'password'
                    }]
                },{
                    xtype: 'container',
                    border: 0,
                    colspan: 2,
                    anchor: '100%'
                },{
                    xtype: 'container',
                    html: Ifresco.helper.Translations.trans('Transformation timeout (ms)') + ':',
                    cellCls: 'ifresco-view-settings-row-left'
                },{
                    xtype: 'container',
                    layout: {
                        type: 'table',
                        columns: 2
                    },
                    width: '100%',
                    anchor: '100%',
                    border: 0,
                    cellCls: 'ifresco-view-settings-row-right',
                    items: [{
                        xtype: 'textfield',
                        name: 'timeout'
                    }]
                },{
                    xtype: 'container',
                    border: 0,
                    colspan: 2,
                    anchor: '100%'
                },{
                    xtype: 'container',
                    html: Ifresco.helper.Translations.trans('Poll time (ms)') + ':',
                    cellCls: 'ifresco-view-settings-row-left'
                },{
                    xtype: 'container',
                    layout: {
                        type: 'table',
                        columns: 2
                    },
                    width: '100%',
                    anchor: '100%',
                    border: 0,
                    cellCls: 'ifresco-view-settings-row-right',
                    items: [{
                        xtype: 'textfield',
                        name: 'sleeptime'
                    }]
                },{
                    xtype: 'container',
                    border: 0,
                    colspan: 2,
                    anchor: '100%'
                },{
                    xtype: 'container',
                    html: '',
                    cellCls: 'ifresco-view-settings-row-left'
                },{
                    xtype: 'container',
                    layout: {
                        type: 'table',
                        columns: 2
                    },
                    width: '100%',
                    anchor: '100%',
                    border: 0,
                    cellCls: 'ifresco-view-settings-row-right',
                    items: [{
                        xtype: 'button',
                        text: Ifresco.helper.Translations.trans('Test Connection'),
                        handler : function(){
                        	this.fireEvent('testconnection');
                        },
                        scope: this
                    }]
                },{
                    xtype: 'container',
                    border: 0,
                    colspan: 2,
                    anchor: '100%'
                },{
                    xtype: 'container',
                    html: Ifresco.helper.Translations.trans('APIKey') + ':',
                    cellCls: 'ifresco-view-settings-row-left'
                },{
                    xtype: 'container',
                    layout: {
                        type: 'table',
                        columns: 2
                    },
                    width: '100%',
                    anchor: '100%',
                    border: 0,
                    cellCls: 'ifresco-view-settings-row-right',
                    items: [{
                        xtype: 'textfield',
                        name: 'apiKey'
                    }]
                },{
                    xtype: 'container',
                    border: 0,
                    colspan: 2,
                    anchor: '100%'
                },{
                    xtype: 'container',
                    html: '',
                    cellCls: 'ifresco-view-settings-row-left'
                },{
                    xtype: 'container',
                    layout: {
                        type: 'table',
                        columns: 2
                    },
                    width: '100%',
                    anchor: '100%',
                    border: 0,
                    cellCls: 'ifresco-view-settings-row-right',
                    items: [{
                        xtype: 'button',
                        text: Ifresco.helper.Translations.trans('Test API Key'),
                        handler : function(){
                        	this.fireEvent('testapikey');
                        },
                        scope: this
                    }]
                }]
            }]
        });

        this.callParent();
    },

    scope: this
});
