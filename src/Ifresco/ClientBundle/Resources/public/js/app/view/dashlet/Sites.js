Ext.define('Ifresco.view.dashlet.Sites', {
    extend: 'Ext.view.View',
    alias: 'widget.ifrescoDashletSites',
    maxHeight: 300,
    
    initComponent: function(){
    	var store = Ifresco.store.Sites.create({});
    	store.proxy.url = Routing.generate('ifresco_client_dashboard_my_sites');
    	
        Ext.apply(this, {
            autoScroll: true,
            store: store, 
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
            //selectedItemCls: 'x-item-selected',
            itemSelector: '.ifresco-sites-entry',
            emptyText: Ifresco.helper.Translations.trans('No sites to display')
        });

        this.callParent(arguments);
    }
});
