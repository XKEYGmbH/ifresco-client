Ext.define('Ifresco.view.window.DetailVersion', { 
    extend: 'Ext.window.Window',
    alias: 'widget.ifrescoViewWindowDetailVersion',
    modal:true,
    layout:'fit',
    width:650,
    height:630,
    closeAction:'destroy',
    configData: null,
    plain: true,
    nodeId: null,
    resizable: false,
    
    initComponent: function () {
        Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('Detailed Version Information'),
            items: [{
                xtype: 'panel',
                layout:{
                    type: 'hbox',
                    align : 'stretch',
                    pack  : 'start'
                },
                border:false,
                items: [{
                	xtype:'ifrescoViewGridVersion',
                	header:false,
                    multiSelect: false,
                    singleSelect: true,
                    height:562,
                    configData: this.configData,
                    width:170,
                    columns: [{
                        header: Ifresco.helper.Translations.trans('Version'),
                        dataIndex: 'version',
                        width: 60
                    },
                    {
                        header: Ifresco.helper.Translations.trans('Date'),
                        xtype: 'datecolumn',
                        format: this.configData.DateFormat + ' ' + this.configData.TimeFormat,
                        dataIndex: 'date',
                        width: 110
                    }],
                    listeners: {
                    	selectionchange: function(t, selected, eOpts) {
                    		console.log("selection changed",this.down("ifrescoViewPreviewTab"));
                    		var record = selected[0];
                    		this.down("form").getForm().loadRecord(record);
                    		this.down("ifrescoViewPreviewTab").loadCurrentData("workspace://version2Store/"+record.data.nodeId,false)
                    	},
                    	scope: this
                    }     
                },{
                	flex:1,
                    layout: {
                        type: 'vbox',
                        align : 'stretch',
                        pack  : 'start'
                    },
                    bodyStyle:'background-color:#e0e8f6;',
                    items: [{
                        xtype: 'form',
                        bodyStyle:'background-color:#e0e8f6;',
                        border: false,
                        frame: false,
                        padding: '5 5 5 5',
                        layout: {
                            type: 'hbox',
                            align: 'stretch'
                        },
                        fieldDefaults: {
                            labelAlign: 'top'
                        },
                        defaults: {
                        	bodyStyle:'background-color:#e0e8f6;',
                        	border: false,
                            frame: false,
                            xtype: 'panel',
                            flex: 1
                        },
                        items: [{
                        	items: [{
    						    xtype: 'textfield',
    						    flex: 1,
    						    fieldLabel: Ifresco.helper.Translations.trans('Version'),
    						    name: 'version'
    						},
    						{
    						    xtype: 'datefield',
    						    flex: 1,
    						    fieldLabel: Ifresco.helper.Translations.trans('Date'),
    						    format: this.configData.DateFormat + ' ' + this.configData.TimeFormat,
    						    name: 'date',
    						    hideTrigger: true
    						}]
                        },{
                        	items: [{
    						    xtype: 'textfield',
    						    flex: 1,
    						    fieldLabel: Ifresco.helper.Translations.trans('Author'),
    						    name: 'author'
    						},
    						{
    						    xtype: 'textarea',
    						    flex: 1,
    						    fieldLabel: Ifresco.helper.Translations.trans('Note'),
    						    name: 'description'
    						}]
                        }]
                    },{
                        flex:1,
                        xtype: 'tabpanel',
                        border:false,
                        plain: true,
                        defaults:{autoHeight: true},
                        activeTab: 0,
                        items: [{
                        	xtype: 'ifrescoViewPreviewTab',
                        	configData: this.configData,
                            title: Ifresco.helper.Translations.trans('Preview'),
                            layout:'fit'
                        }]
                    }]
                }]
            }],
            buttons: [{
                text: Ifresco.helper.Translations.trans('Close'),
                handler: function() {
                    this.close();
                },
                scope: this
            }],
            listeners: {
            	afterrender: function() {
            		console.log("afterrender detailview");
            		var store = this.down('ifrescoViewGridVersion').getStore();
                    store.load({
                        params: {
                            'nodeId': this.nodeId
                        },
                        callback: function() {
                        	this.down("ifrescoViewGridVersion").getSelectionModel().select(0);
                        },
                        scope: this
                    });
            	},
            	scope: this
            }
        });

        this.callParent();
    }
});
