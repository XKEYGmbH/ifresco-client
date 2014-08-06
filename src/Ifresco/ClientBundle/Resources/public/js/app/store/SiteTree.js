Ext.define('Ifresco.store.SiteTree', {
    extend: 'Ext.data.TreeStore',
    alias: 'widget.ifrescoStoreSiteTree',
    autoLoad: false,
    proxy: {
        type: 'ajax',
        url: Routing.generate('ifresco_client_site_tree'),
        simpleSortMode: true,
        extraParams: {
        	isRootSite: false
	    }
    },
    root: null,
    folderSort: true,
    sorters: [{
        property: 'text',
        direction: 'ASC'
    }]
});