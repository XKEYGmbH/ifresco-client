Ext.define('Ifresco.helper.Registry', {
    singleton: true,
    alias: 'widget.ifrescohelperregistry',
    data: {},
    constructor: function () {
        this.read();
        if (typeof this.get("ColumnsetId") == 'undefined') {
            this.set("ColumnsetId", 0);
        }
        if (typeof this.get("ArrangeList") == 'undefined') {
            this.set("ArrangeList", "horizontal");
        }
        if (typeof this.get("BrowseSubCategories") == 'undefined') {
            this.set("BrowseSubCategories", false);
        }

        this.callParent();
    },
    set: function (key, value) {
        this.data[key] = value;
    },
    get: function (key) {
        return this.data[key];
    },
    save: function () {
        var jsonData = $.JSON.encode(this.data);
        Ifresco.helper.Cookie.remove("ifresco-Registry");
        Ifresco.helper.Cookie.save("ifresco-Registry", jsonData, 30);
    },
    read: function () {
        var registryCookies = Ifresco.helper.Cookie.get("ifresco-Registry");

        var jsonDecode = $.JSON.decode(registryCookies);
        if (jsonDecode != null){
            this.data = jsonDecode;
        }
    }
});
