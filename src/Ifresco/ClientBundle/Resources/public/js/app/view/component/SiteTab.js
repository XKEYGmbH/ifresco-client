Ext.define('Ifresco.view.SiteTab', {
	extend: 'Ext.panel.Panel',
    alias: 'widget.ifrescoSiteTab',
    closable: true,
    autoDestroy:false,
    border: 0,
    cls: 'ifresco-view-site-tab',
    layout: 'fit',
    configData: null,
    ifrescoId: null,
    nodeId: null,
    docLib: null,
    shortName: null,
    autoScroll: false,
    initComponent: function () {
        Ext.apply(this, {
            items: [{
                xtype: 'container', 
                padding: 0,
                margin: 0,
                layout: {
                    type: 'hbox',
                    align: 'stretch'
                },
                items: [{
                	xtype: 'ifrescoTreeFolder',
                	flex: 1,
                	header: false,
                	collapsible: false,
                	isSite: true,
                	siteId: this.nodeId,
                	siteDocLib: this.docLib,
                	siteName: this.shortName
                },{
                    xtype: 'splitter',
                    style:{
                        backgroundColor: '#DFE8F6'
                    }
                },{
                	xtype: 'ifrescoContentTab',
                	ifrescoId: this.ifrescoId,
                    title: this.title,
                    configData: this.configData,
                    closable: false,
                    collapsible: false,
                    layout:'fit',
                    header: false,
                    flex: 3
                }]
            }]
        });
        

        this.callParent();
    },
    
    getContentTab: function() {
    	console.log("getcontenttab",this.down("ifrescoContentTab"))
    	return this.down("ifrescoContentTab");
    }
});