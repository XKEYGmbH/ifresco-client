Ext.define('Ifresco.model.TrashCan', {
    extend: 'Ext.data.Model',
    fields: [
        {name: 'nodeRef', type: 'string'},
        {name: 'icon', type: 'string'},
        {name: 'name', type: 'string'},
        {name: 'title', type: 'string'},
        {name: 'description', type: 'string'},
        {name: 'archivedBy', type: 'auto'},
        {name: 'archivedDate', type: 'date', dateFormat: 'c'},
        {name: 'displayPath', type: 'string'},
        {name: 'firstName', type: 'string'},
        {name: 'lastName', type: 'string'},
        {name: 'nodeType', type: 'string'},
        {name: 'isContentType', type: 'boolean', defaultValue: false}
    ]
});