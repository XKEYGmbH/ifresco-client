Ext.define('Ifresco.store.Persons', {
    extend: 'Ext.data.Store',
    model: 'Ifresco.model.Person',
    autoLoad: true,
    proxy: {
        type: 'ajax',
        url: Routing.generate('ifresco_client_user_management_persons'),
        reader: {
            type: 'json',
            idProperty: 'userName',
            root: 'people',
            successProperty : 'success',       
            messageProperty : 'message'
        } 
    }
});