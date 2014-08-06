Ext.define('Ifresco.view.settings.PropertyFilter', {
    extend: 'Ext.form.Panel',
    alias: 'widget.ifrescoViewSettingsPropertyFilter',
    border: 0,
    defaults: {
        margin: 5
    },
    autoScroll: true,
    cls: 'ifresco-view-settings-propertyfilter',
    initComponent: function() {
        Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('Property Filter'),
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
                    html: Ifresco.helper.Translations.trans('Allowed prefixes') + ':',
                    cellCls: 'ifresco-view-settings-row-left',
                    width: 200
                },{
                    xtype: 'container',

                    border: 0,
                    cellCls: 'ifresco-view-settings-row-right',
                    cls: 'ifresco-view-prop-items',
                    defaults: {
                        xtype: 'checkboxfield',
                        name: 'prefs'
                    },
                    items: []
                }]
            }]
        });

        this.callParent();
    },

    scope: this
});