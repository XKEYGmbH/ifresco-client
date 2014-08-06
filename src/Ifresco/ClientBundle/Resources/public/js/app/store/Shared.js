Ext.define('Ifresco.store.Shared', {
    extend: 'Ext.data.Store',
    model: 'Ifresco.model.SharedFile',
    autoLoad: true,
    proxy: {
        type: 'ajax',
        url: Routing.generate('ifresco_client_shared_get'),
        reader: {
            type: 'json',
            idProperty: 'nodeRef',
            root: 'items'
        } 
    }
});