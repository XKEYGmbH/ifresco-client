Ext.define('Ifresco.store.ScannerProfile', {
    extend: 'Ext.data.Store',
    model: 'Ifresco.model.ScannerProfile',
    proxy: {
        type: 'ajax',
        url: Routing.generate('ifresco_client_scan_get_scanner_profiles'),
        actionMethods: {
            read: 'GET'
        },
        timeout : 1200000,
        reader: {
            type: 'json',
            idProperty:'name',
            remoteGroup:true,
            remoteSort:true,
            root: 'data'
        }
    },
    autoLoad: false
});