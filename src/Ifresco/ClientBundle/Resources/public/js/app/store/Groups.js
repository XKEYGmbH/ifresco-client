Ext.define('Ifresco.store.Groups', {
    extend: 'Ext.data.Store',
    model: 'Ifresco.model.Group',
    autoLoad: true,
    proxy: {
        type: 'ajax',
        url: Routing.generate('ifresco_client_user_management_groups'),
        reader: {
            type: 'json',
            idProperty: 'shortName',
            root: 'groups',
            successProperty : 'success',       
            messageProperty : 'message'
        } 
    }
});