Ext.define('Ifresco.view.panel.Grid', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.ifrescoViewPanelGrid',
    border: 0,
    layout: 'fit',
    configData: null,
    localConfigData: null,
    style: {
        borderRight: '1px solid #99BCE8'
    },
    initComponent: function () {
        this.items = [{
            border: 0,
            configData: this.configData,
            localConfigData: this.localConfigData,
            xtype: 'ifrescoViewGridGrid'
        }];

        this.callParent();
    }
});