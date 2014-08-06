Ext.define('Ifresco.store.ColumnSetProperties', {
    extend: 'Ext.data.Store', 
    proxy: {
        type: 'memory'
    },
    model: 'Ifresco.model.ColumnSetProperty'

});