Ext.define('Ifresco.view.window.SpecifyType', {
    extend: 'Ext.window.Window',
    alias: 'widget.ifrescoViewWindowSpecifyType',
    layout: 'fit',
    modal: true,
    width:350,
    height:100,
    closeAction:'hide',
    plain: true,
    constrain: true,
    nodes: null,
    parentEl: null,
    selectedTypeId: null,

    initComponent: function () {
        var store = Ifresco.store.ContentType.create();

        Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('Specify Type'),
            items: [{
                xtype: 'combo',
                store: store.load({}),
                displayField: 'title',
                typeAhead: true,
                queryMode: 'local',
                triggerAction: 'all',
                deferEmptyText: false,
                selectOnFocus:true,
                listeners:{
                    'select': function(combo, record) {
                        var cmp = this.up('ifrescoViewWindowSpecifyType');
                        if (typeof record !== 'undefined') {
                            cmp.selectedTypeId = record[0].data.name;
                        } else {
                            cmp.selectedTypeId = null;
                        }
                        console.log(cmp);
                        console.log(cmp.selectedTypeId);
                    }
                }
            }],
            buttons: [{
                text: Ifresco.helper.Translations.trans('Save'),
                margin: 2,
                formBind: true,
                handler: function (btn) {
                    var win = btn.up('window');
                    var type = this.selectedTypeId;

                    if (type !== null && typeof type !== 'undefined') {
                    	console.log("SEND TO SAVE",this.nodes)
                    	win.fireEvent('saveSpecifiedType', this.nodes, type, this.parentEl);
                    }

                    win.close();
                },
                scope: this
            },{
                text: Ifresco.helper.Translations.trans('Close'),
                margin: '2 4 2 2',
                handler: function (btn) {
                    btn.up('window').close();
                }
            }]
        });

        this.callParent();
    }
});
