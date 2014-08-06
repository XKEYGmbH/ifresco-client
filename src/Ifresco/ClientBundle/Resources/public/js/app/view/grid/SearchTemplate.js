Ext.define('Ifresco.view.grid.SearchTemplate', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.ifrescoViewGridSearchTemplate',
    cls: 'ifresco-view-grid-searchtemplate',
    multiSelect: true,
    border: 0,
    viewConfig: {
        forceFit:true,
        autoHeight: true
    },
    features: Ext.create("Ext.grid.feature.Grouping", {}),
    layout:'fit',
    collapsible: true,
    animCollapse: true,
    header: false,

    initComponent: function () {
        Ext.apply(this, {
            emptyText: Ifresco.helper.Translations.trans('This document has no templates.'),
            store: Ifresco.store.SearchTemplate.create(),
            columns: [{
                id: 'name', 
                header: Ifresco.helper.Translations.trans('Name'),
                width: 150, 
                sortable: true, 
                dataIndex: 'name'
            },{
                header: Ifresco.helper.Translations.trans('Default'),
                width: 80, 
                sortable: true, 
                dataIndex: 'defaultview'
            },{
                header: Ifresco.helper.Translations.trans('Multi Column'),
                flex: 1, 
                width: 40, 
                sortable: true, 
                dataIndex: 'multiColumns'
            }],
            tbar: [{
                iconCls: 'ifresco-icon-add-button',
                tooltip: Ifresco.helper.Translations.trans('Create Template'),
                handler: function(){
                    this.fireEvent('edit', 0);
                },
                scope: this
            },{
                iconCls: 'ifresco-icon-edit',
                cls: 'ifresco-admin-template-edit-button',
                tooltip: Ifresco.helper.Translations.trans('Edit template'),
                disabled: true,
                handler: function(){
                    var selection = this.getSelectionModel().getSelection();
                    this.fireEvent('edit', selection[0].data.id);
                },
                scope: this
            },{
                iconCls: 'ifresco-icon-delete-button',
                cls: 'ifresco-admin-template-delete-button',
                disabled: true,
                tooltip: Ifresco.helper.Translations.trans('Delete selected templates'),
                handler: function(){
                    var grid = this;
                    var templates = this.getSelectionModel().getSelection();
                    var ids = [];
                    Ext.Array.each(templates, function (template) {
                        ids.push(template.data.id);
                    });

                    Ext.MessageBox.show({
                        buttons: Ext.MessageBox.YESNO,
                        icon: Ext.MessageBox.QUESTION,
                        title: Ifresco.helper.Translations.trans('Delete template?'),
                        msg: Ifresco.helper.Translations.trans('Do you really want to delete selected template(s)?'),
                        fn: function (btn) {
                            if (btn == "yes") {
                                Ext.Ajax.request({
                                    url: Routing.generate('ifresco_client_admin_search_templates_delete'),
                                    params:{
                                        'ids[]': ids
                                    },
                                    success: function (res) {
                                        var resData = Ext.decode(res.responseText);
                                        Ext.ux.StatusMessage.show({
                                            title: Ifresco.helper.Translations.trans('Search Template'),
                                            successMsg: Ifresco.helper.Translations.trans('Search template was deleted successfully!'),
                                            errorMsg: Ifresco.helper.Translations.trans('Search template could not be deleted'),
                                            success: resData.success
                                        });
                                        if (resData.success) {
                                            grid.getStore().reload();
                                        }
                                    },
                                    failure: function () {
                                        Ext.ux.ErrorMessage.show({
                                            title: Ifresco.helper.Translations.trans('Search Template'),
                                            msg: Ifresco.helper.Translations.trans('Search template could not be deleted')
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
                tooltip: Ifresco.helper.Translations.trans('Mark as default template'),
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
            this.down('button[cls~=ifresco-admin-template-delete-button]').setDisabled(quantity < 1);
            this.down('button[cls~=ifresco-admin-template-setdefault-button]').setDisabled(quantity < 1 || quantity > 1);
            this.down('button[cls~=ifresco-admin-template-edit-button]').setDisabled(quantity < 1 || quantity > 1);
        }
    }

});
