/*Ext.Loader.setConfig({
    enabled : true,
    disableCaching : true
}); // disable cache for develop
*/

console.log("ifresco Client - started | www.ifresco.at");

var debug = true;
if (!debug) {
    console = console || {};
    console.log = function(){};
}


Ext.application({
    name: 'Ifresco',
    autoCreateViewport: true,
    iSettings: null,
    viewport: null,
    controllers: [
        'Index', 
        'Admin', 
        'Search',
        'Clipboard',
        'Grid',
        'Metadata',
        'Node',
        'TagCloud',
        'Sites',
        'User',
        'Version',
        'TrashCan',
        'Plugin'
    ],
    
    router : Ext.create('Ifresco.util.Router'),
    initRoutes : function(router) {
        router.name('document', 'document/:nodeRef', {
            controller : 'Routing',
            action : 'loadDocument'
        });
        
        router.name('folder', 'folder/:nodeRef', {
            controller : 'Routing',
            action : 'loadFolder'
        });
        
        router.name('search', 'search/:query', {
            controller : 'Routing',
            action : 'loadSearch'
        });
    	console.log("init routes");
    },
    isRouted: false,
    
    initHistory : function() {
        var me = this;
        Ext.util.History.init(function() {
            var token = Ext.util.History.getToken();
            if (!token) {
                Ext.util.History.add(me.defaultHistoryToken);
            } else {
                this.historyChange(token);
            }
        }, me);

        Ext.util.History.on('change', this.historyChange, this);
    },
    
    historyChange : function(token) {
        var me = this;
        if (token) {
            var route = me.router.recognize(token);
            this.dispatch(route);
        }
    },

    dispatch : function(config) {
        var me = this;
        if (typeof config == 'undefined')
            return;

        var controller = me.getController(Ext.String.capitalize(config.controller));

        var baseOnLaunch = controller.onLaunch;
        controller.onLaunch = function(app) {
            this[config.action](config);
            baseOnLaunch();
        };

    },
    
    launch: function() {
    	this.initRoutes(this.router);
        this.initHistory();
        
    	this.viewport = Ext.ComponentQuery.query('viewport')[0];
    	
        /*Ext.state.Manager.setProvider(
            Ext.create('Ext.state.CookieProvider', {
                expires: new Date(new Date().getTime()+(1000*60*60*24*365))
            })
        );*/
    	
    	if(Ext.supports.LocalStorage) {
    		console.log("USE LOCAL STORAGE STATE");
    	    Ext.state.Manager.setProvider(new Ext.state.LocalStorageProvider());
    	}
    	else {
    		console.log("USE COOKIE  STATE");
    	    Ext.state.Manager.setProvider(new Ext.state.CookieProvider());
    	}
        
        window.ondragenter = function(e)
        {
        	console.log("ondragenter window")
            e.dataTransfer.dropEffect = 'none';
            e.preventDefault();
            return false;
        };

        window.ondragover = function(e)
        {
            e.preventDefault();
            return false;
        };

        window.ondrop = function(e)
        {
            return false;
        };

        window.ondragleave = function(e)
        {
            return false;
        };
        
        

        Ifresco.getApplication().getViewport().fireEvent("openDashboard");
        Ifresco.getApplication().getViewport().fireEvent("loadDefaults");
        Ifresco.getApplication().getViewport().fireEvent("setup");
        Ifresco.helper.Plugins.afterLaunch(this);
        
        this.initQtips();
    },
    
    getViewport: function() {
    	return this.viewport;
    },
    
    initQtips: function() {
    	console.log("APPLICATION QTIP INIT")
    	if(Ext.isIE10) { 
    	      Ext.supports.Direct2DBug = true;
    	  }

    	  if(Ext.isChrome) {
    	      Ext.define('Ext.layout.container.AutoTip', {
    	        alias: ['layout.autotip'],
    	        extend: 'Ext.layout.container.Container',

    	        childEls: [
    	            'clearEl'
    	        ],

    	        renderTpl: [
    	            '{%this.renderBody(out,values)%}',

    	            '<div id="{ownerId}-clearEl" class="', Ext.baseCSSPrefix, 'clear" role="presentation"></div>'
    	        ],

    	        calculate: function(ownerContext) {
    	            var me = this,
    	                containerSize;

    	            if (!ownerContext.hasDomProp('containerChildrenDone')) {
    	                me.done = false;
    	            } else {

    	                containerSize = me.getContainerSize(ownerContext);
    	                if (!containerSize.gotAll) {
    	                    me.done = false;
    	                }

    	                me.calculateContentSize(ownerContext);
    	            }
    	        }
    	    });

    	    Ext.override(Ext.tip.Tip, {
    	        layout: {
    	            type: 'autotip'
    	        }
    	    });
    	}

    	//Ext.QuickTips.init();
    }
});

Ext.app.Application.prototype.addController = function(classPath, config) {
	var self = this,
		config = config || {};
	
	Ext.require(classPath, function() {
		var controller = Ext.create(classPath, Ext.apply({
			application : self
		}, config.options || {}));
		
		self.controllers.add(classPath, controller);
		
		controller.init();
		
		if (config.callback) { config.callback.call((config.scope || this), config); }
	});
};