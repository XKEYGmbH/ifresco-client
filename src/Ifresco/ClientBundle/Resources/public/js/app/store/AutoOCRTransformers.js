Ext.define('Ifresco.store.AutoOCRTransformers', {
    extend: 'Ext.data.Store',
    model: 'Ifresco.model.AutoOCRTransformers',
    proxy: {
        type: 'ajax',
        url: Routing.generate('ifresco_client_admin_ocr_transformers'),
        reader: {
            type: 'json',
            idProperty: 'settingsName',
            root: 'transformers',
            successProperty: true
        }
    },
    autoLoad: true
});
