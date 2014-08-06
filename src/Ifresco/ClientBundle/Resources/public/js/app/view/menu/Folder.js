Ext.define('Ifresco.menu.Folder',{
    extend: 'Ifresco.menu.Menu',
    alias: 'widget.ifrescoMenuFolder',
    
    initComponent: function () {
    	this.isFolder = true;
        this.callParent();
    }
});
