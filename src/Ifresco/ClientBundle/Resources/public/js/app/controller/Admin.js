Ext.define('Ifresco.controller.Admin', {
    extend: 'Ext.app.Controller',
    refs: [{
        selector: 'viewport > ifrescoCenter',
        ref: 'tabPanel'
    },{
        selector: 'viewport > ifrescoCenter > #ifrescoAdminTab',
        ref: 'adminTab'
    },{
        selector: 'adminTab > panel[cls~=ifresco-admin-content-panel]',
        ref: 'adminTabContentPanel'
    },{
        selector: 'adminTab > panel[cls~=ifresco-admin-content-panel] > ifrescoViewGridTemplate',
        ref: 'adminTemplate'
    },{
        selector: 'adminTab > panel[cls~=ifresco-admin-content-panel] > ifrescoViewGridDataSource',
        ref: 'adminDataSourceGrid'
    },{
        selector: 'adminTab > panel[cls~=ifresco-admin-content-panel] > ifrescoViewQuickSearch',
        ref: 'adminQuickSearch'
    },{
        selector: 'adminTab > panel[cls~=ifresco-admin-content-panel] > ifrescoViewGridColumnSets',
        ref: 'adminColumnSetsGrid'
    },{
        selector: 'adminTab > panel[cls~=ifresco-admin-content-panel] > ifrescoFormColumnSet',
        ref: 'adminColumnSetForm'
    },{
        selector: 'adminTab > panel[cls~=ifresco-admin-content-panel] > ifrescoViewSettingsSystem',
        ref: 'systemSettingsPanel'
    },{
        selector: 'adminTab > panel[cls~=ifresco-admin-content-panel] > ifrescoViewSettingsEmail',
        ref: 'emailSettingsPanel'
    },{
        selector: 'adminTab > panel[cls~=ifresco-admin-content-panel] > ifrescoViewSettingsInterface',
        ref: 'interfaceSettingsPanel'
    },{
        selector: 'adminTab > panel[cls~=ifresco-admin-content-panel] > ifrescoViewSettingsNamespaceMapping',
        ref: 'namespaceMappingSettingsPanel'
    },{
        selector: 'adminTab > panel[cls~=ifresco-admin-content-panel] > ifrescoViewSettingsPropertyFilter',
        ref: 'propertyFilterSettingsPanel'
    },{
        selector: 'adminTab > panel[cls~=ifresco-admin-content-panel] > ifrescoViewSettingsOnlineEditing',
        ref: 'onlineEditingSettingsPanel'
    },{
        selector: 'adminTab > panel[cls~=ifresco-admin-content-panel] > ifrescoViewSettingsTreeRootFolder',
        ref: 'treeRootFolderSettingsPanel'
    },{
        selector: 'adminTab > panel[cls~=ifresco-admin-content-panel] > ifrescoViewSettingsDropboxConfig',
        ref: 'dropboxConfigSettingsPanel'
    },{
        selector: 'adminTab > panel[cls~=ifresco-admin-content-panel] > ifrescoViewSettingsAspects',
        ref: 'aspectsSettingsPanel'
    },{
        selector: 'adminTab > panel[cls~=ifresco-admin-content-panel] > ifrescoViewSettingsContentTypes',
        ref: 'contentTypesSettingsPanel'
    },{
        selector: 'adminTab > panel[cls~=ifresco-admin-content-panel] > ifrescoViewSettingsUploadAllowedTypes',
        ref: 'uploadAllowedTypesSettingsPanel'
    },{
        selector: 'adminTab > panel[cls~=ifresco-admin-content-panel] > ifrescoViewExport',
        ref: 'exportPanel'
    },{
        selector: 'adminTab > panel[cls~=ifresco-admin-content-panel] > ifrescoViewImport',
        ref: 'importPanel'
    },{
        selector: 'adminTab > panel[cls~=ifresco-admin-content-panel] > ifrescoViewQuickSearch',
        ref: 'quickSearchPanel'
    },{
        selector: 'adminTab > panel[cls~=ifresco-admin-content-panel] > ifrescoViewClickSearch',
        ref: 'clickSearchPanel'
    },{
        selector: 'adminTab > panel[cls~=ifresco-admin-content-panel] > ifrescoViewGridSearchTemplate',
        ref: 'searchTemplateGrid'
    },{
        selector: 'adminTab > panel[cls~=ifresco-admin-content-panel] > ifrescoFormSearchTemplate',
        ref: 'searchTemplateForm'
    },{
        selector: 'adminTab > panel[cls~=ifresco-admin-content-panel] > ifrescoViewExportJobs',
        ref: 'exportJobsPanel'
    },{
        selector: 'adminTab > panel[cls~=ifresco-admin-content-panel] > ifrescoViewGridJobs',
        ref: 'jobsGrid'
    },{
        selector: 'adminTab > panel[cls~=ifresco-admin-content-panel] > ifrescoViewLookups',
        ref: 'lookupsPanel'
    },{
        selector: 'adminTab > panel[cls~=ifresco-admin-content-panel] > ifrescoViewSettingsAutoOCRConfig',
        ref: 'autoOCRConfigPanel'
    },{
        selector: 'adminTab > panel[cls~=ifresco-admin-content-panel] > ifrescoViewSettingsAutoOCRStatus',
        ref: 'autoOCRStatusPanel'
    },{
        selector: 'adminTab > panel[cls~=ifresco-admin-content-panel] > ifrescoViewSettingsAutoOCRTransformers',
        ref: 'autoOCRTransformersPanel'
    },{
        selector: 'adminTab > panel[cls~=ifresco-admin-content-panel] > ifrescoViewSettingsAutoOCRTransformers > gridpanel',
        ref: 'autoOCRTransformersGrid'
    },{
        selector: 'adminTab > panel[cls~=ifresco-admin-content-panel] > ifrescoViewSettingsAutoOCRRuntimeTransformers > gridpanel',
        ref: 'autoOCRRuntimeTransformersGrid'
    },{
        selector: 'adminTab > panel[cls~=ifresco-admin-content-panel] > ifrescoViewGridTagManager',
        ref: 'tagManagerGrid'
    },{
        selector: 'adminTab > panel[cls~=ifresco-admin-content-panel] > ifrescoViewGridPlugins',
        ref: 'pluginsGrid'
    },{
        selector: 'adminTab > panel[cls~=ifresco-admin-content-panel] > ifrescoViewCurrencyFields',
        ref: 'currencyFieldsPanel'
    }],

    init: function () {
        this.control({
            'adminTab': {
                adminTabTemplatesShow : this.loadTemplatesGrid,
                adminTabLookupsShow: this.loadLookupsShow,
                adminTabDataSourcesShow: this.loadDataSourceGrid,
                adminTabQuickSearchShow: this.loadQuickSearch,
                adminTabClickSearchShow: this.loadClickSearch,
                adminTabSearchTemplateShow: this.loadSearchTemplateGrid,
                adminTabColumnSetsShow: this.loadColumnSetsGrid,
                adminTabSettingsSystemShow: this.loadSystemSettings,
                adminTabSettingsInterfaceShow: this.loadInterfaceSettings,
                adminTabSettingsEmailShow: this.loadEmailSettings,
                adminTabSettingsAspectsShow: this.loadAspectsSettings,
                adminTabSettingsUploadAllowedTypesShow: this.loadUploadAllowedTypesSettings,
                adminTabSettingsContentTypesShow: this.loadContentTypesSettings,
                adminTabSettingsNamespaceMappingShow: this.loadNamespaceMappingSettings,
                adminTabSettingsPropertyFilterShow: this.loadPropertyFilterSettings,
                adminTabSettingsOnlineEditingShow: this.loadOnlineEditingSettings,
                adminTabSettingsTreeRootFolderShow: this.loadTreeRootFolderSettings,
                adminTabSettingsDropboxConfigShow: this.loadDropboxConfigSettings,
                adminTabSettingsAutoOCRConfigShow: this.loadAutoOCRConfigSettings,
                adminTabSettingsAutoOCRStatusShow: this.loadAutoOCRStatusSettings,
                adminTabSettingsAutoOCRTransformersShow: this.loadAutoOCRTransformerSettings,
                adminTabSettingsAutoOCRRuntimeTransformersShow: this.loadAutoOCRRuntimeTransformerSettings,
                adminTabSettingsAutoOCRJobsShow: this.loadAutoOCRJobsSettings,
                adminTabExportShow: this.loadExport,
                adminTabImportShow: this.loadImport,
                adminTabJobsShow: this.loadJobsGrid,
                adminTabExportJobsShow: this.loadExportJobs,
                adminTabTagManagerShow: this.loadTagManagerGrid,
                adminTabUserManagementShow: this.loadUserManagement,
                adminTabGroupManagementShow: this.loadGroupManagement,
                adminTabListAllPlugins: this.loadAllPluginList,
                adminTabCurrencyFieldsShow: this.loadCurrencyFields
            },
            'ifrescoViewWindowTemplateDesigner': {
                addTemplate: this.showAddTemplateByTypeId
            },
            'ifrescoFormTemplate': {
                save: this.saveTemplate
            },
            'ifrescoViewWindowTemplateProperties': {
                addProperties: this.addTemplateProperties
            },
            'ifrescoViewLookups': {
                save: this.saveLookups
            },
            'ifrescoViewPanelManageTemplate': {
                saveTemplate: this.saveTemplate
            },
            'ifrescoViewGridTemplate': {
                editTemplate: this.editTemplate
            },
            'ifrescoViewWindowDataSourceDesigner': {
                saveDataSource: this.saveDataSource
            },
            'ifrescoViewQuickSearch': {
                save: this.saveQuickSearch
            },
            'ifrescoViewClickSearch': {
                save: this.saveClickSearch
            },
            'ifrescoViewGridSearchTemplate': {
                edit: this.editSearchTemplate,
                setDefault: this.setDefaultSearchTemplate
            },
            'ifrescoFormSearchTemplate': {
                save: this.saveSearchTemplate
            },
            'ifrescoViewGridColumnSets': {
                edit: this.editColumnSet,
                setDefault: this.setDefaultColumnSet
            },
            'ifrescoFormColumnSet': {
                save: this.saveColumnSet
            },
            'ifrescoViewSettingsEmail': {
                save: this.saveEmailSettings
            },
            'ifrescoViewSettingsInterface': {
                save: this.saveInterfaceSettings
            },
            'ifrescoViewSettingsSystem': {
                save: this.saveSystemSettings
            },
            'ifrescoViewSettingsAspects': {
                save: this.saveAspectsSettings
            },
            'ifrescoViewSettingsContentTypes': {
                save: this.saveContentTypesSettings
            },
            'ifrescoViewSettingsUploadAllowedTypes': {
                save: this.saveUploadAllowedTypesSettings
            },
            'ifrescoViewSettingsNamespaceMapping': {
                create: this.createNamespaceMapping,
                save: this.saveNamespaceMapping
            },
            'ifrescoViewSettingsPropertyFilter': {
                save: this.savePropertyFilterSettings
            },
            'ifrescoViewSettingsOnlineEditing': {
                save: this.saveOnlineEditingSettings
            },
            'ifrescoViewSettingsTreeRootFolder': {
                save: this.saveTreeRootFolderSettings
            },
            'ifrescoViewSettingsDropboxConfig': {
                save: this.saveDropboxConfigSettings
            },
            'ifrescoViewExport': {
                fetch: this.fetchExportSettings
            },
            'ifrescoViewImport': {
                save: this.saveImportSettings
            },
            'ifrescoViewExportJobs': {
                'export': this.exportJobs
            },
            'ifrescoViewSettingsAutoOCRConfig': {
            	testconnection: this.testAutoOCRConnection,
            	testapikey: this.testAutoOCRAPIKey,
            	save: this.saveAutoOCRConfigSettings
            },
            'ifrescoViewSettingsAutoOCRStatus': {
            	save: this.saveAutoOCRStatusSettings
            },
            'ifrescoViewSettingsAutoOCRTransformers': {
            	save: this.saveAutoOCRTransformerSettings
            },
            'ifrescoViewSettingsAutoOCRRuntimeTransformers': {
            	save: this.saveAutoOCRRuntimeTransformerSettings
            },
            'ifrescoViewCurrencyFields': {
                save: this.saveCurrencyFields
            }
        });
    },
    listeners: {
    	afterSave: function() {
        	console.log("AFTER SAVE ADMIN");
        	var task = new Ext.util.DelayedTask(function(){
        		Ext.Ajax.request({
                    url: Routing.generate('ifresco_client_get_ifresco_settings'),
                    disableCaching: true,
                    success: function (response) {
                        var data = Ext.decode(response.responseText);
                        console.log("SETTINGS ARE BEFORE",ifrescoSettings)
                        ifrescoSettings = Ext.decode(data.settings);
                        //Ifresco.helper.Translations.translations = ifrescoSettings.translations;
                        Ifresco.helper.Settings.settings = ifrescoSettings.settings;
                        console.log("SETTINGS ARE AFTER",ifrescoSettings)
                    }
                });
        	},this);
        	task.delay(500);
        }
    },
    
    loadUserManagement: function () {
    	this.getAdminTabContentPanel().removeAll();
        this.getAdminTabContentPanel().add(Ifresco.view.UserManagement.create({}));
    },
    
    loadGroupManagement: function () {
    	this.getAdminTabContentPanel().removeAll();
    	this.getAdminTabContentPanel().add(Ifresco.view.GroupManagement.create({}));
    },

    loadTemplatesGrid: function () {
        if (!this.getAdminTemplate()) {
            this.getAdminTabContentPanel().removeAll();
            this.getAdminTabContentPanel().add(Ifresco.view.grid.Template.create({}));
        } else {
            this.getAdminTemplate().getStore().reload();
        }
    },

    loadLookupsShow: function () {
        this.getAdminTabContentPanel().removeAll();
        var adminTabPanel =  this.getAdminTabContentPanel();
        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_admin_lookups_get'),
            loadMask: true,
            disableCaching: true,
            success: function (response) {
                adminTabPanel.add(Ifresco.view.Lookups.create({
                    configData: Ext.decode(response.responseText)
                }));
            }
        });
        this.getAdminTabContentPanel().add();
    },

    saveLookups: function() {
        var panel = this.getLookupsPanel();
        var data = {};
        var notValid = false;
        panel.items.each(function (lookup) {
            if (! lookup.isValid()) {
                notValid = true;
                return;
            }
            var lookupId = lookup.lookupId;
            var values = lookup.getValues();
            Ext.iterate(values, function(key, value) {
                if (key.indexOf(lookupId) !== -1) {
                    data[key] = value;
                } else {
                    if(Object.prototype.toString.call(data[key]) === '[object Array]') {
                        data[key].push(value);
                    } else {
                        data[key] = [value];
                    }
                }
            });
        });
        if (notValid) {
            Ext.ux.ErrorMessage.show({
                title: Ifresco.helper.Translations.trans('Lookups'),
                msg: Ifresco.helper.Translations.trans('Save failed, there are some erros in form!')
            });
            return;
        }
        Ext.Ajax.request({
            method: 'POST',
            url: Routing.generate('ifresco_client_admin_lookups_save'),
            params: {
                data: Ext.encode(data)
            },
            success: function(res) {
                var resData = Ext.decode(res.responseText);
                Ext.ux.StatusMessage.show({
                    title: Ifresco.helper.Translations.trans('Lookups'),
                    successMsg: Ifresco.helper.Translations.trans('Successfully saved the lookups!'),
                    errorMsg: Ifresco.helper.Translations.trans('Save failed, please try later'),
                    success: resData.success
                });
            },
            failure: function() {
                Ext.ux.ErrorMessage.show({
                    title: Ifresco.helper.Translations.trans('Lookups'),
                    msg: Ifresco.helper.Translations.trans('Save failed, please try later')
                });
            }
        });
        this.fireEvent("afterSave");
    },

    showAddTemplateByTypeId: function (selectedTemplateTypeId, templateId) {
        var tabContentPanel = this.getAdminTabContentPanel();
        var controller = this;
        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_admin_templates_designer'),
            loadMask: true,
            disableCaching: true,
            params: {
                'class': selectedTemplateTypeId,
                id: templateId
            },
            success: function (response) {
                var newPanel = Ifresco.form.Template.create({
                    configData: Ext.decode(response.responseText),
                    selectedTemplateTypeId: selectedTemplateTypeId
                });

                tabContentPanel.removeAll();
                tabContentPanel.add(newPanel);
                controller.loadPropertiesToPanel(newPanel);
            }
        });

    },

    loadPropertiesToPanel: function(panel) {
        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_admin_template_properties'),
            disableCaching: true,
            method: 'GET',
            success: function(response) {
                var data = Ext.decode(response.responseText);
                var properties = [];
                Ext.each(data.properties, function(property) {
                    var value = property.attributes.value.split('/');
                    properties.push({
                        text: property.text,
                        name: value[0],
                        'class': value[1],
                        title: value[2],
                        dataType: value[3],
                        type: 'property'
                    });
                });
                panel.loadProperties(properties);
            }
        });
    },

    saveTemplate: function (selectedTemplateTypeId, configData) {
        var formTemplate = this.getAdminTabContentPanel().down('ifrescoFormTemplate');
        var values = formTemplate.getForm().getValues();
        var data = {
            edit: configData.Id || null,
            'class': configData.Class || selectedTemplateTypeId.replace(':', '_'),
            aspectsView: values.aspectsView,
            multiColumns: values.multiColumns ? true : false,
            col1: [],
            col2: [],
            tabs: [],
            readonlyVals: [],
            requiredVals: []
        };

        this.assignTemplateProperties(
            formTemplate.down('propertyselector[name=column1]').getStore(),
            data.col1,
            data.requiredVals,
            data.readonlyVals
        );

        this.assignTemplateProperties(
            formTemplate.down('propertyselector[name=column2]').getStore(),
            data.col2,
            data.requiredVals,
            data.readonlyVals
        );

        formTemplate.down('tabpanel[name=tabs]').items.each(function (tab) {
            var tempTab = {
                title: tab.tabConfig.title,
                items: []
            };

            this.assignTemplateProperties(
                tab.getStore(),
                tempTab.items,
                data.requiredVals,
                data.readonlyVals
            );

            data.tabs.push(tempTab);

        }, this);

        var controller = this;
        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_admin_template_properties_save'),
            loadMask: true,
            disableCaching: true,
            params: {
                data: Ext.encode(data)
            },
            success: function(res) {
                var resData = Ext.decode(res.responseText);
                Ext.ux.StatusMessage.show({
                    title: Ifresco.helper.Translations.trans('Templates'),
                    successMsg: Ifresco.helper.Translations.trans('Your template was saved successfully!'),
                    errorMsg: Ifresco.helper.Translations.trans('Save failed, please try later'),
                    success: resData.success
                });
                if (resData.success) {
                    controller.loadTemplatesGrid();
                }
            },
            failure: function() {
                Ext.ux.ErrorMessage.show({
                    title: Ifresco.helper.Translations.trans('Templates'),
                    msg: Ifresco.helper.Translations.trans('Save failed, please try later')
                });
            }
        }, this);
        this.fireEvent("afterSave");
    },

    assignTemplateProperties: function(fromStore, toArray, toRequiredArray, toReadonlyArray) {
        fromStore.each(function (property) {
            toArray.push([
                property.get('name'),
                property.get('class'),
                property.get('title'),
                property.get('dataType'),
                property.get('type')
            ].join('/'));

            if (property.get('required')) {
                toRequiredArray.push(property.get('name'));
            }
            if (property.get('readonly')) {
                toReadonlyArray.push(property.get('name'));
            }
        });
    },

    editTemplate: function(templateId) {
        this.showAddTemplateByTypeId(null, templateId);
    },

    loadDataSourceGrid: function() {
        this.getAdminTabContentPanel().removeAll();
        this.getAdminTabContentPanel().add(Ifresco.view.grid.DataSource.create({}));
    },

    saveDataSource: function (window) {
        var controller = this;
        window.down('form').getForm().submit({
            success: function() {
                Ext.Msg.alert(
                    Ifresco.helper.Translations.trans('Success'),
                    Ifresco.helper.Translations.trans('Data source successfully saved!')
                );

                controller.getAdminDataSourceGrid().getStore().reload();
                window.close();
            }
        });
        this.fireEvent("afterSave");
    },

    loadSystemSettings: function() {
        var panel = Ifresco.view.settings.System.create({});

        this.getAdminTabContentPanel().removeAll();
        this.getAdminTabContentPanel().add(panel);
        panel.setLoading(true, true);
        var form = panel.getForm();

        Ext.Ajax.request({
            method: 'GET',
            url: Routing.generate('ifresco_client_admin_system_settings_get'),
            success: function(response){
                var data = Ext.decode(response.responseText);
                form.setValues(data.Settings);
                panel.setLoading(false);
            },
            scope: this
        });
    },

    saveSystemSettings: function() {
        var panel = this.getSystemSettingsPanel(),
            form = panel.getForm();
        Ext.Ajax.request({
            method: 'POST',
            url: Routing.generate('ifresco_client_admin_system_settings_save'),
            params: {
                data: Ext.encode(form.getValues())
            },
            success: function(res) {
                var resData = Ext.decode(res.responseText);
                Ext.ux.StatusMessage.show({
                    title: Ifresco.helper.Translations.trans('System Settings'),
                    successMsg: Ifresco.helper.Translations.trans('Successfully saved the system settings!'),
                    errorMsg: Ifresco.helper.Translations.trans('Save failed, please try later'),
                    success: resData.success
                });
            },
            failure: function() {
                Ext.ux.ErrorMessage.show({
                    title: Ifresco.helper.Translations.trans('System Settings'),
                    msg: Ifresco.helper.Translations.trans('Save failed, please try later')
                });
            }
        });
        this.fireEvent("afterSave");
    },

    loadEmailSettings: function () {
        var panel = Ifresco.view.settings.Email.create({});
        this.getAdminTabContentPanel().removeAll();
        this.getAdminTabContentPanel().add(panel);
        panel.setLoading(true, true);

        Ext.Ajax.request({
            method: 'GET',
            url: Routing.generate('ifresco_client_admin_email_settings_get'),
            success: function(response){
                var data = Ext.decode(response.responseText);
                Ext.iterate(data.data, function(key, value) {
                    panel.createField(value.name, key, value.value, value.type, value.checked);
                });
                panel.setLoading(false);
            },
            scope: this
        });
    },

    saveEmailSettings: function() {
        var panel = this.getEmailSettingsPanel();
        var form = panel.getForm();
        Ext.Ajax.request({
            method: 'POST',
            url: Routing.generate('ifresco_client_admin_email_settings_save'),
            params: {
                data: Ext.encode(form.getValues())
            },
            success: function(res) {
                var resData = Ext.decode(res.responseText);
                Ext.ux.StatusMessage.show({
                    title: Ifresco.helper.Translations.trans('Email Settings'),
                    successMsg: Ifresco.helper.Translations.trans('Successfully saved the Email settings!'),
                    errorMsg: Ifresco.helper.Translations.trans('Save failed, please try later'),
                    success: resData.success
                });
            },
            failure: function() {
                Ext.ux.ErrorMessage.show({
                    title: Ifresco.helper.Translations.trans('Email Settings'),
                    msg: Ifresco.helper.Translations.trans('Save failed, please try later')
                });
            }
        });
        this.fireEvent("afterSave");
    },

    loadAspectsSettings: function() {
        var aspects = [];
        var data = [];
        var selectedAspects = [];
        var panel = Ifresco.view.settings.Aspects.create({});
        var itemSelector = panel.down('itemselector');
        this.getAdminTabContentPanel().removeAll();
        this.getAdminTabContentPanel().add(panel);
        panel.setLoading(true, true);

        Ext.Ajax.request({
            method: 'GET',
            url: Routing.generate('ifresco_client_admin_aspect_list_get'),
            success: function(response) {
                data = Ext.decode(response.responseText);
                Ext.each(data.data, function(item) {
                    aspects.push({
                        text: item.text,
                        id: item.attributes.id,
                        value: item.attributes.value
                    });
                    if (item.state == 'selected') {
                        selectedAspects.push(item.attributes.value);
                    }
                });
                itemSelector.getStore().loadData(aspects);
                itemSelector.bindStore(itemSelector.getStore());
                itemSelector.setValue(selectedAspects);
                panel.setLoading(false);
            }
        });
    },

    saveAspectsSettings: function() {
        var panel = this.getAspectsSettingsPanel();
        var store = panel.down('itemselector').toField.getStore();
        var data = {allowedAspects: [[]]};
        store.each(function(item) {
            data.allowedAspects[0].push(item.data.value);
        });
        Ext.Ajax.request({
            method: 'POST',
            url: Routing.generate('ifresco_client_admin_aspect_list_save'),
            params: {
                data: Ext.encode(data)
            },
            success: function(res) {
                var resData = Ext.decode(res.responseText);
                Ext.ux.StatusMessage.show({
                    title: Ifresco.helper.Translations.trans('Aspects'),
                    successMsg: Ifresco.helper.Translations.trans('Successfully saved the Aspects settings!'),
                    errorMsg: Ifresco.helper.Translations.trans('Save failed, please try later'),
                    success: resData.success
                });
            },
            failure: function() {
                Ext.ux.ErrorMessage.show({
                    title: Ifresco.helper.Translations.trans('Aspects'),
                    msg: Ifresco.helper.Translations.trans('Save failed, please try later')
                });
            }
        });
        this.fireEvent("afterSave");
    },

    loadContentTypesSettings: function() {
        var types = [];
        var data = [];
        var selectedTypes = [];
        var panel = Ifresco.view.settings.ContentTypes.create({});
        var itemSelector = panel.down('itemselector');
        this.getAdminTabContentPanel().removeAll();
        this.getAdminTabContentPanel().add(panel);
        panel.setLoading(true);

        Ext.Ajax.request({
            method: 'GET',
            url: Routing.generate('ifresco_client_admin_type_list_get'),
            success: function(response) {
                data = Ext.decode(response.responseText);
                Ext.each(data.data, function(item) {
                    types.push({
                        text: item.text,
                        id: item.attributes.id,
                        value: item.attributes.value
                    });
                    if (item.state == 'selected') {
                        selectedTypes.push(item.attributes.value);
                    }
                });
                itemSelector.getStore().loadData(types);
                itemSelector.bindStore(itemSelector.getStore());
                itemSelector.setValue(selectedTypes);
                panel.setLoading(false);
            }
        });
    },

    saveContentTypesSettings: function() {
        var panel = this.getContentTypesSettingsPanel();
        var store = panel.down('itemselector').toField.getStore();
        var data = {allowedTypes: [[]]};
        store.each(function(item) {
            data.allowedTypes[0].push(item.data.value);
        });
        Ext.Ajax.request({
            method: 'POST',
            url: Routing.generate('ifresco_client_admin_type_list_save'),
            params: {
                data: Ext.encode(data)
            },
            success: function(res) {
                var resData = Ext.decode(res.responseText);
                Ext.ux.StatusMessage.show({
                    title: Ifresco.helper.Translations.trans('Content Types'),
                    successMsg: Ifresco.helper.Translations.trans('Selected content types saved successfully'),
                    errorMsg: Ifresco.helper.Translations.trans('Save failed, please try later'),
                    success: resData.success
                });
            },
            failure: function() {
                Ext.ux.ErrorMessage.show({
                    title: Ifresco.helper.Translations.trans('Content Types'),
                    msg: Ifresco.helper.Translations.trans('Save failed, please try later')
                });
            }
        });
        this.fireEvent("afterSave");
    },

    loadUploadAllowedTypesSettings: function() {
        var types = [];
        var data;
        var selected = [];
        var panel = Ifresco.view.settings.UploadAllowedTypes.create({});
        var itemSelector = panel.down('itemselector');
        this.getAdminTabContentPanel().removeAll();
        this.getAdminTabContentPanel().add(panel);
        panel.setLoading(true, true);

        Ext.Ajax.request({
            method: 'GET',
            url: Routing.generate('ifresco_client_admin_upload_allowed_types'),
            success: function(response) {
                data = Ext.decode(response.responseText);
                selected = data.selected;
                Ext.each(data.types, function(type) {
                    types.push({value: type});
                });

                itemSelector.getStore().loadData(types);
                itemSelector.bindStore(itemSelector.getStore());
                itemSelector.setValue(selected);
                panel.setLoading(false);
            }
        });
    },

    saveUploadAllowedTypesSettings: function() {
        var panel = this.getUploadAllowedTypesSettingsPanel();
        var store = panel.down('itemselector').toField.getStore();
        var data = {uploadAllowedTypes: []};
        store.each(function(item) {
            data.uploadAllowedTypes.push(item.data.value);
        });
        Ext.Ajax.request({
            method: 'POST',
            url: Routing.generate('ifresco_client_admin_system_settings_save'),
            params: {
                data: Ext.encode(data)
            },
            success: function(res) {
                var resData = Ext.decode(res.responseText);
                Ext.ux.StatusMessage.show({
                    title: Ifresco.helper.Translations.trans('Upload Allowed Types'),
                    successMsg: Ifresco.helper.Translations.trans('Successfully saved upload allowed types!'),
                    errorMsg: Ifresco.helper.Translations.trans('Save failed, please try later'),
                    success: resData.success
                });
            },
            failure: function() {
                Ext.ux.ErrorMessage.show({
                    title: Ifresco.helper.Translations.trans('Upload Allowed Types'),
                    msg: Ifresco.helper.Translations.trans('Save failed, please try later')
                });
            }
        });
        this.fireEvent("afterSave");
    },


    loadInterfaceSettings: function() {
        this.getAdminTabContentPanel().removeAll();
        this.getAdminTabContentPanel().add(Ifresco.view.settings.Interface.create({}));
        var panel = this.getInterfaceSettingsPanel();
        panel.setLoading(true, true);
        Ext.Ajax.request({
            method: 'GET',
            url: Routing.generate('ifresco_client_admin_interface_get'),
            success: function(response){
                var data = Ext.decode(response.responseText);
                panel.getForm().setValues(data.data);
                panel.setLoading(false);
            },
            scope: this
        });
    },

    saveInterfaceSettings: function() {
        var panel = this.getInterfaceSettingsPanel();
        Ext.Ajax.request({
            method: 'POST',
            url: Routing.generate('ifresco_client_admin_system_settings_save'),
            params: {
                data: Ext.encode(panel.getForm().getValues())
            },
            success: function(res) {
                var resData = Ext.decode(res.responseText);
                Ext.ux.StatusMessage.show({
                    title: Ifresco.helper.Translations.trans('Interface'),
                    successMsg: Ifresco.helper.Translations.trans('Successfully saved the Interface settings!'),
                    errorMsg: Ifresco.helper.Translations.trans('Save failed, please try later'),
                    success: resData.success
                });
            },
            failure: function() {
                Ext.ux.ErrorMessage.show({
                    title: Ifresco.helper.Translations.trans('Interface'),
                    msg: Ifresco.helper.Translations.trans('Save failed, please try later')
                });
            }
        });
        this.fireEvent("afterSave");
    },

    loadNamespaceMappingSettings: function() {
        this.getAdminTabContentPanel().removeAll();
        this.getAdminTabContentPanel().add(Ifresco.view.settings.NamespaceMapping.create({}));
    },

    createNamespaceMapping: function() {
        var panel = this.getNamespaceMappingSettingsPanel();
        panel.getStore().insert(0, Ifresco.model.NamespaceMapping.create({}));
        panel.getPlugin('RowEditing').startEdit(0, 0);
    },

    saveNamespaceMapping: function() {
        var panel = this.getNamespaceMappingSettingsPanel(),
            store = panel.getStore(),
            modified = store.getModifiedRecords(),
            data = [];
        Ext.each(modified, function(record){
            data.push(record.getData());
        });
        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_admin_namespace_mapping_save'),
            disableCaching: true,
            method: 'POST',
            params: {
                data: Ext.encode(data)
            },
            success: function(res) {
                var resData = Ext.decode(res.responseText);
                store.commitChanges();
                Ext.ux.StatusMessage.show({
                    title: Ifresco.helper.Translations.trans('Namespace Mapping'),
                    successMsg: Ifresco.helper.Translations.trans('Successfully saved the namespace mapping!'),
                    errorMsg: Ifresco.helper.Translations.trans('Save failed, please try later'),
                    success: resData.success
                });
            },
            failure: function() {
                Ext.ux.ErrorMessage.show({
                    title: Ifresco.helper.Translations.trans('Namespace Mapping'),
                    msg: Ifresco.helper.Translations.trans('Save failed, please try later')
                });
            }
        });
        this.fireEvent("afterSave");
    },

    loadPropertyFilterSettings: function() {
        var checkboxes;
        var panel = Ifresco.view.settings.PropertyFilter.create({});
        this.getAdminTabContentPanel().removeAll();
        this.getAdminTabContentPanel().add(panel);
        panel.setLoading(true, true);
        checkboxes = panel.down('container[cls~=ifresco-view-prop-items]');
        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_admin_filter_properties_get'),
            disableCaching: true,
            method: 'GET',
            success: function(response) {
                var data = Ext.decode(response.responseText);

                Ext.iterate(data.properties.prefArr, function(key, property) {
                    checkboxes.add({
                        inputValue: property,
                        boxLabel: property,
                        checked: Ext.Array.contains(data.properties.allowed, property)
                    });
                });
                panel.setLoading(false);
            }
        });
    },

    savePropertyFilterSettings: function() {
        var panel = this.getPropertyFilterSettingsPanel();
        var data = panel.getForm().getValues();
        data.prefs = [].concat(data.prefs);
        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_admin_filter_properties_save'),
            disableCaching: true,
            method: 'POST',
            params: {
                data: Ext.encode(data)
            },
            success: function(res) {
                var resData = Ext.decode(res.responseText);
                Ext.ux.StatusMessage.show({
                    title: Ifresco.helper.Translations.trans('Property Filter'),
                    successMsg: Ifresco.helper.Translations.trans('Successfully saved the Property Filter settings!'),
                    errorMsg: Ifresco.helper.Translations.trans('Save failed, please try later'),
                    success: resData.success
                });
            },
            failure: function() {
                Ext.ux.ErrorMessage.show({
                    title: Ifresco.helper.Translations.trans('Property Filter'),
                    msg: Ifresco.helper.Translations.trans('Save failed, please try later')
                });
            }
        });
        this.fireEvent("afterSave");
    },

    loadOnlineEditingSettings: function() {
        var panel = Ifresco.view.settings.OnlineEditing.create({});
        this.getAdminTabContentPanel().removeAll();
        this.getAdminTabContentPanel().add(panel);
        panel.setLoading(true, true);

        var form = panel.getForm();
        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_admin_online_editing_get'),
            disableCaching: true,
            method: 'GET',
            success: function(response) {
                form.setValues(Ext.decode(response.responseText).data);
                panel.setLoading(false);
            }
        });
    },

    saveOnlineEditingSettings: function() {
        var panel = this.getOnlineEditingSettingsPanel(),
            data = panel.getForm().getValues();
        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_admin_online_editing_save'),
            disableCaching: true,
            method: 'POST',
            params: {
                data: Ext.encode(data)
            },
            success: function(res) {
                var resData = Ext.decode(res.responseText);
                Ext.ux.StatusMessage.show({
                    title: Ifresco.helper.Translations.trans('Online Editing'),
                    successMsg: Ifresco.helper.Translations.trans('Successfully saved the Online Editing settings!'),
                    errorMsg: Ifresco.helper.Translations.trans('Save failed, please try later'),
                    success: resData.success
                });
            },
            failure: function() {
                Ext.ux.ErrorMessage.show({
                    title: Ifresco.helper.Translations.trans('Online Editing'),
                    msg: Ifresco.helper.Translations.trans('Save failed, please try later')
                });
            }
        });
        this.fireEvent("afterSave");
    },

    loadTreeRootFolderSettings: function() {
        var panel = Ifresco.view.settings.TreeRootFolder.create({});
        this.getAdminTabContentPanel().removeAll();
        this.getAdminTabContentPanel().add(panel);
        panel.setLoading(true, true);
        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_admin_tree_root_folder_get'),
            disableCaching: true,
            success: function(res) {
                var resData = Ext.decode(res.responseText);
                panel.loadTreeData(resData.data);
                panel.setLoading(false);
            }
        });
    },

    saveTreeRootFolderSettings: function() {
        var panel = this.getTreeRootFolderSettingsPanel();
        var data = panel.getForm().getValues();
        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_admin_system_settings_save'),
            disableCaching: true,
            params: {
                data: Ext.encode(data)
            },
            success: function(res) {
                var resData = Ext.decode(res.responseText);
                Ext.ux.StatusMessage.show({
                    title: Ifresco.helper.Translations.trans('Tree Root Folder'),
                    successMsg: Ifresco.helper.Translations.trans('Successfully saved the Tree Root Folder settings!'),
                    errorMsg: Ifresco.helper.Translations.trans('Save failed, please try later'),
                    success: resData.success
                });
            },
            failure: function() {
                Ext.ux.ErrorMessage.show({
                    title: Ifresco.helper.Translations.trans('Tree Root Folder'),
                    msg: Ifresco.helper.Translations.trans('Save failed, please try later')
                });
            }
        });
        this.fireEvent("afterSave");
    },

    loadDropboxConfigSettings: function() {
        var panel = Ifresco.view.settings.DropboxConfig.create({});
        this.getAdminTabContentPanel().removeAll();
        this.getAdminTabContentPanel().add(panel);
        panel.setLoading(true, true);

        var form = panel.getForm();
        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_admin_dropbox_get'),
            disableCaching: true,
            method: 'GET',
            success: function(response) {
                form.setValues(Ext.decode(response.responseText).data);
                panel.setLoading(false);
            }
        });
    },
    
    
    loadAutoOCRTransformerSettings: function() {
    	this.getAdminTabContentPanel().removeAll();
        this.getAdminTabContentPanel().add(Ifresco.view.settings.AutoOCRTransformers.create({}));
    },
    
    loadAutoOCRRuntimeTransformerSettings: function() {
    	this.getAdminTabContentPanel().removeAll();
        this.getAdminTabContentPanel().add(Ifresco.view.settings.AutoOCRRuntimeTransformers.create({}));
    },
    
    loadAutoOCRJobsSettings: function() {
    	this.getAdminTabContentPanel().removeAll();
        this.getAdminTabContentPanel().add(Ifresco.view.settings.AutoOCRJobs.create({}));
    },
    
    loadAutoOCRStatusSettings: function() {
        var panel = Ifresco.view.settings.AutoOCRStatus.create({});
        this.getAdminTabContentPanel().removeAll();
        this.getAdminTabContentPanel().add(panel);
        panel.setLoading(true, true);

        var form = panel.getForm();
        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_admin_ocr_status'),
            disableCaching: true,
            method: 'GET',
            success: function(response) {
            	var resp = Ext.decode(response.responseText);
                form.setValues(resp.data);
                if (resp.status.isServerRunning)
                	panel.down("#server").update(Ifresco.helper.Translations.trans('Running'))
                else
                	panel.down("#server").update(Ifresco.helper.Translations.trans('Not Running'));
                
                panel.down("#version").update(resp.status.serverVersion);
                panel.down("#pages").update(resp.status.availablePages);
                
                panel.setLoading(false);
            }
        });
    },
    
    loadAutoOCRConfigSettings: function() {
        var panel = Ifresco.view.settings.AutoOCRConfig.create({});
        this.getAdminTabContentPanel().removeAll();
        this.getAdminTabContentPanel().add(panel);
        panel.setLoading(true, true);

        var form = panel.getForm();
        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_admin_ocr_connection'),
            disableCaching: true,
            method: 'GET',
            success: function(response) {
                form.setValues(Ext.decode(response.responseText).data);
                panel.setLoading(false);
            }
        });
    },
    
    saveAutoOCRTransformerSettings: function() {
    	var grid = this.getAutoOCRTransformersGrid(),
    		items = grid.getStore().data.items,
    		transformations = [];
    	
    	grid.getStore().each(function(row) {
            transformations.push(row.data);
        });

	    this.saveAutoOCRSettings(grid,
	    		{transformations: transformations},
	    		Ifresco.helper.Translations.trans('AutoOCR transformer'),
	    		Ifresco.helper.Translations.trans('Successfully saved the AutoOCR transformer!'));
	    
    },
    
    saveAutoOCRRuntimeTransformerSettings: function() {
    	var grid = this.getAutoOCRRuntimeTransformersGrid(),
    		items = grid.getStore().data.items,
    		transformations = [];
    	
    	grid.getStore().each(function(row) {
            transformations.push(row.data);
        });

	    this.saveAutoOCRSettings(grid,
	    		{runtimeTransformations: transformations},
	    		Ifresco.helper.Translations.trans('AutoOCR Runtime transformer'),
	    		Ifresco.helper.Translations.trans('Successfully saved the AutoOCR Runtime transformer!'));
    },
    
    saveAutoOCRStatusSettings: function() {
        var panel = this.getAutoOCRStatusPanel(),
            data = panel.getForm().getValues();
        
        if (panel.down("checkboxfield[name~=enabled]").checked == false) {
    	   data["enabled"] = "false";
    	}
        
        this.saveAutoOCRSettings(panel,
        		data,
        		Ifresco.helper.Translations.trans('AutoOCR Settings'),
        		Ifresco.helper.Translations.trans('Successfully saved the AutoOCR settings!'));
    },
    
    saveAutoOCRConfigSettings: function() {
        var panel = this.getAutoOCRConfigPanel(),
            data = panel.getForm().getValues();
        
        this.saveAutoOCRSettings(panel,
        		data,
        		Ifresco.helper.Translations.trans('AutoOCR Connection Settings'),
        		Ifresco.helper.Translations.trans('Successfully saved the AutoOCR settings!'));
    },
    
    saveAutoOCRSettings: function(panel,data,title,msg) {
        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_admin_ocr_settings_save'),
            disableCaching: true,
            method: 'POST',
            params: {
                data: Ext.encode(data)
            },
            success: function(res) {
                var resData = Ext.decode(res.responseText);
                Ext.ux.StatusMessage.show({
                    title: title,
                    successMsg: msg,
                    errorMsg: Ifresco.helper.Translations.trans('Save failed, please try later'),
                    success: resData.success
                });
            },
            failure: function() {
                Ext.ux.ErrorMessage.show({
                    title: Ifresco.helper.Translations.trans('AutoOCR Connection Settings'),
                    msg: Ifresco.helper.Translations.trans('Save failed, please try later')
                });
            }
        });
        this.fireEvent("afterSave");
    },
    
    testAutoOCRConnection: function() {
    	var panel = this.getAutoOCRConfigPanel(),
    		data = panel.getForm().getValues();

    	Ext.Ajax.request({
            url: Routing.generate('ifresco_client_admin_test_ocrconnection'),
            disableCaching: true,
            method: 'POST',
            params: {
                data: Ext.encode(data)
            },
            success: function(res) {
                var resData = Ext.decode(res.responseText);
                Ext.Msg.show({
                    title: Ifresco.helper.Translations.trans('AutoOCR Test Connection'),
                    buttons: Ext.Msg.OK,
                    msg: resData.message,
                    icon: Ext.Msg.QUESTION
                });
            },
            failure: function() {
                Ext.ux.ErrorMessage.show({
                    title: Ifresco.helper.Translations.trans('AutoOCR Test Connection'),
                    msg: Ifresco.helper.Translations.trans('Testing failed - no connection to the Transformer, please try later')
                });
            }
        });
    },
    
    testAutoOCRAPIKey: function() {
    	var panel = this.getAutoOCRConfigPanel(),
    		data = panel.getForm().getValues();

    	Ext.Ajax.request({
            url: Routing.generate('ifresco_client_admin_test_ocrapikey'),
            disableCaching: true,
            method: 'POST',
            params: {
                data: Ext.encode(data)
            },
            success: function(res) {
                var resData = Ext.decode(res.responseText);
                Ext.ux.StatusMessage.show({
                    title: Ifresco.helper.Translations.trans('AutoOCR Test API Key'),
                    successMsg: resData.message,
                    errorMsg: resData.message,
                    success: resData.isKeyValid
                });
            },
            failure: function() {
                Ext.ux.ErrorMessage.show({
                    title: Ifresco.helper.Translations.trans('AutoOCR Test API Key'),
                    msg: Ifresco.helper.Translations.trans('Testing failed - no connection to the Transformer, please try later')
                });
            }
        });
    },
    
    

    saveDropboxConfigSettings: function() {
        var panel = this.getDropboxConfigSettingsPanel(),
            data = panel.getForm().getValues();
        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_admin_system_settings_save'),
            disableCaching: true,
            method: 'POST',
            params: {
                data: Ext.encode(data)
            },
            success: function(res) {
                var resData = Ext.decode(res.responseText);
                Ext.ux.StatusMessage.show({
                    title: Ifresco.helper.Translations.trans('Dropbox Settings'),
                    successMsg: Ifresco.helper.Translations.trans('Successfully saved the Dropbox settings!'),
                    errorMsg: Ifresco.helper.Translations.trans('Save failed, please try later'),
                    success: resData.success
                });
            },
            failure: function() {
                Ext.ux.ErrorMessage.show({
                    title: Ifresco.helper.Translations.trans('Dropbox Settings'),
                    msg: Ifresco.helper.Translations.trans('Save failed, please try later')
                });
            }
        });
        this.fireEvent("afterSave");
    },

    loadExport: function() {
        this.getAdminTabContentPanel().removeAll();
        this.getAdminTabContentPanel().add(Ifresco.view.Export.create({}));
    },

    fetchExportSettings: function() {
        var form = this.getExportPanel().getForm(),
            params = form.getValues();
        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_admin_settings_export'),
            disableCaching: true,
            method: 'GET',
            params: {
                data: Ext.encode(params)
            },
            success: function(response){
                var data = Ext.decode(response.responseText);
                form.findField('target_export').setValue(data.data);
            }
        });
    },

    loadImport: function() {
        this.getAdminTabContentPanel().removeAll();
        this.getAdminTabContentPanel().add(Ifresco.view.Import.create({}));
    },

    saveImportSettings: function() {
        var form = this.getImportPanel().getForm(),
            data = form.findField('target_import').getValue();
        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_admin_settings_import'),
            disableCaching: true,
            method: 'POST',
            params: {
                data: data
            },
            success: function(res) {
                var resData = Ext.decode(res.responseText);
                Ext.ux.StatusMessage.show({
                    title: Ifresco.helper.Translations.trans('Import Settings'),
                    successMsg: Ifresco.helper.Translations.trans('Successfully saved the Import Settings!'),
                    errorMsg: Ifresco.helper.Translations.trans('Save failed, please try later'),
                    success: resData.success
                });
            },
            failure: function() {
                Ext.ux.ErrorMessage.show({
                    title: Ifresco.helper.Translations.trans('Import Settings'),
                    msg: Ifresco.helper.Translations.trans('Save failed, please try later')
                });
            }
        });
        this.fireEvent("afterSave");
    },

    loadQuickSearch: function() {
        var panel = Ifresco.view.QuickSearch.create({
            configData: {properties: []}
        });
        this.loadPropertiesToPanel(panel);
        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_admin_quick_search_get'),
            method: 'GET',
            success: function(response) {
                var data = Ext.decode(response.responseText);
                panel.loadData(data.data);
            }
        });
        this.getAdminTabContentPanel().removeAll();
        this.getAdminTabContentPanel().add(panel);
    },

    saveQuickSearch: function() {
        var panel = this.getQuickSearchPanel();
        var store = panel.down('propertyselector').getStore();
        var fields = [];
        store.each(function(field) {
            var data = field.data;
            fields.push(
                [
                    data.name,
                    data['class'],
                    data.title,
                    data.dataType,
                    data.type
                ].join('/')
            );
        });
        var luceneQuery = panel.down('textfield[name=lucene_query]').getValue();
        var data = {
            fields: fields,
            lucene_query: luceneQuery
        };
        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_admin_quick_search_save'),
            method: 'POST',
            params: {
                data: Ext.encode(data)
            },
            success: function (res) {
                var resData = Ext.decode(res.responseText);
                Ext.ux.StatusMessage.show({
                    title: Ifresco.helper.Translations.trans('Quick Search'),
                    successMsg: Ifresco.helper.Translations.trans('Quick Search was saved successfully!'),
                    errorMsg: Ifresco.helper.Translations.trans('Unable to save Quick Search, please try later'),
                    success: resData.success
                });
            },
            failure: function () {
                Ext.ux.ErrorMessage.show({
                    title: Ifresco.helper.Translations.trans('Quick Search'),
                    msg: Ifresco.helper.Translations.trans('Unable to save Quick Search, please try later')
                });
            }
        });
        this.fireEvent("afterSave");
    },

    loadClickSearch: function() {
        var panel = Ifresco.view.ClickSearch.create({
            configData: {properties: []}
        });
        this.getAdminTabContentPanel().removeAll();
        this.getAdminTabContentPanel().add(panel);
        this.loadPropertiesToPanel(panel);

        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_admin_click_search_get'),
            method: 'GET',
            success: function(response) {
                var data = Ext.decode(response.responseText);
                panel.loadData(data.data);
            }
        });
    },     
    
    

    saveClickSearch: function() {
        var panel = this.getClickSearchPanel();
        var store = panel.down('propertyselector').getStore();
        var fields = [];
        store.each(function(field) {
            var data = field.data;
            fields.push(
                [
                    data.name,
                    data['class'],
                    data.title,
                    data.dataType,
                    data.type
                ].join('/')
            );
        });
        //var columnSetId = panel.down('combo[name=columnset]').getValue();
        var columnSetId = 0;
        var data = {
            fields: fields,
            columnset: columnSetId
        };

        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_admin_click_search_save'),
            method: 'POST',
            params: {
                data: Ext.encode(data)
            },
            success: function(res) {
                var resData = Ext.decode(res.responseText);
                Ext.ux.StatusMessage.show({
                    title: Ifresco.helper.Translations.trans('Click Search'),
                    successMsg: Ifresco.helper.Translations.trans('Click Search was saved successfully!'),
                    errorMsg: Ifresco.helper.Translations.trans('Save failed, please try later'),
                    success: resData.success
                });
            },
            failure: function() {
                Ext.ux.ErrorMessage.show({
                    title: Ifresco.helper.Translations.trans('Click Search'),
                    msg: Ifresco.helper.Translations.trans('Save failed, please try later')
                });
            }
        });
        this.fireEvent("afterSave");
    },

    loadSearchTemplateGrid: function() {
        if (!this.getSearchTemplateGrid()) {
            this.getAdminTabContentPanel().removeAll();
            this.getAdminTabContentPanel().add(Ifresco.view.grid.SearchTemplate.create({}));
        } else {
            this.getSearchTemplateGrid().getStore().reload();
        }
    },

    setDefaultSearchTemplate: function (id) {
        var gridStore = this.getSearchTemplateGrid().getStore();
        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_admin_template_mark_as_default'),
            params: {
                id: id
            },
            success: function() {
                gridStore.reload();
            }
        });
    },

    editSearchTemplate: function(id) {
        var panel = Ifresco.form.SearchTemplate.create({});
        this.getAdminTabContentPanel().removeAll();
        this.getAdminTabContentPanel().add(panel);
        this.loadPropertiesToPanel(panel);
        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_admin_search_template_get', {id: id}),
            success: function(response) {
                var data;
                data = Ext.decode(response.responseText);
                panel.edit = id;
                panel.loadTemplateData(data.data);
            }
        });

    },

    saveSearchTemplate: function() {
        var panel = this.getSearchTemplateForm();
        var data = {
            col1: [],
            col2: [],
            tabs: [],
            customs: [],
            edit: panel.edit,
            customFields: [],
            showDoctype: panel.down('itemselector[name=showDoctype]').getValue(),
            name: panel.down('textfield[name=name]').getValue(),
            columnset: panel.down('combo[name=columnset]').getValue(),
            savedsearch: panel.down('combo[name=savedsearch]').getValue(),
            contenttype: panel.down('combo[name=contenttype]').getValue(),
            multiColumns: panel.down('checkbox[name=multiColumns]').getValue(),
            fulltextChild: panel.down('checkbox[name=fulltextChild]').getValue(),
            fulltextChildOverwrite: panel.down('checkbox[name=fulltextChildOverwrite]').getValue(),
            lucene_query: panel.down('textfield[name=lucene_query]').getValue()
        };

        this.assignProperties(panel.down('propertyselector[name=col1]').getStore(), data.col1);
        this.assignProperties(panel.down('propertyselector[name=col2]').getStore(), data.col2);

        panel.down('tabpanel[name=tabs]').items.each(function(tab) {
            var tempTab = {
                title: tab.tabConfig.title,
                items: []
            };

            this.assignProperties(tab.getStore(), tempTab.items);
            data.tabs.push(tempTab);
        }, this);

        var customFieldsIndex = 0;
        panel.down('tabpanel[name=customFields]').items.each(function(tab) {
            data['custom_field_lable' + customFieldsIndex] = tab.tabConfig.title;
            data['customQueryMode' + customFieldsIndex] = tab.customQueryMode;
            data.customs.push(customFieldsIndex);
            data['customFieldValues_' + customFieldsIndex] = [];

            this.assignProperties(tab.getStore(), data['customFieldValues_' + customFieldsIndex]);

            ++customFieldsIndex;
        }, this);

        var controller = this;
        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_admin_search_template_save'),
            loadMask: true,
            disableCaching: true,
            params: {
                data: Ext.encode(data)
            },
            success: function(res) {
                var resData = Ext.decode(res.responseText);
                Ext.ux.StatusMessage.show({
                    title: Ifresco.helper.Translations.trans('Templates'),
                    successMsg: Ifresco.helper.Translations.trans('Your template was saved successfully!'),
                    errorMsg: Ifresco.helper.Translations.trans('Save failed, please try later'),
                    success: resData.success
                });
                if (resData.success) {
                    controller.loadSearchTemplateGrid();
                }
            },
            failure: function() {
                Ext.ux.ErrorMessage.show({
                    title: Ifresco.helper.Translations.trans('Templates'),
                    msg: Ifresco.helper.Translations.trans('Save failed, please try later')
                });
            }
        }, this);
    },

    assignProperties: function (fromStore, toArray) {
        fromStore.each(function (property) {
            toArray.push([
                property.get('name'),
                property.get('class'),
                property.get('title'),
                property.get('dataType'),
                property.get('type')
            ].join('/'));
        });
    },

    loadColumnSetsGrid: function() {
        if (!this.getAdminColumnSetsGrid()) {
            this.getAdminTabContentPanel().removeAll();
            this.getAdminTabContentPanel().add(Ifresco.view.grid.ColumnSets.create({}));
        } else {
            this.getAdminColumnSetsGrid().getStore().reload();
        }
    },

    setDefaultColumnSet: function (id) {
        var gridStore = this.getAdminColumnSetsGrid().getStore();
        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_admin_column_set_mark_as_default'),
            params: {
                id: id
            },
            success: function() {
                gridStore.reload();
            }
        });
    },

    editColumnSet: function(id) {
        this.getAdminTabContentPanel().removeAll();
        var panel = Ifresco.form.ColumnSet.create({});
        this.getAdminTabContentPanel().add(panel);
        this.loadPropertiesToPanel(panel);
        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_admin_column_set_edit', {id: id}),
            success: function(response) {
                var data;
                data = Ext.decode(response.responseText);
                panel.loadColumnSetData(data.data);
            }
        });
        this.fireEvent("afterSave");
    },

    saveColumnSet: function() {
        var panel = this.getAdminColumnSetForm();
        var data = {
            edit: panel.edit,
            cols: [],
            name: panel.down('textfield[name=name]').getValue(),
            hideInMenu: panel.down("checkboxfield[name=hideInMenu]").checked
        };

        panel.down('propertyselector[name=cols]').getStore().each(function(property) {
            var show = property.get('hidden') ? 'hide' : 'show';
            var sort = property.get('sort') ? 'sort' : 'nosort';
            var sortBy = property.get('ascending') ? 'asc' : 'desc';
            data.cols.push([
                property.get('name'),
                property.get('class'),
                property.get('title'),
                property.get('dataType'),
                property.get('type'),
                show,
                sort,
                sortBy
            ].join('/'));
        });

        var controller = this;
        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_admin_column_set_save'),
            loadMask: true,
            disableCaching: true,
            params: {
                data: Ext.encode(data)
            },
            success: function(res) {
                var resData = Ext.decode(res.responseText);
                Ext.ux.StatusMessage.show({
                    title: Ifresco.helper.Translations.trans('Column Set'),
                    successMsg: Ifresco.helper.Translations.trans('Your columnset was saved successfully!'),
                    errorMsg: Ifresco.helper.Translations.trans('Save failed, please try later'),
                    success: resData.success
                });
                if (resData.success) {
                    controller.loadColumnSetsGrid();
                }
            },
            failure: function() {
                Ext.ux.ErrorMessage.show({
                    title: Ifresco.helper.Translations.trans('Column Set'),
                    msg: Ifresco.helper.Translations.trans('Save failed, please try later')
                });
            }
        }, this);
        this.fireEvent("afterSave");
    },

    loadExportJobs: function() {
        var panel = Ifresco.view.ExportJobs.create({});
        this.getAdminTabContentPanel().removeAll();
        this.getAdminTabContentPanel().add(panel);
        this.loadPropertiesToPanel(panel);
        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_admin_export_jobs_get'),
            loadMask: true,
            disableCaching: true,
            success: function(response) {
                var data = Ext.decode(response.responseText);
                panel.loadExportFields(data.data);
            }

        });
    },

    exportJobs: function() {
        var panel = this.getExportJobsPanel();
        var data = {
            email: panel.down('textfield[name=email]').getValue(),
            fields: [],
            edit: true,
            folders: panel.down('checkbox[name=folders]').getValue()
        };

        panel.down('propertyselector').getStore().each(function(property) {
            data.fields.push([
                property.get('name'),
                property.get('class'),
                property.get('title'),
                property.get('dataType'),
                property.get('type')
            ].join('/'));
        });

        var controller = this;
        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_admin_export_jobs_save'),
            disableCaching: true,
            params: {
                data: Ext.encode(data)
            },
            success: function(res) {
                var resData = Ext.decode(res.responseText);
                Ext.ux.StatusMessage.show({
                    title: Ifresco.helper.Translations.trans('Export jobs'),
                    successMsg: Ifresco.helper.Translations.trans('Job was created successfully!'),
                    errorMsg: Ifresco.helper.Translations.trans('Save failed, please try later'),
                    success: resData.success
                });
                if (resData.success) {
                    controller.loadJobsGrid();
                }
            },
            failure: function() {
                Ext.ux.ErrorMessage.show({
                    title: Ifresco.helper.Translations.trans('Export jobs'),
                    msg: Ifresco.helper.Translations.trans('Save failed, please try later')
                });
            }
        });
    },

    loadJobsGrid: function() {
        if (!this.getJobsGrid()) {
            this.getAdminTabContentPanel().removeAll();
            this.getAdminTabContentPanel().add(Ifresco.view.grid.Jobs.create({}));
        } else {
            this.getJobsGrid().getStore().reload();
        }
    },
    
    loadTagManagerGrid: function () {
        if (!this.getTagManagerGrid()) {
            this.getAdminTabContentPanel().removeAll();
            this.getAdminTabContentPanel().add(Ifresco.view.grid.TagManager.create({}));
        } else {
            this.getTagManagerGrid().getStore().reload();
        }
    },
    
    loadAllPluginList: function() {
    	if (!this.getPluginsGrid()) {
            this.getAdminTabContentPanel().removeAll();
            this.getAdminTabContentPanel().add(Ifresco.view.grid.Plugins.create({}));
        } else {
            this.getPluginsGrid().getStore().reload();
        }
    },
    
    loadCurrencyFields: function() {
        var panel = Ifresco.view.CurrencyFields.create({
            configData: {properties: []}
        });
        this.getAdminTabContentPanel().removeAll();
        this.getAdminTabContentPanel().add(panel);
        this.loadPropertiesToPanel(panel);

        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_admin_currency_fields_get'),
            method: 'GET',
            success: function(response) {
                var data = Ext.decode(response.responseText);
                panel.loadData(data.data);
            }
        });
    },
    
    saveCurrencyFields: function() {
        var panel = this.getCurrencyFieldsPanel();
        var store = panel.down('propertyselector').getStore();
        var fields = [];
        store.each(function(field) {
            var data = field.data;
            fields.push(data);
        });
        
        
        
        var data = {
            fields: fields
        };

        Ext.Ajax.request({
            url: Routing.generate('ifresco_client_admin_currency_fields_save'),
            method: 'POST',
            params: {
                data: Ext.encode(data)
            },
            success: function(res) {
                var resData = Ext.decode(res.responseText);
                Ext.ux.StatusMessage.show({
                    title: Ifresco.helper.Translations.trans('Currency Fields'),
                    successMsg: Ifresco.helper.Translations.trans('Currency Fields were saved successfully!'),
                    errorMsg: Ifresco.helper.Translations.trans('Save failed, please try later'),
                    success: resData.success
                });
            },
            failure: function() {
                Ext.ux.ErrorMessage.show({
                    title: Ifresco.helper.Translations.trans('Currency Fields'),
                    msg: Ifresco.helper.Translations.trans('Save failed, please try later')
                });
            }
        });
        this.fireEvent("afterSave");
    }
    
    /*pluginListSave: function() {
    	var pluginsGrid = this.getPluginsGrid();
    	Ext.Ajax.request({
            url: Routing.generate('ifresco_client_admin_plugins_save'),
            disableCaching: true,
            params: {
                data: Ext.encode(data)
            },
            success: function(res) {
                var resData = Ext.decode(res.responseText);
                Ext.ux.StatusMessage.show({
                    title: Ifresco.helper.Translations.trans('Export jobs'),
                    successMsg: Ifresco.helper.Translations.trans('Job was created successfully!'),
                    errorMsg: Ifresco.helper.Translations.trans('Save failed, please try later'),
                    success: resData.success
                });
                if (resData.success) {
                    controller.loadJobsGrid();
                }
            },
            failure: function() {
                Ext.ux.ErrorMessage.show({
                    title: Ifresco.helper.Translations.trans('Export jobs'),
                    msg: Ifresco.helper.Translations.trans('Save failed, please try later')
                });
            }
        });
    }*/

});