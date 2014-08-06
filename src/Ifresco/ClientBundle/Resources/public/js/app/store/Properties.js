Ext.define('Ifresco.store.Properties', {
    extend: 'Ext.data.Store', 
    proxy: {
        type: 'memory'
    },
    model: 'Ifresco.model.Property'

});