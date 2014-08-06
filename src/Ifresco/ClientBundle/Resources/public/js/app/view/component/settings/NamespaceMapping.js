Ext.define('Ifresco.view.settings.NamespaceMapping', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.ifrescoViewSettingsNamespaceMapping',
    border: 0,
    cls: 'ifresco-view-settings-namespacemapping',
    layout: 'fit',
    autoScroll: true,

    initComponent: function() {
        Ext.apply(this, {
            tbar: [{
            	iconCls: 'ifresco-icon-save',
            	text: Ifresco.helper.Translations.trans('Save'),
                handler: function() {
                    this.fireEvent('save');
                },
                scope: this
            },{
            	iconCls: 'ifresco-icon-add-button',
                text: Ifresco.helper.Translations.trans('Add a Namespace'),
                handler: function() {
                    this.fireEvent('create');
                },
                scope: this
            },{
            	iconCls: 'ifresco-icon-delete-button',
                text: Ifresco.helper.Translations.trans('Delete selected'),
                handler: function() {
                    var grid = this;
                    var selection = this.getSelectionModel().getSelection();
                    Ext.MessageBox.show({
                        buttons: Ext.MessageBox.YESNO,
                        icon: Ext.MessageBox.QUESTION,
                        title: Ifresco.helper.Translations.trans('Delete namespace?'),
                        msg: Ifresco.helper.Translations.trans('Do you really want to delete selected namespace?'),
                        fn: function(btn) {
                            if (btn == "yes") {
                                Ext.Ajax.request({
                                    url: Routing.generate('ifresco_client_admin_namespace_mapping_delete', {
                                        id: selection[0].data.id
                                    }),
                                    success: function() {
                                        grid.getStore().reload();
                                        Ext.MessageBox.show({
                                            title: Ifresco.helper.Translations.trans('Successfully delete namespace!'),
                                            msg: Ifresco.helper.Translations.trans('Namespace was delete successfully!'),
                                            buttons: Ext.MessageBox.OK,
                                            icon: Ext.MessageBox.INFO
                                        });
                                    },
                                    error: function() {
                                        Ext.MessageBox.show({
                                            title: Ifresco.helper.Translations.trans('Error'),
                                            msg: Ifresco.helper.Translations.trans('Namespace could not be deleted'),
                                            buttons: Ext.MessageBox.OK,
                                            icon: Ext.MessageBox.ERROR
                                        });
                                    }
                                });
                            }
                        }
                    });
                },
                scope: this
            }],
            height: 500,
            columns: [{
                text: 'ID',
                dataIndex: 'id',
                hidden: true
            },{
                text: 'Namespace',
                dataIndex: 'namespace',
                editor: 'textfield',
                width: 200
            },{
                text: 'Prefix',
                dataIndex: 'prefix',
                editor: 'textfield'
            }],
            minColumnWidth: 150,
            selType: 'rowmodel',
            plugins: [
                Ext.create('Ext.grid.plugin.RowEditing', {
                    clicksToEdit: 2,
                    pluginId: 'RowEditing',
                    listeners: {
                        canceledit: function(editor, context) {
                            var data = context.record.data;
                            if (!(data.namespace || data.prefix)) {
                                context.record.destroy();
                            }
                        }
                    }
                })
            ],
            store: Ifresco.store.NamespaceMappings.create({})
        });

        this.callParent();
    }
});