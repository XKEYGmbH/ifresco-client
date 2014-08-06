Ext.define('Ifresco.view.window.TemplateProperties', {
    extend: 'Ext.window.Window',
    alias: 'widget.ifrescoViewWindowTemplateProperties',
    layout:'fit',
    modal: true,
    width:550,
    height:380,
    closeAction:'hide',
    plain: true,
    constrain: true,
    columnItemId: null,

    initComponent: function () {
        Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('Select properties to add'),
            items: [{
                xtype: 'form',
                itemId: 'itemSelectorForm',
                layout: 'fit',
                border: 0,
                items: [{
                    xtype: 'itemselector',
                    itemId: 'ifrescoItemSelector',
                    cls: 'ifresco-ui-item-selector',
                    anchor: '100%',
                    border: 0,
                    store: Ifresco.store.TemplateProperties.create(),
                    displayField: 'text',
                    valueField: 'attributes.value',
                    allowBlank: true,
                    msgTarget: 'side',
                    fromTitle: Ifresco.helper.Translations.trans('Available'),
                    toTitle: Ifresco.helper.Translations.trans('Selected'),
                    buttons: ['top', 'up', 'add', 'remove', 'down', 'bottom']
                }]
            }],
            buttons: [{
                text: Ifresco.helper.Translations.trans('Add'),
                handler: function () {
                    this.fireEvent('addProperties', this.columnItemId, this.down('#itemSelectorForm').getForm().getValues());
                    this.close();
                },
                scope: this
            },{
                text: Ifresco.helper.Translations.trans('Reset'),
                handler: function(){
                    this.down('#itemSelectorForm').getForm().reset();
                },
                scope: this
            },{
                text: Ifresco.helper.Translations.trans('Close'),
                handler: function(){
                    this.hide();
                },
                scope: this
            }]
        });

        this.callParent();
    }
});