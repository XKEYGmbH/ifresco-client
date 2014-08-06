Ext.define('Ifresco.model.NamespaceMapping', {
    extend: 'Ext.data.Model',
    fields: [
        {name: 'id',  type: 'int'},
        {name: 'namespace', type: 'string'},
        {name: 'prefix', type: 'string'}
    ]
});