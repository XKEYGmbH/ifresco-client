<script type='text/javascript'>

var templateDesignerWindow = null;
var templateDesignerCombo = null;
var propertyAddWindow = null;
var selectedId = null;
$(document).ready(function() {
    $("#clearCache").click(function() {
        $.ajax({
            cache:false,
            url: '<?php echo $view['router']->generate('ifrescoClientAdminBundle_cacheclear'); ?>',

            success : function (data) {
                $("#overAll").unmask();
            },
            beforeSend: function(xhr) {
                $("#overAll").mask("<?php echo $view['translator']->trans('Clearing cache...'); ?>",300);
            }
        });
    })
});

Ext.onReady(function(){
    propertyAddWindow = Ext.create('Ext.window.Window', {
        renderTo:'admintab',
        layout:'fit',
        title: 'Select properties to add',
        width:550,
        modal: true,
        height:380,
        closeAction:'hide',
        plain: true,
        constrain: true,
        items: [
            Ext.create('Ext.panel.Panel', {
                frame:true,
                header:false,
                contentEl:'contentPropertyWindow'
            })
        ],
        buttons: [{
            text:'<?php echo $view['translator']->trans('Add'); ?>',
            disabled:false,
            handler: function(a,b,c,d,e,f) {
                var location = $("#propertiesaddlocation").val();

                if (location === "quicksearchAdmin" || location == "quickexportAdmin") {
                    var values = new String($("#propertiesadd").val());
                    var valueString = "";
                    if (values.length !== 0) {
                        var valuearr = values.split(",");
                        if (valuearr.length > 0) {
                            for (var i = 0; i < valuearr.length; i++) {
                                var splitter = valuearr[i].split("/");
                                var name = splitter[0];
                                var title = splitter[2];
                                if (title.length === 0)
                                    var showTitle = name;
                                else
                                    var showTitle = title;

                                var datatype = splitter[3];
                                valueString += '<li id="'+valuearr[i]+'/property"><div class="form_row"><div class="showTitle">'+showTitle+'</div> <span style="font-size:10px">('+name+')</span><br /><span style="font-size:10px">('+datatype+')</span><div class="metaActions"><img src="/images/icons/arrow_out.png" class="moveAction"></div></div></li>';
                            }
                        }

                        $(".colContainer").append(valueString);
                        var orderArr = $('.colContainer').sortable('toArray');
                        var json = $.JSON.encode(orderArr);
                        $("#colSubmitValues").val(json);

                        $("#contentPropertyWindow").html('<input type="hidden" value="'+location+'" name="propertiesaddlocation" id="propertiesaddlocation"><select id="propertiesadd" multiple="multiple" name="propertiesadd[]" title="<?php echo $view['translator']->trans('Select a property...'); ?>"></select>');

                        $("#propertiesadd").asmSelect({
                            addItemTarget: 'top',
                            sortable: true,
                            url: '<?php echo $view['router']->generate('ifrescoClientAdminBundle_templateproperties'); ?>'
                        });
                    }
                }
                else if (location === "colAdmin") {
                    var values = new String($("#propertiesadd").val());
                    var valueString = "";
                    if (values.length !== 0) {
                        var valuearr = values.split(",");
                        if (valuearr.length > 0) {
                            for (var i = 0; i < valuearr.length; i++) {
                                var splitter = valuearr[i].split("/");
                                var name = splitter[0];
                                var title = splitter[2];
                                if (title.length === 0)
                                    var showTitle = name;
                                else
                                    var showTitle = title;

                                var datatype = splitter[3];
                                valueString += '<li id="'+valuearr[i]+'/property/show/nosort/desc"><div class="form_row">'+showTitle+' <br /><span style="font-size:10px">('+name+')</span><br /><span style="font-size:10px">('+datatype+')</span><div class="options"><input type="checkbox" class="hiddenFlag" name="hiddenFlag" value="true" onclick="changeSelectState(\''+valuearr[i]+'/property/show/nosort/desc\',this)"> <?php echo $view['translator']->trans('Hide on default'); ?><br><input type="radio" class="hiddenFlag" name="defaultSortFlag" value="true" onclick="changeDefaultState(\''+valuearr[i]+'/property/show/nosort/desc\',this)"> <?php echo $view['translator']->trans('Sort default'); ?>  <input type="checkbox" class="hiddenFlag" value="true" onclick="changeAscState(\''+valuearr[i]+'/property/show/nosort/desc\',this)"> <?php echo $view['translator']->trans('Ascending'); ?></div><div class="metaActions"><img src="/images/icons/arrow_out.png" class="moveAction"></div></div></li>';
                            }
                        }

                        $(".colContainer").append(valueString);
                        var orderArr = $('.colContainer').sortable('toArray');
                        var json = $.JSON.encode(orderArr);
                        $("#colSubmitValues").val(json);

                        $("#contentPropertyWindow").html('<input type="hidden" value="'+location+'" name="propertiesaddlocation" id="propertiesaddlocation"><select id="propertiesadd" multiple="multiple" name="propertiesadd[]" title="<?php echo $view['translator']->trans('Select a property...'); ?>"></select>');

                        $("#propertiesadd").asmSelect({
                            addItemTarget: 'top',
                            sortable: true,
                            url: '<?php echo $view['router']->generate('ifrescoClientAdminBundle_templateproperties'); ?>'
                        });
                    }
                }
                else if (location.length > 0) {

                    var values = new String($("#propertiesadd").val());

                    var valueString = "";
                    if (values.length !== 0) {
                        var valuearr = values.split(",");
                        if (valuearr.length > 0) {
                            for (var i = 0; i < valuearr.length; i++) {
                                var splitter = valuearr[i].split("/");
                                var name = splitter[0];
                                if (name == "cm:content") {
                                    var title = '<?php echo $view['translator']->trans('Content'); ?>';
                                    var datatype = 'd:content';
                                    var showTitle = title;
                                    var realId = name+"//"+title+"/"+datatype;
                                }
                                else {
                                    var title = splitter[2] || '';
                                    if (title.length === 0)
                                        var showTitle = name;
                                    else
                                        var showTitle = title;


                                    var datatype = splitter[3] || 'd:text';
                                    var realId = valuearr[i] || '';
                                }
                                valueString += '<li id="'+realId+'/property"><div class="form_row"><div class="showTitle">'+showTitle+'</div> <span style="font-size:10px">('+name+')</span><br /><span style="font-size:10px">('+datatype+')</span><div class="metaActions"><img src="/images/icons/arrow_out.png" class="moveAction"></div></div></li>';
                            }
                        }


                        if (location === "left") {
                            $(".containerLeft").append(valueString);
                            var orderArr = $('.containerLeft').sortable('toArray');
                            var json = $.JSON.encode(orderArr);
                            $("#col1Values").val(json);
                        }
                        else if (location === "right") {
                            $(".containerRight").append(valueString);
                            var orderArr = $('.containerRight').sortable('toArray');
                            var json = $.JSON.encode(orderArr);
                            $("#col2Values").val(json);
                        }
                        else if (location === "customfield") {
                            $(".containerCustomfield").append(valueString);
                            var orderArr = $('.containerCustomfield').sortable('toArray');
                            var json = $.JSON.encode(orderArr);
                            $("#customFieldValues").val(json);
                        }
                        else if (/customfield-\d+/.test(location)) {
                            customFieldNum = location.replace(/customfield-/,'');
                            $('.custom-field a[rel='+customFieldNum+']~.containerCustomfield').append(valueString);
                            var orderArr = $('.custom-field a[rel='+customFieldNum+']~.containerCustomfield').sortable('toArray');
                            var json = $.JSON.encode(orderArr);
                            $("#customFieldValues-"+customFieldNum).val(json);
                        }

                        $("#contentPropertyWindow").html('<input type="hidden" value="'+location+'" name="propertiesaddlocation" id="propertiesaddlocation"><select id="propertiesadd" multiple="multiple" name="propertiesadd[]" title="<?php echo $view['translator']->trans('Select a property...'); ?>"></select>');

                        $("#propertiesadd").asmSelect({
                            addItemTarget: 'top',
                            sortable: true,
                            url: '<?php echo $view['router']->generate('ifrescoClientAdminBundle_templateproperties'); ?>'
                        });
                    }

                }
                propertyAddWindow.close();
            }
        },{
            text: '<?php echo $view['translator']->trans('Close'); ?>',
            handler: function(){
                propertyAddWindow.hide();
            }
        }]
    });
});

