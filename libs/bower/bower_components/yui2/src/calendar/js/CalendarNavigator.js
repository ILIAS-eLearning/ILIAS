/**
 * The CalendarNavigator is used along with a Calendar/CalendarGroup to 
 * provide a Month/Year popup navigation control, allowing the user to navigate 
 * to a specific month/year in the Calendar/CalendarGroup without having to 
 * scroll through months sequentially
 *
 * @namespace YAHOO.widget
 * @class CalendarNavigator
 * @constructor
 * @param {Calendar|CalendarGroup} cal The instance of the Calendar or CalendarGroup to which this CalendarNavigator should be attached.
 */
YAHOO.widget.CalendarNavigator = function(cal) {
    this.init(cal);
};

(function() {
    // Setup static properties (inside anon fn, so that we can use shortcuts)
    var CN = YAHOO.widget.CalendarNavigator;

    /**
     * YAHOO.widget.CalendarNavigator.CLASSES contains constants
     * for the class values applied to the CalendarNaviatgator's 
     * DOM elements
     * @property YAHOO.widget.CalendarNavigator.CLASSES
     * @type Object
     * @static
     */
    CN.CLASSES = {
        /**
         * Class applied to the Calendar Navigator's bounding box
         * @property YAHOO.widget.CalendarNavigator.CLASSES.NAV
         * @type String
         * @static
         */
        NAV :"yui-cal-nav",
        /**
         * Class applied to the Calendar/CalendarGroup's bounding box to indicate
         * the Navigator is currently visible
         * @property YAHOO.widget.CalendarNavigator.CLASSES.NAV_VISIBLE
         * @type String
         * @static
         */
        NAV_VISIBLE: "yui-cal-nav-visible",
        /**
         * Class applied to the Navigator mask's bounding box
         * @property YAHOO.widget.CalendarNavigator.CLASSES.MASK
         * @type String
         * @static
         */
        MASK : "yui-cal-nav-mask",
        /**
         * Class applied to the year label/control bounding box
         * @property YAHOO.widget.CalendarNavigator.CLASSES.YEAR
         * @type String
         * @static
         */
        YEAR : "yui-cal-nav-y",
        /**
         * Class applied to the month label/control bounding box
         * @property YAHOO.widget.CalendarNavigator.CLASSES.MONTH
         * @type String
         * @static
         */
        MONTH : "yui-cal-nav-m",
        /**
         * Class applied to the submit/cancel button's bounding box
         * @property YAHOO.widget.CalendarNavigator.CLASSES.BUTTONS
         * @type String
         * @static
         */
        BUTTONS : "yui-cal-nav-b",
        /**
         * Class applied to buttons wrapping element
         * @property YAHOO.widget.CalendarNavigator.CLASSES.BUTTON
         * @type String
         * @static
         */
        BUTTON : "yui-cal-nav-btn",
        /**
         * Class applied to the validation error area's bounding box
         * @property YAHOO.widget.CalendarNavigator.CLASSES.ERROR
         * @type String
         * @static
         */
        ERROR : "yui-cal-nav-e",
        /**
         * Class applied to the year input control
         * @property YAHOO.widget.CalendarNavigator.CLASSES.YEAR_CTRL
         * @type String
         * @static
         */
        YEAR_CTRL : "yui-cal-nav-yc",
        /**
         * Class applied to the month input control
         * @property YAHOO.widget.CalendarNavigator.CLASSES.MONTH_CTRL
         * @type String
         * @static
         */
        MONTH_CTRL : "yui-cal-nav-mc",
        /**
         * Class applied to controls with invalid data (e.g. a year input field with invalid an year)
         * @property YAHOO.widget.CalendarNavigator.CLASSES.INVALID
         * @type String
         * @static
         */
        INVALID : "yui-invalid",
        /**
         * Class applied to default controls
         * @property YAHOO.widget.CalendarNavigator.CLASSES.DEFAULT
         * @type String
         * @static
         */
        DEFAULT : "yui-default"
    };

    /**
     * Object literal containing the default configuration values for the CalendarNavigator
     * The configuration object is expected to follow the format below, with the properties being
     * case sensitive.
     * <dl>
     * <dt>strings</dt>
     * <dd><em>Object</em> :  An object with the properties shown below, defining the string labels to use in the Navigator's UI
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
     * @property DEFAULT_CONFIG
     * @type Object
     * @static
     */
    CN.DEFAULT_CONFIG = {
        strings : {
            month: "Month",
            year: "Year",
            submit: "Okay",
            cancel: "Cancel",
            invalidYear : "Year needs to be a number"
        },
        monthFormat: YAHOO.widget.Calendar.LONG,
        initialFocus: "year"
    };

    /**
     * Object literal containing the default configuration values for the CalendarNavigator
     * @property _DEFAULT_CFG
     * @protected
     * @deprecated Made public. See the public DEFAULT_CONFIG property
     * @type Object
     * @static
     */
    CN._DEFAULT_CFG = CN.DEFAULT_CONFIG;


    /**
     * The suffix added to the Calendar/CalendarGroup's ID, to generate
     * a unique ID for the Navigator and it's bounding box.
     * @property YAHOO.widget.CalendarNavigator.ID_SUFFIX
     * @static
     * @type String
     * @final
     */
    CN.ID_SUFFIX = "_nav";
    /**
     * The suffix added to the Navigator's ID, to generate
     * a unique ID for the month control.
     * @property YAHOO.widget.CalendarNavigator.MONTH_SUFFIX
     * @static
     * @type String 
     * @final
     */
    CN.MONTH_SUFFIX = "_month";
    /**
     * The suffix added to the Navigator's ID, to generate
     * a unique ID for the year control.
     * @property YAHOO.widget.CalendarNavigator.YEAR_SUFFIX
     * @static
     * @type String
     * @final
     */
    CN.YEAR_SUFFIX = "_year";
    /**
     * The suffix added to the Navigator's ID, to generate
     * a unique ID for the error bounding box.
     * @property YAHOO.widget.CalendarNavigator.ERROR_SUFFIX
     * @static
     * @type String
     * @final
     */
    CN.ERROR_SUFFIX = "_error";
    /**
     * The suffix added to the Navigator's ID, to generate
     * a unique ID for the "Cancel" button.
     * @property YAHOO.widget.CalendarNavigator.CANCEL_SUFFIX
     * @static
     * @type String
     * @final
     */
    CN.CANCEL_SUFFIX = "_cancel";
    /**
     * The suffix added to the Navigator's ID, to generate
     * a unique ID for the "Submit" button.
     * @property YAHOO.widget.CalendarNavigator.SUBMIT_SUFFIX
     * @static
     * @type String
     * @final
     */
    CN.SUBMIT_SUFFIX = "_submit";

    /**
     * The number of digits to which the year input control is to be limited.
     * @property YAHOO.widget.CalendarNavigator.YR_MAX_DIGITS
     * @static
     * @type Number
     */
    CN.YR_MAX_DIGITS = 4;

    /**
     * The amount by which to increment the current year value,
     * when the arrow up/down key is pressed on the year control
     * @property YAHOO.widget.CalendarNavigator.YR_MINOR_INC
     * @static
     * @type Number
     */
    CN.YR_MINOR_INC = 1;

    /**
     * The amount by which to increment the current year value,
     * when the page up/down key is pressed on the year control
     * @property YAHOO.widget.CalendarNavigator.YR_MAJOR_INC
     * @static
     * @type Number
     */
    CN.YR_MAJOR_INC = 10;

    /**
     * Artificial delay (in ms) between the time the Navigator is hidden
     * and the Calendar/CalendarGroup state is updated. Allows the user
     * the see the Calendar/CalendarGroup page changing. If set to 0
     * the Calendar/CalendarGroup page will be updated instantly
     * @property YAHOO.widget.CalendarNavigator.UPDATE_DELAY
     * @static
     * @type Number
     */
    CN.UPDATE_DELAY = 50;

    /**
     * Regular expression used to validate the year input
     * @property YAHOO.widget.CalendarNavigator.YR_PATTERN
     * @static
     * @type RegExp
     */
    CN.YR_PATTERN = /^\d+$/;
    /**
     * Regular expression used to trim strings
     * @property YAHOO.widget.CalendarNavigator.TRIM
     * @static
     * @type RegExp
     */
    CN.TRIM = /^\s*(.*?)\s*$/;
})();

