Ext.define('Ifresco.model.AutoOCRRuntimeTransformers', {
    extend: 'Ext.data.Model',
    fields: [
        {name: 'arguments',  type: 'string'},
        {name: 'executablePath',  type: 'string'},
        {name: 'sourceMimetype', type: 'string'},
        {name: 'targetMimetype', type: 'string'}
    ]
});