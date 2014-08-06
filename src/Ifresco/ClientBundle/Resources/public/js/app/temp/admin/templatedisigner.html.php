<script type="text/javascript">

function createDeleteAction() {
    $(".deleteAction").click(function() {
        var metaActions = $(this).parent();
        metaActions.parent().slideUp('slow', function() {
            metaActions.parent().remove();
        });
    });
}

function createElements(objEl) {
    if ($("#descBtn").is(".ui-state-active")) {
        $("#descBtn").removeClass("ui-state-active");


        objEl.prepend('<li><div class="form_row">'+
            '<div class="inRow"><div class="editable"></div></div>'+
            '<div class="metaActions">'+
            '<img src="/images/icons/delete.png" class="deleteAction"> '+
            '<img src="/images/icons/arrow_out.png" class="moveAction">'+
            '</div>'+
            '</div></li>');

        $('.editable').inlineEdit();
        createDeleteAction();
    }

    if ($("#headingBtn").is(".ui-state-active")) {
        $("#headingBtn").removeClass("ui-state-active");


        objEl.prepend('<li><div class="form_row">'+
            '<div class="inRow"><h2 class="editable"></h2></div>'+
            '<div class="metaActions">'+
            '<img src="/images/icons/delete.png" class="deleteAction"> '+
            '<img src="/images/icons/arrow_out.png" class="moveAction">'+
            '</div>'+
            '</div></li>');

        $('.editable').inlineEdit();
        createDeleteAction();
    }
}

var tabCount = 0;
var metaTabs = null;
Ext.onReady(function(){
    // basic tabs 1, built from existing content
    var tabs = Ext.create('Ext.tab.Panel', {
        renderTo: 'metaTabs',
        width:450,
        activeTab: 0,
        frame:true,
        closeAction: 'hide',
        plain:true,
        closable: true,
        id:'meta-tabs',
        defaults:{autoHeight: true,autoScroll:true},
        listeners: {
            beforeremove: function(tabPanel, tab) {
                $("#"+tab.id+" ul").children().each(function() {
                    var child = $(this);
                    $(".containerDeleteObjects").append(child);
                });
            }
        }
    });


    metaTabs = tabs;
});


