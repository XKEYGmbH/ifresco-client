Ext.define('Ifresco.controller.Search', {
    extend: 'Ext.app.Controller',
    requires: ['Ifresco.view.ContentTab'],
    refs: [{
        selector: 'viewport > ifrescoCenter',
        ref: 'tabPanel'
    },{
        selector: 'viewport > ifrescoCenter > #ifrescoAdvancedSearchTab',
        ref: 'advancedSearchTab'
    },{
        selector: '#ifrescoAdvancedSearchTab > form[cls~=ifresco-view-advancedsearch-form]',
        ref: 'advancedSearchForm'
    },{
        selector: 'ifrescoCenter > #ifrescoSearchResults',
        ref: 'searchResultsTab'
    }],

    init: function() {
        this.control({
            'advancedSearchTab': {
                loadSearchForm: this.loadSearchForm,
                quickSearch: this.loadQuickSearchGrid,
                loadSavedSearch: this.loadSavedSearch,
                search: this.loadQuickSearchGrid
            },
            'ifrescoViewWindowSaveSearch': {
                saveSearch: this.saveSearch
            },
            'ifrescoFormSearch': {
                search: this.loadSearchGrid
            }
        });
    },

    loadSavedSearch: function (id) {
        var searchTab = this.getAdvancedSearchTab();
        if (! parseInt(id, 10)) {
            searchTab.resetForm();
            return;
        }

        var savedSearch = searchTab.savedSearchesStore.findRecord('id', id);
        console.log("saved search load",savedSearch)
        var form = this.getAdvancedSearchForm();
        if (form.templateId !== savedSearch.get('template')) {
            this.loadSearchForm(savedSearch.get('template'), savedSearch);
        } else {
            searchTab.fillSearchForm(
                savedSearch.get('data')[0], 
                savedSearch.get('data')[1], 
                savedSearch.get('id')
            );
        }
    },

    loadSearchForm: function (templateId, savedSearch) {
        var form = this.getAdvancedSearchForm();
        form.setLoading(true, true);
        var searchTab = this.getAdvancedSearchTab();
        var searchTemplatesCombo = form.down('combo[cls~=ifresco-view-advancedsearch-form-searchtemplate]');
        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_search_template_data_get'),
            // url: '/tmp/search_search_form_get',
            disableCaching: true,
            params: {
                id: templateId || 'null'
            },
            success: function (res) {
                var data = Ext.decode(res.responseText);
                var templateSavedSearchId = parseInt(data.data.config.savedSearchId, 10);
                searchTab.loadForm(data.data);
                savedSearch = savedSearch || searchTab.savedSearchesStore.findRecord('id', templateSavedSearchId);
                if (savedSearch) {
                    searchTab.fillForm(
                        savedSearch.get('data')[0],
                        savedSearch.get('data')[1],
                        savedSearch.get('id')
                    );
                // } else {
                //     searchTab.resetForm();
                }
                if (searchTemplatesCombo.getValue !== templateId) {
                    searchTemplatesCombo.select(templateId);
                }
                form.setLoading(false);
            }
        });
    },

    getSearchData: function () {
        var searchTab = this.getAdvancedSearchTab();
        var form = this.getAdvancedSearchForm();
        var locations = [];
        var locationsTree = searchTab.down('panel[cls~=ifresco-view-advancedsearch-lookin-spaces] > treepanel');
        if (! locationsTree.isDisabled()) {
            var checkedLocations = locationsTree.getChecked();
            Ext.each(checkedLocations, function (location) {
                locations.push({
                    nodeId: location.get('nodeId'),
                    path: location.get('path'),
                    qpath: location.get('qpath')
                });
            });
        }

        var categories = [];
        var categoriesTree = searchTab.down('panel[cls~=ifresco-view-advancedsearch-lookin-categories] > treepanel');
        if (! categoriesTree.isDisabled()) {
            var checkedCategories = categoriesTree.getChecked();
            Ext.each(checkedCategories, function (category) {
                categories.push({
                    nodeId: category.get('nodeId'),
                    path: category.get('path'),
                    qpath: category.get('qpath')
                });
            });
        }

        var searchTemplatesCombo = searchTab.down('combo[cls~=ifresco-view-advancedsearch-form-searchtemplate]');
        var searchTemplate = searchTemplatesCombo.getStore().findRecord('id', searchTemplatesCombo.getValue());
        var columnSetId = 0;
        var searchTemplateId = 0;
        if (searchTemplate) {
            columnSetId = searchTemplate.get('columnSetId');
            searchTemplateId = searchTemplate.get('id');
        } 
        
        var formValues = this.getAdvancedSearchForm().getValues();
        try {
	        Ext.iterate(formValues, function(key, value) { // FIX ARRAY RETURNS
	        	try {
		        	if (value instanceof Array) {
		        		console.log("VALUE IS ARRAY",value); // SOME ERROR HERE 
		        		/*TypeError: formn.down(...) is null
		"VALUE IS ARRAY" "boku_datumVon-checkbox" Array [ "on", "on", "on", "on", "on" ] null
		"VALUE IS ARRAY" "boku_datumVon#from" Array [ "", "", "", "", "" ] ""
		"VALUE IS ARRAY" "boku_datumVon#to" Array [ "", "", "", "", "" ] ""
		"VALUE IS ARRAY" "boku_bezahltAm-checkbox" Array [ "on", "on", "on", "on", "on" ] null
		"VALUE IS ARRAY" "boku_bezahltAm#from" Array [ "", "", "", "", "" ] ""
		"VALUE IS ARRAY" "boku_bezahltAm#to" Array [ "", "", "", "", "" ] ""*/
		        		//value = form.findField(key).getValue();
		        		formValues[key] = form.down("[name="+key+"]").getSubmitValue();
		        	}
	        	}
	        	catch(ex) {}
	        });
        }
        catch (e) {}
        
        try {
	        Ext.iterate(form.query("fieldset"), function(obj,i) {
	        	try {
		        	if ('checkboxName' in obj) {
		        		var checkbox = obj.down("checkbox").getValue();
		        		if (checkbox == false)
		        			formValues[obj.checkboxName] = "off";
		        	}
	        	}
	        	catch(ex) {}
	        });
        }
        catch (e) {}
        
        console.log("FORMVALS",formValues);
        
        return {
            locations: locations,
            categories: categories,
            tags: searchTab.down('tagsfield').getValues().join(','),
            formValues: formValues,
            columnSetId: columnSetId || Ifresco.helper.Registry.get('ColumnSetId'),
            searchTemplateId: searchTemplateId
        };
    },

    loadQuickSearchGrid: function () {
        var searchTab = this.getAdvancedSearchTab();
        var tabPanel = this.getTabPanel();
        var currentSearchTab;
        var searchData = this.getSearchData();
        if (searchTab.isLoading) {
            return;
        }
        searchTab.isLoading = true;

        var dateNow = new Date();
        var ifrescoId = dateNow.getTime();
     // TODO - check if timestamp already exists in tab
        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_data_grid_index'),
            params: {
                columnSetId: searchData.columnSetId,
            },
            success: function (res) {
                
                currentSearchTab = Ifresco.view.ContentTab.create({
                    title: Ifresco.helper.Translations.trans('Search Results') + ' ' + Ext.Date.format(dateNow, 'H:i'),
                    // itemId: 'ifrescoSearchTab' + currentSearchId,
                    configData: Ext.decode(res.responseText),
                    ifrescoId: ifrescoId
                });
                tabPanel.add(currentSearchTab);
                tabPanel.setActiveTab(currentSearchTab);
                currentSearchTab.reloadGridData({params: {
                    advancedSearchFields: Ext.encode(searchData.formValues),
                    columnSetId: searchData.columnSetId,
                    advancedSearchOptions: Ext.encode({
                        "searchTerm": "",
                        "results": "",
                        "locations": searchData.locations,
                        "categories": searchData.categories,
                        "tags": searchData.tags,
                        "contentType": searchTab.config.contentType
                    })
                }});
                searchTab.isLoading = false;
            }
        });
    },
    
    doSearch: function (fields,options,columnSetId) {
        var tabPanel = this.getTabPanel();
        var currentSearchTab;
        // TODO - check if timestamp already exists in tab
        var dateNow = new Date();
        var ifrescoId = dateNow.getTime();
        
        
        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_data_grid_index'),
            params: {
                columnSetId: columnSetId
            },
            success: function (res) {
                currentSearchTab = Ifresco.view.ContentTab.create({
                    title: Ifresco.helper.Translations.trans('Search Results') + ' ' + Ext.Date.format(dateNow, 'H:i'),
                    configData: Ext.decode(res.responseText),
                    ifrescoId: ifrescoId
                });
                
                tabPanel.add(currentSearchTab);
                tabPanel.setActiveTab(currentSearchTab);
                currentSearchTab.reloadGridData({params: {
                    advancedSearchFields: Ext.encode(fields),
                    columnSetId: columnSetId,
                    advancedSearchOptions: Ext.encode(options)
                }});
            }
        });
    },

    loadSearchGrid: function (searchTerm) {
        var tabPanel = this.getTabPanel(),
        	activeTab = tabPanel.getActiveTab();
        
        var quicksearchTriggers = Ifresco.helper.Plugins.getQuicksearchTriggers();
        console.log("QUICKSEARCH TRIGGERS", quicksearchTriggers);
        if (Ext.Array.contains(quicksearchTriggers, activeTab.xtype)) {
        	activeTab.fireEvent("quicksearch",searchTerm);
        }
        else {
        
	        var columnSetId = Ifresco.helper.Registry.get('ColumnsetId'),
	        	searchResultsTab = this.getSearchResultsTab();
	        if (searchResultsTab) {
	            searchResultsTab.reloadGridData({params: {
	                columnSetId: columnSetId,
	                searchTerm: searchTerm
	            }}, true);
	            tabPanel.setActiveTab(searchResultsTab);
	            return;
	        }
	        
	        
	        
	        
	        
	        var dateNow = new Date();
	        var ifrescoId = dateNow.getTime();
	
	        Ext.Ajax.request({
	            url: Routing.generate('ifresco_client_data_grid_index'),
	            params: {
	                columnSetId: Ifresco.helper.Registry.get('ColumnsetId')
	            },
	            success: function (res) {
	                searchResultsTab = Ifresco.view.ContentTab.create({
	                    title: Ifresco.helper.Translations.trans('Search Results'),
	                    itemId: 'ifrescoSearchResults',
	                    configData: Ext.decode(res.responseText),
	                    ifrescoId: ifrescoId
	                });
	                tabPanel.add(searchResultsTab);
	                tabPanel.setActiveTab(searchResultsTab);
	                searchResultsTab.reloadGridData({params: {
	                    columnSetId: columnSetId,
	                    searchTerm: searchTerm
	                }}, true);
	            }
	        });
        }
    },

    saveSearch: function (name, isGeneral) {
        var searchData = this.getSearchData();
        var data = [{
                searchTerm: '',
                results: '',
                locations: searchData.locations,
                categories: searchData.categories,
                tags: searchData.tags
            },
            searchData.formValues
        ];
        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_search_save'),
            params: {
                data: Ext.encode(data),
                template: searchData.searchTemplateId,
                searchName: name,
                searchPrivacy: isGeneral || false
            },
            disableCaching: true,
            success: function(res) {
                var resData = Ext.decode(res.responseText);
                Ext.ux.StatusMessage.show({
                    title: Ifresco.helper.Translations.trans('Save Search'),
                    successMsg: Ifresco.helper.Translations.trans('Search successfully saved'),
                    errorMsg: Ifresco.helper.Translations.trans('Save failed, please try later'),
                    success: resData.success
                });
                this.getAdvancedSearchTab().savedSearchesStore.reload();
            },
            failure: function() {
                Ext.ux.ErrorMessage.show({
                    title: Ifresco.helper.Translations.trans('Save Search'),
                    msg: Ifresco.helper.Translations.trans('Save failed, please try later')
                });
            },
            scope: this
        });
    }
});