Ext.define('Ifresco.view.template.ColumnSetProperty', {
    extend: 'Ext.XTemplate',

    constructor: function () {               
        var html = [
            '<div class="{[values.class == \'custom-field\' ? \'ifresco-template-property-custom\' : \'ifresco-template-property\']}',
                ' {[xindex % 2 === 0 ? \'even\' : \'odd\']}">',

                '<div data-test="test" class="x-tool-close x-tool-img ifresco-template-property-close"> </div>',
                '<div class="ifresco-template-property-title">{title}</div>',
                '<div class="ifresco-template-property-name">{name}</div>',
                '<div class="ifresco-template-property-dataType">{dataType}</div>',
                '<input data-field="hidden" data-id={id} id="property-hiddenflag-{id}" type="checkbox" name="hiddenFlag-{id}"',
                    ' {[this.checkHiddenFlag(values.hidden)]} value="hidden">',
                '<label for="property-hiddenflag-{id}">Hide on default</label><br>',
                '<input data-field="ascending" data-id={id} id="property-ascending-{id}" type="checkbox" name="ascending-{id}"',
                    ' {[this.checkAscending(values.ascending)]} value="ascending">',
                '<label for="property-ascending-{id}">Ascending</label>',
                '<input data-field="sort" data-id={id} id="property-defaultsort-{id}" type="radio" name="defaultsort"',
                    ' {[this.checkSort(values.sort)]} value="true">',
                '<label for="property-defaultsort-{id}">Default sort</label>',
            '</div>',
            {
                checkHiddenFlag: function(option) {
                    return option ? 'checked' : '';
                },
                checkAscending: function(option) {
                    return option ? 'checked' : '';
                },
                checkSort: function(sort) {
                    console.log(sort);
                    return sort ? 'checked' : '';
                }
            }
        ];

        this.callParent(html);
    }

});