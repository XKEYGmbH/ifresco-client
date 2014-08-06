Ext.define('Ifresco.util.Router', {
    requires: ['Ifresco.util.Route', 'Ext.util.MixedCollection'],
    mixins: {
        observable: 'Ext.util.Observable'
    },
    
    constructor: function(config) {
        config = config || {};

        Ext.apply(this, config, {
            defaults: {
                action: 'index'
            }
        });
        
        this.routes = Ext.create('Ext.util.MixedCollection');

        ///this.mixins.observable.constructor.call(this, config);
    },
    
    /**
     * Connects a url-based route to a controller/action pair plus additional params
     * @param {String} url The url to recognize
     */
    connect: function(url, params) {
        params = Ext.apply({url: url}, params || {}, this.defaults);
        var route = Ext.create('Ifresco.util.Route', params);
        
        this.routes.add(route);
        
        return route;
    },
    
    /**
     * Creates a named {@link Ext.util.Route Route}. See intro docs for usage
     * @param {String} name The name of the route to connection
     * @return {Ext.util.Route} The newly-created {@link Ext.util.Route Route} object
     */
    name: function(name, url, params) {
        params = Ext.apply({id: name, url: url}, params || {}, this.defaults);
        
        var route = Ext.create('Ifresco.util.Route', params);
        
        this.routes.add(route);
        
        return route;
    },
    
    /**
     * Generates a url for a given route name
     * @param {String} name The name of the route to generate for
     * @param {Object} params Parameters for the url
     * @return {String} The generated url
     */
    generate: function(name, params) {
        var route = this.routes.get(name);
        
        if (route) {
            return route.urlFor(params);
        }
    },
    
    /**
     * Recognizes a url string connected to the Router, return the controller/action pair plus any additional
     * config associated with it
     * @param {String} url The url to recognize
     * @return {Object/undefined} If the url was recognized, the controller and action to call, else undefined
     */
    recognize: function(url) {
        var routes = this.routes.items,
            length = routes.length,
            i, result;
        
        for (i = 0; i < length; i++) {
            result = routes[i].recognize(url);
            
            if (result != undefined) {
                return result;
            }
        }
        return undefined;
    },
    
    /**
     * Removes all defined Routes
     */
    clear: function() {
        this.routes.clear();
    },
    
    /**
     * Convenience method which just calls the supplied function with the Router instance. Example usage:

        Ext.Router.draw(function(map) {
            map.connect('activate/:token', {controller: 'users', action: 'activate'});
            map.connect('home',            {controller: 'index', action: 'home'});
        });

     * @param {Function} fn The fn to call
     */
    draw: function(fn) {
        fn.call(this, this);
    }
});