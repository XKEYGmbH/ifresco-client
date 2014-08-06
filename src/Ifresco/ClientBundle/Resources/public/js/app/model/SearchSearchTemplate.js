Ext.define('Ifresco.model.SearchSearchTemplate', {
    extend: 'Ext.data.Model',
    fields: [{
        name: 'id'
    },{
        name: 'name'
    },{
        name: 'columnSetId'
    },{
        name: 'isDefaultView', type: 'boolean', defaultValue: false
    }]
});
