Ext.define('Ifresco.menu.Category',{
    extend: 'Ext.menu.Menu',
    alias: 'widget.ifrescoMenuCategory',
    isRoot:false,
    treeComponent: false,
    initComponent: function () {
    	Ext.apply(this, {
            items: this.getItems()
        });
    	
        this.callParent();
    },
    
    getItems: function () {
    	var self = this;
    	
    	var items = [];
    	
    	if (!this.isRoot) {
	    	items.push({
	            iconCls: 'ifresco-icon-add-tab-button', 
	            text: Ifresco.helper.Translations.trans('Open in new tab'),
	            hidden:this.isRoot,
	            handler: function(){
	                //var title = this.nodeId != "root" ? this.DocName : Ifresco.helper.Translations.trans('Repository');
	                this.fireEvent('openCategory', this.record.raw.nodeId, this.record.get('id'), Ifresco.helper.Translations.trans('Category')+": "+this.record.get('text'));
	            },
	            scope:this
	        },'-');
    	}
    	
    	items.push({
            iconCls: 'ifresco-icon-add-sub-category', // TODO icons missing
            text: Ifresco.helper.Translations.trans('Add a subcategory'),
            handler: function(){
            	this.hide();
            	if(this.record.data.leaf == false) {
            		this.record.expand(false, this.finishExpandAdd, this);
                    //treeCat.doLayout();
                    //treeCat.render();
                    return;
                }
                this.finishExpandAdd(this.record);
            },
            scope:this
        },{
            iconCls: 'ifresco-icon-rename-category', // TODO icons missing
            text: Ifresco.helper.Translations.trans('Rename this category'),
            hidden:this.isRoot,
            handler: function(){
            	this.onTreeEditing(this.record);
            },
            scope:this
        },{
            iconCls: 'ifresco-icon-remove-category', // TODO icons missing
            text: Ifresco.helper.Translations.trans('Delete this category'),
            hidden:this.isRoot,
            handler: function(){
            	var nodeId = this.record.raw.nodeId;
                var nodeText = this.record.raw.text;
                
                Ext.Ajax.request({
                	url : Routing.generate('ifresco_client_category_remove'),
                	params: {
                		nodeId: nodeId
                	},
                    loadMask: true,
                    disableCaching: true,
                    success: function (response) {
                    	var data = Ext.decode(response.responseText);
                    	if (data.success == true) {
                    		this.record.remove();
                        }
                        else {
                            return false;
                        }
                    },
                    failure: function() {
                    	return false;
                    }
                });	
            },
            scope:this
        });
    	return items;
    },

    onTreeEditing: function(n) {
        var treeComponent = Ext.ComponentQuery.query("ifrescoTreeCategories")[0];
        
        if(n.data.id == 'newNode') {
            var checkExpanded = function(){
                if(n.parentNode.isExpanded())
                    setTimeout(function(){ treeComponent.getPlugin('treeEditPlugin').startEdit(n, treeComponent.columns[0]);}, 500)
                else
                    setTimeout(checkExpanded, 500)
            };
            checkExpanded();
        }
        else {
            treeComponent.getPlugin('treeEditPlugin').startEdit(n, treeComponent.columns[0]);
        }
    },
    
    finishExpandAdd: function(node) {
        if (node[0] != undefined)
            node = node[0].parentNode;

        var newNode = node.appendChild({id: "newNode", iconCls:'new-category', cls:'folder', text: "", leaf: true});
        if (newNode != null) {
            node.data.leaf = false;
            node.expand(false);
            //treeCat.doLayout();
            //treeCat.render();
            this.onTreeEditing(newNode);
        }
    }
});
