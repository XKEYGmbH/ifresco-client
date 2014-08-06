Ext.define('Ifresco.store.MyDocuments', {
    extend: 'Ext.data.Store',
    model: 'Ifresco.model.MyDocuments',
    autoLoad: false,
    proxy: {
        type: 'ajax',
        url: Routing.generate('ifresco_client_dashboard_my_documents'),
        reader: {
            type: 'json',
            idProperty: 'nodeRef',
            root: 'items'
        } 
    }
});