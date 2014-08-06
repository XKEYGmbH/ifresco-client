Ext.define('Ifresco.model.SavedSearch', {
    extend: 'Ext.data.Model',
    fields: [{
        name: 'id',
        type: 'int'
    },{
        name: 'name',
        type: 'string'
    },{
        name: 'data',
        type: 'json'
    },{
        name: 'user',
        type: 'string'
    },{
        name: 'template',
        type: 'int'
    },{
        name: 'is_privacy',
        type: 'boolean'
    }]
});
