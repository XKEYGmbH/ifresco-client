<link rel="stylesheet" href="/js/BoxSelect/BoxSelect.css" type="text/css">
<script type="text/javascript" src="/js/BoxSelect/BoxSelect.js"></script>
<script type="text/javascript">

var added = null;
var removed = null;
var winCategory = false;
function selectCategory(panelId) {
    if(!winCategory) {
        added = [];
        removed = [];

        manageTreeCatStore = Ext.create('Ext.data.TreeStore', {
            proxy: {
                type: 'ajax',
                url: '<?php echo $view['router']->generate('ifrescoClientCategoryTreeBundle_getjson'); ?>',
                actionMethods: 'POST'
            },
            root: {
                text: '<?php echo $view['translator']->trans('Categories'); ?>',
                draggable:false,
                id:'root',
                disabled:true,
                expanded:true
            }
        });

        var manageTreeCat = Ext.create('Ext.tree.Panel', {
            rootVisible:true,
            autoScroll:true,
            store: manageTreeCatStore,
            animate:true,
            ddConfig: {
                ddGroup:'catDDGroup',
                enableDrag:true
            },
            viewConfig: {
                plugins: {
                    ptype: 'treeviewdragdrop',
                    ddGroup:'catDDGroup',
                    enableDrag:true,
                    enableDrop:false
                }
            },

            containerScroll: true,
            //el:'manageCategoriesTree',
            width:260,
            height:440,


            listeners:{
                beforedrop:function() {
                    var t = Ext.getCmp('target').body.child('div.drop-target');
                    if(t) {
                        t.applyStyles({'background-color':'#f0f0f0'});
                    }
                }
                ,enddrag:function() {
                    var t = Ext.getCmp('target').body.child('div.drop-target');
                    if(t) {
                        t.applyStyles({'background-color':'white'});
                    }
                }
            }
        });

        winCategory = Ext.create('Ext.window.Window', {
            modal:true,
            id:'category-window',
            renderTo: 'meta-form',
            layout:'fit',
            width:650,
            height:500,
            closeAction:'hide',
            constrain: true,
            title:'<?php echo $view['translator']->trans('Manage Categories'); ?>',
            plain: true,

            items: Ext.create('Ext.panel.Panel', {
                id: 'category-window-panel',
                layout: 'hbox',
                //bodyStyle: 'overflow-y: auto',
                border:false,
                items : [manageTreeCat,{
                    width:378,
                    height:440,
                    title:'<?php echo $view['translator']->trans('Manage Categories'); ?>',

                    layout:'fit'
                    ,id:'target'
                    ,bodyStyle:'font-size:13px'
                    ,title:'<?php echo $view['translator']->trans('Drop a category here.'); ?>'
                    ,html:'<div class="drop-target" '
                        //+'style="border:1px silver solid;margin:20px;padding:8px;height:140px">'
                        +'style="height:100%;overflow:auto;">'
                        +'<ul></ul></div>'

                    // setup drop target after we're rendered
                    ,afterRender:function() {
                        Ext.Panel.prototype.afterRender.apply(this, arguments);
                        this.dropTarget = this.body.child('div.drop-target');
                        var dd = new Ext.dd.DropTarget(this.dropTarget, {
                            ddGroup:'catDDGroup'

                            ,notifyDrop:function(dd, e, node) {
                                var nodeid = node.records[0].raw.nodeId;
                                var text = node.records[0].raw.text;
                                var path = findPathOfNode(node.records[0],text);
                                if ($.inArray(nodeid,added) < 0 && node.records[0].internalId != "root") {
                                    addWindowEntry(nodeid,path);
                                    return true;
                                }
                                return false;
                            }
                        });
                    }

                }]
            }),
            listeners:{
                'beforeshow':{
                    fn:function() {
                        added = [];
                        $('div.drop-target ul').html('');
                        $("."+panelId+" li").each(function() {
                            var nodeid = $(this).children(".categoriesValues").val();
                            var path = $(this).children(".path").html();
                            addWindowEntry(nodeid,path);
                        });
                    }
                },
                'hide': {
                    fn:function() {
                    }
                }
            },

            buttons: [{
                text: '<?php echo $view['translator']->trans('Save'); ?>',
                handler: function() {
                    $("#category-window").mask("<?php echo $view['translator']->trans('Adopting...'); ?>",300);
                    $("div.drop-target li").each(function() {
                        var nodeid = $(this).attr("data-noderef");
                        var path = $(this).attr("data-path");
                        if ($(".categoriesValues[value="+nodeid+"]").length <= 0) {
                            $("."+panelId+" ul").append('<li><input class="categoriesValues categoriesValues" type="hidden" name="categories[]" value="'+nodeid+'"><span class="path">'+path+'</span> <div class="removeCategoryBtn"><img src="/images/icons/cross.png" style="cursor:pointer;" title="<?php echo $view['translator']->trans('Remove category') ?>" class="removeBtn" /></div></li>');
                        }
                    });

                    $.each(removed, function(index,nodeid) {
                        var $this = $(".categoriesValues[value="+nodeid+"]");
                        if ($this.length > 0) {
                            $this.parent().slideUp();
                            $this.parent().remove();
                        }
                    });

                    bindRemove();
                    $("#category-window").unmask();
                    Ext.getCmp('meta-form').doCollapseExpand();
                    winCategory.hide(this);

                }
            },
                {
                    text: '<?php echo $view['translator']->trans('Close'); ?>',
                    handler: function() {
                        winCategory.hide(this);
                    }
                }]
        });

    }

    winCategory.show();
}

function addWindowEntry(nodeid,path) {
    if ($.inArray(nodeid,added) < 0) {
        added.push(nodeid);
        var msg = '<li class="categoryWindowObj" data-noderef="' + nodeid + '" data-path="' + path + '" style="border-bottom:1px dotted #99BBE8;padding:4px;"><img align="absmiddle" src="/images/icons/tick.png" style="padding-right:5px;"> ' + path + ' <img style="float:right;cursor:pointer;" src="/images/icons/cross.png" align="absmiddle" class="removeWindowCategory"></li>';
        Ext.getCmp('target').body.child('div.drop-target ul').createChild(msg);
        bindWindowRemove();
    }
}

function bindWindowRemove() {
    $(".removeWindowCategory").unbind('click');
    $(".removeWindowCategory").bind("click", function( event ) {
        removed.push($(this).parent().attr("data-noderef"));
        $(this).parent().fadeOut();
        $(this).parent().remove();
        added.remove($(this).parent().attr("data-noderef"));
    });
}

function findPathOfNode(node,path) {
    if (node.parentNode == null || node.parentNode.isRoot() == true)
        return path;
    else {
        var parent = node.parentNode;
        var path = parent.data.text+"/"+path;
        return findPathOfNode(parent,path);
    }
    return path;
}
</script>

<div id="allMetaData">
    <div id="metaForm">
    </div>

    <div id="additionalData">
    </div>
</div>

