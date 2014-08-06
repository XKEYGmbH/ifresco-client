Ext.define('Ifresco.controller.TagCloud', {
    extend: 'Ext.app.Controller',
    requires: ['Ifresco.view.ContentTab'],
    refs: [{
    	selector: 'ifrescoViewport',
        ref: 'ifrescoViewport'
    },{
        selector: 'viewport > ifrescocenter',
        ref: 'tabPanel'
    },{
        selector: 'viewport > ifrescocenter > #ifrescoDocumentsTab',
        ref: 'documentsTab'
    }],
    init: function() {
    	console.log("INIT GOGO")
        this.control({
            'ifrescoViewPanelTagCloud > dataview': {
                refresh : this.refreshDataView,
                itemclick: this.tagSelect
            }
        });
    },
    
    refreshDataView: function(view) {
    	if (view.getStore().isLoading())
    		return;
    	console.log("REFRESH DATAVIEW"); 
    	var rawData = view.getStore().getProxy().getReader().rawData,
    		maxCount = rawData.countMax,
        	minCount = rawData.countMin;
        if (maxCount == minCount)
            maxCount++;
        var maxSize = 20;
        var minSize = 12;

        var spread = maxCount - minCount;
        var step = (maxSize - minSize) / (spread);
        
    	Ext.each(Ext.select(".ifresco-tag-cloud li").elements, function(obj) {
		  var el = Ext.get(obj),
		  	  count = el.getAttribute("data-count"),
		  	  size = Math.round(minSize + ((count - minCount) * step));
		  el.setStyle({fontSize:size+'px'});
		});
    },
    
    tagSelect: function(view, record, item, index, e, eOpts) {
    	console.log(this.getIfrescoViewport());
    	this.getIfrescoViewport().fireEvent('openTag', record.get('name'));
    }
});