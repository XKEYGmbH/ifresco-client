Ext.define('Ifresco.store.SearchTemplate', {
    extend: 'Ext.data.Store',
    model: 'Ifresco.model.SearchTemplate',
    autoLoad: true,
    sortInfo: {field: 'id', direction: 'ASC'},

    constructor: function (config) {
        this.proxy = {
            type: 'ajax',
            url: Routing.generate('ifresco_client_admin_search_templates_get'),
            method: 'GET',
            reader: {
                type: 'json',
                idProperty: 'id',
                remoteGroup: true,
                remoteSort: true,
                root: 'templates'
            }
        };

        this.callParent([config]);
    }
});