Ext.define('Ifresco.menu.TrashCan',{
    extend: 'Ext.menu.Menu',
    alias: 'widget.ifrescoMenuTrashCan',
    
    initComponent: function () {
        Ext.apply(this, {
            items: this.getItems()
        });

        this.callParent();
    },
    fromComponent: null,
    isMultiple: false,
    nodeId: null,
    getItems: function () {
    	var self = this;

        var items = [{
        	iconCls: 'ifresco-icon-cancel',
            text: Ifresco.helper.Translations.trans('Delete'),
            handler: function(){
            	if (this.isMultiple) {
            		this.fireEvent('deleteNodes', this.records, this.fromComponent);
            	}
            	else {
            		this.fireEvent('deleteNode', this.record, this.nodeId, this.DocName, this.nodeType, this.fromComponent);
            	}
            },
            scope:this
        },'-',{
            iconCls: 'ifresco-icon-revert',
            text: Ifresco.helper.Translations.trans('Restore'),
            handler: function(){
            	if (this.isMultiple) {
            		this.fireEvent('restoreNodes', this.records, this.fromComponent);
            	}
            	else {
            		this.fireEvent('restoreNode', this.record, this.nodeId, this.DocName, this.nodeType, this.fromComponent);
            	}
            },
            scope:this
        }];

        return items;
    }
});
