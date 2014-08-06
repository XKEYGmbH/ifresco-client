Ext.define('Ifresco.model.TemplateProperty', {
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
        {name: 'readonly', type: 'boolean', defaultValue: false}
    ],

    constructor: function() {
        this.callParent(arguments);
        var id = parseInt(Math.random() * 2147000, 10);
        this.raw.id = id;
        this.data.id = id;
    }
});