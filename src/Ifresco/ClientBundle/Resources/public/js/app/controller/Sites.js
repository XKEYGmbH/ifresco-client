Ext.define('Ifresco.controller.Sites', {
    extend: 'Ext.app.Controller',
    refs: [{
    	selector: 'ifrescoViewport',
        ref: 'ifrescoViewport'
    },{
        selector: 'viewport > ifrescoWest > #ifrescoTreeFolder',
        ref: 'treeFolder'
    },{
        selector: 'viewport > ifrescoCenter',
        ref: 'tabPanel'
    },{
        selector: 'ifrescoCenter > ifrescoContentTab[ifrescoId=documents]',
        ref: 'documentsTab'
    }],
    init: function() {
        this.control({
            'ifrescoViewPanelSites > dataview': {
                itemclick: this.siteSelect
            },
            'ifrescoDashletSites': {
            	itemclick: this.siteSelect
            },
            'ifrescoTreeFolder': {
            	openSiteDocument: this.openSiteDocument
            }
        });
    },
    
    siteSelect: function(view, record, item, index, e, eOpts) {
    	var nodeId = record.get("nodeId");
    	console.log("SITE SELECT",record,nodeId);
    	this.getIfrescoViewport().fireEvent('openSite', record.get("shortName"), record.get("title"), record.get("nodeId"), record.get("docLib"));
    },
    
    openSiteDocument: function(nodeId, siteName, siteId, siteDocLib) {
    	console.log("Opensitedocument",nodeId,siteName,siteId,this.getTabPanel());
    	var tabPanel = this.getTabPanel(),
    		siteTab = tabPanel.down('ifrescoSiteTab[ifrescoId=' + siteId + ']');
    	if (siteTab) {
	        siteTab.getContentTab().reloadGridData({params: {
	            nodeId: nodeId,
	            columnSetId: Ifresco.helper.Registry.get('ColumnsetId')
	        }});
    	}
    }
});