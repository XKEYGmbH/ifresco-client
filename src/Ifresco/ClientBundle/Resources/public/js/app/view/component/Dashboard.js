Ext.define('Ifresco.view.Dashboard', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.ifrescoViewDashboard',
    cls: 'ifresco-view-dashboard',
    border: 0,
    configData: null,
    tabPosition: 'top',
    id: 'dashboard',

    style: {
        borderLeft: '1px solid #99BCE8'
    },

    initComponent: function () {
    	Ext.apply(this, {
    		title: Ifresco.helper.Translations.trans('Dashboard'),
    		items: this.getCurrentItems()
    	});
        this.callParent();
    },

    getCurrentItems: function () {
        var items = [{
        	xtype: 'portalpanel',
            region: 'center',
            padding: '5 5 5 5',
            border: false,
            frame: false,
            stateful: true,
            stateId: 'ifrescoViewDashboard',
            stateEvents: ["move","position","drop","hide","show","collapse","expand","columnmove","columnresize","sortchange"],
            applyState : function(state){
                if(state){
                    Ext.apply(this, state);
                }
            },
            items: [{
                id: 'col-1',
                padding: '5 5 5 5',
                items: [{
                    id: 'mySitesDashlet',
                    title: Ifresco.helper.Translations.trans('My Sites'),
                    tools: this.getTools(),
                    items: Ext.create('Ifresco.view.dashlet.Sites'),
                    stateful: true,
                    stateId: 'mySitesDashlet',
                    stateEvents: ["move","position","drop","hide","show","collapse","expand","columnmove","columnresize","sortchange"],
                    applyState : function(state){
                        if(state){
                            Ext.apply(this, state);
                        }
                    },
                    listeners: {
                        'close': Ext.bind(this.onPortletClose, this)
                    }
                }/*,{
                    id: 'portlet-2',
                    title: 'Portlet 2',
                    tools: this.getTools(),
                    html: "xxx",
                    listeners: {
                        'close': Ext.bind(this.onPortletClose, this)
                    }
                }*/]
            },{
                id: 'col-2',
                padding: '5 5 5 5',
                items: [{
                    id: 'savedSearchesDashlet',
                    title: Ifresco.helper.Translations.trans('Saved Searches'),
                    tools: this.getTools(),
                    items: Ext.create('Ifresco.view.dashlet.SavedSearches'),
                    stateful: true,
                    stateId: 'savedSearchesDashlet',
                    stateEvents: ["move","position","drop","hide","show","collapse","expand","columnmove","columnresize","sortchange"],
                    applyState : function(state){
                        if(state){
                            Ext.apply(this, state);
                        }
                    },
                    listeners: {
                        'close': Ext.bind(this.onPortletClose, this)
                    }
                }]
            },{
                id: 'col-3',
                padding: '5 5 5 5',
                items: [{
                    id: 'myDocumentsDashlet',
                    title: Ifresco.helper.Translations.trans('My Documents'),
                    tools: this.getTools(),
                    items: Ext.create('Ifresco.view.dashlet.MyDocuments'),
                    stateful: true,
                    stateId: 'myDocumentsDashlet',
                    stateEvents: ["move","position","drop","hide","show","collapse","expand","columnmove","columnresize","sortchange"],
                    applyState : function(state){
                        if(state){
                            Ext.apply(this, state);
                        }
                    },
                    listeners: {
                        'close': Ext.bind(this.onPortletClose, this)
                    }
                }]
            }]
        }]
        
        return items;
    },

    listeners: {
        tabchange: function (tabPanel, newCard) {

        },
        beforerender: function () {

        }
    },
    
    onPortletClose: function(portlet) {
        this.showMsg('"' + portlet.title + '" was removed');
    },
    
    
    getTools: function(){
        return [{
            xtype: 'tool',
            type: 'gear',
            handler: function(e, target, panelHeader, tool){
                var portlet = panelHeader.ownerCt;
                portlet.setLoading('Working...');
                Ext.defer(function() {
                    portlet.setLoading(false);
                }, 2000);
            }
        }, {
        	type: 'refresh',
            hidden: true,
            handler: function() {
            	if (this.down("dataview")) {
            		this.down("dataview").getStore().reload();
            	}
            	else if (this.down("grid")) {
            		this.down("grid").getStore().reload();
            	}
            }
        }];
    },

    reloadTabData: function (tab) {

    }


});