Ext.define('Ifresco.controller.Index', {
    extend: 'Ext.app.Controller',
    refs: [{
        selector: 'viewport',
        ref: 'viewport'
    },{
        selector: 'viewport > ifrescoWest',
        ref: 'westPanel'
    },{
        selector: 'viewport > ifrescoCenter',
        ref: 'tabPanel'
    },{
        selector: 'viewport > ifrescoWest > #ifrescoTreeFolder',
        ref: 'treeFolder'
    },{
        selector: 'viewport > ifrescoCenter > #ifrescoAdminTab',
        ref: 'adminTab'
    },{
        selector: 'viewport > ifrescoCenter > #ifrescoAdvancedSearchTab',
        ref: 'advancedSearchTab'
    },{
        selector: 'ifrescoCenter > ifrescoContentTab[ifrescoId=documents]',
        ref: 'documentsTab'
    },{
        selector: 'viewport > ifrescoCenter > #ifrescoTrashCanTab',
        ref: 'trashcanTab'
    }],
    
    init: function() {
        this.control({
            'ifrescoViewport': {
            	openClickSearch: this.openClickSearch,
                openDocument: this.openDocument,
                openDocumentDetail: this.openDocumentDetail,
                openCategory: this.openCategory,
                openTag: this.openTag,
                openSite: this.openSite,
                openWindow: this.openWindow,
                openDashboard: this.openDashboard,
                moveTo: this.moveTo,
                loadDefaults: this.loadDefaults,
                setup: this.setup
            },
            'ifrescoNorth': {
            	loadTrashcan: this.loadTrashcan,
                loadAdminTab: this.loadAdminTab,
                changeContentLayout: this.changeContentLayout,
                logout: this.logout
            },
            'ifrescoFormSearch': {
                loadAdvancedSearchTab: this.loadAdvancedSearchTab
            },
            'ifrescoMenuCategory': {
            	openCategory: this.openCategory
            },
            'ifrescoMenu': {
                openDocument: this.openDocument,
                openDocumentDetail: this.openDocumentDetail,
                deleteNode: this.deleteNode,
                deleteNodes: this.deleteNodes,
                transformFiles: this.transformFiles
            },  
            'ifrescoViewTrashCan': {
            	openDocument: this.openDocument,
                openDocumentDetail: this.openDocumentDetail
            }
        });
    },
    runner: null,
    setup: function() {
    	var self = this;
    	console.log("APPLICATION SETUP!");
    	this.runner = new Ext.util.TaskRunner();
    	var checkAuthTask = {
    	    run: function(){
    	    	var window = Ext.create('Ext.window.Window', {
            		width: 530,
            		height: 250,
            		closable: false,
            		closeable: false,
            		draggable: false,
            		modal:true,
        	        closeAction:'destroy',
        	        title:Ifresco.helper.Translations.trans('Login'),
        	        resizable : false,
        	        loader: {
        	            url: Routing.generate('ifresco_client_login'),
        	            autoLoad: true
        	        }
            	});
    	    	
    	    	console.log("CHECK AUTH RUNNING");

    	    	Ext.Ajax.request({
                    url: Routing.generate('ifresco_client_check_auth'),
                    params: {},
                    success: function (req) {
                    	try {
	                    	var data = Ext.decode(req.responseText);
		                    console.log("RESULT CHECKAUTH",data);
		                    if(data.success === false) {
		                    	window.show();
		                    	self.runner.stop(checkAuthTask);
		                    }
                    	}
                    	catch (e) {
                    		window.show();
	                    	self.runner.stop(checkAuthTask);
                    	}
                    },
                    failure: function (data) {
                    	window.show();
                    	self.runner.stop(checkAuthTask);
                    }
                });
	        	
    	    },
    	    interval: 1000*60*5
    	};

    	this.runner.start(checkAuthTask);
    },
    
    loadDefaults: function() {
    	var west = this.getWestPanel();
    	if (Ifresco.helper.Settings.get("DefaultNav").length > 0) {
    		west.down("panel[navEl="+Ifresco.helper.Settings.get("DefaultNav")+"]").expand();
        }
		else
			west.down("panel[navEl=folders]").expand();
    },
    
    openClickSearch: function(propName,propLabel,propValue) {
    	var tabPanel = this.getTabPanel();
    	
        var tabnodename = propName.replace(/[-.\:, ]/g,"");
        var tabprovval = propValue.replace(/[-.\:\/\"\'\\, ]/g,"");
        var tabname = tabnodename+tabprovval;

        var title = propLabel+': '+propValue,
            ifrescoId = tabname,
            contentTab = tabPanel.down('ifrescoContentTab[ifrescoId=' + ifrescoId + ']');


        if (!contentTab) {
            Ext.Ajax.request({
                url: Routing.generate('ifresco_client_data_grid_index'),
                params: {
                    columnSetId: Ifresco.helper.Registry.get('ColumnsetId')
                },
                success: function (req) {
                    contentTab = Ifresco.view.ContentTab.create({
                        ifrescoId: ifrescoId,
                        title: title,
                        configData: Ext.decode(req.responseText)
                    });
                    tabPanel.add(contentTab);
                    tabPanel.setActiveTab(contentTab);
                    contentTab.reloadGridData({params: {
                    	clickSearch: propName,
                    	clickSearchValue: propValue,
                        columnSetId: Ifresco.helper.Registry.get('ColumnsetId')
                    }});
                },
                failure: function (data) {
                    console.log(data.responseText);
                }
            });
        } else {
            this.getTabPanel().setActiveTab(contentTab);
            contentTab.reloadGridData({params: {
            	clickSearch: propName,
            	clickSearchValue: propValue,
                columnSetId: Ifresco.helper.Registry.get('ColumnsetId')
            }});
        }
    },
    
    moveTo: function(records, moveToRecord) {

    	var nodeInfo = "", items = [], recordById = [];
    	Ext.iterate(records, function(record) {
    		var nodeRef = 'workspace://SpacesStore/'+record.get('id');
    		nodeInfo += '<b>'+record.get('cm_name')+'</b><br>';
    		items.push(nodeRef);
    		recordById[nodeRef] = record;
        });
    	
    	Ext.MessageBox.show({
            title: Ifresco.helper.Translations.trans('Move files?'),
            msg: Ifresco.helper.Translations.trans('Do you really want to move those files:') + '<br>' + nodeInfo,
            fn: function(btn) {
                if (btn === "yes") {
                    Ext.Ajax.request({
                        method: 'POST',
                        url: Routing.generate('ifresco_client_node_actions_move_to'),
                        params: {
                            items: Ext.encode(items),
                            destination: moveToRecord.get('id')
                        },
                        success: function (req) {
                        	var data = Ext.decode(req.responseText);
                        	if (data.success) {
                        		Ext.iterate(data.results, function(result) {
                        			if (result.success) {
                        				var record = recordById[result.nodeRef];
                        				record.store.remove(record);
                        			}
                        		});
                        	}
                        },
                        failure: function (data) {
                            console.log("FAIL",data);
                        }
                    });
                }
            },
            buttons: Ext.MessageBox.YESNO,
            icon: Ext.MessageBox.QUESTION
        });
    },
    
    openDashboard: function() {
    	var tabPanel = this.getTabPanel();
    	
    	var dashboardTab = tabPanel.down('ifrescoViewDashboard');
    	
    	if (!dashboardTab) {
	    	dashboardTab = Ifresco.view.Dashboard.create({
	
	        });
	        tabPanel.add(dashboardTab);
    	}
    	
        tabPanel.setActiveTab(dashboardTab);
    },
    
    openWindow: function(url,target) {
    	if (typeof target == "undefined" || target == null)
    		target = "_blank";
	    /*var form = document.createElement("form");
	    form.setAttribute("action",url);
	    form.setAttribute("method","GET");
	    form.setAttribute("target","_blank");
	    document.body.appendChild(form);
	    form.submit();
	    document.body.removeChild(form);*/
    	window.open(url, target);
    },
    
    openDocumentDetail: function (nodeId, title) {
    	console.log("openDocument",nodeId,title)
        var tabPanel = this.getTabPanel();
        var ifrescoId = nodeId;
        var documentTab = tabPanel.down('ifrescoDocumentTab[ifrescoId=' + nodeId + ']');

        if (!documentTab) {
        	Ext.Ajax.request({
                url: Routing.generate('ifresco_client_data_grid_index'),
                params: {
                    columnSetId: Ifresco.helper.Registry.get('ColumnsetId'),
                    nodeId: nodeId
                },
                success: function (req) {
                	var configData = Ext.decode(req.responseText);
                	if (title == null) {

                		title = configData.name;
                	}
                	documentTab = Ifresco.view.DocumentTab.create({
                        ifrescoId: ifrescoId,
                        title: title,
                        configData: configData,
                        nodeId: nodeId
                    });
                    tabPanel.add(documentTab);
                    tabPanel.setActiveTab(documentTab);
                },
                failure: function (data) {
                    console.log(data.responseText);
                }
            });
        } else {
            this.getTabPanel().setActiveTab(documentTab);
            /*contentTab.reloadGridData({params: {
                nodeId: nodeId,
                columnSetId: Ifresco.helper.Registry.get('ColumnsetId')
            }});*/
        }
    },

    openDocument: function (nodeId, title) {
        var tabPanel = this.getTabPanel();
        var ifrescoId;
        var contentTab;
        if (title) {
            ifrescoId = nodeId;
            contentTab = tabPanel.down('ifrescoContentTab[ifrescoId=' + nodeId + ']');
        } else {
            ifrescoId = 'documents';
            title = Ifresco.helper.Translations.trans('Documents');
            contentTab = this.getDocumentsTab();
        }

        if (!contentTab) {
            Ext.Ajax.request({
                url: Routing.generate('ifresco_client_data_grid_index'),
                params: {
                	nodeId: nodeId,
                    columnSetId: Ifresco.helper.Registry.get('ColumnsetId')
                },
                success: function (req) {
                	var configData = Ext.decode(req.responseText);
                	if (title == true || title == null) {
                		title = configData.name;
                	}
                	
                    contentTab = Ifresco.view.ContentTab.create({
                        ifrescoId: ifrescoId,
                        title: title,
                        configData: configData
                    });
                    tabPanel.add(contentTab);
                    tabPanel.setActiveTab(contentTab);
                    contentTab.reloadGridData({params: {
                        nodeId: nodeId,
                        columnSetId: Ifresco.helper.Registry.get('ColumnsetId')
                    }});
                },
                failure: function (data) {
                    console.log(data.responseText);
                }
            });
        } else {
            this.getTabPanel().setActiveTab(contentTab);
            contentTab.reloadGridData({params: {
                nodeId: nodeId,
                columnSetId: Ifresco.helper.Registry.get('ColumnsetId')
            }});
        }
    },

    openCategory: function (nodeId, categoryId, title) {
        var tabPanel = this.getTabPanel();
        var ifrescoId;
        var contentTab;
        
        if (title) {
            ifrescoId = nodeId;
            contentTab = tabPanel.down('ifrescoContentTab[ifrescoId=' + nodeId + ']');
        } else {
            ifrescoId = 'documents';
            title = Ifresco.helper.Translations.trans('Documents');
            contentTab = this.getDocumentsTab();
        }

        if (!contentTab) {
            Ext.Ajax.request({
                url: Routing.generate('ifresco_client_data_grid_index'),
                params: {
                    columnSetId: Ifresco.helper.Registry.get('ColumnsetId')
                },
                success: function (req) {
                    var contentTab = Ifresco.view.ContentTab.create({
                    	ifrescoId: ifrescoId,
                        title: title,
                        configData: Ext.decode(req.responseText)
                    });
                    tabPanel.add(contentTab);
                    tabPanel.setActiveTab(contentTab);
                    contentTab.reloadGridData({params: {
                        fromTree: true,
                        categoryNodeId: nodeId,
                        subCategories: Ifresco.helper.Registry.get('BrowseSubCategories'),
                        categories: categoryId,
                        columnSetId: Ifresco.helper.Registry.get('ColumnsetId')
                    }});
                },
                failure: function (data) {
                    console.log(data.responseText);
                }
            });
        } else {
            this.getTabPanel().setActiveTab(contentTab);
            contentTab.reloadGridData({params: {
            	fromTree: true,
                categoryNodeId: nodeId,
                subCategories: Ifresco.helper.Registry.get('BrowseSubCategories'),
                categories: categoryId,
                columnSetId: Ifresco.helper.Registry.get('ColumnsetId')
            }});
        }
    },

    openTag: function (tag) {
    	console.log("OPENTAG",tag);
        if (!this.getDocumentsTab()) {
            var tabPanel = this.getTabPanel();
            var ifrescoId;
            var contentTab;
            
            ifrescoId = 'documents';
            title = Ifresco.helper.Translations.trans('Documents');
            contentTab = this.getDocumentsTab();
            
            Ext.Ajax.request({
                url: Routing.generate('ifresco_client_data_grid_index'),
                params: {
                    columnSetId: Ifresco.helper.Registry.get('ColumnsetId')
                },
                success: function (req) {
                    var contentTab = Ifresco.view.ContentTab.create({
                    	ifrescoId: ifrescoId,
                        title: title,
                        configData: Ext.decode(req.responseText)
                    });
                    tabPanel.add(contentTab);
                    tabPanel.setActiveTab(contentTab);
                    contentTab.reloadGridData({params: {
                        tag: tag,
                        columnSetId: Ifresco.helper.Registry.get('ColumnsetId')
                    }});
                },
                failure: function (data) {
                    console.log(data.responseText);
                }
            });
        } else {
            this.getTabPanel().setActiveTab(this.getDocumentsTab());
            this.getDocumentsTab().reloadGridData({params: {
            	tag: tag,
                columnSetId: Ifresco.helper.Registry.get('ColumnsetId')
            }});
        }
    },
    
    openSite: function (shortName,title,nodeId,docLib) {
		var tag = "test";
		console.log("OPEN SITE",tag);
		
		var tabPanel = this.getTabPanel();
		var ifrescoId = nodeId;
		var siteTab = tabPanel.down('ifrescoSiteTab[ifrescoId=' + nodeId + ']');
		
		/*frescoId = 'documents';
		title = Ifresco.helper.Translations.trans('Documents');
		contentTab = this.getDocumentsTab();*/
		

		title = Ifresco.helper.Translations.trans('Site')+": "+title;
		    

       if (!siteTab) {
            Ext.Ajax.request({
                url: Routing.generate('ifresco_client_data_grid_index'),
                params: {
                	nodeId: docLib,
                	siteName: shortName,
                    columnSetId: Ifresco.helper.Registry.get('ColumnsetId')
                },
                success: function (req) {
                	siteTab = Ifresco.view.SiteTab.create({
                    	ifrescoId: ifrescoId,
                        title: title,
                        nodeId: nodeId,
                        shortName: shortName,
                        configData: Ext.decode(req.responseText),
                        docLib: docLib
                    });

                    tabPanel.add(siteTab);
                    tabPanel.setActiveTab(siteTab);

                    siteTab.getContentTab().reloadGridData({params: {
                    	nodeId: docLib,
                        columnSetId: Ifresco.helper.Registry.get('ColumnsetId')
                    }});
                },
                failure: function (data) {
                    console.log(data.responseText);
                }
            });
        } else {
        	this.getTabPanel().setActiveTab(siteTab);
        	siteTab.getContentTab().reloadGridData({params: {
        		nodeId: docLib,
                columnSetId: Ifresco.helper.Registry.get('ColumnsetId')
            }});
        }
    },

    loadTrashcan: function() {
    	var tabPanel = this.getTabPanel();
    	console.log("INDEX CONTROLLER LOAD TRASH")
    	if (this.getTrashcanTab()) {
            this.getTabPanel().setActiveTab(this.getTrashcanTab());
        } else {
        	Ext.Ajax.request({
                url: Routing.generate('ifresco_client_data_grid_index'),
                params: {
                    columnSetId: Ifresco.helper.Registry.get('ColumnsetId')
                },
                success: function (req) {
                    contentTab = Ifresco.view.TrashCanTab.create({
                        configData: Ext.decode(req.responseText)
                    });
                    tabPanel.add(contentTab);
                    tabPanel.setActiveTab(contentTab);
                },
                failure: function (data) {
                    console.log(data.responseText);
                }
            });
        }
    },
    
    loadAdminTab: function () {
        if (Ifresco.helper.Settings.isAdmin) {
            if (this.getAdminTab()) {
                this.getTabPanel().setActiveTab(this.getAdminTab());
            } else {
                var adminTab = Ifresco.view.AdminTab.create({});
                Ifresco.getApplication().getController("Plugin").injectAdminPlugins(adminTab);
                this.getTabPanel().add(adminTab).show();
            }
        }
    },
    
    loadAdvancedSearchTabFromSaved: function (savedRecord) {
        var advancedSearchTab = this.getAdvancedSearchTab();
        if (!advancedSearchTab) {
            advancedSearchTab = this.getTabPanel().add(Ifresco.view.AdvancedSearchTab.create({fromSaved: true, savedRecord: savedRecord}));
        }
        else {
        	Ifresco.getApplication().getController("Search").loadSearchForm(savedRecord.get('template'), savedRecord);
        }
        this.getTabPanel().setActiveTab(advancedSearchTab);
    },

    loadAdvancedSearchTab: function () {
        var advancedSearchTab = this.getAdvancedSearchTab();
        if (! advancedSearchTab) {
            advancedSearchTab = this.getTabPanel().add(Ifresco.view.AdvancedSearchTab.create({}));
        }
        this.getTabPanel().setActiveTab(advancedSearchTab);
    },

    changeContentLayout: function () {
        var tab = this.getTabPanel().getActiveTab();
        if (tab && tab.getXType() === 'ifrescoContentTab') {
            tab.changeLayout();
        }
    },

    logout: function () {
        window.location.href = Routing.generate('ifresco_client_logout');
    },

    deleteNode: function (node, nodeId, nodeName, nodeType, fromComponent) {
    	var parentStore = fromComponent.getStore();

        Ext.MessageBox.show({
            title: Ifresco.helper.Translations.trans('Delete?'),
            msg: Ifresco.helper.Translations.trans('Do you really want to delete:') + '<br><b>' + nodeName + '</b>',
            fn: function(btn) {
                if (btn === "yes") {
                    //TODO: implement
                    Ext.Ajax.request({
                        method: 'POST',
                        url: Routing.generate('ifresco_client_node_actions_delete_node'),
                        params: {
                            nodeId: nodeId,
                            nodeType: nodeType
                        },
                        success: function () {
                        	if (fromComponent.xtype == "ifrescoViewGridGrid") {
                        		parentStore.remove(node);
                        	}
                        	else {
	                            if(!parentStore.isLoading()) {
	                            	parentStore.load();
	                            }
                        	}
                        },
                        failure: function (data) {
                            var data = Ext.decode(data.responseText);
                            Ext.MessageBox.show({
                                title: Ifresco.helper.Translations.trans('Error'),
                                msg: data.message,
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.WARNING
                            })
                        }
                    });
                }
            },
            buttons: Ext.MessageBox.YESNO,
            icon: Ext.MessageBox.QUESTION
        });
    },
    
    deleteNodes: function (nodes, fromComponent) {
    	var parentStore = fromComponent.getStore();
    	var nodeNames = [];
    	var nodeArray = [];
    	for (var i=0; i < nodes.length; ++i) {
    		nodeNames.push(nodes[i].nodeName);
    		nodeArray.push({shortType: nodes[i].shortType, nodeRef: nodes[i].nodeId});
        }
    	
        Ext.MessageBox.show({
            title: Ifresco.helper.Translations.trans('Delete?'),
            msg: Ifresco.helper.Translations.trans('Do you really want to delete:') + '<br><b>' + nodeNames.join("<br>") + '</b>',
            fn: function(btn) {
                if (btn === "yes") {
                    //TODO: implement
                    Ext.Ajax.request({
                        method: 'POST',
                        url: Routing.generate('ifresco_client_node_actions_delete_nodes'),
                        params: {
                            nodes: Ext.encode(nodeArray)
                        },
                        success: function () {
                        	if (fromComponent.xtype == "ifrescoViewGridGrid") {
                        		console.log("remove records from store")
                        		for (var i=0; i < nodes.length; ++i) {
                        			console.log("remove record",nodes[i])
                        			parentStore.remove(nodes[i].record);
                        		}
                        	}
                        	else {
	                            if(!parentStore.isLoading()) {
	                            	parentStore.load();
	                            }
                        	}
                        },
                        failure: function (data) {
                            var data = Ext.decode(data.responseText);
                            Ext.MessageBox.show({
                                title: Ifresco.helper.Translations.trans('Error'),
                                msg: data.message,
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.WARNING
                            })
                        }
                    });
                }
            },
            buttons: Ext.MessageBox.YESNO,
            icon: Ext.MessageBox.QUESTION
        });
    },
    
    transformFiles: function(nodes, container, files, uploader, grid) {
    	console.log("start transform files",nodes,container,files,uploader)
	    container = container == undefined ? '' : container;
	    files = files || false;
	    uploader = uploader || false;

	    var transformNodes = Ext.create('Ext.window.Window', {
	        modal:true,
	        width: 900,
	        height: 450,
	        closeAction:'destroy',
	        constrain: true,
	        title:Ifresco.helper.Translations.trans('Transformation Settings'),
	        resizable: false,
	        autoShow: true,
	        filterThis: function (b1, b2) {
	            var itemselector = Ext.getCmp('itemselector-field');

	            itemselector.store.clearFilter();

	            if(!b1.pressed && !b2.pressed) {
	                itemselector.store.filterBy(
	                    function(record) {
	                        return false;
	                    }
	                )
	            } else if(b1.pressed && !b2.pressed) {
	                itemselector.store.filterBy(
	                    function(record) {
	                        return record.get('engine') != 'false'
	                    }
	                )
	            } else if(!b1.pressed && b2.pressed) {
	                itemselector.store.filterBy(
	                    function(record) {
	                        return record.get('engine') == 'false'
	                    }
	                )
	            }

	            itemselector.bindStore(itemselector.store);
	        },
	        listeners: {
	            show: function(t, eOpts) {
	            	//$("#"+t.id).mask(Ifresco.helper.Translations.trans('Loading...'),300); // TODO MASK
	            	Ext.Ajax.request({
                        method: 'POST',
                        url: Routing.generate('ifresco_client_node_actions_list_transformers'),
                        params: {
	                        'nodeId[]': nodes,
	                        'files[]': files
	                    },
                        success: function (response) {
                        	//$("#"+t.id).unmask(); // TODO UNMASK
	                        var defaultVal = [];
	                        var mimes = Ext.JSON.decode(response.responseText);
	                        mimes = mimes.mimetypes || false;
	                        if (mimes) {
	                            if(Ext.Array.pluck(mimes, 'mimetype').indexOf("application/pdf") > -1)
	                                defaultVal = ["application/pdf"];
	                        }
	                        else {
	                            mimes = [];
	                        }

	                        Ext.define('MimesModel', {
	                            extend: 'Ext.data.Model',
	                            fields: [
	                                {name: 'extension', type: 'string'},
	                                {name: 'mimetype',  type: 'string'},
	                                {name: 'fullName',  type: 'string'},
	                                {name: 'engine',  type: 'string'},
	                                {name: 'name', type: 'string'}
	                            ]
	                        });
	                        
	                        var mimesStore = Ext.create('Ext.data.Store', {
	                            model: 'MimesModel',
	                            data: mimes
	                        });
	                        
	                        Ext.require('Ext.ux.form.ItemSelector', function() {
	                            transformNodes.add(
	                                    Ext.create('Ext.form.Panel', {
	                                        bodyPadding: 10,
	                                        border: false,
	                                        height: 400,
	                                        defaultType: 'textfield',
	                                        defaults: {
	                                            anchor: '100%',
	                                            labelAlign: 'top',
	                                            labelStyle: {
	                                                fontWeigh: 'bold'
	                                            }
	                                        },
	                                        api: {
	                                            submit: function(formHTML, func, formPanel, action){
	                                                var values = formPanel.form.getFieldValues();

	                                                if(files && files.length > 0) {
	                                                    uploader.startTransformation(values);
	                                                    transformNodes.close();
	                                                    return;
	                                                }

	                                                values['nodeId[]'] = nodes;

	                                                //$("#"+formPanel.form.owner.id).mask(Ifresco.helper.Translations.trans('Transformation...'),300); // TODO MASKING

	                                                Ext.Ajax.request({
	                                                    url: Routing.generate('ifresco_client_node_actions_do_transform'),
	                                                    params: values,
	                                                    success: function(response){
	                                                        //$("#"+formPanel.form.owner.id).unmask(); // TODO UNMASK

	                                                        var data = $.JSON.decode(response.responseText);

	                                                        if(data.transformations && data.transformations.length > 0) {
	                                                            transformNodes.close();

	                                                            var msg = Ifresco.helper.Translations.trans('Following files were processed')+': <br /><br />';

	                                                            var transItem;
	                                                            for (var i = 0; i < data.transformations.length; i++) {
	                                                                transItem = data.transformations[i];
	                                                                if(transItem.successfull) {
	                                                                    msg += transItem.name+' - <b>'+Ifresco.helper.Translations.trans('Done')+'</b><br />';
	                                                                }
	                                                                else {
	                                                                    msg += transItem.targetMimetype+' - <b>'+Ifresco.helper.Translations.trans('Not done')+'</b> ('+transItem.message+')<br />';
	                                                                }
	                                                            }

	                                                            Ext.MessageBox.show({
	                                                                title: Ifresco.helper.Translations.trans('Transformation result'),
	                                                                msg: msg,
	                                                                buttons: Ext.MessageBox.OK,
	                                                                icon: Ext.MessageBox.INFO
	                                                            });
	                                                        }

	                                                        /*if(eval('typeof refreshGrid'+container) == 'function') {
	                                                            eval('refreshGrid'+container+'()');
	                                                        }*/
	                                                        // TODO Refresh here of grid
	                                                    }
	                                                });
	                                            }
	                                        },
	                                        items: [
	                                            /*{
	                                                fieldLabel: 'Choose your primary transformation',
	                                                xtype: 'combobox',
	                                                store: mimesStore,
	                                                displayField: 'fullName',
	                                                valueField: 'mimetype',
	                                                queryMode: 'local',
	                                                allowBlank: false,
	                                                name: 'targetMimetype'
	                                            },*/
	                                            {
	                                                xtype: 'itemselector',
	                                                name: 'additionals[]',
	                                                id: 'itemselector-field',
	                                                fieldLabel: 'Choose your additional transformations',
	                                                imagePath: '/js/extjs4/ux/css/images',
	                                                store: mimesStore,
	                                                displayField: 'fullName',
	                                                valueField: 'mimetype',
	                                                value: defaultVal,
	                                                msgTarget: 'side',
	                                                fromTitle: 'Supported transformations',
	                                                toTitle: 'Selected transformations',
	                                                height: 300
	                                            },
	                                            {
	                                                xtype: 'container',
	                                                items: [
	                                                    {
	                                                        xtype: 'button',
	                                                        enableToggle: true,
	                                                        text: Ifresco.helper.Translations.trans('AutoOCR'),
	                                                        pressed: true,
	                                                        handler: function(b, e) {
	                                                            var alfStdr = b.nextSibling();
	                                                            transformNodes.filterThis(b, alfStdr);
	                                                        }
	                                                    },
	                                                    {
	                                                        xtype: 'button',
	                                                        margin: '0 0 0 20',
	                                                        enableToggle: true,
	                                                        text: Ifresco.helper.Translations.trans('Alfresco Standard'),
	                                                        pressed: true,
	                                                        handler: function(b, e) {
	                                                            var autoOCR = b.previousSibling();
	                                                            transformNodes.filterThis(autoOCR, b);
	                                                        }
	                                                    }
	                                                ]
	                                            },
	                                            {
	                                                xtype: 'checkbox',
	                                                name: 'overwriteSourceNode',
	                                                fieldLabel: Ifresco.helper.Translations.trans('Replace the source document'),
	                                                labelAlign: 'left',
	                                                labelWidth: 200
	                                            },
	                                            {
	                                                xtype: 'checkbox',
	                                                name: 'overwriteTargetNode',
	                                                fieldLabel: Ifresco.helper.Translations.trans('Replace target documents'),
	                                                labelAlign: 'left',
	                                                labelWidth: 200
	                                            }
	                                        ]
	                                    })
	                            )
	                        });
                        }
                    });
	            }
	        },
	        buttons: [
	            {
	                text: Ifresco.helper.Translations.trans('Transform'),
	                handler: function() {
	                    var form = transformNodes.items.items[0];
	                    form.submit();
	                }
	            },
	            {
	                text: Ifresco.helper.Translations.trans('Close'),
	                handler: function() {
	                    transformNodes.close();
	                }
	            }
	        ]
	    });

	    return transformNodes;
    }
});