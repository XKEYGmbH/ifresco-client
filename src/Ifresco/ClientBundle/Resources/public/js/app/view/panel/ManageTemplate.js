Ext.define('Ifresco.view.panel.ManageTemplate', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.ifrescoViewPanelManageTemplate',
    layout:'fit',
    configData: null,
    border: 0,
    selectedTemplateTypeId: null,

    initComponent: function () {
        console.log(this.configData);

        Ext.apply(this, {
            tbar: [{
                text: Ifresco.helper.Translations.trans('Save'),
                cls: 'ifresco-admin-template-save',
                handler: function () {
                    this.fireEvent('saveTemplate', this.selectedTemplateTypeId, this.configData);
                },
                scope: this
            }],
            items: [{
                xtype: 'ifrescoformtemplate',
                configData: this.configData
            }]
        });

        this.callParent();
    }
});