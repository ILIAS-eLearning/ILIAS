YAHOO.namespace("tool");


//-----------------------------------------------------------------------------
// TestSuite object
//-----------------------------------------------------------------------------

/**
 * A test suite that can contain a collection of TestCase and TestSuite objects.
 * @param {String||Object} data The name of the test suite or an object containing
 *      a name property as well as setUp and tearDown methods.
 * @namespace YAHOO.tool
 * @class TestSuite
 * @constructor
 */
YAHOO.tool.TestSuite = function (data /*:String||Object*/) {

    /**
     * The name of the test suite.
     * @type String
     * @property name
     */
    this.name /*:String*/ = "";

    /**
     * Array of test suites and
     * @private
     */
    this.items /*:Array*/ = [];

    //initialize the properties
    if (YAHOO.lang.isString(data)){
        this.name = data;
    } else if (YAHOO.lang.isObject(data)){
        YAHOO.lang.augmentObject(this, data, true);
    }

    //double-check name
    if (this.name === ""){
        this.name = YAHOO.util.Dom.generateId(null, "testSuite");
    }

};

YAHOO.tool.TestSuite.prototype = {
    
    /**
     * Adds a test suite or test case to the test suite.
     * @param {YAHOO.tool.TestSuite||YAHOO.tool.TestCase} testObject The test suite or test case to add.
     * @return {Void}
     * @method add
     */
    add : function (testObject /*:YAHOO.tool.TestSuite*/) /*:Void*/ {
        if (testObject instanceof YAHOO.tool.TestSuite || testObject instanceof YAHOO.tool.TestCase) {
            this.items.push(testObject);
        }
    },
    
    //-------------------------------------------------------------------------
    // Stub Methods
    //-------------------------------------------------------------------------

    /**
     * Function to run before each test is executed.
     * @return {Void}
     * @method setUp
     */
    setUp : function () /*:Void*/ {
    },
    
    /**
     * Function to run after each test is executed.
     * @return {Void}
     * @method tearDown
     */
    tearDown: function () /*:Void*/ {
    }
    
};
