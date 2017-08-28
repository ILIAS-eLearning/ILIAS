YAHOO.namespace("util");

//-----------------------------------------------------------------------------
// Assert object
//-----------------------------------------------------------------------------

/**
 * The Assert object provides functions to test JavaScript values against
 * known and expected results. Whenever a comparison (assertion) fails,
 * an error is thrown.
 *
 * @namespace YAHOO.util
 * @class Assert
 * @static
 */
YAHOO.util.Assert = {

    //-------------------------------------------------------------------------
    // Helper Methods
    //-------------------------------------------------------------------------
    
    /**
     * Formats a message so that it can contain the original assertion message
     * in addition to the custom message.
     * @param {String} customMessage The message passed in by the developer.
     * @param {String} defaultMessage The message created by the error by default.
     * @return {String} The final error message, containing either or both.
     * @protected
     * @static
     * @method _formatMessage
     */
    _formatMessage : function (customMessage /*:String*/, defaultMessage /*:String*/) /*:String*/ {
        var message = customMessage;
        if (YAHOO.lang.isString(customMessage) && customMessage.length > 0){
            return YAHOO.lang.substitute(customMessage, { message: defaultMessage });
        } else {
            return defaultMessage;
        }        
    },
    
    //-------------------------------------------------------------------------
    // Generic Assertion Methods
    //-------------------------------------------------------------------------
    
    /** 
     * Forces an assertion error to occur.
     * @param {String} message (Optional) The message to display with the failure.
     * @method fail
     * @static
     */
    fail : function (message /*:String*/) /*:Void*/ {
        throw new YAHOO.util.AssertionError(this._formatMessage(message, "Test force-failed."));
    },       
    
    //-------------------------------------------------------------------------
    // Equality Assertion Methods
    //-------------------------------------------------------------------------    
    
    /**
     * Asserts that a value is equal to another. This uses the double equals sign
     * so type coercion may occur.
     * @param {Object} expected The expected value.
     * @param {Object} actual The actual value to test.
     * @param {String} message (Optional) The message to display if the assertion fails.
     * @method areEqual
     * @static
     */
    areEqual : function (expected /*:Object*/, actual /*:Object*/, message /*:String*/) /*:Void*/ {
        if (expected != actual) {
            throw new YAHOO.util.ComparisonFailure(this._formatMessage(message, "Values should be equal."), expected, actual);
        }
    },
    
    /**
     * Asserts that a value is not equal to another. This uses the double equals sign
     * so type coercion may occur.
     * @param {Object} unexpected The unexpected value.
     * @param {Object} actual The actual value to test.
     * @param {String} message (Optional) The message to display if the assertion fails.
     * @method areNotEqual
     * @static
     */
    areNotEqual : function (unexpected /*:Object*/, actual /*:Object*/, 
                         message /*:String*/) /*:Void*/ {
        if (unexpected == actual) {
            throw new YAHOO.util.UnexpectedValue(this._formatMessage(message, "Values should not be equal."), unexpected);
        }
    },
    
    /**
     * Asserts that a value is not the same as another. This uses the triple equals sign
     * so no type coercion may occur.
     * @param {Object} unexpected The unexpected value.
     * @param {Object} actual The actual value to test.
     * @param {String} message (Optional) The message to display if the assertion fails.
     * @method areNotSame
     * @static
     */
    areNotSame : function (unexpected /*:Object*/, actual /*:Object*/, message /*:String*/) /*:Void*/ {
        if (unexpected === actual) {
            throw new YAHOO.util.UnexpectedValue(this._formatMessage(message, "Values should not be the same."), unexpected);
        }
    },

    /**
     * Asserts that a value is the same as another. This uses the triple equals sign
     * so no type coercion may occur.
     * @param {Object} expected The expected value.
     * @param {Object} actual The actual value to test.
     * @param {String} message (Optional) The message to display if the assertion fails.
     * @method areSame
     * @static
     */
    areSame : function (expected /*:Object*/, actual /*:Object*/, message /*:String*/) /*:Void*/ {
        if (expected !== actual) {
            throw new YAHOO.util.ComparisonFailure(this._formatMessage(message, "Values should be the same."), expected, actual);
        }
    },    
    
    //-------------------------------------------------------------------------
    // Boolean Assertion Methods
    //-------------------------------------------------------------------------    
    
    /**
     * Asserts that a value is false. This uses the triple equals sign
     * so no type coercion may occur.
     * @param {Object} actual The actual value to test.
     * @param {String} message (Optional) The message to display if the assertion fails.
     * @method isFalse
     * @static
     */
    isFalse : function (actual /*:Boolean*/, message /*:String*/) {
        if (false !== actual) {
            throw new YAHOO.util.ComparisonFailure(this._formatMessage(message, "Value should be false."), false, actual);
        }
    },
    
    /**
     * Asserts that a value is true. This uses the triple equals sign
     * so no type coercion may occur.
     * @param {Object} actual The actual value to test.
     * @param {String} message (Optional) The message to display if the assertion fails.
     * @method isTrue
     * @static
     */
    isTrue : function (actual /*:Boolean*/, message /*:String*/) /*:Void*/ {
        if (true !== actual) {
            throw new YAHOO.util.ComparisonFailure(this._formatMessage(message, "Value should be true."), true, actual);
        }

    },
    
    //-------------------------------------------------------------------------
    // Special Value Assertion Methods
    //-------------------------------------------------------------------------    
    
    /**
     * Asserts that a value is not a number.
     * @param {Object} actual The value to test.
     * @param {String} message (Optional) The message to display if the assertion fails.
     * @method isNaN
     * @static
     */
    isNaN : function (actual /*:Object*/, message /*:String*/) /*:Void*/{
        if (!isNaN(actual)){
            throw new YAHOO.util.ComparisonFailure(this._formatMessage(message, "Value should be NaN."), NaN, actual);
        }    
    },
    
    /**
     * Asserts that a value is not the special NaN value.
     * @param {Object} actual The value to test.
     * @param {String} message (Optional) The message to display if the assertion fails.
     * @method isNotNaN
     * @static
     */
    isNotNaN : function (actual /*:Object*/, message /*:String*/) /*:Void*/{
        if (isNaN(actual)){
            throw new YAHOO.util.UnexpectedValue(this._formatMessage(message, "Values should not be NaN."), NaN);
        }    
    },
    
    /**
     * Asserts that a value is not null. This uses the triple equals sign
     * so no type coercion may occur.
     * @param {Object} actual The actual value to test.
     * @param {String} message (Optional) The message to display if the assertion fails.
     * @method isNotNull
     * @static
     */
    isNotNull : function (actual /*:Object*/, message /*:String*/) /*:Void*/ {
        if (YAHOO.lang.isNull(actual)) {
            throw new YAHOO.util.UnexpectedValue(this._formatMessage(message, "Values should not be null."), null);
        }
    },

    /**
     * Asserts that a value is not undefined. This uses the triple equals sign
     * so no type coercion may occur.
     * @param {Object} actual The actual value to test.
     * @param {String} message (Optional) The message to display if the assertion fails.
     * @method isNotUndefined
     * @static
     */
    isNotUndefined : function (actual /*:Object*/, message /*:String*/) /*:Void*/ {
        if (YAHOO.lang.isUndefined(actual)) {
            throw new YAHOO.util.UnexpectedValue(this._formatMessage(message, "Value should not be undefined."), undefined);
        }
    },

    /**
     * Asserts that a value is null. This uses the triple equals sign
     * so no type coercion may occur.
     * @param {Object} actual The actual value to test.
     * @param {String} message (Optional) The message to display if the assertion fails.
     * @method isNull
     * @static
     */
    isNull : function (actual /*:Object*/, message /*:String*/) /*:Void*/ {
        if (!YAHOO.lang.isNull(actual)) {
            throw new YAHOO.util.ComparisonFailure(this._formatMessage(message, "Value should be null."), null, actual);
        }
    },
        
    /**
     * Asserts that a value is undefined. This uses the triple equals sign
     * so no type coercion may occur.
     * @param {Object} actual The actual value to test.
     * @param {String} message (Optional) The message to display if the assertion fails.
     * @method isUndefined
     * @static
     */
    isUndefined : function (actual /*:Object*/, message /*:String*/) /*:Void*/ {
        if (!YAHOO.lang.isUndefined(actual)) {
            throw new YAHOO.util.ComparisonFailure(this._formatMessage(message, "Value should be undefined."), undefined, actual);
        }
    },    
    
    //--------------------------------------------------------------------------
    // Instance Assertion Methods
    //--------------------------------------------------------------------------    
   
    /**
     * Asserts that a value is an array.
     * @param {Object} actual The value to test.
     * @param {String} message (Optional) The message to display if the assertion fails.
     * @method isArray
     * @static
     */
    isArray : function (actual /*:Object*/, message /*:String*/) /*:Void*/ {
        if (!YAHOO.lang.isArray(actual)){
            throw new YAHOO.util.UnexpectedValue(this._formatMessage(message, "Value should be an array."), actual);
        }    
    },
   
    /**
     * Asserts that a value is a Boolean.
     * @param {Object} actual The value to test.
     * @param {String} message (Optional) The message to display if the assertion fails.
     * @method isBoolean
     * @static
     */
    isBoolean : function (actual /*:Object*/, message /*:String*/) /*:Void*/ {
        if (!YAHOO.lang.isBoolean(actual)){
            throw new YAHOO.util.UnexpectedValue(this._formatMessage(message, "Value should be a Boolean."), actual);
        }    
    },
   
    /**
     * Asserts that a value is a function.
     * @param {Object} actual The value to test.
     * @param {String} message (Optional) The message to display if the assertion fails.
     * @method isFunction
     * @static
     */
    isFunction : function (actual /*:Object*/, message /*:String*/) /*:Void*/ {
        if (!YAHOO.lang.isFunction(actual)){
            throw new YAHOO.util.UnexpectedValue(this._formatMessage(message, "Value should be a function."), actual);
        }    
    },
   
    /**
     * Asserts that a value is an instance of a particular object. This may return
     * incorrect results when comparing objects from one frame to constructors in
     * another frame. For best results, don't use in a cross-frame manner.
     * @param {Function} expected The function that the object should be an instance of.
     * @param {Object} actual The object to test.
     * @param {String} message (Optional) The message to display if the assertion fails.
     * @method isInstanceOf
     * @static
     */
    isInstanceOf : function (expected /*:Function*/, actual /*:Object*/, message /*:String*/) /*:Void*/ {
        if (!(actual instanceof expected)){
            throw new YAHOO.util.ComparisonFailure(this._formatMessage(message, "Value isn't an instance of expected type."), expected, actual);
        }
    },
    
    /**
     * Asserts that a value is a number.
     * @param {Object} actual The value to test.
     * @param {String} message (Optional) The message to display if the assertion fails.
     * @method isNumber
     * @static
     */
    isNumber : function (actual /*:Object*/, message /*:String*/) /*:Void*/ {
        if (!YAHOO.lang.isNumber(actual)){
            throw new YAHOO.util.UnexpectedValue(this._formatMessage(message, "Value should be a number."), actual);
        }    
    },    
    
    /**
     * Asserts that a value is an object.
     * @param {Object} actual The value to test.
     * @param {String} message (Optional) The message to display if the assertion fails.
     * @method isObject
     * @static
     */
    isObject : function (actual /*:Object*/, message /*:String*/) /*:Void*/ {
        if (!YAHOO.lang.isObject(actual)){
            throw new YAHOO.util.UnexpectedValue(this._formatMessage(message, "Value should be an object."), actual);
        }
    },
    
    /**
     * Asserts that a value is a string.
     * @param {Object} actual The value to test.
     * @param {String} message (Optional) The message to display if the assertion fails.
     * @method isString
     * @static
     */
    isString : function (actual /*:Object*/, message /*:String*/) /*:Void*/ {
        if (!YAHOO.lang.isString(actual)){
            throw new YAHOO.util.UnexpectedValue(this._formatMessage(message, "Value should be a string."), actual);
        }
    },
    
    /**
     * Asserts that a value is of a particular type. 
     * @param {String} expectedType The expected type of the variable.
     * @param {Object} actualValue The actual value to test.
     * @param {String} message (Optional) The message to display if the assertion fails.
     * @method isTypeOf
     * @static
     */
    isTypeOf : function (expected /*:String*/, actual /*:Object*/, message /*:String*/) /*:Void*/{
        if (typeof actual != expected){
            throw new YAHOO.util.ComparisonFailure(this._formatMessage(message, "Value should be of type " + expected + "."), expected, typeof actual);
        }
    }
};

