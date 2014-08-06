Ext.define('Ifresco.view.window.Category', {
    extend: 'Ext.window.Window',
    alias: 'widget.ifrescoViewWindowCategory',
    modal:true,
    layout:'fit',
    width:650,
    height:500,
    closeAction:'destroy',
    constrain: true,
    plain: true,
    resizable: false,
    parent: null,
    added: [],
    removed: [],
    selectedCategories: [],
    
    initComponent: function () {
    	var self = this;
    	
    	var currentStore = self.parent.down("dataview[cls=ifresco-edit-metadata-categories-view]").getStore();
    	this.selectedCategories = [];
    	currentStore.each(function (r) {
    		this.selectedCategories.push(r.data);
        }, this);
    	
        Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('Manage Categories'),
            items: [{
            	xtype: 'panel',
            	layout: 'hbox',
            	border:false,
            	items:[{
            		xtype: 'treepanel',
            		rootVisible:true,
                    autoScroll:true,
                    store: Ifresco.store.TreeCategories.create({}),
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
                    width:260,
                    height:440,
                    listeners:{
                        beforedrop:function() {
                        	var dv = this.down("dataview");
                        	if (dv) {
                        		dv.applyStyles({'background-color':'#f0f0f0'});
                        	}
                            /*var t = this.down('panel[cls=~ifresco-window-category-target]').body.child('div.ifresco-window-category-drop-target');
                            if(t) {
                                t.applyStyles({'background-color':'#f0f0f0'});
                            }*/
                        },
                        enddrag:function() {
                        	var dv = this.down("dataview");
                        	if (dv) {
                        		dv.applyStyles({'background-color':'white'});
                        	}
                            /*var t = this.down('panel[cls=~ifresco-window-category-target]').body.child('div.ifresco-window-category-drop-target');
                            if(t) {
                                t.applyStyles({'background-color':'white'});
                            }*/
                        },
                        scope: this
                    }
            	},{
            		xtype: 'dataview',
            		cls: 'ifresco-manage-categories-view',
            		width:378,
                    height:440,
	            	layout: 'fit',
	            	autoScroll: true,
	            	emptyText: Ifresco.helper.Translations.trans('Drop a category here!'),
            		tpl: [
	                      '<ul class="ifresco-manage-categories">',
	                      '<tpl for=".">',
	                          '<li class="category-item">',
		                        '<div class="category-buttons">',
		                       		'<div class="category-delete"></div>',
	                          	'</div>',
	                          	'<div class="category-name">{path}</div>',
	                          	
	                          '</li>',
	                      '</tpl></ul>',
	                      '<div class="x-clear"></div>'
	                 ],
	                 prepareData: function(data) {
	                	 console.log("PREPARE DATA",data);
	                	 /*console.log("PREPARE DATA")
	                     Ext.apply(data, {
	                         path: data.raw.path
	                     });*/
	                     return data;
	                 },
	                 store: {
	                     fields: ['id', 'path'],
	                     proxy: {
	                         type: 'memory'
	                     },
	                     data: this.selectedCategories
	                 },
	                 multiSelect: false,
	                 trackOver: true,
	                 overItemCls: 'x-item-over',
	                 itemSelector: 'li.category-item',
	                 selectedItemCls: 'x-item-selected',
	                 listeners: {
	                     selectionchange: function(dv, nodes ){
	                     },
	                     itemmousedown: function (me, record, item, index, e) {
	                         var className = e.target.className;
	                         if ("category-delete" == className) {
	                        	 var store = me.getStore();
	                             store.removeAt(index);
	                             //store.save();
	                         }
	                     },
	                     render: function(v) {
	                    	 v.dropTarget = Ext.create('Ext.dd.DropTarget', v.el, {
	                             ddGroup: 'catDDGroup',
	                             notifyDrop: function (source, e, dropData) {
	                                 var recordAlreadyExists = false;
	                                 var record = dropData.records[0],
	                                 	 nodeId = record.raw.nodeId,
	                                 	 text = record.raw.text,	                                
	                                 	 path = self.findPathOfNode(record,text);
	                                 
	                                 
	                                 console.log("MY DROPDATA",nodeId,text,path,record,dropData);
	                                 v.store.each(function (r) {
	                                     if (r.data.id == nodeId) {
	                                         recordAlreadyExists = true;
	                                     }
	                                 });

	                                 if (recordAlreadyExists == false) {                        
	                                     v.store.add({id: nodeId, path: path});
	                                     console.log("ADDED TO STORE")
	                                     /*var nodes = v.container.dom.childNodes[0].childNodes;
	                                     var index = v.container.dom.childNodes[0].childNodes.length -1;

	                                     //
	                                     //Here is where you create the dropTarget for the new node
	                                     //
	                                     console.log("ADDED TO STORE")
	                                     nodes[index].dropTarget = Ext.create('Ext.dd.DropTarget', nodes[index], {
	                                         ddGroup: 'catDDGroup',
	                                         notifyDrop: function (source, e, dropData) {
	                                             console.log('success drop')                            
	                                         }
	                                     });*/
	                                 }

	                             }
	                         });
	                     },
	                     scope: this
	                 }
            	}/*{ // TODO - CHANGE TO DATAVIEW
                    width:378,
                    height:440,
                    title: Ifresco.helper.Translations.trans('Manage Categories'),

                    layout:'fit',
                    cls:'ifresco-window-category-target',
                    bodyStyle:'font-size:13px',
                    title: Ifresco.helper.Translations.trans('Drop a category here.'),
                    html:'<div class="ifresco-window-category-drop-target" '
                        //+'style="border:1px silver solid;margin:20px;padding:8px;height:140px">'
                        +'style="height:100%;overflow:auto;">'
                        +'<ul></ul></div>',

                    // setup drop target after we're rendered
                    afterRender:function() {
                        Ext.Panel.prototype.afterRender.apply(this, arguments);
                        this.dropTarget = this.body.child('div.ifresco-window-category-drop-target');
                        var dd = new Ext.dd.DropTarget(this.dropTarget, {
                            ddGroup:'catDDGroup',
                            notifyDrop:function(dd, e, node) {
                                var nodeid = node.records[0].raw.nodeId;
                                var text = node.records[0].raw.text;
                                
                                var path = self.findPathOfNode(node.records[0],text);
                                
                                console.log("NOTIFY DROP",path,node,node.records)
                                //if ($.inArray(nodeid,added<?php echo $containerName; ?>) < 0 && node.records[0].internalId != "root") {
                                 //   addWindowEntry<?php echo $containerName; ?>(nodeid,path);
                                   // return true;
                                //}
                                return false;
                            }
                        });
                    }

                }*/]
            }],
            buttons: [{
                text: Ifresco.helper.Translations.trans('Save'),
                handler: function() {
                	var window = this;
                	
                	var store = window.parent.down("dataview[cls=ifresco-edit-metadata-categories-view]").getStore();
                	store.removeAll();
                	this.down("dataview[cls=ifresco-manage-categories-view]").store.each(function (r) {
                		console.log("ADD DATA IS",r,r.data);
                		//store.add({id: r.raw.nodeId, path: r.raw.path});
                		store.add(r.data);
                		
                    });
                	
                	window.close();
                    /*var window = this;
                    var values = this.down('form').getValues();
                    values.nodeId = this.nodeId;
                    Ext.Ajax.request({
                        url: Routing.generate('ifresco_client_folder_actions_space_create'),
                        params: values,
                        success: function (req) {
                            if(window.parent) {
                                window.parent.getStore().reload();
                            }

                            //TODO: reload tree
                            window.close();
                        },
                        failure: function (data) {
                            var result = Ext.decode(data.responseText);
                            Ext.MessageBox.show({
                                title: Ifresco.helper.Translations.trans('Error'),
                                msg: result.data.message,
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.WARNING
                            });
                        }
                    });*/
                },
                scope: this
            },{
                text: Ifresco.helper.Translations.trans('Close'),
                handler: function() {
                    this.close();
                },
                scope: this
            }]
        });

        this.callParent();
    },
    
    findPathOfNode: function(node,path) {
        if (node.parentNode == null || node.parentNode.isRoot() == true)
            return path;
        else {
            var parent = node.parentNode;
            var path = parent.data.text+"/"+path;
            return this.findPathOfNode(parent,path);
        }
        return path;
    }
});
