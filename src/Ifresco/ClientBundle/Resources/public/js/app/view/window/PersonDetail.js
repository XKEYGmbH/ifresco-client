Ext.define('Ifresco.view.window.PersonDetail', {
    extend: 'Ext.window.Window',
    alias: 'widget.ifrescoViewWindowPersonDetail',
    modal:true,
    layout:'fit',
    width:800,
    autoHeight:true,
    closeAction:'destroy',
    constrain: true,
    plain: true,
    resizable: true,
    minHeight: 651,
    maxHeight: 651,
    minWidth:800,
    parent: null,
    userName: null,

    initComponent: function () {
    	var groupStore = Ifresco.store.Groups.create({listeners: {
            load: function(store) {
            	var rawData = store.proxy.reader.rawData,
            		selectedGroups = rawData.selectedGroups,
            		values = [];
                console.log("LOADED GROUP STORE FOR GROUP LIST",store,selectedGroups);
                
                Ext.each(selectedGroups, function (group) {
                	values.push(group.displayName);
                });
                
                this.down('itemselector').setValue(values);
            },
            scope: this
        }});
    	
    	groupStore.proxy.extraParams = {userName: this.userName};
    	console.log("GROUP STORE",groupStore);
        
        Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('User Profile'),
            tbar: [{
                iconCls: 'ifresco-icon-edit',
                cls: 'ifresco-user-edit-button',
                text: Ifresco.helper.Translations.trans('Enable Editing'),
                handler: function(){
                	this.setReadOnlyForAll(false);
                	this.down('button[cls~=ifresco-user-save-button]').setVisible(true);
                },
                scope: this
            }],
            items: [{
                xtype: 'form',
                itemId: 'userProfileForm',
                border: 0,
                bodyPadding: 5,
                layout: {
                    type: 'hbox',
                    align: 'stretch'
                },
                fieldDefaults: {
                    labelAlign: 'right',
                },
                items: [{
                	xtype: 'container',
                	layout: 'anchor',
                    defaults: {
                        anchor: '100%'
                    },
                    margin: 5,
                    bodyPadding: 5,
                    flex: 1,
                	items: [{
	                    xtype: 'fieldset',
	                    title: Ifresco.helper.Translations.trans('Information'),
	                    defaultType: 'textfield',
	                    layout: 'anchor',
	                    defaults: {
	                        anchor: '100%'
	                    },
	                    items: [{
	                    	xtype: 'container',
	                        layout: 'hbox',
	                        defaultType: 'textfield',
	                        margin: '0 0 5 0',
	                        items: [{
	                            name: 'firstName',
	                            fieldLabel: Ifresco.helper.Translations.trans('First Name'),
	                            flex: 1,
	                            allowBlank: false
	                        }, {
	                            name: 'lastName',
	                            margin: '0 0 6 0',
	                            fieldLabel: Ifresco.helper.Translations.trans('Last Name'),
	                            flex: 1,
	                            allowBlank: false
	                        }]
	                    },{
	                        fieldLabel: 'Email Address',
	                        name: 'email',
	                        vtype: 'email',
	                        flex: 1,
	                        allowBlank: false
	                    }]
	                },{
	                    xtype: 'fieldset',
	                    title: Ifresco.helper.Translations.trans('Contact Information'),
	                    defaultType: 'textfield',
	                    layout: 'anchor',
	                    defaults: {
	                        anchor: '100%'
	                    },
	                    items: [{
	                    	xtype: 'container',
	                        layout: 'vbox',
	                        defaultType: 'textfield',
	                        margin: '0 0 5 0',
	                        items: [{
	                            name: 'skype',
	                            fieldLabel: Ifresco.helper.Translations.trans('Skype')
	                        },{
	                            name: 'telephone',
	                            fieldLabel: Ifresco.helper.Translations.trans('Phone')
	                        },{
	                            name: 'mobile',
	                            fieldLabel: Ifresco.helper.Translations.trans('Mobile')
	                        },{
	                            name: 'instantmsg',
	                            fieldLabel: Ifresco.helper.Translations.trans('IM')
	                        },{
	                            name: 'googleusername',
	                            fieldLabel: Ifresco.helper.Translations.trans('Google Username')
	                        }]
	                    }]
	                },{
	                    xtype: 'fieldset',
	                    title: Ifresco.helper.Translations.trans('Company Details'),
	                    defaultType: 'textfield',
	                    layout: 'anchor',
	                    defaults: {
	                        anchor: '100%'
	                    },
	                    items: [{
	                    	xtype: 'container',
	                        layout: 'vbox',
	                        defaultType: 'textfield',
	                        margin: '0 0 5 0',
	                        items: [{
	                            name: 'organization',
	                            fieldLabel: Ifresco.helper.Translations.trans('Name')
	                        },{
	                            name: 'companyaddress1',
	                            fieldLabel: Ifresco.helper.Translations.trans('Adress')
	                        },{
	                            name: 'companytelephone',
	                            fieldLabel: Ifresco.helper.Translations.trans('Phone')
	                        },{
	                            name: 'companyfax',
	                            fieldLabel: Ifresco.helper.Translations.trans('Fax')
	                        },{
	                            name: 'companyemail',
	                            fieldLabel: Ifresco.helper.Translations.trans('Email')
	                        }]
	                    }]
	                },{
	                    xtype: 'fieldset',
	                    title: Ifresco.helper.Translations.trans('More Information'),
	                    defaultType: 'textfield',
	                    layout: 'anchor',
	                    defaults: {
	                        anchor: '100%'
	                    },
	                    items: [{
	                    	xtype: 'container',
	                        layout: 'vbox',
	                        defaultType: 'textfield',
	                        margin: '0 0 5 0',
	                        items: [{
	                            name: 'userName',
	                            fieldLabel: Ifresco.helper.Translations.trans('Username')
	                        },{
	                            name: 'groups',
	                            fieldLabel: Ifresco.helper.Translations.trans('Groups')
	                        },{
	                            name: 'enabled',
	                            xtype: 'checkbox',
	                            inputValue: true,
	                            fieldLabel: Ifresco.helper.Translations.trans('Enabled')
	                        }]
	                    }]
	                }]
                },{
                	xtype: 'container',
                	layout: 'anchor',
                    defaults: {
                        anchor: '100%'
                    },
                    margin: 5,
                    bodyPadding: 5,
                    flex:1,
                    items: [{
                        xtype: 'itemselector',
                        store: groupStore,
                        width: '100%',
                        height: 538,
                        fromTitle: Ifresco.helper.Translations.trans('Groups'),
                        toTitle: Ifresco.helper.Translations.trans('Selected'),
                        labelAlign: 'top',
                        name: 'groups',
                        isFormField: true,
                        displayField: 'displayName',
                        valueField: 'shortName',
                        buttons: ['add', 'remove'],
                        getSubmitData: function () {
                            /*var key = this.name;
                            var returnVal = {};
                            if (key) {
                                returnVal[key] = this.getValue().join(',');
                            }
                            return returnVal;*/
                        	return this.getValue();
                        },
                        listeners: {
                        	afterrender: function() {
                        		
                        	},
                        	scope: this
                        }
                    }]
                }]
            }],
            buttons: [{
                text: Ifresco.helper.Translations.trans('Save'),
                cls: 'ifresco-user-save-button',
                hidden: true,
                handler: function() {
                    var window = this;
                    var values = this.down('form').getValues();
                    
                    Ext.Ajax.request({
                        url: Routing.generate('ifresco_client_user_management_update_person'),
                        params: {
                        	data: Ext.encode(values),
                        },
                        success: function (req) {
                        	console.log("MY PARENT IS",window,window.parent)
                            if(window.parent) {
                                window.parent.getStore().reload();
                            }
                            
                            window.close();
                        },
                        failure: function (data) {
                            var result = Ext.decode(data.responseText);
                            Ext.MessageBox.show({
                                title: Ifresco.helper.Translations.trans('Error'),
                                msg: result.data.message,
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.WARNING
                            });
                        }
                    });
                },
                scope: this
            },{
                text: Ifresco.helper.Translations.trans('Close'),
                handler: function() {
                    this.close();
                },
                scope: this
            }]
        });

        this.callParent();
    },
    
    setReadOnlyForAll: function (bReadOnly) {
    	this.down("form").getForm().getFields().each (function (field) {
    		console.log("SET READONLY FOR ",field,field.xtype);
    		if (field.xtype && field.xtype != 'itemselector' && field.xtype != 'multiselectfield')
    			field.setReadOnly(bReadOnly);
        });
    },
    
    loadRecord: function(record) {
    	console.log("PERSON DETAIL LOAD RECORD",record);
    	this.down("form").getForm().loadRecord(record);
    	this.setReadOnlyForAll(true);
    }
});
