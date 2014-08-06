Ext.define('Ifresco.store.AutoOCRRuntimeTransformers', {
    extend: 'Ext.data.Store',
    model: 'Ifresco.model.AutoOCRRuntimeTransformers',
    proxy: {
        type: 'ajax',
        url: Routing.generate('ifresco_client_admin_ocr_runtime_transformers'),
        reader: {
            type: 'json',
            idProperty: 'executablePath',
            root: 'transformers',
            successProperty: true
        }
    },
    autoLoad: true
});
