Ext.define('Ifresco.helper.Plugins', {
    singleton: true,
    plugins: {}, // does not work with []
    constructor: function () { // WITHOUT CONSTRUCTOR, PLUGINS ARRAY STAY EMPTY , LOGIC BUG? EXTJS BUG?
    	console.log("Ifresco.helper.Plugins cunstructor called"); 
        this.callParent();
    },
    get: function (namespace) {
        return this.plugins[namespace] ? this.plugins[namespace] : null;
    },
    
    add: function (namespace,obj) {
        this.plugins[namespace] = obj;
    },
    
    getAll: function() {
    	return this.plugins;
    },
    getAdminViews: function() {
    	var adminSettings = [];
    	Ext.iterate(this.plugins, function(namespace,plugin) {
    		Ext.each(plugin.admin, function(adminSetting) {
    			adminSettings.push(adminSetting);
    		});
    	});
    	return adminSettings;
    },
    getQuicksearchTriggers: function() {
    	var triggers = [];
    	Ext.iterate(this.plugins, function(namespace,plugin) {
    		Ext.each(plugin.quicksearch, function(trigger) {
    			triggers.push(trigger);
    		});
    	});
    	return triggers;
    },
    afterLaunch: function (app) { // GETS EXECUTED IN APPLICATION.JS
    	var me = this;
    	var plugins = this.plugins;
        console.log("LOAD PLUGINS CONTROLLER",plugins);
        
        Ext.iterate(plugins, function(namespace,plugin) {
        	console.log("RUN THRO PLUGINS",namespace,plugin);
        	var noControllers = true, i = 0;
        	Ext.each(plugin.controllers, function(controller) {
        		i++;
        		noControllers = false;
	    		Ifresco.getApplication().addController(controller, {
	        		callback: function() {
	        			console.log("PLUGIN CONTROLLER INITALIZED",controller,this);
	        			if (plugin.controllers.length == i)
	        				me.afterInitLaunch(plugin,app);
	        		}
	        	});
        	});
    		
        	if (noControllers)
        		me.afterInitLaunch(plugin,app);
    	});
    },
    afterInitLaunch: function(plugin,app) {
    	console.log("CALL LAUNCH NOW");
    	plugin.launched(app);
    }
});