//-----------------------------------------------------------------------------
// Assertion errors
//-----------------------------------------------------------------------------

/**
 * AssertionError is thrown whenever an assertion fails. It provides methods
 * to more easily get at error information and also provides a base class
 * from which more specific assertion errors can be derived.
 *
 * @param {String} message The message to display when the error occurs.
 * @namespace YAHOO.util
 * @class AssertionError
 * @extends Error
 * @constructor
 */ 
YAHOO.util.AssertionError = function (message /*:String*/){

    //call superclass
    //arguments.callee.superclass.constructor.call(this, message);
    
    /*
     * Error message. Must be duplicated to ensure browser receives it.
     * @type String
     * @property message
     */
    this.message /*:String*/ = message;
    
    /**
     * The name of the error that occurred.
     * @type String
     * @property name
     */
    this.name /*:String*/ = "AssertionError";
};

//inherit methods
YAHOO.lang.extend(YAHOO.util.AssertionError, Object, {

    /**
     * Returns a fully formatted error for an assertion failure. This should
     * be overridden by all subclasses to provide specific information.
     * @method getMessage
     * @return {String} A string describing the error.
     */
    getMessage : function () /*:String*/ {
        return this.message;
    },
    
    /**
     * Returns a string representation of the error.
     * @method toString
     * @return {String} A string representation of the error.
     */
    toString : function () /*:String*/ {
        return this.name + ": " + this.getMessage();
    }
    
});

