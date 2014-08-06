Ext.define('Ifresco.view.West', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.ifrescoWest',
    split: true,
    width: 290,
    minSize: 175,
    maxSize: 400,
    collapsible: true,
    multi: false,
    
    stateful: true,
    stateId: 'ifrescoWest',

    
    layout: {
        type: 'accordion',
        animate: false,
        activeOnTop: false
    },

    initComponent: function () {
        this.title = Ifresco.helper.Translations.trans('Navigation');
        this.items = [{
            xtype: 'ifrescoTreeFolder',
            navEl: 'folders'
        },{
            xtype: 'ifrescoTreeCategories',
            navEl: 'categories',
            collapsed: true,
        },{
        	xtype: 'ifrescoViewPanelSites',
        	navEl: 'sites'
        }/*,{
        	xtype: 'ifrescoViewPanelShared',
        	navEl: 'tags'
        }*/,{
        	xtype: 'ifrescoViewPanelTagCloud',
        	navEl: 'tags'
        }]; 

        this.callParent();
        
        
    },
    
    listeners: {
    	resize: function() {
    		this.down("ifrescoTreeFolder")
    	}
    }
});