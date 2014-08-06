Ext.define('Ifresco.store.TagManager', {
    extend: 'Ext.data.Store',
    model: 'Ifresco.model.TagManager',
    proxy: {
        type: 'ajax',
        url: Routing.generate('ifresco_client_admin_tag_manager'),
        reader: {
            type: 'json',
            idProperty: 'nodeRef',
            root: 'items',
            successProperty: true
        },
        extraParams: {
        	filter: ''
        }
    },
    autoLoad: true
});
