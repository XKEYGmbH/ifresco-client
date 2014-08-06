<script>
    function loadSitesAction(item) {
        switch (item.text) {
            case "Calendar":
                $.ajax({
                    cache: false,
                    url : "<?php echo $view['router']->generate('ifresco_client_login') ?>?Sites/Calendar",

                    success : function (data) {
                        $("#sitesContent").unmask();

                        $("#sitesWindow").html(data);
                    },
                    beforeSend: function(xhr) {
                        $("#sitesWindow").html('');
                        $("#sitesContent").mask("<?php echo $view['translator']->trans('Loading...'); ?>",300);

                    }
                });
                break;
            default:
                break;
        }
    }

    function mySize() {
        $("#documentGrid").css({'height': (($(window).height()) -98)+'px'});
        return $("#documentGrid").height();
    }

    function getHalfSize() {
        return (($("#documentGrid").height() / 2)-4);
    }

    function refreshGrid() {
        if (lastParams !== null) {
            var tempLast = lastParams;
            lastParams.params.reload = true;
            mainGrid.store.load(lastParams);
            lastParams = tempLast;
        }
    }

    function loadNewColumns(record) {
        if (typeof record !== 'undefined') {
            var ColumnsetId = record.id;
            var ColumnName = record.name;
            var params = lastParams;
            params.params.columnsetid = ColumnsetId;

            var date = new Date();
            var dateString = date.getDate()+"/"+date.getDay()+"/"+date.getFullYear()+" "+date.getHours()+":"+date.getMinutes();
            var timestamp = Date.parse(dateString);

            var tabPanel = Ext.getCmp('content-tabs');
            if (tabPanel) {
                var activeTab = tabPanel.getActiveTab();


                Ifresco.Registry.set("ColumnsetId",ColumnsetId);
                UserColumnsetId = ColumnsetId;
                Ifresco.Registry.save();

                $.ajax({
                    cache: false,
                    url: "<?php echo $view['router']->generate('ifresco_client_data_grid_index') ?>?containerName=<?php echo $containerName ?>&addContainer=<?php echo $addContainer; ?>&columnsetid="+ColumnsetId,

                    success : function (data) {
                        $("#overAll").unmask();
                        $("#"+activeTab.id).html(data);

                        eval("reloadGridData({'params':params.params});");
                    },
                    beforeSend: function(req) {
                        $("#overAll").mask("<?php echo $view['translator']->trans('Loading Results...'); ?>",300);
                    }

                });


            }
        }
    }


    function reloadGridData(params) {
        Ext.Ajax.abort(mainGrid.getStore().getProxy().activeRequest);

        if (typeof params.params.columnsetid !== 'undefined') {
            currentColumnsetid = params.params.columnsetid;
        }
        else {
            var columnsetid = Ifresco.Registry.get("ColumnsetId");
            currentColumnsetid = columnsetid;
            params.params.columnsetid = columnsetid;
        }

        mainGrid.stateId = 'documentGrid-stateid-'+currentColumnsetid;

        if (params.params.clipboarditems != null && typeof params.params.clipboarditems != 'undefined') {
            mainGrid.getStore().getProxy().actionMethods.read = "POST";
        }

        mainGrid.store.currentPage=1;

        var tbar =  Ext.getCmp("viewportToolbar");
        if (tbar) {
            tbar.removeAll();
        }

        <?php if (!empty($DefaultSort) && $DefaultSort != null) { ?>
        mainGrid.store.sortOnLoad = true;
        mainGrid.getStore().sortInfo = {field: '<?php echo $DefaultSort; ?>', direction: '<?php echo $DefaultSortDir; ?>'};
        mainGrid.getStore().sorters.clear();
        mainGrid.getStore().sorters.add(new Ext.util.Sorter({
            property: '<?php echo $DefaultSort; ?>',
            direction:'<?php echo $DefaultSortDir; ?>'
        }));

        <?php } ?>

        lastParams = params;
        if(mainThumbView.isVisible())
            loadThumbnailView();
        else {
            mainGrid.store.proxy.extraParams = {};
            paramsToStore = ['subCategories', 'categories', 'categoryNodeId', 'nodeId', 'columnsetid', 'advancedSearchFields', 'advancedSearchOptions', 'containerName', 'clipboard', 'clipboarditems', 'searchTerm', 'clickSearch', 'clickSearchValue'];

            if (typeof paramsToStore !='undefined') {
                for(var i = 0; i < paramsToStore.length; i++) {
                    if(params.params[paramsToStore[i]]) {
                        mainGrid.store.proxy.extraParams[paramsToStore[i]] = params.params[paramsToStore[i]];
                    }
                }
            }

            mainGrid.store.load(params);
        }

        if (params.params.nodeId !== '') {
            currentNodeId = params.params.nodeId;
        }

        $("#previewWindow").html('');

        if(typeof changeUploadId === 'function') {
            changeUploadId(params.params.nodeId);
        }

        if (params.params.nodeId === '' || typeof params.params.nodeId === 'undefined' || params.params.nodeId == null) {

            var openAlfresco = mainGrid.dockedItems.items[1].getComponent('open-alfresco');
            if (typeof openAlfresco !== 'undefined') {
                openAlfresco.disable();
                //openAlfresco.hide();
            }

            var uploadContent = mainGrid.dockedItems.items[1].getComponent('upload-content');
            if (typeof uploadContent !== 'undefined') {
                uploadContent.disable();
                //uploadContent.hide();
            }

            var createFolder = mainGrid.dockedItems.items[1].getComponent('create-folder');
            if (typeof createFolder !== 'undefined') {
                createFolder.disable();
                //createFolder.hide();
            }

            var pasteCopyClipboard = mainGrid.dockedItems.items[1].getComponent('pastecopy-clipboard');
            if (typeof pasteCopyClipboard !== 'undefined') {
                pasteCopyClipboard.disable();
                //pasteCopyClipboard.hide();
            }

            var pasteCutClipboard = mainGrid.dockedItems.items[1].getComponent('pastecut-clipboard');
            if (typeof pasteCutClipboard !== 'undefined') {
                pasteCutClipboard.disable();
                //pasteCutClipboard.hide();
            }

            var pasteLinkClipboard = mainGrid.dockedItems.items[1].getComponent('pastelink-clipboard');
            if (typeof pasteLinkClipboard !== 'undefined') {
                pasteLinkClipboard.disable();
            }
        }
        else {
            var openAlfresco = mainGrid.dockedItems.items[1].getComponent('open-alfresco');
            if (typeof openAlfresco !== 'undefined')
                openAlfresco.enable();

            var uploadContent = mainGrid.dockedItems.items[1].getComponent('upload-content');
            if (typeof uploadContent !== 'undefined')
                uploadContent.enable();

            var createFolder = mainGrid.dockedItems.items[1].getComponent('create-folder');
            if (typeof createFolder !== 'undefined')
                createFolder.enable();

            var pasteCopyClipboard = mainGrid.dockedItems.items[1].getComponent('pastecopy-clipboard');
            if (typeof pasteCopyClipboard !== 'undefined')
                pasteCopyClipboard.enable();

            var pasteCutClipboard = mainGrid.dockedItems.items[1].getComponent('pastecut-clipboard');
            if (typeof pasteCutClipboard !== 'undefined')
                pasteCutClipboard.enable();
        }

        <? if($view['settings']->getSetting("MetaOnTreeFolder") == 'true'){
            ?>
        PanelNodeId = null;
        Ext.getCmp('mainPreviewTab').disable();
        Ext.getCmp('mainVersionsTab').disable();
        var tabPanel = Ext.getCmp("previewPanel");
        var activeTabId = "";
        if (tabPanel) {
            var activeTab = tabPanel.activeTab;
            if (typeof activeTab !== 'undefined') {
                activeTabId = activeTab.id
            }
        }

        <? if($view['settings']->getSetting("ParentMetaDocumentOnly") == "true" ) {?>
        Ext.getCmp('mainParentMetadataTab').disable();
        <?}?>

        if (activeTabId === "mainMetadataTab")
            loadMetaData(currentNodeId);
        else if (activeTabId === "mainParentMetadataTab") {
            <? if($view['settings']->getSetting("ParentMetaDocumentOnly") == "true" ) {?>
            tabPanel.setActiveTab('mainMetadataTab');
            <?} else {?>
            loadParentMetaData(currentNodeId);
            <?}?>
        }
        <?
    }
     ?>
    }

    function pasteClipBoard(type) {
        if (ClipBoard.items.length > 0) {
            var clipBoardItems = $.JSON.encode(ClipBoard.items);
            var currentNode = currentNodeId;
            $.ajax({
                cache: false,
                url : "<?php echo $view['router']->generate('ifresco_client_grid_paste_clipboard') ?>",
                data: ({'clipboardItems' : clipBoardItems, 'actionType' : type, 'destNodeId' : currentNode}),
                type:'POST',
                success : function (data) {
                    $("#overAll").unmask();

                    jsonData = $.JSON.decode(data);
                    var totalCount = jsonData.totalResults;
                    var successCount = jsonData.successCount;
                    var failureCount = jsonData.failureCount;
                    if(jsonData.success === true) {
                        $(".PDFRenderer").hide();
                        Ext.MessageBox.show({
                            title: '<?php echo $view['translator']->trans('Successfully pasted!'); ?>',
                            msg: '<?php echo $view['translator']->trans('Successfully pasted'); ?> '+successCount+' <?php echo $view['translator']->trans('node(s)'); ?>!',
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.INFO,
                            fn:showRenderer
                        });

                        if (type=="cut") {
                            ClipBoard.clearItems();
                        }
                    }
                    else {
                        $(".PDFRenderer").hide();
                        Ext.MessageBox.show({
                            title: '<?php echo $view['translator']->trans('Pasting was not successful!'); ?>',
                            msg: successCount+' <?php echo $view['translator']->trans('of'); ?>  '+totalCount+' <?php echo $view['translator']->trans('node(s) pasted to the destination folder'); ?>!',
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.WARNING,
                            fn:showRenderer
                        });
                    }

                    refreshGrid();
                },
                beforeSend: function(req) {
                    $("#overAll").mask("<?php echo $view['translator']->trans('Pasting documents'); ?>",300);
                }
            });
        }
    }

    function checkOut(nodeId,MimeType) {
        $.ajax({
            cache: false,
            url : "<?php echo $view['router']->generate('ifresco_client_metadata_checkout') ?>?nodeId="+nodeId,


            success : function (data) {
                data = $.evalJSON(data);
                if (data.success === true) {
                    var workingCopyId = data.workingCopyId;

                    Ext.MessageBox.alert('<?php echo $view['translator']->trans('Checkout'); ?>', '<?php echo $view['translator']->trans('Document checked out successfully.'); ?>');

                    mainGrid.store.on('load', function() {

                            var rowIndex = mainGrid.getStore().find("nodeId",workingCopyId);
                            if (rowIndex !== -1) {
                                mainGrid.getSelectionModel().selectRow(rowIndex);
                                mainGrid.fireEvent('rowclick', mainGrid, rowIndex);
                                var checkoutBtn = Ext.getCmp('checkout');
                                var checkoutZohoBtn = Ext.getCmp('checkoutZoho');
                                var cancelCheckoutBtn = Ext.getCmp('cancel-checkout');

                                var editMetaDataBtn = Ext.getCmp('editMetadata');
                                var manageAspectsBtn = Ext.getCmp('manageAspects');
                                var specifyTypeBtn = Ext.getCmp('specifyType');

                                checkoutBtn.setTooltip("<?php echo $view['translator']->trans('Checkin'); ?>");
                                checkoutBtn.setIconCls("checkin-node");

                                <?php if (isset($OnlineEditing) && $OnlineEditing === "zoho") { ?>
                                if (jQuery.inArray(MimeType,ZohoMimeDocs)>=0) {
                                    checkoutZohoBtn.setTooltip("<?php echo $view['translator']->trans('Edit in Zoho Writer'); ?>");
                                    checkoutZohoBtn.enable();
                                    checkoutZohoBtn.setVisible(true);
                                }
                                else if (jQuery.inArray(MimeType,ZohoMimeSheet)>=0) {
                                    checkoutZohoBtn.setTooltip("<?php echo $view['translator']->trans('Edit in Zoho Sheet'); ?>");
                                    checkoutZohoBtn.enable();
                                    checkoutZohoBtn.setVisible(true);
                                }
                                else {
                                    checkoutZohoBtn.disable();
                                    checkoutZohoBtn.setVisible(false);
                                }
                                <?php } ?>

                                cancelCheckoutBtn.enable();
                                cancelCheckoutBtn.setVisible(true);

                                PanelNodeIsCheckedOut = true;
                                PanelNodeCheckedOutId = workingCopyId;
                            }
                        },
                        this,
                        {
                            single: true
                        });

                    refreshGrid();


                }
                else
                    Ext.MessageBox.alert('<?php echo $view['translator']->trans('Error'); ?>', '<?php echo $view['translator']->trans('An unknown problem occured at the check out process.'); ?>');
            },
            beforeSend: function(req) {

            }
        });
    }

    function checkInWindow(data) {
        showNewVersionWindow(data,"<?php echo $view['translator']->trans('Checkin'); ?>","<?php echo $view['translator']->trans('Save'); ?>","checkInVersion","");
    }

    function checkInVersion(data) {
        checkIn(data.nodeId,data.mime,true,data.note,data.versionchange);
    }

    function checkIn(nodeId,MimeType,msgbox,note,versionchange) {
        if (typeof msgbox === 'undefined' || msgbox === null)
            msgbox = true;

        if (typeof note === 'undefined' || note === null)
            note = "";

        if (typeof versionchange === 'undefined' || versionchange === null)
            versionchange = "minor";

        $.ajax({
            cache: false,
            url : "<?php echo $view['router']->generate('ifresco_client_metadata_checkin') ?>?nodeId="+nodeId,
            data: "note="+note+"&versionchange="+versionchange,

            success : function (data) {
                data = $.evalJSON(data);
                if (data.success = true) {
                    var origNodeId = data.origNodeId;

                    if (msgbox===true)
                        Ext.MessageBox.alert('<?php echo $view['translator']->trans('Checkin'); ?>', '<?php echo $view['translator']->trans('Document checked in successfully.'); ?>');

                    refreshGrid();

                    var rowIndex = mainGrid.getStore().find("nodeId",origNodeId);
                    if (rowIndex !== -1) {
                        mainGrid.getSelectionModel().selectRow(rowIndex);
                        mainGrid.fireEvent('rowclick', mainGrid, rowIndex);

                        var checkoutBtn = Ext.getCmp('checkout');
                        var checkoutZohoBtn = Ext.getCmp('checkoutZoho');
                        var cancelCheckoutBtn = Ext.getCmp('cancel-checkout');

                        var editMetaDataBtn = Ext.getCmp('editMetadata');
                        var manageAspectsBtn = Ext.getCmp('manageAspects');
                        var specifyTypeBtn = Ext.getCmp('specifyType');

                        checkoutBtn.setTooltip("<?php echo $view['translator']->trans('Checkout'); ?>");
                        checkoutBtn.setIconCls("checkout-node");

                        <?php if (isset($OnlineEditing) && $OnlineEditing === "zoho") { ?>
                        if (jQuery.inArray(MimeType,ZohoMimeDocs)>=0) {
                            checkoutZohoBtn.setTooltip("<?php echo $view['translator']->trans('Checkout in Zoho Writer'); ?>");
                            checkoutZohoBtn.enable();
                            checkoutZohoBtn.setVisible(true);
                        }
                        else if (jQuery.inArray(MimeType,ZohoMimeSheet)>=0) {
                            checkoutZohoBtn.setTooltip("<?php echo $view['translator']->trans('Checkout in Zoho Sheet'); ?>");
                            checkoutZohoBtn.enable();
                            checkoutZohoBtn.setVisible(true);
                        }
                        else {
                            checkoutZohoBtn.disable();
                            checkoutZohoBtn.setVisible(false);
                        }
                        <?php } ?>


                        cancelCheckoutBtn.disable();
                        cancelCheckoutBtn.setVisible(false);

                        editMetaDataBtn.enable();
                        manageAspectsBtn.enable();
                        specifyTypeBtn.enable();

                        PanelNodeIsCheckedOut = false;
                    }
                }
                else
                    Ext.MessageBox.alert('<?php echo $view['translator']->trans('Error'); ?>', '<?php echo $view['translator']->trans('An unknown problem occured at the check in process.'); ?>');
            },
            beforeSend: function(req) {
                if (typeof winNewVersion != "undefined")
                    winNewVersion.close();
            }
        });
    }

    function cancelCheckout(nodeId,origNodeId,MimeType,msgbox) {
        if (typeof msgbox === 'undefined' || msgbox === null)
            msgbox = true;

        $.ajax({
            cache: false,
            url : "<?php echo $view['router']->generate('ifresco_client_metadata_cancel_checkout') ?>?nodeId="+nodeId,

            success : function (data) {
                data = $.evalJSON(data);
                if (data.success = true) {

                    if (msgbox===true)
                        Ext.MessageBox.alert('<?php echo $view['translator']->trans('Cancel Checkout'); ?>', '<?php echo $view['translator']->trans('Successfully canceled.'); ?>');

                    refreshGrid();

                    var rowIndex = mainGrid.getStore().find("nodeId",origNodeId);
                    if (rowIndex !== -1) {
                        mainGrid.getSelectionModel().selectRow(rowIndex);
                        mainGrid.fireEvent('rowclick', mainGrid, rowIndex);

                        var checkoutBtn = Ext.getCmp('checkout');
                        var checkoutZohoBtn = Ext.getCmp('checkoutZoho');
                        var cancelCheckoutBtn = Ext.getCmp('cancel-checkout');

                        var editMetaDataBtn = Ext.getCmp('editMetadata');
                        var manageAspectsBtn = Ext.getCmp('manageAspects');
                        var specifyTypeBtn = Ext.getCmp('specifyType');

                        checkoutBtn.setTooltip("<?php echo $view['translator']->trans('Checkout'); ?>");
                        checkoutBtn.setIconCls("checkout-node");

                        <?php if (isset($OnlineEditing) && $OnlineEditing === "zoho") { ?>
                        if (jQuery.inArray(MimeType,ZohoMimeDocs)>=0) {
                            checkoutZohoBtn.setTooltip("<?php echo $view['translator']->trans('Checkout in Zoho Writer'); ?>");
                            checkoutZohoBtn.enable();
                            checkoutZohoBtn.setVisible(true);
                        }
                        else if (jQuery.inArray(MimeType,ZohoMimeSheet)>=0) {
                            checkoutZohoBtn.setTooltip("<?php echo $view['translator']->trans('Checkout in Zoho Sheet'); ?>");
                            checkoutZohoBtn.enable();
                            checkoutZohoBtn.setVisible(true);
                        }
                        else {
                            checkoutZohoBtn.disable();
                            checkoutZohoBtn.setVisible(false);
                        }
                        <?php } ?>

                        cancelCheckoutBtn.disable();
                        cancelCheckoutBtn.setVisible(false);

                        editMetaDataBtn.enable();
                        manageAspectsBtn.enable();
                        specifyTypeBtn.enable();

                        PanelNodeIsCheckedOut = false;
                    }
                }
                else
                    Ext.MessageBox.alert('<?php echo $view['translator']->trans('Error'); ?>', '<?php echo $view['translator']->trans('An unknown problem occured at the check in process.'); ?>');
            },
            beforeSend: function(req) {

            }
        });
    }

    function checkOutZoho(nodeId,MimeType) {
        $.ajax({
            cache: false,
            url : "<?php echo $view['router']->generate('ifresco_client_metadata_checkout') ?>?nodeId="+nodeId,


            success : function (data) {
                data = $.evalJSON(data);
                if (data.success === true) {
                    var workingCopyId = data.workingCopyId;

                    mainGrid.store.on('load', function() {

                            var rowIndex = mainGrid.getStore().find("nodeId",workingCopyId);
                            if (rowIndex !== -1) {
                                mainGrid.getSelectionModel().selectRow(rowIndex);
                                mainGrid.fireEvent('rowclick', mainGrid, rowIndex);
                                var checkoutBtn = Ext.getCmp('checkout');
                                var checkoutZohoBtn = Ext.getCmp('checkoutZoho');

                                var cancelCheckoutBtn = Ext.getCmp('cancel-checkout');

                                checkoutBtn.setTooltip("<?php echo $view['translator']->trans('Checkin'); ?>");
                                checkoutBtn.setIconCls("checkin-node");

                                if (jQuery.inArray(MimeType,ZohoMimeDocs)>=0) {
                                    checkoutZohoBtn.setTooltip("<?php echo $view['translator']->trans('Edit in Zoho Writer'); ?>");
                                    checkoutZohoBtn.enable();
                                    checkoutZohoBtn.setVisible(true);
                                }
                                else if (jQuery.inArray(MimeType,ZohoMimeSheet)>=0) {
                                    checkoutZohoBtn.setTooltip("<?php echo $view['translator']->trans('Edit in Zoho Sheet'); ?>");
                                    checkoutZohoBtn.enable();
                                    checkoutZohoBtn.setVisible(true);
                                }
                                else {
                                    checkoutZohoBtn.disable();
                                    checkoutZohoBtn.setVisible(false);
                                }

                                cancelCheckoutBtn.enable();
                                cancelCheckoutBtn.setVisible(true);


                                PanelNodeIsCheckedOut = true;
                                PanelNodeCheckedOutId = workingCopyId;
                            }
                        },
                        this,
                        {
                            single: true
                        });

                    $.ajax({
                        cache: false,
                        url : "<?php echo $view['router']->generate('ifresco_client_login') ?>?Zoho/ZohoUpload&nodeId="+workingCopyId,


                        success : function (dataZoho) {
                            $("#overAll").unmask();
                            dataZoho = $.evalJSON(dataZoho);
                            var zohoUrl = dataZoho.URL;
                            var success = dataZoho.RESULT;
                            var warning = dataZoho.WARNING;
                            if (success === true || success === "TRUE") {
                                refreshGrid();
                                if (Ifresco.Registry.get("ArrangeList")=="horizontal") {
                                    window.open(zohoUrl);
                                }
                                else {
                                    var previewPanel = Ext.getCmp('previewPanel');
                                    if (previewPanel) {
                                        previewPanel.add({
                                            title: "Zoho",
                                            closable:true,
                                            items: [ Ext.create('Ext.ux.IFrameComponent', { id: 'zohoWriter', url: zohoUrl, width:'100%', height:"100%" }) ],
                                            tbar:[{
                                                iconCls:'open-window',
                                                id: 'zohoWriterOpenBtn',
                                                text:'<?php echo $view['translator']->trans('Open in new Window'); ?>',
                                                handler: function(){
                                                    window.open(zohoUrl);
                                                },
                                                scope: this
                                            }]
                                        }).show();
                                    }
                                }
                            }
                            else {
                                $("#overAll").unmask();
                                Ext.MessageBox.show({
                                    title: '<?php echo $view['translator']->trans('Upload to Zoho Failed!'); ?>',
                                    msg: warning,
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.ERROR
                                });
                                cancelCheckout(workingCopyId,nodeId,MimeType,false);
                            }
                        },
                        beforeSend: function(req) {

                        }
                    });
                }
                else {
                    Ext.MessageBox.alert('<?php echo $view['translator']->trans('Error'); ?>', '<?php echo $view['translator']->trans('An unknown problem occured at the check out process.'); ?>');
                }
            },
            beforeSend: function(req) {
                $("#overAll").mask("<?php echo $view['translator']->trans('Checkout to Zoho'); ?>",300);
            }
        });
    }

    function editInZoho(nodeId,MimeType) {
        $.ajax({
            cache: false,
            url : "<?php echo $view['router']->generate('ifresco_client_login') ?>?Zoho/ZohoUpload&nodeId="+nodeId,


            success : function (dataZoho) {
                $("#overAll").unmask();
                dataZoho = $.evalJSON(dataZoho);
                var zohoUrl = dataZoho.URL;
                var success = dataZoho.RESULT;
                var warning = dataZoho.WARNING;
                if (success === true || success === "TRUE") {
                    refreshGrid();
                    if (Ifresco.Registry.get("ArrangeList")=="horizontal") {
                        window.open(zohoUrl);
                    }
                    else {
                        var previewPanel = Ext.getCmp('previewPanel');
                        if (previewPanel) {
                            previewPanel.add({
                                title: "Zoho Writer",
                                closable:true,
                                items: [ Ext.create('Ext.ux.IFrameComponent', { id: 'zohoWriter', url: zohoUrl, width:'100%', height:"100%" }) ],
                                tbar:[{
                                    iconCls:'open-window',
                                    id: 'zohoWriterOpenBtn',
                                    text:'<?php echo $view['translator']->trans('Open in new Window'); ?>',
                                    handler: function(){
                                        window.open(zohoUrl);
                                    },
                                    scope: this
                                }]
                            }).show();
                        }
                    }
                }
                else {
                    Ext.MessageBox.show({
                        title: '<?php echo $view['translator']->trans('Upload to Zoho Failed!'); ?>',
                        msg: warning,
                        buttons: Ext.MessageBox.OK,
                        icon: Ext.MessageBox.ERROR
                    });
                }
            },
            beforeSend: function(req) {
                $("#overAll").mask("<?php echo $view['translator']->trans('Edit in Zoho Writer'); ?>",300);
            }
        });
    }

    function loadMetaData(nodeId) {
        //goes to component/MetaDataTab

    }

    function loadParentMetaData(nodeId) {

        if (nodeId !== null & typeof nodeId !== 'undefined') {

            var metaHeight = $("#parentMetadataContent").height();
            if ($.browser.msie) {
                $("#metadataWindow").html('');
                metaHeight = $("#parentMetadataContent").height();
            }

            jQuery14.manageAjax.add('metadata', {
                isLocal: true,
                url : "<?php echo $view['router']->generate('ifresco_client_metadata_view') ?>",
                data: "ofParent=true&nodeId="+nodeId+"&containerName=<?php echo $addContainer; ?>&height="+metaHeight,
                success : function (data) {
                    $("#parentMetadataWindow").html('');
                    $("#parentMetadataContent").unmask();

                    $("#parentMetadataWindow").html(data);
                },
                beforeSend: function(xhr) {
                    $("#parentMetadataContent").mask("<?php echo $view['translator']->trans('Loading...'); ?>",300);
                },
                error:function(jqXHR, textStatus, errorThrown) {
                    if (textStatus != 'abort')
                        $("#parentMetadataWindow").html('');
                    $("#parentMetadataContent").unmask();
                }
            });
        }
    }

    //function fillAspectsWindow(myNodeId) {
    function fillAspectsWindow(myNodeId) {

        $.ajax({
            cache: false,
            url : "<?php echo $view['router']->generate('ifresco_client_metadata_add_aspects') ?>",
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
        $.post("<?php echo $view['router']->generate('ifresco_client_metadata_save_aspects') ?>", postData, function(data) {
            var succes = data.success;
            var nodeId = data.nodeId;

            if (PanelNodeId == nodeId)
                loadMetaData(nodeId);
        }, "json");
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
                            $.post("<?php echo $view['router']->generate('ifresco_client_metadata_save_aspects') ?>", postData, function(data) {
                                var succes = data.success;
                                var nodeId = data.nodeId;

                                loadMetaData(nodeId);

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

    var winVersionLookup = null;
    function versionLookup(data) {
        if (versionStore !== null) {
            if (versionStore.getCount() > 0) {
                var rowNode = versionStore.getAt(0);

                var versionLookupTpl = new Ext.Template('<div><div style="float:left;width:40%;"><label><b>Version:</b></label> <i>{version}</i></div><div style="float:left;width:40%;"><label><b><?php echo $view['translator']->trans('Date:'); ?></b></label> <i>{dateFormat}</i></div><div style="float:left;width:40%;"><label><b><?php echo $view['translator']->trans('Author:'); ?></b></label> <i>{author}</i></div><div style="float:left;"><label><b><?php echo $view['translator']->trans('Note:'); ?></b></label></div><div style="float:left;">&nbsp;<i>{description}</i></div></div>');
                var versionLookupHtml = versionLookupTpl.apply(rowNode.data);


                winVersionLookup = Ext.create('Ext.window.Window', {
                    modal:true,
                    id:'version-lookup-window',
                    layout:'fit',
                    width:650,
                    height:630,
                    boxMinWidth:650,
                    boxMinHeight:630,
                    boxMaxHeight:630,
                    closeAction:'destroy',
                    constrain: true,
                    title:"<?php echo $view['translator']->trans('Detailed Version Information'); ?>",
                    plain: true,

                    items: Ext.create('Ext.panel.Panel', {
                        //id:'version-lookup-window-panel',
                        layout:{
                            type: 'hbox',
                            align : 'stretch',
                            pack  : 'start'
                        },
                        height:'100%',
                        bodyStyle:'background-color:#e0e8f6;',
                        border:false,
                        tbar:[{
                            iconCls: 'add-version',
                            id: 'add-version-det',
                            cls: 'x-btn-icon',
                            tooltip: '<?php echo $view['translator']->trans('New Version'); ?>',
                            scope:this,
                            handler: function(){
                                SelectedVersion = {nodeId: PanelNodeId};
                                var data = {nodeId: PanelNodeId};
                                createNewVersion(data,false);
                            }
                        },
                            {
                                iconCls: 'upload-version',
                                id: 'upload-version-det',
                                cls: 'x-btn-icon',
                                tooltip: '<?php echo $view['translator']->trans('Upload new Version'); ?>',
                                scope:this,
                                handler: function(){
                                    SelectedVersion = {nodeId: PanelNodeId};
                                    var data = {nodeId: PanelNodeId};
                                    createNewVersion(data,true);
                                }
                            },'-',{
                                iconCls: 'download-node',
                                id: 'download-selected-version-det',
                                tooltip: '<?php echo $view['translator']->trans('Download Selected Version'); ?>',
                                scope: this,
                                disabled: true,
                                handler: function(){
                                    versionList.fireEvent('itemdblclick', versionList, null, null, Ext.getCmp('version-lookup-view').getSelectionModel().getLastSelected().index);
                                }
                            },'-',{
                                iconCls: 'revert-version',
                                id: 'revert-selected-version-det',
                                tooltip: '<?php echo $view['translator']->trans('Revert to Selected Version'); ?>',
                                scope: this,
                                disabled: true,
                                handler: function(){

                                    var selectedVersion = Ext.getCmp('version-lookup-view').getSelectionModel().getLastSelected();

                                    var ParentNodeId = selectedVersion.data.nodeRef;
                                    var Version = selectedVersion.data.version;
                                    var VersionId = selectedVersion.data.nodeId;

                                    SelectedVersion = {nodeId: ParentNodeId,versionNodeId:VersionId,version:Version};
                                    Ext.MessageBox.show({
                                        title: '<?php echo $view['translator']->trans('Revert to Version'); ?>',
                                        msg: "<?php echo $view['translator']->trans('Are you sure you want to revert to Version:'); ?> <b>"+Version+"</b>",
                                        buttons: Ext.MessageBox.YESNO,
                                        icon: Ext.MessageBox.INFO,
                                        fn:revertVersion
                                    });
                                }
                            }],
                        items: [

                            Ext.create('Ext.list.ListView', {
                                store: versionStore,
                                header:false,
                                multiSelect: false,
                                singleSelect: true,
                                height:562,
                                width: 170,
                                id:'version-lookup-view',
                                loadingText: '<?php echo $view['translator']->trans('Loading...'); ?>',
                                deferEmptyText: false,
                                emptyText: '<span style="font-size:12px;"><img src="/images/icons/information.png" align="absmiddle"> <?php echo $view['translator']->trans('This document has no version history.'); ?></span>',
                                reserveScrollOffset: true,

                                columns: [{
                                    header: '<?php echo $view['translator']->trans('Version'); ?>',
                                    dataIndex: 'version',
                                    width: 60
                                },
                                    {
                                        header: '<?php echo $view['translator']->trans('Date'); ?>',
                                        xtype: 'datecolumn',
                                        format: '<?php echo $DateFormat; ?> <?php echo $TimeFormat; ?>',
                                        dataIndex: 'date',
                                        width: 110
                                    }],

                                listeners: {
                                    itemcontextmenu: function(gridx, record, item, index, event, eOpts){
                                        var existingMenu = Ext.getCmp('version-detail-ctx');
                                        if (existingMenu !== null && typeof existingMenu !== 'undefined') {
                                            existingMenu.destroy();
                                        }

                                        var selectedVersion = versionStore.getAt(index);
                                        var ParentNodeId = selectedVersion.data.nodeRef;
                                        var Version = selectedVersion.data.version;
                                        var VersionId = selectedVersion.data.nodeId;

                                        var selectedItem = Ext.getCmp('dataGrid').getSelectionModel().getSelection()[0] || false;

                                        var editPerm = false;
                                        if(selectedItem)
                                        {
                                            editPerm = selectedItem.data.alfresco_perm_edit;
                                        }

                                        event.stopEvent();
                                        var mnxContext = Ext.create('Ext.menu.Menu', {
                                            id:'version-detail-ctx',
                                            items: [{
                                                iconCls: 'revert-version',
                                                text: '<?php echo $view['translator']->trans('Revert to this Version'); ?>',
                                                scope:this,
                                                disabled: !editPerm,
                                                handler: function(){
                                                    SelectedVersion = {nodeId: ParentNodeId,versionNodeId:VersionId,version:Version};
                                                    Ext.MessageBox.show({
                                                        title: '<?php echo $view['translator']->trans('Revert to Version'); ?>',
                                                        msg: "<?php echo $view['translator']->trans('Are you sure you want to revert to Version:'); ?> <b>"+Version+"</b>",
                                                        buttons: Ext.MessageBox.YESNO,
                                                        icon: Ext.MessageBox.INFO,
                                                        fn:revertVersion
                                                    });
                                                }
                                            },'-',{
                                                iconCls: 'add-version',
                                                text: '<?php echo $view['translator']->trans('New Version'); ?>',
                                                scope: this,
                                                disabled: !editPerm,
                                                handler: function(){
                                                    SelectedVersion = {nodeId: ParentNodeId,versionNodeId:VersionId,version:Version};
                                                    var data = {nodeId: ParentNodeId,versionNodeId:VersionId,version:Version};
                                                    createNewVersion(data,false);
                                                }
                                            },
                                                {
                                                    iconCls: 'upload-version',
                                                    text: '<?php echo $view['translator']->trans('Upload new Version'); ?>',
                                                    scope:this,
                                                    disabled: !editPerm,
                                                    handler: function(){
                                                        SelectedVersion = {nodeId: ParentNodeId,versionNodeId:VersionId,version:Version};
                                                        var data = {nodeId: ParentNodeId,versionNodeId:VersionId,version:Version};
                                                        createNewVersion(data,true);
                                                    }
                                                },'-',{
                                                    iconCls: 'download-node',
                                                    text: '<?php echo $view['translator']->trans('Download this Version'); ?>',
                                                    scope: this,
                                                    handler: function(){
                                                        versionList.fireEvent('itemdblclick', versionList, null, null, index);
                                                    }
                                                }]
                                        });
                                        mnxContext.showAt(event.xy);
                                    },

                                    itemclick: function() {
                                        var listView = Ext.getCmp('version-lookup-view');
                                        var infoPanel = Ext.getCmp('version-lookup-info-panel');
                                        if (infoPanel && listView) {
                                            var selNode = listView.getView().selModel.getSelection();
                                            versionLookupTpl.overwrite(infoPanel.body, selNode[0].data);
                                            var versionTabPanel = Ext.getCmp('version-lookup-tab');
                                            if (versionTabPanel) {
                                                var activeTab = versionTabPanel.activeTab;
                                                if (typeof activeTab !== 'undefined') {
                                                    if (activeTab.title === "<?php echo $view['translator']->trans('Preview'); ?>")
                                                        loadVersionPreview(selNode[0].data.nodeId);
                                                    else if (activeTab.title === "<?php echo $view['translator']->trans('Metadata'); ?>") {
                                                        loadVersionMetaData(selNode[0].data.nodeId);
                                                    }
                                                }
                                            }
                                        }
                                    },
                                    selectionchange: function(t, selected, eOpts) {
                                        Ext.getCmp('download-selected-version-det').setDisabled(!selected.length);

                                        var selectedItem = Ext.getCmp('dataGrid').getSelectionModel().getSelection()[0] || false;

                                        var editPerm = false;
                                        if(selectedItem)
                                        {
                                            editPerm = selectedItem.data.alfresco_perm_edit;
                                        }

                                        Ext.getCmp('revert-selected-version-det').setDisabled(!selected.length || !editPerm);

                                    }
                                }
                            })
                            ,
                            {
                                flex:1,
                                layout: {
                                    type: 'vbox',
                                    align : 'stretch',
                                    pack  : 'start'
                                },
                                bodyStyle:'background-color:#e0e8f6;',
                                items: [
                                    {
                                        bodyStyle:'background-color:#e0e8f6;padding:5px;',
                                        height:80,
                                        id:'version-lookup-info-panel',
                                        html:versionLookupHtml,
                                        border:false,
                                        frame:false
                                    },
                                    {
                                        flex:1,
                                        xtype: 'tabpanel',
                                        border:false,
                                        plain: true,
                                        id:'version-lookup-tab',
                                        defaults:{autoHeight: true},
                                        activeTab: 0,
                                        items: [{
                                            title: '<?php echo $view['translator']->trans('Preview'); ?>',
                                            cls: 'inner-tab-custom',
                                            layout:'fit',
                                            id:'version-lookup-preview'
                                        }
                                            /*,{
                                             title: '<?php echo $view['translator']->trans('Metadata'); ?>',
                                             cls: 'inner-tab-custom',
                                             layout:'fit',
                                             id:'version-lookup-metadata'
                                             }*/
                                        ],
                                        listeners: {
                                            tabchange: function(tabPanel, tab){
                                                if (typeof tab !== 'undefined') {
                                                    var listView = Ext.getCmp('version-lookup-view');
                                                    if (listView) {
                                                        var selNode = listView.getSelectedRecords();
                                                        if (typeof selNode === 'undefined' || typeof selNode[0] === 'undefined')
                                                            return;
                                                        var title = tab.title;
                                                        title = title.toLowerCase();

                                                        var nodeId = selNode[0].data.nodeId;
                                                        if (typeof nodeId !== 'undefined' && nodeId !== null) {
                                                            switch(title) {
                                                                case "metadata":
                                                                    loadVersionMetaData(nodeId);
                                                                    break;
                                                                case "preview":
                                                                    loadVersionPreview(nodeId);
                                                                    break;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                ]

                            }
                        ]
                    }),
                    listeners:{
                        'beforeshow':{
                            fn:function() {

                            }
                        },
                        'close': {
                            fn:function() {
                                //$("#version-lookup-window-panel").html('');
                            }
                        }
                    },

                    buttons: [{
                        text: '<?php echo $view['translator']->trans('Close'); ?>',
                        handler: function() {
                            //$("#version-lookup-window-panel").html('');
                            winVersionLookup.close(this);
                        }
                    }]
                });

                winVersionLookup.show();

                var listView = Ext.getCmp('version-lookup-view');
                if (listView) {
                    //listView.getStore().load({params: data });
                    listView.getView().select(0);
                    loadVersionPreview(rowNode.data.nodeId);
                }
            }
        }
    }

    function loadVersionMetaData(nodeId) {
        if (nodeId !== null & typeof nodeId !== 'undefined') {
            /*var metaHeight =  $("#version-lookup-tab").height();  */
            var metaHeight = "480";

            $.ajax({
                cache: false,
                url : "<?php echo $view['router']->generate('ifresco_client_metadata_view') ?>",
                data: "nodeId=workspace://version2Store/"+nodeId+"&containerName=<?php echo $addContainer; ?>&height="+metaHeight,
                success : function (data) {
                    $("#version-lookup-tab").unmask();
                    $("#version-lookup-metadata").html(data);
                },
                beforeSend: function(xhr) {
                    $("#version-lookup-metadata").html('');
                    $("#version-lookup-tab").mask("<?php echo $view['translator']->trans('Loading...'); ?>",300);
                }
            });
        }
    }


    function showNewVersionWindow(data,title,btnText,saveFunc,addData) {
        //if(!winNewVersion) {
        winNewVersion = Ext.create('Ext.window.Window', {
            modal:true,
            id:'newversion-window',
            layout:'fit',
            width:516,
            height:216,
            resizable: false,
            constrain: true,
            closeAction:'destroy',
            title:title,
            plain: true,

            items: Ext.create('Ext.panel.Panel', {
                id:'newversion-window-panel',
                layout:'fit',
                bodyStyle:'background-color:#e0e8f6;',
                border:false
            }),
            listeners:{
                'beforeshow':{
                    fn:function() {
                        $(".plupload").show();
                    }
                },
                'close': {
                    fn:function() {
                        $(".plupload").hide();
                        $("#newversion-window-panel").html('');
                    }
                }
            },

            buttons: [{
                id:'uploadNewVersionBtn', // no containerName here!!!
                text: btnText,
                handler: function() {
                    var postData = getVersionWindowInfo(SelectedVersion.nodeId);
                    var SelectedVersionData = jQuery.extend(data, postData);
                    SelectedVersionData.nodeId = SelectedVersion.nodeId;
                    eval(saveFunc+"(SelectedVersionData)");

                }
            },
                {
                    text: '<?php echo $view['translator']->trans('Close'); ?>',
                    handler: function() {
                        $("#newversion-window-panel").html('');
                        winNewVersion.close();
                    }
                }]
        });
        //}
        winNewVersion.show();

        $.ajax({
            cache: false,
            url : "<?php echo $view['router']->generate('ifresco_client_versioning_new_version') ?>"+addData,
            data: data,

            success : function (data) {
                $("#newversion-window").unmask();
                $("#newversion-window-panel").html(data);
            },
            beforeSend : function() {
                $("#newversion-window").mask("<?php echo $view['translator']->trans('Loading...'); ?>",300);
                $("#newversion-window-panel").html('');
            }
        });


    }

    function createNewVersion(data,upload) {
        if (data !== null) {
            if (upload === true)
                showNewVersionWindow(data,"<?php echo $view['translator']->trans('Upload a new Version'); ?>","<?php echo $view['translator']->trans('Save & Upload'); ?>","saveUploadNewVersion","?enableUpload&filter="+SelectedVersion.nodeId);
            else
                showNewVersionWindow(data,"<?php echo $view['translator']->trans('Create a new Version'); ?>","<?php echo $view['translator']->trans('Save'); ?>","saveNewVersion","");
        }
    }

    function saveUploadNewVersion(data) {
        uploadNewVersion(data.nodeId, winNewVersion,versionList);
    }

    function revertVersion(btn) {
        if (btn === "yes") {
            if (SelectedVersion !== null) {
                showNewVersionWindow(SelectedVersion,"<?php echo $view['translator']->trans('Revert Version'); ?>","<?php echo $view['translator']->trans('Save'); ?>","saveRevertVersion","?hideVersionNumber");
            }
        }
    }


    function saveNewVersion(data) {
        if (SelectedVersion !== null) {
            $.ajax({
                cache: false,
                url : "<?php echo $view['router']->generate('ifresco_client_versioning_create_new_version') ?>",
                data: data,
                error:function() {
                    $("#newversion-window").unmask();
                    winNewVersion.close();
                },
                success : function (data) {
                    $("#newversion-window").unmask();
                    var jsonData = $.JSON.decode(data);
                    var nodeIdOrg = jsonData.nodeId;
                    versionList.store.load({params:{'nodeId':nodeIdOrg}});
                    winNewVersion.close();
                },
                beforeSend : function() {
                    $("#newversion-window").mask("<?php echo $view['translator']->trans('Loading...'); ?>",300);
                }
            });
        }
        else
            winNewVersion.close();
    }

    function saveRevertVersion(data) {
        if (SelectedVersion !== null) {
            $.ajax({
                cache: false,
                //type:"POST",
                url : "<?php echo $view['router']->generate('ifresco_client_versioning_revert_version') ?>",
                data: data,
                error:function() {
                    $("#newversion-window").unmask();
                    winNewVersion.close();
                },
                success : function (data) {
                    $("#newversion-window").unmask();
                    var jsonData = $.JSON.decode(data);
                    var nodeIdOrg = jsonData.nodeId;
                    if (jsonData.success === true) {
                        Ext.MessageBox.show({
                            title: '<?php echo $view['translator']->trans('Success'); ?>',
                            msg: "<?php echo $view['translator']->trans('Successfully reverted to the Version:'); ?> <b>"+SelectedVersion.version+"</b>",
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.INFO
                        });
                    }
                    else {
                        Ext.MessageBox.show({
                            title: '<?php echo $view['translator']->trans('Error'); ?>',
                            msg: "<?php echo $view['translator']->trans('Something went wrong at the revert process'); ?>",
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.WARNING,
                            fn:showRenderer
                        });
                    }
                    versionList.store.load({params:{'nodeId':nodeIdOrg}});

                    winNewVersion.close();
                },
                beforeSend : function() {
                    $("#newversion-window").mask("<?php echo $view['translator']->trans('Loading...'); ?>",300);
                }
            });
        }
        else
            winNewVersion.close();
    }

    var winSpecifyType = null;
    var SpecifyTypeStore = null;
    var SpecifyTypeCombo = null;
    var SpecifyTypeSelectedId = null;

    function loadVersionPreview(nodeId) {
        /*var previewHeight = $("#version-lookup-tab").height()-30;*/
        var previewHeight = "450";
        $.ajax({
            cache: false,
            url : "<?php echo $view['router']->generate('ifresco_client_view_index') ?>",
            data: "nodeId=workspace://version2Store/"+nodeId+"&height="+previewHeight+"px",
            success : function (data) {
                $("#version-lookup-tab").unmask();
                $("#version-lookup-preview").html(data);
            },
            beforeSend: function(xhr) {
                $("#version-lookup-preview").html('');
                $("#version-lookup-tab").mask("<?php echo $view['translator']->trans('Loading...'); ?>",300);

            }
        });
    }



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
                            url: '<?php echo $view['router']->generate('ifresco_client_metadata_content_type_list') ?>',
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
                    constrain: true,
                    title:'<?php echo $view['translator']->trans('Specify Type'); ?>',
                    plain: true,

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
                                $.post("<?php echo $view['router']->generate('ifresco_client_metadata_save_content_type') ?>", "nodes="+jsonNodes+"&type="+type, function(data) {
                                    var succes = data.success;
                                    if (!multiNodes) {
                                        var nodeId = data.nodeId;
                                        loadMetaData(nodeId);
                                    }

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

                //}
                //else {
                //
                //}
                //SpecifyTypeSelectedId = null;
                SpecifyTypeStore.load({params:{'nodeId':myNodeId}});
                winSpecifyType.show();
            }

        }
    }

    function openFolder(nodeId,nodeText) {
        var tabnodeid = nodeId.replace(/-/g,"");
        addTabDynamic('tab-'+tabnodeid,nodeText);

        $.ajax({
            cache: false,
            url : "<?php echo $view['router']->generate('ifresco_client_data_grid_index') ?>?containerName="+tabnodeid+"&addContainer=<?php echo $nextContainer; ?>&columnsetid="+currentColumnsetid,


            success : function (data) {
                $("#overAll").unmask();
                $("#tab-"+tabnodeid).html(data);

                eval("reloadGridData"+tabnodeid+"({params:{'nodeId':nodeId,'columnsetid':currentColumnsetid}});");
            },
            beforeSend: function(req) {
                $("#overAll").mask("<?php echo $view['translator']->trans('Loading'); ?> "+nodeText+"...",300);
            }
        });
    }

    function deleteNode(nodeId,nodeName,nodeType) {
        $(".PDFRenderer").hide();
        Ext.MessageBox.show({
            title:'<?php echo $view['translator']->trans('Delete?'); ?>',
            msg: '<?php echo $view['translator']->trans('Do you really want to delete:'); ?> <br><b>'+nodeName+'</b>',
            fn:function(btn) {
                if (btn === "yes") {
                    $("#documentGrid").mask("<?php echo $view['translator']->trans('Deleting'); ?> "+nodeName+" ...",300);
                    $.post("<?php echo $view['router']->generate('ifresco_client_node_actions_delete_node') ?>", "nodeId="+nodeId+"&nodeType="+nodeType, function(data) {
                        var succes = data.success;
                        $("#documentGrid").unmask();
                        if (succes === true) {

                            //Ext.MessageBox.alert('Deleted', 'Document <b>'+nodeName+'</b> deleted successfully.');
                            refreshGrid();

                        }
                        else {
                            Ext.MessageBox.show({
                                title: '<?php echo $view['translator']->trans('Error'); ?>',
                                msg: data.message,
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.WARNING
                            })
                        }
                    }, "json");
                }
                $(".PDFRenderer").show();
            },
            buttons: Ext.MessageBox.YESNO,
            icon: Ext.MessageBox.QUESTION
        });
    }


    function getLastParams() {
        return lastParams;
    }

    function deleteNodes(nodes) {
        $(".PDFRenderer").hide();
        var jsonNodes = encodeURIComponent($.toJSON(nodes));
        var nodeNames = "";
        var nodeNamesLoad = "";
        for (var i = 0; i < nodes.length; ++i) {
            nodeNames += nodes[i].nodeName+"<br>";
            nodeNamesLoad += "<i>"+nodes[i].nodeName+"</i><br>";
        }
        Ext.MessageBox.show({
            title:'<?php echo $view['translator']->trans('Delete?'); ?>',
            msg: '<?php echo $view['translator']->trans('Do you really want to delete:'); ?> <br><b>'+nodeNames+'</b>',
            fn:function(btn) {
                if (btn === "yes") {
                    $("#documentGrid").mask("<?php echo $view['translator']->trans('Deleting'); ?> <br>"+nodeNamesLoad,300);
                    $.post("<?php echo $view['router']->generate('ifresco_client_node_actions_delete_nodes') ?>", "nodes="+jsonNodes, function(data) {
                        var succes = data.success;
                        $("#documentGrid").unmask();
                        if (succes === true) {
                            //Ext.MessageBox.alert('Deleted', 'Document <b>'+nodeName+'</b> deleted successfully.');
                            refreshGrid();
                        }
                        else {
                            Ext.MessageBox.show({
                                title: '<?php echo $view['translator']->trans('Error'); ?>',
                                msg: data.message,
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.WARNING
                            })
                        }
                    }, "json");
                }
                $(".PDFRenderer").show();
            },
            buttons: Ext.MessageBox.YESNO,
            icon: Ext.MessageBox.QUESTION
        });
    }

    function ocrFiles(nodes) {
        var encodedNodes = $.toJSON(nodes);
        //
        //console.log(encodedNodes);
        $.ajax({
            cache: false,
            type:"POST",
            url: "<?php echo $view['router']->generate('ifresco_client_node_actions_ocr_files') ?>",
            data: "nodes="+encodedNodes,
            success : function (data) {
                $("#overAll").unmask();
            },
            beforeSend: function(req) {
                $("#overAll").mask("<?php echo $view['translator']->trans('Send files to OCR Engine...'); ?>",300);
            }

        });
    }

    var lastWidth = 0;
    var photo_array = [];
    var initHeight = 400;

    function manageThumbData() {
        lastWidth = $("div#toppics").innerWidth()-20;

        $("div.toppicrow").width(lastWidth);
        processPhotos(photo_array);
    }

    function loadThumbnailView() {
        var container = $("#thumbnailContainer");
        if (container != null && mainGrid.store.data.items.length > 0) {
            container.height($("#thumbnailGrid").height()-40);

            var lastStoreParams = lastParams.params;
            lastStoreParams["thumbs"] = true;

            $("#thumbnailContainer").mask("<?php echo $view['translator']->trans('Loading Thumbnails...'); ?>",300);
            mainGrid.store.on('load', function(){
                photo_array = mainGrid.store.data.items;

                manageThumbData();
                $("#thumbnailContainer").unmask();
            });

            mainGrid.store.load({params:lastStoreParams});

            $(window).resize(function() {
                var nowWidth = $("div#toppics").innerWidth();
                if( nowWidth * 1.1 < lastWidth || nowWidth * 0.9 > lastWidth )
                {
                    manageThumbData();
                }
            });
        }
    }

    function processPhotos(photos) {

        var d = jQuery16("div.toppicrow");
        var w = d.eq(0).innerWidth();
        // initial height - effectively the maximum height +/- 10%;
        var h = initHeight;
        var border = 1;

        var ws = [];
        $.each(photos, function(key, val) {
            //var wt = parseInt(val.width_n, 10);
            //var ht = parseInt(val.height_n, 10);
            var wt = parseInt(val.data.alfresco_thumbnail_width, 10);
            var ht = parseInt(val.data.alfresco_thumbnail_height, 10);
            if( ht != h ) { wt = Math.floor(wt * (h / ht)); }
            ws.push(wt);
        });

        var baseLine = 0;
        var rowNum = 0;
        var total = 0;
        var currentRow = 0;
        while(rowNum++ != d.length)
        {
            var d = jQuery16("div.toppicrow");
            var d_row = d.eq(rowNum-1);
            d_row.empty();

            // number of images appearing in this row
            var c = 0;
            // total width of images in this row - including margins
            var tw = 0;

            // calculate width of images and number of images to view in this row.
            while( tw * 1.1 < w)
            {
                tw += ws[baseLine + c++] + border * 2;
            }

            // Ratio of actual width of row to total width of images to be used.
            var r = w / tw;

            // image number being processed
            var i = 0;
            // reset total width to be total width of processed images
            tw = 0;
            // new height is not original height * ratio
            var ht = Math.floor(h * r);
            while( i < c )
            {
                var photo = photos[baseLine + i];
                // Calculate new width based on ratio
                var wt = Math.floor(ws[baseLine + i] * r);
                // add to total width with margins
                tw += wt + border * 2;
                // Create image, set src, width, height and margin
                (function() {
                    if (typeof photo != 'undefined' && photo != null) {
                        if(photo.data.alfresco_type == '{http://www.alfresco.org/model/content/1.0}folder')
                        {
                            var img = jQuery16('<div data-index="'+currentRow+'" title="'+photo.data.alfresco_thumbname+'" style="width: '+wt+'px; height:'+ht+'px; line-height:'+ht+'px;" class="thumb-folder-block">'+photo.data.alfresco_thumbname+'</div>').css("margin", border + "px");
                        }
                        else {
                            var img = jQuery16('<img src="'+ photo.data.alfresco_thumbnail +'" style="cursor:pointer;" data-index="'+currentRow+'" width="'+wt+'" height="'+ht+'" alt="'+photo.data.alfresco_thumbname+'" title="'+photo.data.alfresco_thumbname+'" style="float:left;"/>').css("margin", border + "px");
                        }
                        //var url = photo.url;
                        img.click(function() { var index = $(this).attr("data-index");  mainGrid.getSelectionModel().selectRow(parseInt(index)); });
                        d_row.append(img);
                        currentRow++;
                    }
                })();
                i++;
                total++;
            }

            // if total width is slightly smaller than
            // actual div width then add 1 to each
            // photo width till they match
            i = 0;
            while( tw < w )
            {
                var img1 = d_row.find("img:nth-child(" + (i + 1) + ")");
                img1.width(img1.width() + 1);
                i = (i + 1) % c;
                tw++;
            }
            // if total width is slightly bigger than
            // actual div width then subtract 1 from each
            // photo width till they match
            i = 0;
            while( tw > w )
            {
                var img2 = d_row.find("img:nth-child(" + (i + 1) + ")");
                img2.width(img2.width() - 1);
                i = (i + 1) % c;
                tw--;
            }

            // set row height to actual height + margins
            d_row.height(ht + border * 2);

            baseLine += c;

            if (total < photos.length) {
                $("#thumbnailContainer").append('<div class="toppicrow"></div>');
            }
        }
    }

    function enableClipBtns() {
        var pasteCopyBtn = Ext.getCmp("pastecopy-clipboard");
        var pasteCutBtn = Ext.getCmp("pastecut-clipboard");
        var pasteLinkBtn = Ext.getCmp("pastelink-clipboard");

        var perms = mainGrid.store.perms;
        var alfresco_perm_create = false;
        if(perms !== false) {
            alfresco_perm_create = perms.alfresco_perm_create;
        }

        pasteCopyBtn.setDisabled(!alfresco_perm_create);
        pasteCutBtn.setDisabled(!alfresco_perm_create);
        pasteLinkBtn.setDisabled(!alfresco_perm_create);
    }


    var winEmail = null;
    var emailForm = null;

    function sendMail(nodes) {
        var attachments = "";
        var clearNodes = [];
        $.each(nodes,function(index,node) {
            if(node.shortType == 'file') {
                attachments += node.docName+"&nbsp;&nbsp;&nbsp;";
                clearNodes.push(node);
            }
        });
        nodes = clearNodes;
        var encodedNodes = $.toJSON(nodes);
        emailForm = Ext.create('Ext.FormPanel', {
            labelAlign: 'left',
            frame:true,
            bodyStyle:'padding:5px 5px 0',
            height:300,
            items: [{
                layout: 'form',
                items: [
                    {
                        xtype:'textfield',
                        fieldLabel: '<?php echo $view['translator']->trans('To'); ?>',
                        name: 'to',
                        anchor:'100%',
                        vtype:'multiemail',
                        allowBlank:false
                    },{
                        xtype:'textfield',
                        fieldLabel: '<?php echo $view['translator']->trans('Cc'); ?>',
                        name: 'cc',
                        anchor:'100%',
                        vtype:'multiemail'
                    },{
                        xtype:'textfield',
                        fieldLabel: '<?php echo $view['translator']->trans('Bcc'); ?>',
                        name: 'bcc',
                        anchor:'100%',
                        vtype:'multiemail'
                    },{
                        xtype:'textfield',
                        fieldLabel: '<?php echo $view['translator']->trans('Subject'); ?>',
                        name: 'subject',
                        anchor:'100%'
                    },{
                        xtype:'panel',
                        fieldLabel: '<?php echo $view['translator']->trans('Attachments'); ?>',
                        anchor:'100%',
                        html:attachments
                    },{
                        xtype:'hidden',
                        name:'nodes',
                        value:encodedNodes
                    }
                ]
            },{
                xtype:'htmleditor',
                id:'body',
                //fieldLabel:'Body',
                height:150,
                name: 'body',
                anchor:'100%',
                hideLabel: true,
                anchor: '100% -130'
            }]
        });

        winEmail = Ext.create('Ext.Window', {
            modal:true,
            id:'email-window',
            title:'<?php echo $view['translator']->trans('Send Attachment(s) via Email'); ?>',
            plain: true,
            constrain: true,
            buttonAlign: 'right', //buttons aligned to the right
            //bodyStyle:'background-color:#fff;padding: 10px',
            width:540,
            height:400,
            closeAction:'destroy',
            plain: true,
            resizable:true,

            items: emailForm,
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
            buttons:[
                {
                    text:'<?php echo $view['translator']->trans('Send'); ?>',
                    handler:function() {
                        if(emailForm.getForm().isValid()) {
                            emailForm.getForm().submit({
                                url: '<?php echo $view['router']->generate('ifresco_client_node_actions_mail_node') ?>',
                                waitMsg: '<?php echo $view['translator']->trans('Sending Email'); ?>',
                                success: function(form, result) {
                                    Ext.Msg.alert('<?php echo $view['translator']->trans('Success'); ?>', "<?php echo $view['translator']->trans('We could send your email successfully!'); ?>");
                                    winEmail.close();
                                },
                                failure: function(form, result) {
                                    Ext.Msg.alert('<?php echo $view['translator']->trans('Error'); ?>', result.result.errorMsg);
                                }
                            });
                        }
                    }
                },
                {
                    text:'<?php echo $view['translator']->trans('Cancel'); ?>',
                    handler: function() {
                        winEmail.close(this);
                    }
                }
            ]
        });

        winEmail.show();
    }
</script>