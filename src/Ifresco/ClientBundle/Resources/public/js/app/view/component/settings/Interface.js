Ext.define('Ifresco.view.settings.Interface', {
    extend: 'Ext.form.Panel',
    alias: 'widget.ifrescoViewSettingsInterface',
    border: 0,
    defaults: {
        margin: 5
    },
    autoScroll: true,
    cls: 'ifresco-view-settings-interface',
    initComponent: function () {
        Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('Interface'),
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
                cls: 'ifresco-view-table-settings-system',
                items: [{
                    xtype: 'container',
                    html: Ifresco.helper.Translations.trans('Upload Logo') + ':',
                    cellCls: 'ifresco-view-settings-row-left',
                    width: 200
                },{
                    xtype: 'container',
                    width: '100%',
                    layout: 'vbox',
                    anchor: '100%',
                    border: 0,
                    cellCls: 'ifresco-view-settings-row-right',
                    items: [{
                        xtype: 'image',
                        margin: 5,
                        src: '/bundles/ifrescoclient/images/logo94x50.png' // TODO: route.better to make throw css styles
                    },{
                        xtype: 'button',
                        text: Ifresco.helper.Translations.trans('Change a logo'),
                        handler: function() {
                            var window = Ifresco.view.window.ChangeLogo.create({});
                            window.show();
                        }
                    }]
                },{
                    xtype: 'box',
                    border: 0,
                    colspan: 2
                },{
                    xtype: 'container',
                    html: Ifresco.helper.Translations.trans('Logo URL') + ':',
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
                        name: 'logoURL'
                    }]
                }]
            }]
        });

        this.callParent();
    },

    scope: this
});
