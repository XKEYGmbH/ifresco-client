Ext.define('Ifresco.view.window.SaveSearch', {
    extend: 'Ext.window.Window',
    alias: 'widget.ifrescoViewWindowSaveSearch',
    layout: 'fit',
    modal: true,
    plain: true,

    initComponent: function () {
        Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('Save Search'),
            items: [{
                xtype: 'form',
                items: [{
                    margin: 10,
                    xtype: 'textfield',
                    width: 296,
                    fieldLabel: Ifresco.helper.Translations.trans('Name'),
                    name: 'name',
                    allowBlank: false,
                    labelWidth: 70
                }, {
                    margin: 10,
                    xtype: 'checkbox',
                    name: 'is_general',
                    fieldLabel: Ifresco.helper.Translations.trans('General?'),
                    labelWidth: 70,
                    checked: true,
                    inputValue: true
                }],
                buttons: [{
                    text: Ifresco.helper.Translations.trans('Save'),
                    margin: 2,
                    formBind: true,
                    handler: function (btn) {
                        var win = btn.up('window');
                        var values = win.down('form').getValues();
                        win.fireEvent('saveSearch', values.name, values.is_general);
                        win.close();
                    }
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