Ext.define('Ifresco.view.GoogleEditorTab', {
    extend: 'Ext.panel.Panel',
    closable: true,
    autoDestroy:false,
    border: 0,
    alias: 'widget.ifrescoGoogleEditorTab',
    cls: 'ifresco-view-google-editor-tab',
    nodeId: null,
    editorUrl: null,
    configData: null,

    initComponent: function () {
        this.trimTitle();

        Ext.apply(this, {
            layout: 'fit',
            tbar : this.createCurrentToolBar(),
            autoScroll: false,
            items : []
        });


        this.callParent();
    },
    
    createCurrentToolBar: function () {
        return [{
            text: Ifresco.helper.Translations.trans('Save to Alfresco'),
            iconCls: 'ifresco-icon-save',
            handler: function(){
            	this.fireEvent('saveGoogleEditorDocs', this, this.nodeId);
            },
            scope:this
        },{
            text: Ifresco.helper.Translations.trans('Discard changes'),
            iconCls: 'ifresco-icon-cancel',
            handler: function(){
            	this.fireEvent('discardGoogleEditorDocs', this, this.nodeId);
            },
            scope:this
        }]
    },

    listeners: {
        beforeclose: function(tab) {
            tab.destroy();
            return false;
        },
        activate: function (tab) {
            
        },
        afterrender: function() {
        	/*{
                xtype : "component",
                autoEl : {
                    tag : "iframe",
                    layout: 'fit',
                    src : this.editorUrl
                }
            }*/
        	var self = this;
        	this.setLoading(true);
        	Ext.Ajax.request({
                url:Routing.generate('ifresco_client_google_docs_node_info'),
                params: {
                    nodeId: this.nodeId
                },
                success: function (res) {
                	var data = Ext.decode(res.responseText);
    	            self.editorUrl = data.editorUrl;
    	            self.resourceID = data.resourceID;
    	            self.locked = data.locked;

    	            self.add({
		                xtype : "component",
		                autoEl : {
		                    tag : "iframe",
		                    layout: 'fit',
		                    src : self.editorUrl
		                }
		            });
                	
                	self.setLoading(false);
                }
            });
        }
    },

    trimTitle: function () {
    	this.title = Ext.util.Format.trim(Ext.util.Format.stripTags(this.title));
        var tabLength = Ifresco.helper.Settings.get('TabTitleLength');
        var titleLength = parseInt(tabLength) || 0;
        if(titleLength > 0 && this.title.length > titleLength + 3) {
            this.title = this.title.substring(0, titleLength) + '...';
        }
    }
});