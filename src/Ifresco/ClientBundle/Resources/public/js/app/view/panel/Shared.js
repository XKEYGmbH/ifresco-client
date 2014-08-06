Ext.define('Ifresco.view.panel.Shared', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.ifrescoViewPanelShared',
    border: 0,
    itemId: 'ifrescoSharedPanel',
    layout: 'fit',
    configData: null,
    iconCls: 'ifresco-icon-shared',
    style: {
        borderRight: '1px solid #99BCE8'
    },
    initComponent: function () {
    	Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('Shared files'),
            tbar: ['->',
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
            	itemId: 'ifrescoSharedDataView',
            	id: 'ifrescoSharedDataView',
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