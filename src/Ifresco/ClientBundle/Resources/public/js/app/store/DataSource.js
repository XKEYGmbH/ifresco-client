Ext.define('Ifresco.store.DataSource', {
    extend: 'Ext.data.Store',
    autoLoad: true,
    model: 'Ifresco.model.DataSource',
    sortInfo: {
        field: 'dataSourceId',
        direction: 'ASC'
    },
    proxy: {
        type: 'ajax',
        url: Routing.generate('ifresco_client_admin_data_sources'),
        actionMethods: {
            read: 'GET'
        },
        reader: {
            type: 'json',
            idProperty:'dataSourceId',
            root: 'datasources'
        }
    },

    initComponent: function() {

        this.callParent();
    }
});