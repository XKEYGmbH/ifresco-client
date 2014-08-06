Ext.define('Ifresco.store.NamespaceMappings', {
    extend: 'Ext.data.Store',
    model: 'Ifresco.model.NamespaceMapping',
    proxy: {
        type: 'ajax',
        url: Routing.generate('ifresco_client_admin_namespace_mapping_get'),
        reader: {
            type: 'json',
            idProperty: 'id',
            root: 'namespacemaps',
            successProperty: false
        }
    },
    autoLoad: true
});
