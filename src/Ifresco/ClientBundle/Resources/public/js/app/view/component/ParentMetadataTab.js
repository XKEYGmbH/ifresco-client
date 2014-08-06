Ext.define('Ifresco.view.ParentMetadataTab', {
    extend: 'Ifresco.view.MetadataTab',
    alias: 'widget.ifrescoViewParentMetadataTab',
    cls: 'ifresco-parent-metadata-tab',
    border: 0,
    deferredRender:false,
    configData: null,
    layout: {
        type: 'vbox',
        align: 'stretch'
    },
    isParent: true,
    
    initComponent: function () {
        this.callParent();
        
        Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('Parent Meta')
        });
    },

    getCurrentToolBar: function() {
    	console.log("try currenttoolbar parentmeta")
    	return [];
    },
    
    loadCurrentData: function (nodeId) {
        console.log('Loading parent metadata tab panel data');

        this.setLoading(true);
        Ext.Ajax.request({
            loadMask: true,
            url: Routing.generate('ifresco_client_metadata_view'),
            params: {
            	ofParent: "true",
                nodeId: nodeId,
                containerName: this.configData.addContainer
            },
            success: function(response) {
                var data = Ext.decode(response.responseText);
                this.loadMetaData(data, nodeId);
                this.setLoading(false);
            },
            scope: this
        });
    }
});
