Ext.define('Ifresco.helper.Settings', {
    singleton: true,
    alias: 'widget.ifrescohelpersettings',
    settings: null,
    isAdmin: false,
    get: function (setting) {
        return this.settings[setting] ? this.settings[setting] : null;
    },

    getAll: function () {
        return this.settings;
    }
});
