Ext.define('Ifresco.store.ContentType', {
    extend: 'Ext.data.Store',
    alias: 'widget.ifrescoStoreContentType',
    model: 'Ifresco.model.ContentType',
    autoLoad: false,
    proxy: {
        type: 'ajax',
        url: Routing.generate('ifresco_client_metadata_content_type_get'),
        reader: {
            type: 'json',
            idProperty: 'name',
            root: 'types'
        },
        actionMethods: {
            read: 'GET'
        }
    },

    constructor: function (config) {

        this.callParent([config]);
    }
});