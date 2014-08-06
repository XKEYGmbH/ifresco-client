Ext.define('Ifresco.view.template.CurrencyProperty', {
    extend: 'Ext.XTemplate',

    constructor: function () {               
        var html = [
            '<div class="{[xindex % 2 === 0 ? \'even\' : \'odd\']}">',
                '<div class="x-tool-close x-tool-img ifresco-template-property-close"> </div>',
                '<div class="ifresco-template-property-title">{title}</div>',
                '<div class="ifresco-template-property-name">{name}</div>',
                '<div class="ifresco-template-property-dataType">{dataType}</div>',
                '<input data-field="showSymbol" data-id={id} id="property-showSymbol-{id}"',
                    ' type="checkbox" name="showSymbol-{id}" {[values.showSymbol === true ? \'checked\' : \'\']} value="showSymbol">',
                '<label for="property-showSymbol-{id}">Show Currency Symbol</label><br>',
                /*'<input data-field="symbolStay" data-id={id} id="property-showSymbol-{id}"',
                ' type="checkbox" name="symbolStay-{id}" {[values.symbolStay === true ? \'checked\' : \'\']} value="symbolStay">',
                '<label for="property-symbolStay-{id}">Symbol stays after input</label><br>',*/
                '<label for="property-symbolStay-{id}">Currency Symbol</label> ',
                '<input data-field="currencySymbol" data-id={id} id="property-currencySymbol-{id}"',
                ' type="text" name="currencySymbol-{id}" value="{[\'currencySymbol\' in values && values.currencySymbol.length > 0 ? values.currencySymbol : \'\']}" onclick="this.focus()" style="width:40px;"><br>',
                '<label for="property-symbolStay-{id}">Thousands</label> ',
                '<input data-field="thousands" data-id={id} id="property-thousands-{id}"',
                ' type="text" name="thousands-{id}" value="{[\'thousands\' in values && values.thousands.length > 0 ? values.thousands : \',\']}" onclick="this.focus()" style="width:40px;"><br>',
                '<label for="property-symbolStay-{id}">Decimal</label> ',
                '<input data-field="decimal" data-id={id} id="property-decimal-{id}"',
                ' type="text" name="decimal-{id}" value="{[\'decimal\' in values && values.decimal.length > 0 ? values.decimal : \'.\']}" onclick="this.focus()" style="width:40px;"><br>',
                '<label for="property-symbolStay-{id}">Precision</label> ',
                '<input data-field="precision" data-id={id} id="property-precision-{id}"',
                ' type="text" name="precision-{id}" value="{[\'precision\' in values && values.precision.length > 0 ? values.precision : \'2\']}" onclick="this.focus()" style="width:40px;"><br>',
                
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
