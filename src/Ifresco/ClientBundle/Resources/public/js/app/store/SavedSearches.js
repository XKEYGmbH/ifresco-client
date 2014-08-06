Ext.define('Ifresco.store.SavedSearches', {
    extend: 'Ext.data.Store',
    model: 'Ifresco.model.SavedSearch',
    autoLoad: true,
    sortInfo: {field: 'name', direction: 'ASC'},

    constructor: function (config) {
        this.proxy = {
            type: 'ajax',
            url: Routing.generate('ifresco_client_search_saved_get'),
            method: 'GET',
            reader: {
                type: 'json'
            }
        };

        this.callParent([config]);
    }
});