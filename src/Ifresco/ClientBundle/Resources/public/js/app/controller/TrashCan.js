Ext.define('Ifresco.controller.TrashCan', {
    extend: 'Ext.app.Controller',
    refs: [{
        selector: 'viewport > ifrescoCenter',
        ref: 'tabPanel'
    }, {
        selector: 'ifrescoCenter > #ifrescoTrashCanTab',
        ref: 'trashcan'
    }],
    
    init: function() {

        this.control({
        	'ifrescoMenuTrashCan': {
            	deleteNode: this.deleteNode,
                deleteNodes: this.deleteNodes,
                restoreNode: this.restoreNode,
                restoreNodes: this.restoreNodes
            }
        });
    },
    
    deleteNode: function (node, nodeId, nodeName, nodeType, fromComponent) {
    	var parentStore = fromComponent.getStore();

        Ext.MessageBox.show({
            title: Ifresco.helper.Translations.trans('Delete?'),
            msg: Ifresco.helper.Translations.trans('Do you really want to delete:') + '<br><b>' + nodeName + '</b>',
            fn: function(btn) {
                if (btn === "yes") {
                    //TODO: implement
                    Ext.Ajax.request({
                        method: 'POST',
                        url: Routing.generate('ifresco_client_user_trash_remove_node'),
                        params: {
                            nodeId: nodeId
                        },
                        success: function () {
                            if(!parentStore.isLoading()) {
                            	parentStore.load();
                            }
                        },
                        failure: function (data) {
                            var data = Ext.decode(data.responseText);
                            Ext.MessageBox.show({
                                title: Ifresco.helper.Translations.trans('Error'),
                                msg: data.message,
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.WARNING
                            })
                        }
                    });
                }
            },
            buttons: Ext.MessageBox.YESNO,
            icon: Ext.MessageBox.QUESTION
        });
    },
    
    deleteNodes: function (nodes, fromComponent) {
    	var parentStore = fromComponent.getStore();
    	var nodeNames = [];
    	var nodeArray = [];
    	for (var i=0; i < nodes.length; ++i) {
    		nodeNames.push(nodes[i].nodeName);
    		nodeArray.push({nodeRef: nodes[i].nodeId});
        }
    	
        Ext.MessageBox.show({
            title: Ifresco.helper.Translations.trans('Delete?'),
            msg: Ifresco.helper.Translations.trans('Do you really want to delete:') + '<br><b>' + nodeNames.join("<br>") + '</b>',
            fn: function(btn) {
                if (btn === "yes") {
                    //TODO: implement
                    Ext.Ajax.request({
                        method: 'POST',
                        url: Routing.generate('ifresco_client_user_trash_remove_nodes'),
                        params: {
                            nodes: Ext.encode(nodeArray)
                        },
                        success: function () {
                            if(!parentStore.isLoading()) {
                            	parentStore.load();
                            }
                        },
                        failure: function (data) {
                            var data = Ext.decode(data.responseText);
                            Ext.MessageBox.show({
                                title: Ifresco.helper.Translations.trans('Error'),
                                msg: data.message,
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.WARNING
                            })
                        }
                    });
                }
            },
            buttons: Ext.MessageBox.YESNO,
            icon: Ext.MessageBox.QUESTION
        });
    },
    
    restoreNode: function (node, nodeId, nodeName, nodeType, fromComponent) {
    	var parentStore = fromComponent.getStore();

        Ext.MessageBox.show({
            title: Ifresco.helper.Translations.trans('Delete?'),
            msg: Ifresco.helper.Translations.trans('Do you really want to restore:') + '<br><b>' + nodeName + '</b>',
            fn: function(btn) {
                if (btn === "yes") {
                    //TODO: implement
                    Ext.Ajax.request({
                        method: 'POST',
                        url: Routing.generate('ifresco_client_user_trash_restore_node'),
                        params: {
                            nodeId: nodeId
                        },
                        success: function () {
                            if(!parentStore.isLoading()) {
                            	parentStore.load();
                            }
                        },
                        failure: function (data) {
                            var data = Ext.decode(data.responseText);
                            Ext.MessageBox.show({
                                title: Ifresco.helper.Translations.trans('Error'),
                                msg: data.message,
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.WARNING
                            })
                        }
                    });
                }
            },
            buttons: Ext.MessageBox.YESNO,
            icon: Ext.MessageBox.QUESTION
        });
    },
    
    restoreNodes: function (nodes, fromComponent) {
    	var parentStore = fromComponent.getStore();
    	var nodeNames = [];
    	var nodeArray = [];
    	for (var i=0; i < nodes.length; ++i) {
    		nodeNames.push(nodes[i].nodeName);
    		nodeArray.push({nodeRef: nodes[i].nodeId});
        }
    	
        Ext.MessageBox.show({
            title: Ifresco.helper.Translations.trans('Delete?'),
            msg: Ifresco.helper.Translations.trans('Do you really want to restore:') + '<br><b>' + nodeNames.join("<br>") + '</b>',
            fn: function(btn) {
                if (btn === "yes") {
                    //TODO: implement
                    Ext.Ajax.request({
                        method: 'POST',
                        url: Routing.generate('ifresco_client_user_trash_restore_nodes'),
                        params: {
                            nodes: Ext.encode(nodeArray)
                        },
                        success: function () {
                            if(!parentStore.isLoading()) {
                            	parentStore.load();
                            }
                        },
                        failure: function (data) {
                            var data = Ext.decode(data.responseText);
                            Ext.MessageBox.show({
                                title: Ifresco.helper.Translations.trans('Error'),
                                msg: data.message,
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.WARNING
                            })
                        }
                    });
                }
            },
            buttons: Ext.MessageBox.YESNO,
            icon: Ext.MessageBox.QUESTION
        });
    }
});