$(document).ready(function() {
    $(".containerLeft").sortable({
        connectWith: 'ul',
        dropOnEmpty: true,
        handle : '.moveAction',
        update : function () {
            //var order = $('.containerLeft').sortable('serialize');
            var orderArr = $('.containerLeft').sortable('toArray');

            var json = $.JSON.encode(orderArr);
            $("#col1Values").val(json);

            //$("#info").load("process-sortable.php?"+order);
        }
    });

    $(".colContainerDeleteObjects").sortable({
        connectWith: 'ul',
        dropOnEmpty: true,
        handle : '.moveAction',
        items: 'li:not(.disabled)',
        cancel: '.disabled',

        update : function () {
            $(".colContainerDeleteObjects").children("li:not(.disabled)").each(function() {
                var child = $(this);
                child.remove();
            });
        }
    });

    $(".containerRight").sortable({
        connectWith: 'ul',
        dropOnEmpty: true,
        handle : '.moveAction',
        update : function (event,ui) {
            //var order = $('.formUl').sortable('serialize');

            var orderArr = $('.containerRight').sortable('toArray');

            var json = $.JSON.encode(orderArr);
            $("#col2Values").val(json);

            //$("#info").load("process-sortable.php?"+order);
        }
    });

    $(".containerDeleteObjects").sortable({
        connectWith: 'ul',
        dropOnEmpty: true,
        handle : '.moveAction',
        items: 'li:not(.disabled)',
        cancel: '.disabled',

        update : function () {
            var order = $('.formUl').sortable('serialize');

            //$("#info").load("process-sortable.php?"+order);
        }
    });

    createDeleteAction();

    $(".containerLeft").click(function() {
        createElements($(this));
    });

    $(".containerRight").click(function() {
        createElements($(this));
    });

    $("#multiColumns").click(function(){
        if ($("#multiColumns").is(":checked")) {
            //$(".containerRight").css({'display':'block'});
            $(".containerRight").slideDown();

        }
        else {
            //$(".containerRight").css({'display':'none'});

            $(".containerRight").slideUp();

            var col1Value = $("#col1Values").val();
            if (col1Value == "" || col1Value == null)
                col1Value = {};
            else
                col1Value = $.JSON.decode(col1Value);

            var col2Value = $("#col2Values").val();
            if (col2Value == "" || col2Value == null)
                col2Value = {};
            else
                col2Value = $.JSON.decode(col2Value);

            $(".containerRight").children().each(function() {
                var child = $(this);
                var name = child.attr('id');
                var indexCol2 = col2Value.indexOf(name);

                if (indexCol2 >= 0)
                    delete col2Value[indexCol2];

                col1Value.push(name);

                $(".containerLeft").append(child);
            });

            col1Value = $.JSON.encode(col1Value);
            $("#col1Values").val(col1Value);

            col2Value = $.JSON.encode(col2Value);
            $("#col2Values").val(col2Value);
        }
    });

    $(".addTab").click(function() {
        if (tabCount == 0) {
            $("#metaTabs").css({'visibility':'visible'});
            metaTabs.show();
        }

        addMetaTab(tabCount,"-");
        tabCount++;
    });

    $("#formAdminMeta").submit(function() {

        if (metaTabs.items.length > 0) {
            var finalObj = {};
            var tabs = [];
            for (var i = 0; i < metaTabs.items.length; i++) {

                var title = metaTabs.items.get(i).title;

                var id = metaTabs.items.get(i).id;
                var items = [];

                //alert(metaTabs.items.get(i).title);
                //finalObj.tabs.add(metaTabs.items.get(i).title);

                $('#'+id+'_list').children().each(function() {
                    var child = $(this);
                    var prop_id = child.attr('id');
                    items.push(prop_id);
                })

                var tabitem = {title:title,items:items};
                tabs.push(tabitem);

            }

            Ext.apply(finalObj, {
                tabs:tabs
            });

            var json = $.JSON.encode(finalObj);
            $("#tabsValues").val(json);
        }
        var formData = form2object('formAdminMeta');
        var jsonData = $.toJSON(formData);

        $.ajax({
            type: 'POST',
            url: "<?php echo $view['router']->generate('ifrescoClientAdminBundle_templatedesignersubmit'); ?>",
            data: "data="+jsonData,
            success: function(data){
                data = $.evalJSON(data);
                //$("#generalSettings").unmask();
                $("#adminPanel").unmask();

                if (data.success == true) {
                    var icon = Ext.MessageBox.INFO;
                    var text = "<?php echo $view['translator']->trans('Successfully saved the template!'); ?>";
                    if(data.editId) {
                        $('#formAdminMeta input[name=edit]').val(data.editId)
                    }
                }
                else {
                    var icon = Ext.MessageBox.ERROR;
                    var text = "<?php echo $view['translator']->trans('An error occured at saving procedure!'); ?>";
                }

                Ext.MessageBox.show({
                    title: '<?php echo $view['translator']->trans('Save Template'); ?>',
                    msg: text,
                    buttons: Ext.MessageBox.OK,
                    icon: icon
                });

            },
            beforeSend: function() {
                $("#adminPanel").mask("<?php echo $view['translator']->trans('Loading...'); ?>",300);
            }
        });

        return false;
    });
});


function restoreTabs(title) {

    if (tabCount == 0) {
        $("#metaTabs").css({'visibility':'visible'});
    }
    var tempCount = tabCount;
    addMetaTab(tabCount,title);
    tabCount++;
    return tempCount;
}

function checkTabsCount() {
    var tabPanel = Ext.getCmp('meta-tabs');
    if(tabPanel.items.items.length == 0) {
        tabPanel.hide();
    }
}

