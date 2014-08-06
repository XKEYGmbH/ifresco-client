Ext.define('Ifresco.controller.Plugin', {
    extend: 'Ext.app.Controller',
    refs: [{
        selector: 'viewport > ifrescoCenter',
        ref: 'tabPanel'
    },{
        selector: 'viewport > ifrescoWest',
        ref: 'westPanel'
    },{
        selector: 'viewport > ifrescoNorth',
        ref: 'northPanel'
    },{
        selector: 'viewport > ifrescoCenter > #ifrescoAdminTab',
        ref: 'adminTab'
    },{
        selector: 'adminTab > panel[cls~=ifresco-admin-content-panel]',
        ref: 'adminTabContentPanel'
    }],

    init: function() {
        /*this.control({
            'ifrescoMenu': {
                addFavorite: this.addFavorite
            }
        });*/
    	console.log("GENERAL PLUGIN CONTROLLER INIT");
    },

    addMenuIcon: function (iconCls,tooltip,func,seperator,pos) {
    	var item = {
            xtype: 'panel',
            baseCls: 'ifresco-toolbar-button-container',
            margin: '0 4px',
            items: [{
                xtype: 'button',
                cls: iconCls,
                tooltip: tooltip,
                width: 20,
                height: 20,
                border: 0,
                handler: func
            }]
        };
    	
    	if (pos > 0)
    		this.getNorthPanel().down("toolbar").insert(pos, item);
    	else
    		this.getNorthPanel().down("toolbar").add(item);
    	
    	if (seperator == true) {
    		var sep = {
                xtype: 'tbseparator',
                baseCls: 'ifresco-toolbar-separator'
            };
    		if (pos > 0)
    			this.getNorthPanel().down("toolbar").insert((pos+1), sep);
    		else
    			this.getNorthPanel().down("toolbar").add(sep);
    	}

        console.log("ADD ICON TO NORTH",this.getNorthPanel());
    },
    
    injectAdminPlugins: function(adminTab) {
    	var adminTabContentPanel = this.getAdminTabContentPanel();
    	console.log("ADD ADMIN PLUGINS");
    	var views = Ifresco.helper.Plugins.getAdminViews();
    	Ext.each(views, function(view) {
    		var container;
    		if ('containerTitle' in view) {
    			container = adminTab.down("panel[containerId="+view.containerId+"]");
    			if (container == null) {
    				var containerItem = {
                        title: view.containerTitle,
                        containerId: view.containerId,
                        items: []
                    };
    				
    				// before about
    				var adminMenu = adminTab.down("panel[cls=ifresco-admin-menu]");
    				container = adminMenu.insert((adminMenu.items.length-1), containerItem);
    			}
    		}
    		else {
    			container = adminTab.down("panel[containerId="+view.containerId+"]");
    			if (container == null) {
    				console.warn("Admin Menu Container "+view.containerId+" for Plugin was not found!");
    			}
    		}
    			
			var linkItem = {
                text: view.text,
                handler: function () {
                    console.log("CLICKED ON LINK", view.text);
                    var panel = Ext.create(view.view, {});
                    adminTabContentPanel.removeAll();
                    adminTabContentPanel.add(panel);
                    
                    Ifresco.getApplication().getController(view.controller).fireEvent(view.controllerEvent, panel);
                }
            };
			container.add(linkItem);
    	});
    }
});