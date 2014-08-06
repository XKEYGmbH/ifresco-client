Ext.define('Ifresco.store.Sites', {
    extend: 'Ext.data.Store',
    model: 'Ifresco.model.Sites',
    autoLoad: true,
    proxy: {
        type: 'ajax',
        url: Routing.generate('ifresco_client_sites_get'),
        reader: {
            type: 'json',
            idProperty: 'shortName',
            root: 'sites'
        } 
    }
});