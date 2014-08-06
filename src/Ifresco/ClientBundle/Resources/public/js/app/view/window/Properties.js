Ext.define('Ifresco.view.window.Properties', {
    extend: 'Ext.window.Window',
    alias: 'widget.ifrescoViewWindowProperties',
    layout: 'fit',
    modal: true,
    width: 550,
    height: 380,
    plain: true,
    constrain: true,
    configData: null,

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
                    store: this.configData.fromStore,
                    displayField: 'text',
                    valueField: 'id',
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
                    var selected = this.down('itemselector').getValue();
                    var store = this.configData.toStore;
                    var fromStore = this.configData.fromStore;
                    Ext.each(selected, function(value){
                        store.add(fromStore.findRecord('id', value));
                    });
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
                    this.close();
                },
                scope: this
            }]
        });

        this.callParent();
    }
});