Ext.define('Ifresco.view.window.SendMail', {
    extend: 'Ext.window.Window',
    alias: 'widget.ifrescoViewWindowSendMail',
    layout: 'fit',
    modal:true,
    plain: true,
    constrain: true,
    buttonAlign: 'right',
    width:540,
    height:400,
    border:0,
    closeAction:'destroy',
    plain: true,
    resizable:true,
    
    attachments: "",
    encodedNodes: [],
    
    initComponent: function () {
        Ext.apply(this, {
        	listeners:{
                'beforeshow':{
                    fn:function() {
                        $(".PDFRenderer").hide();
                    }
                },
                'hide': {
                    fn:function() {
                        $(".PDFRenderer").show();
                    }
                }
            },
            buttons:[
                {
                    text:Ifresco.helper.Translations.trans('Send'),
                    handler:function() {
                    	var self = this;
                    	var emailForm = this.down("form");
                        if(emailForm.getForm().isValid()) {
                            emailForm.getForm().submit({
                                url: Routing.generate('ifresco_client_node_actions_mail_node'),
                                waitMsg: Ifresco.helper.Translations.trans('Sending Email'),
                                success: function(form, result) {
                                    Ext.Msg.alert(Ifresco.helper.Translations.trans('Success'), Ifresco.helper.Translations.trans('We could send your email successfully!'));
                                    self.close();
                                },
                                failure: function(form, result) {
                                    Ext.Msg.alert(Ifresco.helper.Translations.trans('Error'), result.result.errorMsg);
                                }
                            });
                        }
                    },
                    scope: this
                },
                {
                    text:Ifresco.helper.Translations.trans('Cancel'),
                    handler: function() {
                        this.close();
                    },
                    scope: this
                }
            ],
            items: [{
                xtype: 'form',
                labelAlign: 'left',
                frame:true,
                border:0,
                bodyStyle:'padding:5px 5px 0',
                height:300,
                items: [{
                    layout: 'form',
                    items: [
                        {
                            xtype:'textfield',
                            fieldLabel: Ifresco.helper.Translations.trans('To'),
                            name: 'to',
                            anchor:'100%',
                            vtype:'multiemail',
                            allowBlank:false
                        },{
                            xtype:'textfield',
                            fieldLabel: Ifresco.helper.Translations.trans('Cc'),
                            name: 'cc',
                            anchor:'100%',
                            vtype:'multiemail'
                        },{
                            xtype:'textfield',
                            fieldLabel: Ifresco.helper.Translations.trans('Bcc'),
                            name: 'bcc',
                            anchor:'100%',
                            vtype:'multiemail'
                        },{
                            xtype:'textfield',
                            fieldLabel: Ifresco.helper.Translations.trans('Subject'),
                            name: 'subject',
                            anchor:'100%'
                        },{
                            xtype:'panel',
                            fieldLabel: Ifresco.helper.Translations.trans('Attachments'),
                            anchor:'100%',
                            html: this.attachments
                        },{
                            xtype:'hidden',
                            name:'nodes',
                            value: this.encodedNodes
                        }
                    ]
                },{
                    xtype:'htmleditor',
                    id:'body',
                    //fieldLabel:'Body',
                    height:150,
                    name: 'body',
                    anchor:'100%',
                    hideLabel: true,
                    anchor: '100% -130'
                }]
            }]
        });

        this.callParent();
    }
});