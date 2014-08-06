Ext.define('Ext.ux.form.field.DateTime', {
    extend:'Ext.form.FieldContainer',
    mixins: {
        field: 'Ext.form.field.Field'
    },
    alias: 'widget.xdatetime',
    layout: 'fit',
    timePosition: 'right', // valid values:'below', 'right'
    dateCfg:{},
    timeCfg:{},
    allowBlank: true,
    isFormField: true,


    initComponent: function() {
        var me = this;
        me.buildField();
        me.callParent();
        this.dateField = this.down('datefield');
        this.timeField = this.down('timefield');
        me.initField();
    },


    //@private
    buildField: function() {
        var l;
        var d = {};
        if (this.timePosition == 'below') {
            l = {type: 'anchor'};
            d = {anchor: '100%'};
        } else {
            l = {type: 'hbox', align: 'middle'};
        }
        this.items = {
            xtype: 'container',
            layout: l,
            defaults: d,
            items: [Ext.apply({
                xtype: 'datefield',
                format: 'Y-m-d',
                width: this.timePosition != 'below' ? 100 : undefined,
                allowBlank: this.allowBlank,
                listeners: {
                    specialkey: this.onSpecialKey,
                    scope: this
                },
                isFormField: false // prevent submission
            }, this.dateCfg), Ext.apply({
                xtype: 'timefield',
                format: 'H:i',
                margin: this.timePosition != 'below' ? '0 0 0 3' : 0,
                width: this.timePosition != 'below' ? 80 : undefined,
                allowBlank: this.allowBlank,
                listeners: {
                    specialkey: this.onSpecialKey,
                    scope: this
                },
                isFormField: false // prevent submission
            }, this.timeCfg)]
        };
    },


    focus: function() {
        this.callParent();
        this.dateField.focus(false, 100);
    },


    // Handle tab events
    onSpecialKey:function(cmp, e) {
        var key = e.getKey();
        if (key === e.TAB) {
            if (cmp == this.dateField) {
                // fire event in container if we are getting out of focus from datefield
                if (e.shiftKey) {
                    this.fireEvent('specialkey', this, e);
                }
            }
            if (cmp == this.timeField) {
                if (!e.shiftKey) {
                    this.fireEvent('specialkey', this, e);
                }
            }
        } else if (this.inEditor) {
            this.fireEvent('specialkey', this, e);
        }
    },


    getValue: function() {
        var value, date = this.dateField.getSubmitValue(), time = this.timeField.getSubmitValue();

        if (date) {
            if (time) {
                var format = this.getFormat();
                value = Ext.Date.parse(date + ' ' + time, format);
            } else {
                value = this.dateField.getValue();
                if(this.name.indexOf('#to')>-1) {
                    value.setHours(23);
                    value.setMinutes(59);
                    value.setSeconds(59);
                }
            }
        }
        return value;
    },


    setValue: function(value) {
        this.dateField.setValue(value);
        this.timeField.setValue(value);
    },


    getSubmitData: function() {
        if (this.disabled) {
            return;
        }
        var value = this.getValue();
        var format = this.getFormat();
        var returnObj = {};
        returnObj[this.name] = value ? Ext.Date.format(value, format) : '';
        return returnObj;
    },

    getFormat: function() {
        return (this.dateField.submitFormat || this.dateField.format) + " " + (this.timeField.submitFormat || this.timeField.format);
    },


    getErrors: function() {
        return this.dateField.getErrors().concat(this.timeField.getErrors());
    },


    validate: function() {
        if (this.disabled) {
            return true;
        }

        var isDateValid = this.dateField.validate();
        var isTimeValid = this.timeField.validate();
        return isDateValid && isTimeValid;
    },


    reset: function() {
        this.mixins.field.reset();
        this.dateField.reset();
        if (this.timeField.store) {
            this.timeField.reset();
        }
    }


});
