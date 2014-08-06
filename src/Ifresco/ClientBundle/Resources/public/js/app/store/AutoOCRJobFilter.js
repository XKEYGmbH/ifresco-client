Ext.define('Ifresco.store.AutoOCRJobFilter', {
    extend: 'Ext.data.Store',
    fields: ['name', 'value'],
    data: [
		{name: 'All', value: 'ALL'},
		{name: 'Created', value: 'CREATED'},
		{name: 'Uploaded', value: 'UPLOADED'},
		{name: 'Converting', value: 'CONVERTING'},
		{name: 'Converted', value: 'CONVERTED'},
		{name: 'Downloaded', value: 'DOWNLOADED'},
		{name: 'Converting errors', value: 'CONVERSION_ERROR'},
		{name: 'Expired', value: 'EXPIRED'},
		{name: 'Canceled', value: 'CANCELED'}
    ]
});
