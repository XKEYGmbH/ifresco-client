Ext.define('Ifresco.menu.Version',{
    extend: 'Ext.menu.Menu',
    alias: 'widget.ifrescoMenuVersion',
    nodeId: null,
    version: null,
    versionNodeId: null,
    initComponent: function () {
        Ext.apply(this, {
            items: this.getItems()
        });

        this.callParent();
    },

    getItems: function () {
    	var self = this;

        var items = [{
            iconCls: 'ifresco-icon-revert',
            text: Ifresco.helper.Translations.trans('Revert to this Version'),
            handler: function(){
            	Ext.MessageBox.show({
                    title: Ifresco.helper.Translations.trans('Revert to Version'),
                    msg: Ifresco.helper.Translations.trans('Are you sure you want to revert to Version:') +"<b>"+this.version+"</b>",
                    buttons: Ext.MessageBox.YESNO,
                    icon: Ext.MessageBox.INFO,
                    fn: function(btn) {
                    	if (btn === "yes") {
                    		this.fireEvent('revert', {nodeId: this.nodeId, version: this.version, versionNodeId: this.versionNodeId});
                    	}
                    },
                    scope: this
                });
            },
            scope:this
        },'-',{
            iconCls: 'ifresco-icon-download',
            text: Ifresco.helper.Translations.trans('Download this Version'),
            handler: function(){
            	this.fireEvent('download', this.versionNodeId);
            },
            scope: this
        }];

        return items;
    }
});
