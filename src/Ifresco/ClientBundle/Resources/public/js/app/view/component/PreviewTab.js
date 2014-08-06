Ext.define('Ifresco.view.PreviewTab', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.ifrescoViewPreviewTab',
    cls: 'ifresco-preview-tab',
    border: 0,
    deferredRender:false,
    configData: null,
    layout: {
        type: 'fit'
    },

    initComponent: function () {
        Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('Preview'),
            listeners: {
            	resize: function() {
            		console.log("RESIZE PREVIEW");
            	},
            	scope: this
            }
        });

        this.callParent();
    },

    loadCurrentData: function (nodeId, folder) {
        var me = this;
        this.removeAll();
        if (! folder) {
            this.setLoading(true, true);
            /*Ext.Ajax.request({
                url: Routing.generate('ifresco_client_view_index'),
                params: {
                    nodeId: nodeId,
                    height: this.getHeight(),
                    width: this.getWidth()
                },
                success: function (response) {
                    me.add({
                        xtype: 'panel',
                        layout: 'fit',
                        html: response.responseText,
                        autoLoad: {
                    	   scripts: true
                    	}
                    });
                    me.setLoading(false);
                }
            });*/
            
            me.add({
                xtype: 'panel',
                layout: 'fit',
                autoLoad: {
                	url: Routing.generate('ifresco_client_view_index'),
                	scripts: true,
                	params: {
                        nodeId: nodeId,
                        height: this.getHeight(),
                        width: this.getWidth(),
                        ifrescoId: this.up("panel > panel").ifrescoId
                    },
                    scope: this
             	}
            });
            me.setLoading(false);
            
            
            /*var route = Routing.generate('ifresco_client_view_index', {
                nodeId: nodeId,
                height: this.getHeight(),
                width: this.getWidth()
            });
            
            me.add({
                xtype: 'panel',
                layout: 'fit',
                html: '<iframe src="'+route+'" height="100%" width="100%" style="height:100%;width:100%;"></iframe>'
            });
            me.setLoading(false);*/
        } else {
            Ext.Ajax.request({
                url: Routing.generate('ifresco_client_data_grid_index'),
                params: {
                    columnsetid: Ifresco.helper.Registry.get('ColumnsetId')
                },
                success: function (req) {
                    me.add({
                        xtype: 'ifrescoViewGridFolderPreview',
                        configData: Ext.decode(req.responseText),
                        nodeId: nodeId,
                        columnSetId: Ifresco.helper.Registry.get('ColumnsetId')
                    });
                }
            });
        }
    }
});