/**
 * ComparisonFailure is subclass of AssertionError that is thrown whenever
 * a comparison between two values fails. It provides mechanisms to retrieve
 * both the expected and actual value.
 *
 * @param {String} message The message to display when the error occurs.
 * @param {Object} expected The expected value.
 * @param {Object} actual The actual value that caused the assertion to fail.
 * @namespace YAHOO.util
 * @extends YAHOO.util.AssertionError
 * @class ComparisonFailure
 * @constructor
 */ 
YAHOO.util.ComparisonFailure = function (message /*:String*/, expected /*:Object*/, actual /*:Object*/){

    //call superclass
    YAHOO.util.AssertionError.call(this, message);
    
    /**
     * The expected value.
     * @type Object
     * @property expected
     */
    this.expected /*:Object*/ = expected;
    
    /**
     * The actual value.
     * @type Object
     * @property actual
     */
    this.actual /*:Object*/ = actual;
    
    /**
     * The name of the error that occurred.
     * @type String
     * @property name
     */
    this.name /*:String*/ = "ComparisonFailure";
    
};

//inherit methods
YAHOO.lang.extend(YAHOO.util.ComparisonFailure, YAHOO.util.AssertionError, {

    /**
     * Returns a fully formatted error for an assertion failure. This message
     * provides information about the expected and actual values.
     * @method toString
     * @return {String} A string describing the error.
     */
    getMessage : function () /*:String*/ {
        return this.message + "\nExpected: " + this.expected + " (" + (typeof this.expected) + ")"  +
            "\nActual:" + this.actual + " (" + (typeof this.actual) + ")";
    }

});

