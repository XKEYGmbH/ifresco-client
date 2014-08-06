Ext.define('Ifresco.view.dashlet.SavedSearches', {
	extend: 'Ext.grid.Panel',
    alias: 'widget.ifrescoDashletSavedSearches',
    maxHeight: 300,
    
    initComponent: function(){
        Ext.apply(this, {
            autoScroll: true,
            store: Ifresco.store.SavedSearches.create({}), 
            stripeRows: true,
            columnLines: true,
            columns: [{
                text: Ifresco.helper.Translations.trans('Name'),
                flex: 2,
                sortable : true,
                dataIndex: 'name'
            },{
            	text: Ifresco.helper.Translations.trans('Private'),
            	flex: 1,
            	sortable : true,
            	dataIndex: 'is_privacy',
            	trueText: Ifresco.helper.Translations.trans('Yes'),
                falseText: Ifresco.helper.Translations.trans('No')
            }],
            listeners: {
            	itemclick: function(view, record, item, index, e, eOpts) {
                	Ifresco.getApplication().getController("Index").loadAdvancedSearchTabFromSaved(record);
                }
            }
        });

        this.callParent(arguments);
    }
});
