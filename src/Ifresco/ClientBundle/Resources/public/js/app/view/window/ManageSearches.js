Ext.define('Ifresco.view.window.ManageSavedSearches', {
    extend: 'Ext.window.Window',
    alias: 'widget.ifrescoViewWindowManageSavedSearches',
    layout: 'fit',
    modal: true,
    plain: true,

    initComponent: function () {
        Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('Select saved searches'),
            items: [{
                xtype: 'multiselect',
                store: this.configData.store,
                displayField: 'name',
                valueField: 'id',
                padding: 5,
                border: 0,
                minHeight: 200,
                minWidth: 250
            }],
            buttons: [{
                text: Ifresco.helper.Translations.trans('Delete selected'),
                margin: 2,
                handler: function (button) {
                    var win = button.up('ifrescoViewWindowManageSavedSearches');
                    var ids = win.down('multiselect').getValue();
                    if (ids.length == 0) {
                        Ext.MessageBox.show({
                            buttons: Ext.MessageBox.OK,
                            title: Ifresco.helper.Translations.trans('Empty select'),
                            msg: Ifresco.helper.Translations.trans('Please select at least one')
                        });
                        return;
                    }
                    Ext.MessageBox.show({
                        buttons: Ext.MessageBox.YESNO,
                        icon: Ext.MessageBox.QUESTION,
                        title: Ifresco.helper.Translations.trans('Delete selected saved searches?'),
                        msg: Ifresco.helper.Translations.trans('Do you really want to delete selected saved searches?'),
                        fn: function (buttonResult) {
                            if (buttonResult == "yes") {
                                win.setLoading(true, true);

                                Ext.Ajax.request({
                                    url: Routing.generate('ifresco_client_search_saved_delete'),
                                    disableCaching: true,
                                    params:{
                                        data: Ext.encode(ids)
                                    },
                                    success: function (res) {
                                        var resData = Ext.decode(res.responseText);
                                        Ext.ux.StatusMessage.show({
                                            title: Ifresco.helper.Translations.trans('Saved Searches'),
                                            successMsg: Ifresco.helper.Translations.trans('Saved searches was delete successfully!'),
                                            errorMsg: Ifresco.helper.Translations.trans('Saved searches could not be deleted'),
                                            success: resData.success
                                        });
                                        win.down('multiselect').getStore().reload();
                                        win.close();
                                    },
                                    failure: function () {
                                        Ext.ux.ErrorMessage.show({
                                            title: Ifresco.helper.Translations.trans('Saved Searches'),
                                            msg: Ifresco.helper.Translations.trans('Saved searches could not be deleted')
                                        });
                                        win.close();
                                    }
                                });
                            }
                        }
                    });
                }
            },{
                text: Ifresco.helper.Translations.trans('Cancel'),
                margin: '2 4 2 2',
                handler: function (btn) {
                    btn.up('window').close();
                }
            }]
        });

        this.callParent();
    }
});