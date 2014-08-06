Ext.define('Ifresco.view.Import', {
    extend: 'Ext.form.Panel',
    alias: 'widget.ifrescoViewImport',
    border: 0,
    cls: 'ifresco-view-import',
    bodyPadding: 5,
    layout: {
        type: 'fit'
    },
    initComponent: function () {
        Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('Import Settings'),
            tbar: [{
                text: Ifresco.helper.Translations.trans('Import'),
                handler: function() {
                    this.fireEvent('save');
                },
                scope: this
            }],
            items: [{
                xtype: 'textarea',
                name: 'target_import',
                anchor: '100%',
                height: 200
            }]
        });

        this.callParent();
    }
});