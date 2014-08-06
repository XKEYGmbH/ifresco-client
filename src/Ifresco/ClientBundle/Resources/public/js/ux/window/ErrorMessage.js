Ext.define('Ext.ux.window.ErrorMessage', {
    extend: 'Ext.window.MessageBox',
    alternateClassName: 'Ext.ux.ErrorMessage',
    singleton: true,

    show: function(config) {
        config.buttons = this.OK;
        config.icon = this.ERROR;

        this.callParent([config]);
    }
});
