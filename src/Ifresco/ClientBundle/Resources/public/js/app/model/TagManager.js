Ext.define('Ifresco.model.TagManager', {
    extend: 'Ext.data.Model',
    fields: [
        {name: 'type', type: 'string'},
        {name: 'isContainer', type: 'boolean', defaultValue: true},
        {name: 'modified',  type: 'date', dateFormat: 'c'},
        {name: 'modifier',  type: 'string'},
        {name: 'title',  type: 'string'},
        {name: 'description',  type: 'string'},
        {name: 'name',  type: 'string'},
        {name: 'displayPath', type: 'string'},
        {name: 'nodeRef', type: 'string'},
        {name: 'selectable', type: 'boolean', defaultValue: true}
    ]
});