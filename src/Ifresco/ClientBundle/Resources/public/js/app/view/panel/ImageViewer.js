Ext.define('Ifresco.view.panel.ImageViewer', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.ifrescoViewPanelImageViewer',
    border: 0,
    layout: 'fit',
    border: false,
    header: false,
    autoScroll: true,
    originalHeight: null,
    initLoad: true,
    initComponent: function () {
    	Ext.apply(this, {
            tbar: [{
            		xtype: 'slider',
	            	width: 200,
	                value: 100,
	                increment: 10,
	                minValue: 10,
	                maxValue: 100,
	                listeners: {
	                	change: this.onResize,
	                	scope: this
	                }
	            }
            ],
            items: [{
            	xtype: 'container',
			    region: 'south',
			    width: 700,
			    height: 200,
			    autoScroll: true,
			    items: [{
			        xtype:'image',
			        src: this.image,
			        region: 'south',
			        autoScroll: true
			    }]
            }]
        });

        this.callParent();
    },
    
    onResize:function(slider, newValue) {
    	if (this.initLoad == true) {
    		this.initLoad = false;
    		return;
    	}
    		
    	var img = slider.up("ifrescoViewPanelImageViewer").down("image");
    	
    	if (this.originalHeight == null) {
    		this.originalHeight = img.getHeight();
    	}

    	var height = this.originalHeight * (newValue / 100);
        img.setHeight(height);
    },
});