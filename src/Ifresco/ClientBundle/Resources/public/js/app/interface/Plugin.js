Ext.define('Ifresco.interface.Plugin', {
    controllers: [],
    namespace: "", // REQUIRED
    admin: [],
    quicksearch: [],
    constructor: function(config) {
    	console.log("PLUGIN CONSTRUCTOR CALLED",config);
    	Ext.apply(this, config);
    	console.log("PLUGIN APPLYED",this);
    	Ifresco.helper.Plugins.add(this.namespace, this);
    	
    	this.init();
    	//config.init();
    	/*
    	config.init();
    	
    	Ext.each(config.controllers, function(controller) {
    		Ifresco.getApplication().addController(controller, {
        		callback : function() {
        			console.log("PLUGIN CONTROLLER "+controller+" INITALIZED",self.application);
        		}
        	});
    	});*/
    },
    launched: function(app) {
    	// OVERRIDE IF NEEDED!
    }
});

// TODO make singleton class for plugins , on construct in interface plugin add to plugins singleton, in Application on launch run Plguins singleton class to create controllers!