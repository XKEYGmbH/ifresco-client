Ext.define('Ifresco.view.settings.UploadAllowedTypes', {
    extend: 'Ext.form.Panel',
    alias: 'widget.ifrescoViewSettingsUploadAllowedTypes',
    border: 0,
    defaults: {
        margin: 5
    },
    height: '100%',
    width: '100%',
    layout: 'fit',
    configData: null,
    cls: 'ifresco-view-settings-uploadallowedtypes',
    initComponent: function () {
        Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('UploadAllowedTypes'),
            tbar: [{
            	iconCls: 'ifresco-icon-save',
            	text: Ifresco.helper.Translations.trans('Save'),
                handler: function(){
                    this.fireEvent('save');
                },
                scope: this
            },'-',{
                text: Ifresco.helper.Translations.trans('Select all'),
                handler: function(){
                    var itemSelector = this.down('itemselector');
                    var ids = itemSelector.store.collect('value');
                    itemSelector.setValue(ids);
                },
                scope: this
            },{
                text: Ifresco.helper.Translations.trans('Deselect all'),
                handler: function(){
                    this.down('itemselector').setValue([]);
                },
                scope: this
            }],
            items: [{
                xtype: 'itemselector',
                maxWidth: 700,
                name: 'allowedTypesSelector',
                cls: 'ifresco-ui-item-selector',
                anchor: '100%',
                height: '100%',
                border: 0,
                store: {
                    fields: ['value'],
                    proxy: {
                        type: 'memory'
                    }
                },
                displayField: 'value',
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
