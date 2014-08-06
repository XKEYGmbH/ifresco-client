Ext.define('Ifresco.view.settings.ContentTypes', {
    extend: 'Ext.form.Panel',
    alias: 'widget.ifrescoViewSettingsContentTypes',
    border: 0,
    defaults: {
        margin: 5
    },
    height: '100%',
    width: '100%',
    layout: 'fit',
    configData: null,
    cls: 'ifresco-view-settings-contenttypes',
    initComponent: function () {
        Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('Content Types'),
            tbar: [{
            	iconCls: 'ifresco-icon-save',
                text: Ifresco.helper.Translations.trans('Save'),
                handler: function() {
                    this.fireEvent('save');
                },
                scope: this
            },{
            	iconCls: 'ifresco-icon-cancel',
                text: Ifresco.helper.Translations.trans('Reset'),
                handler: function() {
                    this.down('itemselector').setValue([]);
                },
                scope: this
            }],
            items: [{
                xtype: 'itemselector',
                maxWidth: 700,
                name: 'contentTypesSelector',
                cls: 'ifresco-ui-item-selector',
                anchor: '100%',
                height: '100%',
                border: 0,
                store: {
                    fields: ['id', 'text', 'value'],
                    proxy: {
                        type: 'memory'
                    }
                },
                displayField: 'text',
                valueField: 'value',
                allowBlank: true,
                msgTarget: 'side',
                fromTitle: Ifresco.helper.Translations.trans('Available'),
                toTitle: Ifresco.helper.Translations.trans('Selected'),
                buttons: ['top', 'up', 'add', 'remove', 'down', 'bottom']
            }]
        });

        this.callParent();
    },

    scope: this
});
