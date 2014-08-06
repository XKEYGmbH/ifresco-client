Ext.define('Ifresco.store.Comments', {
    extend: 'Ext.data.Store',
    model: 'Ifresco.model.Comments',
    autoLoad: false,
    autoDestroy: false,
    autoSync: false,
    proxy: {
    	api: {
            create: Routing.generate('ifresco_client_comments_new'),
            read: Routing.generate('ifresco_client_comments_get'),
            update: Routing.generate('ifresco_client_comments_update'),
            destroy: Routing.generate('ifresco_client_comments_remove'),
        },
        type: 'ajax',
        reader: {
            type: 'json',
            idProperty: 'nodeRef',
            root: 'items',
            successProperty : 'success',       
            messageProperty : 'message'
        },
        writer: {
            type: 'json',
            writeAllFields: false
        }
    },
    listeners: {
    	add: function(store, records, index, eOpts) {
    		console.log("ADD EVENT",store,records,index);
        },
        write: function(store, operation, eOpts) {
    		console.log("WRITE",store,operation,eOpts);
        }
    }
});