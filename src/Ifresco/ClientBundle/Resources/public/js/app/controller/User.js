Ext.define('Ifresco.controller.User', {
    extend: 'Ext.app.Controller',
    refs: [{
        selector: 'viewport > ifrescoCenter',
        ref: 'tabPanel'
    },{
        selector: 'viewport > ifrescoWest',
        ref: 'westPanel'
    }],

    init: function() {
        this.control({
            'ifrescoMenu': {
                addFavorite: this.addFavorite
            }
        });
    },

    addFavorite: function (nodeId, nodeText, nodeType) {
        Ext.Ajax.request({
            method: 'POST',
            url: Routing.generate('ifresco_client_user_favorite_add'),
            params: {
                nodeId: nodeId,
                nodeText: this.strip_tags(nodeText, ""),
                nodeType: nodeType
            },
            success: function (response) {
                console.log(Ext.decode(response.responseText));
                //TODO: reload favorite tree
            },
            failure: function () {}
        });
    },

    strip_tags: function (input, allowed) {
        allowed = (((allowed || "") + "")
            .toLowerCase()
            .match(/<[a-z][a-z0-9]*>/g) || [])
            .join('');
        var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi;
        commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
        return input.replace(commentsAndPhpTags, '').replace(tags, function($0, $1){
            return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
        });
    }
});