function addMetaTab(tabId,tabTitle){
    var tabPanel = Ext.getCmp('meta-tabs');
    if (tabPanel) {
        tabPanel.add({
            title: tabTitle,
            id:'tab_drop_'+tabId,
            closable:true,
            //height: 300,
            html:'<ul id="tab_drop_'+tabId+'_list" style="margin:10px;" class="tabdrop"></ul>',
            listeners: {
                removed: function(){
                    checkTabsCount();
                }
            },
            tbar:[{
                xtype:'textfield',
                name:tabId+'_titleField',
                id:tabId+'_titleField'
            },
                {
                    xtype:'button',
                    text:'<?php echo $view['translator']->trans('Save title'); ?>',
                    listeners: {
                        click: function(button,eobject) {
                            var titleField = Ext.getCmp(tabId+'_titleField');
                            if (titleField) {
                                var value = titleField.getValue();
                                if (value != "" && value.length > 0 && typeof value != 'undefined') {
                                    var tab = Ext.getCmp('tab_drop_'+tabId);
                                    if (tab) {
                                        tab.setTitle(value);
                                    }
                                }
                            }
                        }
                    }
                }]
        }).show();
        tabPanel.show();
    }

    $("#tab_drop_"+tabId+"_list").sortable({
        connectWith: 'ul',
        dropOnEmpty: true,
        handle : '.moveAction',
        update : function (event,ui) {
            var orderArr = $("#tab_drop_"+tabId+"_list").sortable('toArray');
            Ext.getCmp('tab_drop_'+tabId).doCollapseExpand();
        }
    });

}

function addProperty(where) {
    $("#propertiesaddlocation").val(where);
    propertyAddWindow.show();
}


</script>
<div style="width:95%">
<!--<form id="formAdminMeta" action="<?php echo $view['router']->generate('ifrescoClientAdminBundle_templatedesignersubmit'); ?>" method="post">-->
<form id="formAdminMeta" name="formAdminMeta" action="" method="post">
<input type="hidden" name="edit" value="<?php echo ($FoundTemplate==true ? $Id : 'null'); ?>">
<input type="hidden" name="class" value="<?php echo $Class; ?>">
<input type="hidden" name="col1" id="col1Values" value="">
<input type="hidden" name="col2" id="col2Values" value="">
<input type="hidden" name="tabs" id="tabsValues" value="">
<div id="templateOptions">
    <input type="submit" class="ifrescoBtn saveBtn submitBtn" value="<?php echo $view['translator']->trans('Save'); ?>" name="submit" id="SubmitBtn">
</div>

<div style="width:100%;float:left;margin-bottom:5px;">
    <div style="float:left;width:200px;border:1px solid #617ea3;background-color:#cedff5;color:#214573; padding:5px;margin:5px;">
        <label for=""><?php echo $view['translator']->trans('Multi Columns:'); ?></label>
        <input id="multiColumns" type="checkbox" name="multiColumns" <?php echo ($Multicolumn==1 ? 'checked' : ''); ?> value="true"><br>
        <label for=""><?php echo $view['translator']->trans('Aspects managed as:'); ?></label><br>
        <input type="radio" name="aspectsView" value="tabs" title="Tabs" class="tabsRadio" <?php echo ($Aspectsview == "tabs" ? "checked" : ""); ?>> <img title="Tabs" src="/images/admin/tabs.png" align="absmiddle">
        <input type="radio" name="aspectsView" value="append" title="Append on Column" <?php echo ($Aspectsview != "tabs" ? "checked" : ""); ?> class="appendRadio"> <img title="Append on Column" src="/images/admin/columns.png" align="absmiddle">
        <br />
        <a href="#" class="addTab"><img src="/images/admin/tab_add.png"> <?php echo $view['translator']->trans('Add a tab'); ?></a>
    </div>
    <div style="margin:5px;float:left;width:200px;height:80px;border:1px solid #617ea3;background:url(/images/admin/user-trash.png) no-repeat;background-color:#cedff5;color:#214573;" class="colContainerDeleteObjects">
        <ul class="colContainerDeleteObjects">
            <li class="disabled">
                <span style="display: block; height:80px;margin-left:36px;font-size:10px;font-weight:italic;" class="disabled"><?php echo $view['translator']->trans('To delete items - drop it here'); ?></span>
            </li>
        </ul>

    </div>
</div>

