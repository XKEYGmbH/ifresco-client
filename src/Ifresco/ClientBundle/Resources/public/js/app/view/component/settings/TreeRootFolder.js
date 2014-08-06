Ext.define('Ifresco.view.settings.TreeRootFolder', {
    extend: 'Ext.form.Panel',
    alias: 'widget.ifrescoViewSettingsTreeRootFolder',
    border: 0,
    defaults: {
        margin: 5
    },
    autoScroll: true,
    cls: 'ifresco-view-settings-treerootfolder',
    initComponent: function() {
        var me = this;
        Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('Tree Root Folder'),
            tbar: [{
            	iconCls: 'ifresco-icon-save',
            	text: Ifresco.helper.Translations.trans('Save'),
                handler: function() {
                    this.fireEvent('save');
                },
                scope: this
            }],
            items: [{
                xtype: 'panel',
                layout: {
                    type: 'table',
                    columns: 2
                },
                defaults: {
                    padding: 5
                },
                border: 0,
                cls: 'ifresco-view-table-settings-system',
                items: [{
                    xtype: 'box',
                    html: Ifresco.helper.Translations.trans('Select root folder') + ':',
                    cellCls: 'ifresco-view-settings-row-left',
                    width: 200
                },{
                    xtype: 'container',

                    border: 0,
                    cellCls: 'ifresco-view-settings-row-right',
                    items: [{
                        xtype: 'treepanel',
                        height: 300,
                        rootVisible: false,
                        isFormField: true,
                        isValid: function() {
                            return true;
                        },
                        validate: function() {
                            return this.getChecked().length == 1;
                        },
                        isFileUpload: function() {
                            return false;
                        },
                        getSubmitData: function() {
                            var value = '';
                            var returnValue = {};
                            if (this.getChecked()[0]) {
                                value = this.getChecked()[0].raw.id;
                            }
                            returnValue.treeRootFolder = value;
                            return returnValue;
                        },
                        store: {
                            proxy: {
                                url: Routing.generate('ifresco_client_admin_check_tree_get'),
                                type: 'ajax',
                                actionMethods: 'POST'
                            },
                            root: {
                                nodeType: 'async',
                                id: 'root',
                                draggable: false,
                                text: 'CategoryTree',
                                expanded: true,
                                visible: false,
                                border: false
                            },
                            listeners: {
                                load: function(store, parentNode) {
                                    var treePanel = me.down('treepanel');
                                    if (treePanel.nodeId && treePanel.getChecked().length == 0) {
                                        parentNode.eachChild(function(node) {
                                            if (node.raw.id == treePanel.nodeId) {
                                                node.set('checked', true);
                                            }
                                        });
                                    }
                                }
                            }
                        },
                        listeners: {
                            checkchange: function(node, checked) {
                                if (checked == true) {
                                    var treePanel = me.down('treepanel');
                                    treePanel.nodeId = null;
                                    var treeChecked = this.getView().getChecked();
                                    Ext.each(treeChecked, function(checkedNode) {
                                        if (node != checkedNode) {
                                            checkedNode.set('checked', false);
                                        }
                                    });

                                }
                            }
                        },
                        scope: this
                    }]
                }]
            }]
        });

        this.callParent();
    },

    scope: this,

    listeners: {
        // after render create lookups
    },

    loadTreeData: function(data) {
        if (data && data.nodeId) {
            var treePanel = this.down('treepanel');
            treePanel.nodeId = data.nodeId;
            treePanel.getRootNode().eachChild(function (node) {
                if (node.raw.id == treePanel.nodeId) {
                    node.set('checked', true);
                }
            });
        }
    }
});