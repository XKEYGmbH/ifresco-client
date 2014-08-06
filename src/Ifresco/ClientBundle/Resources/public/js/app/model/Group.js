Ext.define('Ifresco.model.Group', {
    extend: 'Ext.data.Model',
    fields: [
        {name: 'url', type: 'string'},
        {name: 'authorityType', type: 'string'},
        {name: 'shortName', type: 'string'},
        {name: 'fullName', type: 'string'},
        {name: 'displayName', type: 'string'},
        {name: 'zones', type: 'auto'}
    ]
});