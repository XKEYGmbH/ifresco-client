Ext.define('Ifresco.grid.column.Currency', {
    extend: 'Ext.grid.column.Column',
    alias: ['widget.currencycolumn'],
    requires: ['Ext.util.Format'],
    alternateClassName: 'Ifresco.grid.CurrencyColumn',

    format : '0,000.00',
    currencySymbol: '',
    thousands: ',',
    decimal: '.',
    precision: 2,
    //</locale>

    defaultRenderer: function(value){
    	console.log("CALLED DEFAULT RENDERER")
    	Ext.util.Format.decimalSeparator = this.decimal;
    	Ext.util.Format.thousandSeparator = this.thousands;
    	Ext.util.Format.currencyPrecision = this.precision;
    	Ext.util.Format.currencySign = this.currencySymbol;
        //return Ext.util.Format.number(value, this.format);
    	return Ext.util.Format.currency(value);
    }
});