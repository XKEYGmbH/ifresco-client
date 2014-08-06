function editMetadata(nodeId,nodeName) {

    var tabnodeid = nodeId.replace(/-/g,"");

    if(tabExists('metadatatab-'+tabnodeid)) {
        setActive('metadatatab-'+tabnodeid);
        return;
    }

    addTabDynamic('metadatatab-'+tabnodeid, nodeName, 'view-metadata');

    $.ajax({
        cache: false,
        url : "<?php echo $view['router']->generate('ifrescoClientMetadataBundle_homepage') ?>",
        data: ({'nodeId' : nodeId}),


        success : function (data) {
            $("#overAll").unmask();
            $("#metadatatab-"+tabnodeid).html(data);
        },
        beforeSend: function(req) {
            $("#overAll").mask("<?php echo $view['translator']->trans('Loading'); ?> "+nodeName+"...",300);
        }
    });

}

var winSpecifyType = null;
var SpecifyTypeStore = null;
var SpecifyTypeCombo = null;
var SpecifyTypeSelectedId = null;

function specifyType(myNodeId) {
    if (myNodeId !== null & typeof myNodeId !== 'undefined') {
        if (myNodeId.length > 0) {
            var multiNodes = false;
            if (myNodeId.length > 1)
                multiNodes = true;

            if (SpecifyTypeStore === null) {
                SpecifyTypeStore = Ext.create('Ext.data.JsonStore', {
                    autoDestroy: true,
                    proxy: {
                        type: 'ajax',
                        url: '<?php echo $view['router']->generate('ifrescoClientMetadataBundle_contenttypelist') ?>',
                        reader: {
                            root: 'types',
                            idProperty: 'name'
                        }
                    },
                    storeId: 'SpecifyTypeStore',
                    fields: ['name', 'title','description'],
                    listeners: {
                        'beforeload':{
                            fn:function() {
                                $("#specifytype-window").mask("<?php echo $view['translator']->trans('Loading...'); ?>",300);
                            }
                        },
                        'load':{
                            fn:function() {
                                $("#specifytype-window").unmask();
                            }
                        }
                    }
                });

                SpecifyTypeCombo = Ext.create('Ext.form.ComboBox', {
                    store: SpecifyTypeStore,
                    displayField:'title',
                    typeAhead: true,
                    id:'SpecifyTypeCombo',
                    queryMode: 'local',
                    triggerAction: 'all',
                    deferEmptyText: false,
                    emptyText:'<?php echo $view['translator']->trans('Select a content type...'); ?>',
                    selectOnFocus:true,
                    listeners:{
                        'select': function(combo,record,index) {

                            if (typeof record !== 'undefined')
                                SpecifyTypeSelectedId = record[0].data.name;
                            else
                                SpecifyTypeSelectedId = null;
                        }
                    }
                });
            }

            winSpecifyType = Ext.create('Ext.window.Window', {

                modal:true,
                contentEl:'specifytype-window',
                layout:'fit',
                width:350,
                height:100,
                closeAction:'hide',
                title:'<?php echo $view['translator']->trans('Specify Type'); ?>',
                plain: true,
                constrain: true,

                listeners:{
                    'beforeshow':{
                        fn:function() {
                            $(".PDFRenderer").hide();
                        }
                    },
                    'hide': {
                        fn:function() {
                            $(".PDFRenderer").show();
                        }
                    }
                },

                items: [
                    SpecifyTypeCombo
                ],
                buttons: [{
                    text:'<?php echo $view['translator']->trans('Save'); ?>',
                    disabled:false,
                    handler:function() {
                        var jsonNodes = $.toJSON(myNodeId);

                        $("#documentGrid").mask("<?php echo $view['translator']->trans('Loading...'); ?>",300);
                        var type = SpecifyTypeSelectedId;

                        if (type !== null && typeof type !== 'undefined') {
                            $("#specifytype-window").mask("<?php echo $view['translator']->trans('Loading...'); ?>",300);
                            $.post("<?php echo $view['router']->generate('ifrescoClientMetadataBundle_savecontenttype') ?>", "nodes="+jsonNodes+"&type="+type, function(data) {
                                var succes = data.success;

                                if (succes === false) {
                                    $(".PDFRenderer").hide();
                                    Ext.MessageBox.show({
                                        title: '<?php echo $view['translator']->trans('Specify content type error!'); ?>',
                                        msg: '<?php echo $view['translator']->trans('An error occured please try it later again.'); ?>',
                                        buttons: Ext.MessageBox.OK,
                                        icon: Ext.MessageBox.ERROR,
                                        fn:showRenderer
                                });
                            }
                            $("#documentGrid").unmask();
                            $("#specifytype-window").unmask();
                        }, "json");
                        winSpecifyType.hide(this);
                    }
                }
            },{
                text: '<?php echo $view['translator']->trans('Close'); ?>',
                handler: function(){
                    winSpecifyType.hide(this);

                }
            }]
        });

        SpecifyTypeStore.load({params:{'nodeId':myNodeId}});
        winSpecifyType.show();
    }
}
}

var winAspects = null;

function manageAspects(myNodeId) {

    if (myNodeId !== null & typeof myNodeId !== 'undefined') {
        if(!winAspects) {
            winAspects = Ext.create('Ext.window.Window', {
                modal:true,
                //renderTo:'aspects-window',
                layout:'fit',
                width:516,
                height:417,
                closeAction:'hide',
                title:'<?php echo $view['translator']->trans('Add Aspect'); ?>',
                plain: true,
                constrain: true,
                id:'aspects-window',

                items: Ext.create('Ext.panel.Panel', {
                    id: 'aspects-window-panel',
                    layout:'fit',
                    border:false
                }),
                listeners:{
                    'beforeshow':{
                        fn:function() {
                            $(".PDFRenderer").hide();
                        }
                    },
                    'hide': {
                        fn:function() {
                            $(".PDFRenderer").show();

                            $("#aspects-window-panel").html('');
                        }
                    }
                },

                buttons: [{
                    text: '<?php echo $view['translator']->trans('Save'); ?>',
                    handler: function() {
                        var postData = getSelectedAspects();

                        $("#aspects-window").mask("<?php echo $view['translator']->trans('Loading...'); ?>",300);
                        $.post("<?php echo $view['router']->generate('ifrescoClientMetadataBundle_saveaspects') ?>", postData, function(data) {
                            var succes = data.success;
                            var nodeId = data.nodeId;

                            $("#aspects-window").unmask();
                        }, "json");

                        winAspects.hide(this);

                    }
                },
                    {
                        text: '<?php echo $view['translator']->trans('Close'); ?>',
                        handler: function() {
                            $("#aspects-window-panel").html('');
                            winAspects.hide(this);
                        }
                    }]
            });

        }
        else {
        }

        fillAspectsWindow(myNodeId);
        winAspects.show();
    }
}

function fillAspectsWindow(myNodeId) {

    $.ajax({
        cache: false,
        url : "<?php echo $view['router']->generate('ifrescoClientMetadataBundle_addaspects') ?>",
        data: 'nodeId='+myNodeId,

        success : function (data) {
            $("#aspects-window").unmask();
            $("#aspects-window-panel").html(data);
        },
        beforeSend : function() {

            $("#aspects-window").mask("<?php echo $view['translator']->trans('Loading...'); ?>",300);
            $("#aspects-window-panel").html('');
        }
    });
}

