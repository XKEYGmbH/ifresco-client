Ext.define('Ifresco.view.Viewport', {
    alias: 'widget.ifrescoViewport',
    extend: 'Ext.container.Viewport',
    requires: [
        'Ifresco.view.North',
        'Ifresco.view.West',
        'Ifresco.view.Center'
    ],
    layout: 'border',

    initComponent: function() {
        this.items = [{
            xtype: 'ifrescoNorth',
            region: 'north'
        },{
            xtype: 'ifrescoWest',
            region: 'west'
        },{
            region: 'center',
            xtype: 'ifrescoCenter'
        }];

        this.callParent();
    }
});