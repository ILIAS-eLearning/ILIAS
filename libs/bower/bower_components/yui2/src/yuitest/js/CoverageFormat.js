YAHOO.namespace("tool.CoverageFormat");

/**
 * Returns the coverage report in JSON format. This is the straight
 * JSON representation of the native coverage report.
 * @param {Object} coverage The coverage report object.
 * @return {String} A JSON-formatted string of coverage data.
 * @method JSON
 * @namespace YAHOO.tool.CoverageFormat
 */
YAHOO.tool.CoverageFormat.JSON = function(coverage){
    return YAHOO.lang.JSON.stringify(coverage);
};

/**
 * Returns the coverage report in a JSON format compatible with
 * Xdebug. See <a href="http://www.xdebug.com/docs/code_coverage">Xdebug Documentation</a>
 * for more information. Note: function coverage is not available
 * in this format.
 * @param {Object} coverage The coverage report object.
 * @return {String} A JSON-formatted string of coverage data.
 * @method XdebugJSON
 * @namespace YAHOO.tool.CoverageFormat
 */
YAHOO.tool.CoverageFormat.XdebugJSON = function(coverage){
    var report = {},
        prop;
    for (prop in coverage){
        if (coverage.hasOwnProperty(prop)){
            report[prop] = coverage[prop].lines;
        }
    }

    return YAHOO.lang.JSON.stringify(report);        
};

