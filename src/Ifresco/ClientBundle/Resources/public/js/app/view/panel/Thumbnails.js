Ext.define('Ifresco.view.panel.Thumbnails', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.ifrescoViewPanelThumbnails',
    border: 0,
    layout: 'fit',
    configData: null,
    localConfigData: null,
    style: {
        borderRight: '1px solid #99BCE8'
    },
    initComponent: function () {
    	var store = Ifresco.store.Grid.create({configData: this.configData});
        this.items = [{
            border: 0,
            configData: this.configData,
            localConfigData: this.localConfigData,
            xtype: 'dataview',
            multiSelect: true,
            header: false,
            store: store,
            tpl: [
              '<tpl for=".">',
                  '<div class="thumb-wrap" id="{name}">',
                  '<div class="thumb"><img src="{url}" title="{name}"></div>',
                  '<span class="x-editable">{shortName}</span></div>',
              '</tpl>',
              '<div class="x-clear"></div>'
            ],
            overItemCls: 'x-item-over',
            itemSelector: 'div.thumb-wrap',
            emptyText: 'No images to display',
        }];

        this.callParent();
    }
});