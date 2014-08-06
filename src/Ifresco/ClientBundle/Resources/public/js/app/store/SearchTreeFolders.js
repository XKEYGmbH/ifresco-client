Ext.define('Ifresco.store.SearchTreeFolders', {
    extend: 'Ext.data.TreeStore',
    alias: 'widget.ifrescoStoreSearchTreeFolders',
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
                url: Routing.generate('ifresco_client_admin_check_tree_get')
            },
            root: {
                expanded: false,
                text: Ifresco.helper.Translations.trans('Repository'),
                id: 'root',
                disabled: true
            }
        });

        this.callParent([config]);
    }

});
