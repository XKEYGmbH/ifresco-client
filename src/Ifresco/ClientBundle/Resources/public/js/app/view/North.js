Ext.define('Ifresco.view.North', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.ifrescoNorth',
    cls: 'ifresco-north-panel',
    height: 60,
    border: 0,
    layout: {
        type: 'vbox',
        align: 'stretch'
    },

    initComponent: function() {
        var contentLayout = Ifresco.helper.Registry.get('ArrangeList');
        this.items = [{
            xtype: 'toolbar',
            height: 60,
            border: 0,
            style: { background: 'none' },
            items: [{
                xtype: 'image',
                src: logoUrl,
                height: 50,
                width: 94,
                autoEl: {
                    tag: 'a',
                    href: Routing.generate('ifresco_client_index'),
                    style: { marginLeft: '10px', padding: 0 }
                }
            },'->',{
                xtype: 'ifrescoFormSearch',
                autoEl: {
                    tag: 'div',
                    style: {
                        backgroundColor: '#F7F7F7',
                        padding: "8px 10px 5px 5px"
                    }
                }
            },{
                xtype: 'panel',
                baseCls: 'ifresco-toolbar-button-container',
                cls: contentLayout === 'horizontal' ? 'ifresco-toolbar-button-container-active' : 'ifresco-toolbar-button-container-inactive',
                margin: '0 3px',
                items: [{
                    xtype: 'button',
                    cls: 'fa fa-columns rotate-text',
                    allowDepress: false,
                    width: 20,
                    height: 20,
                    border: 0,
                    pressed: (contentLayout === 'horizontal'),
                    enableToggle: true,
                    toggleGroup: 'ifrescoSplitContent',
                    listeners: {
                        toggle: function (button, pressed) {
                            var inactiveCls = 'ifresco-toolbar-button-container-inactive';
                            var activeCls = 'ifresco-toolbar-button-container-active';
                            var panel = button.up('panel');
                            if (pressed) {
                                Ifresco.helper.Registry.set("ArrangeList", "horizontal");
                                button.up('ifrescoNorth').fireEvent('changeContentLayout');
                                panel.removeCls(inactiveCls);
                                panel.addCls(activeCls);
                            } else {
                                panel.addCls(inactiveCls);
                                panel.removeCls(activeCls);
                            }
                        }
                    }
                }]
            },{
                xtype: 'panel',
                baseCls: 'ifresco-toolbar-button-container',
                cls: contentLayout === 'vertical' ? 'ifresco-toolbar-button-container-active' : 'ifresco-toolbar-button-container-inactive',
                margin: '0 4px 0 3px',
                items: [{
                    xtype: 'button',
                    cls: 'fa fa-columns',
                    width: 20,
                    height: 20,
                    border: 0,
                    allowDepress: false,
                    pressed: (contentLayout === 'vertical'),
                    enableToggle: true,
                    toggleGroup: 'ifrescoSplitContent',
                    listeners: {
                        toggle: function (button, pressed) {
                            var inactiveCls = 'ifresco-toolbar-button-container-inactive';
                            var activeCls = 'ifresco-toolbar-button-container-active';
                            var panel = button.up('panel');
                            if (pressed) {
                                Ifresco.helper.Registry.set("ArrangeList", "vertical");
                                button.up('ifrescoNorth').fireEvent('changeContentLayout');
                                panel.removeCls(inactiveCls);
                                panel.addCls(activeCls);
                            } else {
                            	panel.addCls(inactiveCls);
                                panel.removeCls(activeCls);
                            }
                        }
                    }
                }]
            },{
                xtype: 'tbseparator',
                baseCls: 'ifresco-toolbar-separator'
            },{
                xtype: 'panel',
                baseCls: 'ifresco-toolbar-button-container',
                margin: '0 4px',
                items: [{
                    xtype: 'button',
                    cls: 'fa fa-clipboard',
                    tooltip: Ifresco.helper.Translations.trans('Clipboard'),
                    width: 20,
                    height: 20,
                    border: 0,
                    handler: function (button) {
                        button.up('ifrescoNorth').fireEvent('loadClipboard');
                    }
                }]
            },{
                xtype: 'tbseparator',
                baseCls: 'ifresco-toolbar-separator'
            },{
                xtype: 'panel',
                baseCls: 'ifresco-toolbar-button-container',
                margin: '0 4px',
                items: [{
                    xtype: 'button',
                    cls: 'fa fa-trash-o',
                    tooltip: Ifresco.helper.Translations.trans('Trashcan'),
                    width: 20,
                    height: 20,
                    border: 0,
                    handler: function () {
                        this.fireEvent('loadTrashcan');
                    },
                    scope: this
                }]
            },{
                xtype: 'tbseparator',
                baseCls: 'ifresco-toolbar-separator',
                hidden: !Ifresco.helper.Settings.isAdmin,
            },{
                xtype: 'panel',
                baseCls: 'ifresco-toolbar-button-container',
                hidden: !Ifresco.helper.Settings.isAdmin,
                margin: '0 4px',
                items: [{
                    xtype: 'button', 
                    cls: 'fa fa-gears',
                    tooltip: Ifresco.helper.Translations.trans('Admin'),
                    width: 20,
                    height: 20,
                    border: 0,
                    handler: function () {
                        this.fireEvent('loadAdminTab');
                    },
                    scope: this
                }]
            },{
                xtype: 'tbseparator',
                baseCls: 'ifresco-toolbar-separator'
            },{
                xtype: 'panel',
                baseCls: 'ifresco-toolbar-button-container',
                margin: '0 14px 0 4px',
                items: [{
                    xtype: 'button',
                    cls: 'fa fa-sign-out',
                    tooltip: Ifresco.helper.Translations.trans('Logout'),
                    width: 20,
                    height: 20,
                    border: 0,
                    handler: function () {
                        this.fireEvent('logout');
                    },
                    scope: this
                }]
            }]
        }];

        this.callParent();
    }
});