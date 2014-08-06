Ext.define('Ext.ux.window.StatusMessage', {
    extend: 'Ext.window.MessageBox',
    alternateClassName: 'Ext.ux.StatusMessage',
    singleton: true,
    success: true,

    show: function(config) {
        var icon;
        var msg;
        if (config.success) {
            msg = config.successMsg;
            icon = this.INFO;
        } else {
            msg = config.errorMsg;
            icon = this.ERROR;
        }

        this.callParent([{
            title: config.title,
            buttons: this.OK,
            icon: icon,
            msg: msg
        }]);
    }
});
