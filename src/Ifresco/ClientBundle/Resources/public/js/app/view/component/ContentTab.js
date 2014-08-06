Ext.define('Ifresco.view.ContentTab', {
    extend: 'Ext.panel.Panel',
    closable: true,
    autoDestroy:false,
    border: 0,
    alias: 'widget.ifrescoContentTab',
    cls: 'ifresco-view-content-tab',
    //This data comes from server ( all necessary settings for views)
    configData: null,
    //this data stores local settings and changes for local components, grids and so on
    localConfigData: {
        lastParams: null,
        currentColumnSetId: 0,
        mainGrid: null,
        columnStore: null,
        mainThumbView: null,
        versionList: null,
        versionStore: null,
        currentNodeId: null,
        zipArchiveExists: true,
        orgDetailUrl: '',
        detailUrl: '',
        PanelNodeId: null,
        PanelNodeMimeType: null,
        PanelNodeText: null,
        PanelNodeType: null,
        PanelNodeUrl: null,
        PanelNodeIsCheckedOut: null,
        PanelNodeCheckedOutId: null,
        ShareSpaceUrl: null,
        ShareUrl: null,
        ShareSpaceDetail: null,
        PanelNodeOrgId: null,
        SelectedVersion: null,
        FolderPath: null,
        isDblClick: false,
        ZohoMimeDocs: ["application/msword","application/vnd.openxmlformats-officedocument.wordprocessingml.document","application/rtf","text/rtf","text/html","application/vnd.oasis.opendocument.text","application/vnd.sun.xml.writer","text/plain"],
        ZohoMimeSheet: ["application/vnd.ms-excel","application/vnd.openxmlformats-officedocument.spreadsheetml.sheet","application/vnd.oasis.opendocument.spreadsheet","application/vnd.sun.xml.calc","application/vnd.openxmlformats-officedocument.spreadsheetml.sheet","text/csv","text/comma-separated-values","text/tab-separated-values"]
    },
    initComponent: function () {
        this.trimTitle();

        this.localConfigData.zipArchiveExists = this.configData.zipArchiveExists;
        this.localConfigData.orgDetailUrl = this.configData.DetailUrl;
        this.localConfigData.detailUrl = this.configData.DetailUrl;
        this.localConfigData.ShareUrl = this.configData.ShareUrl;
        this.localConfigData.ShareSpaceUrl = this.configData.ShareSpaceUrl;
        this.localConfigData.ShareSpaceDetail = this.configData.ShareSpaceDetail;
        this.localConfigData.FolderPath = this.configData.folder_path;
        
        Ext.apply(this, {
            layout: 'fit',
            autoScroll: false,
            items: [{
                xtype: 'container', 
                hidden: true,
                padding: 0,
                margin: 0,
                cls: 'ifresco-view-content-tab-split-horizontal',
                layout: {
                    type: 'vbox',
                    align: 'stretch'
                },
                items: {
                    xtype: 'splitter',
                    border: 1,
                    style:{
                        backgroundColor: '#DFE8F6'
                    }
                }
            },{
                xtype: 'container',
                hidden: true,
                padding: 0,
                margin: 0,
                cls: 'ifresco-view-content-tab-split-vertical',
                layout: {
                    type: 'hbox',
                    align: 'stretch'
                },
                items: {
                    xtype: 'splitter',
                    style:{
                        backgroundColor: '#DFE8F6'
                    }
                }
            }]
        });

        var container;
        if (Ifresco.helper.Registry.get('ArrangeList') === 'horizontal') {
            container = this.items[0];
        } else {
            container = this.items[1];
        }

        Ext.apply(container, {
            hidden: false,
            items: [{
                xtype: 'ifrescoViewPanelGrid',
                configData: this.configData,
                localConfigData: this.localConfigData,
                flex: 1
            },{
                xtype: 'splitter',
                style:{
                    backgroundColor: '#DFE8F6'
                }
            },{
                xtype: 'ifrescoViewPanelPreview',
                configData: this.configData,
                tabPosition: 'bottom',
                flex: 1
            }]
        });

        this.callParent();

        // var gridStore = this.down('ifrescoViewGridGrid').getStore();

        // gridStore.on('datachanged', this.onGridStoreDataChanged, this);
        // gridStore.on('beforeload', this.onGridStoreBeforeLoad, this);
    },

    listeners: {
        beforeclose: function(tab) {
            tab.destroy();
            return false;
        },
        activate: function (tab) {
            tab.changeLayout();
        }
    },

    getCurrentLayout: function () {
        if (this.down('container[cls~=ifresco-view-content-tab-split-horizontal]').isVisible()) {
            return 'horizontal';
        }
        return 'vertical';
    },

    changeLayout: function () {
        var newContainer;
        var oldContainer;
        var layout = Ifresco.helper.Registry.get('ArrangeList');
        if (layout === this.getCurrentLayout()) {
            return;
        }

        var panelGrid = this.down('ifrescoViewPanelGrid');
        var panelPreview = this.down('ifrescoViewPanelPreview');

        if (layout === 'horizontal') {
            newContainer = this.down('container[cls~=ifresco-view-content-tab-split-horizontal]');
            oldContainer = this.down('container[cls~=ifresco-view-content-tab-split-vertical]');
        } else {
            newContainer = this.down('container[cls~=ifresco-view-content-tab-split-vertical]');
            oldContainer = this.down('container[cls~=ifresco-view-content-tab-split-horizontal]');
        }

        oldContainer.remove(panelGrid, false);
        oldContainer.remove(panelPreview, false);

        newContainer.insert(0, panelGrid);
        newContainer.insert(2, panelPreview);

        newContainer.show();
        oldContainer.hide();
    },

    trimTitle: function () {
    	this.title = Ext.util.Format.trim(Ext.util.Format.stripTags(this.title));
    	console.log("trim title",this.title);
        var tabLength = Ifresco.helper.Settings.get('TabTitleLength');
        var titleLength = parseInt(tabLength) || 0;
        if(titleLength > 0 && this.title.length > titleLength + 3) {
            this.title = this.title.substring(0, titleLength) + '...';
        }
    },

    reloadGridData: function (reqData, remove) {
    	if (typeof remove == 'undefined')
    		remove = false;
    	
    	
    	
        var grid = this.down('ifrescoViewGridGrid');
        this.localConfigData.currentColumnsetid = reqData.params.columnsetid;
        this.localConfigData.lastParams = reqData;
        this.localConfigData.nodeId = reqData.params.nodeId || null;
        grid.localConfigData = this.localConfigData;

//        if(mainThumbView.isVisible())
//            loadThumbnailView();
//        else {

            grid.getStore().getProxy().extraParams = {};
            var paramsToStore = [
                'subCategories', 'categories',
                'categoryNodeId', 'nodeId',
                'columnsetid', 'advancedSearchFields',
                'advancedSearchOptions', 'containerName',
                'clipboard', 'clipboarditems',
                'searchTerm', 'clickSearch', 'clickSearchValue'
            ];

            for(var i = 0; i < paramsToStore.length; i++) {
                if(reqData.params[paramsToStore[i]]) {
                    grid.getStore().getProxy().setExtraParam(paramsToStore[i], reqData.params[paramsToStore[i]]);
                }
            }

            if (remove) {
        		grid.getStore().removeAll();
        		grid.getStore().currentPage = 1;
        	}
            
            grid.store.load(reqData);
//        }
    },

    onGridStoreBeforeLoad: function (store) {
        var grid = this.down('ifrescoViewGridGrid');
        var panelPreview = this.down('ifrescoViewPanelPreview');
        panelPreview.disable();
    }

    // onGridStoreDataChanged: function (store) {
    //     var grid = this.down('ifrescoViewGridGrid');
    //     var gridData = store.getProxy().getReader().rawData || {};
    //     var breadcrumb = gridData.breadcrumb || [];
    //     var folderPath = gridData.folder_path;
    //     var perms = gridData.perms;
    //     var isSearch = gridData.isSearch ? true : false;
    //     var isClipBoard = grid.configData.isClipBoard; 

    //     grid.isSearchRequest = isSearch;
    //     grid.perms = gridData.perms;

    //     //TODO: user access
    //     // if(perms) {
    //     //     grid.down('button[iconCls=ifresco-icon-upload]').setDisabled(!perms.alfresco_perm_create);
    //     //     grid.down('button[iconCls=ifresco-create-folder-button]').setDisabled(!perms.alfresco_perm_create);
    //     //     grid.down('button[iconCls=ifresco-create-html-button]').setDisabled(!perms.alfresco_perm_create);
    //     //     grid.down('button[iconCls=ifresco-copy-clipboard-button]')
    //     //         .setDisabled(!perms.alfresco_perm_create || !ClipBoard.items.length);
    //     //     grid.down('button[iconCls=ifresco-cut-clipboard-button]')
    //     //         .setDisabled(!perms.alfresco_perm_create || !ClipBoard.items.length);
    //     //     grid.down('button[iconCls=ifresco-link-clipboard-button]')
    //     //         .setDisabled(!perms.alfresco_perm_create || !ClipBoard.items.length);

    //     //     this.permEdit = perms.alfresco_perm_edit;
    //     // }

    //     // detailUrl = config.configData.ShareFolder + (folderPath ? folderPath : '/');

    //     var topToolbar = grid.down('toolbar[cls~=ifresco-view-grid-toolbar-buttons]');
    //     var skipNames = [];
    //     if(isSearch) {
    //         skipNames = grid.buttonsConfig.search;
    //         topToolbar.items.each(function (button) {
    //             if (Ext.Array.contains(skipNames, button.iconCls)) {
    //                 return;
    //             }
    //             button.setVisible(false);
    //         });
    //     } else if (isClipBoard) {
    //         console.log('cll');
    //         skipNames = grid.buttonsConfig.clipboard;
    //         topToolbar.items.each(function (button) {
    //             if (Ext.Array.contains(skipNames, button.iconCls)) {
    //                 return;
    //             }
    //             button.setVisible(false);
    //         });
    //     } else {
    //         topToolbar.items.each(function (button) {
    //             button.setVisible(true);
    //         });
    //     }

    //     var breadcrumbToolbar = grid.down('toolbar[cls~=ifresco-view-grid-toolbar-breadcrumb]');
    //     if (breadcrumbToolbar) {
    //         breadcrumbToolbar.removeAll();
    //         if (breadcrumb.length > 0) {
    //             breadcrumbToolbar.show();
    //             Ext.each(breadcrumb, function (crumb, index) {
    //                 breadcrumbToolbar.add({
    //                     text: Ifresco.helper.Translations.trans(crumb.text),
    //                     cls: (index == 0 ? 'x-btn-text' : 'x-btn-text-icon'),
    //                     iconCls: (index == 0 ? '' : 'ifresco-icon-arrow'),
    //                     handler: function () {
    //                         this.up('ifrescoCenter').fireEvent('openDocument', crumb.id, crumb.text);
    //                     }
    //                 });
    //             });
    //         } else {
    //             breadcrumbToolbar.hide();
    //         }
    //     }
    // }
});