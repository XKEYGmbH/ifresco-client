Ext.define('Ifresco.store.ColumnSets', {
    extend: 'Ext.data.Store',
    model: 'Ifresco.model.ColumnSet',
    autoLoad: true,
    sortInfo: {field: 'id', direction: 'ASC'},

    constructor: function (config) {
        this.proxy = {
            type: 'ajax',
            url: Routing.generate('ifresco_client_admin_column_set_get'),
            method: 'GET',
            reader: {
                type: 'json',
                idProperty: 'id',
                remoteGroup: true,
                remoteSort: true,
                root: 'columns'
            }
        };

        this.callParent([config]);
    }
});
