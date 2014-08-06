Ext.define('Ifresco.button.Sane', {
    extend: 'Ext.button.Button',
    alias: 'widget.ifrescoButtonSane',
    requires: [],

    constructor: function(config) {
        var me = this;
        config = config || {};
        Ext.applyIf(config, {

        });
        me.callParent([config]);

        me.scanWas = false;
    },

    initComponent: function() {
        var me = this, e;
        me.callParent();

        me.on({
            click: function(button, e, eOpts) {
                if(me.nodeId) {
                    me.scannerForm();
                }
            }
        });

    },

    /**
     * @private
     */

    scannerForm: function() {
        var me = this;
        var profilesStore__ = Ext.create('Ext.data.Store', {
            model: 'ContentTypesStoreModel',
            proxy : {
                type: 'ajax',
                url: Routing.generate('ifresco_client_scan_get_scanner_profiles'),
                actionMethods: {
                    read: 'GET'
                },
                timeout : 1200000,
                reader: {
                    type: 'json',
                    idProperty:'name',
                    remoteGroup:true,
                    remoteSort:true,
                    root: 'data'
                }
            },
            listeners: {
                load: function(t, records, successful, eOpts) {
                    if(records.length > 0) {
                        profilesControl.setValue(records[0]);
                    }
                }
            }
        });

        var profilesControl = Ext.create('Ext.form.ComboBox', {
            fieldLabel: 'Scanners',
            forceSelection: true,
            store: Ifresco.store.ScannerProfile.create({autoLoad: true}),
            queryMode: 'local',
            margin: '30 5 5 0',
            displayField:'title',
            valueField: 'name',
            name: 'device',
            value: '', // saneProfilesStore.data.items[0] || '',
            width: 300
        });

        Ext.define('ContentTypesStoreModel', {
            extend: 'Ext.data.Model',
            fields: ['name', 'title', 'description']
        });

        var typesStore = Ext.create('Ext.data.Store', {
            model: 'ContentTypesStoreModel',
            proxy : {
                type: 'ajax',
                url: Routing.generate('ifresco_client_upload_content_types_get'),
                actionMethods: {
                    read: 'GET'
                },
                timeout : 1200000,
                reader: {
                    type: 'json',
                    idProperty:'name',
                    remoteGroup:true,
                    remoteSort:true,
                    root: 'data'
                }
            }
        });

        var typesControl = Ext.create('Ext.form.ComboBox', {
            fieldLabel: 'Content Type',
            store: typesStore,
            queryMode: 'local',
            displayField:'title',
            valueField: 'name',
            name: 'type',
            width: 400
        });

        typesStore.load();

        var autoCloseControl = Ext.create('Ext.form.Checkbox', {
            fieldLabel: 'Auto Close',
            checked: true,
            labelWidth: 65
        });

        var qualityControl = Ext.create('Ext.form.field.Number', {
            fieldLabel: 'Quality',
            width: 200,
            name: 'quality',
            hidden: true,
            value: '90',
            maxValue: 100,
            minValue: 0
        });

        me.scanWindow = Ext.create('Ext.window.Window', {
            modal:true,
            constrain: true,
            title: 'Scan into ' + me.folder,
            plain: true,
            resizable: false,
            layout:'fit',
            width:900,
            height:730,
            closeAction:'destroy',
            listeners: {
                close: function() {
                    $('img#scaneArea').imgAreaSelect({ instance: true }).cancelSelection();
                }
            },
            buttons: [
                {
                    text: 'Close',
                    handler: function() {
                        //me.scanWindow.close();
                    	this.scanWindow.close(this); // TODO - close does not work always fix this
                    },
                    scope: this
                }
            ],
            items: Ext.create('Ext.form.Panel', {
                bodyPadding: 10,
                border: false,
                defaultType: 'textfield',
                defaults: {
                    anchor: '100%',
                    labelAlign: 'top',
                    margin: '5 5 5 5',
                    labelStyle: {
                        fontWeigh: 'bold'
                    }
                },
                listeners: {
                    afterrender: function(form, e) {
                        var data = Ifresco.helper.Registry.get('saneParameters') || false;

                        if(data) {
                            form.getForm().setValues(data);
                        }
                    }
                },
                items: {
                    xtype: 'container',
                    layout: 'hbox',
                    items: [
                        {
                            xtype: 'container',
                            width: 420,
                            items: [
                                {
                                    fieldLabel: Ifresco.helper.Translations.trans('Document name'),
                                    xtype: 'textfield',
                                    allowBlank: false,
                                    width: 400,
                                    name: 'name',
                                    value: 'New_Document'
                                },
                                typesControl,
                                profilesControl,
                                {
                                    fieldLabel: Ifresco.helper.Translations.trans('Format'),
                                    xtype: 'combo',
                                    name: 'format',
                                    width: 200,
                                    //store: ['TIFF', 'PDF', 'JPG'],
                                    store: ['PDF', 'JPEG', 'TIFF'],
                                    value: 'PDF',
                                    listeners: {
                                        change: function(t, newValue, oldValue, eOpts) {

                                            if(newValue == 'JPEG') {
                                                qualityControl.show();
                                            }
                                            else {
                                                qualityControl.hide();
                                            }
                                        }
                                    }
                                },
                                qualityControl,
                                {
                                    fieldLabel: Ifresco.helper.Translations.trans('Mode'),
                                    xtype: 'combo',
                                    width: 200,
                                    name: 'mode',
                                    store: ['Gray', 'Color', 'Lineart'],
                                    value: 'Gray'
                                },
                                {
                                    fieldLabel: Ifresco.helper.Translations.trans('Resolution'),
                                    xtype: 'combo',
                                    name: 'resolution',
                                    width: 200,
                                    store: [100, 150, 200, 300],
                                    value: 100
                                },
                                autoCloseControl,
                                {
                                    xtype: 'button',
                                    text: 'Scan & Save',
                                    margin: '5 0 0 0',
                                    handler: function() {

                                        var params = me.scanWindow.items.items[0].getValues();

                                        params.nodeId = me.nodeId;
                                        var area = $('img#scaneArea').imgAreaSelect({ instance: true }).getSelection();

                                        params.top = area.y1;
                                        params.x = area.width
                                        params.left = area.x1;
                                        params.y = area.height;

                                        //console.log(params);

                                        if(!params.device) {
                                            Ext.MessageBox.show({
                                                title: Ifresco.helper.Translations.trans('No scanner'),
                                                msg: Ifresco.helper.Translations.trans('You have not selected a scanner profile.'),
                                                buttons: Ext.MessageBox.OK,
                                                icon: Ext.MessageBox.INFO
                                            });

                                            return;
                                        }

                                        var loadMask = new Ext.LoadMask(me.scanWindow, {msg:Ifresco.helper.Translations.trans("Scan in progress...")});
                                        loadMask.show();
                                        Ext.Ajax.request({
                                            url: Routing.generate('ifresco_client_scan_save'),
                                            params: params,
                                            success: function(response){
                                                var data = $.JSON.decode(response.responseText);
                                                loadMask.destroy();

                                                if(data.success) {
                                                    Ifresco.helper.Registry.set('saneParameters', params);
                                                    Ifresco.helper.Registry.save();

                                                    me.scanWas = true;

                                                    var src = Routing.generate('ifresco_client_scan_get_scanned_image')+'?file='+
                                                        data.tmpfile || '' + '&format=jpeg';

                                                    //window.open(src);

                                                    if (autoCloseControl.getValue()) {
                                                        me.scanWindow.close();
                                                    }
                                                }
                                                else {
                                                    Ext.MessageBox.show({
                                                        title: Ifresco.helper.Translations.trans('Scanner error'),
                                                        msg: Ifresco.helper.Translations.trans('An error has occured. Check your scanner.'),
                                                        buttons: Ext.MessageBox.OK,
                                                        icon: Ext.MessageBox.INFO
                                                    });
                                                }



                                            },
                                            timeout: 1000 * 60 * 2
                                        });
                                    }
                                }
                            ]
                        },
                        {
                            xtype: 'container',
                            width: 450,
                            items: [
                                {
                                    xtype: 'label',
                                    text: Ifresco.helper.Translations.trans('Image scanned')
                                },
                                {
                                    xtype: 'container',
                                    html: '<img id="scaneArea" width=430 height=594 src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" /> ',
                                    width: 430,
                                    style: 'border: 1px solid black',
                                    height: 594,
                                    border: true,
                                    margin: '5 0 0 0'
                                },
                                {
                                    xtype: 'button',
                                    text: Ifresco.helper.Translations.trans('Scan Preview'),
                                    margin: '5 0 0 0',
                                    handler: function() {

                                        var scanner = profilesControl.getValue();

                                        if(!scanner) {
                                            Ext.MessageBox.show({
                                                title: Ifresco.helper.Translations.trans('No scanner'),
                                                msg: Ifresco.helper.Translations.trans('You have not selected a scanner profile.'),
                                                buttons: Ext.MessageBox.OK,
                                                icon: Ext.MessageBox.INFO
                                            });

                                            return;
                                        }

                                        var loadMask = new Ext.LoadMask(me.scanWindow, {msg:Ifresco.helper.Translations.trans("Initial scan in progress...")});
                                        loadMask.show();
                                        Ext.Ajax.request({
                                            url: Routing.generate('ifresco_client_scan_initial_scan'),
                                            params: {
                                                device: scanner
                                            },
                                            success: function(response){
                                                var data = $.JSON.decode(response.responseText);
                                                loadMask.destroy();

                                                if(data.success) {
                                                    Ext.get('scaneArea').dom.src = Routing.generate('ifresco_client_scan_get_scanned_image')+'?file='+
                                                        data.tmpfile || '' + '&format=jpeg'
                                                }
                                                else {
                                                    Ext.MessageBox.show({
                                                        title: Ifresco.helper.Translations.trans('Scanner error'),
                                                        msg: Ifresco.helper.Translations.trans('An error has occured. Check your scanner.'),
                                                        buttons: Ext.MessageBox.OK,
                                                        icon: Ext.MessageBox.INFO
                                                    });
                                                }
                                            },
                                            timeout: 1000 * 60 * 2
                                        });
                                    }
                                }
                            ]
                        }
                    ]
                }
            })
        });

        me.scanWindow.show();
        $('img#scaneArea').imgAreaSelect({
            handles: true,
            onSelectEnd: function(image, params) {
                console.log(params)
            }
        });
    }
});