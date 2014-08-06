Ext.define('Ifresco.view.window.TemplateDesigner', {
    extend: 'Ext.window.Window',
    alias: 'widget.ifrescoViewWindowTemplateDesigner',
    layout:'fit',
    width:350,
    height:100,
    closeAction:'hide',
    plain: true,
    constrain: true,
    selectedTemplateTypeId: null,

    initComponent: function () {
        Ext.apply(this, {
            items: [{
                xtype: 'combobox',
                store: Ifresco.store.TemplateDesigner.create({}),
                displayField: 'title',
                typeAhead: true,
                queryMode: 'local',
                triggerAction: 'all',
                emptyText: Ifresco.helper.Translations.trans('Select a content type...'),
                selectOnFocus: true,
                listeners:{
                    'select': function(combo, record) {
                        var currentWindow = combo.up('ifrescoViewWindowTemplateDesigner');
                        if (typeof record[0] !== 'undefined') {
                            currentWindow.selectedTemplateTypeId = record[0].internalId;
                            currentWindow.down('button[cls~=ifresco-templates-add-next]').setDisabled(false);
                        } else {
                            currentWindow.down('button[cls~=ifresco-templates-add-next]').setDisabled(true);
                            currentWindow.selectedTemplateTypeId = null;
                        }
                    }
                }
            }],
            buttons: [{
                text: Ifresco.helper.Translations.trans('Next'),
                cls: 'ifresco-templates-add-next',
                disabled: true,
                handler: function () {
                    if (this.selectedTemplateTypeId) {
                        this.fireEvent('addTemplate', this.selectedTemplateTypeId, null);
                        this.close();
                    }
                },
                scope: this
            },{
                text: Ifresco.helper.Translations.trans('Close'),
                handler: function(){
                    this.hide();
                },
                scope: this
            }]
        });

        this.callParent();
    }
});