Ext.define('Ext.ux.form.Tags', {
    
    extend: 'Ext.form.FieldContainer',
    
    mixins: {
        field: 'Ext.form.field.Field'    
    },
    requires: [
        'Ext.view.View'
    ],
    
    alternateClassName: 'Ext.ux.Tags',
    alias: 'widget.tagsfield',
    width: '100%',
    layout: {
        type: 'vbox',
        align: 'stretch'
    },
    items: [],
    
    initComponent: function() {
        var me = this;
        this.tagsStore = Ifresco.store.Tags.create({});
        this.items = [{
            xtype: 'boundlist',
            cls: 'x-tags',
            minHeight: 20,
            tpl: [
                '<tpl for=".">',
                    '<div class="x-boundlist-item x-tag">{tagName}',
                        '<span class="x-tool">',
                    '<img src="data:image/gif;base64,R0lGODlhAQABAID/AMDAwAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==" class="x-tool-img x-tool-close">',
                    '</span></div> ',
                '</tpl>',
                '<div class="x-clear"></div>'
            ],
            deferEmptyText: false,
            emptyText: 'Enter tags:',
            border: 0,
            deferInitialRefresh: false,
            itemSelector: '.x-tag',
            store: {
                fields: ['tagName'],
                proxy: {
                    type: 'memory'
                }
            },
            listeners: {
                itemclick: function(cmp, record, el, num, event) {
                    if (event.target.getAttribute('class') === 'x-tool-img x-tool-close') {
                        record.destroy();
                    }
                }
            }
        }, {
            xtype: 'combo',
            width: '100%',
            store: this.tagsStore,
            margin: '5 0 5 0',
            enableKeyEvents: true,
            displayField: 'name',
            valueField: 'name',
            typeAhead: true,
            listeners: {
                keypress: function (field, e) {
                    if (e.getKey() === e.SPACE ||
                        e.getKey() === e.ENTER) {
                        me.onTag(field);
                        e.preventDefault();
                    }
                },
                select: function (field, value) {
                    me.onTag(field); 
                }
            }
        }];

        this.callParent();
    },

    onTag: function (field) {
        var value = Ext.String.trim(field.getValue());
        if (value != '') {
            this.createTag(value);
        }
        field.setValue('');
    },

    createTag: function (tagName) {
        var store = this.down('dataview').getStore();
        if (store.findRecord('tagName', tagName, 0, false, false, true) === null) {
            store.add({tagName: tagName});
        }
    },

    getValues: function () {
        return this.down('boundlist').getStore().collect('tagName');
    },

    getStore: function () {
        return this.down('boundlist').getStore();
    }

});