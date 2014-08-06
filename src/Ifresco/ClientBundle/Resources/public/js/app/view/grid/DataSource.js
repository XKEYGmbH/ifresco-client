Ext.define('Ifresco.view.grid.DataSource', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.ifrescoViewGridDataSource',
    cls: 'ifresco-view-grid-datasource',
    border: 0,
    viewConfig: {
        forceFit:true,
        autoHeight: true
    },
    layout:'fit',
    collapsible: true,
    header: false,

    initComponent: function () {
        Ext.apply(this, {
            store: Ifresco.store.DataSource.create(),
            columns: [{
                header: Ifresco.helper.Translations.trans('Source Name'),
                sortable: true,
                width: 200,
                dataIndex: 'name'
            },{
                header: Ifresco.helper.Translations.trans('Source Type'),
                flex: 1,
                sortable: true,
                dataIndex: 'type'
            }],
            tbar: [{
                iconCls: 'ifresco-icon-add-button',
                tooltip: Ifresco.helper.Translations.trans('Create a Data Source'),
                handler: function(){
                    var window = Ifresco.view.window.DataSourceDesigner.create({});
                    window.show();
                },
                scope: this
            },{
                iconCls: 'ifresco-icon-edit',
                tooltip: Ifresco.helper.Translations.trans('Edit a Data Source'),
                cls: 'ifresco-admin-datasource-edit-button',
                disabled: true,
                handler: function(){
                    this.editDataSource();
                },
                scope: this
            },{
                iconCls: 'ifresco-icon-delete-button',
                tooltip: Ifresco.helper.Translations.trans('Delete a Data Source'),
                cls: 'ifresco-admin-datasource-delete-button',
                disabled: true,
                handler: function() {
                    var grid = this;
                    Ext.MessageBox.show({
                        buttons: Ext.MessageBox.YESNO,
                        icon: Ext.MessageBox.QUESTION,
                        title: Ifresco.helper.Translations.trans('Delete data source?'),
                        msg: Ifresco.helper.Translations.trans('Do you really want to delete selected data source?'),
                        fn: function (btn) {
                            if (btn == "yes") {
                                var selection = grid.getSelectionModel().getSelection();
                                Ext.Ajax.request({
                                    url: Routing.generate('ifresco_client_admin_data_sources_delete', {
                                            id: selection[0].data.data_source_id}
                                    ),
                                    success: function (res) {
                                        var resData = Ext.decode(res.responseText);
                                        Ext.ux.StatusMessage.show({
                                            title: Ifresco.helper.Translations.trans('Data source'),
                                            successMsg: Ifresco.helper.Translations.trans('Data source was delete successfully!'),
                                            errorMsg: Ifresco.helper.Translations.trans('Data source could not be deleted'),
                                            success: resData.success
                                        });
                                        if (resData.success) {
                                            grid.getStore().reload();
                                        }
                                    },
                                    failure: function () {
                                        Ext.ux.ErrorMessage.show({
                                            title: Ifresco.helper.Translations.trans('Column Set'),
                                            msg: Ifresco.helper.Translations.trans('Data source could not be deleted')
                                        });
                                    }
                                });
                            }
                        }
                    });
                },
                scope: this
            },{
                iconCls: 'ifresco-icon-refresh',
                tooltip: Ifresco.helper.Translations.trans('Refresh'),
                handler: function(){
                    this.getStore().reload();
                },
                scope: this
            }],
            listeners: {
                selectionchange: function (el, records) {
                    if (records.length) {
                        this.down('button[cls~=ifresco-admin-datasource-delete-button]').setDisabled(false);
                        this.down('button[cls~=ifresco-admin-datasource-edit-button]').setDisabled(false);
                    }
                },
                beforeitemdblclick: function () {
                    this.editDataSource();
                },
                scope: this
            }
        });

        this.callParent();
    },

    editDataSource: function () {
        var model = this.getSelectionModel().getSelection();
        var win = Ifresco.view.window.DataSourceDesigner.create({title: model[0].data.name});
        var form = win.down('form').getForm();
        form.setValues(model[0].data);
        form.getFields('combo').fireEvent('select');
        win.show();
    }
});