Ext.define('Ifresco.store.TrashCan', {
    extend: 'Ext.data.Store',
    model: 'Ifresco.model.TrashCan',
    autoLoad: true,
    proxy: {
        type: 'ajax',
        url: Routing.generate('ifresco_client_user_trash_can'),
        reader: {
            type: 'json',
            idProperty: 'nodeRef',
            root: 'deletedNodes',
            successProperty : 'success',       
            messageProperty : 'message'
        }
    }
});