Ext.define('Ifresco.form.Search', {
    extend: 'Ext.form.Panel',
    alias: 'widget.ifrescoFormSearch',
    bodyCls: 'ifresco-form-search',
    layout: 'column',
    border: 0,

    initComponent: function() {
        this.items = [{
            border: 0,
            style: {
                margin: 0,
                padding: "5px 5px 0 5px"
            },
            items: [{
                xtype: 'button',
                text: Ifresco.helper.Translations.trans("Advanced Search"),
                cls: 'ifresco-advancedsearch-link',
                bodyCls: '',
                handler: function () {
                    this.up('ifrescoFormSearch').fireEvent('loadAdvancedSearchTab');
                }
            }]
        },{
            xtype: 'textfield',
            name: 'search',
            cls: 'ifresco-search-input',
            listeners: {
                specialkey: function(field, e){
                    if (e.getKey() == e.ENTER) {
                        this.fireEvent('search', field.getValue());
                    }
                },
                scope: this
            }
        },{
            border: 0,
            cls: 'ifresco-search-button-container',
            style: {
                margin: 0,
                padding: 0
            },
            items:[{
                xtype: 'button',
                text: Ifresco.helper.Translations.trans("Search"),
                cls: 'ifresco-search-button',
                style: {
                    padding: '4px 5px',
                    width: '80px'
                },
                handler: function (btn) {
                    this.fireEvent('search', this.down('textfield[cls~=ifresco-search-input]').getValue());
                },
                scope: this
            }]
        }];

//        this.buttons = [{
//
//        }]

        this.callParent();
    }
});