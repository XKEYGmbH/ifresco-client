Ext.define('Ifresco.store.TemplateProperties', {
    extend: 'Ext.data.Store', 
    proxy: {
        type: 'memory'
    },
    model: 'Ifresco.model.TemplateProperty'

});