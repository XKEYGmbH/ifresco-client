Ext.define('Ifresco.view.window.CreateHtmlDocument', {
    extend: 'Ext.window.Window',
    alias: 'widget.ifrescoViewWindowCreateHtmlDocument',
    modal:true,
    layout:'fit',
    width: 900,
    height: 550,
    closeAction:'destroy',
    constrain: true,
    plain: true,
    resizable: false,
    autoShow: true,
    nodeId: null,
    parent: null,

    initComponent: function () {
        Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('Create HTML'),
            items: [{
                border: 0,
                items: [{
                    xtype: 'form',
                    border: 0,
                    bodyPadding: 10,
                    height: 500,
                    defaultType: 'textfield',
                    defaults: {
                        anchor: '100%'
                    },
                    items: [{
                        fieldLabel: Ifresco.helper.Translations.trans('Name'),
                        allowBlank: false,
                        name: 'name'
                    },{
                        fieldLabel: Ifresco.helper.Translations.trans('Title'),
                        name: 'title'
                    },{
                        xtype: 'textarea',
                        fieldLabel: Ifresco.helper.Translations.trans('Description'),
                        name: 'description'
                    },{
                        xtype: 'htmleditor',
                        fieldLabel: Ifresco.helper.Translations.trans('Content'),
                        name: 'content',
                        height: 300
                    }]
                }],
                height: 500
            }],
            buttons: [{
                text: Ifresco.helper.Translations.trans('Save'),
                handler: function() {
                    var window = this;
                    var values = this.down('form').getValues();
                    values.nodeId = this.nodeId;
                    Ext.Ajax.request({
                        url: Routing.generate('ifresco_client_node_actions_html_create'),
                        params: values,
                        success: function () {
                            Ext.MessageBox.show({
                                title: Ifresco.helper.Translations.trans('Success'),
                                msg: Ifresco.helper.Translations.trans('File has been created'),
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.INFO
                            });
                            if(window.parent) {
                                window.parent.getStore().reload();
                            }

                            window.close();
                        },
                        failure: function (data) {
                            var result = Ext.decode(data.responseText);
                            Ext.MessageBox.show({
                                title: Ifresco.helper.Translations.trans('Error'),
                                msg: Ifresco.helper.Translations.trans('Error has been occurred'),
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.ERROR
                            });
                        }
                    });
                },
                scope: this
            },{
                text: Ifresco.helper.Translations.trans('Close'),
                handler: function() {
                    this.hide();
                },
                scope: this
            }]
        });

        this.callParent();
    }
});
