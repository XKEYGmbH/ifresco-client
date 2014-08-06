Ext.define('Ifresco.store.SearchTreeCategories', {
    extend: 'Ext.data.TreeStore',
    alias: 'widget.ifrescoStoreSearchTreeCategories',
    folderSort: true,
    sorters: [{
        property: 'text',
        direction: 'ASC'
    }],
    fields: ['nodeId', 'path', 'qpath', 'text'],

    constructor: function (config) {
        Ext.apply(this, {
            proxy: {
                type: 'ajax',
                url: Routing.generate('ifresco_client_get_tree_categories_soap')
            },
            root: {
                expanded: false,
                text: Ifresco.helper.Translations.trans('Categories'),
                id: 'root',
                disabled: true
            }
        });

        this.callParent([config]);
    }

});
