Ext.define('Ifresco.model.MyDocuments', { 
    extend: 'Ext.data.Model',
    fields: [
        {name: 'nodeRef', type: 'string'},
        {name: 'nodeType', type: 'string'},
        {name: 'type', type: 'string'},
        {name: 'mimetype', type: 'string'},
        {name: 'isFolder', type: 'string'},
        {name: 'fileName', type: 'string'},
        {name: 'displayName', type: 'string'},
        {name: 'createdOn', type: 'date', dateFormat: 'c'},
        {name: 'createdBy', type: 'string'},
        {name: 'createdByUser', type: 'string'},
        {name: 'modifiedOn', type: 'date', dateFormat: 'c'},
        {name: 'modifiedBy', type: 'string'},
        {name: 'modifiedByUser', type: 'string'},
        {name: 'size', type: 'string'},
        {name: 'contentUrl', type: 'string'},
        {name: 'webdavUrl', type: 'string'},
        {name: 'actionSet', type: 'string'}
    ]
});