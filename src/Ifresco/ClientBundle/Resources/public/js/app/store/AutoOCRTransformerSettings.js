Ext.define('Ifresco.store.AutoOCRTransformerSettings', {
    extend: 'Ext.data.Store',
    model: 'Ifresco.model.AutoOCRTransformerSettings',
    proxy: {
        type: 'ajax',
        url: Routing.generate('ifresco_client_admin_ocr_transformer_settings'),
        reader: {
            type: 'json',
            idProperty: 'settingsName',
            root: 'settings',
            successProperty: true
        }
    },
    autoLoad: true
});
