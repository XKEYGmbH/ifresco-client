Ext.define('Ifresco.view.panel.TagCloud', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.ifrescoViewPanelTagCloud',
    border: 0,
    itemId: 'ifrescoTagCloudPanel',
    layout: 'fit',
    configData: null,
    iconCls: 'ifresco-icon-tag-cloud',
    style: {
        borderRight: '1px solid #99BCE8'
    },
    initComponent: function () {
    	Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('Tags'),
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
            	itemId: 'ifrescoTagCloud',
            	id: 'ifrescoTagCloud',
        		xtype: 'dataview',
        		store: new Ifresco.store.Tags(),
        		tpl: [
                  '<ul class="ifresco-tag-cloud"><tpl for=".">',
                      '<li class="ifresco-tag-entry" data-id="{name}" data-count="{count}">',
                          '<span>{name} ({count})</span>',
                      '</li>',
                  '</tpl></ul>'
	            ],
                multiSelect: false,
                layout: 'fit',
                trackOver: true,
                overItemCls: 'x-item-over',
                selectedItemCls: 'x-item-selected',
                itemSelector: '.ifresco-tag-entry',
                emptyText: Ifresco.helper.Translations.trans('No tags to display')
            }
        ];

        this.callParent();
    }
});