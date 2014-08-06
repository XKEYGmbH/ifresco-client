Ext.define('Ifresco.controller.Shared', {
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
            'ifrescoViewPanelShared > dataview': {
                itemclick: this.sharedSelect
            },
        });
    },
    
    sharedSelect: function(view, record, item, index, e, eOpts) {
    	var nodeId = record.get("nodeId");
    	console.log("SITE SELECT",record,nodeId);
    	this.getIfrescoViewport().fireEvent('openSite', record.get("shortName"), record.get("title"), record.get("nodeId"), record.get("docLib"));
    }
});