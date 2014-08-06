Ext.define('Ifresco.store.Plugins', {
    extend: 'Ext.data.Store',
    model: 'Ifresco.model.Plugins',
    autoLoad: true,
    proxy: {
        type: 'ajax',
        url: Routing.generate('ifresco_client_admin_plugins_get'),
        reader: {
            type: 'json',
            idProperty: 'name',
            root: 'plugins',
            successProperty: true
        } 
    }
});