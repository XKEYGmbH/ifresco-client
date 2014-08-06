Ext.define('Ifresco.view.MetadataForm', {
	extend: 'Ext.panel.Panel',
    /*constructor: function(config) {
        Ext.apply(this, config, {
            conditions: {}
        });
    },*/
    
	createField: function (field, config) {
    	console.log("ADVANCED SEARCH CREATE FIELD",field,config);
        var fieldEl;
        switch (field.type) {
            case 'content':
                fieldEl = this.createTextField(field);
                break;
            case 'textarea':
                //fieldEl = this.createTextareaField(field);
                //break;
            case 'mltext': 
            case 'text': 
                fieldEl = this.createTextField(field);
                break;
            case 'boolean':
                fieldEl = this.createBooleanField(field);
                break;
            case 'date':
                fieldEl = this.createDateField(field);
                break;
            case 'datetime':
                fieldEl = this.createDateTimeField(
                    field
                );
                break;
            case 'long':
            case 'double':
            case 'float':
            case 'int':
                //fieldEl = this.createNumberField(field); 
            	fieldEl = this.createTextField(field);
                break;
            case 'hidden':
                fieldEl = this.createHiddenField(field);
                break;
            case 'combo':
                fieldEl = this.createComboField(field);
                break;
            case 'superboxselect':
                fieldEl = this.createMultiComboField(field);
                break;
            case 'category':
                fieldEl = this.createCategoryField(field);
                break;
            default:
                console.warn('Unexpected field type:', field.type, 'in:', field);
                return null;
        }

        return fieldEl;
    },

    createTabPanel: function () {
        return {
            xtype: 'tabpanel',
            region: 'south',
            cls: 'ifresco-view-advancedsearch-tabs',
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

    createTab: function (tabConfig, formConfig) {
        var items = [];
        Ext.each(tabConfig.fields, function (field) {
            items.push(this.createField(field, formConfig));
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

    /*createTextareaField: function (fieldConfig) {
        return {
            xtype: "textarea",
            fieldLabel: fieldConfig.fieldLabel,
            labelAlign: 'top',
            name: fieldConfig.name
        };
    },*/
    
    createTextField: function (fieldConfig) {
        return {
            xtype: 'textfield',
            labelAlign: 'top',
            name: fieldConfig.name,
            fieldLabel: fieldConfig.fieldLabel || '(empty label)',
            allowBlank: ! fieldConfig.required,
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

    createNumberField: function (fieldConfig) {
        return {
            xtype: 'numberfield',
            labelAlign: 'top',
            name: fieldConfig.name,
            fieldLabel: fieldConfig.fieldLabel || '(empty label)',
            allowBlank: ! fieldConfig.required,
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

    createBooleanField: function (fieldConfig) {
        return {
            xtype: 'checkbox',
            fieldLabel: fieldConfig.fieldLabel || '(empty label)',
            name: fieldConfig.name,
            inputValue: true,
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

    createDateField: function (fieldConfig, dateFormat) {
    	var validateFunc = function(value) {
    		this.clearInvalid();
        	console.log("VALIDATE VAL",value);
        	if (value == "%TODAY%" || !value) {
        		
        		return true;
        	}
        	else {
        		var date = this.parseDate(value);
        		console.log("VALIDATE DATE",date)
                if (!date || date == null) {
                	this.markInvalid(Ext.String.format(this.invalidText, value, this.format));
                	this.getErrors();
                	return false;
                }
                return true;
        	}
        };
        var submitValFunc = function() {
	            var format = this.submitFormat || this.format,
	            value = this.getValue();
	
	        if (Ext.form.DateField.superclass.getValue.call(this) == "%TODAY%")
				return "%TODAY%";
	        return value ? Ext.Date.format(value, format) : '';
	    };
        return {
            xtype: 'fieldset',
            checkboxToggle: true,
            checkboxName: fieldConfig.name + '-checkbox',
            title: fieldConfig.fieldLabel || '(empty title)',
            defaultType: 'datefield',
            items: [{
                width: 195,
                labelWidth: 90,
                name: fieldConfig.name + '#from',
                xtype: "datefield",
                validateValue: validateFunc,
                getSubmitValue: submitValFunc,
                format: dateFormat || Ifresco.helper.Settings.get("DateFormat") || 'm/d/Y',
                fieldLabel:  Ifresco.helper.Translations.trans('From date'),
                listeners: {
                    specialkey: function(field, e){
                        if (e.getKey() == e.ENTER) {
                            this.fireEvent('search');
                        }
                    },
                    scope: this
                }
                //validateOnChange: false,
                /*getValue : function(){
                	if (Ext.form.DateField.superclass.getValue.call(this) == "%TODAY%")
                		return "%TODAY%";
                	
                    return this.parseDate(Ext.form.DateField.superclass.getValue.call(this)) || "";
                },*/
                
            }, {
                width: 195,
                labelWidth: 90,
                name: fieldConfig.name + '#to',
                xtype: "datefield",
                validateValue: validateFunc,
                getSubmitValue: submitValFunc,
                format: dateFormat || Ifresco.helper.Settings.get("DateFormat") || 'm/d/Y',
                fieldLabel:  Ifresco.helper.Translations.trans('To date'),
                listeners: {
                    specialkey: function(field, e){
                        if (e.getKey() == e.ENTER) {
                            this.fireEvent('search');
                        }
                    },
                    scope: this
                }
            }],
            listeners: {
                collapse: function(p) {
                    p.items.each(function() {
                        this.disable();
                    });
                },
                expand: function(p) {
                    p.items.each(function() {
                        this.enable();
                    });
                }
            }
        };
    },

    createDateTimeField: function (fieldConfig, dateFormat, timeFormat) {
        return {
            xtype: 'fieldset',
            checkboxToggle: true,
            checkboxName: fieldConfig.name + '-checkbox',
            title: fieldConfig.fieldLabel || '(empty label)',
            defaultType: 'datefield',
            items: [{
                xtype: "xdatetime",
                labelWidth: 90,
                disabled: true,
                width: 300,
                name: fieldConfig.name + '#from',
                fieldLabel: "From date",
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
            }, {
                xtype: "xdatetime",
                labelWidth: 90,
                width: 300,
                disabled: true,
                name: fieldConfig.name + '#to',
                fieldLabel: "To date",
                dateCfg: {
                    format: dateFormat || Ifresco.helper.Settings.get("DateFormat") || 'm/d/Y',
                },
                timeCfg: {
                    format: timeFormat || "H:i"
                }
            }],
            listeners: {
                collapse: function(p) {
                    p.items.each(function() {
                        this.disable();
                    });
                },
                expand: function(p) {
                    p.items.each(function() {
                        this.enable();
                    });
                }
            }
        };
    },

    createHiddenField: function (fieldConfig) {
        return {
            xtype: 'hiddenfield',
            name: fieldConfig.name,
            value: fieldConfig.value
        };
    },

    createCategoryField: function (fieldConfig) {
        return {
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
        };
    },

    createComboField: function (fieldConfig) {
        return {
            xtype: 'combo',
            //queryMode: 'local',
            store: fieldConfig.store,
            name: fieldConfig.name, 
            queryMode: fieldConfig.queryMode || 'local',
            fieldLabel: fieldConfig.fieldLabel,
            displayField: "name",
            valueField: "name",
            labelAlign: 'top'
        };
    },

    createMultiComboField: function (fieldConfig) {
        return {
        	xtype: 'boxselect',
            store: fieldConfig.store,
            queryMode: fieldConfig.queryMode || 'local',
            multiSelect: true,
            width: '100%',
            fieldLabel: fieldConfig.fieldLabel,
            labelAlign: 'top',
            name: fieldConfig.name,
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
            }
        };
    }
});