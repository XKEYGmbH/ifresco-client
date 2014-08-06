Ext.define('Ifresco.view.panel.Preview', {
    extend: 'Ext.tab.Panel',
    alias: 'widget.ifrescoViewPanelPreview',
    cls: 'ifresco-view-preview-panel',
    border: 0,
    configData: null,
    tabPosition: 'top',
    disablePreview: false,
    isSingleDocument: false,
    style: {
        borderLeft: '1px solid #99BCE8'
    },

    initComponent: function () {
    	Ext.apply(this, {
    		items: this.getCurrentItems()
    	});
        this.callParent();
    },

    getCurrentItems: function () {
        var items = []
    
        if (!this.disablePreview || Ifresco.helper.Settings.get('DisableTab').indexOf("preview") < 0) {
	    	items.push({
	            xtype: 'ifrescoViewPreviewTab',
	            configData: this.configData
	        });
        }
        
        if (Ifresco.helper.Settings.get('DisableTab').indexOf("versions") < 0) {
	        items.push({
	            xtype: 'ifrescoViewVersionsTab',
	            configData: this.configData
	        });
        }

        if (Ifresco.helper.Settings.get('ParentNodeMeta') == 'true') {
            items.push({
                xtype: 'ifrescoViewParentMetadataTab',
                configData: this.configData
            });
        }

        if (Ifresco.helper.Settings.get('DisableTab').indexOf("metadata") < 0) {
	        items.push({
	            xtype: 'ifrescoViewMetadataTab',
	            configData: this.configData
	        });
        }
        
        if (Ifresco.helper.Settings.get('DisableTab').indexOf("comments") < 0) {
	        items.push({
	            xtype: 'ifrescoViewCommentsTab',
	            configData: this.configData
	        });
        }

        return items;
    },

    listeners: {
        tabchange: function (tabPanel, newCard) {
            tabPanel.reloadTabData(newCard);
        },
        beforerender: function () {
            var defaultTab = this.configData.DefaultTab;
            
            
            var tabCls = "";
            switch (defaultTab) {
            	case "ifrescoViewPreviewTab":
            		tabCls = 'ifresco-preview-tab';
            	break;
            	case "ifrescoViewMetadataTab":
            		tabCls = 'ifresco-metadata-tab';
                break;
            	case "ifrescoViewParentMetadataTab":
            		tabCls = 'ifresco-parent-metadata-tab';
                break;
            	case "ifrescoViewCommentsTab":
            		tabCls = 'ifresco-comments-tab';
            	break;
            	case "ifrescoViewVersionsTab":
            		tabCls = 'ifresco-versions-tab';
                break;
                default:
                	break;
            }
            
            console.log("defaulttab is ",defaultTab,tabCls);
            
            if (defaultTab == "ifrescoViewPreviewTab" && this.disablePreview) {
            	this.setActiveTab(this.down("panel[cls~=ifresco-metadata-tab]"));
            }
            else if (defaultTab) {
                this.setActiveTab(this.down("panel[cls~="+tabCls+"]"));
            }
            /*if (defaultTab == "ifrescoViewPreviewTab" && this.disablePreview) {
            	this.setActiveTab(this.down("ifrescoViewMetadataTab"));
            }
            else if (defaultTab) {
                this.setActiveTab(this.down(this.configData.DefaultTab));
            }*/
        }
    },

    reloadTabData: function (tab) {
        if (! tab) {
            tab = this.getActiveTab();
        }
        var tabXType = tab.getXType();
        var nodeId, isFolder;
        if (this.isSingleDocument) {
        	var documentTab = this.up('panel[cls~=ifresco-view-document-tab]');
        	nodeId = documentTab.nodeId;
        	isFolder = false;
        }
        else {
        	var localConfigData = this.up('panel[cls~=ifresco-view-content-tab]').localConfigData;
        	nodeId = localConfigData.selectedNodeId;
        	isFolder = localConfigData.PanelNodeType === '{http://www.alfresco.org/model/content/1.0}folder';
        	
        	if (Ifresco.helper.Settings.get('MetaOnTreeFolder') == 'true' && !nodeId) {
                nodeId = localConfigData.nodeId;
            }
        }

        if (!nodeId) {
            return;
        }

        // if (! nodeId) {
        //     if (Ifresco.helper.Settings.get('MetaOnTreeFolder') == 'true') {
        //         nodeId = localConfigData.nodeId;
        //     } else {
        //         return;
        //     }
        // }

        console.log("ACTIVE TAB",tab,tabXType,isFolder);
        if (tabXType == "ifrescoViewParentMetadataTab" && isFolder && Ifresco.helper.Settings.get('ParentMetaDocumentOnly') == "true") {
        	this.setActiveTab(this.down("panel[cls~=ifresco-metadata-tab]"));
        } else if (tabXType == "ifrescoViewVersionsTab" && isFolder) {
        	this.setActiveTab(this.down("panel[cls~=ifresco-preview-tab]"));
        } else if (tabXType === 'ifrescoViewPreviewTab') {
            tab.loadCurrentData(nodeId, isFolder); 
        } else {
            tab.loadCurrentData(nodeId);  
        }
    }


});