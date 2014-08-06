Ext.define('Ifresco.store.SearchSearchTemplates', {
    extend: 'Ext.data.Store',
    model: 'Ifresco.model.SearchSearchTemplate',
    autoLoad: true,

    constructor: function (config) {
        this.proxy = {
            type: 'ajax',
            url: Routing.generate('ifresco_client_search_templates_get'),
            method: 'GET',
            reader: { 
                type: 'json',
                // idProperty: 'id',
                root: 'templates'
            }
        };

        this.callParent([config]);
    }
});