 /**
 * The Logger widget provides a simple way to read or write log messages in
 * JavaScript code. Integration with the YUI Library's debug builds allow
 * implementers to access under-the-hood events, errors, and debugging messages.
 * Output may be read through a LogReader console and/or output to a browser
 * console.
 *
 * @module logger
 * @requires yahoo, event, dom
 * @optional dragdrop
 * @namespace YAHOO.widget
 * @title Logger Widget
 */

/****************************************************************************/
/****************************************************************************/
/****************************************************************************/

// Define once
if(!YAHOO.widget.Logger) {
    /**
     * The singleton Logger class provides core log management functionality. Saves
     * logs written through the global YAHOO.log function or written by a LogWriter
     * instance. Provides access to logs for reading by a LogReader instance or
     * native browser console such as the Firebug extension to Firefox or Safari's
     * JavaScript console through integration with the console.log() method.
     *
     * @class Logger
     * @static
     */
    YAHOO.widget.Logger = {
        // Initialize properties
        loggerEnabled: true,
        _browserConsoleEnabled: false,
        categories: ["info","warn","error","time","window"],
        sources: ["global"],
        _stack: [], // holds all log msgs
        maxStackEntries: 2500,
        _startTime: new Date().getTime(), // static start timestamp
        _lastTime: null, // timestamp of last logged message
        _windowErrorsHandled: false,
        _origOnWindowError: null
    };

    /////////////////////////////////////////////////////////////////////////////
    //
    // Public properties
    //
    /////////////////////////////////////////////////////////////////////////////
    /**
     * True if Logger is enabled, false otherwise.
     *
     * @property loggerEnabled
     * @type Boolean
     * @static
     * @default true
     */

    /**
     * Array of categories.
     *
     * @property categories
     * @type String[]
     * @static
     * @default ["info","warn","error","time","window"]
     */

    /**
     * Array of sources.
     *
     * @property sources
     * @type String[]
     * @static
     * @default ["global"]
     */

    /**
     * Upper limit on size of internal stack.
     *
     * @property maxStackEntries
     * @type Number
     * @static
     * @default 2500
     */

    /////////////////////////////////////////////////////////////////////////////
    //
    // Private properties
    //
    /////////////////////////////////////////////////////////////////////////////
    /**
     * Internal property to track whether output to browser console is enabled.
     *
     * @property _browserConsoleEnabled
     * @type Boolean
     * @static
     * @default false
     * @private
     */

    /**
     * Array to hold all log messages.
     *
     * @property _stack
     * @type Array
     * @static
     * @private
     */
    /**
     * Static timestamp of Logger initialization.
     *
     * @property _startTime
     * @type Date
     * @static
     * @private
     */
    /**
     * Timestamp of last logged message.
     *
     * @property _lastTime
     * @type Date
     * @static
     * @private
     */
    /////////////////////////////////////////////////////////////////////////////
    //
    // Public methods
    //
    /////////////////////////////////////////////////////////////////////////////
    /**
     * Saves a log message to the stack and fires newLogEvent. If the log message is
     * assigned to an unknown category, creates a new category. If the log message is
     * from an unknown source, creates a new source.  If browser console is enabled,
     * outputs the log message to browser console.
     * Note: the LogReader adds the message, category, and source to the DOM
     * as HTML.
     *
     * @method log
     * @param sMsg {HTML} The log message.
     * @param sCategory {HTML} Category of log message, or null.
     * @param sSource {HTML} Source of LogWriter, or null if global.
     */
    YAHOO.widget.Logger.log = function(sMsg, sCategory, sSource) {
        if(this.loggerEnabled) {
            if(!sCategory) {
                sCategory = "info"; // default category
            }
            else {
                sCategory = sCategory.toLocaleLowerCase();
                if(this._isNewCategory(sCategory)) {
                    this._createNewCategory(sCategory);
                }
            }
            var sClass = "global"; // default source
            var sDetail = null;
            if(sSource) {
                var spaceIndex = sSource.indexOf(" ");
                if(spaceIndex > 0) {
                    // Substring until first space
                    sClass = sSource.substring(0,spaceIndex);
                    // The rest of the source
                    sDetail = sSource.substring(spaceIndex,sSource.length);
                }
                else {
                    sClass = sSource;
                }
                if(this._isNewSource(sClass)) {
                    this._createNewSource(sClass);
                }
            }

            var timestamp = new Date();
            var logEntry = new YAHOO.widget.LogMsg({
                msg: sMsg,
                time: timestamp,
                category: sCategory,
                source: sClass,
                sourceDetail: sDetail
            });

            var stack = this._stack;
            var maxStackEntries = this.maxStackEntries;
            if(maxStackEntries && !isNaN(maxStackEntries) &&
                (stack.length >= maxStackEntries)) {
                stack.shift();
            }
            stack.push(logEntry);
            this.newLogEvent.fire(logEntry);

            if(this._browserConsoleEnabled) {
                this._printToBrowserConsole(logEntry);
            }
            return true;
        }
        else {
            return false;
        }
    };

    /**
     * Resets internal stack and startTime, enables Logger, and fires logResetEvent.
     *
     * @method reset
     */
    YAHOO.widget.Logger.reset = function() {
        this._stack = [];
        this._startTime = new Date().getTime();
        this.loggerEnabled = true;
        this.log("Logger reset");
        this.logResetEvent.fire();
    };

    /**
     * Public accessor to internal stack of log message objects.
     *
     * @method getStack
     * @return {Object[]} Array of log message objects.
     */
    YAHOO.widget.Logger.getStack = function() {
        return this._stack;
    };

    /**
     * Public accessor to internal start time.
     *
     * @method getStartTime
     * @return {Date} Internal date of when Logger singleton was initialized.
     */
    YAHOO.widget.Logger.getStartTime = function() {
        return this._startTime;
    };

    /**
     * Disables output to the browser's global console.log() function, which is used
     * by the Firebug extension to Firefox as well as Safari.
     *
     * @method disableBrowserConsole
     */
    YAHOO.widget.Logger.disableBrowserConsole = function() {
        YAHOO.log("Logger output to the function console.log() has been disabled.");
        this._browserConsoleEnabled = false;
    };

    /**
     * Enables output to the browser's global console.log() function, which is used
     * by the Firebug extension to Firefox as well as Safari.
     *
     * @method enableBrowserConsole
     */
    YAHOO.widget.Logger.enableBrowserConsole = function() {
        this._browserConsoleEnabled = true;
        YAHOO.log("Logger output to the function console.log() has been enabled.");
    };

    /**
     * Surpresses native JavaScript errors and outputs to console. By default,
     * Logger does not handle JavaScript window error events.
     * NB: Not all browsers support the window.onerror event.
     *
     * @method handleWindowErrors
     */
    YAHOO.widget.Logger.handleWindowErrors = function() {
        if(!YAHOO.widget.Logger._windowErrorsHandled) {
            // Save any previously defined handler to call
            if(window.error) {
                YAHOO.widget.Logger._origOnWindowError = window.onerror;
            }
            window.onerror = YAHOO.widget.Logger._onWindowError;
            YAHOO.widget.Logger._windowErrorsHandled = true;
            YAHOO.log("Logger handling of window.onerror has been enabled.");
        }
        else {
            YAHOO.log("Logger handling of window.onerror had already been enabled.");
        }
    };

    /**
     * Unsurpresses native JavaScript errors. By default,
     * Logger does not handle JavaScript window error events.
     * NB: Not all browsers support the window.onerror event.
     *
     * @method unhandleWindowErrors
     */
    YAHOO.widget.Logger.unhandleWindowErrors = function() {
        if(YAHOO.widget.Logger._windowErrorsHandled) {
            // Revert to any previously defined handler to call
            if(YAHOO.widget.Logger._origOnWindowError) {
                window.onerror = YAHOO.widget.Logger._origOnWindowError;
                YAHOO.widget.Logger._origOnWindowError = null;
            }
            else {
                window.onerror = null;
            }
            YAHOO.widget.Logger._windowErrorsHandled = false;
            YAHOO.log("Logger handling of window.onerror has been disabled.");
        }
        else {
            YAHOO.log("Logger handling of window.onerror had already been disabled.");
        }
    };
    
    /////////////////////////////////////////////////////////////////////////////
    //
    // Public events
    //
    /////////////////////////////////////////////////////////////////////////////

     /**
     * Fired when a new category has been created.
     *
     * @event categoryCreateEvent
     * @param sCategory {String} Category name.
     */
    YAHOO.widget.Logger.categoryCreateEvent =
        new YAHOO.util.CustomEvent("categoryCreate", this, true);

     /**
     * Fired when a new source has been named.
     *
     * @event sourceCreateEvent
     * @param sSource {String} Source name.
     */
    YAHOO.widget.Logger.sourceCreateEvent =
        new YAHOO.util.CustomEvent("sourceCreate", this, true);

     /**
     * Fired when a new log message has been created.
     *
     * @event newLogEvent
     * @param sMsg {String} Log message.
     */
    YAHOO.widget.Logger.newLogEvent = new YAHOO.util.CustomEvent("newLog", this, true);

    /**
     * Fired when the Logger has been reset has been created.
     *
     * @event logResetEvent
     */
    YAHOO.widget.Logger.logResetEvent = new YAHOO.util.CustomEvent("logReset", this, true);

    /////////////////////////////////////////////////////////////////////////////
    //
    // Private methods
    //
    /////////////////////////////////////////////////////////////////////////////

    /**
     * Creates a new category of log messages and fires categoryCreateEvent.
     *
     * @method _createNewCategory
     * @param sCategory {String} Category name.
     * @private
     */
    YAHOO.widget.Logger._createNewCategory = function(sCategory) {
        this.categories.push(sCategory);
        this.categoryCreateEvent.fire(sCategory);
    };

    /**
     * Checks to see if a category has already been created.
     *
     * @method _isNewCategory
     * @param sCategory {String} Category name.
     * @return {Boolean} Returns true if category is unknown, else returns false.
     * @private
     */
    YAHOO.widget.Logger._isNewCategory = function(sCategory) {
        for(var i=0; i < this.categories.length; i++) {
            if(sCategory == this.categories[i]) {
                return false;
            }
        }
        return true;
    };

    /**
     * Creates a new source of log messages and fires sourceCreateEvent.
     *
     * @method _createNewSource
     * @param sSource {String} Source name.
     * @private
     */
    YAHOO.widget.Logger._createNewSource = function(sSource) {
        this.sources.push(sSource);
        this.sourceCreateEvent.fire(sSource);
    };

    /**
     * Checks to see if a source already exists.
     *
     * @method _isNewSource
     * @param sSource {String} Source name.
     * @return {Boolean} Returns true if source is unknown, else returns false.
     * @private
     */
    YAHOO.widget.Logger._isNewSource = function(sSource) {
        if(sSource) {
            for(var i=0; i < this.sources.length; i++) {
                if(sSource == this.sources[i]) {
                    return false;
                }
            }
            return true;
        }
    };

    /**
     * Outputs a log message to global console.log() function.
     *
     * @method _printToBrowserConsole
     * @param oEntry {Object} Log entry object.
     * @private
     */
    YAHOO.widget.Logger._printToBrowserConsole = function(oEntry) {
        if ((window.console && console.log) ||
            (window.opera && opera.postError)) {
            var category = oEntry.category;
            var label = oEntry.category.substring(0,4).toUpperCase();

            var time = oEntry.time;
            var localTime;
            if (time.toLocaleTimeString) {
                localTime  = time.toLocaleTimeString();
            }
            else {
                localTime = time.toString();
            }

            var msecs = time.getTime();
            var elapsedTime = (YAHOO.widget.Logger._lastTime) ?
                (msecs - YAHOO.widget.Logger._lastTime) : 0;
            YAHOO.widget.Logger._lastTime = msecs;

            var output =
                localTime + " (" +
                elapsedTime + "ms): " +
                oEntry.source + ": ";

            if (window.console) {
                console.log(output, oEntry.msg);
            } else {
                opera.postError(output + oEntry.msg);
            }
        }
    };

    /////////////////////////////////////////////////////////////////////////////
    //
    // Private event handlers
    //
    /////////////////////////////////////////////////////////////////////////////

    /**
     * Handles logging of messages due to window error events.
     *
     * @method _onWindowError
     * @param sMsg {String} The error message.
     * @param sUrl {String} URL of the error.
     * @param sLine {String} Line number of the error.
     * @private
     */
    YAHOO.widget.Logger._onWindowError = function(sMsg,sUrl,sLine) {
        // Logger is not in scope of this event handler
        try {
            YAHOO.widget.Logger.log(sMsg+' ('+sUrl+', line '+sLine+')', "window");
            if(YAHOO.widget.Logger._origOnWindowError) {
                YAHOO.widget.Logger._origOnWindowError();
            }
        }
        catch(e) {
            return false;
        }
    };

    /////////////////////////////////////////////////////////////////////////////
    //
    // First log
    //
    /////////////////////////////////////////////////////////////////////////////

    YAHOO.widget.Logger.log("Logger initialized");
}

