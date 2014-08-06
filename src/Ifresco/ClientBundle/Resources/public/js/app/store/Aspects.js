Ext.define('Ifresco.store.Aspects', {
    extend: 'Ext.data.Store',
    sortInfo: {
        field: 'attributes.id',
        direction: 'ASC'
    },
    fields: ['attributes.id','attributes.value', 'text'],
    proxy: {
        type: 'memory'
    },

    constructor: function (config) {
        this.callParent([config]);
    }
});