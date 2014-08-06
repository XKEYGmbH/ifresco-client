Ext.define('Ifresco.model.AutoOCRTransformerSettings', {
    extend: 'Ext.data.Model',
    fields: [
        {name: 'additionalOutputs',  type: 'auto'},
        {name: 'description',  type: 'string'},
        {name: 'engine', type: 'string'},
        {name: 'settingsName',  type: 'string'},
        {name: 'inputFormats', type: 'auto'},
        {name: 'inputFormatsMT', type: 'auto'},
        {name: 'outputFormats', type: 'auto'},
        {name: 'outputFormatsMT', type: 'auto'}
    ]
});