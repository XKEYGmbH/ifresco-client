Ext.define('Ifresco.model.Sites', {
    extend: 'Ext.data.Model',
    fields: [
        {name: 'url', type: 'string'},
        {name: 'sitePreset', type: 'string'},
        {name: 'shortName', type: 'string'},
        {name: 'title', type: 'string'},
        {name: 'description', type: 'string'},
        {name: 'node', type: 'string'},
        {name: 'tagScope', type: 'string'},
        {name: 'siteRole', type: 'string'},
        {name: 'isPublic', type: 'boolean', defaultValue: true},
        {name: 'visibility',  type: 'string'},
        {name: 'nodeId', type: 'string'},
        {name: 'docLib', type: 'string'}
    ]
});