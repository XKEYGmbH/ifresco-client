Ext.define('Ifresco.store.TreeFolders', {
    extend: 'Ext.data.TreeStore',
    alias: 'widget.ifrescostoretreefolders',
    autoLoad: true,
    proxy: {
        type: 'ajax',
        url: Routing.generate('ifresco_client_get_tree_folders'),
        simpleSortMode: true
    },
    root: null,
    folderSort: true,
    sorters: [{
        property: 'text',
        direction: 'ASC'
    }]
});