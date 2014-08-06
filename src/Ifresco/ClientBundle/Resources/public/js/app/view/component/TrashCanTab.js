Ext.define('Ifresco.view.TrashCanTab', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.trashcanTab',
    itemId: 'ifrescoTrashCanTab',
    closable:true,
    border: 0,
    cls: 'ifresco-view-trashcan-tab',
    closable: true,
    autoDestroy:false,
    configData: null,
    initComponent: function () {
    	Ext.apply(this, {
    		title: Ifresco.helper.Translations.trans('Trashcan'),
    		layout: 'fit',
            autoScroll: false,
            items: [{
                xtype: 'ifrescoViewGridTrashCan',
                configData: this.configData,
                flex: 1
            }]
        });

        this.callParent();
    },
    listeners: {

    }
});