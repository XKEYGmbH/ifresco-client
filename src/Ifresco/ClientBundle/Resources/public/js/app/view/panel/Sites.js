Ext.define('Ifresco.view.panel.Sites', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.ifrescoViewPanelSites',
    border: 0,
    itemId: 'ifrescoSitesPanel',
    layout: 'fit',
    configData: null,
    iconCls: 'ifresco-icon-sites',
    style: {
        borderRight: '1px solid #99BCE8'
    },
    initComponent: function () {
    	Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('Sites'),
            tbar: [{
            	xtype: 'textfield',
            	name: 'searchSite',
            	listeners: {
            		render : {
                        single : true,
                        buffer : 100,
                        fn     :function() {
                        	console.log("render sites field")
                            this.el.setWidth(this.up('toolbar').el.getWidth() - 55);
                        }
                    },
            	}
            },{
            	iconCls: 'ifresco-icon-magnifier',
            	tooltip: Ifresco.helper.Translations.trans('Search'),
                handler: function() {
                    var filter = this.down("textfield[name~=searchSite]").getValue();
                    this.down("dataview").getStore().load({
                    	params:{
                    		filter: filter
                    	}
                    });
                },
                scope: this 
            },'->','-',
	            {
	                iconCls: 'ifresco-icon-refresh',
	                tooltip: Ifresco.helper.Translations.trans('Reload'),
	                scope:this,
	                handler: function(){
	                    if(!this.down("dataview").getStore().isLoading()) {
	                    	this.down("dataview").getStore().load();
	                    }
	                }
	            }
            ]
        });
    	//this.items = []; 
        this.items = [
			{
            	itemId: 'ifrescoSitesDataView',
            	id: 'ifrescoSitesDataView',
        		xtype: 'dataview',
        		store: new Ifresco.store.Sites({}),
        		tpl: [
                  '<ul class="ifresco-sites"><tpl for=".">',
                      '<li class="ifresco-sites-entry" data-id="{shortName}">',
                          '<h1>{title}</h1><p>{description}</p>',
                      '</li>',
                  '</tpl></ul>'
	            ],
                multiSelect: false,
                layout: 'fit',
                trackOver: true,
                overItemCls: 'x-item-over',
                selectedItemCls: 'x-item-selected',
                itemSelector: '.ifresco-sites-entry',
                emptyText: Ifresco.helper.Translations.trans('No sites to display')
            }
        ];
        this.callParent();
    }
});