(function () {

    /**
    * Overlay is a Module that is absolutely positioned above the page flow. It 
    * has convenience methods for positioning and sizing, as well as options for 
    * controlling zIndex and constraining the Overlay's position to the current 
    * visible viewport. Overlay also contains a dynamicly generated IFRAME which 
    * is placed beneath it for Internet Explorer 6 and 5.x so that it will be 
    * properly rendered above SELECT elements.
    * @namespace YAHOO.widget
    * @class Overlay
    * @extends YAHOO.widget.Module
    * @param {String} el The element ID representing the Overlay <em>OR</em>
    * @param {HTMLElement} el The element representing the Overlay
    * @param {Object} userConfig The configuration object literal containing 
    * the configuration that should be set for this Overlay. See configuration 
    * documentation for more details.
    * @constructor
    */
    YAHOO.widget.Overlay = function (el, userConfig) {
        YAHOO.widget.Overlay.superclass.constructor.call(this, el, userConfig);
    };

    var Lang = YAHOO.lang,
        CustomEvent = YAHOO.util.CustomEvent,
        Module = YAHOO.widget.Module,
        Event = YAHOO.util.Event,
        Dom = YAHOO.util.Dom,
        Config = YAHOO.util.Config,
        UA = YAHOO.env.ua,
        Overlay = YAHOO.widget.Overlay,

        _SUBSCRIBE = "subscribe",
        _UNSUBSCRIBE = "unsubscribe",
        _CONTAINED = "contained",

        m_oIFrameTemplate,

        /**
        * Constant representing the name of the Overlay's events
        * @property EVENT_TYPES
        * @private
        * @final
        * @type Object
        */
        EVENT_TYPES = {
            "BEFORE_MOVE": "beforeMove",
            "MOVE": "move"
        },

        /**
        * Constant representing the Overlay's configuration properties
        * @property DEFAULT_CONFIG
        * @private
        * @final
        * @type Object
        */
        DEFAULT_CONFIG = {

            "X": { 
                key: "x", 
                validator: Lang.isNumber, 
                suppressEvent: true, 
                supercedes: ["iframe"]
            },

            "Y": { 
                key: "y", 
                validator: Lang.isNumber, 
                suppressEvent: true, 
                supercedes: ["iframe"]
            },

            "XY": { 
                key: "xy", 
                suppressEvent: true, 
                supercedes: ["iframe"] 
            },

            "CONTEXT": { 
                key: "context", 
                suppressEvent: true, 
                supercedes: ["iframe"] 
            },

            "FIXED_CENTER": { 
                key: "fixedcenter", 
                value: false, 
                supercedes: ["iframe", "visible"] 
            },

            "WIDTH": { 
                key: "width",
                suppressEvent: true,
                supercedes: ["context", "fixedcenter", "iframe"]
            }, 

            "HEIGHT": { 
                key: "height", 
                suppressEvent: true, 
                supercedes: ["context", "fixedcenter", "iframe"] 
            },

            "AUTO_FILL_HEIGHT" : {
                key: "autofillheight",
                supercedes: ["height"],
                value:"body"
            },

            "ZINDEX": { 
                key: "zindex", 
                value: null 
            },

            "CONSTRAIN_TO_VIEWPORT": { 
                key: "constraintoviewport", 
                value: false, 
                validator: Lang.isBoolean, 
                supercedes: ["iframe", "x", "y", "xy"]
            }, 

            "IFRAME": { 
                key: "iframe", 
                value: (UA.ie == 6 ? true : false), 
                validator: Lang.isBoolean, 
                supercedes: ["zindex"] 
            },

            "PREVENT_CONTEXT_OVERLAP": {
                key: "preventcontextoverlap",
                value: false,
                validator: Lang.isBoolean,  
                supercedes: ["constraintoviewport"]
            }

        };

    /**
    * The URL that will be placed in the iframe
    * @property YAHOO.widget.Overlay.IFRAME_SRC
    * @static
    * @final
    * @type String
    */
    Overlay.IFRAME_SRC = "javascript:false;";

    /**
    * Number representing how much the iframe shim should be offset from each 
    * side of an Overlay instance, in pixels.
    * @property YAHOO.widget.Overlay.IFRAME_SRC
    * @default 3
    * @static
    * @final
    * @type Number
    */
    Overlay.IFRAME_OFFSET = 3;

    /**
    * Number representing the minimum distance an Overlay instance should be 
    * positioned relative to the boundaries of the browser's viewport, in pixels.
    * @property YAHOO.widget.Overlay.VIEWPORT_OFFSET
    * @default 10
    * @static
    * @final
    * @type Number
    */
    Overlay.VIEWPORT_OFFSET = 10;

    /**
    * Constant representing the top left corner of an element, used for 
    * configuring the context element alignment
    * @property YAHOO.widget.Overlay.TOP_LEFT
    * @static
    * @final
    * @type String
    */
    Overlay.TOP_LEFT = "tl";

    /**
    * Constant representing the top right corner of an element, used for 
    * configuring the context element alignment
    * @property YAHOO.widget.Overlay.TOP_RIGHT
    * @static
    * @final
    * @type String
    */
    Overlay.TOP_RIGHT = "tr";

    /**
    * Constant representing the top bottom left corner of an element, used for 
    * configuring the context element alignment
    * @property YAHOO.widget.Overlay.BOTTOM_LEFT
    * @static
    * @final
    * @type String
    */
    Overlay.BOTTOM_LEFT = "bl";

    /**
    * Constant representing the bottom right corner of an element, used for 
    * configuring the context element alignment
    * @property YAHOO.widget.Overlay.BOTTOM_RIGHT
    * @static
    * @final
    * @type String
    */
    Overlay.BOTTOM_RIGHT = "br";

    Overlay.PREVENT_OVERLAP_X = {
        "tltr": true,
        "blbr": true,
        "brbl": true,
        "trtl": true
    };
            
    Overlay.PREVENT_OVERLAP_Y = {
        "trbr": true,
        "tlbl": true,
        "bltl": true,
        "brtr": true
    };

    /**
    * Constant representing the default CSS class used for an Overlay
    * @property YAHOO.widget.Overlay.CSS_OVERLAY
    * @static
    * @final
    * @type String
    */
    Overlay.CSS_OVERLAY = "yui-overlay";

    /**
    * Constant representing the default hidden CSS class used for an Overlay. This class is 
    * applied to the overlay's outer DIV whenever it's hidden.
    *
    * @property YAHOO.widget.Overlay.CSS_HIDDEN
    * @static
    * @final
    * @type String
    */
    Overlay.CSS_HIDDEN = "yui-overlay-hidden";

    /**
    * Constant representing the default CSS class used for an Overlay iframe shim.
    * 
    * @property YAHOO.widget.Overlay.CSS_IFRAME
    * @static
    * @final
    * @type String
    */
    Overlay.CSS_IFRAME = "yui-overlay-iframe";

    /**
     * Constant representing the names of the standard module elements
     * used in the overlay.
     * @property YAHOO.widget.Overlay.STD_MOD_RE
     * @static
     * @final
     * @type RegExp
     */
    Overlay.STD_MOD_RE = /^\s*?(body|footer|header)\s*?$/i;

    /**
    * A singleton CustomEvent used for reacting to the DOM event for 
    * window scroll
    * @event YAHOO.widget.Overlay.windowScrollEvent
    */
    Overlay.windowScrollEvent = new CustomEvent("windowScroll");

    /**
    * A singleton CustomEvent used for reacting to the DOM event for
    * window resize
    * @event YAHOO.widget.Overlay.windowResizeEvent
    */
    Overlay.windowResizeEvent = new CustomEvent("windowResize");

    /**
    * The DOM event handler used to fire the CustomEvent for window scroll
    * @method YAHOO.widget.Overlay.windowScrollHandler
    * @static
    * @param {DOMEvent} e The DOM scroll event
    */
    Overlay.windowScrollHandler = function (e) {
        var t = Event.getTarget(e);

        // - Webkit (Safari 2/3) and Opera 9.2x bubble scroll events from elements to window
        // - FF2/3 and IE6/7, Opera 9.5x don't bubble scroll events from elements to window
        // - IE doesn't recognize scroll registered on the document.
        //
        // Also, when document view is scrolled, IE doesn't provide a target, 
        // rest of the browsers set target to window.document, apart from opera 
        // which sets target to window.
        if (!t || t === window || t === window.document) {
            if (UA.ie) {

                if (! window.scrollEnd) {
                    window.scrollEnd = -1;
                }

                clearTimeout(window.scrollEnd);
        
                window.scrollEnd = setTimeout(function () { 
                    Overlay.windowScrollEvent.fire(); 
                }, 1);
        
            } else {
                Overlay.windowScrollEvent.fire();
            }
        }
    };

    /**
    * The DOM event handler used to fire the CustomEvent for window resize
    * @method YAHOO.widget.Overlay.windowResizeHandler
    * @static
    * @param {DOMEvent} e The DOM resize event
    */
    Overlay.windowResizeHandler = function (e) {

        if (UA.ie) {
            if (! window.resizeEnd) {
                window.resizeEnd = -1;
            }

            clearTimeout(window.resizeEnd);

            window.resizeEnd = setTimeout(function () {
                Overlay.windowResizeEvent.fire(); 
            }, 100);
        } else {
            Overlay.windowResizeEvent.fire();
        }
    };

    /**
    * A boolean that indicated whether the window resize and scroll events have 
    * already been subscribed to.
    * @property YAHOO.widget.Overlay._initialized
    * @private
    * @type Boolean
    */
    Overlay._initialized = null;

    if (Overlay._initialized === null) {
        Event.on(window, "scroll", Overlay.windowScrollHandler);
        Event.on(window, "resize", Overlay.windowResizeHandler);
        Overlay._initialized = true;
    }

    /**
     * Internal map of special event types, which are provided
     * by the instance. It maps the event type to the custom event 
     * instance. Contains entries for the "windowScroll", "windowResize" and
     * "textResize" static container events.
     *
     * @property YAHOO.widget.Overlay._TRIGGER_MAP
     * @type Object
     * @static
     * @private
     */
    Overlay._TRIGGER_MAP = {
        "windowScroll" : Overlay.windowScrollEvent,
        "windowResize" : Overlay.windowResizeEvent,
        "textResize"   : Module.textResizeEvent
    };

    YAHOO.extend(Overlay, Module, {

        /**
         * <p>
         * Array of default event types which will trigger
         * context alignment for the Overlay class.
         * </p>
         * <p>The array is empty by default for Overlay,
         * but maybe populated in future releases, so classes extending
         * Overlay which need to define their own set of CONTEXT_TRIGGERS
         * should concatenate their super class's prototype.CONTEXT_TRIGGERS 
         * value with their own array of values.
         * </p>
         * <p>
         * E.g.:
         * <code>CustomOverlay.prototype.CONTEXT_TRIGGERS = YAHOO.widget.Overlay.prototype.CONTEXT_TRIGGERS.concat(["windowScroll"]);</code>
         * </p>
         * 
         * @property CONTEXT_TRIGGERS
         * @type Array
         * @final
         */
        CONTEXT_TRIGGERS : [],

        /**
        * The Overlay initialization method, which is executed for Overlay and  
        * all of its subclasses. This method is automatically called by the 
        * constructor, and  sets up all DOM references for pre-existing markup, 
        * and creates required markup if it is not already present.
        * @method init
        * @param {String} el The element ID representing the Overlay <em>OR</em>
        * @param {HTMLElement} el The element representing the Overlay
        * @param {Object} userConfig The configuration object literal 
        * containing the configuration that should be set for this Overlay. 
        * See configuration documentation for more details.
        */
        init: function (el, userConfig) {

            /*
                 Note that we don't pass the user config in here yet because we
                 only want it executed once, at the lowest subclass level
            */

            Overlay.superclass.init.call(this, el/*, userConfig*/);

            this.beforeInitEvent.fire(Overlay);

            Dom.addClass(this.element, Overlay.CSS_OVERLAY);

            if (userConfig) {
                this.cfg.applyConfig(userConfig, true);
            }

            if (this.platform == "mac" && UA.gecko) {

                if (! Config.alreadySubscribed(this.showEvent,
                    this.showMacGeckoScrollbars, this)) {

                    this.showEvent.subscribe(this.showMacGeckoScrollbars, 
                        this, true);

                }

                if (! Config.alreadySubscribed(this.hideEvent, 
                    this.hideMacGeckoScrollbars, this)) {

                    this.hideEvent.subscribe(this.hideMacGeckoScrollbars, 
                        this, true);

                }
            }

            this.initEvent.fire(Overlay);
        },
        
        /**
        * Initializes the custom events for Overlay which are fired  
        * automatically at appropriate times by the Overlay class.
        * @method initEvents
        */
        initEvents: function () {

            Overlay.superclass.initEvents.call(this);

            var SIGNATURE = CustomEvent.LIST;

            /**
            * CustomEvent fired before the Overlay is moved.
            * @event beforeMoveEvent
            * @param {Number} x x coordinate
            * @param {Number} y y coordinate
            */
            this.beforeMoveEvent = this.createEvent(EVENT_TYPES.BEFORE_MOVE);
            this.beforeMoveEvent.signature = SIGNATURE;

            /**
            * CustomEvent fired after the Overlay is moved.
            * @event moveEvent
            * @param {Number} x x coordinate
            * @param {Number} y y coordinate
            */
            this.moveEvent = this.createEvent(EVENT_TYPES.MOVE);
            this.moveEvent.signature = SIGNATURE;

        },
        
        /**
        * Initializes the class's configurable properties which can be changed 
        * using the Overlay's Config object (cfg).
        * @method initDefaultConfig
        */
        initDefaultConfig: function () {
    
            Overlay.superclass.initDefaultConfig.call(this);

            var cfg = this.cfg;

            // Add overlay config properties //
            
            /**
            * The absolute x-coordinate position of the Overlay
            * @config x
            * @type Number
            * @default null
            */
            cfg.addProperty(DEFAULT_CONFIG.X.key, { 
    
                handler: this.configX, 
                validator: DEFAULT_CONFIG.X.validator, 
                suppressEvent: DEFAULT_CONFIG.X.suppressEvent, 
                supercedes: DEFAULT_CONFIG.X.supercedes
    
            });

            /**
            * The absolute y-coordinate position of the Overlay
            * @config y
            * @type Number
            * @default null
            */
            cfg.addProperty(DEFAULT_CONFIG.Y.key, {

                handler: this.configY, 
                validator: DEFAULT_CONFIG.Y.validator, 
                suppressEvent: DEFAULT_CONFIG.Y.suppressEvent, 
                supercedes: DEFAULT_CONFIG.Y.supercedes

            });

            /**
            * An array with the absolute x and y positions of the Overlay
            * @config xy
            * @type Number[]
            * @default null
            */
            cfg.addProperty(DEFAULT_CONFIG.XY.key, {
                handler: this.configXY, 
                suppressEvent: DEFAULT_CONFIG.XY.suppressEvent, 
                supercedes: DEFAULT_CONFIG.XY.supercedes
            });

            /**
            * <p>
            * The array of context arguments for context-sensitive positioning. 
            * </p>
            *
            * <p>
            * The format of the array is: <code>[contextElementOrId, overlayCorner, contextCorner, arrayOfTriggerEvents (optional), xyOffset (optional)]</code>, the
            * the 5 array elements described in detail below:
            * </p>
            *
            * <dl>
            * <dt>contextElementOrId &#60;String|HTMLElement&#62;</dt>
            * <dd>A reference to the context element to which the overlay should be aligned (or it's id).</dd>
            * <dt>overlayCorner &#60;String&#62;</dt>
            * <dd>The corner of the overlay which is to be used for alignment. This corner will be aligned to the 
            * corner of the context element defined by the "contextCorner" entry which follows. Supported string values are: 
            * "tr" (top right), "tl" (top left), "br" (bottom right), or "bl" (bottom left).</dd>
            * <dt>contextCorner &#60;String&#62;</dt>
            * <dd>The corner of the context element which is to be used for alignment. Supported string values are the same ones listed for the "overlayCorner" entry above.</dd>
            * <dt>arrayOfTriggerEvents (optional) &#60;Array[String|CustomEvent]&#62;</dt>
            * <dd>
            * <p>
            * By default, context alignment is a one time operation, aligning the Overlay to the context element when context configuration property is set, or when the <a href="#method_align">align</a> 
            * method is invoked. However, you can use the optional "arrayOfTriggerEvents" entry to define the list of events which should force the overlay to re-align itself with the context element. 
            * This is useful in situations where the layout of the document may change, resulting in the context element's position being modified.
            * </p>
            * <p>
            * The array can contain either event type strings for events the instance publishes (e.g. "beforeShow") or CustomEvent instances. Additionally the following
            * 3 static container event types are also currently supported : <code>"windowResize", "windowScroll", "textResize"</code> (defined in <a href="#property__TRIGGER_MAP">_TRIGGER_MAP</a> private property).
            * </p>
            * </dd>
            * <dt>xyOffset &#60;Number[]&#62;</dt>
            * <dd>
            * A 2 element Array specifying the X and Y pixel amounts by which the Overlay should be offset from the aligned corner. e.g. [5,0] offsets the Overlay 5 pixels to the left, <em>after</em> aligning the given context corners.
            * NOTE: If using this property and no triggers need to be defined, the arrayOfTriggerEvents property should be set to null to maintain correct array positions for the arguments. 
            * </dd>
            * </dl>
            *
            * <p>
            * For example, setting this property to <code>["img1", "tl", "bl"]</code> will 
            * align the Overlay's top left corner to the bottom left corner of the
            * context element with id "img1".
            * </p>
            * <p>
            * Setting this property to <code>["img1", "tl", "bl", null, [0,5]</code> will 
            * align the Overlay's top left corner to the bottom left corner of the
            * context element with id "img1", and then offset it by 5 pixels on the Y axis (providing a 5 pixel gap between the bottom of the context element and top of the overlay).
            * </p>
            * <p>
            * Adding the optional trigger values: <code>["img1", "tl", "bl", ["beforeShow", "windowResize"], [0,5]]</code>,
            * will re-align the overlay position, whenever the "beforeShow" or "windowResize" events are fired.
            * </p>
            *
            * @config context
            * @type Array
            * @default null
            */
            cfg.addProperty(DEFAULT_CONFIG.CONTEXT.key, {
                handler: this.configContext, 
                suppressEvent: DEFAULT_CONFIG.CONTEXT.suppressEvent, 
                supercedes: DEFAULT_CONFIG.CONTEXT.supercedes
            });

            /**
            * Determines whether or not the Overlay should be anchored 
            * to the center of the viewport.
            * 
            * <p>This property can be set to:</p>
            * 
            * <dl>
            * <dt>true</dt>
            * <dd>
            * To enable fixed center positioning
            * <p>
            * When enabled, the overlay will 
            * be positioned in the center of viewport when initially displayed, and 
            * will remain in the center of the viewport whenever the window is 
            * scrolled or resized.
            * </p>
            * <p>
            * If the overlay is too big for the viewport, 
            * it's top left corner will be aligned with the top left corner of the viewport.
            * </p>
            * </dd>
            * <dt>false</dt>
            * <dd>
            * To disable fixed center positioning.
            * <p>In this case the overlay can still be 
            * centered as a one-off operation, by invoking the <code>center()</code> method,
            * however it will not remain centered when the window is scrolled/resized.
            * </dd>
            * <dt>"contained"<dt>
            * <dd>To enable fixed center positioning, as with the <code>true</code> option.
            * <p>However, unlike setting the property to <code>true</code>, 
            * when the property is set to <code>"contained"</code>, if the overlay is 
            * too big for the viewport, it will not get automatically centered when the 
            * user scrolls or resizes the window (until the window is large enough to contain the 
            * overlay). This is useful in cases where the Overlay has both header and footer 
            * UI controls which the user may need to access.
            * </p>
            * </dd>
            * </dl>
            *
            * @config fixedcenter
            * @type Boolean | String
            * @default false
            */
            cfg.addProperty(DEFAULT_CONFIG.FIXED_CENTER.key, {
                handler: this.configFixedCenter,
                value: DEFAULT_CONFIG.FIXED_CENTER.value, 
                validator: DEFAULT_CONFIG.FIXED_CENTER.validator, 
                supercedes: DEFAULT_CONFIG.FIXED_CENTER.supercedes
            });
    
            /**
            * CSS width of the Overlay.
            * @config width
            * @type String
            * @default null
            */
            cfg.addProperty(DEFAULT_CONFIG.WIDTH.key, {
                handler: this.configWidth, 
                suppressEvent: DEFAULT_CONFIG.WIDTH.suppressEvent, 
                supercedes: DEFAULT_CONFIG.WIDTH.supercedes
            });

            /**
            * CSS height of the Overlay.
            * @config height
            * @type String
            * @default null
            */
            cfg.addProperty(DEFAULT_CONFIG.HEIGHT.key, {
                handler: this.configHeight, 
                suppressEvent: DEFAULT_CONFIG.HEIGHT.suppressEvent, 
                supercedes: DEFAULT_CONFIG.HEIGHT.supercedes
            });

            /**
            * Standard module element which should auto fill out the height of the Overlay if the height config property is set.
            * Supported values are "header", "body", "footer".
            *
            * @config autofillheight
            * @type String
            * @default null
            */
            cfg.addProperty(DEFAULT_CONFIG.AUTO_FILL_HEIGHT.key, {
                handler: this.configAutoFillHeight, 
                value : DEFAULT_CONFIG.AUTO_FILL_HEIGHT.value,
                validator : this._validateAutoFill,
                supercedes: DEFAULT_CONFIG.AUTO_FILL_HEIGHT.supercedes
            });

            /**
            * CSS z-index of the Overlay.
            * @config zIndex
            * @type Number
            * @default null
            */
            cfg.addProperty(DEFAULT_CONFIG.ZINDEX.key, {
                handler: this.configzIndex,
                value: DEFAULT_CONFIG.ZINDEX.value
            });

            /**
            * True if the Overlay should be prevented from being positioned 
            * out of the viewport.
            * @config constraintoviewport
            * @type Boolean
            * @default false
            */
            cfg.addProperty(DEFAULT_CONFIG.CONSTRAIN_TO_VIEWPORT.key, {

                handler: this.configConstrainToViewport, 
                value: DEFAULT_CONFIG.CONSTRAIN_TO_VIEWPORT.value, 
                validator: DEFAULT_CONFIG.CONSTRAIN_TO_VIEWPORT.validator, 
                supercedes: DEFAULT_CONFIG.CONSTRAIN_TO_VIEWPORT.supercedes

            });

            /**
            * @config iframe
            * @description Boolean indicating whether or not the Overlay should 
            * have an IFRAME shim; used to prevent SELECT elements from 
            * poking through an Overlay instance in IE6.  When set to "true", 
            * the iframe shim is created when the Overlay instance is intially
            * made visible.
            * @type Boolean
            * @default true for IE6 and below, false for all other browsers.
            */
            cfg.addProperty(DEFAULT_CONFIG.IFRAME.key, {

                handler: this.configIframe, 
                value: DEFAULT_CONFIG.IFRAME.value, 
                validator: DEFAULT_CONFIG.IFRAME.validator, 
                supercedes: DEFAULT_CONFIG.IFRAME.supercedes

            });

            /**
            * @config preventcontextoverlap
            * @description Boolean indicating whether or not the Overlay should overlap its 
            * context element (defined using the "context" configuration property) when the 
            * "constraintoviewport" configuration property is set to "true".
            * @type Boolean
            * @default false
            */
            cfg.addProperty(DEFAULT_CONFIG.PREVENT_CONTEXT_OVERLAP.key, {
                value: DEFAULT_CONFIG.PREVENT_CONTEXT_OVERLAP.value, 
                validator: DEFAULT_CONFIG.PREVENT_CONTEXT_OVERLAP.validator, 
                supercedes: DEFAULT_CONFIG.PREVENT_CONTEXT_OVERLAP.supercedes
            });
        },

        /**
        * Moves the Overlay to the specified position. This function is  
        * identical to calling this.cfg.setProperty("xy", [x,y]);
        * @method moveTo
        * @param {Number} x The Overlay's new x position
        * @param {Number} y The Overlay's new y position
        */
        moveTo: function (x, y) {
            this.cfg.setProperty("xy", [x, y]);
        },

        /**
        * Adds a CSS class ("hide-scrollbars") and removes a CSS class 
        * ("show-scrollbars") to the Overlay to fix a bug in Gecko on Mac OS X 
        * (https://bugzilla.mozilla.org/show_bug.cgi?id=187435)
        * @method hideMacGeckoScrollbars
        */
        hideMacGeckoScrollbars: function () {
            Dom.replaceClass(this.element, "show-scrollbars", "hide-scrollbars");
        },

        /**
        * Adds a CSS class ("show-scrollbars") and removes a CSS class 
        * ("hide-scrollbars") to the Overlay to fix a bug in Gecko on Mac OS X 
        * (https://bugzilla.mozilla.org/show_bug.cgi?id=187435)
        * @method showMacGeckoScrollbars
        */
        showMacGeckoScrollbars: function () {
            Dom.replaceClass(this.element, "hide-scrollbars", "show-scrollbars");
        },

        /**
         * Internal implementation to set the visibility of the overlay in the DOM.
         *
         * @method _setDomVisibility
         * @param {boolean} visible Whether to show or hide the Overlay's outer element
         * @protected
         */
        _setDomVisibility : function(show) {
            Dom.setStyle(this.element, "visibility", (show) ? "visible" : "hidden");
            var hiddenClass = Overlay.CSS_HIDDEN;

            if (show) {
                Dom.removeClass(this.element, hiddenClass);
            } else {
                Dom.addClass(this.element, hiddenClass);
            }
        },

        // BEGIN BUILT-IN PROPERTY EVENT HANDLERS //
        /**
        * The default event handler fired when the "visible" property is 
        * changed.  This method is responsible for firing showEvent
        * and hideEvent.
        * @method configVisible
        * @param {String} type The CustomEvent type (usually the property name)
        * @param {Object[]} args The CustomEvent arguments. For configuration
        * handlers, args[0] will equal the newly applied value for the property.
        * @param {Object} obj The scope object. For configuration handlers, 
        * this will usually equal the owner.
        */
        configVisible: function (type, args, obj) {

            var visible = args[0],
                currentVis = Dom.getStyle(this.element, "visibility"),
                effects = this._cachedEffects || this._createEffects(this.cfg.getProperty("effect")),
                isMacGecko = (this.platform == "mac" && UA.gecko),
                alreadySubscribed = Config.alreadySubscribed,
                ei, e, j, k, h,
                nEffectInstances;

            if (currentVis == "inherit") {
                e = this.element.parentNode;

                while (e.nodeType != 9 && e.nodeType != 11) {
                    currentVis = Dom.getStyle(e, "visibility");

                    if (currentVis != "inherit") {
                        break;
                    }

                    e = e.parentNode;
                }

                if (currentVis == "inherit") {
                    currentVis = "visible";
                }
            }

            if (visible) { // Show

                if (isMacGecko) {
                    this.showMacGeckoScrollbars();
                }

                if (effects) { // Animate in
                    if (visible) { // Animate in if not showing

                         // Fading out is a bit of a hack, but didn't want to risk doing 
                         // something broader (e.g a generic this._animatingOut) for 2.9.0

                        if (currentVis != "visible" || currentVis === "" || this._fadingOut) {
                            if (this.beforeShowEvent.fire()) {

                                nEffectInstances = effects.length;

                                for (j = 0; j < nEffectInstances; j++) {
                                    ei = effects[j];
                                    if (j === 0 && !alreadySubscribed(ei.animateInCompleteEvent, this.showEvent.fire, this.showEvent)) {
                                        ei.animateInCompleteEvent.subscribe(this.showEvent.fire, this.showEvent, true);
                                    }
                                    ei.animateIn();
                                }
                            }
                        }
                    }
                } else { // Show
                    if (currentVis != "visible" || currentVis === "") {
                        if (this.beforeShowEvent.fire()) {
                            this._setDomVisibility(true);
                            this.cfg.refireEvent("iframe");
                            this.showEvent.fire();
                        }
                    } else {
                        this._setDomVisibility(true);
                    }
                }
            } else { // Hide

                if (isMacGecko) {
                    this.hideMacGeckoScrollbars();
                }

                if (effects) { // Animate out if showing
                    if (currentVis == "visible" || this._fadingIn) {
                        if (this.beforeHideEvent.fire()) {
                            nEffectInstances = effects.length;
                            for (k = 0; k < nEffectInstances; k++) {
                                h = effects[k];
        
                                if (k === 0 && !alreadySubscribed(h.animateOutCompleteEvent, this.hideEvent.fire, this.hideEvent)) {
                                    h.animateOutCompleteEvent.subscribe(this.hideEvent.fire, this.hideEvent, true);
                                }
                                h.animateOut();
                            }
                        }

                    } else if (currentVis === "") {
                        this._setDomVisibility(false);
                    }

                } else { // Simple hide

                    if (currentVis == "visible" || currentVis === "") {
                        if (this.beforeHideEvent.fire()) {
                            this._setDomVisibility(false);
                            this.hideEvent.fire();
                        }
                    } else {
                        this._setDomVisibility(false);
                    }
                }
            }
        },

        /**
        * Fixed center event handler used for centering on scroll/resize, but only if 
        * the overlay is visible and, if "fixedcenter" is set to "contained", only if 
        * the overlay fits within the viewport.
        *
        * @method doCenterOnDOMEvent
        */
        doCenterOnDOMEvent: function () {
            var cfg = this.cfg,
                fc = cfg.getProperty("fixedcenter");

            if (cfg.getProperty("visible")) {
                if (fc && (fc !== _CONTAINED || this.fitsInViewport())) {
                    this.center();
                }
            }
        },

        /**
         * Determines if the Overlay (including the offset value defined by Overlay.VIEWPORT_OFFSET) 
         * will fit entirely inside the viewport, in both dimensions - width and height.
         * 
         * @method fitsInViewport
         * @return boolean true if the Overlay will fit, false if not
         */
        fitsInViewport : function() {
            var nViewportOffset = Overlay.VIEWPORT_OFFSET,
                element = this.element,
                elementWidth = element.offsetWidth,
                elementHeight = element.offsetHeight,
                viewportWidth = Dom.getViewportWidth(),
                viewportHeight = Dom.getViewportHeight();

            return ((elementWidth + nViewportOffset < viewportWidth) && (elementHeight + nViewportOffset < viewportHeight));
        },

        /**
        * The default event handler fired when the "fixedcenter" property 
        * is changed.
        * @method configFixedCenter
        * @param {String} type The CustomEvent type (usually the property name)
        * @param {Object[]} args The CustomEvent arguments. For configuration 
        * handlers, args[0] will equal the newly applied value for the property.
        * @param {Object} obj The scope object. For configuration handlers, 
        * this will usually equal the owner.
        */
        configFixedCenter: function (type, args, obj) {

            var val = args[0],
                alreadySubscribed = Config.alreadySubscribed,
                windowResizeEvent = Overlay.windowResizeEvent,
                windowScrollEvent = Overlay.windowScrollEvent;

            if (val) {
                this.center();

                if (!alreadySubscribed(this.beforeShowEvent, this.center)) {
                    this.beforeShowEvent.subscribe(this.center);
                }

                if (!alreadySubscribed(windowResizeEvent, this.doCenterOnDOMEvent, this)) {
                    windowResizeEvent.subscribe(this.doCenterOnDOMEvent, this, true);
                }

                if (!alreadySubscribed(windowScrollEvent, this.doCenterOnDOMEvent, this)) {
                    windowScrollEvent.subscribe(this.doCenterOnDOMEvent, this, true);
                }

            } else {
                this.beforeShowEvent.unsubscribe(this.center);

                windowResizeEvent.unsubscribe(this.doCenterOnDOMEvent, this);
                windowScrollEvent.unsubscribe(this.doCenterOnDOMEvent, this);
            }
        },

        /**
        * The default event handler fired when the "height" property is changed.
        * @method configHeight
        * @param {String} type The CustomEvent type (usually the property name)
        * @param {Object[]} args The CustomEvent arguments. For configuration 
        * handlers, args[0] will equal the newly applied value for the property.
        * @param {Object} obj The scope object. For configuration handlers, 
        * this will usually equal the owner.
        */
        configHeight: function (type, args, obj) {

            var height = args[0],
                el = this.element;

            Dom.setStyle(el, "height", height);
            this.cfg.refireEvent("iframe");
        },

        /**
         * The default event handler fired when the "autofillheight" property is changed.
         * @method configAutoFillHeight
         *
         * @param {String} type The CustomEvent type (usually the property name)
         * @param {Object[]} args The CustomEvent arguments. For configuration 
         * handlers, args[0] will equal the newly applied value for the property.
         * @param {Object} obj The scope object. For configuration handlers, 
         * this will usually equal the owner.
         */
        configAutoFillHeight: function (type, args, obj) {
            var fillEl = args[0],
                cfg = this.cfg,
                autoFillHeight = "autofillheight",
                height = "height",
                currEl = cfg.getProperty(autoFillHeight),
                autoFill = this._autoFillOnHeightChange;

            cfg.unsubscribeFromConfigEvent(height, autoFill);
            Module.textResizeEvent.unsubscribe(autoFill);
            this.changeContentEvent.unsubscribe(autoFill);

            if (currEl && fillEl !== currEl && this[currEl]) {
                Dom.setStyle(this[currEl], height, "");
            }

            if (fillEl) {
                fillEl = Lang.trim(fillEl.toLowerCase());

                cfg.subscribeToConfigEvent(height, autoFill, this[fillEl], this);
                Module.textResizeEvent.subscribe(autoFill, this[fillEl], this);
                this.changeContentEvent.subscribe(autoFill, this[fillEl], this);

                cfg.setProperty(autoFillHeight, fillEl, true);
            }
        },

        /**
        * The default event handler fired when the "width" property is changed.
        * @method configWidth
        * @param {String} type The CustomEvent type (usually the property name)
        * @param {Object[]} args The CustomEvent arguments. For configuration 
        * handlers, args[0] will equal the newly applied value for the property.
        * @param {Object} obj The scope object. For configuration handlers, 
        * this will usually equal the owner.
        */
        configWidth: function (type, args, obj) {

            var width = args[0],
                el = this.element;

            Dom.setStyle(el, "width", width);
            this.cfg.refireEvent("iframe");
        },

        /**
        * The default event handler fired when the "zIndex" property is changed.
        * @method configzIndex
        * @param {String} type The CustomEvent type (usually the property name)
        * @param {Object[]} args The CustomEvent arguments. For configuration 
        * handlers, args[0] will equal the newly applied value for the property.
        * @param {Object} obj The scope object. For configuration handlers, 
        * this will usually equal the owner.
        */
        configzIndex: function (type, args, obj) {

            var zIndex = args[0],
                el = this.element;

            if (! zIndex) {
                zIndex = Dom.getStyle(el, "zIndex");
                if (! zIndex || isNaN(zIndex)) {
                    zIndex = 0;
                }
            }

            if (this.iframe || this.cfg.getProperty("iframe") === true) {
                if (zIndex <= 0) {
                    zIndex = 1;
                }
            }

            Dom.setStyle(el, "zIndex", zIndex);
            this.cfg.setProperty("zIndex", zIndex, true);

            if (this.iframe) {
                this.stackIframe();
            }
        },

        /**
        * The default event handler fired when the "xy" property is changed.
        * @method configXY
        * @param {String} type The CustomEvent type (usually the property name)
        * @param {Object[]} args The CustomEvent arguments. For configuration 
        * handlers, args[0] will equal the newly applied value for the property.
        * @param {Object} obj The scope object. For configuration handlers, 
        * this will usually equal the owner.
        */
        configXY: function (type, args, obj) {

            var pos = args[0],
                x = pos[0],
                y = pos[1];

            this.cfg.setProperty("x", x);
            this.cfg.setProperty("y", y);

            this.beforeMoveEvent.fire([x, y]);

            x = this.cfg.getProperty("x");
            y = this.cfg.getProperty("y");

            YAHOO.log(("xy: " + [x, y]), "iframe");

            this.cfg.refireEvent("iframe");
            this.moveEvent.fire([x, y]);
        },

        /**
        * The default event handler fired when the "x" property is changed.
        * @method configX
        * @param {String} type The CustomEvent type (usually the property name)
        * @param {Object[]} args The CustomEvent arguments. For configuration 
        * handlers, args[0] will equal the newly applied value for the property.
        * @param {Object} obj The scope object. For configuration handlers, 
        * this will usually equal the owner.
        */
        configX: function (type, args, obj) {

            var x = args[0],
                y = this.cfg.getProperty("y");

            this.cfg.setProperty("x", x, true);
            this.cfg.setProperty("y", y, true);

            this.beforeMoveEvent.fire([x, y]);

            x = this.cfg.getProperty("x");
            y = this.cfg.getProperty("y");

            Dom.setX(this.element, x, true);

            this.cfg.setProperty("xy", [x, y], true);

            this.cfg.refireEvent("iframe");
            this.moveEvent.fire([x, y]);
        },

        /**
        * The default event handler fired when the "y" property is changed.
        * @method configY
        * @param {String} type The CustomEvent type (usually the property name)
        * @param {Object[]} args The CustomEvent arguments. For configuration 
        * handlers, args[0] will equal the newly applied value for the property.
        * @param {Object} obj The scope object. For configuration handlers, 
        * this will usually equal the owner.
        */
        configY: function (type, args, obj) {

            var x = this.cfg.getProperty("x"),
                y = args[0];

            this.cfg.setProperty("x", x, true);
            this.cfg.setProperty("y", y, true);

            this.beforeMoveEvent.fire([x, y]);

            x = this.cfg.getProperty("x");
            y = this.cfg.getProperty("y");

            Dom.setY(this.element, y, true);

            this.cfg.setProperty("xy", [x, y], true);

            this.cfg.refireEvent("iframe");
            this.moveEvent.fire([x, y]);
        },
        
        /**
        * Shows the iframe shim, if it has been enabled.
        * @method showIframe
        */
        showIframe: function () {

            var oIFrame = this.iframe,
                oParentNode;

            if (oIFrame) {
                oParentNode = this.element.parentNode;

                if (oParentNode != oIFrame.parentNode) {
                    this._addToParent(oParentNode, oIFrame);
                }
                oIFrame.style.display = "block";
            }
        },

        /**
        * Hides the iframe shim, if it has been enabled.
        * @method hideIframe
        */
        hideIframe: function () {
            if (this.iframe) {
                this.iframe.style.display = "none";
            }
        },

        /**
        * Syncronizes the size and position of iframe shim to that of its 
        * corresponding Overlay instance.
        * @method syncIframe
        */
        syncIframe: function () {

            var oIFrame = this.iframe,
                oElement = this.element,
                nOffset = Overlay.IFRAME_OFFSET,
                nDimensionOffset = (nOffset * 2),
                aXY;

            if (oIFrame) {
                // Size <iframe>
                oIFrame.style.width = (oElement.offsetWidth + nDimensionOffset + "px");
                oIFrame.style.height = (oElement.offsetHeight + nDimensionOffset + "px");

                // Position <iframe>
                aXY = this.cfg.getProperty("xy");

                if (!Lang.isArray(aXY) || (isNaN(aXY[0]) || isNaN(aXY[1]))) {
                    this.syncPosition();
                    aXY = this.cfg.getProperty("xy");
                }
                Dom.setXY(oIFrame, [(aXY[0] - nOffset), (aXY[1] - nOffset)]);
            }
        },

        /**
         * Sets the zindex of the iframe shim, if it exists, based on the zindex of
         * the Overlay element. The zindex of the iframe is set to be one less 
         * than the Overlay element's zindex.
         * 
         * <p>NOTE: This method will not bump up the zindex of the Overlay element
         * to ensure that the iframe shim has a non-negative zindex.
         * If you require the iframe zindex to be 0 or higher, the zindex of 
         * the Overlay element should be set to a value greater than 0, before 
         * this method is called.
         * </p>
         * @method stackIframe
         */
        stackIframe: function () {
            if (this.iframe) {
                var overlayZ = Dom.getStyle(this.element, "zIndex");
                if (!YAHOO.lang.isUndefined(overlayZ) && !isNaN(overlayZ)) {
                    Dom.setStyle(this.iframe, "zIndex", (overlayZ - 1));
                }
            }
        },

        /**
        * The default event handler fired when the "iframe" property is changed.
        * @method configIframe
        * @param {String} type The CustomEvent type (usually the property name)
        * @param {Object[]} args The CustomEvent arguments. For configuration 
        * handlers, args[0] will equal the newly applied value for the property.
        * @param {Object} obj The scope object. For configuration handlers, 
        * this will usually equal the owner.
        */
        configIframe: function (type, args, obj) {

            var bIFrame = args[0];

            function createIFrame() {

                var oIFrame = this.iframe,
                    oElement = this.element,
                    oParent;

                if (!oIFrame) {
                    if (!m_oIFrameTemplate) {
                        m_oIFrameTemplate = document.createElement("iframe");

                        if (this.isSecure) {
                            m_oIFrameTemplate.src = Overlay.IFRAME_SRC;
                        }

                        /*
                            Set the opacity of the <iframe> to 0 so that it 
                            doesn't modify the opacity of any transparent 
                            elements that may be on top of it (like a shadow).
                        */
                        if (UA.ie) {
                            m_oIFrameTemplate.style.filter = "alpha(opacity=0)";
                            /*
                                 Need to set the "frameBorder" property to 0 
                                 supress the default <iframe> border in IE.  
                                 Setting the CSS "border" property alone 
                                 doesn't supress it.
                            */
                            m_oIFrameTemplate.frameBorder = 0;
                        }
                        else {
                            m_oIFrameTemplate.style.opacity = "0";
                        }

                        m_oIFrameTemplate.style.position = "absolute";
                        m_oIFrameTemplate.style.border = "none";
                        m_oIFrameTemplate.style.margin = "0";
                        m_oIFrameTemplate.style.padding = "0";
                        m_oIFrameTemplate.style.display = "none";
                        m_oIFrameTemplate.tabIndex = -1;
                        m_oIFrameTemplate.className = Overlay.CSS_IFRAME;
                    }

                    oIFrame = m_oIFrameTemplate.cloneNode(false);
                    oIFrame.id = this.id + "_f";
                    oParent = oElement.parentNode;

                    var parentNode = oParent || document.body;

                    this._addToParent(parentNode, oIFrame);
                    this.iframe = oIFrame;
                }

                /*
                     Show the <iframe> before positioning it since the "setXY" 
                     method of DOM requires the element be in the document 
                     and visible.
                */
                this.showIframe();

                /*
                     Syncronize the size and position of the <iframe> to that 
                     of the Overlay.
                */
                this.syncIframe();
                this.stackIframe();

                // Add event listeners to update the <iframe> when necessary
                if (!this._hasIframeEventListeners) {
                    this.showEvent.subscribe(this.showIframe);
                    this.hideEvent.subscribe(this.hideIframe);
                    this.changeContentEvent.subscribe(this.syncIframe);

                    this._hasIframeEventListeners = true;
                }
            }

            function onBeforeShow() {
                createIFrame.call(this);
                this.beforeShowEvent.unsubscribe(onBeforeShow);
                this._iframeDeferred = false;
            }

            if (bIFrame) { // <iframe> shim is enabled

                if (this.cfg.getProperty("visible")) {
                    createIFrame.call(this);
                } else {
                    if (!this._iframeDeferred) {
                        this.beforeShowEvent.subscribe(onBeforeShow);
                        this._iframeDeferred = true;
                    }
                }

            } else {    // <iframe> shim is disabled
                this.hideIframe();

                if (this._hasIframeEventListeners) {
                    this.showEvent.unsubscribe(this.showIframe);
                    this.hideEvent.unsubscribe(this.hideIframe);
                    this.changeContentEvent.unsubscribe(this.syncIframe);

                    this._hasIframeEventListeners = false;
                }
            }
        },

        /**
         * Set's the container's XY value from DOM if not already set.
         * 
         * Differs from syncPosition, in that the XY value is only sync'd with DOM if 
         * not already set. The method also refire's the XY config property event, so any
         * beforeMove, Move event listeners are invoked.
         * 
         * @method _primeXYFromDOM
         * @protected
         */
        _primeXYFromDOM : function() {
            if (YAHOO.lang.isUndefined(this.cfg.getProperty("xy"))) {
                // Set CFG XY based on DOM XY
                this.syncPosition();
                // Account for XY being set silently in syncPosition (no moveTo fired/called)
                this.cfg.refireEvent("xy");
                this.beforeShowEvent.unsubscribe(this._primeXYFromDOM);
            }
        },

        /**
        * The default event handler fired when the "constraintoviewport" 
        * property is changed.
        * @method configConstrainToViewport
        * @param {String} type The CustomEvent type (usually the property name)
        * @param {Object[]} args The CustomEvent arguments. For configuration 
        * handlers, args[0] will equal the newly applied value for 
        * the property.
        * @param {Object} obj The scope object. For configuration handlers, 
        * this will usually equal the owner.
        */
        configConstrainToViewport: function (type, args, obj) {
            var val = args[0];

            if (val) {
                if (! Config.alreadySubscribed(this.beforeMoveEvent, this.enforceConstraints, this)) {
                    this.beforeMoveEvent.subscribe(this.enforceConstraints, this, true);
                }
                if (! Config.alreadySubscribed(this.beforeShowEvent, this._primeXYFromDOM)) {
                    this.beforeShowEvent.subscribe(this._primeXYFromDOM);
                }
            } else {
                this.beforeShowEvent.unsubscribe(this._primeXYFromDOM);
                this.beforeMoveEvent.unsubscribe(this.enforceConstraints, this);
            }
        },

         /**
        * The default event handler fired when the "context" property
        * is changed.
        *
        * @method configContext
        * @param {String} type The CustomEvent type (usually the property name)
        * @param {Object[]} args The CustomEvent arguments. For configuration 
        * handlers, args[0] will equal the newly applied value for the property.
        * @param {Object} obj The scope object. For configuration handlers, 
        * this will usually equal the owner.
        */
        configContext: function (type, args, obj) {

            var contextArgs = args[0],
                contextEl,
                elementMagnetCorner,
                contextMagnetCorner,
                triggers,
                offset,
                defTriggers = this.CONTEXT_TRIGGERS;

            if (contextArgs) {

                contextEl = contextArgs[0];
                elementMagnetCorner = contextArgs[1];
                contextMagnetCorner = contextArgs[2];
                triggers = contextArgs[3];
                offset = contextArgs[4];

                if (defTriggers && defTriggers.length > 0) {
                    triggers = (triggers || []).concat(defTriggers);
                }

                if (contextEl) {
                    if (typeof contextEl == "string") {
                        this.cfg.setProperty("context", [
                                document.getElementById(contextEl), 
                                elementMagnetCorner,
                                contextMagnetCorner,
                                triggers,
                                offset],
                                true);
                    }

                    if (elementMagnetCorner && contextMagnetCorner) {
                        this.align(elementMagnetCorner, contextMagnetCorner, offset);
                    }

                    if (this._contextTriggers) {
                        // Unsubscribe Old Set
                        this._processTriggers(this._contextTriggers, _UNSUBSCRIBE, this._alignOnTrigger);
                    }

                    if (triggers) {
                        // Subscribe New Set
                        this._processTriggers(triggers, _SUBSCRIBE, this._alignOnTrigger);
                        this._contextTriggers = triggers;
                    }
                }
            }
        },

        /**
         * Custom Event handler for context alignment triggers. Invokes the align method
         * 
         * @method _alignOnTrigger
         * @protected
         * 
         * @param {String} type The event type (not used by the default implementation)
         * @param {Any[]} args The array of arguments for the trigger event (not used by the default implementation)
         */
        _alignOnTrigger: function(type, args) {
            this.align();
        },

        /**
         * Helper method to locate the custom event instance for the event name string
         * passed in. As a convenience measure, any custom events passed in are returned.
         *
         * @method _findTriggerCE
         * @private
         *
         * @param {String|CustomEvent} t Either a CustomEvent, or event type (e.g. "windowScroll") for which a 
         * custom event instance needs to be looked up from the Overlay._TRIGGER_MAP.
         */
        _findTriggerCE : function(t) {
            var tce = null;
            if (t instanceof CustomEvent) {
                tce = t;
            } else if (Overlay._TRIGGER_MAP[t]) {
                tce = Overlay._TRIGGER_MAP[t];
            }
            return tce;
        },

        /**
         * Utility method that subscribes or unsubscribes the given 
         * function from the list of trigger events provided.
         *
         * @method _processTriggers
         * @protected 
         *
         * @param {Array[String|CustomEvent]} triggers An array of either CustomEvents, event type strings 
         * (e.g. "beforeShow", "windowScroll") to/from which the provided function should be 
         * subscribed/unsubscribed respectively.
         *
         * @param {String} mode Either "subscribe" or "unsubscribe", specifying whether or not
         * we are subscribing or unsubscribing trigger listeners
         * 
         * @param {Function} fn The function to be subscribed/unsubscribed to/from the trigger event.
         * Context is always set to the overlay instance, and no additional object argument 
         * get passed to the subscribed function.
         */
        _processTriggers : function(triggers, mode, fn) {
            var t, tce;

            for (var i = 0, l = triggers.length; i < l; ++i) {
                t = triggers[i];
                tce = this._findTriggerCE(t);
                if (tce) {
                    tce[mode](fn, this, true);
                } else {
                    this[mode](t, fn);
                }
            }
        },

        // END BUILT-IN PROPERTY EVENT HANDLERS //
        /**
        * Aligns the Overlay to its context element using the specified corner 
        * points (represented by the constants TOP_LEFT, TOP_RIGHT, BOTTOM_LEFT, 
        * and BOTTOM_RIGHT.
        * @method align
        * @param {String} elementAlign  The String representing the corner of 
        * the Overlay that should be aligned to the context element
        * @param {String} contextAlign  The corner of the context element 
        * that the elementAlign corner should stick to.
        * @param {Number[]} xyOffset Optional. A 2 element array specifying the x and y pixel offsets which should be applied
        * after aligning the element and context corners. For example, passing in [5, -10] for this value, would offset the 
        * Overlay by 5 pixels along the X axis (horizontally) and -10 pixels along the Y axis (vertically) after aligning the specified corners.
        */
        align: function (elementAlign, contextAlign, xyOffset) {

            var contextArgs = this.cfg.getProperty("context"),
                me = this,
                context,
                element,
                contextRegion;

            function doAlign(v, h) {

                var alignX = null, alignY = null;

                switch (elementAlign) {
    
                    case Overlay.TOP_LEFT:
                        alignX = h;
                        alignY = v;
                        break;
        
                    case Overlay.TOP_RIGHT:
                        alignX = h - element.offsetWidth;
                        alignY = v;
                        break;
        
                    case Overlay.BOTTOM_LEFT:
                        alignX = h;
                        alignY = v - element.offsetHeight;
                        break;
        
                    case Overlay.BOTTOM_RIGHT:
                        alignX = h - element.offsetWidth; 
                        alignY = v - element.offsetHeight;
                        break;
                }

                if (alignX !== null && alignY !== null) {
                    if (xyOffset) {
                        alignX += xyOffset[0];
                        alignY += xyOffset[1];
                    }
                    me.moveTo(alignX, alignY);
                }
            }

            if (contextArgs) {
                context = contextArgs[0];
                element = this.element;
                me = this;

                if (! elementAlign) {
                    elementAlign = contextArgs[1];
                }

                if (! contextAlign) {
                    contextAlign = contextArgs[2];
                }

                if (!xyOffset && contextArgs[4]) {
                    xyOffset = contextArgs[4];
                }

                if (element && context) {
                    contextRegion = Dom.getRegion(context);

                    switch (contextAlign) {
    
                        case Overlay.TOP_LEFT:
                            doAlign(contextRegion.top, contextRegion.left);
                            break;
        
                        case Overlay.TOP_RIGHT:
                            doAlign(contextRegion.top, contextRegion.right);
                            break;
        
                        case Overlay.BOTTOM_LEFT:
                            doAlign(contextRegion.bottom, contextRegion.left);
                            break;
        
                        case Overlay.BOTTOM_RIGHT:
                            doAlign(contextRegion.bottom, contextRegion.right);
                            break;
                    }
                }
            }
        },

        /**
        * The default event handler executed when the moveEvent is fired, if the 
        * "constraintoviewport" is set to true.
        * @method enforceConstraints
        * @param {String} type The CustomEvent type (usually the property name)
        * @param {Object[]} args The CustomEvent arguments. For configuration 
        * handlers, args[0] will equal the newly applied value for the property.
        * @param {Object} obj The scope object. For configuration handlers, 
        * this will usually equal the owner.
        */
        enforceConstraints: function (type, args, obj) {
            var pos = args[0];

            var cXY = this.getConstrainedXY(pos[0], pos[1]);
            this.cfg.setProperty("x", cXY[0], true);
            this.cfg.setProperty("y", cXY[1], true);
            this.cfg.setProperty("xy", cXY, true);
        },

        /**
         * Shared implementation method for getConstrainedX and getConstrainedY.
         * 
         * <p>
         * Given a coordinate value, returns the calculated coordinate required to 
         * position the Overlay if it is to be constrained to the viewport, based on the 
         * current element size, viewport dimensions, scroll values and preventoverlap 
         * settings
         * </p>
         *
         * @method _getConstrainedPos
         * @protected
         * @param {String} pos The coordinate which needs to be constrained, either "x" or "y"
         * @param {Number} The coordinate value which needs to be constrained
         * @return {Number} The constrained coordinate value
         */
        _getConstrainedPos: function(pos, val) {

            var overlayEl = this.element,

                buffer = Overlay.VIEWPORT_OFFSET,

                x = (pos == "x"),

                overlaySize      = (x) ? overlayEl.offsetWidth : overlayEl.offsetHeight,
                viewportSize     = (x) ? Dom.getViewportWidth() : Dom.getViewportHeight(),
                docScroll        = (x) ? Dom.getDocumentScrollLeft() : Dom.getDocumentScrollTop(),
                overlapPositions = (x) ? Overlay.PREVENT_OVERLAP_X : Overlay.PREVENT_OVERLAP_Y,

                context = this.cfg.getProperty("context"),

                bOverlayFitsInViewport = (overlaySize + buffer < viewportSize),
                bPreventContextOverlap = this.cfg.getProperty("preventcontextoverlap") && context && overlapPositions[(context[1] + context[2])],

                minConstraint = docScroll + buffer,
                maxConstraint = docScroll + viewportSize - overlaySize - buffer,

                constrainedVal = val;

            if (val < minConstraint || val > maxConstraint) {
                if (bPreventContextOverlap) {
                    constrainedVal = this._preventOverlap(pos, context[0], overlaySize, viewportSize, docScroll);
                } else {
                    if (bOverlayFitsInViewport) {
                        if (val < minConstraint) {
                            constrainedVal = minConstraint;
                        } else if (val > maxConstraint) {
                            constrainedVal = maxConstraint;
                        }
                    } else {
                        constrainedVal = minConstraint;
                    }
                }
            }

            return constrainedVal;
        },

        /**
         * Helper method, used to position the Overlap to prevent overlap with the 
         * context element (used when preventcontextoverlap is enabled)
         *
         * @method _preventOverlap
         * @protected
         * @param {String} pos The coordinate to prevent overlap for, either "x" or "y".
         * @param {HTMLElement} contextEl The context element
         * @param {Number} overlaySize The related overlay dimension value (for "x", the width, for "y", the height)
         * @param {Number} viewportSize The related viewport dimension value (for "x", the width, for "y", the height)
         * @param {Object} docScroll  The related document scroll value (for "x", the scrollLeft, for "y", the scrollTop)
         *
         * @return {Number} The new coordinate value which was set to prevent overlap
         */
        _preventOverlap : function(pos, contextEl, overlaySize, viewportSize, docScroll) {
            
            var x = (pos == "x"),

                buffer = Overlay.VIEWPORT_OFFSET,

                overlay = this,

                contextElPos   = ((x) ? Dom.getX(contextEl) : Dom.getY(contextEl)) - docScroll,
                contextElSize  = (x) ? contextEl.offsetWidth : contextEl.offsetHeight,

                minRegionSize = contextElPos - buffer,
                maxRegionSize = (viewportSize - (contextElPos + contextElSize)) - buffer,

                bFlipped = false,

                flip = function () {
                    var flippedVal;

                    if ((overlay.cfg.getProperty(pos) - docScroll) > contextElPos) {
                        flippedVal = (contextElPos - overlaySize);
                    } else {
                        flippedVal = (contextElPos + contextElSize);
                    }

                    overlay.cfg.setProperty(pos, (flippedVal + docScroll), true);

                    return flippedVal;
                },

                setPosition = function () {

                    var displayRegionSize = ((overlay.cfg.getProperty(pos) - docScroll) > contextElPos) ? maxRegionSize : minRegionSize,
                        position;

                    if (overlaySize > displayRegionSize) {
                        if (bFlipped) {
                            /*
                                 All possible positions and values have been 
                                 tried, but none were successful, so fall back 
                                 to the original size and position.
                            */
                            flip();
                        } else {
                            flip();
                            bFlipped = true;
                            position = setPosition();
                        }
                    }

                    return position;
                };

            setPosition();

            return this.cfg.getProperty(pos);
        },

        /**
         * Given x coordinate value, returns the calculated x coordinate required to 
         * position the Overlay if it is to be constrained to the viewport, based on the 
         * current element size, viewport dimensions and scroll values.
         *
         * @param {Number} x The X coordinate value to be constrained
         * @return {Number} The constrained x coordinate
         */		
        getConstrainedX: function (x) {
            return this._getConstrainedPos("x", x);
        },

        /**
         * Given y coordinate value, returns the calculated y coordinate required to 
         * position the Overlay if it is to be constrained to the viewport, based on the 
         * current element size, viewport dimensions and scroll values.
         *
         * @param {Number} y The Y coordinate value to be constrained
         * @return {Number} The constrained y coordinate
         */		
        getConstrainedY : function (y) {
            return this._getConstrainedPos("y", y);
        },

        /**
         * Given x, y coordinate values, returns the calculated coordinates required to 
         * position the Overlay if it is to be constrained to the viewport, based on the 
         * current element size, viewport dimensions and scroll values.
         *
         * @param {Number} x The X coordinate value to be constrained
         * @param {Number} y The Y coordinate value to be constrained
         * @return {Array} The constrained x and y coordinates at index 0 and 1 respectively;
         */
        getConstrainedXY: function(x, y) {
            return [this.getConstrainedX(x), this.getConstrainedY(y)];
        },

        /**
        * Centers the container in the viewport.
        * @method center
        */
        center: function () {

            var nViewportOffset = Overlay.VIEWPORT_OFFSET,
                elementWidth = this.element.offsetWidth,
                elementHeight = this.element.offsetHeight,
                viewPortWidth = Dom.getViewportWidth(),
                viewPortHeight = Dom.getViewportHeight(),
                x,
                y;

            if (elementWidth < viewPortWidth) {
                x = (viewPortWidth / 2) - (elementWidth / 2) + Dom.getDocumentScrollLeft();
            } else {
                x = nViewportOffset + Dom.getDocumentScrollLeft();
            }

            if (elementHeight < viewPortHeight) {
                y = (viewPortHeight / 2) - (elementHeight / 2) + Dom.getDocumentScrollTop();
            } else {
                y = nViewportOffset + Dom.getDocumentScrollTop();
            }

            this.cfg.setProperty("xy", [parseInt(x, 10), parseInt(y, 10)]);
            this.cfg.refireEvent("iframe");

            if (UA.webkit) {
                this.forceContainerRedraw();
            }
        },

        /**
        * Synchronizes the Panel's "xy", "x", and "y" properties with the 
        * Panel's position in the DOM. This is primarily used to update  
        * position information during drag & drop.
        * @method syncPosition
        */
        syncPosition: function () {

            var pos = Dom.getXY(this.element);

            this.cfg.setProperty("x", pos[0], true);
            this.cfg.setProperty("y", pos[1], true);
            this.cfg.setProperty("xy", pos, true);

        },

        /**
        * Event handler fired when the resize monitor element is resized.
        * @method onDomResize
        * @param {DOMEvent} e The resize DOM event
        * @param {Object} obj The scope object
        */
        onDomResize: function (e, obj) {

            var me = this;

            Overlay.superclass.onDomResize.call(this, e, obj);

            setTimeout(function () {
                me.syncPosition();
                me.cfg.refireEvent("iframe");
                me.cfg.refireEvent("context");
            }, 0);
        },

        /**
         * Determines the content box height of the given element (height of the element, without padding or borders) in pixels.
         *
         * @method _getComputedHeight
         * @private
         * @param {HTMLElement} el The element for which the content height needs to be determined
         * @return {Number} The content box height of the given element, or null if it could not be determined.
         */
        _getComputedHeight : (function() {

            if (document.defaultView && document.defaultView.getComputedStyle) {
                return function(el) {
                    var height = null;
                    if (el.ownerDocument && el.ownerDocument.defaultView) {
                        var computed = el.ownerDocument.defaultView.getComputedStyle(el, '');
                        if (computed) {
                            height = parseInt(computed.height, 10);
                        }
                    }
                    return (Lang.isNumber(height)) ? height : null;
                };
            } else {
                return function(el) {
                    var height = null;
                    if (el.style.pixelHeight) {
                        height = el.style.pixelHeight;
                    }
                    return (Lang.isNumber(height)) ? height : null;
                };
            }
        })(),

        /**
         * autofillheight validator. Verifies that the autofill value is either null 
         * or one of the strings : "body", "header" or "footer".
         *
         * @method _validateAutoFillHeight
         * @protected
         * @param {String} val
         * @return true, if valid, false otherwise
         */
        _validateAutoFillHeight : function(val) {
            return (!val) || (Lang.isString(val) && Overlay.STD_MOD_RE.test(val));
        },

        /**
         * The default custom event handler executed when the overlay's height is changed, 
         * if the autofillheight property has been set.
         *
         * @method _autoFillOnHeightChange
         * @protected
         * @param {String} type The event type
         * @param {Array} args The array of arguments passed to event subscribers
         * @param {HTMLElement} el The header, body or footer element which is to be resized to fill
         * out the containers height
         */
        _autoFillOnHeightChange : function(type, args, el) {
            var height = this.cfg.getProperty("height");
            if ((height && height !== "auto") || (height === 0)) {
                this.fillHeight(el);
            }
        },

        /**
         * Returns the sub-pixel height of the el, using getBoundingClientRect, if available,
         * otherwise returns the offsetHeight
         * @method _getPreciseHeight
         * @private
         * @param {HTMLElement} el
         * @return {Float} The sub-pixel height if supported by the browser, else the rounded height.
         */
        _getPreciseHeight : function(el) {
            var height = el.offsetHeight;

            if (el.getBoundingClientRect) {
                var rect = el.getBoundingClientRect();
                height = rect.bottom - rect.top;
            }

            return height;
        },

        /**
         * <p>
         * Sets the height on the provided header, body or footer element to 
         * fill out the height of the container. It determines the height of the 
         * containers content box, based on it's configured height value, and 
         * sets the height of the autofillheight element to fill out any 
         * space remaining after the other standard module element heights 
         * have been accounted for.
         * </p>
         * <p><strong>NOTE:</strong> This method is not designed to work if an explicit 
         * height has not been set on the container, since for an "auto" height container, 
         * the heights of the header/body/footer will drive the height of the container.</p>
         *
         * @method fillHeight
         * @param {HTMLElement} el The element which should be resized to fill out the height
         * of the container element.
         */
        fillHeight : function(el) {
            if (el) {
                var container = this.innerElement || this.element,
                    containerEls = [this.header, this.body, this.footer],
                    containerEl,
                    total = 0,
                    filled = 0,
                    remaining = 0,
                    validEl = false;

                for (var i = 0, l = containerEls.length; i < l; i++) {
                    containerEl = containerEls[i];
                    if (containerEl) {
                        if (el !== containerEl) {
                            filled += this._getPreciseHeight(containerEl);
                        } else {
                            validEl = true;
                        }
                    }
                }

                if (validEl) {

                    if (UA.ie || UA.opera) {
                        // Need to set height to 0, to allow height to be reduced
                        Dom.setStyle(el, 'height', 0 + 'px');
                    }

                    total = this._getComputedHeight(container);

                    // Fallback, if we can't get computed value for content height
                    if (total === null) {
                        Dom.addClass(container, "yui-override-padding");
                        total = container.clientHeight; // Content, No Border, 0 Padding (set by yui-override-padding)
                        Dom.removeClass(container, "yui-override-padding");
                    }
    
                    remaining = Math.max(total - filled, 0);
    
                    Dom.setStyle(el, "height", remaining + "px");
    
                    // Re-adjust height if required, to account for el padding and border
                    if (el.offsetHeight != remaining) {
                        remaining = Math.max(remaining - (el.offsetHeight - remaining), 0);
                    }
                    Dom.setStyle(el, "height", remaining + "px");
                }
            }
        },

        /**
        * Places the Overlay on top of all other instances of 
        * YAHOO.widget.Overlay.
        * @method bringToTop
        */
        bringToTop: function () {

            var aOverlays = [],
                oElement = this.element;

            function compareZIndexDesc(p_oOverlay1, p_oOverlay2) {

                var sZIndex1 = Dom.getStyle(p_oOverlay1, "zIndex"),
                    sZIndex2 = Dom.getStyle(p_oOverlay2, "zIndex"),

                    nZIndex1 = (!sZIndex1 || isNaN(sZIndex1)) ? 0 : parseInt(sZIndex1, 10),
                    nZIndex2 = (!sZIndex2 || isNaN(sZIndex2)) ? 0 : parseInt(sZIndex2, 10);

                if (nZIndex1 > nZIndex2) {
                    return -1;
                } else if (nZIndex1 < nZIndex2) {
                    return 1;
                } else {
                    return 0;
                }
            }

            function isOverlayElement(p_oElement) {

                var isOverlay = Dom.hasClass(p_oElement, Overlay.CSS_OVERLAY),
                    Panel = YAHOO.widget.Panel;

                if (isOverlay && !Dom.isAncestor(oElement, p_oElement)) {
                    if (Panel && Dom.hasClass(p_oElement, Panel.CSS_PANEL)) {
                        aOverlays[aOverlays.length] = p_oElement.parentNode;
                    } else {
                        aOverlays[aOverlays.length] = p_oElement;
                    }
                }
            }

            Dom.getElementsBy(isOverlayElement, "div", document.body);

            aOverlays.sort(compareZIndexDesc);

            var oTopOverlay = aOverlays[0],
                nTopZIndex;

            if (oTopOverlay) {
                nTopZIndex = Dom.getStyle(oTopOverlay, "zIndex");

                if (!isNaN(nTopZIndex)) {
                    var bRequiresBump = false;

                    if (oTopOverlay != oElement) {
                        bRequiresBump = true;
                    } else if (aOverlays.length > 1) {
                        var nNextZIndex = Dom.getStyle(aOverlays[1], "zIndex");
                        // Don't rely on DOM order to stack if 2 overlays are at the same zindex.
                        if (!isNaN(nNextZIndex) && (nTopZIndex == nNextZIndex)) {
                            bRequiresBump = true;
                        }
                    }
                    if (bRequiresBump) {
                        this.cfg.setProperty("zindex", (parseInt(nTopZIndex, 10) + 2));
                    }
                }
            }
        },

        /**
        * Removes the Overlay element from the DOM and sets all child 
        * elements to null.
        * @method destroy
        * @param {boolean} shallowPurge If true, only the parent element's DOM event listeners are purged. If false, or not provided, all children are also purged of DOM event listeners. 
        * NOTE: The flag is a "shallowPurge" flag, as opposed to what may be a more intuitive "purgeChildren" flag to maintain backwards compatibility with behavior prior to 2.9.0.
        */
        destroy: function (shallowPurge) {

            if (this.iframe) {
                this.iframe.parentNode.removeChild(this.iframe);
            }

            this.iframe = null;

            Overlay.windowResizeEvent.unsubscribe(
                this.doCenterOnDOMEvent, this);
    
            Overlay.windowScrollEvent.unsubscribe(
                this.doCenterOnDOMEvent, this);

            Module.textResizeEvent.unsubscribe(this._autoFillOnHeightChange);

            if (this._contextTriggers) {
                // Unsubscribe context triggers - to cover context triggers which listen for global
                // events such as windowResize and windowScroll. Easier just to unsubscribe all
                this._processTriggers(this._contextTriggers, _UNSUBSCRIBE, this._alignOnTrigger);
            }

            Overlay.superclass.destroy.call(this, shallowPurge);
        },

        /**
         * Can be used to force the container to repaint/redraw it's contents.
         * <p>
         * By default applies and then removes a 1px bottom margin through the 
         * application/removal of a "yui-force-redraw" class.
         * </p>
         * <p>
         * It is currently used by Overlay to force a repaint for webkit 
         * browsers, when centering.
         * </p>
         * @method forceContainerRedraw
         */
        forceContainerRedraw : function() {
            var c = this;
            Dom.addClass(c.element, "yui-force-redraw");
            setTimeout(function() {
                Dom.removeClass(c.element, "yui-force-redraw");
            }, 0);
        },

        /**
        * Returns a String representation of the object.
        * @method toString
        * @return {String} The string representation of the Overlay.
        */
        toString: function () {
            return "Overlay " + this.id;
        }

    });
}());
