Ext.define('Ifresco.view.window.CreateSpace', {
    extend: 'Ext.window.Window',
    alias: 'widget.ifrescoViewWindowCreateSpace',
    modal:true,
    layout:'fit',
    width:500,
    height:225,
    closeAction:'hide',
    constrain: true,
    plain: true,
    resizable: false,
    nodeId: null,
    parent: null,

    initComponent: function () {
        Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('Create Space'),
            items: [{
                xtype: 'form',
                itemId: 'createSpaceForm',
                border: 0,
                layout: {
                    type: 'vbox',
                    align: 'stretch'
                },
                items: [{
                    xtype: 'textfield',
                    padding: '5 5 5 5',
                    fieldLabel: Ifresco.helper.Translations.trans('Name'),
                    name: 'properties[cm_name]'
                },{
                    xtype: 'textfield',
                    padding: '5 5 5 5',
                    fieldLabel: Ifresco.helper.Translations.trans('Title'),
                    name: 'properties[cm_title]'
                },{
                    xtype: 'textarea',
                    padding: '5 5 5 5',
                    fieldLabel: Ifresco.helper.Translations.trans('Description'),
                    name: 'properties[cm_description]'
                }]
            }],
            buttons: [{
                text: Ifresco.helper.Translations.trans('Save'),
                handler: function() {
                    var window = this;
                    var values = this.down('form').getValues();
                    values.nodeId = this.nodeId;
                    Ext.Ajax.request({
                        url: Routing.generate('ifresco_client_folder_actions_space_create'),
                        params: values,
                        success: function (req) {
                        	var data = Ext.decode(req.responseText);
                        	
                        	console.log("window paraent",window,window.parent)
                            if(window.parent) {
                            	/*if (window.parent.xtype == 'ifrescoTreeFolder') {
                            		var store = window.parent.getStore();
                            		var root = store.getById(window.nodeId);
                            		if (root) {
                            			root.appendChild({
                            			    text: data.data.text,
                            			    nodeId: data.data.nodeId,
                            			    id: data.data.nodeId,
                            			    leaf: false,
                            			    qtip: data.data.title,
                            			    alfresco_perm_edit: true,
                            			    alfresco_perm_delete: true,
                            			    alfresco_perm_cancel_checkout: true,
                            			    alfresco_perm_create: true,
                            			    alfresco_perm_permissions: true
                            			});
                            			
                            			if (!root.isExpanded())
                            				root.expand();
                            		}
                            	}
                            	else {
                            		window.parent.getStore().reload();
                            	}*/
                            	
                            	if (window.parent.xtype != 'ifrescoTreeFolder') {
                            		window.parent.getStore().reload();
                            	}
                            	
                            	var tree = Ext.ComponentQuery.query("ifrescoTreeFolder")[0];
                        		var store = tree.getStore();
                        		var root = store.getById(window.nodeId);
                        		if (root) {
                        			var treeModel = {
                        			    text: data.data.text,
                        			    nodeId: data.data.nodeId,
                        			    id: data.data.nodeId,
                        			    leaf: false,
                        			    qtip: data.data.title,
                        			    alfresco_perm_edit: true,
                        			    alfresco_perm_delete: true,
                        			    alfresco_perm_cancel_checkout: true,
                        			    alfresco_perm_create: true,
                        			    alfresco_perm_permissions: true
                        			};
                        			
                        			if (!root.isExpanded())
                        				root.expand();
                        			//console.log("ADD SPACE ",treeModel);
                        			root.appendChild(treeModel);	
                        		}

                            }

                            //TODO: reload tree
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
                    this.hide();
                },
                scope: this
            }]
        });

        this.callParent();
    }
});
