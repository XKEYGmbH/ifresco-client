Ext.define('Ifresco.view.template.TemplateProperty', {
    extend: 'Ext.XTemplate',

    constructor: function () {               
        var html = [
            '<div class="{[xindex % 2 === 0 ? \'even\' : \'odd\']}">',
                '<div class="x-tool-close x-tool-img ifresco-template-property-close"> </div>',
                '<div class="ifresco-template-property-title">{title}</div>',
                '<div class="ifresco-template-property-name">{name}</div>',
                '<div class="ifresco-template-property-dataType">{dataType}</div>',
                '<input data-field="required" data-id={id} id="property-required-{id}"',
                    ' type="checkbox" name="required-{id}" {[values.required === true ? \'checked\' : \'\']} value="required">',
                '<label for="property-required-{id}">Required</label><br>',
                '<input data-field="readonly" data-id={id} id="property-readonly-{id}"',
                    ' type="checkbox" name="readonly-{id}" {[values.readonly === true ? \'checked\' : \'\']} value="readonly">',
                '<label for="property-readonly-{id}">Readonly</label>',
            '</div>',
            {
                debug: function(values) {
                    console.log('It is: ', values);
                }
            }
        ];

        this.callParent(html);
    }

});