function quickAddAspect(myNodeId,aspect) {
    var postData = "nodeId="+myNodeId+"&aspects="+aspect;
    $.post("<?php echo $view['router']->generate('ifrescoClientMetadataBundle_saveaspects') ?>", postData, function(data) {
        var succes = data.success;
        var nodeId = data.nodeId;
    }, "json");
}

function sendMailLink(nodes) {
    var body = [];
    if(nodes.length > 0) {
        for(var i=0; i < nodes.length; i++) {
            var urlOf = '';
            if(nodes[i].type == "{http://www.alfresco.org/model/content/1.0}folder") {
                urlOf = "<?php echo $view['router']->generate('ifrescoClientDashletsBundle_homepage',array(),true) ?>#folder/workspace://SpacesStore/"+nodes[i].nodeId;
            } else {
                urlOf = "<?php echo $view['router']->generate('ifrescoClientDashletsBundle_homepage',array(),true) ?>#document/workspace://SpacesStore/"+nodes[i].nodeId;
            }
            body.push(urlOf);
        }
        body = body.join('%0d%0a');
        document.location.href = 'mailto:?body='+body;
    }
}

function insertSpace(field) {
    var new_line = '';
    new_line = field.value.substring(0, field.selectionStart);
    new_line += ' ';
    new_line += field.value.substring(field.selectionEnd, field.value.length);
    var new_pos = field.selectionStart + 1;
    field.value = new_line;
    field.selectionStart = field.selectionEnd = new_pos;
}

function addTreeNode(parentNodeId, value) {

    if(value.length > 0) {

        $.ajax({
            cache: false,
            url : "<?php echo $view['router']->generate('ifrescoClientCategoryTreeBundle_addcategory') ?>",
            data: {nodeId: parentNodeId, value: value},
            dataType:"json",

            success : function (data) {
                $("#categoriesTree").unmask();

                if (data.success == true) {
                    return true;
                }
                else {
                    return false;
                }
            },
            error: function() {

                return false;
            },
            beforeSend: function(req) {
                $("#categoriesTree").mask("<?php echo $view['translator']->trans('Add category'); ?> <b>"+value+"</b>...",300);
            }
        });
    }
}

function editTreeNode(nodeId, value, oldValue) {

    $.ajax({
        cache: false,
        url : "<?php echo $view['router']->generate('ifrescoClientCategoryTreeBundle_editcategory') ?>",
        data: {nodeId: nodeId, value: value},
        dataType:"json",

        success : function (data) {
            $("#categoriesTree").unmask();

            if (data.success == true) {
                firedContextMenuEditing = false;
                return true;
            }
            else {
                firedContextMenuEditing = false;

            }
        },
        error: function() {

            $("#categoriesTree").unmask();
        },
        beforeSend: function(req) {
            $("#categoriesTree").mask("<?php echo $view['translator']->trans('Rename'); ?> <b>"+oldValue+"</b> <?php echo $view['translator']->trans('to'); ?> <b>"+value+"</b>...",300);
        }
    });
}

function transformFiles(nodes, container, files, uploader) {

    container = container == undefined ? '' : container;
    files = files || false;
    uploader = uploader || false;

    var transformNodes = Ext.create('Ext.window.Window', {
        modal:true,
        width: 900,
        height: 450,
        closeAction:'destroy',
        constrain: true,
        title:'<?php echo $view['translator']->trans('Transformation Settings'); ?>',
        resizable: false,
        autoShow: true,
        filterThis: function (b1, b2) {
            var itemselector = Ext.getCmp('itemselector-field');

            itemselector.store.clearFilter();

            if(!b1.pressed && !b2.pressed) {
                itemselector.store.filterBy(
                    function(record) {
                        return false;
                    }
                )
            } else if(b1.pressed && !b2.pressed) {
                itemselector.store.filterBy(
                    function(record) {
                        return record.get('engine') != 'false'
                    }
                )
            } else if(!b1.pressed && b2.pressed) {
                itemselector.store.filterBy(
                    function(record) {
                        return record.get('engine') == 'false'
                    }
                )
            }

            itemselector.bindStore(itemselector.store);
        },
        listeners: {
            show: function(t, eOpts) {

                $("#"+t.id).mask('<?php echo $view['translator']->trans('Loading...'); ?>',300);
                Ext.Ajax.request({
                    url: '<?php echo $view['router']->generate('ifrescoClientNodeActionsBundle_listAvailableTransformations') ?>',
                    params: {
                        'nodeId[]': nodes,
                        'files[]': files
                    },
                    success: function(response){
                        $("#"+t.id).unmask();
                        var defaultVal = [];
                        var mimes = Ext.JSON.decode(response.responseText);
                        mimes = mimes.mimetypes || false;
                        if (mimes) {
                            if(Ext.Array.pluck(mimes, 'mimetype').indexOf("application/pdf") > -1)
                                defaultVal = ["application/pdf"];
                        }
                        else {
                            mimes = [];
                        }

                        Ext.define('MimesModel', {
                            extend: 'Ext.data.Model',
                            fields: [
                                {name: 'extension', type: 'string'},
                                {name: 'mimetype',  type: 'string'},
                                {name: 'fullName',  type: 'string'},
                                {name: 'engine',  type: 'string'},
                                {name: 'name', type: 'string'}
                            ]
                        });

                        var mimesStore = Ext.create('Ext.data.Store', {
                            model: 'MimesModel',
                            data: mimes
                        });

                        Ext.require('Ext.ux.form.ItemSelector', function(){
                            transformNodes.add(
                                Ext.create('Ext.form.Panel', {
                                    bodyPadding: 10,
                                    border: false,
                                    height: 400,
                                    defaultType: 'textfield',
                                    defaults: {
                                        anchor: '100%',
                                        labelAlign: 'top',
                                        labelStyle: {
                                            fontWeigh: 'bold'
                                        }
                                    },
                                    api: {
                                        submit: function(formHTML, func, formPanel, action){
                                            var values = formPanel.form.getFieldValues();

                                            if(files && files.length > 0) {
                                                uploader.startTransformation(values);
                                                transformNodes.close();
                                                return;
                                            }

                                            values['nodeId[]'] = nodes;

                                            $("#"+formPanel.form.owner.id).mask('<?php echo $view['translator']->trans('Transformation...'); ?>',300);

                                            Ext.Ajax.request({
                                                url: "<?php echo $view['router']->generate('ifrescoClientNodeActionsBundle_doNodeTransformation') ?>",
                                                params: values,
                                                success: function(response){
                                                    $("#"+formPanel.form.owner.id).unmask();

                                                    var data = $.JSON.decode(response.responseText);

                                                    if(data.transformations && data.transformations.length > 0) {
                                                        transformNodes.close();

                                                        var msg = '<?php echo $view['translator']->trans('Following files were processed'); ?>: <br /><br />';

                                                        var transItem;
                                                        for (var i = 0; i < data.transformations.length; i++) {
                                                            transItem = data.transformations[i];
                                                            if(transItem.successfull) {
                                                                msg += transItem.name+' - <b><?php echo $view['translator']->trans('Done'); ?></b><br />';
                                                            }
                                                            else {
                                                                msg += transItem.targetMimetype+' - <b><?php echo $view['translator']->trans('Not done'); ?></b> ('+transItem.message+')<br />';
                                                            }
                                                        }

                                                        Ext.MessageBox.show({
                                                            title: '<?php echo $view['translator']->trans('Transformation result'); ?>',
                                                            msg: msg,
                                                            buttons: Ext.MessageBox.OK,
                                                            icon: Ext.MessageBox.INFO
                                                        });
                                                    }

                                                    if(eval('typeof refreshGrid'+container) == 'function') {
                                                        eval('refreshGrid'+container+'()');
                                                    }
                                                }
                                            });
                                        }
                                    },
                                    items: [
                                        /*{
                                         fieldLabel: 'Choose your primary transformation',
                                         xtype: 'combobox',
                                         store: mimesStore,
                                         displayField: 'fullName',
                                         valueField: 'mimetype',
                                         queryMode: 'local',
                                         allowBlank: false,
                                         name: 'targetMimetype'
                                         },*/
                                        {
                                            xtype: 'itemselector',
                                            name: 'additionals[]',
                                            id: 'itemselector-field',
                                            fieldLabel: 'Choose your additional transformations',
                                            imagePath: '/js/extjs4/ux/css/images',
                                            store: mimesStore,
                                            displayField: 'fullName',
                                            valueField: 'mimetype',
                                            value: defaultVal,
                                            msgTarget: 'side',
                                            fromTitle: 'Supported transformations',
                                            toTitle: 'Selected transformations',
                                            height: 300
                                        },
                                        {
                                            xtype: 'container',
                                            items: [
                                                {
                                                    xtype: 'button',
                                                    enableToggle: true,
                                                    text: '<?php echo $view['translator']->trans('AutoOCR'); ?>',
                                                    pressed: true,
                                                    handler: function(b, e) {
                                                        var alfStdr = b.nextSibling();
                                                        transformNodes.filterThis(b, alfStdr);
                                                    }
                                                },
                                                {
                                                    xtype: 'button',
                                                    margin: '0 0 0 20',
                                                    enableToggle: true,
                                                    text: '<?php echo $view['translator']->trans('Alfresco Standard'); ?>',
                                                    pressed: true,
                                                    handler: function(b, e) {
                                                        var autoOCR = b.previousSibling();
                                                        transformNodes.filterThis(autoOCR, b);
                                                    }
                                                }
                                            ]
                                        },
                                        {
                                            xtype: 'checkbox',
                                            name: 'overwriteSourceNode',
                                            fieldLabel: 'Replace the source document',
                                            labelAlign: 'left',
                                            labelWidth: 200
                                        },
                                        {
                                            xtype: 'checkbox',
                                            name: 'overwriteTargetNode',
                                            fieldLabel: 'Replace target documents',
                                            labelAlign: 'left',
                                            labelWidth: 200
                                        }
                                    ]
                                })
                            )
                        });
                    }
                });
            }
        },
        buttons: [
            {
                text: '<?php echo $view['translator']->trans('Transform'); ?>',
                handler: function() {
                    var form = transformNodes.items.items[0];
                    form.submit();
                }
            },
            {
                text: '<?php echo $view['translator']->trans('Close'); ?>',
                handler: function() {
                    transformNodes.close();
                }
            }
        ]
    });

    return transformNodes;
}

