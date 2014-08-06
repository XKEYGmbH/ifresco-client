Ext.define('Ifresco.controller.Version', {
    extend: 'Ext.app.Controller',
    refs: [{
        selector: 'viewport > ifrescoCenter',
        ref: 'tabPanel'
    }],
    
    init: function() {
        this.control({
            'ifrescoViewVersionsTab': {
            	createNewVersion: this.createNewVersion,
            	uploadNewVersion: this.uploadNewVersion,
            	versionLookup: this.versionLookup,
            	revert: this.revert,
            	versionLookup: this.versionLookup            	
            },
            'ifrescoViewGridVersion': {
            	download: this.download,
            },
            'ifrescoMenuVersion': {
            	revert: this.revert,
            	download: this.download
            }
        });
    },
    
    download: function(nodeId) {
        var dlUrl = Routing.generate('ifresco_client_version_download')+'?nodeId='+nodeId;
        if ($.browser.msie) {
            $(".PDFRenderer").hide();
        }
        window.open(dlUrl);
        $(".PDFRenderer").show();
    },
    
    showNewVersionWindow: function(data,title,upload,addData) {  	
    	var window = Ifresco.view.window.NewVersion.create({
    		title: title,
    		data: data,
    		addData: addData,
    		upload: upload
        });
        
        window.show();
    },
    
    createNewVersion: function(data,addData) {
    	this.showNewVersionWindow(data,Ifresco.helper.Translations.trans('Upload a new Version'),false,addData);
    },
    
    uploadNewVersion: function(data,addData) {
        this.showNewVersionWindow(data,Ifresco.helper.Translations.trans('Upload a new Version'),true,addData); //?enableUpload&filter="+SelectedVersion.nodeId
    },

    revert: function(data) {
    	this.showNewVersionWindow(data,Ifresco.helper.Translations.trans('Revert Version'),false,{revert: true}); //?hideVersionNumber
    },
    
    saveNewVersion: function(parent,data) {
    	var tabPanel = this.getTabPanel(),
    		activeTab = tabPanel.getActiveTab(),
    		versionGrid = activeTab.down("ifrescoViewGridVersion");
    	
    	Ext.Ajax.request({
            url: Routing.generate('ifresco_client_version_create_new'), //ifrescoClientVersioningBundle_createnewversion
            params: data,
            success: function (res) {
            	var jsonData = Ext.decode(res.responseText);
                versionGrid.getStore().load({params:{'nodeId': jsonData.nodeId}});
                parent.close();
            },
            failure: function (data) {
                console.log(data.responseText);
            }
        });
    },
    
    uploadDone: function(parent,data) {
    	var tabPanel = this.getTabPanel(),
			activeTab = tabPanel.getActiveTab(),
			versionGrid = activeTab.down("ifrescoViewGridVersion");

    	versionGrid.getStore().load({params:{'nodeId': data.nodeId}});
        parent.close();
    },

    saveRevertVersion: function(parent, data) {
    	var tabPanel = this.getTabPanel(),
			activeTab = tabPanel.getActiveTab(),
			versionGrid = activeTab.down("ifrescoViewGridVersion");
    	

    	Ext.Ajax.request({
            url: Routing.generate('ifresco_client_version_revert'), //ifrescoClientVersioningBundle_createnewversion
            params: data,
            success: function (res) {
            	var jsonData = Ext.decode(res.responseText);
            	
                if (jsonData.success === true) {
                    Ext.MessageBox.show({
                        title: Ifresco.helper.Translations.trans('Success'),
                        msg: Ifresco.helper.Translations.trans('Successfully reverted to the Version:')+"<b>"+data.version+"</b>",
                        buttons: Ext.MessageBox.OK,
                        icon: Ext.MessageBox.INFO
                    });
                }
                else {
                    Ext.MessageBox.show({
                        title: Ifresco.helper.Translations.trans('Error'),
                        msg: Ifresco.helper.Translations.trans('Something went wrong at the revert process'),
                        buttons: Ext.MessageBox.OK,
                        icon: Ext.MessageBox.WARNING
                    });
                }
                versionGrid.getStore().load({params:{'nodeId': jsonData.nodeId}});

                parent.close();
            },
            failure: function (data) {
                console.log(data.responseText);
            }
        });   
    },
    
    versionLookup: function(nodeId) {
    	var tabPanel = this.getTabPanel(),
			activeTab = tabPanel.getActiveTab(),
			versionGrid = activeTab.down("ifrescoViewGridVersion");
    	
    	var window = Ifresco.view.window.DetailVersion.create({
    		nodeId: nodeId,
    		configData: versionGrid.configData
        });
        
        window.show();
    }
});