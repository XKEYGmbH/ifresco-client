Ext.define('Ifresco.store.AutoOCRJobs', {
    extend: 'Ext.data.Store',
    model: 'Ifresco.model.AutoOCRJobs',
    proxy: {
        type: 'ajax',
        url: Routing.generate('ifresco_client_admin_ocr_jobs'),
        reader: {
            type: 'json',
            idProperty: 'jobId',
            root: 'result',
            successProperty: false
        },
        extraParams: {
        	jobType: 'ALL'
        }
    },
    autoLoad: true
});
