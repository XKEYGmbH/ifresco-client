Ext.define('Ifresco.view.Export', {
    extend: 'Ext.form.Panel',
    alias: 'widget.ifrescoViewExport',
    border: 0,
    cls: 'ifresco-view-export',
    bodyPadding: 5,
    layout: {
        type: 'hbox',
        pack: 'start',
        align: 'stretch'
    },
    initComponent: function () {
        Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('Export Settings'),
            tbar: [
                {
                    text: Ifresco.helper.Translations.trans('Export'),
                    handler: function() {
                        this.fireEvent('fetch');
                    },
                    scope: this
                },'-',{
                    text: Ifresco.helper.Translations.trans('All Admin Settings'),
                    handler: function() {
                        var form = this.up('panel').getForm(),
                            fields = form.getFields();
                        fields.each(function(field) {
                            if (field.isXType('checkbox')) {
                                field.setValue(true);
                            }
                        });
                    }
                }, {
                    text: Ifresco.helper.Translations.trans('All Templates & Columnsets'),
                    handler: function() {
                        var form = this.up('panel').getForm(),
                            fields = form.getFields();
                        fields.each(function(field) {
                            if (field.isXType('checkbox')) {
                                switch(field.getName()) {
                                    case 'content_model_tpls':
                                        break;
                                    case 'search_tpls':
                                        break;
                                    case 'column_set':
                                        field.setValue(true);
                                        break;
                                    default: 
                                        field.setValue(false);
                                }
                            }
                        });
                    }
                }, {
                    text: Ifresco.helper.Translations.trans('Columnsets'),
                    handler: function() {
                        var form = this.up('panel').getForm(),
                            fields = form.getFields();
                        fields.each(function(field) {
                            if (field.isXType('checkbox')) {
                                switch(field.getName()) {
                                    case 'column_set':
                                        field.setValue(true);
                                        break;
                                    default: 
                                        field.setValue(false);
                                }
                            }
                        });
                    }
                }, {
                    text: Ifresco.helper.Translations.trans('Advanced Search Templates'),
                    handler: function() {
                        var form = this.up('panel').getForm(),
                            fields = form.getFields();
                        fields.each(function(field) {
                            if (field.isXType('checkbox')) {
                                switch(field.getName()) {
                                    case 'search_tpls':
                                        break;
                                    case 'quick_search':
                                        field.setValue(true);
                                        break;
                                    default: 
                                        field.setValue(false);
                                }
                            }
                        });
                    }
                }, {
                    text: Ifresco.helper.Translations.trans('Content templates'),
                    handler: function() {
                        var form = this.up('panel').getForm(),
                            fields = form.getFields();
                        fields.each(function(field) {
                            if (field.isXType('checkbox')) {
                                switch(field.getName()) {
                                    case 'content_model_tpls':
                                        break;
                                    case 'content_model_lookups':
                                        field.setValue(true);
                                        break;
                                    default: 
                                        field.setValue(false);
                                }
                            }
                        });
                    }
                }
            ],
            items: [{
                xtype: 'panel',
                width: 240,
                border: 0,
                layout: {
                    type: 'table',
                    columns: 1
                },
                defaults: {
                    cellCls: 'ifresco-view-export-checkbox',
                    xtype: 'checkboxfield',
                    padding: '0 5 0 5',
                    margin: 0,
                    labelWidth: 200
                },
                items: [
                    {
                        fieldLabel: Ifresco.helper.Translations.trans('Content Model Templates'),
                        name: 'content_model_tpls',
                        inputValue: 1
                    }, {
                        fieldLabel: Ifresco.helper.Translations.trans('Content Model Lookups'),
                        name: 'content_model_lookups',
                        inputValue: 1
                    }, {
                        fieldLabel: Ifresco.helper.Translations.trans('Search Templates'),
                        name: 'search_tpls',
                        inputValue: 1
                    }, {
                        fieldLabel: Ifresco.helper.Translations.trans('Quick Search'),
                        name: 'quick_search',
                        inputValue: 1
                    }, {
                        fieldLabel: Ifresco.helper.Translations.trans('Column Set'),
                        name: 'column_set',
                        inputValue: 1
                    }, {
                        fieldLabel: Ifresco.helper.Translations.trans('System Settings'),
                        name: 'system_settings',
                        inputValue: 1
                    }, {
                        fieldLabel: Ifresco.helper.Translations.trans('OCR Settings'),
                        name: 'ocr_settings',
                        inputValue: 1
                    }, {
                        fieldLabel: Ifresco.helper.Translations.trans('Email Settings'),
                        name: 'email_settings',
                        inputValue: 1
                    }, {
                        fieldLabel: Ifresco.helper.Translations.trans('Aspects'),
                        name: 'aspects',
                        inputValue: 1
                    }, {
                        fieldLabel: Ifresco.helper.Translations.trans('Content Types'),
                        name: 'content_types',
                        inputValue: 1
                    }, {
                        fieldLabel: Ifresco.helper.Translations.trans('Namespace Mapping'),
                        name: 'namespace_mapping',
                        inputValue: 1
                    }, {
                        fieldLabel: Ifresco.helper.Translations.trans('Online Editing'),
                        name: 'online_editing',
                        inputValue: 1
                    }, {
                        fieldLabel: Ifresco.helper.Translations.trans('Data Sources'),
                        name: 'data_sources',
                        inputValue: 1
                    }, {
                        fieldLabel: Ifresco.helper.Translations.trans('Property Filter'),
                        name: 'property_filter',
                        inputValue: 1
                    }, {
                        fieldLabel: Ifresco.helper.Translations.trans('Export Fields'),
                        name: 'export_fields',
                        inputValue: 1
                    }, {
                        cellCls: 'ifresco-view-export-checkbox '
                            + 'ifresco-view-export-checkbox-last',
                        fieldLabel: Ifresco.helper.Translations.trans('Dropbox Settings'),
                        name: 'dropbox_settings',
                        inputValue: 1
                    }
                ]
            },{
                xtype: 'panel',
                layout: 'fit',
                flex: 1,
                border: 0,
                items: [{
                    xtype: 'textarea',
                    name: 'target_export',
                    anchor: '100%',
                    formBind: false,
                    readOnly: true,
                    selectOnFocus: true,
                    submitValue: false
                }]
            }]
        });

        this.callParent();
    }
});