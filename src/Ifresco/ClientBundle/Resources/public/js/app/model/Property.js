Ext.define('Ifresco.model.Property', {
    extend: 'Ext.data.Model',
    fields: [
        {name: 'id', type: 'int'}, 
        {name: 'name', type: 'string'}, 
        {name: 'class', type: 'string'}, 
        {name: 'dataType', type: 'string'}, 
        {name: 'type', type: 'string', defaultValue: 'property'},
        {name: 'title', type: 'string'},
        {name: 'text', type: 'string'},
        {name: 'required', type: 'boolean', defaultValue: false},
        {name: 'readonly', type: 'boolean', defaultValue: false},
        {name: 'isCustom', type: 'boolean', defaultValue: false},
        {name: 'showSymbol', type: 'boolean', defaultValue: false},
        {name: 'symbolStay', type: 'boolean', defaultValue: false},
        {name: 'currencySymbol', type: 'string'},
        {name: 'thousands', type: 'string'},
        {name: 'decimal', type: 'string'},
        {name: 'precision', type: 'string'}
    ],

    constructor: function() {
        this.callParent(arguments);
        var id = parseInt(Math.random() * 2147000, 10);
        this.raw.id = id;
        this.data.id = id;
    }


});