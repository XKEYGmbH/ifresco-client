Ext.define('Ifresco.store.FolderSearch', {
    extend: 'Ext.data.Store',
    autoLoad: false,
    model: 'Ifresco.model.FolderSearch',
    /*sortInfo: {
        field: 'dataSourceId',
        direction: 'ASC'
    },*/
    proxy: {
        type: 'ajax',
        url: Routing.generate('ifresco_client_tree_search_folder'),
        actionMethods: {
            read: 'GET'
        },
        reader: {
            type: 'json',
            idProperty:'nodeRef',
            root: 'folders'
        }
    },

    initComponent: function() {
        this.callParent();
    }
});