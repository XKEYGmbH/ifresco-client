Ext.define('Ifresco.view.settings.AutoOCRStatus', {
    extend: 'Ext.form.Panel',
    alias: 'widget.ifrescoViewSettingsAutoOCRStatus',
    border: 0,
    defaults: {
        margin: 5
    },
    autoScroll: true,
    cls: 'ifresco-view-settings-autoocrstatus',
    initComponent: function () {
        Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('ifresco Transformer Status'),
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
                    html: Ifresco.helper.Translations.trans('Enable ifresco Transformer') + ':',
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
                        xtype: 'checkboxfield',
                        name: 'enabled',
                        inputValue: 'true'
                    }]
                },{
                    xtype: 'container',
                    border: 0,
                    colspan: 2,
                    anchor: '100%'
                },{
                    xtype: 'container',
                    html: Ifresco.helper.Translations.trans('Server Status') + ':',
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
                        xtype: 'container',
                        itemId: 'server'
                    }]
                },{
                    xtype: 'container',
                    border: 0,
                    colspan: 2,
                    anchor: '100%'
                },{
                    xtype: 'container',
                    html: Ifresco.helper.Translations.trans('Version') + ':',
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
                        xtype: 'container',
                        itemId: 'version'
                    }]
                },{
                    xtype: 'container',
                    border: 0,
                    colspan: 2,
                    anchor: '100%'
                },{
                    xtype: 'container',
                    html: Ifresco.helper.Translations.trans('Pages left') + ':',
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
                        xtype: 'container',
                        itemId: 'pages'
                    }]
                }]
            }]
        });

        this.callParent();
    },

    scope: this
});
