Ext.define('Ifresco.model.DataSource', {
    extend: 'Ext.data.Model',
    fields: [{
        name: 'data_source_id'
    },{
        name: 'name'
    },{
        name: 'type'
    },{
        name: 'host'
    },{
        name: 'username'
    },{
        name: 'database_name'
    },{
        name: 'password'
    },{
        name: 'port'
    }]
});