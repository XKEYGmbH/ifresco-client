Ext.define('Ifresco.helper.Translations', {
    singleton: true,
    alias: 'widget.ifrescohelpertranslations',
    translations: null,
    trans: function (phrase) {
        return this.translations[phrase] ? this.translations[phrase] : phrase;
    },
	transReplace: function (phrase,replace) {
	    var phrase = this.translations[phrase] ? this.translations[phrase] : phrase;
	    Ext.each(replace, function(text, index) {
	    	console.log("transreplace search","/%"+(index+1)+"%/g")
	    	var search = "%"+(index+1)+"%";
	    	phrase = phrase.replace(search,text);
	    })
	    return phrase;
	}
});