<div class="metaForm">

    <ul class="formUl">
        <li class="first" style="padding-left:10px;border:0;">
            <a href="javascript:addProperty('left')" style="color:#000000;"><img src="/images/icons/add.png" align="absmiddle"> <?php echo $view['translator']->trans('Add a property'); ?></a>
            <ul class="containerLeft">
                <?php
                if ($FoundTemplate == true) {
                    $column1Values = array();
                foreach ($Column1 as $key => $Prop) {
                    $column1Values[] = $Prop->name."/".$Prop->title."/".$Prop->dataType."/".$Prop->type; ?>
                    <li id="<?php echo $Prop->name;?>/<?php echo $Prop->title; ?>/<?php echo $Prop->dataType; ?>/<?php echo $Prop->type; ?>">
                        <div class="form_row">
                            <div class="showTitle custom-name"><?php echo $Prop->title; ?></div>
                            <span style="font-size:10px">(<?php echo $Prop->name; ?>)</span><br />
                            <span style="font-size:10px">(<?php echo $Prop->dataType; ?>)</span>
                            <div class="fieldProps">
                                <label><input name="requiredVals[]" value="<?php echo $Prop->name?>" type="checkbox" <?php echo (isset($Prop->required) && $Prop->required?' checked':''); ?> /> <?php echo $view['translator']->trans('Required'); ?></label>
                                <label><input name="readonlyVals[]" value="<?php echo $Prop->name?>" type="checkbox" <?php echo (isset($Prop->readonly) && $Prop->readonly?' checked':''); ?> /> <?php echo $view['translator']->trans('Read Only'); ?></label>
                            </div>
                            <div class="metaActions">
                                <img src="/images/icons/arrow_out.png" class="moveAction">
                            </div>
                        </div>
                    </li>

                <?php }
                $column1Values = json_encode($column1Values);
                ?>
                    <script type="text/javascript">
                        $("#col1Values").val('<?php echo $column1Values; ?>');
                    </script>
                <?php
                }
                else {
                $column1Values = array();
                foreach($properties as $key => $Prop) {
                $inputname = str_replace(":", ":", $Prop->name); ?>
                    <li id="<?php echo $inputname;?>/<?php echo isset($Prop->title)?$Prop->title:''; ?>/<?php echo $Prop->dataType; ?>/property">
                        <div class="form_row">
                            <div class="showTitle custom-name"><?php echo isset($Prop->title)?$Prop->title:''; ?></div>
                            <span style="font-size:10px">(<?php echo $inputname; ?>)</span><br />
                            <span style="font-size:10px">(<?php echo $Prop->dataType; ?>)</span>
                            <div class="fieldProps">
                                <label><input name="requiredVals[]" value="<?php echo $Prop->name?>" type="checkbox" <?php echo (isset($Prop->required) && $Prop->required?' checked':'');?> /> <?php echo $view['translator']->trans('Required'); ?></label>
                                <label><input name="readonlyVals[]" value="<?php echo $Prop->name?>" type="checkbox" <?php echo (isset($Prop->readonly) && $Prop->readonly?' checked':''); ?> /> <?php echo $view['translator']->trans('Read Only'); ?></label>
                            </div>
                            <div class="metaActions">
                                <img src="/images/icons/arrow_out.png" class="moveAction">
                            </div>
                        </div>
                    </li>
                <?php
                $column1Values[] = $inputname."/".(isset($Prop->title)?$Prop->title:'')."/".$Prop->dataType."/property";
                }
                if (count($associations) > 0) {
                foreach($associations as $key => $Prop) {
                $dataType = isset($Prop->target)?$Prop->target->class:'';
                $inputname = str_replace(":", ":", isset($Prop->name) ? $Prop->name : ''); ?>
                    <li id="<?php echo $inputname;?>/<?php echo isset($Prop->title)?$Prop->title:''; ?>/<?php echo $dataType; ?>/association">
                        <div class="form_row">
                            <div class="showTitle custom-name"><?php echo isset($Prop->title)?$Prop->title:''; ?></div>
                            <span style="font-size:10px">(<?php echo $inputname; ?>)</span><br />
                            <span style="font-size:10px">(<?php echo $dataType; ?>)</span>
                            <div class="fieldProps">
                                <label><input name="requiredVals[]" value="<?php echo $Prop->name?>" type="checkbox" <?php echo (isset($Prop->required) && $Prop->required?' checked':'');?> /> <?php echo $view['translator']->trans('Required'); ?></label>
                                <label><input name="readonlyVals[]" value="<?php echo $Prop->name?>" type="checkbox" <?php echo (isset($Prop->readonly) && $Prop->readonly?' checked':''); ?> /> <?php echo $view['translator']->trans('Read Only'); ?></label>
                            </div>
                            <div class="metaActions">
                                <img src="/images/icons/arrow_out.png" class="moveAction">
                            </div>
                        </div>
                    </li>
                <?php
                $column1Values[] = $inputname."/".(isset($Prop->title)?$Prop->title:'')."/".$dataType."/association";
                }
                }
                $column1Values = json_encode($column1Values);
                ?>
                    <script type="text/javascript">
                        $("#col1Values").val('<?php echo $column1Values; ?>');
                    </script>
                <?php
                }

                ?>
            </ul>
        </li>

        <li>
            <a href="javascript:addProperty('right')" style="color:#000000;"><img src="/images/icons/add.png" align="absmiddle"> <?php echo $view['translator']->trans('Add a property'); ?></a>
            <ul class="containerRight">
                <?php
                if ($FoundTemplate == true) {
                    $column2Values = array();

                foreach ($Column2 as $key => $Prop) {
                    $column2Values[] = $Prop->name."/".$Prop->title."/".$Prop->dataType."/".$Prop->type; ?>
                    <li id="<?php echo $Prop->name;?>/<?php echo $Prop->title; ?>/<?php echo $Prop->dataType; ?>/<?php echo $Prop->type; ?>">
                        <div class="form_row">
                            <div class="showTitle custom-name"><?php echo $Prop->title; ?></div>
                            <span style="font-size:10px">(<?php echo $Prop->name; ?>)</span><br />
                            <span style="font-size:10px">(<?php echo $Prop->dataType; ?>)</span>
                            <div class="fieldProps">
                                <label><input name="requiredVals[]" value="<?php echo $Prop->name?>" type="checkbox" <?php echo (isset($Prop->required) && $Prop->required?' checked':'');?> /> <?php echo $view['translator']->trans('Required'); ?></label>
                                <label><input name="readonlyVals[]" value="<?php echo $Prop->name?>" type="checkbox" <?php echo (isset($Prop->readonly) && $Prop->readonly?' checked':''); ?> /> <?php echo $view['translator']->trans('Read Only'); ?></label>
                            </div>
                            <div class="metaActions">
                                <img src="/images/icons/arrow_out.png" class="moveAction">
                            </div>
                        </div>
                    </li>
                <?php }
                $column2Values = json_encode($column2Values);
                ?>
                    <script type="text/javascript">
                        $("#col2Values").val('<?php echo $column2Values; ?>');
                    </script>
                <?php
                }
                ?>
            </ul>
        </li>
    </ul>
