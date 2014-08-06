Ext.define('Ifresco.view.grid.ColumnSets', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.ifrescoViewGridColumnSets',
    cls: 'ifresco-view-grid-columnsets',
    border: 0,
    viewConfig: {
        forceFit:true,
        autoHeight: true
    },
    layout:'fit',
    collapsible: true,
    animCollapse: true,
    header: false,

    initComponent: function () {
        Ext.apply(this, {
            emptyText: Ifresco.helper.Translations.trans('This document has no column sets.'),
            store: Ifresco.store.ColumnSets.create(),
            columns: [{
                header: Ifresco.helper.Translations.trans('Name'),
                width: 150, 
                sortable: true, 
                dataIndex: 'name'
            },{
                header: Ifresco.helper.Translations.trans('Default'),
                width: 80, 
                sortable: true, 
                dataIndex: 'defaultset'
            },{
                header: Ifresco.helper.Translations.trans('Hide in Menu'),
                width: 80, 
                sortable: true, 
                dataIndex: 'hideInMenu'
            }],
            tbar: [{
                iconCls: 'ifresco-icon-add-button',
                tooltip: Ifresco.helper.Translations.trans('Create column Set'),
                handler: function(){
                    this.fireEvent('edit');
                },
                scope: this
            },{
                iconCls: 'ifresco-icon-edit',
                cls: 'ifresco-admin-columnset-edit-button',
                tooltip: Ifresco.helper.Translations.trans('Edit column set'),
                disabled: true,
                handler: function(){
                    var selection = this.getSelectionModel().getSelection();
                    this.fireEvent('edit', selection[0].data.id);
                },
                scope: this
            },{
                iconCls: 'ifresco-icon-delete-button',
                cls: 'ifresco-admin-columnset-delete-button',
                disabled: true,
                tooltip: Ifresco.helper.Translations.trans('Delete selected column set'),
                handler: function(){
                    var grid = this;
                    var columnSets = this.getSelectionModel().getSelection();
                    var ids = [];
                    Ext.Array.each(columnSets, function (columnSet) {
                        ids.push(columnSet.data.id);
                    });

                    Ext.MessageBox.show({
                        buttons: Ext.MessageBox.YESNO,
                        icon: Ext.MessageBox.QUESTION,
                        title: Ifresco.helper.Translations.trans('Delete column set?'),
                        msg: Ifresco.helper.Translations.trans('Do you really want to delete selected column set?'),
                        fn: function (btn) {
                            if (btn == "yes") {
                                Ext.Ajax.request({
                                    url: Routing.generate('ifresco_client_admin_column_set_delete'),
                                    params:{
                                        'ids[]': ids
                                    },
                                    success: function (res) {
                                        var resData = Ext.decode(res.responseText);
                                        Ext.ux.StatusMessage.show({
                                            title: Ifresco.helper.Translations.trans('Column Set'),
                                            successMsg: Ifresco.helper.Translations.trans('Your column set was delete successfully!'),
                                            errorMsg: Ifresco.helper.Translations.trans('The ColumnSet(s) could not be deleted'),
                                            success: resData.success
                                        });
                                        if (resData.success) {
                                            grid.getStore().reload();
                                        }
                                    },
                                    failure: function () {
                                        Ext.ux.ErrorMessage.show({
                                            title: Ifresco.helper.Translations.trans('Column Set'),
                                            msg: Ifresco.helper.Translations.trans('The ColumnSet(s) could not be deleted')
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
            },{
                iconCls: 'ifresco-icon-star',
                cls: 'ifresco-admin-template-setdefault-button',
                disabled: true,
                tooltip: Ifresco.helper.Translations.trans('Mark as default column set'),
                handler: function(){
                    var selected = this.getSelectionModel().getSelection();
                    this.fireEvent('setDefault', selected[0].get('id'));
                },
                scope: this
            }]
        });
        this.callParent();
    },
    listeners: {
        'selectionchange': function () {
            var quantity = this.getSelectionModel().getCount();
            this.down('button[cls~=ifresco-admin-columnset-delete-button]').setDisabled(quantity < 1);
            this.down('button[cls~=ifresco-admin-template-setdefault-button]').setDisabled(quantity < 1 || quantity > 1);
            this.down('button[cls~=ifresco-admin-columnset-edit-button]').setDisabled(quantity < 1 || quantity > 1);
        }
    }

});
