Ext.define('Ifresco.view.grid.Template', {
    extend: 'Ext.grid.Panel',
    itemId: 'ifrescoViewGridTemplate',
    alias: 'widget.ifrescoViewGridTemplate',
    cls: 'ifresco-view-grid-template',
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
            store: Ifresco.store.Template.create(),
            columns: [{
                header: Ifresco.helper.Translations.trans('Content Type'),
                width: 200,
                sortable: true,
                dataIndex: 'class'
            },{
                header: Ifresco.helper.Translations.trans('Multi Column'),
                width: 100,
                sortable: true,
                dataIndex: 'multiColumns'
            },{
                header: Ifresco.helper.Translations.trans('Aspects show as'),
                width: 100,
                sortable: true,
                dataIndex: 'aspectsView'
            },{
                header: Ifresco.helper.Translations.trans('Tabs'),
                width: 50,
                flex: 1,
                sortable: true,
                dataIndex: 'tabs'
            }],
            tbar: [{
                iconCls: 'ifresco-icon-add-button',
                tooltip: Ifresco.helper.Translations.trans('Create a Template'),
                handler: function(){
                    var window = Ifresco.view.window.TemplateDesigner.create({});
                    window.show();
                },
                scope: this
            },{
                iconCls: 'ifresco-icon-edit',
                cls: 'ifresco-admin-template-edit-button',
                tooltip: Ifresco.helper.Translations.trans('Edit template'),
                disabled: true,
                handler: function(){
                    var selection = this.getSelectionModel().getSelection();
                    this.fireEvent('editTemplate', selection[0].data.id);
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
                    var ids= [];
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
                                    url: Routing.generate('ifresco_client_admin_templates_delete'),
                                    params:{
                                        'ids[]': ids
                                    },
                                    success: function (res) {
                                        var resData = Ext.decode(res.responseText);
                                        Ext.ux.StatusMessage.show({
                                            title: Ifresco.helper.Translations.trans('Template'),
                                            successMsg: Ifresco.helper.Translations.trans('Template was deleted successfully!'),
                                            errorMsg: Ifresco.helper.Translations.trans('Template could not be deleted'),
                                            success: resData.success
                                        });
                                        if (resData.success) {
                                            grid.getStore().reload();
                                        }
                                    },
                                    failure: function () {
                                        Ext.ux.ErrorMessage.show({
                                            title: Ifresco.helper.Translations.trans('Template'),
                                            msg: Ifresco.helper.Translations.trans('Template could not be deleted')
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
            }]
        });

        this.callParent();
    },
    listeners: {
        'selectionchange': function () {
            var quantity = this.getSelectionModel().getCount();
            this.down('button[cls~=ifresco-admin-template-delete-button]').setDisabled(quantity < 1);
            this.down('button[cls~=ifresco-admin-template-edit-button]').setDisabled(quantity < 1 || quantity > 1);
        }
    }
});