function createHTMLdoc(nodeId, container) {

    container = container == undefined ? nodeId.replace(/-/g, '') : container;

    var createHTMLdoc = Ext.create('Ext.window.Window', {
        modal:true,
        layout:'fit',
        width: 900,
        height: 550,
        closeAction:'destroy',
        constrain: true,
        title:'<?php echo $view['translator']->trans('Create HTML'); ?>',
        plain: true,
        resizable: false,
        autoShow: true,
        items: Ext.create('Ext.panel.Panel', {
            height: 500,
            items: [
                Ext.create('Ext.form.Panel', {
                    border: false,
                    bodyPadding: 10,
                    border:false,
                    height: 500,
                    defaultType: 'textfield',
                    defaults: {
                        anchor: '100%'
                    },
                    api: {
                        submit: function(formHTML, func, formPanel, action){
                            var values = formPanel.form.getValues();
                            values.nodeId = nodeId;

                            Ext.Ajax.request({
                                url: "<?php echo $view['router']->generate('ifrescoClientNodeActionsBundle_createhtml') ?>",
                                params: values,
                                success: function(response){
                                    var data = $.JSON.decode(response.responseText);

                                    if(data.success == true) {
                                        createHTMLdoc.close();
                                        Ext.MessageBox.show({
                                            title: '<?php echo $view['translator']->trans('Success'); ?>',
                                            msg: '<?php echo $view['translator']->trans('File has been created'); ?>',
                                            buttons: Ext.MessageBox.OK,
                                            icon: Ext.MessageBox.INFO
                                        });


                                        if(eval('typeof refreshGrid'+container) == 'function') {
                                            eval('refreshGrid'+container+'()');
                                        }

                                    } else {
                                        Ext.MessageBox.show({
                                            title: '<?php echo $view['translator']->trans('Success'); ?>',
                                            msg: '<?php echo $view['translator']->trans('Error has been occurred'); ?>',
                                            buttons: Ext.MessageBox.OK,
                                            icon: Ext.MessageBox.ERROR
                                        });
                                    }
                                }
                            });
                        }
                    },
                    items: [{
                        fieldLabel: '<?php echo $view['translator']->trans('Name'); ?>',
                        allowBlank: false,
                        name: 'name'
                    },{
                        fieldLabel: '<?php echo $view['translator']->trans('Title'); ?>',
                        name: 'title'
                    },{
                        fieldLabel: '<?php echo $view['translator']->trans('Description'); ?>',
                        name: 'description',
                        xtype: 'textareafield'
                    },{
                        fieldLabel: '<?php echo $view['translator']->trans('Content'); ?>',
                        name: 'content',
                        height: 300,
                        xtype: 'htmleditor'
                    }]
                })
            ]
        }),

        listeners:{
        },

        buttons: [{
            text: '<?php echo $view['translator']->trans('Save'); ?>',
            handler: function() {
                createHTMLdoc.items.items[0].items.items[0].getForm().submit()
            }
        },
            {
                text: '<?php echo $view['translator']->trans('Close'); ?>',
                handler: function() {
                    createHTMLdoc.close();
                }
            }]
    });
}

