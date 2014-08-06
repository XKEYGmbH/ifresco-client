Ext.define('Ifresco.store.Jobs', {
    extend: 'Ext.data.Store',
    fields: ['id','created', 'status'],
    autoLoad: true,
    proxy: {
        type: 'ajax',
        url: Routing.generate('ifresco_client_admin_current_jobs_get'),
        reader: {
            type: 'json',
            idProperty: 'id',
            root: 'jobs'
        }
    }
});