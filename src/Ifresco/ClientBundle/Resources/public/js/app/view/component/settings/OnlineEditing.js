Ext.define('Ifresco.view.settings.OnlineEditing', {
    extend: 'Ext.form.Panel',
    alias: 'widget.ifrescoViewSettingsOnlineEditing',
    border: 0,
    defaults: {
        margin: 5
    },
    autoScroll: true,
    cls: 'ifresco-view-settings-propertyfilter',
    initComponent: function() {
        Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('Online Editing'),
            tbar: [{
            	iconCls: 'ifresco-icon-save',
            	text: Ifresco.helper.Translations.trans('Save'),
                handler: function() {
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
                cls: 'ifresco-view-table-settings-system',
                items: [{
                    xtype: 'box',
                    html: Ifresco.helper.Translations.trans('Online Editing') + ':',
                    cellCls: 'ifresco-view-settings-row-left',
                    width: 200
                },{
                    xtype: 'container',

                    border: 0,
                    cellCls: 'ifresco-view-settings-row-right',
                    cls: 'ifresco-view-prop-items',
                    items: [{
                        xtype: 'radio',
                        boxLabel: Ifresco.helper.Translations.trans('Not active'),
                        name: 'OnlineEditing',
                        inputValue: 'none'
                    },{
                        xtype: 'radio',
                        boxLabel: Ifresco.helper.Translations.trans('Zoho Writer'),
                        name: 'OnlineEditing',
                        inputValue: 'zoho'
                    }, {
                        xtype: 'image',
                        src: '/bundles/ifrescoclient/images/zoho.png', // TODO: route.better to make throw css styles
                        height: 60
                    },{
                        xtype: 'textfield',
                        name: 'OnlineEditingZohoApiKey',
                        fieldLabel: 'ApiKey'
                    },{
                        xtype: 'textfield',
                        name: 'OnlineEditingZohoSkey',
                        fieldLabel: 'Skey'
                    }]
                }]
            }]
        });

        this.callParent();
    },

    scope: this
});