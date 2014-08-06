Ext.define('Ifresco.store.TreeCategories', {
    extend: 'Ext.data.TreeStore',
    alias: 'widget.ifrescostoretreecategories',
    autoLoad: true,
    proxy: {
        type: 'ajax',
        url: Routing.generate('ifresco_client_get_tree_categories'),
        actionMethods: 'POST',
        simpleSortMode: true
    },
    root: {
        expanded: true,
        text: 'Categories',
        id: 'root',
        nodeId: 'root',
        disabled:true
    },
    folderSort: true,
    sorters: [{
        property: 'text',
        direction: 'ASC'
    }]
});