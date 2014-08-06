Ext.define('Ifresco.model.AutoOCRTransformers', {
    extend: 'Ext.data.Model',
    fields: [
        {name: 'settingsName',  type: 'string'},
        {name: 'autoOCRExtension',  type: 'string'},
        {name: 'sourceMimetype', type: 'string'},
        {name: 'targetMimetype', type: 'string'}
    ]
});