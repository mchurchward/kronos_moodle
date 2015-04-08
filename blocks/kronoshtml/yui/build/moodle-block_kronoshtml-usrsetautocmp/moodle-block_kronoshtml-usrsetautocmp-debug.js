YUI.add('moodle-block_kronoshtml-usrsetautocmp', function (Y, NAME) {

/**
 * This method calls the base class constructor
 * @method USRSETAUTOCMP
 */
var USRSETAUTOCMP = function() {
    USRSETAUTOCMP.superclass.constructor.apply(this, arguments);
};

Y.extend(USRSETAUTOCMP, Y.Base, {
    /**
     * Init function.
     * @property params
     * @type {Object}
     */
    init: function(params) {
        var ds = new Y.DataSource.Get({
            source: params.datasource
        });

        // Retireve Node objects for form elements.
        var hiddennode = Y.one('#id_config_userset');

        // Initialize autocomplete object.
        var autonode = Y.one('#'+params.divid).plug(Y.Plugin.AutoComplete, {
            resultHighlighter: 'phraseMatch',
            minQueryLength: 3,
            source: ds,
            requestTemplate: '?&usrset={query}&blockcontext='+params.blockinstanceid,
            resultListLocator: function (response) {
                // Make sure to return the result object.  It contains the names of the usersets.
                return response[0].result;
            },
            resultTextLocator: function (result) {
                // Add a sub userset indication and return the new name.
                var depth = parseInt(result.depth, 10);
                var subindication = '';

                for (var i = 1; i < depth; i++) {
                    subindication += '-';
                }
                return subindication+result.name;
            }
        });

        // Listen to the autocomplete 'selected' event.
        autonode.ac.on('select', function (e) {
            // Set formlib hidden field.
            hiddennode.set('value', e.result.raw.id);
        });
    }
},
{
    NAME: 'moodle-block_kronoshtml-usrsetautocmp',
    ATTRS: {
        datasource: {
           value: ''
        },
        divid: {
            value: ''
        },
        blockinstanceid: {
            value: 0
        }
    }
});

M.block_kronoshtml = M.block_kronoshtml || {};

/**
 * Entry point for usrsetautocmp module
 * @param string params additional parameters.
 * @return object the usrsetautocmp object
 */
M.block_kronoshtml.init = function(params) {
    return new USRSETAUTOCMP(params);
};

}, '@VERSION@', {
    "requires": [
        "base",
        "node",
        "autocomplete-base",
        "autocomplete-plugin",
        "autocomplete-sources",
        "autocomplete-highlighters",
        "autocomplete-filters",
        "array-extras",
        "datasource-get"
    ]
});
