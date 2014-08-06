Ext.define('Ifresco.view.window.ManageAspects', {
    extend: 'Ext.window.Window',
    alias: 'widget.ifrescoViewWindowManageAspects',
    layout: 'fit',
    modal: true,
    width:516,
    height:417,
    closeAction:'hide',
    plain: true,
    constrain: true,
    nodeId: null,
    localStore: null,
    selectedValues: [],
    parentEl: null,

    initComponent: function () {
        Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('Add Aspect'),
            items: [{
                xtype: 'form',
                itemId: 'itemSelectorForm',
                layout: 'fit',
                border: 0,
                items: [{
                    xtype: 'itemselector',
                    itemId: 'ifrescoAspectsSelector',
                    cls: 'ifresco-ui-item-selector',
                    anchor: '100%',
                    border: 0,
                    store: this.localStore,
                    displayField: 'text',
                    valueField: 'value',
                    value: this.selectedValues,
                    allowBlank: true,
                    msgTarget: 'side',
                    fromTitle: Ifresco.helper.Translations.trans('Available Aspects:'),
                    toTitle: Ifresco.helper.Translations.trans('Used Aspects:'),
                    buttons: ['add', 'remove']
                }],
                buttons: [{
                    text: Ifresco.helper.Translations.trans('Save'),
                    margin: 2,
                    formBind: true,
                    handler: function (btn) {
                        var win = btn.up('window');
                        var selected = this.down('itemselector').getValue();
                        var all = [];
                        var sel =[];

                        this.localStore.each(function (item) {
                            all.push(item.data.value);
                        });

                        Ext.each(selected, function(value){
                            sel.push(value);
                        });

                        win.fireEvent('saveAspects', this.nodeId, sel, all, this.parentEl);
                        win.close();
                    },
                    scope: this
                },{
                    text: Ifresco.helper.Translations.trans('Reset'),
                    handler: function(){
                        this.down('#itemSelectorForm').getForm().reset();
                    },
                    scope: this
                },{
                    text: Ifresco.helper.Translations.trans('Cancel'),
                    margin: '2 4 2 2',
                    handler: function (btn) {
                        btn.up('window').close();
                    }
                }]
            }]
        });

        this.callParent();
    }
});
