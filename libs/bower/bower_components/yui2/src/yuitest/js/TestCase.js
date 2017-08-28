YAHOO.namespace("tool");

//-----------------------------------------------------------------------------
// TestCase object
//-----------------------------------------------------------------------------
(function(){
    
    //used for autogenerating test case names
    var tempId = 0;
    
    /**
     * Test case containing various tests to run.
     * @param template An object containing any number of test methods, other methods,
     *                 an optional name, and anything else the test case needs.
     * @class TestCase
     * @namespace YAHOO.tool
     * @constructor
     */
    YAHOO.tool.TestCase = function (template /*:Object*/) {
        
        /**
         * Special rules for the test case. Possible subobjects
         * are fail, for tests that should fail, and error, for
         * tests that should throw an error.
         */
        this._should /*:Object*/ = {};
        
        //copy over all properties from the template to this object
        for (var prop in template) {
            this[prop] = template[prop];
        }    
        
        //check for a valid name
        if (!YAHOO.lang.isString(this.name)){
            /**
             * Name for the test case.
             */
            this.name /*:String*/ = "testCase" + (tempId++);
        }
    
    };
    
    
    YAHOO.tool.TestCase.prototype = {  
    
        /**
         * Resumes a paused test and runs the given function.
         * @param {Function} segment (Optional) The function to run.
         *      If omitted, the test automatically passes.
         * @return {Void}
         * @method resume
         */
        resume : function (segment /*:Function*/) /*:Void*/ {
            YAHOO.tool.TestRunner.resume(segment);
        },
    
        /**
         * Causes the test case to wait a specified amount of time and then
         * continue executing the given code.
         * @param {Function} segment (Optional) The function to run after the delay.
         *      If omitted, the TestRunner will wait until resume() is called.
         * @param {int} delay (Optional) The number of milliseconds to wait before running
         *      the function. If omitted, defaults to zero.
         * @return {Void}
         * @method wait
         */
        wait : function (segment /*:Function*/, delay /*:int*/) /*:Void*/{
            var args = arguments;
            if (YAHOO.lang.isFunction(args[0])){
                throw new YAHOO.tool.TestCase.Wait(args[0], args[1]);
            } else {
                throw new YAHOO.tool.TestCase.Wait(function(){
                    YAHOO.util.Assert.fail("Timeout: wait() called but resume() never called.");
                }, (YAHOO.lang.isNumber(args[0]) ? args[0] : 10000));
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
    
    /**
     * Represents a stoppage in test execution to wait for an amount of time before
     * continuing.
     * @param {Function} segment A function to run when the wait is over.
     * @param {int} delay The number of milliseconds to wait before running the code.
     * @class Wait
     * @namespace YAHOO.tool.TestCase
     * @constructor
     *
     */
    YAHOO.tool.TestCase.Wait = function (segment /*:Function*/, delay /*:int*/) {
        
        /**
         * The segment of code to run when the wait is over.
         * @type Function
         * @property segment
         */
        this.segment /*:Function*/ = (YAHOO.lang.isFunction(segment) ? segment : null);
    
        /**
         * The delay before running the segment of code.
         * @type int
         * @property delay
         */
        this.delay /*:int*/ = (YAHOO.lang.isNumber(delay) ? delay : 0);
    
    };

})();