Ext.define('Ifresco.model.Comments', {
    extend: 'Ext.data.Model',
    fields: [
        {name: 'url', type: 'string'},
        {name: 'nodeRef', type: 'string'},
        {name: 'name', type: 'string'},
        {name: 'title', type: 'string'},
        {name: 'content', type: 'string'},
        {name: 'author', type: 'auto'},
        {name: 'createdOnISO', type: 'date', dateFormat: 'c'},
        {name: 'modifiedOnISO', type: 'date', dateFormat: 'c'},
        {name: 'permissions', type: 'auto'},
        {name: 'isUpdated', type: 'boolean', defaultValue: false}
    ]
});