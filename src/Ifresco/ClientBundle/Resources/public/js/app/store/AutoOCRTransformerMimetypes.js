Ext.define('Ifresco.store.AutoOCRTransformerMimetypes', {
    extend: 'Ext.data.Store',
    model: 'Ifresco.model.AutoOCRTransformerMimetypes',
    proxy: {
        type: 'ajax',
        url: Routing.generate('ifresco_client_admin_ocr_transformer_mimetypes'),
        reader: {
            type: 'json',
            idProperty: 'mimetype',
            root: 'mimetypes',
            successProperty: true
        }
    },
    autoLoad: true
});
