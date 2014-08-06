Ext.define('Ifresco.view.settings.DropboxConfig', {
    extend: 'Ext.form.Panel',
    alias: 'widget.ifrescoViewSettingsDropboxConfig',
    border: 0,
    defaults: {
        margin: 5
    },
    autoScroll: true,
    cls: 'ifresco-view-settings-dropboxconfig',
    initComponent: function () {
        Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('Dropbox Config'),
            tbar: [{
            	iconCls: 'ifresco-icon-save',
            	text: Ifresco.helper.Translations.trans('Save'),
                handler : function(){
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
                items: [{
                    xtype: 'container',
                    html: Ifresco.helper.Translations.trans('Dropbox Api Key') + ':',
                    cellCls: 'ifresco-view-settings-row-left'
                },{
                    xtype: 'container',
                    layout: {
                        type: 'table',
                        columns: 2
                    },
                    width: '100%',
                    anchor: '100%',
                    border: 0,
                    cellCls: 'ifresco-view-settings-row-right',
                    items: [{
                        xtype: 'textfield',
                        name: 'dropboxApiKey'
                    }]
                }]
            }]
        });

        this.callParent();
    },

    scope: this
});