function changeSelectState(id,checkbox) {
    if (checkbox.checked === true) {
        var newid = id.replace(/\/show/g,"/hide");
        var el = document.getElementById(id);
        el.id = newid;
    }
    else {
        var newid = id.replace(/\/hide/g,"/show");
        var el = document.getElementById(id);
        el.id = newid;
    }
}

function changeDefaultState(id,checkbox) {
    if (checkbox.checked === true) {
        var newid = id.replace(/\/nosort/g,"/sort");
        var el = document.getElementById(id);
        el.id = newid;

    }
    else {
        var newid = id.replace(/\/sort/g,"/nosort");
        var el = document.getElementById(id);
        el.id = newid;

    }
}

function changeAscState(id,checkbox) {
    if (checkbox.checked === true) {
        var newid = id.replace(/\/desc/g,"/asc");
        var el = document.getElementById(id);
        el.id = newid;
    }
    else {
        var newid = id.replace(/\/asc/g,"/desc");
        var el = document.getElementById(id);
        el.id = newid;
    }
}


var propsListScroll = 0;

$(document).ready(function() {
    $("#propertiesadd").asmSelect({
        addItemTarget: 'top',
        sortable: true,
        url: '<?php echo $view['router']->generate('ifrescoClientAdminBundle_templateproperties');  ?>'
    });
});

