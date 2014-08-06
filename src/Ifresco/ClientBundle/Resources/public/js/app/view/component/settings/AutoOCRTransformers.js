Ext.define('Ifresco.view.settings.AutoOCRTransformers', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.ifrescoViewSettingsAutoOCRTransformers',
    cls: 'ifresco-view-settings-autoocrtransformers',
    header: false,
    layout: {
        type: 'vbox',
        align : 'stretch',
        pack  : 'start'
    },
    border: 0,
    margin: 0,
    padding: 0,

    initComponent: function() {
        Ext.apply(this, {
        	items:[{
        		xtype: 'gridpanel',
        		border: 0,
        	    layout:'fit',
        	    flex: 2,
        	    header: false,
	            tbar: [{
	            	iconCls: 'ifresco-icon-save',
	                text: Ifresco.helper.Translations.trans('Save'),
	                handler: function() {
	                    this.fireEvent('save');
	                },
	                scope: this
	            },{
	            	iconCls: 'ifresco-icon-delete-button',
	                text: Ifresco.helper.Translations.trans('Delete selected'),
	                handler: function() {
	                    var grid = this.down("gridpanel");
	                    var selection = grid.getSelectionModel().getSelection();
	                    if (selection.length > 0) {
		                    Ext.MessageBox.show({
		                        buttons: Ext.MessageBox.YESNO,
		                        icon: Ext.MessageBox.QUESTION,
		                        title: Ifresco.helper.Translations.trans('Delete Transformer?'),
		                        msg: Ifresco.helper.Translations.trans('Do you really want to delete the selected transformer?'),
		                        fn: function(btn) {
		                            if (btn == "yes") {
		                            	grid.getStore().remove(selection[0]);
		                            }
		                        }
		                    });
	                    }
	                },
	                scope: this
	            }],
	            columns: [{
	                text: Ifresco.helper.Translations.trans('Profile'),
	                dataIndex: 'settingsName',
	                flex: 1
	            },{
	                text: Ifresco.helper.Translations.trans('Source mimetype'),
	                dataIndex: 'sourceMimetype',
	                flex: 1
	            },{
	                text: Ifresco.helper.Translations.trans('Target mimetype'),
	                dataIndex: 'targetMimetype',
	                flex: 1
	            }],
	            minColumnWidth: 150,
	            selType: 'rowmodel',
	            store: Ifresco.store.AutoOCRTransformers.create({})
	        },{
	        	xtype: 'form',
	        	title: Ifresco.helper.Translations.trans('Register a new transformer in ifresco Transformer'),
	        	header:true,
	        	layout:'fit',
	        	border: 0,
	            defaults: {
	                margin: 5
	            },
	            autoScroll: true,
	        	
                bodyPadding: 10,

                fieldDefaults: {
                    labelAlign: 'top',
                    labelWidth: 100,
                },
                defaults: {
                    margins: '0 0 10 0'
                },
	            
	            items: [{
	                xtype: 'panel',
	                layout: {
	                    type: 'table',
	                    columns: 4
	                },
	                defaults: {
	                    padding: 5
	                },
	                border: 0,
	                items: [{
	                    xtype: 'combobox',
	                    fieldLabel: Ifresco.helper.Translations.trans('Profile'),
	                    store: Ifresco.store.AutoOCRTransformerSettings.create({}),
	                    queryMode: 'local',
	                    name: 'settingsName',
	                    displayField: 'settingsName',
	                    valueField: 'settingsName',
	                    allowBlank: false,
	                    editable: false,
	                    listConfig: {
	                        minWidth: 350
	                    },
	                    listeners: {
	                    	scope: this,
	                        change: function( t, newValue, oldValue, eOpts ) {
	                        	var sourceCombo = this.down("combobox[name~=sourceMimetype]"),
	                        		targetCombo = this.down("combobox[name~=targetMimetype]");

	                        	var conf = t.getStore().getById(newValue);
	                        	
                            	sourceCombo.store.loadData(function(dataSet){
                                    var retVal = []
                                    Ext.Array.each(dataSet, function(d) {
                                        retVal.push({field1: d});
                                    });
                                    return retVal;
                                }(conf.raw.inputFormatsMT));

                                sourceCombo.setValue(conf.raw.inputFormatsMT[0]);

                                targetCombo.store.loadData(function(dataSet){
                                    var retVal = []
                                    Ext.Array.each(dataSet, function(d) {
                                        retVal.push({field1: d});
                                    });
                                    return retVal;
                                }(conf.raw.outputFormatsMT));

                                targetCombo.setValue(conf.raw.outputFormatsMT[0]);
                                
                                this.down("button[name~=addTransformer]").setDisabled(false);
                            }
	                    }
	                },{
	                	xtype: 'combobox',
	                	fieldLabel: Ifresco.helper.Translations.trans('Source mimetype'),
	                    store: [],
	                    queryMode: 'local',
	                    name: 'sourceMimetype',
	                    listConfig: {
	                        minWidth: 300
	                    },
	                    allowBlank: false,
	                    editable: false
	                },{
	                	xtype: 'combobox',
	                	fieldLabel: Ifresco.helper.Translations.trans('Target mimetype'),
	                    store: [],
	                    queryMode: 'local',
	                    name: 'targetMimetype',
	                    listConfig: {
	                        minWidth: 300
	                    },
	                    allowBlank: false,
	                    editable: false
	                },{
	                	xtype: 'button',
	                	name: 'addTransformer',
	                	text: Ifresco.helper.Translations.trans('Add transformer'),
	                	disabled: true,
	                	handler: function() {
	                		var grid = this.down("gridpanel"),
	                			settingsName = this.down("combobox[name~=settingsName]").getValue(),
	                			sourceMimetype = this.down("combobox[name~=sourceMimetype]").getValue(),
	                			targetMimetype = this.down("combobox[name~=targetMimetype]").getValue();
	                		
	                		
	                		var searchRecord = grid.getStore().findBy(
                			    function(record, id){
                			        if(record.get('sourceMimetype') === sourceMimetype &&
                			           record.get('targetMimetype') === targetMimetype){
                			              return true;
                			        }
                			        return false;
                			    }
                			);
	                		
	                		if(searchRecord != -1) { 
	                			Ext.ux.ErrorMessage.show({
	                                title: Ifresco.helper.Translations.trans('Add transformer'),
	                                msg: Ifresco.helper.Translations.trans('There is a similar transformer definied!')
	                            });
	                		}
	                		else {
		                		grid.getStore().add(new Ifresco.model.AutoOCRTransformers({
		                			settingsName:settingsName,
		                			sourceMimetype:sourceMimetype,
		                			targetMimetype:targetMimetype,
		                			autoOCRExtension:targetMimetype
		                			})
		                		);
	                		}
	                	},
	                	scope: this
	                }]
	            }]
	        }]
        });

        this.callParent();
    }
});