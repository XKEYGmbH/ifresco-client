Ext.define('Ifresco.model.AutoOCRJobs', {
    extend: 'Ext.data.Model',
    fields: [
        {name: 'converted',  type: 'date', dateFormat: 'Y/m/d H:i:s'},
        {name: 'created',  type: 'date', dateFormat: 'Y/m/d H:i:s'},
        {name: 'error',  type: 'string'},
        {name: 'guid',  type: 'string'},
        {name: 'jobId',  type: 'integer'},
        {name: 'label',  type: 'string'},
        {name: 'owner', type: 'string'},
        {name: 'pageCount', type: 'integer'},
        {name: 'status', type: 'string'}
    ]
});