function editHTMLdoc(nodeId, container) {

    container = container == undefined ? nodeId.replace(/-/g, '') : container;

    var editHTMLdoc = Ext.create('Ext.window.Window', {
        modal:true,
        layout:'fit',
        width: 900,
        height: 550,
        closeAction:'destroy',
        constrain: true,
        title:'<?php echo $view['translator']->trans('Inline Edit'); ?>',
        plain: true,
        resizable: false,
        autoShow: true,
        items: Ext.create('Ext.panel.Panel', {
            height: 500,
            items: [
                Ext.create('Ext.form.Panel', {
                    border: false,
                    bodyPadding: 10,
                    border:false,
                    height: 500,
                    defaultType: 'textfield',
                    defaults: {
                        anchor: '100%'
                    },
                    api: {
                        submit: function(formHTML, func, formPanel, action){
                            var values = formPanel.form.getValues();
                            values.nodeId = nodeId;

                            Ext.Ajax.request({
                                url: "<?php echo $view['router']->generate('ifrescoClientNodeActionsBundle_edithtml') ?>",
                                params: values,
                                success: function(response){
                                    var data = $.JSON.decode(response.responseText);

                                    if(data.success == true) {
                                        editHTMLdoc.close();
                                        Ext.MessageBox.show({
                                            title: '<?php echo $view['translator']->trans('Success'); ?>',
                                            msg: '<?php echo $view['translator']->trans('Changes were saved'); ?>',
                                            buttons: Ext.MessageBox.OK,
                                            icon: Ext.MessageBox.INFO
                                        });


                                        if(eval('typeof refreshGrid'+container) == 'function') {
                                            eval('refreshGrid'+container+'()');
                                        }

                                    } else {
                                        Ext.MessageBox.show({
                                            title: '<?php echo $view['translator']->trans('Success'); ?>',
                                            msg: '<?php echo $view['translator']->trans('Error has been occurred'); ?>',
                                            buttons: Ext.MessageBox.OK,
                                            icon: Ext.MessageBox.ERROR
                                        });
                                    }
                                }
                            });
                        }
                    },
                    items: [{
                        fieldLabel: '<?php echo $view['translator']->trans('Name'); ?>',
                        allowBlank: false,
                        name: 'name'
                    },{
                        fieldLabel: '<?php echo $view['translator']->trans('Title'); ?>',
                        name: 'title'
                    },{
                        fieldLabel: '<?php echo $view['translator']->trans('Description'); ?>',
                        name: 'description',
                        xtype: 'textareafield'
                    },{
                        fieldLabel: '<?php echo $view['translator']->trans('Content'); ?>',
                        name: 'content',
                        height: 300,
                        xtype: 'htmleditor'
                    }]
                })
            ]
        }),

        listeners:{
            afterrender: function ( t, eOpts ) {
                Ext.Ajax.request({
                    url: "<?php echo $view['router']->generate('ifrescoClientNodeActionsBundle_gethtmldocument') ?>",
                    params: {nodeId: nodeId},
                    success: function(response){
                        var data = $.JSON.decode(response.responseText);
                        editHTMLdoc.items.items[0].items.items[0].getForm().setValues(data);
                    }
                });
            }
        },

        buttons: [{
            text: '<?php echo $view['translator']->trans('Save'); ?>',
            handler: function() {
                editHTMLdoc.items.items[0].items.items[0].getForm().submit()
            }
        },
            {
                text: '<?php echo $view['translator']->trans('Close'); ?>',
                handler: function() {
                    editHTMLdoc.close();
                }
            }]
    });

}

function openTab(tabname,tabtitle,url) {
    if (!tabExists(tabname)) {
        addTabDynamic(tabname,tabtitle);

        $.ajax({
            cache: false,
            url : url,

            success : function (data) {

                $("#"+tabname).html(data);
                $("#overAll").unmask();
            },
            beforeSend: function(req) {
                $("#overAll").mask("<?php echo $view['translator']->trans('Loading'); ?> "+tabtitle+"...",300);
            }

        });
    }
    setActive(tabname);
}

function openFixedTab(tabname,tabtitle,url) {
    if (!tabExists(tabname)) {
        addTabFixed(tabname,tabtitle);

        $.ajax({
            cache: false,
            url : url,

            success : function (data) {

                $("#"+tabname).html(data);
                $("#overAll").unmask();
            },
            beforeSend: function(req) {
                $("#overAll").mask("<?php echo $view['translator']->trans('Loading'); ?> "+tabtitle+"...",300);
            }

        });
    }
    setActive(tabname);
}

function addFavorite(nodeId,nodeText,nodeType) {
    nodeText = strip_tags(nodeText,"");
    $.post("<?php echo $view['router']->generate('ifrescoClientUserSpecificBundle_addfavorite') ?>", "nodeId="+nodeId+"&nodeText="+nodeText+"&nodeType="+nodeType, function(data) {
        var success = data.success;
        if (success == true) {

            var favTree = Ext.getCmp('favTree');
            if (favTree) {
                if(!favTree.getStore().isLoading())
                    favTree.getStore().load();
                //favTree.render();
            }
        }
        else {
            /*Ext.MessageBox.show({
             title: 'Error',
             msg: data.message,
             buttons: Ext.MessageBox.OK,
             icon: Ext.MessageBox.WARNING
             });*/
        }
    }, "json");
}

function removeFavoriteById(id) {
    removeFavorite(id,'favId');
}

function removeFavoriteByNodeId(id) {
    removeFavorite(id,'nodeId');
}

function removeFavorite(id,type) {
    $.post("<?php echo $view['router']->generate('ifrescoClientUserSpecificBundle_removefavorite') ?>", type+"="+id, function(data) {
        var success = data.success;
        if (success == true) {

            var favTree = Ext.getCmp('favTree');
            if (favTree) {
                if(!favTree.getStore().isLoading())
                    favTree.getStore().load();
                //favTree.render();
            }
        }
        else {

        }
    }, "json");
}

function alfDocument(nodeId,nodeType,nodeImgText,tabnodeid,containerName) {
    if (typeof tabnodeId=='undefined') {
        var tabnodeid = nodeId.replace(/-/g,"");
    }

    if (typeof containerName=='undefined')
        var containerName = "";

    tabnodeid = tabnodeid.replace(/ /g,"");
    containerName = containerName.replace(/ /g,"");

    /*if (typeof node.attributes.nodeId != 'undefined')
     var tabnodeid = node.attributes.nodeId.replace(/-/g,"");
     else
     var tabnodeid = nodeId.replace(/-/g,"");    */

    var createTab = false;
    if (!tabExists('tab-'+tabnodeid)) {
        addTabDynamic('tab-'+tabnodeid,nodeImgText);
        createTab = true;
    }
    setActive('tab-'+tabnodeid);

    if (nodeType == "file") {
        $.ajax({
            cache: false,
            url : "<?php echo $view['router']->generate('ifrescoClientDataGridBundle_detailview') ?>",
            data: ({'nodeId' : nodeId}),


            success : function (data) {
                $("#overAll").unmask();
                $("#tab-"+tabnodeid).html(data);

            },
            beforeSend: function(req) {
                $("#overAll").mask("Loading "+nodeImgText+"...",300);
            }
        });
    }
    else if(nodeType == "folder" || nodeType == "category" || nodeType == "tag") {

        if (createTab == true) {
            $.ajax({
                cache: false,
                url : "<?php echo $view['router']->generate('ifrescoClientDataGridBundle_homepage') ?>?containerName="+tabnodeid+"&addContainer="+containerName+"&columnsetid="+UserColumnsetId,

                success : function (data) {
                    $("#overAll").unmask();
                    $("#tab-"+tabnodeid).html(data);

                    if (nodeType == "folder")
                        eval("reloadGridData"+tabnodeid+"({params:{'nodeId':nodeId,'columnsetid':Registry.getInstance().get(\"ColumnsetId\")}});");
                    else if (nodeType == "category")
                        eval("reloadGridData"+tabnodeid+"({params:{'subCategories':false,'fromTree':false,'categoryNodeId':nodeId,'categories':nodeId,'columnsetid':Registry.getInstance().get(\"ColumnsetId\")}});");
                },
                beforeSend: function(req) {
                    $("#overAll").mask("Loading "+nodeImgText+"...",300);
                }
            });
        }
        else {
            if (nodeType == "folder")
                eval("reloadGridData"+tabnodeid+"({params:{'nodeId':nodeId,'columnsetid':Registry.getInstance().get(\"ColumnsetId\")}});");
            else if (nodeType == "category")
                eval("reloadGridData"+tabnodeid+"({params:{'subCategories':Registry.getInstance().get(\"BrowseSubCategories\"),'categories':nodeId,'columnsetid':Registry.getInstance().get(\"ColumnsetId\")}});");
        }
    }
}

