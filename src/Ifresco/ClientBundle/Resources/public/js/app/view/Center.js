Ext.define('Ifresco.view.Center', {
    extend: 'Ext.tab.Panel',
    cls: 'ifresco-view-center',
    alias: 'widget.ifrescoCenter',
    activeTab: 0,
    enableTabScroll : true,
    autoDestroy     : true,
    resizeTabs      : false,
    closeAction     : 'destroy',
    border          : true,
    defaults: {
        autoScroll:true
    },
    tokenDelimiter: '/',
    tokenSet: false,
    listeners: {
        tabchange: function(tabPanel, tab){
//            TODO://add history write
        	if (!this.tokenSet) {
        		var oldToken = Ext.History.getToken();
	            if (typeof tab != 'undefined') {
	            	if (typeof tab.id != 'undefined' && tab.id != null)
	            		Ext.History.add(tab.id);
	            	
	            	this.tokenSet = true;
	            	//Ext.History.add(tabPanel.id + this.tokenDelimiter + tab.id);
	            }
        	}
        	/*var tabs = [],
            ownerCt = tabPanel.ownerCt, 
	            oldToken, newToken;
	
	        tabs.push(tab.id);
	        tabs.push(tabPanel.id);
	
	        while (ownerCt && ownerCt.is('tabpanel')) {
	            tabs.push(ownerCt.id);
	            ownerCt = ownerCt.ownerCt;
	        }
	        
	        newToken = tabs.reverse().join(this.tokenDelimiter);
	        
	        oldToken = Ext.History.getToken();
	       
	        if (oldToken === null || oldToken.search(newToken) === -1) {
	            Ext.History.add(newToken);
	        }*/
        }
    },

    initComponent: function () {
        /*if (Ifresco.helper.Settings.get('HomeScreen')) {
            this.items = [{
                title: Ifresco.helper.Translations.trans('Home'),
                itemId: 'ifrescoHomeTab',
                iconCls: 'ifresco-home-image',
                bodyStyle:'background-color:#006AB3;',
                loader: {
                    url: Routing.generate('ifresco_client_home_page'),
                    autoLoad: true
                }
            }];
        }*/

        this.callParent();
    }
});