/**
 * UnexpectedValue is subclass of AssertionError that is thrown whenever
 * a value was unexpected in its scope. This typically means that a test
 * was performed to determine that a value was *not* equal to a certain
 * value.
 *
 * @param {String} message The message to display when the error occurs.
 * @param {Object} unexpected The unexpected value.
 * @namespace YAHOO.util
 * @extends YAHOO.util.AssertionError
 * @class UnexpectedValue
 * @constructor
 */ 
YAHOO.util.UnexpectedValue = function (message /*:String*/, unexpected /*:Object*/){

    //call superclass
    YAHOO.util.AssertionError.call(this, message);
    
    /**
     * The unexpected value.
     * @type Object
     * @property unexpected
     */
    this.unexpected /*:Object*/ = unexpected;
    
    /**
     * The name of the error that occurred.
     * @type String
     * @property name
     */
    this.name /*:String*/ = "UnexpectedValue";
    
};

//inherit methods
YAHOO.lang.extend(YAHOO.util.UnexpectedValue, YAHOO.util.AssertionError, {

    /**
     * Returns a fully formatted error for an assertion failure. The message
     * contains information about the unexpected value that was encountered.
     * @method getMessage
     * @return {String} A string describing the error.
     */
    getMessage : function () /*:String*/ {
        return this.message + "\nUnexpected: " + this.unexpected + " (" + (typeof this.unexpected) + ") ";
    }

});

