Ext.define('Ifresco.view.MetadataTab', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.ifrescoViewMetadataTab',
    cls: 'ifresco-metadata-tab',
    border: 0,
    bodyPadding: '0 0 0 5',
    deferredRender: false,
    configData: null,
    autoScroll: true,
    nodeId: null,
    isParent: false,
    ifrescoId: null,
    layout: {
        type: 'vbox',
        align: 'stretch'
    },

    initComponent: function() {
    	
        Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('Metadata'),
            items: [{
                xtype: 'panel',
                cls: 'ifresco-metadata-data-panel',
                border: 0,
                flex: 1,
                layout: {
                    type: 'hbox',
                    align: 'stretch'
                }
            }],
            dockedItems: [{
                dock: 'top',
                xtype: 'toolbar',
                items: this.getCurrentToolBar()
            },{
                dock: 'top',
                xtype: 'toolbar',
                cls: 'ifresco-metadata-lock-tooltip',
                closable: true,
                hidden: true,
                layout: {
                    type: 'vbox',
                    align: 'center'
                },
                items: [{
                    xtype: 'tbtext',
                    cls: 'ifresco-metadata-lock-tooltip-text',
                    text: ''
                },{
                    xtype: 'button',
                    cls: 'ifresco-metadata-lock-tooltip-link',
                    text: '',
                    handler: function (button) {
                        console.log('Open detailView', button.nodeId);
                    }
                }]
            },{
                dock: 'top',
                xtype: 'toolbar',
                cls: 'ifresco-metadata-path',
                layout: {
                    type: 'hbox',
                    align: 'stretch'
                }
            },{
                dock: 'top',
                xtype: 'toolbar',
                padding: 0,
                layout: {
                    type: 'hbox',
                    align: 'middle'
                },
                height: 30,
                margin: 0,
                    items: [{
                        xtype: 'tbtext',
                        padding: '5 5 0 5',
                        text: Ifresco.helper.Translations.trans('Node URL')
                    },{
                        xtype: 'textfield',
                        value: '',
                        cls: 'ifresco-metadata-nodeurl',
                        flex: 1,
                        maxWidth: 600,
                        selectOnFocus: true,
                        hideLabel: true,
                        readOnly: true
                    }]
            }]
        });
        this.callParent();
    },

    getCurrentToolBar: function() {
        return [{
            iconCls: 'ifresco-metadata-edit-button',
            cls: 'ifresco-metadata-edit',
            tooltip: Ifresco.helper.Translations.trans('Edit Metadata'),
            disabled: true,
            handler: function() {
                var config = this.up('panel[cls~=ifresco-view-content-tab]').localConfigData;
                this.fireEvent('editMetadata', config.PanelNodeId, config.PanelNodeText);
            },
            scope: this
        }, {
            iconCls: 'ifresco-manage-aspects-button',
            cls: 'ifresco-metadata-manage-aspects',
            tooltip: Ifresco.helper.Translations.trans('Manage Aspects'),
            disabled: true,
            handler: function() {
                var config = this.up('panel[cls~=ifresco-view-content-tab]').localConfigData;
                this.fireEvent('manageAspects', config.PanelNodeId, this);
            },
            scope: this
        }, {
            iconCls: 'ifresco-specify-type-button',
            cls: 'ifresco-metadata-specify-type',
            tooltip: Ifresco.helper.Translations.trans('Specify Type'),
            disabled: true,
            handler: function() {
                var config = this.up('panel[cls~=ifresco-view-content-tab]').localConfigData;
                this.fireEvent('specifyType', [config.PanelNodeId], this);
            },
            scope: this
        }, '-', {
            iconCls: 'ifresco-download-node-button',
            cls: 'ifresco-metadata-download-content',
            tooltip: Ifresco.helper.Translations.trans('Download'),
            disabled: true,
            handler: function() {
                var config = this.up('panel[cls~=ifresco-view-content-tab]').localConfigData;
                var dlUrl = Routing.generate('ifresco_client_node_actions_download', {nodeId: config.PanelNodeId});
                window.open(dlUrl);
            },
            scope: this
        }, '-', {
            iconCls: 'ifresco-checkout-node-button',
            cls: 'ifresco-metadata-checkout',
            tooltip: Ifresco.helper.Translations.trans('Checkout'),
            disabled: true,
            handler: function() {
                var config = this.up('panel[cls~=ifresco-view-content-tab]').localConfigData;
                if (config.PanelNodeIsCheckedOut === true) {
                    this.fireEvent('checkIn', config.PanelNodeCheckedOutId, this);
                } else {
                    this.fireEvent('checkOut', config.PanelNodeId, this);
                }
            },
            scope: this
        }, {
            iconCls: 'ifresco-zoho-writer-button',
            cls: 'ifresco-metadata-zoho-writer',
            tooltip: Ifresco.helper.Translations.trans('Checkout to Zoho Writer'),
            disabled: true,
            hidden: this.configData.OnlineEditing && this.configData.OnlineEditing == "zoho" ? false : true,
            handler: function() {
                //TODO: this functional is disabled
                var config = this.up('panel[cls~=ifresco-view-content-tab]').localConfigData;
                var mime = config.PanelNodeMimeType;
                if (config.PanelNodeIsCheckedOut === true) {
                    this.fireEvent('editInZoho', config.PanelNodeCheckedOutId, mime);
                } else {
                    this.fireEvent('checkOutZoho', config.PanelNodeId, mime);
                }
            },
            scope: this
        }, {
            iconCls: 'ifresco-cancel-checkout-button',
            cls: 'ifresco-metadata-cancel-checkout',
            tooltip: Ifresco.helper.Translations.trans('Cancel Checkout'),
            hidden: true,
            disabled: true,
            handler: function() {
                var config = this.up('panel[cls~=ifresco-view-content-tab]').localConfigData;
                if (config.PanelNodeIsCheckedOut === true) {
                    this.fireEvent('cancelCheckout', this.nodeId, this);
                }
            },
            scope: this
        }, '-', {
            iconCls: 'ifresco-icon-refresh',
            cls: 'ifresco-metadata-refresh',
            disabled: true,
            tooltip: Ifresco.helper.Translations.trans('Refresh'),
            handler: function() {
                this.loadCurrentData(this.up('panel[cls~=ifresco-view-content-tab]').localConfigData.PanelNodeId);
            },
            scope: this
        }, '-', {
            iconCls: 'ifresco-copy-link-button',
            cls: 'ifresco-metadata-copy-link',
            disabled: true,
            tooltip: Ifresco.helper.Translations.trans('Copy Link'),
            handler: function() {
                var config = this.up('panel[cls~=ifresco-view-content-tab]').localConfigData;
                var nodeId = config.PanelNodeIsCheckedOut === true ? config.PanelNodeCheckedOutId : config.PanelNodeId;
                if (config.PanelNodeType == "{http://www.alfresco.org/model/content/1.0}folder") {
                    var ret = Routing.generate('ifresco_client_index') + '#folder/workspace://SpacesStore/' + nodeId;
                } else {
                    var ret = Routing.generate('ifresco_client_index') + '#document/workspace://SpacesStore/' + nodeId;
                }
                //TODO: need to find solution to replace jquery clipboard with extjs 4 solution
//                var clip = new ZeroClipboard.Client();
//                clip.setText(ret);
//                clip.glue(this.down('button[cls~=ifresco-metadata-copy-link]').getEl().dom);
            },
            scope: this
        }];
    },

    loadCurrentData: function(nodeId) {
    	this.ifrescoId = nodeId;
        this.setLoading(true);
        Ext.Ajax.request({
            loadMask: true,
            url: Routing.generate('ifresco_client_metadata_view'),
            params: {
                nodeId: nodeId,
                containerName: this.configData.addContainer
            },
            success: function(response) {
                var data = Ext.decode(response.responseText);
                this.loadMetaData(data, nodeId);
                this.setLoading(false);
            },
            scope: this
        });
    },

    loadMetaData: function(data, nodeId) {
    	console.log("LOAD META parent is",this.isParent)
        this.nodeId = nodeId;
        var lockToolTipBar = this.down('toolbar[cls~=ifresco-metadata-lock-tooltip]');
        var lockToolTipText = lockToolTipBar.down('tbtext');
        var lockToolTipBtn = lockToolTipBar.down('button');

        if (data.isWorkingCopy) {
            lockToolTipBar.show();
            lockToolTipText.setText(
                Ifresco.helper.Translations.trans('This document is locked by ') 
                    + data.checkedOutBy
            );
            lockToolTipBtn.setText(Ifresco.helper.Translations.trans('Get Original Document'));
            lockToolTipBtn.nodeId = data.checkoutRefNodeId;
            console.log("Get Original Document ",data.checkoutRefNodeId, data)
        } else if (data.isCheckedOut) {
            lockToolTipBar.show();
            lockToolTipText.setText(
                Ifresco.helper.Translations.trans('This document is locked for the offline editing by ') 
                    + data.checkedOutBy
            );
            lockToolTipBtn.setText(Ifresco.helper.Translations.trans('Get Working Copy'));
            lockToolTipBtn.nodeId = data.checkoutRefNodeId;
            console.log("Locked offline ",data.checkoutRefNodeId, data)
        } else {
            lockToolTipBar.hide();
        }

        var dataPanel = this.down('panel[cls~=ifresco-metadata-data-panel]');
        dataPanel.removeAll();
        var columns = [];

        if (Ext.isArray(data.Column1) && data.Column1.length) {
            Ext.each(data.Column1, function (property) {
                if (data.MetaData.data[property.name]) {
                    property.value = data.MetaData.data[property.name];
                }
            });
            columns.push(this.createColumn(data.Column1));
        }
        if (Ext.isArray(data.Column2) && data.Column2.length) {
            Ext.each(data.Column2, function (property) {
                if (data.MetaData.data[property.name]) {
                    property.value = data.MetaData.data[property.name];
                }
            });
            columns.push(this.createColumn(data.Column2));
        }
        if (Ext.isArray(data.Tabs) && data.Tabs.length) {
            Ext.each(data.Tabs, function (tab) {
                Ext.each(tab.fields, function (property) {
                    if (data.MetaData.data[property.name]) {
                        property.value = data.MetaData.data[property.name];
                    }
                });
            });
            columns.push(this.createTabPanel(data.Tabs));
        }
        dataPanel.add(columns);
        this.down('textfield[cls~=ifresco-metadata-nodeurl]').setValue(data.nodeURL);

        var pathContainer = this.down('container[cls~=ifresco-metadata-path]');
        pathContainer.removeAll();
        
        Ext.each(data.path, function (breadcrumb, index) {
        	console.log("ADD BREADCRUMB",breadcrumb,index);
            pathContainer.add({
                text: Ifresco.helper.Translations.trans(breadcrumb.title),
                cls: (index == 0 ? 'x-btn-text' : 'x-btn-text-icon'),
                iconCls: (index == 0 ? '' : 'ifresco-icon-arrow'),
                handler: function () {
                    this.up('ifrescoViewport').fireEvent('openDocument', breadcrumb.nodeId, breadcrumb.title);
                }
            });
        });
    },

    createColumn: function(fields) {
        return {
            xtype: 'grid',
            margin: '5 5 5 0',
            border: 1,
            disableSelection: true,
            minWidth: 200,
            maxWidth: 410,
            flex: 1,
            columns: [{
                text: 'label',
                dataIndex: 'fieldLabel',
                tdCls: 'ifresco-metadata-grid-key',
                flex: 1
            }, {
                text: 'label',
                dataIndex: 'value',
                tdCls: 'ifresco-metadata-grid-value',
                flex: 1
            }],
            cls: 'ifresco-metadata-grid',
            hideHeaders: true,
            viewConfig: {
                getRowClass: function(record, index, rowParams, store) {
                	console.log("GET ROW CLASS",record);
                    return (typeof record.raw.empty != 'undefined' && record.raw.empty === true ? 'ifresco-metadata-empty-row' : '');
                }
            },
            store: {
                fields: ['fieldLabel', 'name', 'value'],
                data: fields,
                proxy: {
                    type: 'memory'
                }
            }
        };
    },

    createTabPanel: function(tabs) {
        var tabPanel = {
            xtype: 'panel',
            minWidth: 210,
            cls: 'ifresco-metadata-tab-panel',
            border: 0,
            maxWidth: 410,
            flex: 1,
            layout: {
                type: 'accordion'
            },
            items: []
        };
        Ext.each(tabs, function(tab) {
            tabPanel.items.push(this.createTab(tab));
        }, this);
        return {
            xtype: 'container',
            flex: 1,
            minWidth: 210,
            layout: {
                type: 'hbox',
                align: 'stretch',
                pack: 'end'
            },
            items: [tabPanel]
        };
    },

    createTab: function(tab) {
        return {
            xtype: 'grid',
            anchor: '100%',
            title: tab.title,
            fitHeight: true,
            autoScroll: true,
            disableSelection: true,
            columns: [{
                text: 'label',
                dataIndex: 'fieldLabel',
                tdCls: 'ifresco-metadata-grid-key',
                flex: 1
            }, {
                text: 'label',
                dataIndex: 'value',
                tdCls: 'ifresco-metadata-grid-value',
                flex: 1
            }],
            cls: 'ifresco-metadata-grid',
            hideHeaders: true,
            store: {
                fields: ['fieldLabel', 'name', 'value'],
                data: tab.fields,
                proxy: {
                    type: 'memory'
                }
            }
        };
    }
});