Ext.define('Ifresco.view.AdvancedSearchTab', {
    extend: 'Ifresco.view.MetadataForm',
    alias: 'widget.advancedSearchTab',
    itemId: 'ifrescoAdvancedSearchTab',
    closable:true,
    border: 0,
    cls: 'ifresco-view-content-tab',
    id: 'advancedSearch',
    layout: {
        type: 'hbox',
        align: 'stretch'
    },
    autoScroll: true,
    searchId: null,
    savedSearchId: null,
    fromSaved: false,
    savedRecord: null,
    items: [],

    initComponent: function () {
        var searchTemplatesStore = Ifresco.store.SearchSearchTemplates.create({});
        var me = this;
        searchTemplatesStore.on('load', function(store, records) {
        	if (me.fromSaved) {
            	console.log("LOAD saved SEARCH",me.savedRecord);
            	me.fireEvent('loadSearchForm', me.savedRecord.get('template'), me.savedRecord);
            }
        	else {
	            Ext.each(records, function (record) {
	            	console.log("load search templates",me.fromSaved,record);
	            	
	            	if (record.get('isDefaultView')) {
	                    me.fireEvent('loadSearchForm', record.get('id'));
	                    return false;
	                }
	            });
        	}
        });
        Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('Advanced Search'),
            savedSearchesStore: Ifresco.store.SavedSearches.create({}),
            items: [{
                xtype: 'panel',
                flex: 1,
                cls: 'ifresco-view-advancedsearch-lookin',
                maxWidth: 250,
                layout: {
                    type: 'fit'
                },
                collapsible: true,
                collapseDirection: 'left',
                title: Ifresco.helper.Translations.trans('Look in'),
                tbar: [{
                    text: 'Reset Selection',
                    handler: function (btn) {
                        var panel = btn.up('panel[cls~=ifresco-view-advancedsearch-lookin]');
                        Ext.each(panel.query('treepanel'), function (treePanel) {
                            var checkedNodes = treePanel.getChecked();
                            Ext.each(checkedNodes, function (node) {
                                node.set('checked', false);
                            });
                        });
                        panel.down('tagsfield').getStore().removeAll();
                        Ext.each(panel.query('radio[cls~=ifresco-view-advancedsearch-lookin-all]'), function (radio) {
                            radio.setValue(true);
                        });
                    }
                }],
                items: [{
                    xtype: 'tabpanel',
                    flex: 1,
                    border: 0,
                    defaults: {
                        border: 0
                    },
                    layout: 'fit',
                    items: [{
                        xtype: 'panel',
                        layout: {
                            type: 'vbox',
                            align: 'stretch'
                        },
                        padding: 5,
                        cls: 'ifresco-view-advancedsearch-lookin-spaces',
                        tabConfig: {
                            title: 'Location'
                        },
                        items: [{
                            xtype: 'radio',
                            name: 'spaces',
                            cls: 'ifresco-view-advancedsearch-lookin-all',
                            checked: true,
                            boxLabel: Ifresco.helper.Translations.trans('All Spaces')
                        },{
                            xtype: 'radio',
                            name: 'spaces',
                            cls: 'ifresco-view-advancedsearch-lookin-custom',
                            boxLabel: Ifresco.helper.Translations.trans('Specify Space'),
                            listeners: {
                                change: function (radio, isChecked) {
                                    radio.up('panel').down('treepanel').setDisabled(! isChecked);
                                    Ext.each(radio.up('panel').query('button'), function (button) {
                                        button.setDisabled(! isChecked);
                                    });
                                }
                            }
                        },{
                            xtype: 'container',
                            padding: '5 0 5 0',
                            layout: 'hbox',
                            items: [{
                                xtype: 'button',
                                margin: '0 5 0 0',
                                disabled: true,
                                flex: 2,
                                text: Ifresco.helper.Translations.trans('Auto. check sub-folders'),
                                enableToggle: true,
                                handler: function (btn, pressed) {
                                    btn.up('panel[cls~=ifresco-view-advancedsearch-lookin-spaces]')
                                        .down('treepanel')
                                        .autoCheckSub = pressed;
                                }
                            },{
                                xtype: 'button',
                                flex: 1,
                                disabled: true,
                                text: Ifresco.helper.Translations.trans('Uncheck all'),
                                handler: function (btn) {
                                    var treePanel = btn.up('panel[cls~=ifresco-view-advancedsearch-lookin-spaces]').down('treepanel');
                                    var checkedNodes = treePanel.getChecked();
                                    Ext.each(checkedNodes, function (node) {
                                        node.set('checked', false);
                                    });
                                }
                            }]
                        },{
                            xtype: 'treepanel',
                            store: Ifresco.store.SearchTreeFolders.create({}),
                            disabled: true,
                            animate: false,
                            autoCheckSub: false,
                            flex: 1,
                            autoScroll: true,
                            listeners: {
                                enable: function (tree) {
                                    tree.getRootNode().expand();
                                },
                                checkchange: function (node, checked) {
                                    var onExpand = function (node) {
                                        node.eachChild(function (childNode) {
                                            childNode.set('checked', checked);
                                        });
                                        node.removeListener('expand', onExpand);
                                    };
                                    if (this.autoCheckSub) {
                                        if (checked) {
                                            node.expand();
                                        }
                                        if (node.isLoaded()) {
                                            onExpand(node);
                                        } else {
                                            node.on('expand', onExpand);
                                        }
                                    }
                                }
                            },
                            getValues: function () {
                                var checkedNodes = this.getChecked();
                                var values = [];
                                Ext.each(checkedNodes, function (node) {
                                    values.push(node.get('id'));
                                });
                                return values;
                            }
                        }]
                    },{
                        xtype: 'panel',
                        cls: 'ifresco-view-advancedsearch-lookin-categories',
                        layout: {
                            type: 'vbox',
                            align: 'stretch'
                        },
                        padding: 5,
                        tabConfig: {
                            title: Ifresco.helper.Translations.trans('Categories')
                        },
                        items: [{
                            xtype: 'radio',
                            cls: 'ifresco-view-advancedsearch-lookin-all',
                            name: 'categories',
                            checked: true,
                            boxLabel: Ifresco.helper.Translations.trans('All Categories')
                        },{
                            xtype: 'radio',
                            cls: 'ifresco-view-advancedsearch-lookin-custom',
                            name: 'categories',
                            boxLabel: Ifresco.helper.Translations.trans('Specify Categories'),
                            listeners: {
                                change: function (radio, isChecked) {
                                    radio.up('panel').down('treepanel').setDisabled(! isChecked);
                                    Ext.each(radio.up('panel').query('button'), function (button) {
                                        button.setDisabled(! isChecked);
                                    });
                                }
                            }
                        },{
                            xtype: 'container',
                            layout: 'hbox',
                            padding: '5 0 5 0',
                            items: [{
                                xtype: 'button',
                                flex: 2,
                                margin: '0 5 0 0',
                                text: Ifresco.helper.Translations.trans('Auto. check sub-categories'),
                                disabled: true,
                                enableToggle: true,
                                handler: function (btn, pressed) {
                                    btn.up('panel[cls~=ifresco-view-advancedsearch-lookin-categories]')
                                        .down('treepanel')
                                        .autoCheckSub = pressed;
                                }
                            },{
                                flex: 1,
                                xtype: 'button',
                                text: Ifresco.helper.Translations.trans('Uncheck all'),
                                disabled: true,
                                handler: function (btn) {
                                    var treePanel = btn.up('panel[cls~=ifresco-view-advancedsearch-lookin-categories]').down('treepanel');
                                    var checkedNodes = treePanel.getChecked();
                                    Ext.each(checkedNodes, function (node) {
                                        node.set('checked', false);
                                    });
                                }
                            }]
                        },{
                            xtype: 'treepanel',
                            store: Ifresco.store.SearchTreeCategories.create({}),
                            disabled: true,
                            flex: 1,
                            autoScroll: true,
                            autoCheckSub: false,
                            listeners: {
                                enable: function (tree) {
                                    tree.getRootNode().expand();
                                },
                                checkchange: function (node, checked) {
                                    var onExpand = function (node) {
                                        node.eachChild(function (childNode) {
                                            childNode.set('checked', checked);
                                        });
                                        node.removeListener('expand', onExpand);
                                    };
                                    if (this.autoCheckSub) {
                                        if (checked) {
                                            node.expand();
                                        }
                                        if (node.isLoaded()) {
                                            onExpand(node);
                                        } else {
                                            node.on('expand', onExpand);
                                        }
                                    }
                                }
                            },
                            getValues: function () {
                                var checkedNodes = this.getChecked();
                                var values = [];
                                Ext.each(checkedNodes, function (node) {
                                    values.push(node.get('id'));
                                });
                                return values;
                            }
                        }]
                    },{
                        xtype: 'panel',
                        padding: 5,
                        layout: 'fit',
                        tabConfig: {
                            title: 'Tags'
                        },
                        items: [{
                            xtype: 'tagsfield'
                        }]
                    }]
                }]
            },{
                xtype: 'form',
                cls: 'ifresco-view-advancedsearch-form',
                templateId: 0,
                border: 0,
                margin: 0,
                padding: 0,
                layout: 'border',
                flex: 1,
                tbar: [{
                	cls: 'ifresco-search-button-toolbar',
                	width:100,
                	scale: 'medium',
                    text: Ifresco.helper.Translations.trans('Search'),
                    handler: function () {
                        this.up('advancedSearchTab').fireEvent('quickSearch');
                    }
                },{
                    iconCls: 'ifresco-icon-panel',
                    handler: function (button) {
                        var store = button.up('advancedSearchTab').savedSearchesStore;
                        Ifresco.view.window.ManageSavedSearches.create({configData: {store: store}}).show();
                    }
                },{
                    iconCls: 'ifresco-icon-magnifier-zoom-in',
                    handler: function () {
                        Ifresco.view.window.SaveSearch.create({}).show();
                    }
                },{
                    iconCls: 'ifresco-icon-magnifier',
                    cls: 'ifresco-view-advancedsearch-saved-menu',
                    menu: []
                },'->', {
                    text: Ifresco.helper.Translations.trans('Reset Fields'),
                    handler: function (button) {
                        button.up('advancedSearchTab').resetForm();
                    }
                },{
                    text: Ifresco.helper.Translations.trans('Reload'),
                    handler: function () {
                        var searchTab = this.up('panel#ifrescoAdvancedSearchTab');
                        var templateId = searchTab.down('combo[cls~=ifresco-view-advancedsearch-form-searchtemplate]')
                            .getValue();
                        var savedSearch = searchTab.savedSearchesStore.findRecord('id', searchTab.savedSearchId);
                        searchTab.fireEvent('loadSearchForm', templateId, savedSearch);
                    }
                },'-',{
                    xtype: 'combo',
                    store: searchTemplatesStore,
                    cls: 'ifresco-view-advancedsearch-form-searchtemplate',
                    displayField: 'name',
                    // isFormField: false,
                    valueField: 'id',
                    fieldLabel: Ifresco.helper.Translations.trans('Search Template'),
                    queryMode: 'local',
                    padding: '0 0 0 10',
                    listeners: {
                        select: function (combo, records) {
                            var searchTab = this.up('panel#ifrescoAdvancedSearchTab');
                            console.log("SELECTED TEMPLATE",records[0]);
                            searchTab.fireEvent('loadSearchForm', records[0].get('id'));
                        }
                    },
                    getSubmitValue: function () {
//                        return;
                    },
                    reset: function () {
//                        return;
                    }
                }],
                items: [{
                    xtype: 'panel',
                    region: 'center',
                    border: 0,
                    layout: {
                        type: 'hbox',
                        align: 'stretch'
                    },
                    autoScroll: true,
                    items: [{
                        autoScroll: true,
                        flex: 1,
                        layout: {
                            type: 'vbox',
                            align: 'stretch'
                        },
                        maxWidth: 500,
                        minWidth: 300,
                        border: 0,
                        bodyPadding: 10,
                        xtype: 'panel',
                        cls: 'ifresco-view-advancedsearch-column1'
                    },{
                        autoScroll: true,
                        flex: 1,
                        layout: {
                            type: 'vbox',
                            align: 'stretch'
                        },
                        maxWidth: 500,
                        minWidth: 300,
                        border: 0,
                        bodyPadding: 10,
                        xtype: 'panel',
                        cls: 'ifresco-view-advancedsearch-column2'
                    }]
                }]
            }]
        });

        var advancedSearchTab = this;
        this.savedSearchesStore.on('load', function (store, records) {
            var btn = advancedSearchTab.down('button[cls~=ifresco-view-advancedsearch-saved-menu]');
            var handler = function (item) {
                advancedSearchTab.fireEvent('loadSavedSearch', item.savedSearchId);
            };
            btn.menu.removeAll();
            Ext.each(records, function (record) {
                btn.menu.add({
                    text: record.get('name'),
                    savedSearchId: record.get('id'),
                    handler: handler
                });
            });
        });

        this.callParent();
    },


    loadForm: function (data) {
    	console.log("LOAD FORM SEARCH",data);
        var form = this.down('form[cls~=ifresco-view-advancedsearch-form]');
        var column1 = form.down('panel[cls~=ifresco-view-advancedsearch-column1]');
        var column2 = form.down('panel[cls~=ifresco-view-advancedsearch-column2]');
        var tabs = form.down('tabpanel[cls~=ifresco-view-advancedsearch-tabs]');
        if (tabs !== null) {
            tabs.destroy();
        }
        column1.removeAll();
        column2.removeAll();
        Ext.each(data.fields, function (field) {
            var fieldEl = this.createField(field, data.config);
            switch (field.column) {
                case 1:
                    column1.add(fieldEl);
                    break;
                case 2:
                    column2.add(fieldEl);
                    break;
                default:
                    column1.add(fieldEl);
            }
        }, this);

        if (data.tabs.length > 0) {
            tabs = this.createTabPanel();
            var tabsItems = [];
            Ext.each(data.tabs, function (tab) {
                tabsItems.push(this.createTab(tab, data.config));
            }, this);
            tabs.items = tabsItems;
            form.add(tabs);
        }
        
        this.config = data.config;
    },

    resetForm: function () {
        this.savedSearchId = null;
        this.down('form[cls~=ifresco-view-advancedsearch-form]').getForm().reset();
    },

    fillForm: function (settingsVals, formVals, savedSearchId) {
        var form = this.down('form[cls~=ifresco-view-advancedsearch-form]');
        var tags = (settingsVals.tags && settingsVals.tags.split(',')) || [];
        this.savedSearchId = savedSearchId;

        var locationsPanel = this.down('panel[cls~=ifresco-view-advancedsearch-lookin-spaces]');
        this.fillTree(locationsPanel, settingsVals.locations);
        var categoriesPanel = this.down('panel[cls~=ifresco-view-advancedsearch-lookin-categories]');
        this.fillTree(categoriesPanel, settingsVals.categories);

        var tagsRecords = [];
        Ext.each(tags, function (tag) {
            if (tag.length == 0) {
                return;
            }
            tagsRecords.push({
                tagName: tag
            });
        });
        this.down('tagsfield').getStore().loadData(tagsRecords);
        
        var dateFormat = Ifresco.helper.Settings.get("DateFormat"), dateFormat = dateFormat ? dateFormat : 'd/m/Y';

        
        Ext.iterate(formVals, function(key, value) {
        	console.log("ITARATE",key,value);
        	if (key.match(/#from/g) || key.match(/#to/g)) {
        		console.log("FOUND DATE FIELD",key);

	        	if (value.length > 0) {
	        		if (value == "%TODAY%") {
		        		value = new Date();
		        	}
	        		
	        		if (value instanceof Date) {
	        			//value = Ext.Date.parse(value,dateFormat);
	        			value = Ext.Date.format(value,dateFormat);
	        		}

	        		console.log("DATE INSTACNE",key,value,dateFormat,form.getForm().findField(key));
	        		//form.getForm().findField(key).setValue(value);
	        		form.down("[name="+key+"]").setValue(value);
	        		
	        		delete formVals[key];

	        	}
        	}
        	else if (key.match(/-checkbox/g)) {
        		var fieldset = form.down("fieldset[checkboxName="+key+"]");
        		if (fieldset != null) {
        			if (value == "off")
        				fieldset.collapse();
        		}
        		delete formVals[key];
        	}
        });
        console.log(formVals);
        form.getForm().setValues(formVals);
    },

    fillTree: function (lookinPanel, paths) {
        var treePanel = lookinPanel.down('treepanel');
        treePanel.collapseAll();
        if (! (paths && paths.length)) {
            lookinPanel.down('radio[cls~=ifresco-view-advancedsearch-lookin-all]').setValue(true);
            return;
        }
        var pathsArray = [];
        Ext.each(paths, function (path) {
            pathsArray.push(path.path);
        });
        var checkedNodes = treePanel.getChecked();
        Ext.each(checkedNodes, function (node) {
            node.set('checked', false);
        });

        treePanel.getRootNode().on('expand', function onExpand (parentNode) {
            parentNode.removeListener('expand', onExpand);
            parentNode.eachChild(function (childNode) {
                Ext.each(pathsArray, function (path, id) {
                    if (path === null) {
                        return;
                    }
                    var splitPath = path.split('/');
                    var idx = Ext.Array.indexOf(splitPath, childNode.get('text'));
                    //TODO: fix to childNode.get('text')
                    var nodePath = '/' + decodeURI(childNode.get('id')) + '/';
                    var splitNodePath = nodePath.split('/');
                    if (idx !== -1) {
                        if (path === nodePath) {
                            childNode.set('checked', true);
                            pathsArray[id] = null;
                            return;
                        }
                        var i;
                        for (i = 0; i < idx; i++) {
                            if (splitPath[i] !== splitNodePath[i]) {
                                return;
                            }
                        }
                        if (childNode.isLeaf()) {
                            return;
                        }
                        if (childNode.isLoaded()) {
                            onExpand(childNode);
                        }
                        else {
                            childNode.on('expand', onExpand);
                        }
                        childNode.expand();
                    }
                });
            });
        });
        lookinPanel.down('radio[cls~=ifresco-view-advancedsearch-lookin-custom]').setValue(true);
    }
});