function loadLink(url,params) {
    jQuery14.manageAjax.add('admin', {
        isLocal: true,
        cache:false,
        url : url,
        data: (params),
        success : function (data) {
            $("#adminPanel").unmask();
            $("#adminContent").html(data);
        },
        beforeSend: function(xhr) {
            $("#adminPanel").mask("<?php echo $view['translator']->trans('Loading...'); ?>",300);
        },
        error:function(jqXHR, textStatus, errorThrown) {
            if (textStatus != 'abort')
                $("#adminContent").html('');
            $("#adminPanel").unmask();
        }
    });
}

function loadRealDesigner() {
    if (selectedId !== null) {
        templateDesignerWindow.hide();
        loadLink('<?php echo $view['router']->generate('ifrescoClientAdminBundle_templatedesigner'); ?>',{'class':selectedId});
    }
}

function loadTemplateDesigner() {
    templateDesignerWindow.show();
}
</script>

<div id="contentModelPanel" class="x-hidden">
    <ul>
        <li><a href="#" onclick="loadTemplateDesigner()"><?php echo $view['translator']->trans('Create a Template'); ?></a></li>
        <li><a href="#" onclick="loadLink('<?php echo $view['router']->generate('ifrescoClientAdminBundle_lookups'); ?>',{});"><?php echo $view['translator']->trans('Lookups'); ?></a></li>
    </ul>
</div>
<div id="dataSourcePanel" class="x-hidden">
    <ul>
        <li><a href="#" onclick="loadLink('<?php echo $view['router']->generate('ifrescoClientAdminBundle_datasources'); ?>',{});"><?php echo $view['translator']->trans('Data Sources'); ?></a></li>
        <li><a href="#" onclick="loadLink('<?php echo $view['router']->generate('ifrescoClientAdminBundle_datasourcecreate'); ?>',{});"><?php echo $view['translator']->trans('Create a Data Sources'); ?></a></li>
    </ul>
