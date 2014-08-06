Ext.define('Ifresco.controller.Node', {
    extend: 'Ext.app.Controller',
    requires: ['Ifresco.view.window.SendMail'],
    refs: [{
        selector: 'viewport > ifrescoCenter',
        ref: 'tabPanel'
    }],
    
    init: function() {
        this.control({
            'ifrescoMenu': {
                sendMail: this.sendMail
            }
        });
    },
    
    sendMail: function(nodes) {
        var attachments = "";
        var clearNodes = [];
        console.log("email in",nodes);
        Ext.each(nodes,function(node) {
        	console.log("sendmail iterate ",node);
            if(node.shortType == 'file') {
                attachments += node.docName+"&nbsp;&nbsp;&nbsp;";
                clearNodes.push(node);
            }
        });
        nodes = clearNodes;
        var encodedNodes = Ext.encode(nodes);
        console.log("send eamil",nodes,encodedNodes);
        
        var window = Ifresco.view.window.SendMail.create({
        	encodedNodes: encodedNodes,
        	attachments: attachments
        });
        
        window.show();
    }
});