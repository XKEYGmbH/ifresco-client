Ext.define('Ifresco.view.EditMetadataTab', {
    extend: 'Ifresco.view.ValuedMetadataForm',
    closable: true,
    autoDestroy: false,
    border: 0,
    alias: 'widget.ifrescoEditMetadataTab',
    cls: 'ifresco-view-edit-metadata-tab',
    layout: 'fit',
    ifrescoId: null,
    nodeId: null,
    title: null,

    initComponent: function () {
    	this.trimTitle();
    	
        Ext.apply(this, {
            items: [{
                xtype: 'form',
                cls: 'ifresco-view-editmetadata-form',
                border: 0,
                margin: 0,
                padding: 0,
                layout: 'border',
                flex: 1,
                tbar: [{
                	iconCls: 'ifresco-icon-save',
                    text: Ifresco.helper.Translations.trans('Save'),
                    handler: function(){
                    	this.fireEvent('saveMetadata', this, this.nodeId);
                    },
                    scope:this
                }, {
                	iconCls: 'ifresco-icon-cancel',
                    text: Ifresco.helper.Translations.trans('Cancel'),
                    handler: function(){
                    	this.close();
                    },
                    scope:this
                }],
                items: [{
                    xtype: 'panel',
                    region: 'center',
                    border: 0,
                    layout: {
                        type: 'hbox',
                        align: 'stretch'
                    },
                    autoScroll: true,
                    items: [{
                        autoScroll: true,
                        flex: 1,
                        layout: {
                            type: 'vbox',
                            align: 'stretch'
                        },
                        maxWidth: 500,
                        minWidth: 300,
                        border: 0,
                        bodyPadding: 10,
                        xtype: 'panel',
                        cls: 'ifresco-view-editmetadata-column1'
                    },{
                        autoScroll: true,
                        flex: 1,
                        layout: {
                            type: 'vbox',
                            align: 'stretch'
                        },
                        maxWidth: 500,
                        minWidth: 300,
                        border: 0,
                        bodyPadding: 10,
                        xtype: 'panel',
                        cls: 'ifresco-view-editmetadata-column2'
                    }]
                }]
            }]
        });

        this.callParent();
    },


    loadForm: function (data) {
    	console.log("LOAD FORM EDIT",data);
        var form = this.down('form[cls~=ifresco-view-editmetadata-form]');
        var column1 = form.down('panel[cls~=ifresco-view-editmetadata-column1]');
        var column2 = form.down('panel[cls~=ifresco-view-editmetadata-column2]');
        var tabs = form.down('tabpanel[cls~=ifresco-view-editmetadata-tabs]');
        if (tabs !== null) {
            tabs.destroy();
        }
        column1.removeAll();
        column2.removeAll();
        Ext.each(data.metaData.fields, function (field, index) {
            if (field.empty === true) {
                return;
            }
            var type = field.type || field.name.substr(field.name.indexOf('#') + 1);
            var fieldValue = '';
            Ext.Object.each(data.data, function (key, value) {
                if (key === field.name) {
                    fieldValue = value;
                }
            });
            var fieldEl = this.createField(field, fieldValue, data.config);
            if (index % 2) {
                column2.add(fieldEl);
            } else {
                column1.add(fieldEl);
            }
            // console.log(field);
            // switch (field.column) {
            //     case 1:
            //         column1.add(fieldEl);
            //         break;
            //     case 2:
            //         column2.add(fieldEl);
            //         break;
            //     default:
            //         column1.add(fieldEl);
            // }
        }, this);

        if (data.tabs && data.tabs.length > 0) {
            tabs = this.createTabPanel();
            var tabsItems = [];
            Ext.each(data.tabs, function (tab) {
                tabsItems.push(this.createTab(tab, data.config, data.data));
            }, this);
            tabs.items = tabsItems;
            form.add(tabs);
        }
    },

    
    
    trimTitle: function () {
    	this.title = Ext.util.Format.trim(Ext.util.Format.stripTags(this.title));
    	console.log("trim title",this.title);
        var tabLength = Ifresco.helper.Settings.get('TabTitleLength');
        var titleLength = parseInt(tabLength) || 0;
        if(titleLength > 0 && this.title.length > titleLength + 3) {
            this.title = Ifresco.helper.Translations.trans('Edit Metadata') + ": "+this.title.substring(0, titleLength) + '...';
        }
        else
        	this.title = Ifresco.helper.Translations.trans('Edit Metadata') + ": "+this.title;
    }

});