/**
 * ShouldFail is subclass of AssertionError that is thrown whenever
 * a test was expected to fail but did not.
 *
 * @param {String} message The message to display when the error occurs.
 * @namespace YAHOO.util
 * @extends YAHOO.util.AssertionError
 * @class ShouldFail
 * @constructor
 */  
YAHOO.util.ShouldFail = function (message /*:String*/){

    //call superclass
    YAHOO.util.AssertionError.call(this, message || "This test should fail but didn't.");
    
    /**
     * The name of the error that occurred.
     * @type String
     * @property name
     */
    this.name /*:String*/ = "ShouldFail";
    
};

//inherit methods
YAHOO.lang.extend(YAHOO.util.ShouldFail, YAHOO.util.AssertionError);

/**
 * ShouldError is subclass of AssertionError that is thrown whenever
 * a test is expected to throw an error but doesn't.
 *
 * @param {String} message The message to display when the error occurs.
 * @namespace YAHOO.util
 * @extends YAHOO.util.AssertionError
 * @class ShouldError
 * @constructor
 */  
YAHOO.util.ShouldError = function (message /*:String*/){

    //call superclass
    YAHOO.util.AssertionError.call(this, message || "This test should have thrown an error but didn't.");
    
    /**
     * The name of the error that occurred.
     * @type String
     * @property name
     */
    this.name /*:String*/ = "ShouldError";
    
};

//inherit methods
YAHOO.lang.extend(YAHOO.util.ShouldError, YAHOO.util.AssertionError);

/**
 * UnexpectedError is subclass of AssertionError that is thrown whenever
 * an error occurs within the course of a test and the test was not expected
 * to throw an error.
 *
 * @param {Error} cause The unexpected error that caused this error to be 
 *                      thrown.
 * @namespace YAHOO.util
 * @extends YAHOO.util.AssertionError
 * @class UnexpectedError
 * @constructor
 */  
YAHOO.util.UnexpectedError = function (cause /*:Object*/){

    //call superclass
    YAHOO.util.AssertionError.call(this, "Unexpected error: " + cause.message);
    
    /**
     * The unexpected error that occurred.
     * @type Error
     * @property cause
     */
    this.cause /*:Error*/ = cause;
    
    /**
     * The name of the error that occurred.
     * @type String
     * @property name
     */
    this.name /*:String*/ = "UnexpectedError";
    
    /**
     * Stack information for the error (if provided).
     * @type String
     * @property stack
     */
    this.stack /*:String*/ = cause.stack;
    
};

//inherit methods
YAHOO.lang.extend(YAHOO.util.UnexpectedError, YAHOO.util.AssertionError);
