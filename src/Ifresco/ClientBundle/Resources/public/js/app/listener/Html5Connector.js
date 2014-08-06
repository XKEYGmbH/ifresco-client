Ext.define('Ifresco.listener.Html5Connector', {
    extend: 'Ext.util.Observable',

    enableHighlight: false,
    enableGlobalHighlight: false,
    
    constructor: function(config) {
        Ext.apply(this, config);

        this.addEvents(
        	"dragenter",
            "dragstart",
            "dragstop",
            "windragenter",
            "windragstart",
            "windragstop",
            "drop"
        );
        this.callParent();
        this.init();

    },
   
    // private
    init:function() {
        this.setWindowEvents();

        this.el.on({
            scope:this,
            
            dragenter: function(e) {
            	console.log("dragemter");
            	e.stopPropagation();
            	e.preventDefault();
            	this.fireEvent("dragenter", this);
            	return;
            },
            
            dragover:function(e) {
            	console.log("dragover");
                e.stopPropagation();
                e.preventDefault();
                if (!Ext.isGecko) { // prevents drop in FF ;-(
                    e.browserEvent.dataTransfer.dropEffect = 'copy';
                }
                if (this.enableHighlight)
                	this.el.addCls("x-dnd-dragover");
                this.fireEvent("dragstart", this);
                return;
            },

            dragleave:function(e) {
            	console.log("dragleave");
                e.stopPropagation();
                e.preventDefault();
                if (this.enableHighlight)
                	this.el.removeCls("x-dnd-dragover");
                this.fireEvent("dragstop", this);
                return;
            },

            drop: function(e) {
                e.stopPropagation();
                e.preventDefault();
                var mediaLink = e.browserEvent.dataTransfer.getData('Text');
                var files = e.browserEvent.dataTransfer.files;
                this.fireEvent("drop", this);
            }

        });
    },

    // private
    setWindowEvents:function() {

        Ext.getBody().on({
            scope:this,
            
            dragenter: function(e) {
            	e.stopPropagation();
            	e.preventDefault();
            	this.fireEvent("windragenter", this);
            	return;
            },
            dragover:function(e) {
                e.stopPropagation();
                e.preventDefault();
                if (!Ext.isGecko) { // prevents drop in FF ;-(
                    e.browserEvent.dataTransfer.dropEffect = 'copy';
                }
                if (this.enableGlobalHighlight)
                	this.el.addCls("x-dnd-dragover");
                this.fireEvent("windragstart", this);
                return;
            },

            dragleave:function(e) {
                e.stopPropagation();
                e.preventDefault();
                if (this.enableGlobalHighlight)
                	this.el.removeCls("x-dnd-dragover");
                this.fireEvent("windragstop", this);
                return;
            }
        });
    }
});