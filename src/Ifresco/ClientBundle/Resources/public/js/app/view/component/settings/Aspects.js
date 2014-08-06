Ext.define('Ifresco.view.settings.Aspects', {
    extend: 'Ext.form.Panel',
    alias: 'widget.ifrescoViewSettingsAspects',
    border: 0,
    defaults: {
        margin: 5
    },
    height: '100%',
    width: '100%',
    layout: 'fit',
    configData: null,
    cls: 'ifresco-view-settings-aspects',
    initComponent: function () {
        Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('Aspects'),
            tbar: [{
            	iconCls: 'ifresco-icon-save',
                text: Ifresco.helper.Translations.trans('Save'),
                handler: function(){ 
                    this.fireEvent('save');
                },
                scope: this
           },{
        	   iconCls: 'ifresco-icon-cancel',
               text: Ifresco.helper.Translations.trans('Reset to defaults'),
               handler: function(){
                   this.down('itemselector').setValue(this.configData.defaultAspcets);
               },
               scope: this
            }],
            items: [{
                xtype: 'itemselector',
                maxWidth: 700,
                name: 'aspectSelector',
                cls: 'ifresco-ui-item-selector',
                anchor: '100%',
                height: '100%',
                border: 0,
                store: {
                    fields: ['id', 'text', 'value'],
                    proxy: {
                        type: 'memory'
                    }
                },
                displayField: 'text',
                valueField: 'value',
                allowBlank: true,
                msgTarget: 'side',
                fromTitle: Ifresco.helper.Translations.trans('Available'),
                toTitle: Ifresco.helper.Translations.trans('Selected'),
                buttons: ['top', 'up', 'add', 'remove', 'down', 'bottom']
            }]
        });

        this.configData = this.configData || {};

        this.configData.defaultAspcets = [
            'cm:generalclassifiable',
            'cm:complianceable',
            'cm:dublincore',
            'cm:effectivity',
            'cm:summarizable',
            'cm:versionable',
            'cm:templatable',
            'cm:emailed',
            'emailserver:aliasable',
            'cm:taggable',
            'app:inlineeditable',
            'cm:geographic',
            'exif:exif'
        ];

        this.callParent();
    },

    scope: this
});
