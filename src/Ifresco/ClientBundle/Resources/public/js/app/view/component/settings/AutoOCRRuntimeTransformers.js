Ext.define('Ifresco.view.settings.AutoOCRRuntimeTransformers', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.ifrescoViewSettingsAutoOCRRuntimeTransformers',
    cls: 'ifresco-view-settings-autoocrruntimetransformers',
    header: false,
    layout: {
        type: 'vbox',
        align : 'stretch',
        pack  : 'start'
    },
    border: 0,
    margin: 0,
    padding: 0,
    
    mimetypeStore: null,

    initComponent: function() {
    	this.mimetypeStore = Ifresco.store.AutoOCRTransformerMimetypes.create({});
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
	                text: 'Delete selected',
	                handler: function() {
	                    var grid = this.down("gridpanel");
	                    var selection = grid.getSelectionModel().getSelection();
	                    if (selection.length > 0) {
		                    Ext.MessageBox.show({
		                        buttons: Ext.MessageBox.YESNO,
		                        icon: Ext.MessageBox.QUESTION,
		                        title: Ifresco.helper.Translations.trans('Delete Runtime transformer?'),
		                        msg: Ifresco.helper.Translations.trans('Do you really want to delete the selected runtime transformer?'),
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
	                text: Ifresco.helper.Translations.trans('Source mimetype'),
	                dataIndex: 'sourceMimetype',
	                flex: 1
	            },{
	                text: Ifresco.helper.Translations.trans('Target mimetype'),
	                dataIndex: 'targetMimetype',
	                flex: 1
	            },{
	                text: Ifresco.helper.Translations.trans('Executable path'),
	                dataIndex: 'executablePath',
	                flex: 1
	            },{
	                text: Ifresco.helper.Translations.trans('Arguments'),
	                dataIndex: 'arguments',
	                flex: 1
	            }],
	            minColumnWidth: 150,
	            selType: 'rowmodel',
	            store: Ifresco.store.AutoOCRRuntimeTransformers.create({})
	        },{
	        	xtype: 'form',
	        	title: Ifresco.helper.Translations.trans('Register a new runtime transformation'),
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
	                    columns: 5
	                },
	                defaults: {
	                    padding: 5
	                },
	                border: 0,
	                items: [{
	                    xtype: 'container',
	                    border: 0,
	                    colspan: 5,
	                    html: Ifresco.helper.Translations.trans("'Arguments' must include ${sourceFile} and ${targetFile} for file IO or ${pipes} for STDIN/STDOUT IO."),
	                    anchor: '100%'
	                },{
	                	xtype: 'combobox',
	                	fieldLabel: Ifresco.helper.Translations.trans('Source mimetype'),
	                	store: this.mimetypeStore,
	                    queryMode: 'local',
	                    displayField: 'mimetype',
	                    valueField: 'mimetype',
	                    name: 'sourceMimetype',
	                    listConfig: {
	                        minWidth: 300
	                    },
	                    allowBlank: false,
	                    editable: false
	                },{
	                	xtype: 'combobox',
	                	fieldLabel: Ifresco.helper.Translations.trans('Target mimetype'),
	                	store: this.mimetypeStore,
	                    queryMode: 'local',
	                    displayField: 'mimetype',
	                    valueField: 'mimetype',
	                    name: 'targetMimetype',
	                    listConfig: {
	                        minWidth: 300
	                    },
	                    allowBlank: false,
	                    editable: false
	                },{
	                	xtype: 'textfield',
	                	fieldLabel: Ifresco.helper.Translations.trans('Executable path'),
	                	name: 'executablePath',
	                    allowBlank: false
	                },{
	                	xtype: 'textfield',
	                	fieldLabel: Ifresco.helper.Translations.trans('Arguments'),
	                	name: 'arguments',
	                    allowBlank: false
	                },{
	                	xtype: 'button',
	                	name: 'addTransformer',
	                	text: Ifresco.helper.Translations.trans('Add runtime transformer'),
	                	handler: function() {
	                		var grid = this.down("gridpanel"),
	                			sourceCombo = this.down("combobox[name~=sourceMimetype]").getValue(),
                    			targetCombo = this.down("combobox[name~=targetMimetype]").getValue(),
                    			executablePath = this.down("textfield[name~=executablePath]").getValue(),
                    			arguments = this.down("textfield[name~=arguments]").getValue();
	                		
	                		if (sourceCombo == null || 
	                				targetCombo == null ||
	                				executablePath.length < 1) {

	                			Ext.ux.ErrorMessage.show({
	                                title: Ifresco.helper.Translations.trans('Add runtime transformer'),
	                                msg: Ifresco.helper.Translations.trans('Please fill out all fields!')
	                            });
	                			return false;
	                		}
	                		
	                		if ((!arguments.match(/\$\{sourceFile\}/g) || !arguments.match(/\$\{targetFile\}/g)) &&
	                				!arguments.match(/\$\{pipes\}/g)) {
	                			
	                			Ext.ux.ErrorMessage.show({
	                                title: Ifresco.helper.Translations.trans('Add runtime transformer'),
	                                msg: Ifresco.helper.Translations.trans("'Arguments' must include ${sourceFile} and ${targetFile} for file IO or ${pipes} for STDIN/STDOUT IO.")
	                            });
	                			return false;
	                		}

	                		var searchRecord = grid.getStore().findBy(
                			    function(record, id){
                			        if(record.get('sourceMimetype') === sourceMimetype &&
                			           record.get('targetMimetype') === targetMimetype &&
                			           record.get('executablePath') === executablePath){
                			              return true;
                			        }
                			        return false;
                			    }
                			);
	                		
	                		if(searchRecord != -1) { 
	                			Ext.ux.ErrorMessage.show({
	                                title: Ifresco.helper.Translations.trans('Add runtime transformer'),
	                                msg: Ifresco.helper.Translations.trans('There is a similar runtime transformer definied!')
	                            });
	                		}
	                		else {
		                		grid.getStore().add(new Ifresco.model.AutoOCRTransformers({
		                			executablePath:executablePath,
		                			sourceMimetype:sourceMimetype,
		                			targetMimetype:targetMimetype,
		                			arguments:arguments
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