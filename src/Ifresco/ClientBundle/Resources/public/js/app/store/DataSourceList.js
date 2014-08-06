Ext.define('Ifresco.store.DataSourceList', {
    extend: 'Ext.data.Store',
    alias: 'widget.ifrescoDataSourceList',
    proxy: {
        url: '/getdatasource.json',
        type: 'ajax',
        actionMethods: 'GET'
    },
    fields: ['id', 'name'],
    reader: {
        type: 'json'
    },
    autoLoad: true,

    constructor: function (config) {
        this.callParent([config]);
    }
});
