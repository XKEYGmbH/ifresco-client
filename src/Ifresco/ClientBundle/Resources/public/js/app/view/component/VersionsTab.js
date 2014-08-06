Ext.define('Ifresco.view.VersionsTab', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.ifrescoViewVersionsTab',
    cls: 'ifresco-versions-tab',
    border: 0,
    deferredRender:false,
    configData: null,
    collapsible:false,
    header:false,
    nodeId: null,
    record: null,
    filter: {},
    name: null,
    menu: null,
    layout: {
        type: 'vbox',
        align: 'stretch'
    },

    initComponent: function () {
        Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('Versions'),
            tbar : this.createCurrentToolBar(),
            items: [{
                xtype: 'ifrescoViewGridVersion',
                configData: this.configData
            }]
        });

        this.callParent();
    },

    createCurrentToolBar: function () {
        return [{
            iconCls: 'ifresco-icon-add',
            cls: 'ifresco-version-add',
            tooltip: Ifresco.helper.Translations.trans('New Version'),
            handler: function(){
            	this.fireEvent('createNewVersion', {nodeId: this.nodeId}, {});
            },
            scope:this
        },{
            iconCls: 'ifresco-icon-upload',
            cls: 'ifresco-version-upload',
            tooltip: Ifresco.helper.Translations.trans('Upload new Version'),
            handler: function(){
            	this.fireEvent('uploadNewVersion', {nodeId: this.nodeId, filter: this.filter}, {});
            },
            scope:this
        },{
            iconCls: 'ifresco-icon-panel ',
            tooltip: Ifresco.helper.Translations.trans('Detailed Version Info'),
            handler: function(){
            	this.fireEvent('versionLookup', this.nodeId);
            },
            scope:this
        },'-',{
            iconCls: 'ifresco-icon-download',
            tooltip: Ifresco.helper.Translations.trans('Download Selected Version'),
            disabled: true,
            handler: function(){
            	var selectedVersion = this.selectedItem();
            	this.fireEvent('download', selectedVersion.data.nodeId);
          	
            },
            scope: this
        },'-',{
            iconCls: 'ifresco-icon-revert',
            tooltip: Ifresco.helper.Translations.trans('Revert to Selected Version'),
            disabled: true,
            handler: function(){
            	var selectedVersion = this.selectedItem();

               Ext.MessageBox.show({
                    title: Ifresco.helper.Translations.trans('Revert to Version'),
                    msg: Ifresco.helper.Translations.trans('Are you sure you want to revert to Version:') +"<b>"+selectedVersion.data.version+"</b>",
                    buttons: Ext.MessageBox.YESNO,
                    icon: Ext.MessageBox.INFO,
                    fn: function(btn) {
                    	if (btn === "yes") {
                    		this.fireEvent('revert', {nodeId: selectedVersion.data.nodeRef, version: selectedVersion.data.version, versionNodeId: selectedVersion.data.nodeId});
                    	}
                    },
                    scope: this
                });
            },
            scope: this
        }];
    },
    selectedItem: function() {
    	var records = this.down("ifrescoViewGridVersion").getSelectionModel().getSelection();
    	if (records.length > 0)
    		return records[0];
    	else
    		return null;
    },
    loadCurrentData: function (nodeId) {
        console.log('Loading version tab panel data');
        this.nodeId = nodeId;
        var store = this.down('ifrescoViewGridVersion').getStore();
        store.load({
            params: {
                'nodeId': nodeId
            }
        });
        
        var grid = this.up("ifrescoContentTab").down("gridpanel")
        this.record = grid.getStore().findRecord("nodeId",this.nodeId);
        this.name = this.record.get("alfresco_name");
        var ext = this.name.split('.').pop();
        
        
        this.filter = [{title : this.name, extensions : ext}];

        console.log("FOUND RECORD EXT OF ",this.nodeId,this.name,this.filter)
        
    }
});
