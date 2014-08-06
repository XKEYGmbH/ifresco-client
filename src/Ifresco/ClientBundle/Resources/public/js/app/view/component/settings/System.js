Ext.define('Ifresco.view.settings.System', {
    extend: 'Ext.form.Panel',
    alias: 'widget.ifrescoViewSettingsSystem',
    border: 0,
    defaults: {
        margin: 5
    },
    autoScroll: true,
    closeAction: 'hide',
    cls: 'ifresco-view-settings-system',
    configData: null,

    initComponent: function() {
        Ext.apply(this, {
            title: Ifresco.helper.Translations.trans('System'),
            tbar: [{
            	iconCls: 'ifresco-icon-save',
            	text: Ifresco.helper.Translations.trans('Save'),
                handler: function() {
                    this.fireEvent('save');
                },
                scope: this
            }],
            items: [{
                xtype: 'panel',
                layout: {
                    type: 'table',
                    columns: 2
                },
                defaults: {
                    padding: 5
                },
                border: 0,
                cls: 'ifresco-view-table-settings-system',
                width: '100%',
                items: [{
                    xtype: 'container',
                    html: Ifresco.helper.Translations.trans('Disable renderers') + ':',
                    cellCls: 'ifresco-view-settings-row-left'
                },{
                    xtype: 'container',
                    layout: {
                        type: 'table',
                        columns: 2
                    },

                    width: '100%',
                    anchor: '100%',
                    border: 0,
                    cellCls: 'ifresco-view-settings-row-right',
                    items: [{
                        xtype: 'checkboxfield',
                        boxLabel: Ifresco.helper.Translations.trans('HtmlRenderer'),
                        boxLabelCls: 'ifresco-view-settings-label',
                        //cellCls: 'ifresco-view-settings-label',
                        colspan: 2,
                        width: 200,
                        name: 'Renderer',
                        inputValue: 'HtmlRenderer',
                        margin: '5 5 0 0'
                    },{
                        xtype: 'container',
                        cellCls: 'ifresco-view-settings-label-desc',
                        html: Ifresco.helper.Translations.trans('HTML Viewer')
                    },{
                        cellCls: 'ifresco-view-settings-types',
                        xtype: 'container',
                        html: 'text/html'
                    },{
                        xtype: 'checkboxfield',
                        boxLabel: Ifresco.helper.Translations.trans('FlowPlayerRenderer'),
                        boxLabelCls: 'ifresco-view-settings-label',
                        name: 'Renderer',
                        inputValue: 'FlowTipRenderer',
                        colspan: 2,
                        width: 200,
                        margin: '5 5 0 0'
                    },{
                        xtype: 'container',
                        html: 'FLV/MP4 FlowPlayer<br>(www.flowplayer.org)',
                        cellCls: 'ifresco-view-settings-label-desc',
                        align: 'top'
                    },{
                        xtype: 'container',
                        cellCls: 'ifresco-view-settings-types',
                        html: 'video/mp4<br>video/x-flv<br>video/quicktime',
                        align: 'top',
                        textAlign: 'top'
                    },{
                        xtype: 'checkboxfield',
                        boxLabel: Ifresco.helper.Translations.trans('ImageRenderer'),
                        boxLabelCls: 'ifresco-view-settings-label',
                        name: 'Renderer',
                        inputValue: 'ImageRenderer',
                        colspan: 2,
                        width: 200,
                        margin: '5 5 0 0'
                    },{
                        xtype: 'container',
                        html: Ifresco.helper.Translations.trans('Inline Image View'),
                        cellCls: 'ifresco-view-settings-label-desc',
                        align: 'top'
                    },{
                        xtype: 'container',
                        cellCls: 'ifresco-view-settings-types',
                        html: 'image/jpg<br>image/jpeg<br>image/png<br>image/gif',
                        align: 'top',
                        textAlign: 'top'
                    }/*,{ // TODO MOVE LATER
                        xtype: 'checkboxfield',
                        boxLabel: Ifresco.helper.Translations.trans('JavaScriptPDFRenderer'),
                        boxLabelCls: 'ifresco-view-settings-label',
                        name: 'Renderer',
                        inputValue: 'JavaScriptPDFRenderer',
                        colspan: 2,
                        width: 200,
                        margin: '5 5 0 0'
                    },{
                        xtype: 'container',
                        html: Ifresco.helper.Translations.trans('JavaScript PDF Viewer'),
                        cellCls: 'ifresco-view-settings-label-desc',
                        align: 'top'
                    },{
                        xtype: 'container',
                        cellCls: 'ifresco-view-settings-types',
                        html: 'application/pdf',
                        align: 'top',
                        textAlign: 'top'
                    }*/,{
                        xtype: 'checkboxfield',
                        boxLabel: Ifresco.helper.Translations.trans('PDFRenderer'),
                        boxLabelCls: 'ifresco-view-settings-label',
                        name: 'Renderer',
                        inputValue: 'PDFRenderer',
                        colspan: 2,
                        width: 200,
                        margin: '5 5 0 0'
                    },{
                        xtype: 'container',
                        html: Ifresco.helper.Translations.trans('Local PDF Viewer'),
                        cellCls: 'ifresco-view-settings-label-desc',
                        align: 'top'
                    },{
                        xtype: 'container',
                        cellCls: 'ifresco-view-settings-types',
                        html: 'application/pdf',
                        align: 'top',
                        textAlign: 'top'
                    },{
                        xtype: 'checkboxfield',
                        boxLabel: Ifresco.helper.Translations.trans('FileRenderer'),
                        boxLabelCls: 'ifresco-view-settings-label',
                        name: 'Renderer',
                        inputValue: 'FileRenderer',
                        colspan: 2,
                        width: 200,
                        margin: '5 5 0 0'
                    },{
                        xtype: 'container',
                        html: 'Inline File Content',
                        cellCls: 'ifresco-view-settings-label-desc',
                        align: 'top'
                    },{
                        xtype: 'container',
                        cellCls: 'ifresco-view-settings-types',
                        html: 'application/x-javascript<br>' 
                            + 'application/x-httpd-php<br>text/x-c<br>' 
                            + 'application/java<br>text/x-script.perl<br>' 
                            + 'text/x-script.phyton<br>application/php<br>' 
                            + 'text/richtext<br>text/javascript<br>text/plain<br>' 
                            + 'application/xml<br>text/xml<br>text/css<br>text/plain<br>',
                        align: 'top',
                        textAlign: 'top'
                    },{
                        xtype: 'checkboxfield',
                        boxLabel: Ifresco.helper.Translations.trans('AlfrescoRenderer'),
                        boxLabelCls: 'ifresco-view-settings-label',
                        name: 'Renderer',
                        inputValue: 'AlfrescoRenderer',
                        colspan: 2,
                        width: 200,
                        margin: '5 5 0 0'
                    },{
                        xtype: 'container',
                        html: Ifresco.helper.Translations.trans('AlfrescoPDFRenderer'),
                        cellCls: 'ifresco-view-settings-label-desc',
                        align: 'top'
                    },{
                        xtype: 'container',
                        cellCls: 'ifresco-view-settings-types',
                        html: 'default',
                        align: 'top',
                        textAlign: 'top'
                    }]
                },{
                    xtype: 'container',
                    border: 0,
                    colspan: 2,
                    anchor: '100%'
                },{
                    xtype: 'container',
                    html: Ifresco.helper.Translations.trans('Default Navigation element') + ':',
                    anchor: '100%',
                    cellCls: 'ifresco-view-settings-row-left'
                },{
                    xtype: 'container',
                    layout: 'vbox',
                    border: 0,
                    cellCls: 'ifresco-view-settings-row-right',
                    items: [{
                        xtype: 'radiofield',
                        boxLabel: Ifresco.helper.Translations.trans('Folders'),
                        name: 'DefaultNav',
                        inputValue: 'folders',
                        checked: true,
                        margin: '0 5 0 0'
                    },{
                        xtype: 'container',
                        cls: 'ifresco-view-settings-label-desc',
                        html: Ifresco.helper.Translations.trans('Folder Tree')
                    },{
                        xtype: 'radiofield',
                        boxLabel: Ifresco.helper.Translations.trans('Categories'),
                        name: 'DefaultNav',
                        inputValue: 'categories',
                        margin: '5 5 0 0'
                    },{
                        xtype: 'container',
                        cls: 'ifresco-view-settings-label-desc',
                        html: Ifresco.helper.Translations.trans('Categories Tree')
                    },{
                        xtype: 'radiofield',
                        boxLabel: Ifresco.helper.Translations.trans('Sites'),
                        name: 'DefaultNav',
                        inputValue: 'sites',
                        margin: '5 5 0 0'
                    },{
                        xtype: 'container',
                        cls: 'ifresco-view-settings-label-desc',
                        html: Ifresco.helper.Translations.trans('Sites')
                    },{
                        xtype: 'radiofield',
                        boxLabel: Ifresco.helper.Translations.trans('Favorites'),
                        name: 'DefaultNav',
                        inputValue: 'favorites',
                        margin: '5 5 0 0'
                    },{
                        xtype: 'container',
                        cls: 'ifresco-view-settings-label-desc',
                        html: Ifresco.helper.Translations.trans('User favorite nodes/categories')
                    },{
                        xtype: 'radiofield',
                        boxLabel: Ifresco.helper.Translations.trans('Tags'),
                        name: 'DefaultNav',
                        inputValue: 'tags',
                        margin: '5 5 0 0'
                    },{
                        xtype: 'container',
                        cls: 'ifresco-view-settings-label-desc',
                        html: Ifresco.helper.Translations.trans('Tag Scope')
                    }]
                },{
                    xtype: 'container',
                    border: 0,
                    colspan: 2,
                    anchor: '100%'
                },{
                    xtype: 'container',
                    html: Ifresco.helper.Translations.trans('Default Tab of Node') + ':',
                    anchor: '100%',
                    cellCls: 'ifresco-view-settings-row-left'
                },{
                    xtype: 'container',
                    layout: 'vbox',
                    border: 0,
                    cellCls: 'ifresco-view-settings-row-right',
                    items: [{
                        xtype: 'radiofield',
                        boxLabel: Ifresco.helper.Translations.trans('Preview'),
                        name: 'DefaultTab',
                        inputValue: 'preview',
                        checked: true,
                        margin: '0 5 0 0'
                    },{
                        xtype: 'container',
                        cls: 'ifresco-view-settings-label-desc',
                        html: Ifresco.helper.Translations.trans('Preview of the Node -> Renderer')
                    },{
                        xtype: 'radiofield',
                        boxLabel: Ifresco.helper.Translations.trans('Versions'),
                        name: 'DefaultTab',
                        inputValue: 'versions',
                        margin: '5 5 0 0'
                    },{
                        xtype: 'container',
                        cls: 'ifresco-view-settings-label-desc',
                        html: Ifresco.helper.Translations.trans('Version Control of the Node')
                    },{
                        xtype: 'radiofield',
                        boxLabel: Ifresco.helper.Translations.trans('Metadata'),
                        name: 'DefaultTab',
                        inputValue: 'metadata',
                        margin: '5 5 0 0'
                    },{
                        xtype: 'container',
                        cls: 'ifresco-view-settings-label-desc',
                        html: Ifresco.helper.Translations.trans('Display Metadata of the Node')
                    },{
                        xtype: 'radiofield',
                        boxLabel: Ifresco.helper.Translations.trans('Parent Metadata'),
                        name: 'DefaultTab',
                        inputValue: 'parentmetadata',
                        margin: '5 5 0 0'
                    },{
                        xtype: 'container',
                        cls: 'ifresco-view-settings-label-desc',
                        html: Ifresco.helper.Translations.trans('Display Metadata of the Parent Node')
                    }]
                },{
                    xtype: 'container',
                    border: 0,
                    colspan: 2,
                    anchor: '100%'
                },{
                    xtype: 'container',
                    html: Ifresco.helper.Translations.trans('Disable tabs') + ':',
                    anchor: '100%',
                    cellCls: 'ifresco-view-settings-row-left'
                },{
                    xtype: 'container',
                    layout: 'vbox',
                    border: 0,
                    cellCls: 'ifresco-view-settings-row-right',
                    items: [{
                        xtype: 'checkboxfield',
                        boxLabel: Ifresco.helper.Translations.trans('Versions'),
                        name: 'DisableTab',
                        inputValue: 'versions',
                        margin: '5 5 0 0'
                    },{
                        xtype: 'container',
                        cls: 'ifresco-view-settings-label-desc',
                        html: Ifresco.helper.Translations.trans('Version Control of the Node')
                    },{
                        xtype: 'checkboxfield',
                        boxLabel: Ifresco.helper.Translations.trans('Metadata'),
                        name: 'DisableTab',
                        inputValue: 'metadata',
                        margin: '5 5 0 0'
                    },{
                        xtype: 'container',
                        cls: 'ifresco-view-settings-label-desc',
                        html: Ifresco.helper.Translations.trans('Display Metadata of the Node')
                    },{
                        xtype: 'checkboxfield',
                        boxLabel: Ifresco.helper.Translations.trans('Comments Tab'),
                        inputValue: 'comments',
                        name: 'DisableTab',
                        margin: '5 5 0 0'
                    },{
                        xtype: 'container',
                        cls: 'ifresco-view-settings-label-desc',
                        html: Ifresco.helper.Translations.trans('Comments of Node')
                    }]
                },{
                    xtype: 'container',
                    border: 0,
                    colspan: 2,
                    anchor: '100%'
                },{
                    xtype: 'container',
                    html: Ifresco.helper.Translations.trans('Category Cache') + ':',
                    anchor: '100%',
                    cellCls: 'ifresco-view-settings-row-left'
                },{
                    xtype: 'container',
                    layout: 'vbox',
                    border: 0,
                    cellCls: 'ifresco-view-settings-row-right',
                    items: [{
                        xtype: 'radiofield',
                        boxLabel: Ifresco.helper.Translations.trans('Enabled'),
                        name: 'CategoryCache',
                        inputValue: 'true',
                        checked: true,
                        margin: '0'
                    },{
                        xtype: 'radiofield',
                        boxLabel: Ifresco.helper.Translations.trans('Disabled'),
                        name: 'CategoryCache',
                        inputValue: 'false',
                        margin: '0'
                    }]
                },{
                    xtype: 'container',
                    border: 0,
                    colspan: 2,
                    anchor: '100%'
                },{
                    xtype: 'container',
                    html: Ifresco.helper.Translations.trans('Node Cache') + ':',
                    anchor: '100%',
                    cellCls: 'ifresco-view-settings-row-left'
                },{
                    xtype: 'container',
                    layout: 'vbox',
                    border: 0,
                    cellCls: 'ifresco-view-settings-row-right',
                    items: [{
                        xtype: 'radiofield',
                        boxLabel: Ifresco.helper.Translations.trans('Enabled'),
                        name: 'NodeCache',
                        inputValue: 'true',
                        checked: true,
                        margin: '0'
                    },{
                        xtype: 'radiofield',
                        boxLabel: Ifresco.helper.Translations.trans('Disabled'),
                        name: 'NodeCache',
                        inputValue: 'false',
                        margin: '0'
                    }]
                },{
                    xtype: 'container',
                    border: 0,
                    colspan: 2,
                    anchor: '100%'
                },{
                    xtype: 'container',
                    html: Ifresco.helper.Translations.trans('Date format & time format') + ':',
                    anchor: '100%',
                    cellCls: 'ifresco-view-settings-row-left'
                },{
                    xtype: 'container',
                    layout: 'vbox',
                    border: 0,
                    cellCls: 'ifresco-view-settings-row-right',
                    items: [{
                        xtype: 'textfield',
                        name: "DateFormat",
                        value: 'm/d/Y',
                        margin: '0',
                        width: '100%'
                    },{
                        xtype: 'textfield',
                        name: 'TimeFormat',
                        value: 'H:i',
                        margin: '0',
                        width: '100%'
                    },{
                        xtype: 'box',
                        cls: 'ifresco-view-settings-label-desc',
                        html: Ifresco.helper.Translations.trans('Day'),
                        margin: '5 0 0 5',
                        width: '100%'
                    },{
                        xtype: 'box',
                        html: 'd = ' + Ifresco.helper.Translations.trans('Day of the month, 2 digits with leading zeros') + '<br>' 
                            + 'D = ' + Ifresco.helper.Translations.trans('A textual representation of a day, three letters') + '<br>' 
                            + 'j = ' + Ifresco.helper.Translations.trans('Day of the month without leading zeros') + '<br>' 
                            + Ifresco.helper.Translations.trans('l (lowercase \'L\') = A full textual representation of the day of the week') + '<br>' 
                            + 'N = ' + Ifresco.helper.Translations.trans('ISO-8601 numeric representation of the day of the week') + '<br>' 
                            + 'S = ' + Ifresco.helper.Translations.trans('English ordinal suffix for the day of the month, 2 characters') + '<br>' 
                            + 'w = ' + Ifresco.helper.Translations.trans('Numeric representation of the day of the week') + '<br>' 
                            + 'z = ' + Ifresco.helper.Translations.trans('The day of the year (starting from 0)'),
                        cls: 'ifresco-view-settings-types',
                        margin: '0 0 10 0',
                        width: '100%'
                    },{
                        xtype: 'box',
                        cls: 'ifresco-view-settings-label-desc',
                        html: Ifresco.helper.Translations.trans('Week'),
                        margin: '5 0 0 5',
                        width: '100%'
                    },{
                        xtype: 'box',
                        html: 'W = ' + Ifresco.helper.Translations.trans('ISO-8601 week number of year, weeks starting on Monday'),
                        cls: 'ifresco-view-settings-types',
                        margin: '0 0 10 0',
                        width: '100%'
                    },{
                        xtype: 'box',
                        cls: 'ifresco-view-settings-label-desc',
                        html: Ifresco.helper.Translations.trans('Month'),
                        margin: '5 0 0 5',
                        width: '100%'
                    },{
                        xtype: 'box',
                        html: 'F = ' + Ifresco.helper.Translations.trans('A full textual representation of a month, such as January or March') + '<br>' 
                            + 'm = ' + Ifresco.helper.Translations.trans('Numeric representation of a month, with leading zeros') + '<br>'
                            + 'n = ' + Ifresco.helper.Translations.trans('Numeric representation of a month, without leading zeros') + '<br>' 
                            + 't = ' + Ifresco.helper.Translations.trans('Number of days in the given month'),
                        cls: 'ifresco-view-settings-types',
                        margin: '0 0 10 0',
                        width: '100%'
                    },{
                        xtype: 'box',
                        cls: 'ifresco-view-settings-label-desc',
                        html: Ifresco.helper.Translations.trans('Year'),
                        margin: '5 0 0 5',
                        width: '100%'
                    },{
                        xtype: 'box',
                        html: 'L = ' + Ifresco.helper.Translations.trans('Whether it\'s a leap year') + '<br>' 
                            + 'o = ' + Ifresco.helper.Translations.trans('ISO-8601 year number. This has the same value as Y, '
                                + ' except that if the ISO week number (W) belongs to the previous or next year, that year is used instead.') + '<br>' 
                            + 'Y = ' + Ifresco.helper.Translations.trans('A full numeric representation of a year, 4 digits') + '<br>' 
                            + 'y = ' + Ifresco.helper.Translations.trans('A two digit representation of a year'),
                        cls: 'ifresco-view-settings-types',
                        margin: '0 0 10 0',
                        width: '100%'
                    },{
                        xtype: 'box',
                        cls: 'ifresco-view-settings-label-desc',
                        html: Ifresco.helper.Translations.trans('Time'),
                        margin: '5 0 0 5',
                        width: '100%'
                    },{
                        xtype: 'box',
                        html: 'a = ' + Ifresco.helper.Translations.trans('Lowercase Ante meridiem and Post meridiem') + '<br>' 
                            + 'A = ' + Ifresco.helper.Translations.trans('Uppercase Ante meridiem and Post meridiem') + '<br>' 
                            + 'B = ' + Ifresco.helper.Translations.trans('Swatch Internet time') + '<br>' 
                            + 'g = ' + Ifresco.helper.Translations.trans('12-hour format of an hour without leading zeros') + '<br>' 
                            + 'G = ' + Ifresco.helper.Translations.trans('24-hour format of an hour without leading zeros') + '<br>' 
                            + 'h = ' + Ifresco.helper.Translations.trans('12-hour format of an hour with leading zeros') + '<br>' 
                            + 'H = ' + Ifresco.helper.Translations.trans('24-hour format of an hour with leading zeros') + '<br>' 
                            + 'i = ' + Ifresco.helper.Translations.trans('Minutes with leading zeros') + '<br>' 
                            + 's = ' + Ifresco.helper.Translations.trans('Seconds, with leading zeros') + '<br>' 
                            + 'u = ' + Ifresco.helper.Translations.trans('Microseconds'),
                        cls: 'ifresco-view-settings-types',
                        margin: '0 0 10 0',
                        width: '100%'
                    },{
                        xtype: 'box',
                        cls: 'ifresco-view-settings-label-desc',
                        html: Ifresco.helper.Translations.trans('Full Date/Time'),
                        margin: '5 0 0 5',
                        width: '100%'
                    },{
                        xtype: 'box',
                        html: 'c = ' + Ifresco.helper.Translations.trans('ISO 8601 date') + '<br>' 
                            + 'L = ' + Ifresco.helper.Translations.trans('Whether it\'s a leap year') + '<br>' 
                            + 'o = ' + Ifresco.helper.Translations.trans('ISO-8601 year number. This has the same value as Y,' 
                                    +' except that if the ISO week number (W) belongs to the previous or next year, that year is used instead.'),
                        cls: 'ifresco-view-settings-types',
                        margin: '0 0 10 0',
                        width: '100%'
                    }]

                },{
                    xtype: 'container',
                    border: 0,
                    colspan: 2,
                    anchor: '100%'
                },{
                    xtype: 'container',
                    html: Ifresco.helper.Translations.trans('Transform function') + ':',
                    anchor: '100%',
                    cellCls: 'ifresco-view-settings-row-left'
                },{
                    xtype: 'container',
                    layout: 'vbox',
                    border: 0,
                    cellCls: 'ifresco-view-settings-row-right',
                    items: [{
                        xtype: 'radiofield',
                        boxLabel: Ifresco.helper.Translations.trans('Enabled'),
                        name: 'OCREnabled',
                        inputValue: 'true',
                        checked: true,
                        margin: '0'
                    },{
                        xtype: 'radiofield',
                        boxLabel: Ifresco.helper.Translations.trans('Disabled'),
                        name: 'OCREnabled',
                        inputValue: 'false',
                        margin: '0'
                    }]
                },{
                    xtype: 'container',
                    border: 0,
                    colspan: 2,
                    anchor: '100%'
                },{
                    xtype: 'container',
                    html: Ifresco.helper.Translations.trans('Enable OCR on Upload') + ':',
                    anchor: '100%',
                    cellCls: 'ifresco-view-settings-row-left'
                },{
                    xtype: 'container',
                    layout: 'vbox',
                    border: 0,
                    cellCls: 'ifresco-view-settings-row-right',
                    items: [{
                        xtype: 'radiofield',
                        boxLabel: Ifresco.helper.Translations.trans('Enabled'),
                        name: 'OCROnUpload',
                        inputValue: 'true',
                        checked: true,
                        margin: '0'
                    },{
                        xtype: 'radiofield',
                        boxLabel: Ifresco.helper.Translations.trans('Disabled'),
                        name: 'OCROnUpload',
                        inputValue: 'false',
                        margin: '0'
                    }]
                },{
                    xtype: 'container',
                    border: 0,
                    colspan: 2,
                    anchor: '100%'
                },{
                    xtype: 'container',
                    html: Ifresco.helper.Translations.trans('Parent Node Meta Tab') + ':',
                    anchor: '100%',
                    cellCls: 'ifresco-view-settings-row-left'
                },{
                    xtype: 'container',
                    layout: 'vbox',
                    border: 0,
                    cellCls: 'ifresco-view-settings-row-right',
                    items: [{
                        xtype: 'radiofield',
                        boxLabel: Ifresco.helper.Translations.trans('Enabled'),
                        name: 'ParentNodeMeta',
                        inputValue: 'true',
                        checked: true,
                        margin: '0'
                    },{
                        xtype: 'radiofield',
                        boxLabel: Ifresco.helper.Translations.trans('Disabled'),
                        name: 'ParentNodeMeta',
                        inputValue: 'false',
                        margin: '0'
                    }]
                },{
                    xtype: 'container',
                    border: 0,
                    colspan: 2,
                    anchor: '100%'
                },{
                    xtype: 'container',
                    html: Ifresco.helper.Translations.trans('Parent Node Meta Level') + ':',
                    anchor: '100%',
                    cellCls: 'ifresco-view-settings-row-left'
                },{
                    xtype: 'textfield',
                    value: 0,
                    name: 'ParentNodeMetaLevel',
                    cellCls: 'ifresco-view-settings-row-right'
                },{
                    xtype: 'container',
                    border: 0,
                    colspan: 2,
                    anchor: '100%'
                },{
                    xtype: 'container',
                    html: Ifresco.helper.Translations.trans('Parent Meta Document Only') + ':',
                    anchor: '100%',
                    cellCls: 'ifresco-view-settings-row-left'
                },{
                    xtype: 'container',
                    layout: 'vbox',
                    border: 0,
                    cellCls: 'ifresco-view-settings-row-right',
                    items: [{
                        xtype: 'radiofield',
                        boxLabel: Ifresco.helper.Translations.trans('Yes'),
                        name: 'ParentMetaDocumentOnly',
                        inputValue: 'true',
                        checked: true,
                        margin: '0'
                    },{
                        xtype: 'radiofield',
                        boxLabel: Ifresco.helper.Translations.trans('No'),
                        name: 'ParentMetaDocumentOnly',
                        inputValue: 'false',
                        margin: '0'
                    }]
                },{
                    xtype: 'container',
                    border: 0,
                    colspan: 2,
                    anchor: '100%'
                },{
                    xtype: 'container',
                    html: Ifresco.helper.Translations.trans('CSV Export') + ':',
                    anchor: '100%',
                    cellCls: 'ifresco-view-settings-row-left'
                },{
                    xtype: 'container',
                    layout: 'vbox',
                    border: 0,
                    cellCls: 'ifresco-view-settings-row-right',
                    items: [{
                        xtype: 'radiofield',
                        boxLabel: Ifresco.helper.Translations.trans('Enabled'),
                        name: 'CSVExport',
                        inputValue: 'true',
                        checked: true,
                        margin: '0'
                    },{
                        xtype: 'radiofield',
                        boxLabel: Ifresco.helper.Translations.trans('Disabled'),
                        name: 'CSVExport',
                        inputValue: 'false',
                        margin: '0'
                    }]
                },{
                    xtype: 'container',
                    border: 0,
                    colspan: 2,
                    anchor: '100%'
                },{
                    xtype: 'container',
                    html: Ifresco.helper.Translations.trans('PDF Export') + ':',
                    anchor: '100%',
                    cellCls: 'ifresco-view-settings-row-left'
                },{
                    xtype: 'container',
                    layout: 'vbox',
                    border: 0,
                    cellCls: 'ifresco-view-settings-row-right',
                    items: [{
                        xtype: 'radiofield',
                        boxLabel: Ifresco.helper.Translations.trans('Enabled'),
                        name: 'PDFExport',
                        inputValue: 'true',
                        checked: true,
                        margin: '0'
                    },{
                        xtype: 'radiofield',
                        boxLabel: Ifresco.helper.Translations.trans('Disabled'),
                        name: 'PDFExport',
                        inputValue: false,
                        margin: '0'
                    }]
                },{
                    xtype: 'container',
                    border: 0,
                    colspan: 2,
                    anchor: '100%'
                },{
                    xtype: 'container',
                    html: Ifresco.helper.Translations.trans('Meta On Tree Folder') + ':',
                    anchor: '100%',
                    cellCls: 'ifresco-view-settings-row-left'
                },{
                    xtype: 'container',
                    layout: 'vbox',
                    border: 0,
                    cellCls: 'ifresco-view-settings-row-right',
                    items: [{
                        xtype: 'radiofield',
                        boxLabel: Ifresco.helper.Translations.trans('Enabled'),
                        name: 'MetaOnTreeFolder',
                        inputValue: 'true',
                        checked: true,
                        margin: '0'
                    },{
                        xtype: 'radiofield',
                        boxLabel: Ifresco.helper.Translations.trans('Disabled'),
                        name: 'MetaOnTreeFolder',
                        inputValue: 'false',
                        margin: '0'
                    }]
                },{
                    xtype: 'container',
                    border: 0,
                    colspan: 2,
                    anchor: '100%'
                },{
                    xtype: 'container',
                    html: Ifresco.helper.Translations.trans('Open in Alfresco') + ':',
                    anchor: '100%',
                    cellCls: 'ifresco-view-settings-row-left'
                },{
                    xtype: 'container',
                    layout: 'vbox',
                    border: 0,
                    cellCls: 'ifresco-view-settings-row-right',
                    items: [{
                        xtype: 'radiofield',
                        boxLabel: Ifresco.helper.Translations.trans('Enabled'),
                        name: 'openInAlfresco',
                        inputValue: 'true',
                        checked: true,
                        margin: '0'
                    },{
                        xtype: 'radiofield',
                        boxLabel: Ifresco.helper.Translations.trans('Disabled'),
                        name: 'openInAlfresco',
                        inputValue: 'false',
                        margin: '0'
                    }]
                },{
                    xtype: 'container',
                    border: 0,
                    colspan: 2,
                    anchor: '100%'
                },{
                    xtype: 'container',
                    html: Ifresco.helper.Translations.trans('Tab Title Length') + ':',
                    anchor: '100%',
                    cellCls: 'ifresco-view-settings-row-left'
                },{
                    xtype: 'container',
                    layout: 'vbox',
                    cellCls: 'ifresco-view-settings-row-right',
                    items: [{
                        xtype: 'textfield',
                        name: 'TabTitleLength',
                        value: 0
                    },{
                        xtype: 'container',
                        html: '0 - ' + Ifresco.helper.Translations.trans('Do not cut a title'),
                        cls: 'ifresco-view-settings-types'
                    }]
                },{
                    xtype: 'container',
                    border: 0,
                    colspan: 2,
                    anchor: '100%'
                },{
                    xtype: 'container',
                    html: Ifresco.helper.Translations.trans('Share') + ':',
                    anchor: '100%',
                    cellCls: 'ifresco-view-settings-row-left'
                },{
                    xtype: 'container',
                    layout: 'vbox',
                    border: 0,
                    cellCls: 'ifresco-view-settings-row-right',
                    items: [{
                        xtype: 'radiofield',
                        boxLabel: Ifresco.helper.Translations.trans('Enabled'),
                        name: 'shareEnabled',
                        inputValue: 'true',
                        checked: true,
                        margin: '0'
                    },{
                        xtype: 'radiofield',
                        boxLabel: Ifresco.helper.Translations.trans('Disabled'),
                        name: 'shareEnabled',
                        inputValue: 'false',
                        margin: '0'
                    }]
                },{
                    xtype: 'container',
                    border: 0,
                    colspan: 2,
                    anchor: '100%'
                },{
                    xtype: 'container',
                    html: Ifresco.helper.Translations.trans('Thumbnail Hover') + ':',
                    anchor: '100%',
                    cellCls: 'ifresco-view-settings-row-left'
                },{
                    xtype: 'container',
                    layout: 'vbox',
                    border: 0,
                    cellCls: 'ifresco-view-settings-row-right',
                    items: [{
                        xtype: 'radiofield',
                        boxLabel: Ifresco.helper.Translations.trans('Enabled'),
                        name: 'thumbnailHover',
                        inputValue: 'true',
                        checked: true,
                        margin: '0'
                    },{
                        xtype: 'radiofield',
                        boxLabel: Ifresco.helper.Translations.trans('Disabled'),
                        name: 'thumbnailHover',
                        inputValue: 'false',
                        margin: '0'
                    }]
                },{
                    xtype: 'container',
                    border: 0,
                    colspan: 2,
                    anchor: '100%'
                },{
                    xtype: 'container',
                    html: Ifresco.helper.Translations.trans('Scan via SANE') + ':',
                    anchor: '100%',
                    cellCls: 'ifresco-view-settings-row-left'
                },{
                    xtype: 'container',
                    layout: 'vbox',
                    border: 0,
                    cellCls: 'ifresco-view-settings-row-right',
                    items: [{
                        xtype: 'radiofield',
                        boxLabel: Ifresco.helper.Translations.trans('Enabled'),
                        inputValue: 'true',
                        name: 'scanViaSane',
                        checked: true,
                        margin: '0'
                    },{
                        xtype: 'radiofield',
                        boxLabel: Ifresco.helper.Translations.trans('Disabled'),
                        name: 'scanViaSane',
                        inputValue: 'false',
                        margin: '0'
                    }]
                },{
                    xtype: 'container',
                    border: 0,
                    colspan: 2,
                    anchor: '100%'
                },{
                    xtype: 'container',
                    html: Ifresco.helper.Translations.trans('User lookup label') + ':',
                    anchor: '100%',
                    cellCls: 'ifresco-view-settings-row-left'
                },{
                    xtype: 'container',
                    layout: 'vbox',
                    cellCls: 'ifresco-view-settings-row-right',
                    items: [{
                        xtype: 'textfield',
                        value: '%firstName% %lastName% (%email%)',
                        name: 'UserLookupLabel'
                    },{
                        xtype: 'container',
                        html: Ifresco.helper.Translations.trans('Available Variables'),
                        cls: 'ifresco-view-settings-label-desc'
                    },{
                        xtype: 'container',
                        cls: 'ifresco-view-settings-types',
                        html: '%firstName%<br>%lastName%<br>%email%<br>%userName%'
                    }]
                },{
                    xtype: 'container',
                    border: 0,
                    colspan: 2,
                    anchor: '100%'
                },{
                    xtype: 'container',
                    html: Ifresco.helper.Translations.trans('Search Paging') + ':',
                    anchor: '100%',
                    cellCls: 'ifresco-view-settings-row-left'
                },{
                    xtype: 'container',
                    layout: 'vbox',
                    border: 0,
                    cellCls: 'ifresco-view-settings-row-right',
                    items: [{
                        xtype: 'radiofield',
                        boxLabel: Ifresco.helper.Translations.trans('Server side'),
                        inputValue: 'ServerSide',
                        name: 'SearchPaging',
                        checked: true,
                        margin: '0'
                    },{
                        xtype: 'radiofield',
                        boxLabel: Ifresco.helper.Translations.trans('Client side'),
                        name: 'SearchPaging',
                        inputValue: 'ClientSide',
                        margin: '0'
                    }]
                },{
                    xtype: 'container',
                    border: 0,
                    colspan: 2,
                    anchor: '100%'
                },{
                    xtype: 'container',
                    html: Ifresco.helper.Translations.trans('Max search results') + ':',
                    anchor: '100%',
                    cellCls: 'ifresco-view-settings-row-left'
                },{
                    xtype: 'container',
                    layout: 'vbox',
                    cellCls: 'ifresco-view-settings-row-right',
                    items: [{
                        xtype: 'textfield',
                        name: 'MaxSearchResults',
                        value: 250
                    }]
                }]
            }]
        });

        this.callParent();
    },

    scope: this
});