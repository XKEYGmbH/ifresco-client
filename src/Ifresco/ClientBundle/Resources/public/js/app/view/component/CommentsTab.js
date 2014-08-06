Ext.define('Ifresco.view.CommentsTab', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.ifrescoViewCommentsTab',
    cls: 'ifresco-comments-tab',
    border: 0,
    deferredRender:false,
    configData: null,
    nodeId: null,
    editMode: false,
    editIndex: null,
    layout: {
        type: 'fit'
    },

    initComponent: function () {
    	var self = this;
        Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('Comments'),
            items: [{
            	xtype: 'panel',
                layout:{
                    type: 'hbox',
                    align : 'stretch',
                    pack  : 'start'
                },
                border:false,
                items: [{
	            	xtype: 'dataview',
	            	layout: 'fit',
	            	flex: 2,
	            	autoScroll: true,
	            	store: Ifresco.store.Comments.create({}),
	            	tpl: [
	                      '<ul class="ifresco-comments">',
	                      '<tpl for=".">',
	                          '<li class="comment-item" id="{name}">',
	                          	'<div class="comment-author">{author.firstName} {author.lastName} - {createdString}</div>',
		                          	'<div class="comment-buttons">',
		                          	'<tpl if="permissions.delete == 1">',
		                          		'<div class="comment-delete"></div>',
		                          	'</tpl>',
		                          	'<tpl if="permissions.edit == 1">',
		                          		'<div class="comment-edit"></div>',
		                          	'</tpl>',
	                          	'</div>',
	                          	'<div class="comment-content">{content}</div>',
	                          	
	                          '</li>',
	                      '</tpl></ul>',
	                      '<div class="x-clear"></div>'
	                 ],
	                 multiSelect: false,
	                 trackOver: true,
	                 overItemCls: 'x-item-over',
	                 itemSelector: 'li.comment-item',
	                 selectedItemCls: 'x-item-selected',
	                 emptyText: Ifresco.helper.Translations.trans('No comments to display'),
	                 prepareData: function(data) {
	                     Ext.apply(data, {
	                         createdString: Ext.util.Format.date(data.createdOnISO, self.configData.DateFormat + ' ' + self.configData.TimeFormat)
	                     });
	                     return data;
	                 },
	                 listeners: {
	                     selectionchange: function(dv, nodes ){
	                     },
	                     itemmousedown: function (me, record, item, index, e) {
	                         var className = e.target.className;
	                         if ("comment-edit" == className) {
	                        	 this.editIndex = index;
	                        	 this.setToEditField();
	                        	 
	                             this.down("form").getForm().loadRecord(record);                             
	                         }
	                         else if ("comment-delete" == className) {
	                        	 var store = me.getStore();
	                             Ext.MessageBox.show({
	                                 title: Ifresco.helper.Translations.trans('Delete comment?'),
	                                 msg: Ifresco.helper.Translations.trans('Are you sure you want to delete this comment:') +"<br><b>"+record.get("content")+"</b>",
	                                 buttons: Ext.MessageBox.YESNO,
	                                 icon: Ext.MessageBox.INFO,
	                                 fn: function(btn) {
	                                 	if (btn === "yes") {
	                                 		store.removeAt(index);
	                                 		store.save();
	                                 		
	                                 	}
	                                 },
	                                 scope: this
	                             });
	                         }
	                     },
	                     scope: this
	                 }
                },{
                	flex: 1,
                    xtype: 'form',
                    bodyStyle:'background-color:#e0e8f6;',
                    border: true,
                    frame: false,
                    padding: '5 5 5 5',
                    layout: {
                        type: 'vbox',
                        padding: '5 5 5 5',
                    },
                    fieldDefaults: {
                        labelAlign: 'top'
                    },
                    items: [{
                        xtype:'htmleditor',
                        fieldLabel: Ifresco.helper.Translations.trans('Comment'),
                        layout:'fit',
                        name: 'content',
                        anchor:'100%',
                        hideLabel: false,
                        editMode: false
                    },{
                        xtype: 'panel',
                        border: false,
                        frame: false,
                        layout: {
                            type: 'hbox'
                        },
                        items: [{
	                    	xtype: 'button',
	                    	cls: 'ifresco-comment-save-button',
	                    	text: Ifresco.helper.Translations.trans('Save new comment'),
	                    	fieldLabel: Ifresco.helper.Translations.trans('Save comment'),
	                    	handler: function() {
	                    		var text = this.down("htmleditor").getValue(),
	                    			store = this.down("dataview").getStore();

	                    		if (this.editMode) {
	                    			store.getAt(this.editIndex).set("content",text);
	                    		}
	                    		else {
	                    			comment = Ext.ModelManager.create({content: text, nodeRef:'new-node'}, 'Ifresco.model.Comments');

	                    			store.getProxy().extraParams = {
		                    		    nodeId: this.nodeId
		                    		};
	                    			
	                    			store.add(comment);
	                    		}

	                    		store.save();

	                    		this.down("htmleditor").reset();
	                    		this.setToNewField();
	                    	},
	                    	scope: this
	                    },{
	                    	xtype: 'button',
	                    	cls: 'ifresco-comment-edit-cancel',
	                    	hidden: true,
	                    	text: Ifresco.helper.Translations.trans('Cancel edit'),
	                    	fieldLabel: Ifresco.helper.Translations.trans('Cancel edit'),
	                    	handler: function() {
	                    		this.setToNewField();
	                    	},
	                    	scope: this
	                    }]
                    }]
                }]
            }],
            listeners: {
            	scope: this
            }
        });

        this.callParent();
    },
    
    setToEditField: function() {
    	this.editMode = true;
    	this.down("form button[cls~=ifresco-comment-save-button]").setText(Ifresco.helper.Translations.trans('Edit comment'));
        this.down("form button[cls~=ifresco-comment-edit-cancel]").show();
    },
    
    setToNewField: function() {
    	this.down("form button[cls~=ifresco-comment-save-button]").setText(Ifresco.helper.Translations.trans('Save new comment'));
		this.down("form button[cls~=ifresco-comment-edit-cancel]").hide();
		this.down("htmleditor").reset();
		this.editMode = false;
		this.editIndex = null;
    },

    loadCurrentData: function (nodeId) {
        this.nodeId = nodeId;
        var store = this.down('dataview').getStore();
        store.load({
            params: {
                'nodeId': nodeId
            }
        });
    }
});
