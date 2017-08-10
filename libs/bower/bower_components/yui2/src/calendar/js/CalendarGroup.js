(function() {

    var Dom = YAHOO.util.Dom,
        DateMath = YAHOO.widget.DateMath,
        Event = YAHOO.util.Event,
        Lang = YAHOO.lang,
        Calendar = YAHOO.widget.Calendar;

/**
* YAHOO.widget.CalendarGroup is a special container class for YAHOO.widget.Calendar. This class facilitates
* the ability to have multi-page calendar views that share a single dataset and are
* dependent on each other.
*
* The calendar group instance will refer to each of its elements using a 0-based index.
* For example, to construct the placeholder for a calendar group widget with id "cal1" and
* containerId of "cal1Container", the markup would be as follows:
*   <xmp>
*       <div id="cal1Container_0"></div>
*       <div id="cal1Container_1"></div>
*   </xmp>
* The tables for the calendars ("cal1_0" and "cal1_1") will be inserted into those containers.
*
* <p>
* <strong>NOTE: As of 2.4.0, the constructor's ID argument is optional.</strong>
* The CalendarGroup can be constructed by simply providing a container ID string, 
* or a reference to a container DIV HTMLElement (the element needs to exist 
* in the document).
* 
* E.g.:
*   <xmp>
*       var c = new YAHOO.widget.CalendarGroup("calContainer", configOptions);
*   </xmp>
* or:
*   <xmp>
*       var containerDiv = YAHOO.util.Dom.get("calContainer");
*       var c = new YAHOO.widget.CalendarGroup(containerDiv, configOptions);
*   </xmp>
* </p>
* <p>
* If not provided, the ID will be generated from the container DIV ID by adding an "_t" suffix.
* For example if an ID is not provided, and the container's ID is "calContainer", the CalendarGroup's ID will be set to "calContainer_t".
* </p>
* 
* @namespace YAHOO.widget
* @class CalendarGroup
* @constructor
* @param {String} id optional The id of the table element that will represent the CalendarGroup widget. As of 2.4.0, this argument is optional.
* @param {String | HTMLElement} container The id of the container div element that will wrap the CalendarGroup table, or a reference to a DIV element which exists in the document.
* @param {Object} config optional The configuration object containing the initial configuration values for the CalendarGroup.
*/
function CalendarGroup(id, containerId, config) {
    if (arguments.length > 0) {
        this.init.apply(this, arguments);
    }
}

/**
* The set of default Config property keys and values for the CalendarGroup.
* 
* <p>
* NOTE: This property is made public in order to allow users to change 
* the default values of configuration properties. Users should not 
* modify the key string, unless they are overriding the Calendar implementation
* </p>
*
* @property YAHOO.widget.CalendarGroup.DEFAULT_CONFIG
* @static
* @type Object An object with key/value pairs, the key being the 
* uppercase configuration property name and the value being an objec 
* literal with a key string property, and a value property, specifying the 
* default value of the property 
*/

/**
* The set of default Config property keys and values for the CalendarGroup
* @property YAHOO.widget.CalendarGroup._DEFAULT_CONFIG
* @deprecated Made public. See the public DEFAULT_CONFIG property for details
* @private
* @static
* @type Object
*/
CalendarGroup.DEFAULT_CONFIG = CalendarGroup._DEFAULT_CONFIG = Calendar.DEFAULT_CONFIG;
CalendarGroup.DEFAULT_CONFIG.PAGES = {key:"pages", value:2};

var DEF_CFG = CalendarGroup.DEFAULT_CONFIG;

CalendarGroup.prototype = {

    /**
    * Initializes the calendar group. All subclasses must call this method in order for the
    * group to be initialized properly.
    * @method init
    * @param {String} id optional The id of the table element that will represent the CalendarGroup widget. As of 2.4.0, this argument is optional.
    * @param {String | HTMLElement} container The id of the container div element that will wrap the CalendarGroup table, or a reference to a DIV element which exists in the document.
    * @param {Object} config optional The configuration object containing the initial configuration values for the CalendarGroup.
    */
    init : function(id, container, config) {

        // Normalize 2.4.0, pre 2.4.0 args
        var nArgs = this._parseArgs(arguments);

        id = nArgs.id;
        container = nArgs.container;
        config = nArgs.config;

        this.oDomContainer = Dom.get(container);
        if (!this.oDomContainer) { this.logger.log("Container not found in document.", "error"); }

        if (!this.oDomContainer.id) {
            this.oDomContainer.id = Dom.generateId();
        }
        if (!id) {
            id = this.oDomContainer.id + "_t";
        }

        /**
        * The unique id associated with the CalendarGroup
        * @property id
        * @type String
        */
        this.id = id;

        /**
        * The unique id associated with the CalendarGroup container
        * @property containerId
        * @type String
        */
        this.containerId = this.oDomContainer.id;

        this.logger = new YAHOO.widget.LogWriter("CalendarGroup " + this.id);
        this.initEvents();
        this.initStyles();

        /**
        * The collection of Calendar pages contained within the CalendarGroup
        * @property pages
        * @type YAHOO.widget.Calendar[]
        */
        this.pages = [];

        Dom.addClass(this.oDomContainer, CalendarGroup.CSS_CONTAINER);
        Dom.addClass(this.oDomContainer, CalendarGroup.CSS_MULTI_UP);

        /**
        * The Config object used to hold the configuration variables for the CalendarGroup
        * @property cfg
        * @type YAHOO.util.Config
        */
        this.cfg = new YAHOO.util.Config(this);

        /**
        * The local object which contains the CalendarGroup's options
        * @property Options
        * @type Object
        */
        this.Options = {};

        /**
        * The local object which contains the CalendarGroup's locale settings
        * @property Locale
        * @type Object
        */
        this.Locale = {};

        this.setupConfig();

        if (config) {
            this.cfg.applyConfig(config, true);
        }

        this.cfg.fireQueue();

        this.logger.log("Initialized " + this.pages.length + "-page CalendarGroup", "info");
    },

    setupConfig : function() {

        var cfg = this.cfg;

        /**
        * The number of pages to include in the CalendarGroup. This value can only be set once, in the CalendarGroup's constructor arguments.
        * @config pages
        * @type Number
        * @default 2
        */
        cfg.addProperty(DEF_CFG.PAGES.key, { value:DEF_CFG.PAGES.value, validator:cfg.checkNumber, handler:this.configPages } );

        /**
        * The positive or negative year offset from the Gregorian calendar year (assuming a January 1st rollover) to 
        * be used when displaying or parsing dates.  NOTE: All JS Date objects returned by methods, or expected as input by
        * methods will always represent the Gregorian year, in order to maintain date/month/week values.
        *
        * @config year_offset
        * @type Number
        * @default 0
        */
        cfg.addProperty(DEF_CFG.YEAR_OFFSET.key, { value:DEF_CFG.YEAR_OFFSET.value, handler: this.delegateConfig, supercedes:DEF_CFG.YEAR_OFFSET.supercedes, suppressEvent:true } );

        /**
        * The date to use to represent "Today".
        *
        * @config today
        * @type Date
        * @default Today's date
        */
        cfg.addProperty(DEF_CFG.TODAY.key, { value: new Date(DEF_CFG.TODAY.value.getTime()), supercedes:DEF_CFG.TODAY.supercedes, handler: this.configToday, suppressEvent:false } );

        /**
        * The month/year representing the current visible Calendar date (mm/yyyy)
        * @config pagedate
        * @type String | Date
        * @default Today's date
        */
        cfg.addProperty(DEF_CFG.PAGEDATE.key, { value: DEF_CFG.PAGEDATE.value || new Date(DEF_CFG.TODAY.value.getTime()), handler:this.configPageDate } );

        /**
        * The date or range of dates representing the current Calendar selection
        *
        * @config selected
        * @type String
        * @default []
        */
        cfg.addProperty(DEF_CFG.SELECTED.key, { value:[], handler:this.configSelected } );

        /**
        * The title to display above the CalendarGroup's month header. The title is inserted into the DOM as HTML, and should be escaped by the implementor if coming from an external source.
        * @config title
        * @type HTML
        * @default ""
        */
        cfg.addProperty(DEF_CFG.TITLE.key, { value:DEF_CFG.TITLE.value, handler:this.configTitle } );

        /**
        * Whether or not a close button should be displayed for this CalendarGroup
        * @config close
        * @type Boolean
        * @default false
        */
        cfg.addProperty(DEF_CFG.CLOSE.key, { value:DEF_CFG.CLOSE.value, handler:this.configClose } );

        /**
        * Whether or not an iframe shim should be placed under the Calendar to prevent select boxes from bleeding through in Internet Explorer 6 and below.
        * This property is enabled by default for IE6 and below. It is disabled by default for other browsers for performance reasons, but can be 
        * enabled if required.
        * 
        * @config iframe
        * @type Boolean
        * @default true for IE6 and below, false for all other browsers
        */
        cfg.addProperty(DEF_CFG.IFRAME.key, { value:DEF_CFG.IFRAME.value, handler:this.configIframe, validator:cfg.checkBoolean } );

        /**
        * The minimum selectable date in the current Calendar (mm/dd/yyyy)
        * @config mindate
        * @type String | Date
        * @default null
        */
        cfg.addProperty(DEF_CFG.MINDATE.key, { value:DEF_CFG.MINDATE.value, handler:this.delegateConfig } );

        /**
        * The maximum selectable date in the current Calendar (mm/dd/yyyy)
        * @config maxdate
        * @type String | Date
        * @default null
        */
        cfg.addProperty(DEF_CFG.MAXDATE.key, { value:DEF_CFG.MAXDATE.value, handler:this.delegateConfig  } );

        /**
        * True if the Calendar should allow multiple selections. False by default.
        * @config MULTI_SELECT
        * @type Boolean
        * @default false
        */
        cfg.addProperty(DEF_CFG.MULTI_SELECT.key, { value:DEF_CFG.MULTI_SELECT.value, handler:this.delegateConfig, validator:cfg.checkBoolean } );

        /**
        * True if the Calendar should allow selection of out-of-month dates. False by default.
        * @config OOM_SELECT
        * @type Boolean
        * @default false
        */
        cfg.addProperty(DEF_CFG.OOM_SELECT.key, { value:DEF_CFG.OOM_SELECT.value, handler:this.delegateConfig, validator:cfg.checkBoolean } );

        /**
        * The weekday the week begins on. Default is 0 (Sunday).
        * @config START_WEEKDAY
        * @type number
        * @default 0
        */ 
        cfg.addProperty(DEF_CFG.START_WEEKDAY.key, { value:DEF_CFG.START_WEEKDAY.value, handler:this.delegateConfig, validator:cfg.checkNumber  } );
        
        /**
        * True if the Calendar should show weekday labels. True by default.
        * @config SHOW_WEEKDAYS
        * @type Boolean
        * @default true
        */ 
        cfg.addProperty(DEF_CFG.SHOW_WEEKDAYS.key, { value:DEF_CFG.SHOW_WEEKDAYS.value, handler:this.delegateConfig, validator:cfg.checkBoolean } );
        
        /**
        * True if the Calendar should show week row headers. False by default.
        * @config SHOW_WEEK_HEADER
        * @type Boolean
        * @default false
        */ 
        cfg.addProperty(DEF_CFG.SHOW_WEEK_HEADER.key,{ value:DEF_CFG.SHOW_WEEK_HEADER.value, handler:this.delegateConfig, validator:cfg.checkBoolean } );
        
        /**
        * True if the Calendar should show week row footers. False by default.
        * @config SHOW_WEEK_FOOTER
        * @type Boolean
        * @default false
        */
        cfg.addProperty(DEF_CFG.SHOW_WEEK_FOOTER.key,{ value:DEF_CFG.SHOW_WEEK_FOOTER.value, handler:this.delegateConfig, validator:cfg.checkBoolean } );
        
        /**
        * True if the Calendar should suppress weeks that are not a part of the current month. False by default.
        * @config HIDE_BLANK_WEEKS
        * @type Boolean
        * @default false
        */  
        cfg.addProperty(DEF_CFG.HIDE_BLANK_WEEKS.key,{ value:DEF_CFG.HIDE_BLANK_WEEKS.value, handler:this.delegateConfig, validator:cfg.checkBoolean } );

        /**
        * The image URL that should be used for the left navigation arrow. The image URL is inserted into the DOM as HTML, and should be escaped by the implementor if coming from an external source.
        * @config NAV_ARROW_LEFT
        * @type String
        * @deprecated You can customize the image by overriding the default CSS class for the left arrow - "calnavleft"
        * @default null
        */  
        cfg.addProperty(DEF_CFG.NAV_ARROW_LEFT.key, { value:DEF_CFG.NAV_ARROW_LEFT.value, handler:this.delegateConfig } );

        /**
        * The image URL that should be used for the right navigation arrow. The image URL is inserted into the DOM as HTML, and should be escaped by the implementor if coming from an external source.
        * @config NAV_ARROW_RIGHT
        * @type String
        * @deprecated You can customize the image by overriding the default CSS class for the right arrow - "calnavright"
        * @default null
        */  
        cfg.addProperty(DEF_CFG.NAV_ARROW_RIGHT.key, { value:DEF_CFG.NAV_ARROW_RIGHT.value, handler:this.delegateConfig } );
    
        // Locale properties
        
        /**
        * The short month labels for the current locale. The month labels are inserted into the DOM as HTML, and should be escaped by the implementor if coming from an external source.
        * @config MONTHS_SHORT
        * @type HTML[]
        * @default ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"]
        */
        cfg.addProperty(DEF_CFG.MONTHS_SHORT.key, { value:DEF_CFG.MONTHS_SHORT.value, handler:this.delegateConfig } );
        
        /**
        * The long month labels for the current locale. The month labels are inserted into the DOM as HTML, and should be escaped by the implementor if coming from an external source.
        * @config MONTHS_LONG
        * @type HTML[]
        * @default ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"
        */ 
        cfg.addProperty(DEF_CFG.MONTHS_LONG.key,  { value:DEF_CFG.MONTHS_LONG.value, handler:this.delegateConfig } );
        
        /**
        * The 1-character weekday labels for the current locale. The weekday labels are inserted into the DOM as HTML, and should be escaped by the implementor if coming from an external source.
        * @config WEEKDAYS_1CHAR
        * @type HTML[]
        * @default ["S", "M", "T", "W", "T", "F", "S"]
        */ 
        cfg.addProperty(DEF_CFG.WEEKDAYS_1CHAR.key, { value:DEF_CFG.WEEKDAYS_1CHAR.value, handler:this.delegateConfig } );
        
        /**
        * The short weekday labels for the current locale. The weekday labels are inserted into the DOM as HTML, and should be escaped by the implementor if coming from an external source.
        * @config WEEKDAYS_SHORT
        * @type HTML[]
        * @default ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"]
        */ 
        cfg.addProperty(DEF_CFG.WEEKDAYS_SHORT.key, { value:DEF_CFG.WEEKDAYS_SHORT.value, handler:this.delegateConfig } );
        
        /**
        * The medium weekday labels for the current locale. The weekday labels are inserted into the DOM as HTML, and should be escaped by the implementor if coming from an external source.
        * @config WEEKDAYS_MEDIUM
        * @type HTML[]
        * @default ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"]
        */ 
        cfg.addProperty(DEF_CFG.WEEKDAYS_MEDIUM.key, { value:DEF_CFG.WEEKDAYS_MEDIUM.value, handler:this.delegateConfig } );
        
        /**
        * The long weekday labels for the current locale. The weekday labels are inserted into the DOM as HTML, and should be escaped by the implementor if coming from an external source.
        * @config WEEKDAYS_LONG
        * @type HTML[]
        * @default ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"]
        */ 
        cfg.addProperty(DEF_CFG.WEEKDAYS_LONG.key, { value:DEF_CFG.WEEKDAYS_LONG.value, handler:this.delegateConfig } );
    
        /**
        * The setting that determines which length of month labels should be used. Possible values are "short" and "long".
        * @config LOCALE_MONTHS
        * @type String
        * @default "long"
        */
        cfg.addProperty(DEF_CFG.LOCALE_MONTHS.key, { value:DEF_CFG.LOCALE_MONTHS.value, handler:this.delegateConfig } );
    
        /**
        * The setting that determines which length of weekday labels should be used. Possible values are "1char", "short", "medium", and "long".
        * @config LOCALE_WEEKDAYS
        * @type String
        * @default "short"
        */ 
        cfg.addProperty(DEF_CFG.LOCALE_WEEKDAYS.key, { value:DEF_CFG.LOCALE_WEEKDAYS.value, handler:this.delegateConfig } );
    
        /**
        * The value used to delimit individual dates in a date string passed to various Calendar functions.
        * @config DATE_DELIMITER
        * @type String
        * @default ","
        */
        cfg.addProperty(DEF_CFG.DATE_DELIMITER.key,  { value:DEF_CFG.DATE_DELIMITER.value, handler:this.delegateConfig } );
    
        /**
        * The value used to delimit date fields in a date string passed to various Calendar functions.
        * @config DATE_FIELD_DELIMITER
        * @type String
        * @default "/"
        */ 
        cfg.addProperty(DEF_CFG.DATE_FIELD_DELIMITER.key,{ value:DEF_CFG.DATE_FIELD_DELIMITER.value, handler:this.delegateConfig } );
    
        /**
        * The value used to delimit date ranges in a date string passed to various Calendar functions.
        * @config DATE_RANGE_DELIMITER
        * @type String
        * @default "-"
        */
        cfg.addProperty(DEF_CFG.DATE_RANGE_DELIMITER.key,{ value:DEF_CFG.DATE_RANGE_DELIMITER.value, handler:this.delegateConfig } );
    
        /**
        * The position of the month in a month/year date string
        * @config MY_MONTH_POSITION
        * @type Number
        * @default 1
        */
        cfg.addProperty(DEF_CFG.MY_MONTH_POSITION.key, { value:DEF_CFG.MY_MONTH_POSITION.value, handler:this.delegateConfig, validator:cfg.checkNumber } );
        
        /**
        * The position of the year in a month/year date string
        * @config MY_YEAR_POSITION
        * @type Number
        * @default 2
        */ 
        cfg.addProperty(DEF_CFG.MY_YEAR_POSITION.key, { value:DEF_CFG.MY_YEAR_POSITION.value, handler:this.delegateConfig, validator:cfg.checkNumber } );
        
        /**
        * The position of the month in a month/day date string
        * @config MD_MONTH_POSITION
        * @type Number
        * @default 1
        */ 
        cfg.addProperty(DEF_CFG.MD_MONTH_POSITION.key, { value:DEF_CFG.MD_MONTH_POSITION.value, handler:this.delegateConfig, validator:cfg.checkNumber } );
        
        /**
        * The position of the day in a month/year date string
        * @config MD_DAY_POSITION
        * @type Number
        * @default 2
        */ 
        cfg.addProperty(DEF_CFG.MD_DAY_POSITION.key,  { value:DEF_CFG.MD_DAY_POSITION.value, handler:this.delegateConfig, validator:cfg.checkNumber } );
        
        /**
        * The position of the month in a month/day/year date string
        * @config MDY_MONTH_POSITION
        * @type Number
        * @default 1
        */ 
        cfg.addProperty(DEF_CFG.MDY_MONTH_POSITION.key, { value:DEF_CFG.MDY_MONTH_POSITION.value, handler:this.delegateConfig, validator:cfg.checkNumber } );
        
        /**
        * The position of the day in a month/day/year date string
        * @config MDY_DAY_POSITION
        * @type Number
        * @default 2
        */ 
        cfg.addProperty(DEF_CFG.MDY_DAY_POSITION.key, { value:DEF_CFG.MDY_DAY_POSITION.value, handler:this.delegateConfig, validator:cfg.checkNumber } );
        
        /**
        * The position of the year in a month/day/year date string
        * @config MDY_YEAR_POSITION
        * @type Number
        * @default 3
        */ 
        cfg.addProperty(DEF_CFG.MDY_YEAR_POSITION.key, { value:DEF_CFG.MDY_YEAR_POSITION.value, handler:this.delegateConfig, validator:cfg.checkNumber } );
    
        /**
        * The position of the month in the month year label string used as the Calendar header
        * @config MY_LABEL_MONTH_POSITION
        * @type Number
        * @default 1
        */
        cfg.addProperty(DEF_CFG.MY_LABEL_MONTH_POSITION.key, { value:DEF_CFG.MY_LABEL_MONTH_POSITION.value, handler:this.delegateConfig, validator:cfg.checkNumber } );
    
        /**
        * The position of the year in the month year label string used as the Calendar header
        * @config MY_LABEL_YEAR_POSITION
        * @type Number
        * @default 2
        */
        cfg.addProperty(DEF_CFG.MY_LABEL_YEAR_POSITION.key, { value:DEF_CFG.MY_LABEL_YEAR_POSITION.value, handler:this.delegateConfig, validator:cfg.checkNumber } );

        /**
        * The suffix used after the month when rendering the Calendar header
        * @config MY_LABEL_MONTH_SUFFIX
        * @type String
        * @default " "
        */
        cfg.addProperty(DEF_CFG.MY_LABEL_MONTH_SUFFIX.key, { value:DEF_CFG.MY_LABEL_MONTH_SUFFIX.value, handler:this.delegateConfig } );
        
        /**
        * The suffix used after the year when rendering the Calendar header
        * @config MY_LABEL_YEAR_SUFFIX
        * @type String
        * @default ""
        */
        cfg.addProperty(DEF_CFG.MY_LABEL_YEAR_SUFFIX.key, { value:DEF_CFG.MY_LABEL_YEAR_SUFFIX.value, handler:this.delegateConfig } );

        /**
        * Configuration for the Month/Year CalendarNavigator UI which allows the user to jump directly to a 
        * specific Month/Year without having to scroll sequentially through months.
        * <p>
        * Setting this property to null (default value) or false, will disable the CalendarNavigator UI.
        * </p>
        * <p>
        * Setting this property to true will enable the CalendarNavigatior UI with the default CalendarNavigator configuration values.
        * </p>
        * <p>
        * This property can also be set to an object literal containing configuration properties for the CalendarNavigator UI.
        * The configuration object expects the the following case-sensitive properties, with the "strings" property being a nested object.
        * Any properties which are not provided will use the default values (defined in the CalendarNavigator class).
        * </p>
        * <dl>
        * <dt>strings</dt>
        * <dd><em>Object</em> :  An object with the properties shown below, defining the string labels to use in the Navigator's UI. The strings are inserted into the DOM as HTML, and should be escaped by the implementor if coming from an external source. 
        *     <dl>
        *         <dt>month</dt><dd><em>HTML</em> : The markup to use for the month label. Defaults to "Month".</dd>
        *         <dt>year</dt><dd><em>HTML</em> : The markup to use for the year label. Defaults to "Year".</dd>
        *         <dt>submit</dt><dd><em>HTML</em> : The markup to use for the submit button label. Defaults to "Okay".</dd>
        *         <dt>cancel</dt><dd><em>HTML</em> : The markup to use for the cancel button label. Defaults to "Cancel".</dd>
        *         <dt>invalidYear</dt><dd><em>HTML</em> : The markup to use for invalid year values. Defaults to "Year needs to be a number".</dd>
        *     </dl>
        * </dd>
        * <dt>monthFormat</dt><dd><em>String</em> : The month format to use. Either YAHOO.widget.Calendar.LONG, or YAHOO.widget.Calendar.SHORT. Defaults to YAHOO.widget.Calendar.LONG</dd>
        * <dt>initialFocus</dt><dd><em>String</em> : Either "year" or "month" specifying which input control should get initial focus. Defaults to "year"</dd>
        * </dl>
        * <p>E.g.</p>
        * <pre>
        * var navConfig = {
        *   strings: {
        *    month:"Calendar Month",
        *    year:"Calendar Year",
        *    submit: "Submit",
        *    cancel: "Cancel",
        *    invalidYear: "Please enter a valid year"
        *   },
        *   monthFormat: YAHOO.widget.Calendar.SHORT,
        *   initialFocus: "month"
        * }
        * </pre>
        * @config navigator
        * @type {Object|Boolean}
        * @default null
        */
        cfg.addProperty(DEF_CFG.NAV.key, { value:DEF_CFG.NAV.value, handler:this.configNavigator } );

        /**
         * The map of UI strings which the CalendarGroup UI uses.
         *
         * @config strings
         * @type {Object}
         * @default An object with the properties shown below:
         *     <dl>
         *         <dt>previousMonth</dt><dd><em>HTML</em> : The markup to use for the "Previous Month" navigation label. Defaults to "Previous Month". The string is added to the DOM as HTML, and should be escaped by the implementor if coming from an external source.</dd>
         *         <dt>nextMonth</dt><dd><em>HTML</em> : The markup to use for the "Next Month" navigation UI. Defaults to "Next Month". The string is added to the DOM as HTML, and should be escaped by the implementor if coming from an external source.</dd>
         *         <dt>close</dt><dd><em>HTML</em> : The markup to use for the close button label. Defaults to "Close". The string is added to the DOM as HTML, and should be escaped by the implementor if coming from an external source.</dd>
         *     </dl>
         */
        cfg.addProperty(DEF_CFG.STRINGS.key, { 
            value:DEF_CFG.STRINGS.value, 
            handler:this.configStrings, 
            validator: function(val) {
                return Lang.isObject(val);
            },
            supercedes: DEF_CFG.STRINGS.supercedes
        });
    },

    /**
    * Initializes CalendarGroup's built-in CustomEvents
    * @method initEvents
    */
    initEvents : function() {

        var me = this,
            strEvent = "Event",
            CE = YAHOO.util.CustomEvent;

        /**
        * Proxy subscriber to subscribe to the CalendarGroup's child Calendars' CustomEvents
        * @method sub
        * @private
        * @param {Function} fn The function to subscribe to this CustomEvent
        * @param {Object} obj The CustomEvent's scope object
        * @param {Boolean} bOverride Whether or not to apply scope correction
        */
        var sub = function(fn, obj, bOverride) {
            for (var p=0;p<me.pages.length;++p) {
                var cal = me.pages[p];
                cal[this.type + strEvent].subscribe(fn, obj, bOverride);
            }
        };

        /**
        * Proxy unsubscriber to unsubscribe from the CalendarGroup's child Calendars' CustomEvents
        * @method unsub
        * @private
        * @param {Function} fn The function to subscribe to this CustomEvent
        * @param {Object} obj The CustomEvent's scope object
        */
        var unsub = function(fn, obj) {
            for (var p=0;p<me.pages.length;++p) {
                var cal = me.pages[p];
                cal[this.type + strEvent].unsubscribe(fn, obj);
            }
        };

        var defEvents = Calendar._EVENT_TYPES;

        /**
        * Fired before a date selection is made
        * @event beforeSelectEvent
        */
        me.beforeSelectEvent = new CE(defEvents.BEFORE_SELECT);
        me.beforeSelectEvent.subscribe = sub; me.beforeSelectEvent.unsubscribe = unsub;

        /**
        * Fired when a date selection is made
        * @event selectEvent
        * @param {Array} Array of Date field arrays in the format [YYYY, MM, DD].
        */
        me.selectEvent = new CE(defEvents.SELECT); 
        me.selectEvent.subscribe = sub; me.selectEvent.unsubscribe = unsub;

        /**
        * Fired before a date or set of dates is deselected
        * @event beforeDeselectEvent
        */
        me.beforeDeselectEvent = new CE(defEvents.BEFORE_DESELECT); 
        me.beforeDeselectEvent.subscribe = sub; me.beforeDeselectEvent.unsubscribe = unsub;

        /**
        * Fired when a date or set of dates has been deselected
        * @event deselectEvent
        * @param {Array} Array of Date field arrays in the format [YYYY, MM, DD].
        */
        me.deselectEvent = new CE(defEvents.DESELECT); 
        me.deselectEvent.subscribe = sub; me.deselectEvent.unsubscribe = unsub;
        
        /**
        * Fired when the Calendar page is changed
        * @event changePageEvent
        */
        me.changePageEvent = new CE(defEvents.CHANGE_PAGE); 
        me.changePageEvent.subscribe = sub; me.changePageEvent.unsubscribe = unsub;

        /**
        * Fired before the Calendar is rendered
        * @event beforeRenderEvent
        */
        me.beforeRenderEvent = new CE(defEvents.BEFORE_RENDER);
        me.beforeRenderEvent.subscribe = sub; me.beforeRenderEvent.unsubscribe = unsub;
    
        /**
        * Fired when the Calendar is rendered
        * @event renderEvent
        */
        me.renderEvent = new CE(defEvents.RENDER);
        me.renderEvent.subscribe = sub; me.renderEvent.unsubscribe = unsub;
    
        /**
        * Fired when the Calendar is reset
        * @event resetEvent
        */
        me.resetEvent = new CE(defEvents.RESET); 
        me.resetEvent.subscribe = sub; me.resetEvent.unsubscribe = unsub;
    
        /**
        * Fired when the Calendar is cleared
        * @event clearEvent
        */
        me.clearEvent = new CE(defEvents.CLEAR);
        me.clearEvent.subscribe = sub; me.clearEvent.unsubscribe = unsub;

        /**
        * Fired just before the CalendarGroup is to be shown
        * @event beforeShowEvent
        */
        me.beforeShowEvent = new CE(defEvents.BEFORE_SHOW);
    
        /**
        * Fired after the CalendarGroup is shown
        * @event showEvent
        */
        me.showEvent = new CE(defEvents.SHOW);
    
        /**
        * Fired just before the CalendarGroup is to be hidden
        * @event beforeHideEvent
        */
        me.beforeHideEvent = new CE(defEvents.BEFORE_HIDE);
    
        /**
        * Fired after the CalendarGroup is hidden
        * @event hideEvent
        */
        me.hideEvent = new CE(defEvents.HIDE);

        /**
        * Fired just before the CalendarNavigator is to be shown
        * @event beforeShowNavEvent
        */
        me.beforeShowNavEvent = new CE(defEvents.BEFORE_SHOW_NAV);
    
        /**
        * Fired after the CalendarNavigator is shown
        * @event showNavEvent
        */
        me.showNavEvent = new CE(defEvents.SHOW_NAV);
    
        /**
        * Fired just before the CalendarNavigator is to be hidden
        * @event beforeHideNavEvent
        */
        me.beforeHideNavEvent = new CE(defEvents.BEFORE_HIDE_NAV);

        /**
        * Fired after the CalendarNavigator is hidden
        * @event hideNavEvent
        */
        me.hideNavEvent = new CE(defEvents.HIDE_NAV);

        /**
        * Fired just before the CalendarNavigator is to be rendered
        * @event beforeRenderNavEvent
        */
        me.beforeRenderNavEvent = new CE(defEvents.BEFORE_RENDER_NAV);

        /**
        * Fired after the CalendarNavigator is rendered
        * @event renderNavEvent
        */
        me.renderNavEvent = new CE(defEvents.RENDER_NAV);

        /**
        * Fired just before the CalendarGroup is to be destroyed
        * @event beforeDestroyEvent
        */
        me.beforeDestroyEvent = new CE(defEvents.BEFORE_DESTROY);

        /**
        * Fired after the CalendarGroup is destroyed. This event should be used
        * for notification only. When this event is fired, important CalendarGroup instance
        * properties, dom references and event listeners have already been 
        * removed/dereferenced, and hence the CalendarGroup instance is not in a usable 
        * state.
        *
        * @event destroyEvent
        */
        me.destroyEvent = new CE(defEvents.DESTROY);
    },
    
    /**
    * The default Config handler for the "pages" property
    * @method configPages
    * @param {String} type The CustomEvent type (usually the property name)
    * @param {Object[]} args The CustomEvent arguments. For configuration handlers, args[0] will equal the newly applied value for the property.
    * @param {Object} obj The scope object. For configuration handlers, this will usually equal the owner.
    */
    configPages : function(type, args, obj) {
        var pageCount = args[0],
            cfgPageDate = DEF_CFG.PAGEDATE.key,
            sep = "_",
            caldate,
            firstPageDate = null,
            groupCalClass = "groupcal",
            firstClass = "first-of-type",
            lastClass = "last-of-type";

        for (var p=0;p<pageCount;++p) {
            var calId = this.id + sep + p,
                calContainerId = this.containerId + sep + p,
                childConfig = this.cfg.getConfig();

            childConfig.close = false;
            childConfig.title = false;
            childConfig.navigator = null;

            if (p > 0) {
                caldate = new Date(firstPageDate);
                this._setMonthOnDate(caldate, caldate.getMonth() + p);
                childConfig.pageDate = caldate;
            }

            var cal = this.constructChild(calId, calContainerId, childConfig);

            Dom.removeClass(cal.oDomContainer, this.Style.CSS_SINGLE);
            Dom.addClass(cal.oDomContainer, groupCalClass);

            if (p===0) {
                firstPageDate = cal.cfg.getProperty(cfgPageDate);
                Dom.addClass(cal.oDomContainer, firstClass);
            }
    
            if (p==(pageCount-1)) {
                Dom.addClass(cal.oDomContainer, lastClass);
            }
    
            cal.parent = this;
            cal.index = p; 
    
            this.pages[this.pages.length] = cal;
        }
    },
    
    /**
    * The default Config handler for the "pagedate" property
    * @method configPageDate
    * @param {String} type The CustomEvent type (usually the property name)
    * @param {Object[]} args The CustomEvent arguments. For configuration handlers, args[0] will equal the newly applied value for the property.
    * @param {Object} obj The scope object. For configuration handlers, this will usually equal the owner.
    */
    configPageDate : function(type, args, obj) {
        var val = args[0],
            firstPageDate;

        var cfgPageDate = DEF_CFG.PAGEDATE.key;
        
        for (var p=0;p<this.pages.length;++p) {
            var cal = this.pages[p];
            if (p === 0) {
                firstPageDate = cal._parsePageDate(val);
                cal.cfg.setProperty(cfgPageDate, firstPageDate);
            } else {
                var pageDate = new Date(firstPageDate);
                this._setMonthOnDate(pageDate, pageDate.getMonth() + p);
                cal.cfg.setProperty(cfgPageDate, pageDate);
            }
        }
    },
    
    /**
    * The default Config handler for the CalendarGroup "selected" property
    * @method configSelected
    * @param {String} type The CustomEvent type (usually the property name)
    * @param {Object[]} args The CustomEvent arguments. For configuration handlers, args[0] will equal the newly applied value for the property.
    * @param {Object} obj The scope object. For configuration handlers, this will usually equal the owner.
    */
    configSelected : function(type, args, obj) {
        var cfgSelected = DEF_CFG.SELECTED.key;
        this.delegateConfig(type, args, obj);
        var selected = (this.pages.length > 0) ? this.pages[0].cfg.getProperty(cfgSelected) : []; 
        this.cfg.setProperty(cfgSelected, selected, true);
    },

    
    /**
    * Delegates a configuration property to the CustomEvents associated with the CalendarGroup's children
    * @method delegateConfig
    * @param {String} type The CustomEvent type (usually the property name)
    * @param {Object[]} args The CustomEvent arguments. For configuration handlers, args[0] will equal the newly applied value for the property.
    * @param {Object} obj The scope object. For configuration handlers, this will usually equal the owner.
    */
    delegateConfig : function(type, args, obj) {
        var val = args[0];
        var cal;
    
        for (var p=0;p<this.pages.length;p++) {
            cal = this.pages[p];
            cal.cfg.setProperty(type, val);
        }
    },

    /**
    * Adds a function to all child Calendars within this CalendarGroup.
    * @method setChildFunction
    * @param {String}  fnName  The name of the function
    * @param {Function}  fn   The function to apply to each Calendar page object
    */
    setChildFunction : function(fnName, fn) {
        var pageCount = this.cfg.getProperty(DEF_CFG.PAGES.key);
    
        for (var p=0;p<pageCount;++p) {
            this.pages[p][fnName] = fn;
        }
    },

    /**
    * Calls a function within all child Calendars within this CalendarGroup.
    * @method callChildFunction
    * @param {String}  fnName  The name of the function
    * @param {Array}  args  The arguments to pass to the function
    */
    callChildFunction : function(fnName, args) {
        var pageCount = this.cfg.getProperty(DEF_CFG.PAGES.key);

        for (var p=0;p<pageCount;++p) {
            var page = this.pages[p];
            if (page[fnName]) {
                var fn = page[fnName];
                fn.call(page, args);
            }
        } 
    },

    /**
    * Constructs a child calendar. This method can be overridden if a subclassed version of the default
    * calendar is to be used.
    * @method constructChild
    * @param {String} id   The id of the table element that will represent the calendar widget
    * @param {String} containerId The id of the container div element that will wrap the calendar table
    * @param {Object} config  The configuration object containing the Calendar's arguments
    * @return {YAHOO.widget.Calendar} The YAHOO.widget.Calendar instance that is constructed
    */
    constructChild : function(id,containerId,config) {
        var container = document.getElementById(containerId);
        if (! container) {
            container = document.createElement("div");
            container.id = containerId;
            this.oDomContainer.appendChild(container);
        }
        return new Calendar(id,containerId,config);
    },
    
    /**
    * Sets the calendar group's month explicitly. This month will be set into the first
    * page of the multi-page calendar, and all other months will be iterated appropriately.
    * @method setMonth
    * @param {Number} month  The numeric month, from 0 (January) to 11 (December)
    */
    setMonth : function(month) {
        month = parseInt(month, 10);
        var currYear;

        var cfgPageDate = DEF_CFG.PAGEDATE.key;

        for (var p=0; p<this.pages.length; ++p) {
            var cal = this.pages[p];
            var pageDate = cal.cfg.getProperty(cfgPageDate);
            if (p === 0) {
                currYear = pageDate.getFullYear();
            } else {
                pageDate.setFullYear(currYear);
            }
            this._setMonthOnDate(pageDate, month+p); 
            cal.cfg.setProperty(cfgPageDate, pageDate);
        }
    },

    /**
    * Sets the calendar group's year explicitly. This year will be set into the first
    * page of the multi-page calendar, and all other months will be iterated appropriately.
    * @method setYear
    * @param {Number} year  The numeric 4-digit year
    */
    setYear : function(year) {
    
        var cfgPageDate = DEF_CFG.PAGEDATE.key;
    
        year = parseInt(year, 10);
        for (var p=0;p<this.pages.length;++p) {
            var cal = this.pages[p];
            var pageDate = cal.cfg.getProperty(cfgPageDate);
    
            if ((pageDate.getMonth()+1) == 1 && p>0) {
                year+=1;
            }
            cal.setYear(year);
        }
    },

    /**
    * Calls the render function of all child calendars within the group.
    * @method render
    */
    render : function() {
        this.renderHeader();
        for (var p=0;p<this.pages.length;++p) {
            var cal = this.pages[p];
            cal.render();
        }
        this.renderFooter();
    },

    /**
    * Selects a date or a collection of dates on the current calendar. This method, by default,
    * does not call the render method explicitly. Once selection has completed, render must be 
    * called for the changes to be reflected visually.
    * @method select
    * @param    {String/Date/Date[]}    date    The date string of dates to select in the current calendar. Valid formats are
    *                               individual date(s) (12/24/2005,12/26/2005) or date range(s) (12/24/2005-1/1/2006).
    *                               Multiple comma-delimited dates can also be passed to this method (12/24/2005,12/11/2005-12/13/2005).
    *                               This method can also take a JavaScript Date object or an array of Date objects.
    * @return {Date[]} Array of JavaScript Date objects representing all individual dates that are currently selected.
    */
    select : function(date) {
        for (var p=0;p<this.pages.length;++p) {
            var cal = this.pages[p];
            cal.select(date);
        }
        return this.getSelectedDates();
    },

    /**
    * Selects dates in the CalendarGroup based on the cell index provided. This method is used to select cells without having to do a full render. The selected style is applied to the cells directly.
    * The value of the MULTI_SELECT Configuration attribute will determine the set of dates which get selected. 
    * <ul>
    *    <li>If MULTI_SELECT is false, selectCell will select the cell at the specified index for only the last displayed Calendar page.</li>
    *    <li>If MULTI_SELECT is true, selectCell will select the cell at the specified index, on each displayed Calendar page.</li>
    * </ul>
    * @method selectCell
    * @param {Number} cellIndex The index of the cell to be selected. 
    * @return {Date[]} Array of JavaScript Date objects representing all individual dates that are currently selected.
    */
    selectCell : function(cellIndex) {
        for (var p=0;p<this.pages.length;++p) {
            var cal = this.pages[p];
            cal.selectCell(cellIndex);
        }
        return this.getSelectedDates();
    },
    
    /**
    * Deselects a date or a collection of dates on the current calendar. This method, by default,
    * does not call the render method explicitly. Once deselection has completed, render must be 
    * called for the changes to be reflected visually.
    * @method deselect
    * @param {String/Date/Date[]} date The date string of dates to deselect in the current calendar. Valid formats are
    *        individual date(s) (12/24/2005,12/26/2005) or date range(s) (12/24/2005-1/1/2006).
    *        Multiple comma-delimited dates can also be passed to this method (12/24/2005,12/11/2005-12/13/2005).
    *        This method can also take a JavaScript Date object or an array of Date objects. 
    * @return {Date[]}   Array of JavaScript Date objects representing all individual dates that are currently selected.
    */
    deselect : function(date) {
        for (var p=0;p<this.pages.length;++p) {
            var cal = this.pages[p];
            cal.deselect(date);
        }
        return this.getSelectedDates();
    },
    
    /**
    * Deselects all dates on the current calendar.
    * @method deselectAll
    * @return {Date[]}  Array of JavaScript Date objects representing all individual dates that are currently selected.
    *      Assuming that this function executes properly, the return value should be an empty array.
    *      However, the empty array is returned for the sake of being able to check the selection status
    *      of the calendar.
    */
    deselectAll : function() {
        for (var p=0;p<this.pages.length;++p) {
            var cal = this.pages[p];
            cal.deselectAll();
        }
        return this.getSelectedDates();
    },

    /**
    * Deselects dates in the CalendarGroup based on the cell index provided. This method is used to select cells without having to do a full render. The selected style is applied to the cells directly.
    * deselectCell will deselect the cell at the specified index on each displayed Calendar page.
    *
    * @method deselectCell
    * @param {Number} cellIndex The index of the cell to deselect. 
    * @return {Date[]} Array of JavaScript Date objects representing all individual dates that are currently selected.
    */
    deselectCell : function(cellIndex) {
        for (var p=0;p<this.pages.length;++p) {
            var cal = this.pages[p];
            cal.deselectCell(cellIndex);
        }
        return this.getSelectedDates();
    },

    /**
    * Resets the calendar widget to the originally selected month and year, and 
    * sets the calendar to the initial selection(s).
    * @method reset
    */
    reset : function() {
        for (var p=0;p<this.pages.length;++p) {
            var cal = this.pages[p];
            cal.reset();
        }
    },

    /**
    * Clears the selected dates in the current calendar widget and sets the calendar
    * to the current month and year.
    * @method clear
    */
    clear : function() {
        for (var p=0;p<this.pages.length;++p) {
            var cal = this.pages[p];
            cal.clear();
        }

        this.cfg.setProperty(DEF_CFG.SELECTED.key, []);
        this.cfg.setProperty(DEF_CFG.PAGEDATE.key, new Date(this.pages[0].today.getTime()));
        this.render();
    },

    /**
    * Navigates to the next month page in the calendar widget.
    * @method nextMonth
    */
    nextMonth : function() {
        for (var p=0;p<this.pages.length;++p) {
            var cal = this.pages[p];
            cal.nextMonth();
        }
    },
    
    /**
    * Navigates to the previous month page in the calendar widget.
    * @method previousMonth
    */
    previousMonth : function() {
        for (var p=this.pages.length-1;p>=0;--p) {
            var cal = this.pages[p];
            cal.previousMonth();
        }
    },
    
    /**
    * Navigates to the next year in the currently selected month in the calendar widget.
    * @method nextYear
    */
    nextYear : function() {
        for (var p=0;p<this.pages.length;++p) {
            var cal = this.pages[p];
            cal.nextYear();
        }
    },

    /**
    * Navigates to the previous year in the currently selected month in the calendar widget.
    * @method previousYear
    */
    previousYear : function() {
        for (var p=0;p<this.pages.length;++p) {
            var cal = this.pages[p];
            cal.previousYear();
        }
    },

    /**
    * Gets the list of currently selected dates from the calendar.
    * @return   An array of currently selected JavaScript Date objects.
    * @type Date[]
    */
    getSelectedDates : function() { 
        var returnDates = [];
        var selected = this.cfg.getProperty(DEF_CFG.SELECTED.key);
        for (var d=0;d<selected.length;++d) {
            var dateArray = selected[d];

            var date = DateMath.getDate(dateArray[0],dateArray[1]-1,dateArray[2]);
            returnDates.push(date);
        }

        returnDates.sort( function(a,b) { return a-b; } );
        return returnDates;
    },

    /**
    * Adds a renderer to the render stack. The function reference passed to this method will be executed
    * when a date cell matches the conditions specified in the date string for this renderer.
    * 
    * <p>NOTE: The contents of the cell set by the renderer will be added to the DOM as HTML. The custom renderer implementation should 
    * escape markup used to set the cell contents, if coming from an external source.<p>
    * @method addRenderer
    * @param {String} sDates  A date string to associate with the specified renderer. Valid formats
    *         include date (12/24/2005), month/day (12/24), and range (12/1/2004-1/1/2005)
    * @param {Function} fnRender The function executed to render cells that match the render rules for this renderer.
    */
    addRenderer : function(sDates, fnRender) {
        for (var p=0;p<this.pages.length;++p) {
            var cal = this.pages[p];
            cal.addRenderer(sDates, fnRender);
        }
    },

    /**
    * Adds a month renderer to the render stack. The function reference passed to this method will be executed
    * when a date cell matches the month passed to this method
    * 
    * <p>NOTE: The contents of the cell set by the renderer will be added to the DOM as HTML. The custom renderer implementation should 
    * escape markup used to set the cell contents, if coming from an external source.<p>
    * @method addMonthRenderer
    * @param {Number} month  The month (1-12) to associate with this renderer
    * @param {Function} fnRender The function executed to render cells that match the render rules for this renderer.
    */
    addMonthRenderer : function(month, fnRender) {
        for (var p=0;p<this.pages.length;++p) {
            var cal = this.pages[p];
            cal.addMonthRenderer(month, fnRender);
        }
    },

    /**
    * Adds a weekday renderer to the render stack. The function reference passed to this method will be executed
    * when a date cell matches the weekday passed to this method.
    *
    * <p>NOTE: The contents of the cell set by the renderer will be added to the DOM as HTML. The custom renderer implementation should 
    * escape HTML used to set the cell contents, if coming from an external source.<p>
    *
    * @method addWeekdayRenderer
    * @param {Number} weekday  The weekday (Sunday = 1, Monday = 2 ... Saturday = 7) to associate with this renderer
    * @param {Function} fnRender The function executed to render cells that match the render rules for this renderer.
    */
    addWeekdayRenderer : function(weekday, fnRender) {
        for (var p=0;p<this.pages.length;++p) {
            var cal = this.pages[p];
            cal.addWeekdayRenderer(weekday, fnRender);
        }
    },

    /**
     * Removes all custom renderers added to the CalendarGroup through the addRenderer, addMonthRenderer and 
     * addWeekRenderer methods. CalendarGroup's render method needs to be called to after removing renderers 
     * to see the changes applied.
     * 
     * @method removeRenderers
     */
    removeRenderers : function() {
        this.callChildFunction("removeRenderers");
    },

    /**
    * Renders the header for the CalendarGroup.
    * @method renderHeader
    */
    renderHeader : function() {
        // EMPTY DEFAULT IMPL
    },

    /**
    * Renders a footer for the 2-up calendar container. By default, this method is
    * unimplemented.
    * @method renderFooter
    */
    renderFooter : function() {
        // EMPTY DEFAULT IMPL
    },

    /**
    * Adds the designated number of months to the current calendar month, and sets the current
    * calendar page date to the new month.
    * @method addMonths
    * @param {Number} count The number of months to add to the current calendar
    */
    addMonths : function(count) {
        this.callChildFunction("addMonths", count);
    },
    
    /**
    * Subtracts the designated number of months from the current calendar month, and sets the current
    * calendar page date to the new month.
    * @method subtractMonths
    * @param {Number} count The number of months to subtract from the current calendar
    */
    subtractMonths : function(count) {
        this.callChildFunction("subtractMonths", count);
    },

    /**
    * Adds the designated number of years to the current calendar, and sets the current
    * calendar page date to the new month.
    * @method addYears
    * @param {Number} count The number of years to add to the current calendar
    */
    addYears : function(count) {
        this.callChildFunction("addYears", count);
    },

    /**
    * Subtcats the designated number of years from the current calendar, and sets the current
    * calendar page date to the new month.
    * @method subtractYears
    * @param {Number} count The number of years to subtract from the current calendar
    */
    subtractYears : function(count) {
        this.callChildFunction("subtractYears", count);
    },

    /**
     * Returns the Calendar page instance which has a pagedate (month/year) matching the given date. 
     * Returns null if no match is found.
     * 
     * @method getCalendarPage
     * @param {Date} date The JavaScript Date object for which a Calendar page is to be found.
     * @return {Calendar} The Calendar page instance representing the month to which the date 
     * belongs.
     */
    getCalendarPage : function(date) {
        var cal = null;
        if (date) {
            var y = date.getFullYear(),
                m = date.getMonth();

            var pages = this.pages;
            for (var i = 0; i < pages.length; ++i) {
                var pageDate = pages[i].cfg.getProperty("pagedate");
                if (pageDate.getFullYear() === y && pageDate.getMonth() === m) {
                    cal = pages[i];
                    break;
                }
            }
        }
        return cal;
    },

    /**
    * Sets the month on a Date object, taking into account year rollover if the month is less than 0 or greater than 11.
    * The Date object passed in is modified. It should be cloned before passing it into this method if the original value needs to be maintained
    * @method _setMonthOnDate
    * @private
    * @param {Date} date The Date object on which to set the month index
    * @param {Number} iMonth The month index to set
    */
    _setMonthOnDate : function(date, iMonth) {
        // Bug in Safari 1.3, 2.0 (WebKit build < 420), Date.setMonth does not work consistently if iMonth is not 0-11
        if (YAHOO.env.ua.webkit && YAHOO.env.ua.webkit < 420 && (iMonth < 0 || iMonth > 11)) {
            var newDate = DateMath.add(date, DateMath.MONTH, iMonth-date.getMonth());
            date.setTime(newDate.getTime());
        } else {
            date.setMonth(iMonth);
        }
    },
    
    /**
     * Fixes the width of the CalendarGroup container element, to account for miswrapped floats
     * @method _fixWidth
     * @private
     */
    _fixWidth : function() {
        var w = 0;
        for (var p=0;p<this.pages.length;++p) {
            var cal = this.pages[p];
            w += cal.oDomContainer.offsetWidth;
        }
        if (w > 0) {
            this.oDomContainer.style.width = w + "px";
        }
    },
    
    /**
    * Returns a string representation of the object.
    * @method toString
    * @return {String} A string representation of the CalendarGroup object.
    */
    toString : function() {
        return "CalendarGroup " + this.id;
    },

    /**
     * Destroys the CalendarGroup instance. The method will remove references
     * to HTML elements, remove any event listeners added by the CalendarGroup.
     * 
     * It will also destroy the Config and CalendarNavigator instances created by the 
     * CalendarGroup and the individual Calendar instances created for each page.
     *
     * @method destroy
     */
    destroy : function() {

        if (this.beforeDestroyEvent.fire()) {

            var cal = this;
    
            // Child objects
            if (cal.navigator) {
                cal.navigator.destroy();
            }
    
            if (cal.cfg) {
                cal.cfg.destroy();
            }
    
            // DOM event listeners
            Event.purgeElement(cal.oDomContainer, true);
    
            // Generated markup/DOM - Not removing the container DIV since we didn't create it.
            Dom.removeClass(cal.oDomContainer, CalendarGroup.CSS_CONTAINER);
            Dom.removeClass(cal.oDomContainer, CalendarGroup.CSS_MULTI_UP);
            
            for (var i = 0, l = cal.pages.length; i < l; i++) {
                cal.pages[i].destroy();
                cal.pages[i] = null;
            }
    
            cal.oDomContainer.innerHTML = "";
    
            // JS-to-DOM references
            cal.oDomContainer = null;
    
            this.destroyEvent.fire();
        }
    }
};

/**
* CSS class representing the container for the calendar
* @property YAHOO.widget.CalendarGroup.CSS_CONTAINER
* @static
* @final
* @type String
*/
CalendarGroup.CSS_CONTAINER = "yui-calcontainer";

/**
* CSS class representing the container for the calendar
* @property YAHOO.widget.CalendarGroup.CSS_MULTI_UP
* @static
* @final
* @type String
*/
CalendarGroup.CSS_MULTI_UP = "multi";

/**
* CSS class representing the title for the 2-up calendar
* @property YAHOO.widget.CalendarGroup.CSS_2UPTITLE
* @static
* @final
* @type String
*/
CalendarGroup.CSS_2UPTITLE = "title";

/**
* CSS class representing the close icon for the 2-up calendar
* @property YAHOO.widget.CalendarGroup.CSS_2UPCLOSE
* @static
* @final
* @deprecated Along with Calendar.IMG_ROOT and NAV_ARROW_LEFT, NAV_ARROW_RIGHT configuration properties.
*     Calendar's <a href="YAHOO.widget.Calendar.html#Style.CSS_CLOSE">Style.CSS_CLOSE</a> property now represents the CSS class used to render the close icon
* @type String
*/
CalendarGroup.CSS_2UPCLOSE = "close-icon";

YAHOO.lang.augmentProto(CalendarGroup, Calendar, "buildDayLabel",
                                                 "buildMonthLabel",
                                                 "renderOutOfBoundsDate",
                                                 "renderRowHeader",
                                                 "renderRowFooter",
                                                 "renderCellDefault",
                                                 "styleCellDefault",
                                                 "renderCellStyleHighlight1",
                                                 "renderCellStyleHighlight2",
                                                 "renderCellStyleHighlight3",
                                                 "renderCellStyleHighlight4",
                                                 "renderCellStyleToday",
                                                 "renderCellStyleSelected",
                                                 "renderCellNotThisMonth",
                                                 "styleCellNotThisMonth",
                                                 "renderBodyCellRestricted",
                                                 "initStyles",
                                                 "configTitle",
                                                 "configClose",
                                                 "configIframe",
                                                 "configStrings",
                                                 "configToday",
                                                 "configNavigator",
                                                 "createTitleBar",
                                                 "createCloseButton",
                                                 "removeTitleBar",
                                                 "removeCloseButton",
                                                 "hide",
                                                 "show",
                                                 "toDate",
                                                 "_toDate",
                                                 "_parseArgs",
                                                 "browser");

YAHOO.widget.CalGrp = CalendarGroup;
YAHOO.widget.CalendarGroup = CalendarGroup;

/**
* @class YAHOO.widget.Calendar2up
* @extends YAHOO.widget.CalendarGroup
* @deprecated The old Calendar2up class is no longer necessary, since CalendarGroup renders in a 2up view by default.
*/
YAHOO.widget.Calendar2up = function(id, containerId, config) {
    this.init(id, containerId, config);
};

YAHOO.extend(YAHOO.widget.Calendar2up, CalendarGroup);

/**
* @deprecated The old Calendar2up class is no longer necessary, since CalendarGroup renders in a 2up view by default.
*/
YAHOO.widget.Cal2up = YAHOO.widget.Calendar2up;

})();