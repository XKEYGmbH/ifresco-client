Ext.Loader.setConfig({
    enabled: true,
    paths: {
        'Ext.ux.upload': 'js/extjs/ux/upload',
        'Ext.ux.statusbar': 'js/extjs/ux/statusbar'
    }
});

Ext.require(['Ext.grid.*',
    'Ext.data.*',
    'Ext.util.*',
    'Ext.state.*',
    'Ext.ux.upload.Button',
    'Ext.ux.upload.plugin.Window']);



Ext.define('Ifresco.window.Upload', {
    extend: 'Ext.window.Window',
    alias: 'widget.ifrescoWindowUpload',
    requires: ['Ext.ux.upload.Basic',
                'Ext.ux.statusbar.StatusBar',
                'Ext.ux.statusbar.ValidationStatus'],
    disabled: true,

    constructor: function(config)
    {
        var me = this;
        config = config || {};
        Ext.applyIf(config.uploader, {
            //browse_button: config.id || Ext.id(me)
            drop_element: config.id || Ext.id(me),
            autoStart: false,
            max_file_size: '300mb',
            chunk_size : '2mb',
            runtimes : 'html5,flash,silverlight,browserplus',
            statusQueuedText: Ifresco.helper.Translations.trans('Ready to upload'),
            unique_names : false,
            statusUploadingText: Ifresco.helper.Translations.trans('Uploading ({0}%)'),
            statusFailedText: '<span style="color: red">'+Ifresco.helper.Translations.trans('Error')+'</span>',
            statusDoneText: '<span style="color: green">'+Ifresco.helper.Translations.trans('Complete')+'</span>',
            statusInvalidSizeText: Ifresco.helper.Translations.trans('File too large'),
            statusInvalidExtensionText: Ifresco.helper.Translations.trans('Invalid file type'),
            autoRemoveUploaded: false
        });

        me.callParent([config]);
    },

    initComponent: function()
    {

        var me = this,
            e;
        me.callParent();
        var uploader = me.uploader = me.createUploader();

        uploader.actions.startTransform = Ext.create('Ext.Action', {
            text: Ifresco.helper.Translations.trans('Upload & Transform'),
            disabled: true,
            handler: function() {
                var files = [];
                this.store.each(function(rec){
                    files.push(rec.get('name'));
                });

                if(files.length > 0) {
                    //transformFiles([], '', files, uploader); // TODO needs to be implemented
                    Ifresco.getApplication().getController("Index").transformFiles([], '', files, uploader);
                }
            },
            scope: uploader
        });

        uploader.actions.start.setText(Ifresco.helper.Translations.trans('Upload'));

        uploader.startTransformation = function(types) {
            uploader.uploader.settings.multipart_params = Ext.apply(uploader.uploader.settings.multipart_params, {transform: Ext.JSON.encode(types)});
            uploader.start();
        }

        me._dropboxFiles = [];

        me.uploadComposed = false;

        if(me.uploader.drop_element && (e = Ext.getCmp(me.uploader.drop_element)))
        {
            e.addListener('afterRender', function()
                {
                    me.uploader.initialize();
                },
                {
                    single: true,
                    scope: me
                });
        }
        else
        {
            me.listeners = {
                afterRender: {
                    fn: function()
                    {
                        me.uploader.initialize();
                    },
                    single: true,
                    scope: me
                }
            };
        }

        me.on({
            filesadded: {
                fn: function(uploader, files)
                {
                    uploader.actions.startTransform.setDisabled(false);

                    var isAutoStart = autoUploadControl.getValue();

                    me.uploadComposed = true;

                    if(isAutoStart) {
                        //uploader.start();
                        setTimeout(function () { uploader.start(); }, 100);
                    }
                },
                scope: me
            },
            storeempty: {
                fn: function(uploader, files)
                {
                    uploader.actions.startTransform.setDisabled(true);
                },
                scope: me
            },
            beforeupload: {
                fn: function(uploader, files)
                {
                    Ext.apply(uploader.uploader.settings.multipart_params, {type: typesControl.getValue()});
                },
                scope: me
            },
            beforestart: {
                fn: function(uploader, files)
                {

                },
                scope: me
            },
            uploadcomplete: {
                fn: function(uploader, files)
                {
                    me.sendDropboxFiles(uploader);
                },
                scope: me
            },
            afterRender: {
                fn: function(w)
                {
                    w.dockedItems.items[1].insert(0, uploader.actions.cancel);
                    if(uploader.autoStart == false)
                        w.dockedItems.items[1].insert(0, uploader.actions.start);
                        w.dockedItems.items[1].insert(1, uploader.actions.startTransform);
                },
                scope: me
            },
            updateprogress: {
                fn: function(uploader, total, percent, sent, success, failed, queued, speed)
                {
                    var t = Ext.String.format(Ifresco.helper.Translations.trans('Upload {0}% ({1} of {2})'), percent, sent, total);
                    me.statusbar.showBusy({
                        text: t,
                        clear: false
                    });
                },
                scope: me
            },
            uploadprogress: {
                fn: function(uploader, file, name, size, percent)
                {
                     me.statusbar.setText(name + ' ' + percent + '%');
                },
                scope: me
            },
            beforehide: {
                fn: function(w, e)
                {
                    if(!me.uploadComposed)
                        me.uploader.actions.removeAll.execute();
                },
                scope: me
            }
        });

        me.statusbar = Ext.create('Ext.ux.StatusBar', {
            dock: 'bottom',
            //id: 'form-statusbar',
            defaultText: Ifresco.helper.Translations.trans('Ready')
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
            fieldLabel: Ifresco.helper.Translations.trans('Content Type'),
            store: typesStore,
            queryMode: 'local',
            displayField:'title',
            valueField: 'name',
            width: 250,
            labelWidth: 75
        });

        me.typesControl = typesControl;

        var autoCloseControl = Ext.create('Ext.form.Checkbox', {
            fieldLabel: Ifresco.helper.Translations.trans('Auto Close'),
            checked: true,
            labelWidth: 65
        });

        var autoUploadControl = Ext.create('Ext.form.Checkbox', {
            fieldLabel: Ifresco.helper.Translations.trans('Auto Upload'),
            labelWidth: 70
        });

        me.autoCloseControl = autoCloseControl;

        me.view = Ext.create('Ext.grid.Panel', {
            store: uploader.store,
            stateful: true,
            multiSelect: true,
            hideHeaders: true,
            stateId: 'stateGrid',
            columns: [{
                text: Ifresco.helper.Translations.trans('Name'),
                flex: 1,
                sortable: false,
                dataIndex: 'name'
            },
                {
                    text: Ifresco.helper.Translations.trans('Size'),
                    width: 90,
                    sortable: true,
                    align: 'right',
                    renderer: Ext.util.Format.fileSize,
                    dataIndex: 'size'
                },
                {
                    text: Ifresco.helper.Translations.trans('Change'),
                    width: 75,
                    sortable: true,
                    hidden: true,
                    dataIndex: 'percent'
                },
                {
                    text: 'status',
                    width: 75,
                    hidden: true,
                    sortable: true,
                    dataIndex: 'status'
                },
                {
                    text: 'msg',
                    width: 175,
                    sortable: true,
                    dataIndex: 'msg'
                }],
            viewConfig: {
                stripeRows: true,
                enableTextSelection: false
            },
            tbar: [
                typesControl, '-', autoCloseControl, '-', autoUploadControl
            ],
            dockedItems: [{
                dock: 'top',
                enableOverflow: true,
                xtype: 'toolbar',
                style: {
                    background: 'transparent',
                    border: 'none',
                    padding: '5px 0'
                },
                items: [
                    uploader.actions.add,
                    {
                        xtype: 'button',
                        text: Ifresco.helper.Translations.trans('Add files'),
                        icon: uploadDropBoxImg,
                        handler: function(){
                            if(Dropbox)
                                Dropbox.choose({
                                    linkType: "direct",
                                    success: function(files) {
                                        Ext.Array.each(files, function(file, index, filesItSelf) {
                                            file.id = Ext.id();
                                            me._dropboxFiles.push(file);
                                            me.uploader.actions.startTransform.setDisabled(false);
                                            me.uploadComposed = true;

                                            var additional = {};

                                            if(!me.isTypeAllowed(file.name))
                                            {
                                                additional = {
                                                    msg: Ext.String.format('<span style="color: red">{0}</span>', me.uploader.statusInvalidExtensionText),
                                                    status: 4
                                                };
                                            }

                                            me.uploader.store.add(Ext.apply({
                                                id: file.id,
                                                name: file.name,
                                                size: file.bytes,
                                                status: 1,
                                                percent: 0,
                                                loaded: 0,
                                                msg: me.uploader.statusQueuedText
                                            }, additional));

                                            me.uploader.updateProgress();
                                        }, this);

                                        if(files.length > 0) {
                                            me.uploader.actions.removeUploaded.setDisabled(false);
                                            me.uploader.actions.removeAll.setDisabled(false);
                                            me.uploader.actions.start.setDisabled(false);
                                        }
                                    }
                                });
                        }
                    }
                ],
                listeners: {
                    beforerender: function(toolbar)
                    {
                        //toolbar.add(uploader.actions.add);
                        /*if(uploader.autoStart == false)
                            toolbar.add(uploader.actions.start);*/
                        //toolbar.add(uploader.actions.cancel);
                        toolbar.add(uploader.actions.removeAll);
                        if(uploader.autoRemoveUploaded == false)
                            toolbar.add(uploader.actions.removeUploaded);

                        typesStore.load();
                    },
                    scope: me
                }
            },
                me.statusbar],
            listeners: {
                itemcontextmenu: function(gridx, record, item, index, event, eOpts){
                    event.stopEvent();
                    var mnxContext = Ext.create('Ext.menu.Menu', {
                        items: [
                            {
                                iconCls: 'ifresco-icon-cancel',
                                text: Ifresco.helper.Translations.trans('Remove'),
                                scope:this,
                                handler: function(){
                                    var selected = gridx.getSelectionModel().getSelection();

                                    Ext.Array.each(selected, function(rec, index, recordsItSelf) {

                                        var file = me.uploader.uploader.getFile(rec.data.id);

                                        if(file)
                                            me.uploader.uploader.removeFile(file);
                                        else
                                            me.uploader.store.remove(rec);

                                    }, this);

                                }
                            }
                        ]
                    });
                    mnxContext.showAt(event.xy);
                },
                close: function(panel, e) {
                    panel.destroy();
                }
            }
        });

        me.uploader.browse_button = me.view.dockedItems.items[1].items.items[0].id;

        me.insert(0, me.view );

        me.relayEvents(me.uploader, ['beforestart',
            'uploadready',
            'uploadstarted',
            'uploadcomplete',
            'uploaderror',
            'filesadded',
            'beforeupload',
            'fileuploaded',
            'updateprogress',
            'uploadprogress',
            'storeempty']);
    },

    /**
     * @private
     */
    createUploader: function()
    {
        return Ext.create('Ext.ux.upload.Basic', this, Ext.applyIf({
            listeners: {}
        }, this.initialConfig));
    },

    sendDropboxFiles: function(uploader) {

        var panel = this;
        var allFiles = this._dropboxFiles.slice(0);
        var type = panel.typesControl.getValue();

        Ext.Array.each(allFiles, function(file, index, filesItSelf) {
            var record = uploader.store.getById(file.id);
            if(record && record.get('status') == 1) {
                record.set('status', 2);
                record.commit();
                Ext.Ajax.request({
                    url: uploader.uploader.settings.url,
                    params: {
                        dropboxFile: Ext.JSON.encode(file),
                        transform: uploader.uploader.settings.multipart_params.transform,
                        type: type
                    },
                    success: function(response){
                        var data = Ext.JSON.decode(response.responseText);
//                        var data = $.JSON.decode(response.responseText);
                        record.set('status', 5);
                        record.commit();

                        panel._dropboxFiles.splice(index-(allFiles.length - panel._dropboxFiles.length),1);

                        if(panel._dropboxFiles.length == 0) {
                            panel.fireEvent('uploadcomplete', uploader, []);
                        }
                    }
                });
            }
            else {
                panel._dropboxFiles.splice(index-(allFiles.length - panel._dropboxFiles.length),1);
            }
        }, this);

        uploader.uploader.settings.multipart_params.transform = null;

    },

    isTypeAllowed: function(file) {
        var types = this.uploader.filters[0].extensions.toUpperCase().split(',');

        var ext = /\.(.+)$/.exec(file)[1] || '';

        return types.indexOf(ext.toUpperCase()) > -1;
    }
});


Ext.define('Ext.ux.upload.IfsButton', {
    extend:'Ext.ux.upload.Button',
    alias: 'widget.ifsmultiupload',




    initComponent: function() {
        var me = this;

        me.callParent();
    },

    constructor:function(cnfg){
        this.callParent(arguments);//Calling the parent class constructor
        this.initConfig(cnfg);//Initializing the component
    }


});