</div>


<?php
if ($FoundTemplate == true) {
    if (isset($Tabs->tabs) && count($Tabs->tabs) > 0) {

        foreach ($Tabs->tabs as $key => $value) {

            $items = $value->items;
            $title = $value->title;

            ?>
            <script type="text/javascript">
                Ext.onReady(function(){
                    var tabId = restoreTabs('<?php echo $title; ?>');

                    <?php foreach ($items as $item) {
                        $itemValue = $item->name."/".$item->title."/".$item->dataType."/".$item->type;
                        ?>
                    $("#tab_drop_"+tabId+"_list").
                        append('<li id="<?php echo $itemValue; ?>">' +
                            '<div class="form_row">' +
                            '<div class="showTitle custom-name"><?php echo $item->title; ?></div> ' +
                            '<span style="font-size:10px">(<?php echo $item->dataType; ?>)</span>' +
                            '<div class="fieldProps">' +
                            '<label><input name="requiredVals[]" value="<?php echo $item->name?>" type="checkbox" <?php echo (isset($item->required) && $item->required?' checked':'');?> /> <?php echo $view['translator']->trans('Required'); ?></label><br />' +
                            '<label><input name="readonlyVals[]" value="<?php echo $item->name?>" type="checkbox" <?php echo (isset($item->readonly) && $item->readonly?' checked':'');?> /> <?php echo $view['translator']->trans('Read Only'); ?></label>' +
                            '</div>'+
                            '<div class="metaActions">' +
                            '<img src="/images/icons/arrow_out.png" class="moveAction">' +
                            '</div>' +
                            '</div></li>');

                    <?php
                } ?>
                });
            </script>
        <?php }
    }
}
?>

<script type="text/javascript">
    var tabs = Ext.getCmp('meta-tabs').items.items;
    for(var i = 0; i < tabs.length; i++) {
        tabs[i].doCollapseExpand();
    }
</script>

<div style="float:left;width:100%;margin:5px;margin-left:10px;">
    <div id="metaTabs" style="visibility: hidden;">

    </div>
</div>
</form>
</div>