function openDetailView(nodeId,nodeName) {
    var tabnodeid = nodeId.replace(/[-., ]/g,"");
    if (!tabExists('tab-'+tabnodeid))
        addTabDynamic('tab-'+tabnodeid,nodeName);
    else
        setActive('tab-'+tabnodeid);

    $.ajax({
        cache: false,
        url : "<?php echo $view['router']->generate('ifrescoClientDataGridBundle_detailview') ?>",
        data: ({'nodeId' : nodeId}),


        success : function (data) {
            $("#overAll").unmask();
            $("#tab-"+tabnodeid).html(data);
        },
        beforeSend: function(req) {
            $("#overAll").mask("Loading "+nodeName+"...",300);
        }
    });
}

function openCategory(nodeId,nodeName,subCategories,fromTree) {
    jQuery14.manageAjax.abort('metadata');

    if (typeof subCategories == 'undefined')
        subCategories = "false";
    if (typeof fromTree == 'undefined')
        fromTree = "false";
    var tabnodeid = nodeId.replace(/[-.,:\+/\\ ]/g,"");

    tabnodeid = tabnodeid.replace(/%[a-z0-9A-Z][a-z0-9A-Z]/g,"");
    if (!tabExists('tab-'+tabnodeid))
        addTabDynamic('tab-'+tabnodeid,'<?php echo $view['translator']->trans('Category:'); ?> '+nodeName);
else
    setActive('tab-'+tabnodeid);

    $.ajax({
        cache: false,
        url : "<?php echo $view['router']->generate('ifrescoClientDataGridBundle_homepage') ?>?containerName="+tabnodeid+"&columnsetid="+UserColumnsetId,

        success : function (data) {
            $("#overAll").unmask();
            $("#tab-"+tabnodeid).html(data);
// reloadGridData({params:{'fromTree':"true",'subCategories':BrowseSubCategories,'categoryNodeId':node.attributes.nodeId,'categories':node.id,'columnsetid':UserColumnsetId}});
            eval("reloadGridData"+tabnodeid+"({params:{'fromTree':fromTree,'subCategories':subCategories,'categoryNodeId':nodeId,'categories':nodeName,'columnsetid':Registry.getInstance().get(\"ColumnsetId\")}});");
        },
        beforeSend: function(req) {
            $("#overAll").mask("Loading Documents...",300);
        }
    });
}

function openCategoryAdvanced(nodeId,nodePath,nodeName,subCategories,fromTree) {
    jQuery14.manageAjax.abort('metadata');

    if (typeof subCategories == 'undefined')
        subCategories = "false";
    if (typeof fromTree == 'undefined')
        fromTree = "false";
    var tabnodeid = nodeId.replace(/[-.,:\+/\\ ]/g,"");

    tabnodeid = tabnodeid.replace(/%[a-z0-9A-Z][a-z0-9A-Z]/g,"");
    if (!tabExists('tab-'+tabnodeid))
        addTabDynamic('tab-'+tabnodeid,'<?php echo $view['translator']->trans('Category:'); ?> '+nodeName);
else
    setActive('tab-'+tabnodeid);

    $.ajax({
        cache: false,
        url : "<?php echo $view['router']->generate('ifrescoClientDataGridBundle_homepage') ?>?containerName="+tabnodeid+"&columnsetid="+UserColumnsetId,

        success : function (data) {
            $("#overAll").unmask();
            $("#tab-"+tabnodeid).html(data);
// reloadGridData({params:{'fromTree':"true",'subCategories':BrowseSubCategories,'categoryNodeId':node.attributes.nodeId,'categories':node.id,'columnsetid':UserColumnsetId}});
            eval("reloadGridData"+tabnodeid+"({params:{'fromTree':fromTree,'subCategories':subCategories,'categoryNodeId':nodeId,'categories':nodePath,'columnsetid':Registry.getInstance().get(\"ColumnsetId\")}});");
        },
        beforeSend: function(req) {
            $("#overAll").mask("Loading Documents...",300);
        }
    });
}

function openFolder(nodeId,nodeName) {
    jQuery14.manageAjax.abort('metadata');

    var tabnodeid = nodeId.replace(/[-., ]/g,"");
    addTabDynamic('tab-'+tabnodeid,nodeName);

    $.ajax({
        cache: false,
        url : "<?php echo $view['router']->generate('ifrescoClientDataGridBundle_homepage') ?>?containerName="+tabnodeid+"&columnsetid="+UserColumnsetId,

        success : function (data) {
            $("#overAll").unmask();
            $("#tab-"+tabnodeid).html(data);

            eval("reloadGridData"+tabnodeid+"({params:{'nodeId':nodeId,'columnsetid':Registry.getInstance().get(\"ColumnsetId\")}});");
        },
        beforeSend: function(req) {
            $("#overAll").mask('<?php echo $view['translator']->trans('Loading Documents...'); ?>',300);
        }
    });
}

function openTag(nodeName) {
    jQuery14.manageAjax.abort('metadata');

    var tabnodename = nodeName.replace(/[-., ]/g,"");
    if (!tabExists('tab-'+tabnodename))
        addTabDynamic('tab-'+tabnodename,'Tag: '+nodeName);
    else
        setActive('tab-'+tabnodename);

    $.ajax({
        cache: false,
        url : "<?php echo $view['router']->generate('ifrescoClientDataGridBundle_homepage') ?>?containerName="+tabnodename+"&columnsetid="+UserColumnsetId,

        success : function (data) {
            $("#overAll").unmask();
            $("#tab-"+tabnodename).html(data);

            eval("reloadGridData"+tabnodename+"({params:{'tag':nodeName,'columnsetid':Registry.getInstance().get(\"ColumnsetId\")}});");
        },
        beforeSend: function(req) {
            $("#overAll").mask('<?php echo $view['translator']->trans('Loading Documents...'); ?>',300);
        }
    });
}

