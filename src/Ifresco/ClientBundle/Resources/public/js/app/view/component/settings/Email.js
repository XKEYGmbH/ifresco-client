Ext.define('Ifresco.view.settings.Email', {
    extend: 'Ext.form.Panel',
    alias: 'widget.ifrescoViewSettingsEmail',
    border: 0,
    defaults: {
        margin: 5
    },
    autoScroll: true,
    cls: 'ifresco-view-settings-email',
    initComponent: function () {
        Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('Email'),
            tbar: [{
                xtype: 'button',
                iconCls: 'ifresco-icon-save',
            	text: Ifresco.helper.Translations.trans('Save'),
                formBind: true,
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
                items: []
            }]
        });

        this.callParent();
    },

    createField: function(title, name, value, type, checked) {
        var inputValue = '';
        type = type || 'textfield';
        if (type == 'checkbox') {
            inputValue = "true";
        }
        var field;
        if (title === '') {
            field = {xtype: 'box', colspan: 2};
        }
        else {
            field = [{
                xtype: 'container',
                html: title + ':',
                cellCls: 'ifresco-view-settings-row-left',
                width: 200
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
                    xtype: type,
                    name: name,
                    value: value,
                    inputValue: inputValue,
                    checked: checked
                }]
            },{
                xtype: 'box',
                border: 0,
                padding: 2,
                colspan: 2
            }];
        }

        this.down('panel').add(field);
    }

});
