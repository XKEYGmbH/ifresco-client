Ext.define('Ifresco.controller.Routing', {
    extend: 'Ext.app.Controller',
    refs: [{
        selector: 'viewport > ifrescoCenter',
        ref: 'tabPanel'
    }],
    
    init: function() {
        this.control({
            
        });
    },
    
    
    
    loadDocument: function(config) {
    	Ifresco.getApplication().isRouted = true;
    	var nodeRef = config.nodeRef,
    		nodeId = nodeRef.replace(/workspace:\/\/SpacesStore\//,"");
    	Ifresco.getApplication().getController("Index").openDocumentDetail(nodeId, null);
    	
    },
    
    loadFolder: function(config) {
    	Ifresco.getApplication().isRouted = true;
    	var nodeRef = config.nodeRef,
    		nodeId = nodeRef.replace(/workspace:\/\/SpacesStore\//,"");
    	Ifresco.getApplication().getController("Index").openDocument(nodeId, true);
    },
    
    loadSearch: function(config) {
    	Ifresco.getApplication().isRouted = true;
    	var query = config.query,
    		parse = query.split('&'),
    		fields = {},
    		options = {searchTerm: null, results:'', locations: [], categories: [], searchBy: "AND", tags:''};
    	
    	Ext.each(parse, function(field, index) {
    		var keyValue = field.split('='),
    			key = keyValue[0],
    			value = keyValue[1];
    			
    		switch (key) {
    			case "searchBy":
    				value = value.toUpperCase();
    				if (value == "AND")
    					options.searchBy = "AND";
    				else
    					options.searchBy = "OR";
				break;
    			case "contentType":
    				options.contentType = value;
				break;
    			case "searchTerm":
    				options.searchTerm = value;
    				break;
    			case "tags":
    				options.tags = value;
    				break;
    			default:
    				fields[key] = value;
    				break;
    		}
    		
    	});
    	
    	
    	
    	/*options = Ext.apply({}, options, {
            prop3: false,
            prop4: false
        });*/

    	console.log("LOAD SEARCH",config,query,parse,fields,options)
        
    	Ifresco.getApplication().getController("Search").doSearch(fields,options,Ifresco.helper.Registry.get('ColumnsetId'));
    },
    
    setHistory : function(config) {
        /*var record = config.record, path = record.getPath("name", "/");

        var type = record.get('type');
        var navType = "slides";
        if (type === "cm:category") {
            navType = "cat";
        }
        var newToken = navType + encodeURI(path);
        var oldToken = Ext.History.getToken();
        // window.location.hash = "slides" + encodeURI(path);

        if (oldToken === null || oldToken != newToken) {
            Ext.History.add(newToken);
        }*/
    	console.log("SET HISTORY");
    }
});