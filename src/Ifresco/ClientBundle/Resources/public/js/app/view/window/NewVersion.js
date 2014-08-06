Ext.define('Ifresco.view.window.NewVersion', { 
    extend: 'Ext.window.Window',
    alias: 'widget.ifrescoViewWindowNewVersion',
    modal:true,
    layout:'fit',
    width:516,
    autoHeight: true,
    closeAction:'destroy',
    plain: true,
    resizable: false,
    data: {},
    addData: {},
    upload: false,
    revert: false,
    googleDocs: false,
    filter: [],
    callback: function() {},
    
    uploader: null,
    fileExt: "",
    uploadUrl: "",

    initComponent: function () {
    	if (typeof this.addData.revert != "undefined" && this.addData.revert != null)
    		this.revert = this.addData.revert;
    	
    	if (typeof this.data.filter != 'undefined')
    		this.filter = this.data.filter;
    	
    	this.uploadUrl = Routing.generate('ifresco_client_version_upload_new');
    	var items = [];
    	if (this.upload) {
    		items.push(
    			{
    				xtype: 'panel',
    				bodyStyle:'background-color:#e0e8f6;',
    				padding: '5 5 5 5',
    				columns: 2,
    				height:120,
    				border:false,
    				layout: {
                        type: 'hbox',
                        align: 'stretch'
                    },
    				items: [
						{
						    xtype: 'textarea',
						    flex: 1,
						    fieldLabel: Ifresco.helper.Translations.trans('Note'),
						    name: 'newVersionNote'
						},
						{
							xtype: "panel",
							padding: '5 5 5 5',
							flex: 1,
							layout: {
		                        type: 'vbox',
		                        align: 'stretch'
		                    },
							items: [
								{
									xtype: "panel",
									id: "uploadNewVersionElement",
									flex: 3,
									html: new Ext.XTemplate('<div class="text">{text}</div>').apply({
						                text: Ifresco.helper.Translations.transReplace('Drag and Drop a file here %1%', [this.fileExt])
						            })
								}, {
									xtype: 'button',
									flex: 1,
									text: Ifresco.helper.Translations.trans('Browse files'),
									name: 'selectNewVersionBtn',
									id: 'selectNewVersionBtn'
								}
							]
						}
    				]
    			}	
    		);    		
    	}
    	else {
    		items.push({
	            xtype: 'textarea',
	            padding: '5 5 5 5',
	            fieldLabel: Ifresco.helper.Translations.trans('Note'),
	            name: 'newVersionNote'
	        });
    	}
    	
    	if (!this.revert) {
	    	items.push({
			        	xtype: 'radiogroup',
			        	padding: '5 5 5 5',
			            fieldLabel: Ifresco.helper.Translations.trans('Version'),
			            columns: 2,
			            vertical: true,
			            items: [{
			                boxLabel: Ifresco.helper.Translations.trans('Minor version'),
			                name: 'newVersionChange',
			                inputValue: 'minor',
			                checked: true
			            }, {
			                boxLabel: Ifresco.helper.Translations.trans('Major version'),
			                name: 'newVersionChange',
			                inputValue: 'major'
			                
			            }]
			});
    	}
    	
        Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('New Version'),
            items: [{
                xtype: 'form',
                bodyStyle:'background-color:#e0e8f6;',
                itemId: 'newVersionForm',
                border: 0,
                layout: {
                    type: 'vbox',
                    align: 'stretch'
                },
                items: items
            }],
            buttons: [{
                text: (this.upload == true ? Ifresco.helper.Translations.trans('Save & Upload') : Ifresco.helper.Translations.trans('Save')),
                handler: function() {
                    var window = this;
                    var values = this.down('form').getValues(), 
                    	note = values.newVersionNote,
                    	change = values.newVersionChange;

                    if (this.upload) {
                    	var note = encodeURIComponent(note);
                    	this.uploader.settings.url = this.uploadUrl+"?nodeId="+this.data.nodeId+"&note="+note+"&versionchange="+change;
                    	this.uploader.start();
                    }
                    else if (this.revert) {
                    	Ifresco.getApplication().getController("Version").saveRevertVersion(this,{nodeId: this.data.nodeId, note: note, versionNodeId: this.data.versionNodeId, version: this.data.version});
                    }
                    else if (this.googleDocs) {
                    	this.callback(this,{nodeId: this.data.nodeId, note: note, versionchange: change});
                    }
                    else {
                    	Ifresco.getApplication().getController("Version").saveNewVersion(this,{nodeId: this.data.nodeId, note: note, versionchange: change});
                    }
                },
                scope: this
            },{
                text: Ifresco.helper.Translations.trans('Close'),
                handler: function() {
                    this.close();
                },
                scope: this
            }],
            listeners: {
            	scope: this,
            	afterrender: function() {
            		var self = this; // TODO change from jQuery to Extjs
            		console.log("afterrender versiontab");
            		if (this.upload) {
            			var fileExt = this.fileExt;
            			this.uploader = new plupload.Uploader({
                            //runtimes : 'html5,gears,flash,silverlight,browserplus',
                            runtimes : 'html5,flash,silverlight,browserplus',
                            browse_button : 'selectNewVersionBtn',
                            drop_element: "uploadNewVersionElement",
                            max_file_size : '300mb',
                            //chunk_size : '10mb',
                            chunk_size : '2mb',
                            unique_names : false,
                            multi_selection: false,
                            urlstream_upload: true,
                            url: self.uploadUrl,
                            filters: self.filter,
                            flash_swf_url : '/js/plupload/plupload.flash.swf',
                            silverlight_xap_url : '/js/plupload/plupload.silverlight.xap'
                        });


            			this.uploader.bind('Init', function(up, params){
                            try{
                                if(!!FileReader && !((params.runtime == "flash") || (params.runtime == "silverlight")))
                                    $("#uploadNewVersionElement .text").show();
                                else
                                	$("#uploadNewVersionElement .text").hide();
                            }
                            catch(err){}});
                        
            			this.uploader.init();

            			this.uploader.bind('FilesAdded', function(up, files) {
                            $.each(files, function(i, file) {
                                if (self.uploader.files.length == 1) {
                                    $('#uploadNewVersionElement').html(
                                        '<div id="' + file.id + '" class="fileItem"><div class="name">' +
                                            file.name + '</div><a href="#" id="cancel'+file.id+'" class="cancel"><img src="/bundles/ifrescoclient/images/icon/cross.png" align="absmiddle" border="0"></a></div><div class="fileInfo"><span class="size"><b>'+Ifresco.helper.Translations.trans('Size:')+'</b> ' + plupload.formatSize(file.size) + '</span>' +
                                            '<div id="' + file.id + '_uploadStatus" class="uploadStatus">0%</div>');

                                    //Fire Upload Event
                                    up.refresh(); // Reposition Flash/Silverlight
                                    
                                    $('#cancel'+file.id).click(function(){
                                        //$("#uploadNewVersionBtn").hide();
                                        $fileItem = $('#' + file.id);
                                        $("#uploadNewVersionElement").html('<div class="text">'+Ifresco.helper.Translations.transReplace('Drag and Drop a file here %1%', [self.fileExt]),'</div>');
                                        self.uploader.removeFile(file);
                                        self.uploader.refresh();
                                        $(this).unbind().remove();

                                    });

                                    //$("#uploadNewVersionBtn").show();
                                }
                                else {
                                	self.uploader.removeFile(file);
                                	self.uploader.refresh();
                                }
                            });
                        });

            			this.uploader.bind('UploadProgress', function(up, file) {
                            $('#' + file.id + "_uploadStatus").html(file.percent + "%");
                        });

            			this.uploader.bind('FileUploaded', function(up, file) {
                            //$("#NewVersionContainer").unmask();
                            $('#' + file.id + "_uploadStatus").html("100%");

                            Ifresco.getApplication().getController("Version").uploadDone(self, {nodeId: self.data.nodeId});

                        });

            			this.uploader.bind('Error', function(up, err) {
                            $('#uploadContainer').css("background-color","#910000");
                            $('#uploadContainer').css("color","#fff");

                            up.refresh(); // Reposition Flash/Silverlight
                        });
            		}
            	}
            }
        });
        
        

        this.callParent();
    }
});