</div>
<div id="searchPanelLeft" class="x-hidden">
    <ul>
        <li><a href="#" onclick="loadLink('<?php echo $view['router']->generate('ifrescoClientAdminBundle_searchtemplates');  ?>',{});"><?php echo $view['translator']->trans('Templates'); ?></a></li>
        <li><a href="#" onclick="loadLink('<?php echo $view['router']->generate('ifrescoClientAdminBundle_searchtemplatedesigner'); ?>',{});"><?php echo $view['translator']->trans('Create a Template'); ?></a></li>
        <li><a href="#" onclick="loadLink('<?php echo $view['router']->generate('ifrescoClientAdminBundle_quicksearch'); ?>',{});"><?php echo $view['translator']->trans('Quick Search'); ?></a></li>
        <li><a href="#" onclick="loadLink('<?php echo $view['router']->generate('ifrescoClientAdminBundle_clicksearch'); ?>',{});"><?php echo $view['translator']->trans('Click Search'); ?></a></li>
    </ul>
</div>
<div id="colPanelLeft" class="x-hidden">
    <ul>
        <li><a href="#" onclick="loadLink('<?php echo $view['router']->generate('ifrescoClientAdminBundle_searchcolumnsets'); ?>',{});"><?php echo $view['translator']->trans('Column Sets'); ?></a></li>
        <li><a href="#" onclick="loadLink('<?php echo $view['router']->generate('ifrescoClientAdminBundle_searchcolumnsetsadd'); ?>',{});"><?php echo $view['translator']->trans('Add a Column Set'); ?></a></li>
    </ul>
</div>
<div id="generalSettingsLeft" class="x-hidden">
    <ul>

        <li><a href="#" onclick="loadLink('<?php echo $view['router']->generate('ifrescoClientAdminBundle_systemsettings'); ?>',{});"><?php echo $view['translator']->trans('System'); ?></a></li>
        <li><a href="#" onclick="loadLink('<?php echo $view['router']->generate('ifrescoClientAdminBundle_interface'); ?>',{});"><?php echo $view['translator']->trans('Interface'); ?></a></li>
        <li><a href="#" onclick="loadLink('<?php echo $view['router']->generate('ifrescoClientAdminBundle_emailsettings'); ?>',{});"><?php echo $view['translator']->trans('Email'); ?></a></li>
        <li><a href="#" onclick="loadLink('<?php echo $view['router']->generate('ifrescoClientAdminBundle_aspectlist'); ?>',{});"><?php echo $view['translator']->trans('Aspects'); ?></a></li>
        <li><a href="#" onclick="loadLink('<?php echo $view['router']->generate('ifrescoClientAdminBundle_typelist'); ?>',{});"><?php echo $view['translator']->trans('Content Types'); ?></a></li>

        <li><a href="#" onclick="loadLink('<?php echo $view['router']->generate('ifrescoClientAdminBundle_namespacemapping'); ?>',{});"><?php echo $view['translator']->trans('Namespace Mapping'); ?></a></li>
        <li><a href="#" onclick="loadLink('<?php echo $view['router']->generate('ifrescoClientAdminBundle_propsfilter'); ?>',{});"><?php echo $view['translator']->trans('Property Filter'); ?></a></li>
        <li><a href="#" onclick="loadLink('<?php echo $view['router']->generate('ifrescoClientAdminBundle_onlineediting'); ?>',{});"><?php echo $view['translator']->trans('Online Editing'); ?></a></li>
        <li><a href="#" onclick="loadLink('<?php echo $view['router']->generate('ifrescoClientAdminBundle_uploadallowedtypes'); ?>',{});"><?php echo $view['translator']->trans('Upload Allowed Types'); ?></a></li>
        <li><a href="#" onclick="loadLink('<?php echo $view['router']->generate('ifrescoClientAdminBundle_treerootfolder'); ?>',{});"><?php echo $view['translator']->trans('Tree Root Folder'); ?></a></li>
        <li><a href="#" onclick="loadLink('<?php echo $view['router']->generate('ifrescoClientAdminBundle_dropboxConfig'); ?>',{});"><?php echo $view['translator']->trans('Dropbox Config'); ?></a></li>
    </ul>
