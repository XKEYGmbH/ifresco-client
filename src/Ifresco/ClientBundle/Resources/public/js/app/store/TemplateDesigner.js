Ext.define('Ifresco.store.TemplateDesigner', {
    extend: 'Ext.data.Store',
    alias: 'widget.ifrescoStoreTemplateDesigner',
    autoDestroy: true,
    autoLoad: true,
    proxy: {
        type: 'ajax',
        url: Routing.generate('ifresco_client_admin_templates_content_types'),
        reader: {
            type: 'json',
            root: 'types',
            idProperty: 'name'
        }
    },
    fields: ['name', 'title','description']
});