function openClickSearch(propName,propLabel,propValue) {
    jQuery14.manageAjax.abort('metadata');

    var tabnodename = propName.replace(/[-.\:, ]/g,"");
    var tabprovval = propValue.replace(/[-.\:\/\"\'\\, ]/g,"");
    var tabname = tabnodename+tabprovval;
    if (!tabExists('tab-'+tabname))
        addTabDynamic('tab-'+tabname,propLabel+': '+propValue);
    else
        setActive('tab-'+tabname);

    $.ajax({
        cache: false,
        url : "<?php echo $view['router']->generate('ifrescoClientDataGridBundle_homepage') ?>?containerName="+tabname+"&columnsetid="+ClickSearchColumnSet,

        success : function (data) {
            $("#overAll").unmask();
            $("#tab-"+tabname).html(data);

            eval("reloadGridData"+tabname+"({params:{'clickSearch':propName, 'clickSearchValue':propValue,'columnsetid':ClickSearchColumnSet}});");
        },
        beforeSend: function(req) {
            $("#overAll").mask('<?php echo $view['translator']->trans('Loading Documents...'); ?>',300);
        }
    });
}

function openClipboard() {
    ClipBoard.reloadClip();
}

function onTreeEditComplete(treeEditor, value, oldValue) {
    if (value != oldValue) {
        var nodeId = treeEditor.editNode.attributes.nodeId;
        if (nodeId !== null && typeof nodeId !== 'undefined') {
            $.ajax({
                cache: false,
                url : "<?php echo $view['router']->generate('ifrescoClientCategoryTreeBundle_editcategory') ?>",
                data: {nodeId: nodeId, value: value},
                dataType:"json",

                success : function (data) {
                    $("#categoriesTree").unmask();

                    if (data.success == true) {
                        firedContextMenuEditing = false;
                        return true;
                    }
                    else {
                        firedContextMenuEditing = false;
                        treeEditor.editNode.setText(oldValue);
                    }
                },
                error: function() {
                    treeEditor.editNode.setText(oldValue);
                    $("#categoriesTree").unmask();
                },
                beforeSend: function(req) {
                    $("#categoriesTree").mask("<?php echo $view['translator']->trans('Rename'); ?> <b>"+oldValue+"</b> <?php echo $view['translator']->trans('to'); ?> <b>"+value+"</b>...",300);
                }
            });
        }
        else {
            //if (nodeId == "newNode" || treeId == "root") {
            if (value.length == 0) {
                treeEditor.editNode.remove(true);
                return false;
            }
            else {
                var parentNode = treeEditor.editNode.parentNode;
                if (parentNode.attributes.id == "root")
                    var parentNodeId = "root";
                else
                    var parentNodeId = parentNode.attributes.nodeId;

                $.ajax({
                    cache: false,
                    url : "<?php echo $view['router']->generate('ifrescoClientCategoryTreeBundle_addcategory') ?>",
                    data: {nodeId: parentNodeId, value: value},
                    dataType:"json",

                    success : function (data) {
                        $("#categoriesTree").unmask();

                        if (data.success == true) {
                            treeEditor.editNode.attributes.nodeId = data.nodeId;
                            return true;
                        }
                        else {
                            treeEditor.editNode.remove(true);
                            return false;
                        }
                    },
                    error: function() {
                        treeEditor.editNode.remove(true);
                        $("#categoriesTree").unmask();
                        return false;
                    },
                    beforeSend: function(req) {
                        $("#categoriesTree").mask("<?php echo $view['translator']->trans('Add category'); ?> <b>"+value+"</b>...",300);
                    }
                });
            }
            //}
        }
    }
    else
        return false;
}

function openAdmin() {
    if (!tabExists('admintab')) {
        addTabDynamic('admintab',"<?php echo $view['translator']->trans('Administration'); ?>");


        $.ajax({
            cache: false,
            url : "<?php echo $view['router']->generate('ifrescoClientAdminBundle_homepage') ?>",

            success : function (data) {
                $("#admintab").html(data);
            }
        });
    }
    setActive('admintab');
}

function tabExists(tabId) {
    return Ext.getCmp(tabId) == undefined ? false : true;
    var tabPanel = Ext.getCmp('content-tabs');
    if (tabPanel) {
        var tab = tabPanel.getItem(tabId);
        if(tab)
            return true;
    }
    return false;
}

function showRenderer(btn) {
    $(".PDFRenderer").show();
}

function strip_tags(input, allowed) {
    allowed = (((allowed || "") + "")
        .toLowerCase()
        .match(/<[a-z][a-z0-9]*>/g) || [])
        .join('');
    var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi;
    commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
    return input.replace(commentsAndPhpTags, '').replace(tags, function($0, $1){
        return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
    });
}

function addTab(tabTitle, targetUrl){
    var tabPanel = Ext.getCmp('content-tabs');
    if (tabPanel) {
        tabPanel.add({
            title: tabTitle,
            autoLoad: {url: targetUrl, callback: this.initSearch, scope: this},
            closable:true,
            listeners: {
                'beforeclose': function(tab, eOpts) {
                    tab.destroy()
                }
            }
        }).show();
    }
}

function addTabFixed(tabId,tabTitle){
    var tabPanel = Ext.getCmp('content-tabs');
    if (tabPanel) {
        tabPanel.add({
            title: tabTitle,
            id:tabId,
            closable:false,
            listeners: {
                'beforeclose': function(tab, eOpts) {
                    tab.destroy()
                    return false;
                }
            }
        }).show();
    }

}

//function addTabDynamic(tabId, tabTitle, iconCls){
//    iconCls = iconCls || false;
//    var titleLength = <? echo (int)$view['settings']->getSetting("TabTitleLength"); ?>;
//
//    var tabPanel = Ext.getCmp('content-tabs');
//
//    tabTitle = Ext.util.Format.stripTags(tabTitle);
//
//    if(titleLength > 0 && tabTitle.length > titleLength + 3) {
//        var tabTitle = tabTitle.substring(0, titleLength)+'...';
//    }
//
//    if (tabPanel) {
//        tabPanel.add({
//            title: tabTitle,
//            id:tabId,
//            closable:true,
//            iconCls: iconCls,
//            autoDestroy:false,
//            listeners: {
//                'beforeclose': function(tab, eOpts) {
//                    tab.destroy()
//                    return false;
//                }
//    }
//    }).show();
//    }
//
//    }
//
//    function addTabDynamicLoad(tabId,tabTitle,autoLoad){
//        var titleLength = <? echo (int)$view['settings']->getSetting("TabTitleLength"); ?>;
//    var tabPanel = Ext.getCmp('content-tabs');
//
//    tabTitle = Ext.util.Format.stripTags(tabTitle);
//    if(titleLength > 0 && tabTitle.length > titleLength + 3) {
//        var tabTitle = tabTitle.substring(0, titleLength)+'...';
//        }
//
//    if (tabPanel) {
//        autoLoad.autoLoad = true;
//        tabPanel.add({
//        title: tabTitle,
//        id:tabId,
//        loader:autoLoad,
//        closable:true,
//        listeners: {
//        'beforeclose': function(tab, eOpts) {
//        tab.destroy();
//        }
//    }
//    }).show();
//    }
//    }
//
//    function getActiveContentTab() {
//        var tabPanel = Ext.getCmp('content-tabs');
//        if (tabPanel) {
//        return tabPanel.getActiveTab();
//        }
//    return null;
//    }
//
//    function closeActiveContentTab() {
//        var activeTab = getActiveContentTab();
//        if (activeTab != null) {
//        removeContentTab(activeTab.id);
//        }
//    }
//
//    function removeContentTab(tabId) {
//        if (tabExists(tabId)) {
//        var tabPanel = Ext.getCmp('content-tabs');
//        if (tabPanel) {
//        tabPanel.remove(tabId);
//        }
//    }
//    }
//
//
//    function updateTab(tabId,title, url) {
//        var tabPanel = Ext.getCmp('content-tabs');
//        if (tabPanel) {
//        var tab = tabPanel.query('#'+tabId);
//        if(tab){
//        tab.getUpdater().update(url);
//        tab.setTitle(title);
//        }else{
//        tab = addTab(title,url);
//        }
//    tabPanel.setActiveTab(tab);
//    }
//    }
//
//    function setActive(tabId) {
//        var tabPanel = Ext.getCmp('content-tabs');
//        if (tabPanel) {
//        var tab = tabPanel.query('#'+tabId);
//        if(tab[0])
//        tabPanel.setActiveTab(tab[0]);
//        }
//    }
//
//
//    function checkAuthPresence() {
//
//        $.ajax({
//            cache: false,
//            url: "<?php echo $view['router']->generate('ifrescoClientDashletsBundle_checkauth') ?>",
//            success: function(data) {
//
//                data = $.JSON.decode(data);
//
//                if(data.success === false) {
//                    jQuery(document.body).elapsor({
//                        color:'#000',
//                        opacity:85,
//                        func : function () {
//                            $(".PDFRenderer").hide();
//
//                            if($("#loginWindow").length == 0)
//                                jQuery(document.body).append('<div id="loginWindow" style="z-index:10000;position:absolute;top:30%;left:30%;width:500px;height:200px;"></div>');
//                            $.ajax({
//                                cache: false,
//                                url : "<?php echo $view['router']->generate('ifrescoClientLoginBundle_login') ?>",
//
//                                success : function (data) {
//                                    $("#loginWindow").html(data).fadeIn();
//                                },
//    beforeSend: function(req) {
//
//        }
//    });
//    window.clearInterval(checkLoginInterval);
//    }
//    });
//    }
//    }
//    });
//
//    setTimeout(checkAuthPresence, 1000*60*5);
//    }
//
//    function checkloginIntervalFunc() {
//
//        $.ajax({
//            cache: false,
//            url: "<?php echo $view['router']->generate(/*'AlfrescoLogin/IsLoggedin'*/ 'ifrescoClientDashletsBundle_homepage') ?>?4",
//            error: function(event, request, options, error) {
//                switch (event.status) {
//                    case 401:
//                        jQuery(document.body).elapsor({
//                            color:'#000',
//                            opacity:85,
//                            func : function () {
//                                $(".PDFRenderer").hide();
//                                //jQuery(document.body).append('<div id="loginWindow" style="z-index:10000;position:absolute;top:30%;left:50%;width:530px;height:210px;background-color:#eeeeee;"></div>');
//                                jQuery(document.body).append('<div id="loginWindow" style="z-index:10000;position:absolute;top:30%;left:30%;width:500px;height:200px;"></div>');
//                                $.ajax({
//                                    cache: false,
//                                    url : "<?php echo $view['router']->generate(/*'AlfrescoLogin/loginAjax'*/'ifrescoClientDashletsBundle_homepage') ?>?5",
//
//                                    success : function (data) {
//                                        $("#loginWindow").html(data).fadeIn();
//                                    },
//    beforeSend: function(req) {
//
//        }
//    });
//    window.clearInterval(checkLoginInterval);
//    }
//    });
//    break;
//    }
//    }
//
//    });
//    }

$(window).hashchange( function(){
    var hash = location.hash;
    var tokenDelimiter = '/';

    var token = hash.replace(/^#/, '');

    var parts = new Array();
    if (token.length > 0) {
        parts = token.split(tokenDelimiter);
    }
    else
        parts[0] = "";

    var action = parts[0];

    switch (action) {
        case "document":
            var nodeIdMatch = token.replace(action+tokenDelimiter,"");
            nodeIdMatch = nodeIdMatch.replace(action,"");
            if (nodeIdMatch.length > 0) {
                var nodeId = nodeIdMatch;
                if (nodeId.length > 0) {
                    var nodeType = "file";
                    $.get("<?php echo $view['router']->generate('ifrescoClientMetadataBundle_getnameofnode') ?>", "nodeId="+nodeId, function(data) {
                        var success = data.success;
                        if (success == true) {
                            var nodeImgText = data.imgName;
                            nodeId = nodeId.replace("workspace://SpacesStore/","");
                            hasDocumentOrFolder = true;
                            alfDocument(nodeId,nodeType,nodeImgText);
                        }
                    }, "json");


                }
            }
            break;
        case "folder":
            var nodeIdMatch = token.replace(action+tokenDelimiter,"");
            nodeIdMatch = nodeIdMatch.replace(action,"");
            if (nodeIdMatch.length > 0) {
                var nodeId = nodeIdMatch;
                if (nodeId.length > 0) {
                    var nodeType = "folder";
                    $.get("<?php echo $view['router']->generate('ifrescoClientMetadataBundle_getnameofnode') ?>", "nodeId="+nodeId, function(data) {
                        var success = data.success;
                        if (success == true) {
                            var nodeImgText = data.imgName;
                            nodeId = nodeId.replace("workspace://SpacesStore/","");
                            hasDocumentOrFolder = true;
                            alfDocument(nodeId,nodeType,nodeImgText);
                        }
                    }, "json");


                }
            }
            break;
        case "search":
            var query = token.replace(action+tokenDelimiter,"");
            query = query.replace(action,"");
            if (query.length > 0) {
                var date = new Date();
                var dateStringTstamp = date.getDate()+"/"+date.getDay()+"/"+date.getFullYear()+" "+date.getHours()+":"+date.getMinutes();
                var dateString = date.format("<?php echo $view['settings']->getSetting("TimeFormat");; ?>");
                var timestamp = Date.parse(dateStringTstamp);
                $.ajax({
                    url: "<?php echo $view['router']->generate('ifrescoClientDataGridBundle_homepage'); ?>?containerName="+timestamp+"&columnsetid=null&addContainer="+timestamp,
                    success: function(data) {
                        $("#overAll").unmask();
                        addTabDynamic('searchresult-tab-'+timestamp,'<?php echo $view['translator']->trans('Search Result'); ?> - '+dateString);
                        $("#searchresult-tab-"+timestamp).html(data);
                        jsonItems = {};

                        pars = query.split('&');
                        for(var i=0; i< pars.length; i++) {
                            jsonItems[pars[i].split('=')[0]] = pars[i].split('=')[1];
                        }

                        jsonItems = $.JSON.encode(jsonItems);

                        jsonOptions = $.JSON.encode({searchTerm:null,results:'',locations:[],categories:[],tags:''});

                        //grid.store.load({params:{'nodeId':node.id}});
                        eval("reloadGridData"+timestamp+"({params:{'columnsetid':null,'advancedSearchFields':jsonItems,'advancedSearchOptions':jsonOptions}});");
                    },
                    beforeSend: function(req) {
                        $("#overAll").mask("<?php echo $view['translator']->trans('Loading Results...'); ?>",300);
                    }
                });
            }

            break;
    }
});
$(window).hashchange();

function IsEmpty(aTextFieldvalue) {
    if ((aTextFieldvalue.length==0) ||
        (aTextFieldvalue==null)) {
        return true;
    }
    else { return false; }
}

function alfrescoSearchSubmit() {
    var searchTerm = $("#alfrescoSearchTerm");
    var foundAdvancedSearch = false;
    /*
     deactivated to take the quicksearch field also in advanced search for full text search
     if (tabExists('searchtab')) {
     var tabPanel = Ext.getCmp('content-tabs');
     if (tabPanel) {
     var activetab = tabPanel.getActiveTab();
     if (activetab.id == "searchtab") {
     var searchString = "";
     if (!IsEmpty(searchTerm.val())) {
     searchString = searchTerm.val();
     }
     else {
     var searchTermAdv = $("#searchTermAdvanced");
     if (searchTermAdv != null && typeof searchTermAdv.length != 'undefined') {
     searchString = searchTermAdv.val();
     }
     }
     submitAdvancedSearch(searchString);
     foundAdvancedSearch = true;
     }
     }
     }
     *
     * modify that just the btn of quciksearch can be used for advanced search too
     */
    if (tabExists('searchtab')) {
        var tabPanel = Ext.getCmp('content-tabs');
        if (tabPanel) {
            var activetab = tabPanel.getActiveTab();
            if (activetab.id == "searchtab") {
                submitBtnClick();
                foundAdvancedSearch = true;
            }
        }
    }
    if (foundAdvancedSearch == false) {
        if (!IsEmpty(searchTerm.val())) {
            /*var dataGridHTML =$("#dataGrid").html();
             if (dataGridHTML == null || dataGridHTML == "") {
             $.ajax({

             url : "<?php echo $view['router']->generate('ifrescoClientDataGridBundle_homepage') ?>",

             success : function (data) {
             $("#ContentBox").html(data);
             //grid.store.load({params:{'nodeId':node.id}});
             reloadGridData({params:{'searchTerm':searchTerm.val()}});
             }
             });
             }
             else {
             //grid.store.load({params:{'nodeId':node.id}});
             reloadGridData({params:{'searchTerm':searchTerm.val()}});
             }*/

            if (!tabExists('documentgrid-tab')) {
                addTabDynamic('documentgrid-tab','<?php echo $view['translator']->trans('Documents'); ?>');

                $.ajax({
                    cache: false,
                    url : "<?php echo $view['router']->generate('ifrescoClientDataGridBundle_homepage') ?>?columnsetid="+UserColumnsetId,

                    success : function (data) {
                        $("#documentgrid-tab").html(data);
                        //grid.store.load({params:{'nodeId':node.id}});

                        reloadGridData({params:{'searchTerm':searchTerm.val(),'columnsetid':UserColumnsetId}});
                    }
                });
            }
            else {
                setActive('documentgrid-tab');
                reloadGridData({params:{'searchTerm':searchTerm.val(),'columnsetid':UserColumnsetId}});
            }
        }
    }
}

$(function() {
    $("#alfrescoSearchTerm").keypress(function (e) {
        if ((e.which && e.which == 13) || (e.keyCode && e.keyCode == 13)) {
            $('#alfrescoSearchSubmit').click();
            return false;
        } else {
            return true;
        }
    });

    $("#alfrescoSearchSubmit").click(function() {
        alfrescoSearchSubmit();
    });
});

var ClipBoard = {
    items: new Array(),
    addItem: function(path,name) {
        if ($.inArray(path,ClipBoard.items) < 0) {
            ClipBoard.items.push(path);
        }
    },
    removeItem: function(path) {
        if ($.inArray(path,ClipBoard.items) >= 0) {
            ClipBoard.items.splice($.inArray(path,ClipBoard.items), 1);
        }
    },
    clearItems: function() {
        ClipBoard.items = new Array();

        ClipBoard.reloadClip();
    },
    reloadClip: function() {
        var jsonItems = $.toJSON(ClipBoard.items);

        if (!tabExists('clipboard-tab')) {
            addTabDynamic('clipboard-tab','<?php echo $view['translator']->trans('Clipboard'); ?>');

            $.ajax({
                cache: false,
                url : "<?php echo $view['router']->generate('ifrescoClientDataGridBundle_homepage') ?>?containerName=ClipBoard&addContainer=clipboardcontainer&clipboard=true&columnsetid="+UserColumnsetId,
                success : function (data) {
                    $("#clipboard-tab").html(data);
                    eval("reloadGridDataClipBoard({params:{'clipboard':true,'clipboarditems':'"+jsonItems+"','columnsetid':Registry.getInstance().get(\"ColumnsetId\")}})");
                }
            });
        }
        else {
            eval("reloadGridDataClipBoard({params:{'clipboard':true,'clipboarditems':'"+jsonItems+"','columnsetid':Registry.getInstance().get(\"ColumnsetId\")}})");
            setActive('clipboard-tab'); // TODO - REMOVE JUST FOR DEBUG
        }
    }
}

Ext.History.on('change', function(token){
    var token = token || "";
    var parts = new Array();

    if (token.length > 0) {
        parts = token.split(tokenDelimiter);
    }
    else
        parts[0] = "";

    var action = parts[0];

    switch (action) {

        case "document-details":
            if (parts.length >= 2) {
                var nodeId = parts[1];
                if (nodeId.length > 0) {
                    var nodeType = "file";

                    $.get("<?php echo $view['router']->generate('ifrescoClientMetadataBundle_getnameofnode') ?>", "nodeId="+nodeId, function(data) {
                        var success = data.success;
                        if (success == true) {
                            var nodeImgText = data.imgName;
                            alfDocument(nodeId,nodeType,nodeImgText);
                        }
                    }, "json");


                }
            }
            break;
        case "folder-view":
            if (parts.length >= 2) {
                var nodeId = parts[1];
                if (nodeId.length > 0) {
                    var nodeType = "folder";
                    $.get("<?php echo $view['router']->generate('ifrescoClientMetadataBundle_getnameofnode') ?>", "nodeId="+nodeId, function(data) {
                        var success = data.success;
                        if (success == true) {
                            var nodeImgText = data.imgName;
                            alfDocument(nodeId,nodeType,nodeImgText);
                        }
                    }, "json")
                }
            }
            break;
        case "category-view":
            if (parts.length >= 2) {
                var category = parts[1];
                if (category.length > 0) {
                    var nodeType = "category";
                    alfDocument(category,nodeType,category);
                }
            }
            break;
        case "":

            break;
        default:

            var tabPanel = Ext.getCmp(parts[0]);
            var tabId = parts[1];

            if (tabExists(tabId)) {
                tabPanel.show();
                tabPanel.setActiveTab(tabId);
            }
            break;
    }
});

function arrangeWindows(where) {
    var verticalBtn = $("#verticalBtn");
    var horizontalBtn = $("#horizontalBtn");

    if (Registry.getInstance().get("ArrangeList") != where) {
        if (where == "horizontal") {
            verticalBtn.addClass("disabled");
            horizontalBtn.removeClass("disabled");
            Registry.getInstance().set("ArrangeList","horizontal");
            Registry.getInstance().save();
            ArrangeList = "horizontal";
        }
        else {
            horizontalBtn.addClass("disabled");
            verticalBtn.removeClass("disabled");
            Registry.getInstance().set("ArrangeList","vertical");
            Registry.getInstance().save();
            ArrangeList = "vertical";
        }
    }
}

var firedContextMenuEditing = false;
function onTreeEditing(n) {
    firedContextMenuEditing = true;

    if(n.data.id == 'newNode') {
        var checkExpanded = function(){
            if(n.parentNode.isExpanded())
                setTimeout(function(){treeCat.getPlugin('treeEditPlugin').startEdit(n, treeCat.columns[0]);}, 500)
            else
                setTimeout(checkExpanded, 500)
        };
        checkExpanded();
    }
    else {
        treeCat.getPlugin('treeEditPlugin').startEdit(n, treeCat.columns[0]);
    }
    //n.save()
    //treeCatEditor.editNode = n;
    //treeCatEditor.startEdit(n.ui.textNode);
    firedContextMenuEditing = false;
}

function finishExpandAdd(node) {
    if (node[0] != undefined)
        node = node[0].parentNode;

    var newNode = node.appendChild({id: "newNode", iconCls:'new-category', cls:'folder', text: "", leaf: true});
    if (newNode != null) {
        node.data.leaf = false;
        node.expand(false);
        //treeCat.doLayout();
        //treeCat.render();
        onTreeEditing(newNode);
    }
}