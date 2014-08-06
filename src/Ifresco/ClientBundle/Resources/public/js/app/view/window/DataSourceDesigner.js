Ext.define('Ifresco.view.window.DataSourceDesigner', {
    extend: 'Ext.window.Window',
    alias: 'widget.ifrescoViewWindowDataSourceDesigner',
    width: 350,
    height: 255,
    closeAction: 'hide',
    plain: true,
    constrain: true,
    layout: 'fit',
    modal: true,

    initComponent: function () {
        Ext.apply(this, {
            items: [{
                xtype: 'form',
                url: Routing.generate('ifresco_client_admin_data_sources_save'),
                bodyPadding: 5,
                defaults: {
                    labelWidth: 150,
                    width: 330
                },
                border: 0,
                items: [{
                    xtype: 'hiddenfield',
                    name: 'data_source_id',
                    value: 0,
                    margin: 0,
                    padding: 0
                },{
                    xtype: 'textfield',
                    fieldLabel: 'Data Source Name',
                    name: 'name'
                },{
                    xtype: 'combo',
                    fieldLabel: 'Source Type',
                    name: 'type',
                    editable: false,
                    store: [['mysql', 'MySQL'], ['mssql', 'MSSQL'], ['sugarcrm', 'SugarCRM']],
                    value: 'mysql',
                    listeners: {
                        change: function(combo, newValue) {
                            var port = this.down('textfield[name=port]');
                            var dbname = this.down('textfield[name=databasename]');
                            switch(newValue) {
                                case 'mysql':
                                    break;
                                case 'mssql':
                                    port.show().setDisabled(false);
                                    dbname.show().setDisabled(false);
                                    break;
                                case 'sugarcrm':
                                    port.setDisabled(true).hide();
                                    dbname.setDisabled(true).hide();
                                    break;
                            }
                        },
                        scope: this
                    }
                },{
                    xtype: 'textfield',
                    fieldLabel: 'DataBase Name',
                    name: 'database_name'
                },{
                    xtype: 'textfield',
                    fieldLabel: 'User Name',
                    name: 'username'
                },{
                    xtype: 'textfield',
                    fieldLabel: 'Password',
                    name: "password"
                },{
                    xtype: 'textfield',
                    fieldLabel: 'Host',
                    name: 'host'
                },{
                    xtype: 'textfield',
                    fieldLabel: 'Port',
                    name: 'port'
                }]
            }],
            buttons: [{
                text: Ifresco.helper.Translations.trans('Test Connection'),
                handler: function() {
                    var formValues = this.down('form').getForm().getValues();
                    Ext.Ajax.request({
                        url: Routing.generate('ifresco_client_admin_data_sources_test'),
                        params: {
                            type: formValues.type,
                            database_name: formValues.database_name,
                            username: formValues.username,
                            password: formValues.password,
                            host: formValues.host,
                            port: formValues.port
                        },
                        success: function(response) {
                            var data = Ext.decode(response.responseText);
                            if (data.success) {
                                Ext.Msg.alert(
                                    Ifresco.helper.Translations.trans('Status'),
                                    Ifresco.helper.Translations.trans('Connection is established!')
                                );
                            } else {
                                Ext.Msg.alert(
                                    Ifresco.helper.Translations.trans('Status'),
                                    Ifresco.helper.Translations.trans('An error occured at connection procedure!')
                                );
                            }
                        }
                    });
                },
                scope: this
            },{
                text: Ifresco.helper.Translations.trans('Save'),
                handler: function () {
                    this.fireEvent('saveDataSource', this);
                },
                scope: this
            },{
                text: Ifresco.helper.Translations.trans('Close'),
                handler: function(){
                    this.hide();
                },
                scope: this
            }]
        });

        this.callParent();
    }
});