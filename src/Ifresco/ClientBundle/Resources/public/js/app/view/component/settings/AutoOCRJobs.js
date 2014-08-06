Ext.define('Ifresco.view.settings.AutoOCRJobs', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.ifrescoViewSettingsAutoOCRJobs',
    border: 0,
    cls: 'ifresco-view-settings-autoocrjobs',
    layout: 'fit',
    autoScroll: true,

    initComponent: function() {
        Ext.apply(this, {
            tbar: [{
            	xtype: 'combo',
                width: 130,
                name: 'jobFilter',
                displayField: 'name',
                valueField: 'value',
                mode: 'local',
                triggerAction: 'all',
                value: 'ALL',
                store: Ifresco.store.AutoOCRJobFilter.create({}),
                listeners: {
                	scope: this,
                	change: function( t, newValue, oldValue, eOpts ) {
                		this.getStore().load({
                			params:{jobType: newValue}
                		});
                	}
                }
            }],
            border: 0,
    	    layout:'fit',
            columns: [{
                text: Ifresco.helper.Translations.trans('Job ID'),
                dataIndex: 'jobId',
                hidden: true
            },{
                text: Ifresco.helper.Translations.trans('Label'),
                dataIndex: 'label',
                flex: 2
            },{
                text: Ifresco.helper.Translations.trans('Job GUID'),
                dataIndex: 'guid',
                flex: 1
            },{
                text: Ifresco.helper.Translations.trans('Status'),
                dataIndex: 'status',
                flex: 1
            },{
                text: Ifresco.helper.Translations.trans('Page Count'),
                dataIndex: 'pageCount',
                flex: 1
            },{
                text: Ifresco.helper.Translations.trans('Created'),
                dataIndex: 'created',
                flex: 1
            },{
                text: Ifresco.helper.Translations.trans('Converted'),
                dataIndex: 'converted',
                flex: 1
            },{
                text: Ifresco.helper.Translations.trans('Owner'),
                dataIndex: 'owner',
                flex: 1
            },{
                text: Ifresco.helper.Translations.trans('Error'),
                dataIndex: 'error',
                flex: 1
            }],
            minColumnWidth: 150,
            store: Ifresco.store.AutoOCRJobs.create({})
        });

        this.callParent();
    }
});