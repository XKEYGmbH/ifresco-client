Ext.define('Ifresco.model.Plugins', {
    extend: 'Ext.data.Model',
    fields: [
        {name: 'name', type: 'string'},
        {name: 'status', type: 'boolean'},
        {name: 'version', type: 'string'},
        {name: 'description', type: 'string'},
        {name: 'author', type: 'string'}
    ]
});