</div>
<div id="jobsLeft" class="x-hidden">
    <ul>
        <li><a href="#" onclick="loadLink('<?php echo $view['router']->generate('ifrescoClientAdminBundle_currentjobs'); ?>',{});"><?php echo $view['translator']->trans('Current jobs'); ?></a></li>
        <li><a href="#" onclick="loadLink('<?php echo $view['router']->generate('ifrescoClientAdminBundle_export'); ?>',{});"><?php echo $view['translator']->trans('Export'); ?></a></li>
    </ul>
</div>
<div id="OCRSettingsLeft" class="x-hidden">
    <ul>

        <li><a href="#" onclick="loadLink('<?php echo $view['router']->generate('ifrescoClientAdminBundle_ocrconnection'); ?>',{});"><?php echo $view['translator']->trans('Connection configuration'); ?></a></li>
        <li><a href="#" onclick="loadLink('<?php echo $view['router']->generate('ifrescoClientAdminBundle_ocrstatus'); ?>',{});"><?php echo $view['translator']->trans('Status'); ?></a></li>
        <li><a href="#" onclick="loadLink('<?php echo $view['router']->generate('ifrescoClientAdminBundle_ocrtransformer'); ?>',{});"><?php echo $view['translator']->trans('Transformer Configuration'); ?></a></li>
        <li><a href="#" onclick="loadLink('<?php echo $view['router']->generate('ifrescoClientAdminBundle_ocrjobs'); ?>',{});"><?php echo $view['translator']->trans('Jobs'); ?></a></li>

    </ul>
</div>
<div id="settingsExportLeft" class="x-hidden">
    <ul>
        <li><a href="#" onclick="loadLink('<?php echo $view['router']->generate('ifrescoClientAdminBundle_settingsexport'); ?>',{});"><?php echo $view['translator']->trans('Export'); ?></a>    </li>
        <li><a href="#" onclick="loadLink('<?php echo $view['router']->generate('ifrescoClientAdminBundle_settingsimport'); ?>',{});"><?php echo $view['translator']->trans('Import'); ?></a>    </li>
    </ul>
</div>
<div id="infoPanel" class="x-hidden">
    <b><?php echo $view['translator']->trans('Version'); ?></b>: <?php echo $ifrescoVersion; ?><br>
    <b><?php echo $view['translator']->trans('Report a bug'); ?></b>: <a href="mailto:ifresco@ifresco.at">ifresco@ifresco.at</a>
    <br><br>
    <a href="#" id="clearCache"><?php echo $view['translator']->trans('Clear Cache'); ?></a>
</div>
<div id="allElAdmin">
    <div id="leftpanel"></div>
    <div id='loading' style='display:none'><?php echo $view['translator']->trans('Loading...'); ?></div>
    <div id="adminPanel">
        <div id='adminContent'>

        </div>
    </div>

    <div id="templateDesignerWindow" class="x-hidden">
        <div class="x-window-header"><?php echo $view['translator']->trans('Select a content type'); ?></div>

    </div>

    <div id="propertyAddWindow2" class="x-hidden">
        <div class="x-window-header"><?php echo $view['translator']->trans('Select a property'); ?></div>

        <div id="contentPropertyWindow">
            <input type="hidden" value="" name="propertiesaddlocation" id="propertiesaddlocation">
            <select id="propertiesadd" multiple="multiple" name="propertiesadd[]" title="<?php echo $view['translator']->trans('Select a property...'); ?>"></select>
        </div>
    </div>

</div>