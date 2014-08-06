Ext.define('Ifresco.view.AdminTab', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.adminTab',
    itemId: 'ifrescoAdminTab',
    closable:true,
    border: 0,
    cls: 'ifresco-view-content-tab',
    layout: {
        type: 'hbox',
        align: 'stretch'
    },

    initComponent: function () {
        Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('Administration'),
            items: [{
                xtype: 'panel',
                cls: 'ifresco-admin-menu',
                flex: 1,
                maxWidth: 250,
                layout: {
                    type: 'accordion'
                },
                collapsible: true,
                collapseDirection: 'left',
                title: 'Menu',
                defaults: {
                    autoScroll: true,
                    layout: {
                        type: 'vbox',
                        align: 'stretch'
                    },
                    defaults: {
                        textAlign: 'left',
                        xtype: 'button',
                        scope: this
                    }
                },
                items: [{
                        title: Ifresco.helper.Translations.trans('Tools'),
                        containerId: 'tools',
                        items: [{
                            text: Ifresco.helper.Translations.trans('Interface'),
                            handler: function () {
                                this.fireEvent('adminTabSettingsInterfaceShow');
                            }
                        },{
                            text: Ifresco.helper.Translations.trans('Tag Manager'),
                            handler: function () {
                                this.fireEvent('adminTabTagManagerShow');
                            }
                        }]
                    },{
                        title: Ifresco.helper.Translations.trans('Content Model'),
                        containerId: 'content-model',
                        items: [{
                            text: Ifresco.helper.Translations.trans('Templates'),
                            handler: function () {
                                this.fireEvent('adminTabTemplatesShow');
                            }
                        },{
                            text: Ifresco.helper.Translations.trans('Lookups'),
                            handler: function () {
                                this.fireEvent('adminTabLookupsShow');
                            }
                        },{
                            text: Ifresco.helper.Translations.trans('Currency Fields'),
                            handler: function () {
                                this.fireEvent('adminTabCurrencyFieldsShow');
                            }
                        }]
                    },{
                        title: Ifresco.helper.Translations.trans('Data Sources'),
                        containerId: 'data-sources',
                        items: [{
                            text: Ifresco.helper.Translations.trans('Data Sources'),
                            handler: function () {
                                this.fireEvent('adminTabDataSourcesShow');
                            }
                        }]
                    },{
                        title: Ifresco.helper.Translations.trans('Search'),
                        containerId: 'search',
                        items: [{
                            text: Ifresco.helper.Translations.trans('Templates'),
                            handler: function () {
                                this.fireEvent('adminTabSearchTemplateShow');
                            }
                        },{
                            text: Ifresco.helper.Translations.trans('Quick Search'),
                            handler: function () {
                                this.fireEvent('adminTabQuickSearchShow');
                            }
                        },{
                            text: Ifresco.helper.Translations.trans('Click Search'),
                            handler: function () {
                                this.fireEvent('adminTabClickSearchShow');
                            }
                        }]
                    },{
                        title: Ifresco.helper.Translations.trans('Columns'),
                        containerId: 'columns',
                        items: [{
                            text: Ifresco.helper.Translations.trans('Column Sets'),
                            handler: function () {
                                this.fireEvent('adminTabColumnSetsShow');
                            }
                        }]
                    },{
                        title: Ifresco.helper.Translations.trans('Users and Groups'),
                        containerId: 'users-groups',
                        items: [{
                            text: Ifresco.helper.Translations.trans('Users'),
                            handler: function () {
                                this.fireEvent('adminTabUserManagementShow');
                            }
                        },{
                            text: Ifresco.helper.Translations.trans('Groups'),
                            handler: function () {
                                this.fireEvent('adminTabGroupManagementShow');
                            }
                        }]
                    },{
                        title: Ifresco.helper.Translations.trans('General Settings'),
                        containerId: 'general-settings',
                        items: [{
                            text: Ifresco.helper.Translations.trans('System'),
                            handler: function () {
                                this.fireEvent('adminTabSettingsSystemShow');
                            }
                        },{
                            text: Ifresco.helper.Translations.trans('Email'),
                            handler: function () {
                                this.fireEvent('adminTabSettingsEmailShow');
                            }
                        },{
                            text: Ifresco.helper.Translations.trans('Aspects'),
                            handler: function () {
                                this.fireEvent('adminTabSettingsAspectsShow');
                            }
                        },{
                            text: Ifresco.helper.Translations.trans('Content Types'),
                            handler: function () {
                                this.fireEvent('adminTabSettingsContentTypesShow');
                            }
                        },{
                            text: Ifresco.helper.Translations.trans('Namespace Mapping'),
                            handler: function () {
                                this.fireEvent('adminTabSettingsNamespaceMappingShow');
                            }
                        },{
                            text: Ifresco.helper.Translations.trans('Property Filter'),
                            handler: function () {
                                this.fireEvent('adminTabSettingsPropertyFilterShow');
                            }
                        }/*,{
                            text: Ifresco.helper.Translations.trans('Online Editing'),
                            handler: function () {
                                this.fireEvent('adminTabSettingsOnlineEditingShow');
                            }
                        }*/,{
                            text: Ifresco.helper.Translations.trans('Upload Allowed Types'),
                            handler: function () {
                                this.fireEvent('adminTabSettingsUploadAllowedTypesShow');
                            }
                        },{
                            text: Ifresco.helper.Translations.trans('Tree Root Folder'),
                            handler: function () {
                                this.fireEvent('adminTabSettingsTreeRootFolderShow');
                            }
                        },{
                            text: Ifresco.helper.Translations.trans('Dropbox Config'),
                            handler: function () {
                                this.fireEvent('adminTabSettingsDropboxConfigShow');
                            }
                        }]
                    },{
                        title: Ifresco.helper.Translations.trans('ifresco Transformer Settings'),
                        containerId: 'autoocr-settings',
                        items: [{
                            text: Ifresco.helper.Translations.trans('Connection configuration'),
                            handler: function () {
                                this.fireEvent('adminTabSettingsAutoOCRConfigShow');
                            }
                        },{
                            text: Ifresco.helper.Translations.trans('Status'),
                            handler: function () {
                                this.fireEvent('adminTabSettingsAutoOCRStatusShow');
                            }
                        },{
                            text: Ifresco.helper.Translations.trans('Transformer Configuration'),
                            handler: function () {
                                this.fireEvent('adminTabSettingsAutoOCRTransformersShow');
                            }
                        },{
                            text: Ifresco.helper.Translations.trans('Runtime transformer'),
                            handler: function () {
                                this.fireEvent('adminTabSettingsAutoOCRRuntimeTransformersShow');
                            }
                        },{
                            text: Ifresco.helper.Translations.trans('Jobs'),
                            handler: function () {
                                this.fireEvent('adminTabSettingsAutoOCRJobsShow');
                            }
                        }]
                    },{
                        title: Ifresco.helper.Translations.trans('Jobs'),
                        containerId: 'jobs',
                        items: [{
                            text: Ifresco.helper.Translations.trans('Current Jobs'),
                            handler: function () {
                                this.fireEvent('adminTabJobsShow');
                            }
                        },{
                            text: Ifresco.helper.Translations.trans('Export'),
                            handler: function () {
                                this.fireEvent('adminTabExportJobsShow');
                            }
                        }]
                    },{
                        title: Ifresco.helper.Translations.trans('Settings Export/Import'),
                        containerId: 'export-import',
                        items: [{
                            text: Ifresco.helper.Translations.trans('Export'),
                            handler: function () {
                                this.fireEvent('adminTabExportShow');
                            }
                        },{
                            text: Ifresco.helper.Translations.trans('Import'),
                            handler: function () {
                                this.fireEvent('adminTabImportShow');
                            }
                        }]
                    },{
                        title: Ifresco.helper.Translations.trans('Plugins'),
                        containerId: 'plugins',
                        items: [{
                            text: Ifresco.helper.Translations.trans('List all Plugins'),
                            handler: function () {
                                this.fireEvent('adminTabListAllPlugins');
                            }
                        }]
                    },{
                        title: Ifresco.helper.Translations.trans('About'),
                        bodyPadding: 5,
                        tpl: Ext.XTemplate('<b>{versionMsg}:</b> {version}<br>' 
                            + '<b>{mailMsg}:</b> <a href="mailto:{mail}">{mail}</a><br><br>'
                            + '<a href="' + '{clearCacheLink}' + '">{clearCacheMsg}</a>'),
                        data: {
                            versionMsg: Ifresco.helper.Translations.trans('Version'),
                            version: ifrescoVersion,
                            mailMsg: Ifresco.helper.Translations.trans('Contact us'),
                            mail: 'office@xkey.at',
                            clearCacheMsg: Ifresco.helper.Translations.trans('Clear cache'),
                            clearCacheLink: '#'
                        }
                }]
            },{
                xtype: 'panel',
                cls: 'ifresco-admin-content-panel',
                layout: 'fit',
                border: 0,
                flex: 1,
                overflowY: 'auto'
            }]
        });

        this.callParent();
    },
    listeners: {
    }
});