Ext.define('Ifresco.store.Version', {
    extend: 'Ext.data.Store',
    alias: 'widget.ifrescoStoreVersion',
    autoLoad: false,

    constructor: function (config) {
        Ext.apply(this, {
            proxy: {
                type: 'ajax',
                url: Routing.generate('ifresco_client_versioning_get_json'),
                reader: {
                    type: 'json',
                    root: 'versions'
                },
                actionMethods: {
                    read: 'POST'
                }
            },
            fields: [
                'nodeRef',
                'nodeId',
                'version',
                'description',
                {
                    name:'date',
                    type:'date',
                    dateFormat:'timestamp'
                },
                'dateFormat',
                'author'
            ],
            listeners: {
                beforeload: function(store, operation, eOpts) {
        //            if(store.loadMask == undefined)
        //                store.loadMask = new Ext.LoadMask(VersionlistView, {msg:"Loading..."});
        //            store.loadMask.show();
                },
                load: function(store, records, successful, eOpts) {
        //            store.loadMask.hide();
        //            var versionPanel = Ext.getCmp('version-panel');
        //            versionPanel.setDisabled(!store.data.items.length);
                }
            },
        });
        this.callParent([config]);
    }
});