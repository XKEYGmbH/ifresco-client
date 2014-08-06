Ext.define('Ifresco.view.ValuedMetadataForm', {
	extend: 'Ext.panel.Panel',
    /*constructor: function(config) {
        Ext.apply(this, config, {
            conditions: {}
        });
    },*/
    
	createField: function (field, value, config) {
    	var type = field.type;
    	console.log("CREATE FIELD",field,type,value,config)
        var fieldEl;
        switch (type) {
            case 'mltext':
            case 'text': 
                fieldEl = this.createTextField(field, value);
                break;
            case 'boolean':
                fieldEl = this.createBooleanField(field, value);
                break;
            case 'date':
                fieldEl = this.createDateField(field, value);
                break;
            case 'datetime':
                fieldEl = this.createDateTimeField(
                    field, 
                    value
                );
                break;
            case 'long':
            case 'double':
            case 'float':
            case 'int':
                fieldEl = this.createNumberField(field, value);
                break;
            case 'hidden':
                fieldEl = this.createHiddenField(field, value);
                break;
            case 'text_constraints':
            case 'combo':
                fieldEl = this.createComboField(field, value);
                break;

            case 'superboxselect':
                fieldEl = this.createMultiComboField(field, value);
                break;
            case 'category':
                fieldEl = this.createCategoryField(field, value);
                break;
            case 'textarea':
                fieldEl = this.createTextareaField(field, value);
                break;
            case 'autocomplete':
                fieldEl = this.createAssociationField(field, value);
                break;
            case 'tags':
                fieldEl = this.createTagsField(field, value);
                break;
            default:
                console.warn('Unexpected field type:', type, 'in:', field);
                console.error('{' + type + '}');
                return null;
        }

        return fieldEl;
    },

    createTabPanel: function () {
        return {
            xtype: 'tabpanel',
            region: 'south',
            cls: 'ifresco-view-editmetadata-tabs',
            height: '50%',
            width: '50%',
            split: true,
            autoScroll: true,
            items: [{
                xtype: 'panel',
                title: 'Tab One'
            }]
        };
    },

    createTab: function (tabConfig, formConfig, data) {
        var items = [];
        Ext.each(tabConfig.fields, function (field) {
        	var fieldValue = '';
        	Ext.Object.each(data, function (key, value) {
                if (key === field.name) {
                    fieldValue = value;
                }
            });
            items.push(this.createField(field, fieldValue, formConfig));
        }, this);
        return {
            xtype: 'panel',
            title: tabConfig.title || '(empty title)',
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            maxWidth: 500,
            minWidth: 300,
            items: items,
            autoScroll: true,
            border: 0,
            bodyPadding: 10
        };
    },

    createTextField: function (fieldConfig, value) {
        return {
            xtype: 'textfield',
            labelAlign: 'top',
            name: fieldConfig.name,
            realName: fieldConfig.realName,
            fieldLabel: fieldConfig.fieldLabel || '(empty label)',
            allowBlank: ! fieldConfig.required,
            value: value,
            listeners: {
                specialkey: function(field, e){
                    if (e.getKey() == e.ENTER) {
                        this.fireEvent('search');
                    }
                },
                scope: this
            }
        };
    },

    createNumberField: function (fieldConfig, value) {
    	console.log("NUMBER VALUE IS ",value);
    	var general = {
            labelAlign: 'top',
            name: fieldConfig.name,
            realName: fieldConfig.realName,
            fieldLabel: fieldConfig.fieldLabel || '(empty label)',
            allowBlank: ! fieldConfig.required,
            value: value,
            listeners: {
                specialkey: function(field, e){
                    if (e.getKey() == e.ENTER) {
                        this.fireEvent('search');
                    }
                },
                scope: this
            },
            decimalPrecision: (fieldConfig.type == "double" || fieldConfig.type == "float" ? 2 : 0),
            allowDecimals: fieldConfig.type == "double" || fieldConfig.type == "float"
        };
    	
    	var currencyField = Ifresco.helper.CurrencyFields.getField(fieldConfig.realName);
    	if (currencyField != false) {
    		var prec = parseInt(currencyField.precision);
    		if (!(prec >= 0))
    			prec = 2;
			Ext.apply(general, {
				xtype: 'field-money',
				showSymbol: currencyField.showSymbol,
				symbol: currencyField.currencySymbol + ' ',
				symbolStay: false,
				defaultZero: false,
				thousands: currencyField.thousands,
				decimal: currencyField.decimal,
				precision: prec
			});
    	}
    	else {
    		Ext.apply(general, {
	            xtype: 'numberfield',
	            decimalPrecision: (fieldConfig.type == "double" || fieldConfig.type == "float" ? 2 : 0),
	            allowDecimals: fieldConfig.type == "double" || fieldConfig.type == "float"
	        });
    	}
    	return general;
    },

    createBooleanField: function (fieldConfig, value) {
    	console.log("BOOLEAN VALUE",value);
        return {
            xtype: 'checkbox',
            fieldLabel: fieldConfig.fieldLabel || '(empty label)',
            name: fieldConfig.name,
            realName: fieldConfig.realName,
            inputValue: true,
            checked: (value == true || value == "true" ? true : false),
            uncheckedValue: false,
            listeners: {
                specialkey: function(field, e){
                    if (e.getKey() == e.ENTER) {
                        this.fireEvent('search');
                    }
                },
                scope: this
            }
        };
    },

    createDateField: function (fieldConfig, value, dateFormat) {
        return {
            name: fieldConfig.name + '#from',
            realName: fieldConfig.realName,
            xtype: 'datefield',
            //maxWidth: 100,
            format: dateFormat || Ifresco.helper.Settings.get("DateFormat") || 'm/d/Y',
            value: value,
            fieldLabel: fieldConfig.fieldLabel,
            labelAlign: 'top'
        };
    },

    createDateTimeField: function (fieldConfig, value, dateFormat, timeFormat) {
        return {
            xtype: "xdatetime",
            width: 300,
            labelAlign: 'top',
            fieldLabel: fieldConfig.fieldLabel,
            value: value,
            name: fieldConfig.name,
            realName: fieldConfig.realName,
            dateCfg: {
                format: dateFormat || Ifresco.helper.Settings.get("DateFormat") || 'm/d/Y',
                listeners: {
                    specialkey: function(field, e){
                        if (e.getKey() == e.ENTER) {
                            this.fireEvent('search');
                        }
                    },
                    scope: this
                }
            },
            timeCfg: {
                format: timeFormat || "H:i",
                listeners: {
                    specialkey: function(field, e){
                        if (e.getKey() == e.ENTER) {
                            this.fireEvent('search');
                        }
                    },
                    scope: this
                }
            }
        };
    },

    createHiddenField: function (fieldConfig, value) {
        return {
            xtype: 'hiddenfield',
            name: fieldConfig.name,
            realName: fieldConfig.realName,
            value: value
        };
    },


    createCategoryField: function (fieldConfig, value) {
    	return {
    		xtype:'panel',
    		layout:'vbox',
    		border:false,
    		flex: 1,
    		items: [{
	    		xtype: 'button', 
	    		text: Ifresco.helper.Translations.trans('Change Categories'),
	    		handler: function() {
	    			var window = Ifresco.view.window.Category.create({
	            		parent: this
	        		});
	        		
	        		window.show();
	    		},
	    		scope: this
	    	},{
	    		xtype: 'dataview',
	    		cls: 'ifresco-edit-metadata-categories-view',
	    		layout: 'fit',
	    		autoScroll: true,
	    		flex: 1,
	    		width: '100%',
	    		formBind: true,
	    		tpl: [
	                  '<ul class="ifresco-edit-metadata-categories">',
	                  '<tpl for=".">',
	                      '<li class="category-item">',
	                      	'<div class="category-name">{path}</div> <div class="category-delete"></div>',
	                      '</li>',
	                  '</tpl></ul>',
	                  '<div class="x-clear"></div>'
	             ],
	             store: {
	                 fields: ['id', 'path'],
	                 proxy: {
	                     type: 'memory'
	                 },
	                 data: fieldConfig.categoriesData
	             },
	             multiSelect: false,
	             trackOver: true,
	             overItemCls: 'x-item-over',
	             itemSelector: 'li.category-item',
	             selectedItemCls: 'x-item-selected',
	             listeners: {
	                 itemmousedown: function (me, record, item, index, e) {
	                     var className = e.target.className;
	                     if ("category-delete" == className) {
	                    	 var store = me.getStore();
	                         store.removeAt(index);
	                     }
	                 }
	             }
	    	}]
    	};
        /*return {
            xtype: 'treepanel',
            height: 200,
            rootVisible: false,
            name: fieldConfig.name,
            loaded: false,
            isFormField: true,
            activeError: null,
            isValid: function() {
                return this.disabled || Ext.isEmpty(this.getErrors());
            },
            isDirty: function() {
                return false;
            }, 
            validate: function() {
                var isValid = this.isValid();
                if (isValid !== this.up('form').wasValid) {
                    this.wasValid = isValid;
                    this.fireEvent('validitychange', this, isValid);
                }
                return isValid;
            },
            getErrors: function() {
                var msg;
                var fieldValue = this.getChecked();
                this.errors = [];

                if (Ext.isFunction(this.validator)) {
                    msg = this.validator.call(this, fieldValue);

                    if (msg !== true) {
                        this.errors.push(msg);
                    }
                }
                return this.errors;
            },
            validator: function() {
                return true;
            },
            getName: function () {
                return this.name;
            },
            isFileUpload: function() {return false;},
            getSubmitData: function() {
                var key = this.name,
                    returnVal = {},
                    records,
                    vals = [];
                records = this.getChecked();
                Ext.each(records, function (record) {
                    vals.push(record.get('id'));
                });
                returnVal[key] = vals.join(',');
                return returnVal;
            },
            unsetActiveError: function() {
                this.activeError = null;
            },
            setActiveError: function(error) {
                this.activeError = error;
            },
            getActiveErrors: function() {
                return [this.activeError];
            },
            store: {
                autoLoad: true,
                proxy: {
                    url: fieldConfig.url,
                    type: 'ajax',
                    actionMethods: 'POST'
                },
                root: {
                    id: 'root',
                    expanded: true,
                    visible: false
                }
            }
        };*/
    },

    /*createComboField: function (fieldConfig, value) {
        return {
            xtype: 'combo',
            queryMode: 'local',
            store: fieldConfig.store,
            name: fieldConfig.name,
            fieldLabel: fieldConfig.fieldLabel,
            labelAlign: 'top'
        };
    },

    createMultiComboField: function (fieldConfig, value) {
        return {
            xtype: 'itemselector',
            store: fieldConfig.store,
            width: '100%',
            fieldLabel: fieldConfig.fieldLabel,
            labelAlign: 'top',
            name: fieldConfig.name,
            isFormField: true,
            getSubmitData: function () {
                var key = this.name;
                var returnVal = {};
                if (key) {
                    returnVal[key] = this.getValue().join(',');
                }
                return returnVal;
            }
        };
    },*/
    
    createComboField: function (fieldConfig, value) {
    	console.log("CREATE COMBOFIELD",fieldConfig)
        var item = {
            xtype: 'combo',
            //queryMode: 'local',
            store: fieldConfig.store,
            name: fieldConfig.name, 
            realName: fieldConfig.realName,
            queryMode: fieldConfig.queryMode || 'local',
            fieldLabel: fieldConfig.fieldLabel,
            displayField: "name",
            valueField: "name",
            labelAlign: 'top',
            value: value,
            relatedValue: null,
            listeners: {
            	select: function(combo, records, eOpts) {
            		var me = this;
            		console.log("SELECT COMBOBOX",combo);
            		var rec = records[0];
                    var mapData = rec.raw.mapData || false;
                    console.log("GET MAP DATA",mapData)
                    if(mapData) {
                        var form = this.down("form").getForm();

                        Ext.iterate(mapData, function (field, value) { 
                        	var field = me.down("[realName="+field+"]");
                        	if(field) {
                        		field.setValue(value);
                            }
                        });
                        /*for(var i in mapData) {
                            var columnName = combo.id.match(/^\d+/)[0]+i;
                            console.log("SEARCH FOR COLUMNNAME",columnName);
                            var el = form.findField(columnName);
                            if(el) {
                                el.setValue(mapData[i]);
                            }
                        }*/
                    }
            	},
            	scope: this
            }
        };
    	
    	var datasourcerel = fieldConfig.datasourcerel || false;
    	if (datasourcerel) {
    		/*var relCol = datasourcerel.relatedcolumn || false;
            //var relField = this.down("form").getForm().findField(relCol);
            var relField = this.down("[realName="+relCol+"]");
            console.log("SEARCH REL FIELD ",relCol,relField)
            
            if(relField != null) {*/
    			item.datasourcerel = fieldConfig.datasourcerel;
            	item.listeners.beforequery = function(queryEvent, eOpts) {
            		console.log("BEFORE QUERY",queryEvent);
                    var relColumn = queryEvent.combo.datasourcerel.relatedcolumn || false;
                    //relColumn = relColumn.replace(':', '_');
                    //var relFieldEl =  this.down("form").getForm().findField(relColumn);
                    var relFieldEl = this.down("[realName="+relColumn+"]");
                    var relValue = relFieldEl.getValue();
                    var relLabel = relFieldEl.getFieldLabel();

                    console.log("BEFORE QUERY DATA",relColumn,relFieldEl,relValue,relLabel);
                    if(Ext.isEmpty(relValue)) {
                        queryEvent.cancel = true;
                        var toolTip = Ext.create('Ext.tip.ToolTip', {
                            target: queryEvent.combo.id,
                            title: queryEvent.combo.fieldLabel,
                            html: 'Value for <b>' + relLabel + '</b> must be supplied first',
                            autoHide : true,
                            anchor: 'right',
                            closeAction: 'destroy',
                            width : 240,
                            listeners: {
                                hide: function( t, eOpts ) {
                                    t.close();
                                }
                            }
                        });

                        toolTip.show()
                    }
                    
                    if (queryEvent.combo.relatedValue != null && queryEvent.combo.relatedValue != relValue) // FIX AUTO RELOAD
                    	queryEvent.combo.store.reload();
                    
                    queryEvent.combo.relatedValue = relValue;
                };

                item.store.listeners = {
                	beforeload: function(store, operation, eOpts) {
                		console.log("BEFORE LOAD COMBOX STORE",store,fieldConfig)
                		/*var relColumn = this.datasourcerel.relatedcolumn || false;
                        relColumn = this.id.match(/^\d+/)[0]+relColumn.replace(':', '_');
                        var relFieldEl = metaForm<?php echo $containerName; ?>.getForm().findField(relColumn);*/
                		var relColumn = fieldConfig.datasourcerel.relatedcolumn || false;
                        var relFieldEl = this.down("[realName="+relColumn+"]");
                        var relValue = relFieldEl.getValue();

                        relValue = typeof relValue == 'string' ? relValue : relValue.join(', ');

                        operation.params.firstParam = relValue
                	},
                	scope: this
                }
                /*record.store.on('beforeload',function(store, operation, eOpts) {
                    var relColumn = this.datasourcerel.relatedcolumn || false;
                    relColumn = this.id.match(/^\d+/)[0]+relColumn.replace(':', '_');
                    var relFieldEl = metaForm<?php echo $containerName; ?>.getForm().findField(relColumn);
                    var relValue = relFieldEl.getValue();

                    relValue = typeof relValue == 'string' ? relValue : relValue.join(', ');

                    operation.params.firstParam = relValue
                }, record);*/
            //}
    	}
    	console.log("COMBI FIELD FINAL",item)
    	return item;
    },

    createMultiComboField: function (fieldConfig, value) {
        return {
        	xtype: 'boxselect',
            store: fieldConfig.store,
            queryMode: fieldConfig.queryMode || 'local',
            multiSelect: true,
            width: '100%',
            fieldLabel: fieldConfig.fieldLabel,
            labelAlign: 'top',
            name: fieldConfig.name,
            realName: fieldConfig.realName,
            isFormField: true,
            displayField: "name",
            valueField: "name",
            typeAhead: true,
            forceSelection: false,
            getSubmitData: function () {
                var key = this.name;
                var returnVal = {};
                if (key) {
                    returnVal[key] = this.getValue().join(',');
                }
                return returnVal;
            },
            value: value
        };
    },

    createTextareaField: function (fieldConfig, value) {
        return {
            xtype: "textarea",
            fieldLabel: fieldConfig.fieldLabel,
            labelAlign: 'top',
            name: fieldConfig.name,
            realName: fieldConfig.realName
        };
    },

    /*createTagsField: function (fieldConfig, value) {
    	console.log("CREATE TAGS FIELD",fieldConfig)
        return {
            xtype: "tagsfield",
            fieldLabel: fieldConfig.fieldLabel,
            width: '100%',
            name: fieldConfig.name,
            //tagsStore: fieldConfig.store,
            labelAlign: 'top'
        };
    },*/
    
    createTagsField: function (fieldConfig, value) {
    	console.log("CREATE TAGS FIELD",fieldConfig)
        return {
        	xtype: 'boxselect',
            store: fieldConfig.store,
            queryMode: fieldConfig.queryMode || 'local',
            multiSelect: true,
            width: '100%',
            fieldLabel: fieldConfig.fieldLabel,
            labelAlign: 'top',
            name: fieldConfig.name,
            realName: fieldConfig.realName,
            isFormField: true,
            displayField: "name",
            valueField: "name",
            typeAhead: true,
            createNewOnEnter: true,
            forceSelection: false,
            getSubmitData: function () {
                var key = this.name;
                var returnVal = {};
                if (key) {
                    returnVal[key] = this.getValue().join(',');
                }
                return returnVal;
            },
            value: value
        };
    },

    createAssociationField: function (fieldConfig, value) {
        var store = Ext.create('Ext.data.Store', {
            proxy: {
                type: 'memory'
            },
            fields: [{
                name: 'nodeId',
                type: 'string'
            }, {
                name: 'nodeCmName',
                type: 'string'
            }, {
                name: 'extension',
                type: 'string'
            }]
        });
        return {
            xtype: 'panel',
            border: 0,
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            items: [{
                xtype: 'combo',
                store: {
                    proxy: {
                        type: 'ajax',
                        url : fieldConfig.autoCompleteUrl,
                        reader: {
                            type: 'json',
                            root: 'data'
                        }
                    },
                    fields: [{
                        name: 'nodeId',
                        type: 'string'
                    }, {
                        name: 'nodeCmName',
                        type: 'string'
                    }, {
                        name: 'extension',
                        type: 'string'
                    }]
                },
                displayField: 'nodeCmName',
                typeAhead: false,
                queryParam: 'q',
                height: 20,
                hideLabel: true,
                hideTrigger: true,
                anchor: '100%',
                listeners: {
                    select: function (combo, selectedRecords) {
                        if (selectedRecords[0] 
                            && store.findRecord('nodeCmName', selectedRecords[0].get('nodeCmName')) === null) {
                            store.add(selectedRecords[0]);
                        }
                        combo.setValue('');
                        combo.up('panel').down('dataview').updateHeight();
                    }
                }
            }, {
                xtype: 'dataview',
                cls: 'ifresco-autocomplete',
                scrollable: true,
                updateHeight: function () {
                    this.setHeight(store.getCount() * 20);
                },
                margin: '5 0 0 0',
                itemCls: 'ifresco-template-property',
                store: store,
                itemTpl: [
                        '<div class="x-tool-close x-tool-img ifresco-template-property-close"> </div>',
                        '<div class="ifresco-template-propert">{nodeCmName}</div>'
                ].join(''),
                listeners: {
                    itemclick: function(view, record, el, index, e) {
                        var cls = e.target.getAttribute('class');
                        if (cls && cls.indexOf('x-tool-close') > -1) {
                            this.getStore().remove(record);
                        }
                        view.updateHeight();
                    }
                }
            }]

        };
    }
});