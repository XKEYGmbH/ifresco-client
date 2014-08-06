Ext.define('Ifresco.store.Template', {
    extend: 'Ext.data.Store',
    autoLoad: true,
    model: 'Ifresco.model.Template',
    sortInfo: {
        field: 'id',
        direction: 'ASC'
    },

    constructor: function (config) {
        this.proxy = {
            type: 'ajax',
            url: Routing.generate('ifresco_client_admin_templates_list'),
            actionMethods: {
                read: 'GET'
            },
            timeout : 1200000,
            reader: {
                type: 'json',
                idProperty:'id',
                remoteGroup:true,
                remoteSort:true,
//                totalProperty: 'totalCount',
                root: 'templates'
            }
        };

        this.callParent([config]);
    }
});