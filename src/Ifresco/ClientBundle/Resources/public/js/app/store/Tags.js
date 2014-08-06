Ext.define('Ifresco.store.Tags', {
    extend: 'Ext.data.Store',
    fields: ['name','count'],
    autoLoad: true,
    proxy: {
        type: 'ajax',
        url: Routing.generate('ifresco_client_tag_cloud'),
        reader: {
            type: 'json',
            idProperty: 'name',
            root: 'tags'
        } 
    }
});