YAHOO.widget.CalendarNavigator.prototype = {

    /**
     * The unique ID for this CalendarNavigator instance
     * @property id
     * @type String
     */
    id : null,

    /**
     * The Calendar/CalendarGroup instance to which the navigator belongs
     * @property cal
     * @type {Calendar|CalendarGroup}
     */
    cal : null,

    /**
     * Reference to the HTMLElement used to render the navigator's bounding box
     * @property navEl
     * @type HTMLElement
     */
    navEl : null,

    /**
     * Reference to the HTMLElement used to render the navigator's mask
     * @property maskEl
     * @type HTMLElement
     */
    maskEl : null,

    /**
     * Reference to the HTMLElement used to input the year
     * @property yearEl
     * @type HTMLElement
     */
    yearEl : null,

    /**
     * Reference to the HTMLElement used to input the month
     * @property monthEl
     * @type HTMLElement
     */
    monthEl : null,

    /**
     * Reference to the HTMLElement used to display validation errors
     * @property errorEl
     * @type HTMLElement
     */
    errorEl : null,

    /**
     * Reference to the HTMLElement used to update the Calendar/Calendar group
     * with the month/year values
     * @property submitEl
     * @type HTMLElement
     */
    submitEl : null,
    
    /**
     * Reference to the HTMLElement used to hide the navigator without updating the 
     * Calendar/Calendar group
     * @property cancelEl
     * @type HTMLElement
     */
    cancelEl : null,

    /** 
     * Reference to the first focusable control in the navigator (by default monthEl)
     * @property firstCtrl
     * @type HTMLElement
     */
    firstCtrl : null,
    
    /** 
     * Reference to the last focusable control in the navigator (by default cancelEl)
     * @property lastCtrl
     * @type HTMLElement
     */
    lastCtrl : null,

    /**
     * The document containing the Calendar/Calendar group instance
     * @protected
     * @property _doc
     * @type HTMLDocument
     */
    _doc : null,

    /**
     * Internal state property for the current year displayed in the navigator
     * @protected
     * @property _year
     * @type Number
     */
    _year: null,
    
    /**
     * Internal state property for the current month index displayed in the navigator
     * @protected
     * @property _month
     * @type Number
     */
    _month: 0,

    /**
     * Private internal state property which indicates whether or not the 
     * Navigator has been rendered.
     * @private
     * @property __rendered
     * @type Boolean
     */
    __rendered: false,

    /**
     * Init lifecycle method called as part of construction
     * 
     * @method init
     * @param {Calendar} cal The instance of the Calendar or CalendarGroup to which this CalendarNavigator should be attached
     */
    init : function(cal) {
        var calBox = cal.oDomContainer;

        this.cal = cal;
        this.id = calBox.id + YAHOO.widget.CalendarNavigator.ID_SUFFIX;
        this._doc = calBox.ownerDocument;

        /**
         * Private flag, to identify IE Quirks
         * @private
         * @property __isIEQuirks
         */
        var ie = YAHOO.env.ua.ie;
        this.__isIEQuirks = (ie && ((ie <= 6) || (this._doc.compatMode == "BackCompat")));
    },

    /**
     * Displays the navigator and mask, updating the input controls to reflect the 
     * currently set month and year. The show method will invoke the render method
     * if the navigator has not been renderered already, allowing for lazy rendering
     * of the control.
     * 
     * The show method will fire the Calendar/CalendarGroup's beforeShowNav and showNav events
     * 
     * @method show
     */
    show : function() {
        var CLASSES = YAHOO.widget.CalendarNavigator.CLASSES;

        if (this.cal.beforeShowNavEvent.fire()) {
            if (!this.__rendered) {
                this.render();
            }
            this.clearErrors();

            this._updateMonthUI();
            this._updateYearUI();
            this._show(this.navEl, true);

            this.setInitialFocus();
            this.showMask();

            YAHOO.util.Dom.addClass(this.cal.oDomContainer, CLASSES.NAV_VISIBLE);
            this.cal.showNavEvent.fire();
        }
    },

    /**
     * Hides the navigator and mask
     * 
     * The show method will fire the Calendar/CalendarGroup's beforeHideNav event and hideNav events
     * @method hide
     */
    hide : function() {
        var CLASSES = YAHOO.widget.CalendarNavigator.CLASSES;

        if (this.cal.beforeHideNavEvent.fire()) {
            this._show(this.navEl, false);
            this.hideMask();
            YAHOO.util.Dom.removeClass(this.cal.oDomContainer, CLASSES.NAV_VISIBLE);
            this.cal.hideNavEvent.fire();
        }
    },
    

    /**
     * Displays the navigator's mask element
     * 
     * @method showMask
     */
    showMask : function() {
        this._show(this.maskEl, true);
        if (this.__isIEQuirks) {
            this._syncMask();
        }
    },

    /**
     * Hides the navigator's mask element
     * 
     * @method hideMask
     */
    hideMask : function() {
        this._show(this.maskEl, false);
    },

    /**
     * Returns the current month set on the navigator
     * 
     * Note: This may not be the month set in the UI, if 
     * the UI contains an invalid value.
     * 
     * @method getMonth
     * @return {Number} The Navigator's current month index
     */
    getMonth: function() {
        return this._month;
    },

    /**
     * Returns the current year set on the navigator
     * 
     * Note: This may not be the year set in the UI, if 
     * the UI contains an invalid value.
     * 
     * @method getYear
     * @return {Number} The Navigator's current year value
     */
    getYear: function() {
        return this._year;
    },

    /**
     * Sets the current month on the Navigator, and updates the UI
     * 
     * @method setMonth
     * @param {Number} nMonth The month index, from 0 (Jan) through 11 (Dec).
     */
    setMonth : function(nMonth) {
        if (nMonth >= 0 && nMonth < 12) {
            this._month = nMonth;
        }
        this._updateMonthUI();
    },

    /**
     * Sets the current year on the Navigator, and updates the UI. If the 
     * provided year is invalid, it will not be set.
     * 
     * @method setYear
     * @param {Number} nYear The full year value to set the Navigator to.
     */
    setYear : function(nYear) {
        var yrPattern = YAHOO.widget.CalendarNavigator.YR_PATTERN;
        if (YAHOO.lang.isNumber(nYear) && yrPattern.test(nYear+"")) {
            this._year = nYear;
        }
        this._updateYearUI();
    },

    /**
     * Renders the HTML for the navigator, adding it to the 
     * document and attaches event listeners if it has not 
     * already been rendered.
     * 
     * @method render
     */
    render: function() {
        this.cal.beforeRenderNavEvent.fire();
        if (!this.__rendered) {
            this.createNav();
            this.createMask();
            this.applyListeners();
            this.__rendered = true;
        }
        this.cal.renderNavEvent.fire();
    },

    /**
     * Creates the navigator's containing HTMLElement, it's contents, and appends 
     * the containg element to the Calendar/CalendarGroup's container.
     * 
     * @method createNav
     */
    createNav : function() {
        var NAV = YAHOO.widget.CalendarNavigator;
        var doc = this._doc;

        var d = doc.createElement("div");
        d.className = NAV.CLASSES.NAV;

        var htmlBuf = this.renderNavContents([]);

        d.innerHTML = htmlBuf.join('');
        this.cal.oDomContainer.appendChild(d);

        this.navEl = d;

        this.yearEl = doc.getElementById(this.id + NAV.YEAR_SUFFIX);
        this.monthEl = doc.getElementById(this.id + NAV.MONTH_SUFFIX);
        this.errorEl = doc.getElementById(this.id + NAV.ERROR_SUFFIX);
        this.submitEl = doc.getElementById(this.id + NAV.SUBMIT_SUFFIX);
        this.cancelEl = doc.getElementById(this.id + NAV.CANCEL_SUFFIX);

        if (YAHOO.env.ua.gecko && this.yearEl && this.yearEl.type == "text") {
            // Avoid XUL error on focus, select [ https://bugzilla.mozilla.org/show_bug.cgi?id=236791, 
            // supposedly fixed in 1.8.1, but there are reports of it still being around for methods other than blur ]
            this.yearEl.setAttribute("autocomplete", "off");
        }

        this._setFirstLastElements();
    },

    /**
     * Creates the Mask HTMLElement and appends it to the Calendar/CalendarGroups
     * container.
     * 
     * @method createMask
     */
    createMask : function() {
        var C = YAHOO.widget.CalendarNavigator.CLASSES;

        var d = this._doc.createElement("div");
        d.className = C.MASK;

        this.cal.oDomContainer.appendChild(d);
        this.maskEl = d;
    },

    /**
     * Used to set the width/height of the mask in pixels to match the Calendar Container.
     * Currently only used for IE6 or IE in quirks mode. The other A-Grade browser are handled using CSS (width/height 100%).
     * <p>
     * The method is also registered as an HTMLElement resize listener on the Calendars container element.
     * </p>
     * @protected
     * @method _syncMask
     */
    _syncMask : function() {
        var c = this.cal.oDomContainer;
        if (c && this.maskEl) {
            var r = YAHOO.util.Dom.getRegion(c);
            YAHOO.util.Dom.setStyle(this.maskEl, "width", r.right - r.left + "px");
            YAHOO.util.Dom.setStyle(this.maskEl, "height", r.bottom - r.top + "px");
        }
    },

    /**
     * Renders the contents of the navigator. NOTE: The contents of the array passed into this method are added to the DOM as HTML, and should be escaped by the implementor if coming from an external source.
     * 
     * @method renderNavContents
     * 
     * @param {HTML[]} html The HTML buffer to append the HTML to.
     * @return {HTML[]} A reference to the buffer passed in.
     */
    renderNavContents : function(html) {
        var NAV = YAHOO.widget.CalendarNavigator,
            C = NAV.CLASSES,
            h = html; // just to use a shorter name

        h[h.length] = '<div class="' + C.MONTH + '">';
        this.renderMonth(h);
        h[h.length] = '</div>';
        h[h.length] = '<div class="' + C.YEAR + '">';
        this.renderYear(h);
        h[h.length] = '</div>';
        h[h.length] = '<div class="' + C.BUTTONS + '">';
        this.renderButtons(h);
        h[h.length] = '</div>';
        h[h.length] = '<div class="' + C.ERROR + '" id="' + this.id + NAV.ERROR_SUFFIX + '"></div>';

        return h;
    },

    /**
     * Renders the month label and control for the navigator. NOTE: The contents of the array passed into this method are added to the DOM as HTML, and should be escaped by the implementor if coming from an external source.
     * 
     * @method renderNavContents
     * @param {HTML[]} html The HTML buffer to append the HTML to.
     * @return {HTML[]} A reference to the buffer passed in.
     */
    renderMonth : function(html) {
        var NAV = YAHOO.widget.CalendarNavigator,
            C = NAV.CLASSES;

        var id = this.id + NAV.MONTH_SUFFIX,
            mf = this.__getCfg("monthFormat"),
            months = this.cal.cfg.getProperty((mf == YAHOO.widget.Calendar.SHORT) ? "MONTHS_SHORT" : "MONTHS_LONG"),
            h = html;

        if (months && months.length > 0) {
            h[h.length] = '<label for="' + id + '">';
            h[h.length] = this.__getCfg("month", true);
            h[h.length] = '</label>';
            h[h.length] = '<select name="' + id + '" id="' + id + '" class="' + C.MONTH_CTRL + '">';
            for (var i = 0; i < months.length; i++) {
                h[h.length] = '<option value="' + i + '">';
                h[h.length] = months[i];
                h[h.length] = '</option>';
            }
            h[h.length] = '</select>';
        }
        return h;
    },

    /**
     * Renders the year label and control for the navigator. NOTE: The contents of the array passed into this method are added to the DOM as HTML, and should be escaped by the implementor if coming from an external source. 
     * 
     * @method renderYear
     * @param {Array} html The HTML buffer to append the HTML to.
     * @return {Array} A reference to the buffer passed in.
     */
    renderYear : function(html) {
        var NAV = YAHOO.widget.CalendarNavigator,
            C = NAV.CLASSES;

        var id = this.id + NAV.YEAR_SUFFIX,
            size = NAV.YR_MAX_DIGITS,
            h = html;

        h[h.length] = '<label for="' + id + '">';
        h[h.length] = this.__getCfg("year", true);
        h[h.length] = '</label>';
        h[h.length] = '<input type="text" name="' + id + '" id="' + id + '" class="' + C.YEAR_CTRL + '" maxlength="' + size + '"/>';
        return h;
    },

    /**
     * Renders the submit/cancel buttons for the navigator. NOTE: The contents of the array passed into this method are added to the DOM as HTML, and should be escaped by the implementor if coming from an external source.
     * 
     * @method renderButtons
     * @param {Array} html The HTML buffer to append the HTML to.
     * @return {Array} A reference to the buffer passed in.
     */
    renderButtons : function(html) {
        var C = YAHOO.widget.CalendarNavigator.CLASSES;
        var h = html;

        h[h.length] = '<span class="' + C.BUTTON + ' ' + C.DEFAULT + '">';
        h[h.length] = '<button type="button" id="' + this.id + '_submit' + '">';
        h[h.length] = this.__getCfg("submit", true);
        h[h.length] = '</button>';
        h[h.length] = '</span>';
        h[h.length] = '<span class="' + C.BUTTON +'">';
        h[h.length] = '<button type="button" id="' + this.id + '_cancel' + '">';
        h[h.length] = this.__getCfg("cancel", true);
        h[h.length] = '</button>';
        h[h.length] = '</span>';

        return h;
    },

    /**
     * Attaches DOM event listeners to the rendered elements
     * <p>
     * The method will call applyKeyListeners, to setup keyboard specific 
     * listeners
     * </p>
     * @method applyListeners
     */
    applyListeners : function() {
        var E = YAHOO.util.Event;

        function yearUpdateHandler() {
            if (this.validate()) {
                this.setYear(this._getYearFromUI());
            }
        }

        function monthUpdateHandler() {
            this.setMonth(this._getMonthFromUI());
        }

        E.on(this.submitEl, "click", this.submit, this, true);
        E.on(this.cancelEl, "click", this.cancel, this, true);
        E.on(this.yearEl, "blur", yearUpdateHandler, this, true);
        E.on(this.monthEl, "change", monthUpdateHandler, this, true);

        if (this.__isIEQuirks) {
            YAHOO.util.Event.on(this.cal.oDomContainer, "resize", this._syncMask, this, true);
        }

        this.applyKeyListeners();
    },

    /**
     * Removes/purges DOM event listeners from the rendered elements
     * 
     * @method purgeListeners
     */
    purgeListeners : function() {
        var E = YAHOO.util.Event;
        E.removeListener(this.submitEl, "click", this.submit);
        E.removeListener(this.cancelEl, "click", this.cancel);
        E.removeListener(this.yearEl, "blur");
        E.removeListener(this.monthEl, "change");
        if (this.__isIEQuirks) {
            E.removeListener(this.cal.oDomContainer, "resize", this._syncMask);
        }

        this.purgeKeyListeners();
    },

    /**
     * Attaches DOM listeners for keyboard support. 
     * Tab/Shift-Tab looping, Enter Key Submit on Year element,
     * Up/Down/PgUp/PgDown year increment on Year element
     * <p>
     * NOTE: MacOSX Safari 2.x doesn't let you tab to buttons and 
     * MacOSX Gecko does not let you tab to buttons or select controls,
     * so for these browsers, Tab/Shift-Tab looping is limited to the 
     * elements which can be reached using the tab key.
     * </p>
     * @method applyKeyListeners
     */
    applyKeyListeners : function() {
        var E = YAHOO.util.Event,
            ua = YAHOO.env.ua;

        // IE/Safari 3.1 doesn't fire keypress for arrow/pg keys (non-char keys)
        var arrowEvt = (ua.ie || ua.webkit) ? "keydown" : "keypress";

        // - IE/Safari 3.1 doesn't fire keypress for non-char keys
        // - Opera doesn't allow us to cancel keydown or keypress for tab, but 
        //   changes focus successfully on keydown (keypress is too late to change focus - opera's already moved on).
        var tabEvt = (ua.ie || ua.opera || ua.webkit) ? "keydown" : "keypress";

        // Everyone likes keypress for Enter (char keys) - whoo hoo!
        E.on(this.yearEl, "keypress", this._handleEnterKey, this, true);

        E.on(this.yearEl, arrowEvt, this._handleDirectionKeys, this, true);
        E.on(this.lastCtrl, tabEvt, this._handleTabKey, this, true);
        E.on(this.firstCtrl, tabEvt, this._handleShiftTabKey, this, true);
    },

    /**
     * Removes/purges DOM listeners for keyboard support
     *
     * @method purgeKeyListeners
     */
    purgeKeyListeners : function() {
        var E = YAHOO.util.Event,
            ua = YAHOO.env.ua;

        var arrowEvt = (ua.ie || ua.webkit) ? "keydown" : "keypress";
        var tabEvt = (ua.ie || ua.opera || ua.webkit) ? "keydown" : "keypress";

        E.removeListener(this.yearEl, "keypress", this._handleEnterKey);
        E.removeListener(this.yearEl, arrowEvt, this._handleDirectionKeys);
        E.removeListener(this.lastCtrl, tabEvt, this._handleTabKey);
        E.removeListener(this.firstCtrl, tabEvt, this._handleShiftTabKey);
    },

    /**
     * Updates the Calendar/CalendarGroup's pagedate with the currently set month and year if valid.
     * <p>
     * If the currently set month/year is invalid, a validation error will be displayed and the 
     * Calendar/CalendarGroup's pagedate will not be updated.
     * </p>
     * @method submit
     */
    submit : function() {
        if (this.validate()) {
            this.hide();

            this.setMonth(this._getMonthFromUI());
            this.setYear(this._getYearFromUI());

            var cal = this.cal;

            // Artificial delay, just to help the user see something changed
            var delay = YAHOO.widget.CalendarNavigator.UPDATE_DELAY;
            if (delay > 0) {
                var nav = this;
                window.setTimeout(function(){ nav._update(cal); }, delay);
            } else {
                this._update(cal);
            }
        }
    },

    /**
     * Updates the Calendar rendered state, based on the state of the CalendarNavigator
     * @method _update
     * @param cal The Calendar instance to update
     * @protected
     */
    _update : function(cal) {
        var date = YAHOO.widget.DateMath.getDate(this.getYear() - cal.cfg.getProperty("YEAR_OFFSET"), this.getMonth(), 1);
        cal.cfg.setProperty("pagedate", date);
        cal.render();
    },

    /**
     * Hides the navigator and mask, without updating the Calendar/CalendarGroup's state
     * 
     * @method cancel
     */
    cancel : function() {
        this.hide();
    },

    /**
     * Validates the current state of the UI controls
     * 
     * @method validate
     * @return {Boolean} true, if the current UI state contains valid values, false if not
     */
    validate : function() {
        if (this._getYearFromUI() !== null) {
            this.clearErrors();
            return true;
        } else {
            this.setYearError();
            this.setError(this.__getCfg("invalidYear", true));
            return false;
        }
    },

    /**
     * Displays an error message in the Navigator's error panel.
     * 
     * @method setError
     * @param {HTML} msg The markup for the error message to display. NOTE: The msg passed into this method is added to the DOM as HTML, and should be escaped by the implementor if coming from an external source. 
     */
    setError : function(msg) {
        if (this.errorEl) {
            this.errorEl.innerHTML = msg;
            this._show(this.errorEl, true);
        }
    },

    /**
     * Clears the navigator's error message and hides the error panel
     * @method clearError 
     */
    clearError : function() {
        if (this.errorEl) {
            this.errorEl.innerHTML = "";
            this._show(this.errorEl, false);
        }
    },

    /**
     * Displays the validation error UI for the year control
     * @method setYearError
     */
    setYearError : function() {
        YAHOO.util.Dom.addClass(this.yearEl, YAHOO.widget.CalendarNavigator.CLASSES.INVALID);
    },

    /**
     * Removes the validation error UI for the year control
     * @method clearYearError
     */
    clearYearError : function() {
        YAHOO.util.Dom.removeClass(this.yearEl, YAHOO.widget.CalendarNavigator.CLASSES.INVALID);
    },

    /**
     * Clears all validation and error messages in the UI
     * @method clearErrors
     */
    clearErrors : function() {
        this.clearError();
        this.clearYearError();
    },

    /**
     * Sets the initial focus, based on the configured value
     * @method setInitialFocus
     */
    setInitialFocus : function() {
        var el = this.submitEl,
            f = this.__getCfg("initialFocus");

        if (f && f.toLowerCase) {
            f = f.toLowerCase();
            if (f == "year") {
                el = this.yearEl;
                try {
                    this.yearEl.select();
                } catch (selErr) {
                    // Ignore;
                }
            } else if (f == "month") {
                el = this.monthEl;
            }
        }

        if (el && YAHOO.lang.isFunction(el.focus)) {
            try {
                el.focus();
            } catch (focusErr) {
                // TODO: Fall back if focus fails?
            }
        }
    },

    /**
     * Removes all renderered HTML elements for the Navigator from
     * the DOM, purges event listeners and clears (nulls) any property
     * references to HTML references
     * @method erase
     */
    erase : function() {
        if (this.__rendered) {
            this.purgeListeners();

            // Clear out innerHTML references
            this.yearEl = null;
            this.monthEl = null;
            this.errorEl = null;
            this.submitEl = null;
            this.cancelEl = null;
            this.firstCtrl = null;
            this.lastCtrl = null;
            if (this.navEl) {
                this.navEl.innerHTML = "";
            }

            var p = this.navEl.parentNode;
            if (p) {
                p.removeChild(this.navEl);
            }
            this.navEl = null;

            var pm = this.maskEl.parentNode;
            if (pm) {
                pm.removeChild(this.maskEl);
            }
            this.maskEl = null;
            this.__rendered = false;
        }
    },

    /**
     * Destroys the Navigator object and any HTML references
     * @method destroy
     */
    destroy : function() {
        this.erase();
        this._doc = null;
        this.cal = null;
        this.id = null;
    },

    /**
     * Protected implementation to handle how UI elements are 
     * hidden/shown.
     *
     * @method _show
     * @protected
     */
    _show : function(el, bShow) {
        if (el) {
            YAHOO.util.Dom.setStyle(el, "display", (bShow) ? "block" : "none");
        }
    },

    /**
     * Returns the month value (index), from the month UI element
     * @protected
     * @method _getMonthFromUI
     * @return {Number} The month index, or 0 if a UI element for the month
     * is not found
     */
    _getMonthFromUI : function() {
        if (this.monthEl) {
            return this.monthEl.selectedIndex;
        } else {
            return 0; // Default to Jan
        }
    },

    /**
     * Returns the year value, from the Navitator's year UI element
     * @protected
     * @method _getYearFromUI
     * @return {Number} The year value set in the UI, if valid. null is returned if 
     * the UI does not contain a valid year value.
     */
    _getYearFromUI : function() {
        var NAV = YAHOO.widget.CalendarNavigator;

        var yr = null;
        if (this.yearEl) {
            var value = this.yearEl.value;
            value = value.replace(NAV.TRIM, "$1");

            if (NAV.YR_PATTERN.test(value)) {
                yr = parseInt(value, 10);
            }
        }
        return yr;
    },

    /**
     * Updates the Navigator's year UI, based on the year value set on the Navigator object
     * @protected
     * @method _updateYearUI
     */
    _updateYearUI : function() {
        if (this.yearEl && this._year !== null) {
            this.yearEl.value = this._year;
        }
    },

    /**
     * Updates the Navigator's month UI, based on the month value set on the Navigator object
     * @protected
     * @method _updateMonthUI
     */
    _updateMonthUI : function() {
        if (this.monthEl) {
            this.monthEl.selectedIndex = this._month;
        }
    },

    /**
     * Sets up references to the first and last focusable element in the Navigator's UI
     * in terms of tab order (Naviagator's firstEl and lastEl properties). The references
     * are used to control modality by looping around from the first to the last control
     * and visa versa for tab/shift-tab navigation.
     * <p>
     * See <a href="#applyKeyListeners">applyKeyListeners</a>
     * </p>
     * @protected
     * @method _setFirstLastElements
     */
    _setFirstLastElements : function() {
        this.firstCtrl = this.monthEl;
        this.lastCtrl = this.cancelEl;

        // Special handling for MacOSX.
        // - Safari 2.x can't focus on buttons
        // - Gecko can't focus on select boxes or buttons
        if (this.__isMac) {
            if (YAHOO.env.ua.webkit && YAHOO.env.ua.webkit < 420){
                this.firstCtrl = this.monthEl;
                this.lastCtrl = this.yearEl;
            }
            if (YAHOO.env.ua.gecko) {
                this.firstCtrl = this.yearEl;
                this.lastCtrl = this.yearEl;
            }
        }
    },

    /**
     * Default Keyboard event handler to capture Enter 
     * on the Navigator's year control (yearEl)
     * 
     * @method _handleEnterKey
     * @protected
     * @param {Event} e The DOM event being handled
     */
    _handleEnterKey : function(e) {
        var KEYS = YAHOO.util.KeyListener.KEY;

        if (YAHOO.util.Event.getCharCode(e) == KEYS.ENTER) {
            YAHOO.util.Event.preventDefault(e);
            this.submit();
        }
    },

    /**
     * Default Keyboard event handler to capture up/down/pgup/pgdown
     * on the Navigator's year control (yearEl).
     * 
     * @method _handleDirectionKeys
     * @protected
     * @param {Event} e The DOM event being handled
     */
    _handleDirectionKeys : function(e) {
        var E = YAHOO.util.Event,
            KEYS = YAHOO.util.KeyListener.KEY,
            NAV = YAHOO.widget.CalendarNavigator;

        var value = (this.yearEl.value) ? parseInt(this.yearEl.value, 10) : null;
        if (isFinite(value)) {
            var dir = false;
            switch(E.getCharCode(e)) {
                case KEYS.UP:
                    this.yearEl.value = value + NAV.YR_MINOR_INC;
                    dir = true;
                    break;
                case KEYS.DOWN:
                    this.yearEl.value = Math.max(value - NAV.YR_MINOR_INC, 0);
                    dir = true;
                    break;
                case KEYS.PAGE_UP:
                    this.yearEl.value = value + NAV.YR_MAJOR_INC;
                    dir = true;
                    break;
                case KEYS.PAGE_DOWN:
                    this.yearEl.value = Math.max(value - NAV.YR_MAJOR_INC, 0);
                    dir = true;
                    break;
                default:
                    break;
            }
            if (dir) {
                E.preventDefault(e);
                try {
                    this.yearEl.select();
                } catch(err) {
                    // Ignore
                }
            }
        }
    },

    /**
     * Default Keyboard event handler to capture Tab 
     * on the last control (lastCtrl) in the Navigator.
     * 
     * @method _handleTabKey
     * @protected
     * @param {Event} e The DOM event being handled
     */
    _handleTabKey : function(e) {
        var E = YAHOO.util.Event,
            KEYS = YAHOO.util.KeyListener.KEY;

        if (E.getCharCode(e) == KEYS.TAB && !e.shiftKey) {
            try {
                E.preventDefault(e);
                this.firstCtrl.focus();
            } catch (err) {
                // Ignore - mainly for focus edge cases
            }
        }
    },

    /**
     * Default Keyboard event handler to capture Shift-Tab 
     * on the first control (firstCtrl) in the Navigator.
     * 
     * @method _handleShiftTabKey
     * @protected
     * @param {Event} e The DOM event being handled
     */
    _handleShiftTabKey : function(e) {
        var E = YAHOO.util.Event,
            KEYS = YAHOO.util.KeyListener.KEY;

        if (e.shiftKey && E.getCharCode(e) == KEYS.TAB) {
            try {
                E.preventDefault(e);
                this.lastCtrl.focus();
            } catch (err) {
                // Ignore - mainly for focus edge cases
            }
        }
    },

    /**
     * Retrieve Navigator configuration values from 
     * the parent Calendar/CalendarGroup's config value.
     * <p>
     * If it has not been set in the user provided configuration, the method will 
     * return the default value of the configuration property, as set in DEFAULT_CONFIG
     * </p>
     * @private
     * @method __getCfg
     * @param {String} Case sensitive property name.
     * @param {Boolean} true, if the property is a string property, false if not.
     * @return The value of the configuration property
     */
    __getCfg : function(prop, bIsStr) {
        var DEF_CFG = YAHOO.widget.CalendarNavigator.DEFAULT_CONFIG;
        var cfg = this.cal.cfg.getProperty("navigator");

        if (bIsStr) {
            return (cfg !== true && cfg.strings && cfg.strings[prop]) ? cfg.strings[prop] : DEF_CFG.strings[prop];
        } else {
            return (cfg !== true && cfg[prop]) ? cfg[prop] : DEF_CFG[prop];
        }
    },

    /**
     * Private flag, to identify MacOS
     * @private
     * @property __isMac
     */
    __isMac : (navigator.userAgent.toLowerCase().indexOf("macintosh") != -1)

};