Ext.define('Ifresco.view.window.ChangeLogo', {
    extend: 'Ext.window.Window',
    alias: 'widget.ifrescoViewWindowChangeLogo',
    layout: 'fit',
    modal: true,
    plain: true,

    initComponent: function () {
        Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('Change Logo'),
            items: [{
                xtype: 'form',
                items: [{
                    margin: 10,
                    xtype: 'filefield',
                    width: 296,
                    fieldLabel: 'Image',
                    emptyText: 'Select Company logo...',
                    id: 'cmplogo',
                    itemId: 'cmplogo',
                    name: 'cmplogo',
                    labelWidth: 70
                }],
                buttons: [{
                    text: Ifresco.helper.Translations.trans('Upload Image'),
                    margin: 2,
                    handler: function(btn) {
                        var win = btn.up('window');
                        var form = win.down('form').getForm();
                        if(form.isValid()){
                            form.submit({
                                url : Routing.generate('ifresco_client_admin_upload_logo'),
                                waitMsg: Ifresco.helper.Translations.trans('Uploading your file...'),
                                success: function() {
                                    Ext.Msg.alert(
                                        Ifresco.helper.Translations.trans('Success'), 
                                        Ifresco.helper.Translations.trans('Logo has been uploaded.')
                                    );
                                    win.close();
                                    // TODO: change logo
                                },
                                failure: function() {
                                    Ext.Msg.alert(
                                        Ifresco.helper.Translations.trans('Failure'),
                                        Ifresco.helper.Translations.trans('Logo is not uploaded.')
                                    );
                                    win.close();
                                }
                            });
                        }
                    }
                }]
            }]
        });

        this.callParent();
    }
});