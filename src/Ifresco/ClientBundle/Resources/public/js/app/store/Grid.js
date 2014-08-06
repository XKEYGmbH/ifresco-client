Ext.define('Ifresco.store.Grid', {
    extend: 'Ext.data.Store',
    configData: null,
    pageSize: 30,
//    autoLoad: true,

    constructor: function (config) {
    	console.log("Ifresco.store.Grid constructor - FIELDS:",config.configData.fields);
        Ext.define('Ifresco.model.DynamicGrid', {extend: 'Ext.data.Model', fields: config.configData.fields});
        this.model = 'Ifresco.model.DynamicGrid';

        this.proxy = {
            type: 'ajax',
            url: Routing.generate('ifresco_client_grid_data'),
            actionMethods: {
                read: 'POST'
            },
            timeout : 1200000,
            reader: {
                type: 'json',
                idProperty:'nodeId',
                remoteGroup:true,
                remoteSort:true,
                totalProperty: 'totalCount',
                root: 'data'
            }
        };

        if (config.configData.DefaultSort && config.configData.DefaultSort.length > 0) {
            Ext.apply(this, {
                sortOnLoad: true,
                sortInfo: {field: config.configData.DefaultSort, direction: config.configData.DefaultSortDir},
                sorters: [{
                    property: config.configData.DefaultSort,
                    direction: config.configData.DefaultSortDir
                }],
                permEdit: false,
                perms: false,
                isSearchRequest: false
            });
        }

        this.callParent([config]);
    }
});