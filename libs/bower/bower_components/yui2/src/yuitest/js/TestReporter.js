YAHOO.namespace("tool");

/**
 * An object capable of sending test results to a server.
 * @param {String} url The URL to submit the results to.
 * @param {Function} format (Optiona) A function that outputs the results in a specific format.
 *      Default is YAHOO.tool.TestFormat.XML.
 * @constructor
 * @namespace YAHOO.tool
 * @class TestReporter
 */
YAHOO.tool.TestReporter = function(url /*:String*/, format /*:Function*/) {

    /**
     * The URL to submit the data to.
     * @type String
     * @property url
     */
    this.url /*:String*/ = url;

    /**
     * The formatting function to call when submitting the data.
     * @type Function
     * @property format
     */
    this.format /*:Function*/ = format || YAHOO.tool.TestFormat.XML;

    /**
     * Extra fields to submit with the request.
     * @type Object
     * @property _fields
     * @private
     */
    this._fields /*:Object*/ = new Object();
    
    /**
     * The form element used to submit the results.
     * @type HTMLFormElement
     * @property _form
     * @private
     */
    this._form /*:HTMLElement*/ = null;

    /**
     * Iframe used as a target for form submission.
     * @type HTMLIFrameElement
     * @property _iframe
     * @private
     */
    this._iframe /*:HTMLElement*/ = null;
};

YAHOO.tool.TestReporter.prototype = {

    //restore missing constructor
    constructor: YAHOO.tool.TestReporter,
    
    /**
     * Convert a date into ISO format.
     * From Douglas Crockford's json2.js
     * @param {Date} date The date to convert.
     * @return {String} An ISO-formatted date string
     * @method _convertToISOString
     * @private
     */    
    _convertToISOString: function(date){
        function f(n) {
            // Format integers to have at least two digits.
            return n < 10 ? '0' + n : n;
        }

        return date.getUTCFullYear()   + '-' +
             f(date.getUTCMonth() + 1) + '-' +
             f(date.getUTCDate())      + 'T' +
             f(date.getUTCHours())     + ':' +
             f(date.getUTCMinutes())   + ':' +
             f(date.getUTCSeconds())   + 'Z';     
    
    },

    /**
     * Adds a field to the form that submits the results.
     * @param {String} name The name of the field.
     * @param {Variant} value The value of the field.
     * @return {Void}
     * @method addField
     */
    addField : function (name /*:String*/, value /*:Variant*/) /*:Void*/{
        this._fields[name] = value;    
    },
    
    /**
     * Removes all previous defined fields.
     * @return {Void}
     * @method addField
     */
    clearFields : function() /*:Void*/{
        this._fields = new Object();
    },

    /**
     * Cleans up the memory associated with the TestReporter, removing DOM elements
     * that were created.
     * @return {Void}
     * @method destroy
     */
    destroy : function() /*:Void*/ {
        if (this._form){
            this._form.parentNode.removeChild(this._form);
            this._form = null;
        }        
        if (this._iframe){
            this._iframe.parentNode.removeChild(this._iframe);
            this._iframe = null;
        }
        this._fields = null;
    },

    /**
     * Sends the report to the server.
     * @param {Object} results The results object created by TestRunner.
     * @return {Void}
     * @method report
     */
    report : function(results /*:Object*/) /*:Void*/{
    
        //if the form hasn't been created yet, create it
        if (!this._form){
            this._form = document.createElement("form");
            this._form.method = "post";
            this._form.style.visibility = "hidden";
            this._form.style.position = "absolute";
            this._form.style.top = 0;
            document.body.appendChild(this._form);
        
            //IE won't let you assign a name using the DOM, must do it the hacky way
            if (YAHOO.env.ua.ie){
                this._iframe = document.createElement("<iframe name=\"yuiTestTarget\" />");
            } else {
                this._iframe = document.createElement("iframe");
                this._iframe.name = "yuiTestTarget";
            }

            this._iframe.src = "javascript:false";
            this._iframe.style.visibility = "hidden";
            this._iframe.style.position = "absolute";
            this._iframe.style.top = 0;
            document.body.appendChild(this._iframe);

            this._form.target = "yuiTestTarget";
        }

        //set the form's action
        this._form.action = this.url;
    
        //remove any existing fields
        while(this._form.hasChildNodes()){
            this._form.removeChild(this._form.lastChild);
        }
        
        //create default fields
        this._fields.results = this.format(results);
        this._fields.useragent = navigator.userAgent;
        this._fields.timestamp = this._convertToISOString(new Date());

        //add fields to the form
        for (var prop in this._fields){
            if (YAHOO.lang.hasOwnProperty(this._fields, prop) && typeof this._fields[prop] != "function"){
                if (YAHOO.env.ua.ie){
                    input = document.createElement("<input name=\"" + prop + "\" >");
                } else {
                    input = document.createElement("input");
                    input.name = prop;
                }
                input.type = "hidden";
                input.value = this._fields[prop];
                this._form.appendChild(input);
            }
        }

        //remove default fields
        delete this._fields.results;
        delete this._fields.useragent;
        delete this._fields.timestamp;
        
        if (arguments[1] !== false){
            this._form.submit();
        }
    
    }

};