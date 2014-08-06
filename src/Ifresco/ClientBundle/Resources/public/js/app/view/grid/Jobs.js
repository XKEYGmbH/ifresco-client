Ext.define('Ifresco.view.grid.Jobs', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.ifrescoViewGridJobs',
    cls: 'ifresco-view-grid-jobs',
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
            emptyText: Ifresco.helper.Translations.trans('No jobs avaiable.'),
            store: Ifresco.store.Jobs.create(),
            columns: [{
                header: Ifresco.helper.Translations.trans('Status'),
                width: 80, 
                sortable: true,
                dataIndex: 'status'
            },{
                header: Ifresco.helper.Translations.trans('Created'),
                flex: 1,
                width: 40,
                sortable: true,
                dataIndex: 'created'
            }],
            tbar: [{
                text: Ifresco.helper.Translations.trans('Export all nodes to CSV'),
                cls: 'ifresco-admin-jobs-export',
                disabled: true,
                handler: function(){
                    var selected = this.getSelectionModel().getSelection();
                    var id = selected[0].get('id');
                    window.location.assign(Routing.generate('ifresco_client_admin_current_jobs_download', {id: id}));
                },
                scope: this
            },{
                text: Ifresco.helper.Translations.trans('Clear finished jobs'),
                handler: function(){
                    var grid = this;
                    Ext.Ajax.request({
                        url: Routing.generate('ifresco_client_admin_current_jobs_clear'),
                        success: function() {
                            grid.getStore().reload();
                        }
                    });
                },
                scope: this
            },{
                text: Ifresco.helper.Translations.trans('Clear all jobs'),
                handler: function(){
                    var grid = this;
                    Ext.Ajax.request({
                        url: Routing.generate('ifresco_client_admin_current_jobs_clear'),
                        method: 'GET',
                        params: {
                            all: true
                        },
                        success: function() {
                            grid.getStore().reload();
                        }
                    });
                },
                scope: this
            },{
                text: Ifresco.helper.Translations.trans('Refresh'),
                handler: function() {
                    this.getStore().reload();
                },
                scope: this
            }],
            listeners: {
                selectionchange: function () {
                    var selected = this.getSelectionModel().getSelection();
                    var button = this.down('button[cls~=ifresco-admin-jobs-export]');
                    if (selected.length && selected[0].get('status') == 'DONE') {
                        button.enable();
                    } else {
                        button.disable();
                    }
                },
                scope: this
            }
        });
        this.callParent();
    }

});
