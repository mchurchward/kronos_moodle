/**
 * This method calls the base class constructor
 * @method VMCOURSESAUTOCMP
 */
var VMCOURSESAUTOCMP = function() {
    VMCOURSESAUTOCMP.superclass.constructor.apply(this, arguments);
};

Y.extend(VMCOURSESAUTOCMP, Y.Base, {
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
        var hiddennode = Y.one('#id_config_vmcourses');

        // Initialize autocomplete object.
        var autonode = Y.one('#'+params.divid).plug(Y.Plugin.AutoComplete, {
            resultHighlighter: 'phraseMatch',
            minQueryLength: 3,
            source: ds,
            requestTemplate: '?&q={query}&course='+params.course,
            resultListLocator: function (response) {
                // Make sure to return the result object.  It contains the names of the vmcourses.
                return response[0].result;
            },
            resultTextLocator: function (result) {
                return result.name;
            }
        }

);

        // Listen to the autocomplete 'selected' event.
        autonode.ac.on('select', function (e) {
            // Set formlib hidden field.
            hiddennode.set('value', e.result.raw.id);
        });
    }
},
{
    NAME: 'moodle-mod_kronossandvm-vmcoursesautocmp',
    ATTRS: {
        datasource: {
           value: ''
        },
        divid: {
            value: ''
        }
    }
});

M.mod_kronossandvm = M.mod_kronossandvm || {};

/**
 * Entry point for vmcoursesautocmp module
 * @param string params additional parameters.
 * @return object the vmcoursesautocmp object
 */
M.mod_kronossandvm.init = function(params) {
    return new VMCOURSESAUTOCMP(params);
};
