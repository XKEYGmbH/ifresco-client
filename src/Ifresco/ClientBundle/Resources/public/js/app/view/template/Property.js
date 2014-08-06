Ext.define('Ifresco.view.template.Property', {
    extend: 'Ext.XTemplate',

    constructor: function () {               
        var html = [
            '<div class="{[values.class == \'custom-field\' ? \'ifresco-template-property-custom\' : \'ifresco-template-property\']}',
                ' {[xindex % 2 === 0 ? \'even\' : \'odd\']}">',

                '<div class="x-tool-close x-tool-img ifresco-template-property-close"> </div>',
                '<div class="ifresco-template-property-title">{title}</div>',
                '<div class="ifresco-template-property-name">{name}</div>',
                '<div class="ifresco-template-property-dataType">{dataType}</div>',
            '</div>'
        ];

        this.callParent(html);
    }

});