Ext.define('Ifresco.helper.CurrencyFields', {
    singleton: true,
    alias: 'widget.ifrescohelpercurrencyfields',
    settings: null,
    isAdmin: false,
    
    getField: function (searchField) {
    	var currencyFields = Ifresco.helper.Settings.get("CurrencyFields");
    	if (currencyFields != null) {
    		currencyFields = Ext.decode(currencyFields);
    		var found = false;
    		Ext.each(currencyFields, function(field) {
    			if (field.name === searchField)
    				found = field;
    		});
    		return found;
    	}
    	return false;
    }
});
