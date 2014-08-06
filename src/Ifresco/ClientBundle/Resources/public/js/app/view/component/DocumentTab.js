Ext.define('Ifresco.view.DocumentTab', {
    extend: 'Ext.panel.Panel',
    closable: true,
    autoDestroy:false,
    border: 0,
    alias: 'widget.ifrescoDocumentTab',
    cls: 'ifresco-view-document-tab',
    nodeId: null,
    //This data comes from server ( all necessary settings for views)
    configData: null,

    initComponent: function () {
        this.trimTitle();

        Ext.apply(this, {
            layout: 'fit',
            autoScroll: false,
            items: [{
                xtype: 'container', 
                hidden: false,
                padding: 0,
                margin: 0,
                cls: 'ifresco-view-document-tab-split-horizontal',
                layout: {
                    type: 'hbox',
                    align: 'stretch'
                },
                items: [{
                	xtype: 'ifrescoViewPreviewTab',
                    configData: this.configData,
                    flex: 1
                },{
                    xtype: 'splitter',
                    style:{
                        backgroundColor: '#DFE8F6'
                    }
                },{
                    xtype: 'ifrescoViewPanelPreview',
                    configData: this.configData,
                    tabPosition: 'top',
                    flex: 1,
                    disablePreview: true,
                    isSingleDocument: true
                }]
            }]
        });


        this.callParent();
    },

    listeners: {
        beforeclose: function(tab) {
            tab.destroy();
            return false;
        },
        activate: function (tab) {
            
        },
        afterrender: function() {
        	this.down("ifrescoViewPreviewTab").loadCurrentData(this.nodeId,false);
        }
    },

    trimTitle: function () {
    	this.title = Ext.util.Format.trim(Ext.util.Format.stripTags(this.title));
    	console.log("trim title",this.title);
        var tabLength = Ifresco.helper.Settings.get('TabTitleLength');
        var titleLength = parseInt(tabLength) || 0;
        if(titleLength > 0 && this.title.length > titleLength + 3) {
            this.title = this.title.substring(0, titleLength) + '...';
        }
    }
});