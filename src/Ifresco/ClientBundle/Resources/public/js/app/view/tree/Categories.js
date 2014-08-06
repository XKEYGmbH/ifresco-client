Ext.define('Ifresco.tree.Categories',{
    extend: 'Ext.tree.Panel',
    alias: 'widget.ifrescoTreeCategories',
    hideHeaders: true,
    rootVisible:true,
    border: false,
    autoScroll:true,
    animate:true,
    ddConfig:false,
    height: 'auto',
    split: true,
    containerScroll: true,
    iconCls: 'ifresco-icon-tree-category',
    plugins: [
	      Ext.create('Ext.grid.plugin.CellEditing', {
	          pluginId: 'treeEditPlugin',
	          listeners: {
	              edit: function(editor, e) {
	            	  var treeComponent = Ext.ComponentQuery.query("ifrescoTreeCategories")[0]; // TODO - this did not work here scope didnt work
	                  e.record.commit();
	                  if(e.record.data.id == 'newNode') {
	                      if(e.record.parentNode.raw)
	                          var parentNode = e.record.parentNode.raw.nodeId;
	                      else
	                          var parentNode = e.record.parentNode.data.id
	                      console.log("get parent node",e);
	                      treeComponent.addTreeNode(parentNode, e.value); // TODO - this did not work here scope didnt work
	                  }
	                  else {
	                	  treeComponent.editTreeNode(e.record.raw.nodeId, e.value, e.originalValue); // TODO - this did not work here scope didnt work
	                  }
	              },
	              scope: this
	          }
	      })
    ],
    columns:[{
    	xtype:'treecolumn',
    	dataIndex:'text',
    	flex:1,
    	editor: {
          xtype:'textfield',
          allowBlank:false
    	}
    }],
    initComponent: function () {
        Ext.apply(this, {
            store: new Ifresco.store.TreeCategories(),
            title: Ifresco.helper.Translations.trans('Categories'),
            tbar: [{
                xtype: 'checkbox',
                name: 'browsesub',
                checked: (Ifresco.helper.Registry.get('BrowseSubCategories') == true ? true : false),
                boxLabel: Ifresco.helper.Translations.trans('Browse items in sub-categories?'),
                listeners: {
                    change: function(component,checked, oldchecked, eOpts) {
                        Ifresco.helper.Registry.set("BrowseSubCategories",checked);
                        Ifresco.helper.Registry.save();
                    }
                }
            },'->',{
                iconCls: 'ifresco-icon-refresh',
                tooltip: Ifresco.helper.Translations.trans('Reload'),
                scope:this,
                handler: function(){
                    if(!this.getStore().isLoading()) {
                        this.getStore().load();
                    }
                }
            },'-',{
                iconCls: 'ifresco-icon-expand-all',
                tooltip: Ifresco.helper.Translations.trans('Expand All'),
                handler: function(){
                    this.getStore().getRootNode().expand(true);
                },
                scope: this
            },{
                iconCls: 'ifresco-icon-collapse-all',
                tooltip: Ifresco.helper.Translations.trans('Collapse All'),
                handler: function(){
                    this.getStore().getRootNode().collapse(true);
                },
                scope: this
            }]
        });

        this.callParent();
    },

    listeners: {
        itemclick: function(node, record) {
        	console.log("click ",record);
        	if (record.data.id != "root")
        		this.up('ifrescoViewport').fireEvent('openCategory', record.raw.nodeId, record.get('id'));
        },
        beforeitemdblclick: function(node, record, item, index ,e){
            e.preventDefault();
            return false; // avoid double load
        },
        itemcontextmenu: function (node, record, item, index ,e) {
            var data = {};
            if (record.raw) {
                data = {
                    nodeId: record.data.id,
                    treeComponent: this,
                    isRoot: (record.data.id == "root" ? true : false),
                    record: record
                }
            }

            var menu = Ifresco.menu.Category.create(data);
            e.stopEvent();
            menu.showAt(e.getXY());
        }
    },
    
    addTreeNode: function(parentNodeId, value) {
    	if (parentNodeId.length == 0)
    		parentNodeId = "root";
    	
        if(value.length > 0) {
        	Ext.Ajax.request({
            	url : Routing.generate('ifresco_client_category_add'),
            	params: {
            		nodeId: parentNodeId,
            		value: value
            	},
                loadMask: true,
                disableCaching: true,
                success: function (response) {
                	var data = Ext.decode(response.responseText);
                	if (data.success == true) {
                        return true;
                    }
                    else {
                        return false;
                    }
                },
                failure: function() {
                	return false;
                }
            });	
        }
    },
    
    editTreeNode: function(nodeId, value, oldValue) {
    	
    	Ext.Ajax.request({
        	url : Routing.generate('ifresco_client_category_edit'),
        	params: {
        		nodeId: nodeId,
        		value: value
        	},
            loadMask: true,
            disableCaching: true,
            success: function (response) {
            	var data = Ext.decode(response.responseText);
            	if (data.success == true) {
                    return true;
                }
                else {
                    return false;
                }
            },
            failure: function() {
            	return false;
            }
        });	
    }
});
