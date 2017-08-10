(function () {

    /**
    * Tooltip is an implementation of Overlay that behaves like an OS tooltip, 
    * displaying when the user mouses over a particular element, and 
    * disappearing on mouse out.
    * @namespace YAHOO.widget
    * @class Tooltip
    * @extends YAHOO.widget.Overlay
    * @constructor
    * @param {String} el The element ID representing the Tooltip <em>OR</em>
    * @param {HTMLElement} el The element representing the Tooltip
    * @param {Object} userConfig The configuration object literal containing 
    * the configuration that should be set for this Overlay. See configuration 
    * documentation for more details.
    */
    YAHOO.widget.Tooltip = function (el, userConfig) {
        YAHOO.widget.Tooltip.superclass.constructor.call(this, el, userConfig);
    };

    var Lang = YAHOO.lang,
        Event = YAHOO.util.Event,
        CustomEvent = YAHOO.util.CustomEvent,
        Dom = YAHOO.util.Dom,
        Tooltip = YAHOO.widget.Tooltip,
        UA = YAHOO.env.ua,
        bIEQuirks = (UA.ie && (UA.ie <= 6 || document.compatMode == "BackCompat")),

        m_oShadowTemplate,

        /**
        * Constant representing the Tooltip's configuration properties
        * @property DEFAULT_CONFIG
        * @private
        * @final
        * @type Object
        */
        DEFAULT_CONFIG = {

            "PREVENT_OVERLAP": { 
                key: "preventoverlap", 
                value: true, 
                validator: Lang.isBoolean, 
                supercedes: ["x", "y", "xy"] 
            },

            "SHOW_DELAY": { 
                key: "showdelay", 
                value: 200, 
                validator: Lang.isNumber 
            }, 

            "AUTO_DISMISS_DELAY": { 
                key: "autodismissdelay", 
                value: 5000, 
                validator: Lang.isNumber 
            }, 

            "HIDE_DELAY": { 
                key: "hidedelay", 
                value: 250, 
                validator: Lang.isNumber 
            }, 

            "TEXT": { 
                key: "text", 
                suppressEvent: true 
            }, 

            "CONTAINER": { 
                key: "container"
            },

            "DISABLED": {
                key: "disabled",
                value: false,
                suppressEvent: true
            },

            "XY_OFFSET": {
                key: "xyoffset",
                value: [0, 25],
                suppressEvent: true
            }
        },

        /**
        * Constant representing the name of the Tooltip's events
        * @property EVENT_TYPES
        * @private
        * @final
        * @type Object
        */
        EVENT_TYPES = {
            "CONTEXT_MOUSE_OVER": "contextMouseOver",
            "CONTEXT_MOUSE_OUT": "contextMouseOut",
            "CONTEXT_TRIGGER": "contextTrigger"
        };

    /**
    * Constant representing the Tooltip CSS class
    * @property YAHOO.widget.Tooltip.CSS_TOOLTIP
    * @static
    * @final
    * @type String
    */
    Tooltip.CSS_TOOLTIP = "yui-tt";

    function restoreOriginalWidth(sOriginalWidth, sForcedWidth) {

        var oConfig = this.cfg,
            sCurrentWidth = oConfig.getProperty("width");

        if (sCurrentWidth == sForcedWidth) {
            oConfig.setProperty("width", sOriginalWidth);
        }
    }

    /* 
        changeContent event handler that sets a Tooltip instance's "width"
        configuration property to the value of its root HTML 
        elements's offsetWidth if a specific width has not been set.
    */

    function setWidthToOffsetWidth(p_sType, p_aArgs) {

        if ("_originalWidth" in this) {
            restoreOriginalWidth.call(this, this._originalWidth, this._forcedWidth);
        }

        var oBody = document.body,
            oConfig = this.cfg,
            sOriginalWidth = oConfig.getProperty("width"),
            sNewWidth,
            oClone;

        if ((!sOriginalWidth || sOriginalWidth == "auto") && 
            (oConfig.getProperty("container") != oBody || 
            oConfig.getProperty("x") >= Dom.getViewportWidth() || 
            oConfig.getProperty("y") >= Dom.getViewportHeight())) {

            oClone = this.element.cloneNode(true);
            oClone.style.visibility = "hidden";
            oClone.style.top = "0px";
            oClone.style.left = "0px";

            oBody.appendChild(oClone);

            sNewWidth = (oClone.offsetWidth + "px");

            oBody.removeChild(oClone);
            oClone = null;

            oConfig.setProperty("width", sNewWidth);
            oConfig.refireEvent("xy");

            this._originalWidth = sOriginalWidth || "";
            this._forcedWidth = sNewWidth;
        }
    }

    // "onDOMReady" that renders the ToolTip

    function onDOMReady(p_sType, p_aArgs, p_oObject) {
        this.render(p_oObject);
    }

    //  "init" event handler that automatically renders the Tooltip

    function onInit() {
        Event.onDOMReady(onDOMReady, this.cfg.getProperty("container"), this);
    }

    YAHOO.extend(Tooltip, YAHOO.widget.Overlay, { 

        /**
        * The Tooltip initialization method. This method is automatically 
        * called by the constructor. A Tooltip is automatically rendered by 
        * the init method, and it also is set to be invisible by default, 
        * and constrained to viewport by default as well.
        * @method init
        * @param {String} el The element ID representing the Tooltip <em>OR</em>
        * @param {HTMLElement} el The element representing the Tooltip
        * @param {Object} userConfig The configuration object literal 
        * containing the configuration that should be set for this Tooltip. 
        * See configuration documentation for more details.
        */
        init: function (el, userConfig) {

            this.logger = new YAHOO.widget.LogWriter(this.toString());

            Tooltip.superclass.init.call(this, el);

            this.beforeInitEvent.fire(Tooltip);

            Dom.addClass(this.element, Tooltip.CSS_TOOLTIP);

            if (userConfig) {
                this.cfg.applyConfig(userConfig, true);
            }

            this.cfg.queueProperty("visible", false);
            this.cfg.queueProperty("constraintoviewport", true);

            this.setBody("");

            this.subscribe("changeContent", setWidthToOffsetWidth);
            this.subscribe("init", onInit);
            this.subscribe("render", this.onRender);

            this.initEvent.fire(Tooltip);
        },

        /**
        * Initializes the custom events for Tooltip
        * @method initEvents
        */
        initEvents: function () {

            Tooltip.superclass.initEvents.call(this);
            var SIGNATURE = CustomEvent.LIST;

            /**
            * CustomEvent fired when user mouses over a context element. Returning false from
            * a subscriber to this event will prevent the tooltip from being displayed for
            * the current context element.
            * 
            * @event contextMouseOverEvent
            * @param {HTMLElement} context The context element which the user just moused over
            * @param {DOMEvent} e The DOM event object, associated with the mouse over
            */
            this.contextMouseOverEvent = this.createEvent(EVENT_TYPES.CONTEXT_MOUSE_OVER);
            this.contextMouseOverEvent.signature = SIGNATURE;

            /**
            * CustomEvent fired when the user mouses out of a context element.
            * 
            * @event contextMouseOutEvent
            * @param {HTMLElement} context The context element which the user just moused out of
            * @param {DOMEvent} e The DOM event object, associated with the mouse out
            */
            this.contextMouseOutEvent = this.createEvent(EVENT_TYPES.CONTEXT_MOUSE_OUT);
            this.contextMouseOutEvent.signature = SIGNATURE;

            /**
            * CustomEvent fired just before the tooltip is displayed for the current context.
            * <p>
            *  You can subscribe to this event if you need to set up the text for the 
            *  tooltip based on the context element for which it is about to be displayed.
            * </p>
            * <p>This event differs from the beforeShow event in following respects:</p>
            * <ol>
            *   <li>
            *    When moving from one context element to another, if the tooltip is not
            *    hidden (the <code>hidedelay</code> is not reached), the beforeShow and Show events will not
            *    be fired when the tooltip is displayed for the new context since it is already visible.
            *    However the contextTrigger event is always fired before displaying the tooltip for
            *    a new context.
            *   </li>
            *   <li>
            *    The trigger event provides access to the context element, allowing you to 
            *    set the text of the tooltip based on context element for which the tooltip is
            *    triggered.
            *   </li>
            * </ol>
            * <p>
            *  It is not possible to prevent the tooltip from being displayed
            *  using this event. You can use the contextMouseOverEvent if you need to prevent
            *  the tooltip from being displayed.
            * </p>
            * @event contextTriggerEvent
            * @param {HTMLElement} context The context element for which the tooltip is triggered
            */
            this.contextTriggerEvent = this.createEvent(EVENT_TYPES.CONTEXT_TRIGGER);
            this.contextTriggerEvent.signature = SIGNATURE;
        },

        /**
        * Initializes the class's configurable properties which can be 
        * changed using the Overlay's Config object (cfg).
        * @method initDefaultConfig
        */
        initDefaultConfig: function () {

            Tooltip.superclass.initDefaultConfig.call(this);

            /**
            * Specifies whether the Tooltip should be kept from overlapping 
            * its context element.
            * @config preventoverlap
            * @type Boolean
            * @default true
            */
            this.cfg.addProperty(DEFAULT_CONFIG.PREVENT_OVERLAP.key, {
                value: DEFAULT_CONFIG.PREVENT_OVERLAP.value, 
                validator: DEFAULT_CONFIG.PREVENT_OVERLAP.validator, 
                supercedes: DEFAULT_CONFIG.PREVENT_OVERLAP.supercedes
            });

            /**
            * The number of milliseconds to wait before showing a Tooltip 
            * on mouseover.
            * @config showdelay
            * @type Number
            * @default 200
            */
            this.cfg.addProperty(DEFAULT_CONFIG.SHOW_DELAY.key, {
                handler: this.configShowDelay,
                value: 200, 
                validator: DEFAULT_CONFIG.SHOW_DELAY.validator
            });

            /**
            * The number of milliseconds to wait before automatically 
            * dismissing a Tooltip after the mouse has been resting on the 
            * context element.
            * @config autodismissdelay
            * @type Number
            * @default 5000
            */
            this.cfg.addProperty(DEFAULT_CONFIG.AUTO_DISMISS_DELAY.key, {
                handler: this.configAutoDismissDelay,
                value: DEFAULT_CONFIG.AUTO_DISMISS_DELAY.value,
                validator: DEFAULT_CONFIG.AUTO_DISMISS_DELAY.validator
            });

            /**
            * The number of milliseconds to wait before hiding a Tooltip 
            * after mouseout.
            * @config hidedelay
            * @type Number
            * @default 250
            */
            this.cfg.addProperty(DEFAULT_CONFIG.HIDE_DELAY.key, {
                handler: this.configHideDelay,
                value: DEFAULT_CONFIG.HIDE_DELAY.value, 
                validator: DEFAULT_CONFIG.HIDE_DELAY.validator
            });

            /**
            * Specifies the Tooltip's text. The text is inserted into the DOM as HTML, and should be escaped by the implementor if coming from an external source. 
            * @config text
            * @type HTML
            * @default null
            */
            this.cfg.addProperty(DEFAULT_CONFIG.TEXT.key, {
                handler: this.configText,
                suppressEvent: DEFAULT_CONFIG.TEXT.suppressEvent
            });

            /**
            * Specifies the container element that the Tooltip's markup 
            * should be rendered into.
            * @config container
            * @type HTMLElement/String
            * @default document.body
            */
            this.cfg.addProperty(DEFAULT_CONFIG.CONTAINER.key, {
                handler: this.configContainer,
                value: document.body
            });

            /**
            * Specifies whether or not the tooltip is disabled. Disabled tooltips
            * will not be displayed. If the tooltip is driven by the title attribute
            * of the context element, the title attribute will still be removed for 
            * disabled tooltips, to prevent default tooltip behavior.
            * 
            * @config disabled
            * @type Boolean
            * @default false
            */
            this.cfg.addProperty(DEFAULT_CONFIG.DISABLED.key, {
                handler: this.configContainer,
                value: DEFAULT_CONFIG.DISABLED.value,
                supressEvent: DEFAULT_CONFIG.DISABLED.suppressEvent
            });

            /**
            * Specifies the XY offset from the mouse position, where the tooltip should be displayed, specified
            * as a 2 element array (e.g. [10, 20]); 
            *
            * @config xyoffset
            * @type Array
            * @default [0, 25]
            */
            this.cfg.addProperty(DEFAULT_CONFIG.XY_OFFSET.key, {
                value: DEFAULT_CONFIG.XY_OFFSET.value.concat(),
                supressEvent: DEFAULT_CONFIG.XY_OFFSET.suppressEvent 
            });

            /**
            * Specifies the element or elements that the Tooltip should be 
            * anchored to on mouseover.
            * @config context
            * @type HTMLElement[]/String[]
            * @default null
            */ 

            /**
            * String representing the width of the Tooltip.  <em>Please note:
            * </em> As of version 2.3 if either no value or a value of "auto" 
            * is specified, and the Toolip's "container" configuration property
            * is set to something other than <code>document.body</code> or 
            * its "context" element resides outside the immediately visible 
            * portion of the document, the width of the Tooltip will be 
            * calculated based on the offsetWidth of its root HTML and set just 
            * before it is made visible.  The original value will be 
            * restored when the Tooltip is hidden. This ensures the Tooltip is 
            * rendered at a usable width.  For more information see 
            * YUILibrary bug #1685496 and YUILibrary 
            * bug #1735423.
            * @config width
            * @type String
            * @default null
            */
        
        },
        
        // BEGIN BUILT-IN PROPERTY EVENT HANDLERS //
        
        /**
        * The default event handler fired when the "text" property is changed.
        * @method configText
        * @param {String} type The CustomEvent type (usually the property name)
        * @param {Object[]} args The CustomEvent arguments. For configuration 
        * handlers, args[0] will equal the newly applied value for the property.
        * @param {Object} obj The scope object. For configuration handlers, 
        * this will usually equal the owner.
        */
        configText: function (type, args, obj) {
            var text = args[0];
            if (text) {
                this.setBody(text);
            }
        },
        
        /**
        * The default event handler fired when the "container" property 
        * is changed.
        * @method configContainer
        * @param {String} type The CustomEvent type (usually the property name)
        * @param {Object[]} args The CustomEvent arguments. For 
        * configuration handlers, args[0] will equal the newly applied value 
        * for the property.
        * @param {Object} obj The scope object. For configuration handlers,
        * this will usually equal the owner.
        */
        configContainer: function (type, args, obj) {
            var container = args[0];

            if (typeof container == 'string') {
                this.cfg.setProperty("container", document.getElementById(container), true);
            }
        },
        
        /**
        * @method _removeEventListeners
        * @description Removes all of the DOM event handlers from the HTML
        *  element(s) that trigger the display of the tooltip.
        * @protected
        */
        _removeEventListeners: function () {
        
            var aElements = this._context,
                nElements,
                oElement,
                i;

            if (aElements) {
                nElements = aElements.length;
                if (nElements > 0) {
                    i = nElements - 1;
                    do {
                        oElement = aElements[i];
                        Event.removeListener(oElement, "mouseover", this.onContextMouseOver);
                        Event.removeListener(oElement, "mousemove", this.onContextMouseMove);
                        Event.removeListener(oElement, "mouseout", this.onContextMouseOut);
                    }
                    while (i--);
                }
            }
        },
        
        /**
        * The default event handler fired when the "context" property 
        * is changed.
        * @method configContext
        * @param {String} type The CustomEvent type (usually the property name)
        * @param {Object[]} args The CustomEvent arguments. For configuration 
        * handlers, args[0] will equal the newly applied value for the property.
        * @param {Object} obj The scope object. For configuration handlers,
        * this will usually equal the owner.
        */
        configContext: function (type, args, obj) {

            var context = args[0],
                aElements,
                nElements,
                oElement,
                i;

            if (context) {

                // Normalize parameter into an array
                if (! (context instanceof Array)) {
                    if (typeof context == "string") {
                        this.cfg.setProperty("context", [document.getElementById(context)], true);
                    } else { // Assuming this is an element
                        this.cfg.setProperty("context", [context], true);
                    }
                    context = this.cfg.getProperty("context");
                }

                // Remove any existing mouseover/mouseout listeners
                this._removeEventListeners();

                // Add mouseover/mouseout listeners to context elements
                this._context = context;

                aElements = this._context;

                if (aElements) {
                    nElements = aElements.length;
                    if (nElements > 0) {
                        i = nElements - 1;
                        do {
                            oElement = aElements[i];
                            Event.on(oElement, "mouseover", this.onContextMouseOver, this);
                            Event.on(oElement, "mousemove", this.onContextMouseMove, this);
                            Event.on(oElement, "mouseout", this.onContextMouseOut, this);
                        }
                        while (i--);
                    }
                }
            }
        },

        // END BUILT-IN PROPERTY EVENT HANDLERS //

        // BEGIN BUILT-IN DOM EVENT HANDLERS //

        /**
        * The default event handler fired when the user moves the mouse while 
        * over the context element.
        * @method onContextMouseMove
        * @param {DOMEvent} e The current DOM event
        * @param {Object} obj The object argument
        */
        onContextMouseMove: function (e, obj) {
            obj.pageX = Event.getPageX(e);
            obj.pageY = Event.getPageY(e);
        },

        /**
        * The default event handler fired when the user mouses over the 
        * context element.
        * @method onContextMouseOver
        * @param {DOMEvent} e The current DOM event
        * @param {Object} obj The object argument
        */
        onContextMouseOver: function (e, obj) {
            var context = this;

            if (context.title) {
                obj._tempTitle = context.title;
                context.title = "";
            }

            // Fire first, to honor disabled set in the listner
            if (obj.fireEvent("contextMouseOver", context, e) !== false && !obj.cfg.getProperty("disabled")) {

                // Stop the tooltip from being hidden (set on last mouseout)
                if (obj.hideProcId) {
                    clearTimeout(obj.hideProcId);
                    obj.logger.log("Clearing hide timer: " + obj.hideProcId, "time");
                    obj.hideProcId = null;
                }

                Event.on(context, "mousemove", obj.onContextMouseMove, obj);

                /**
                * The unique process ID associated with the thread responsible 
                * for showing the Tooltip.
                * @type int
                */
                obj.showProcId = obj.doShow(e, context);
                obj.logger.log("Setting show tooltip timeout: " + obj.showProcId, "time");
            }
        },

        /**
        * The default event handler fired when the user mouses out of 
        * the context element.
        * @method onContextMouseOut
        * @param {DOMEvent} e The current DOM event
        * @param {Object} obj The object argument
        */
        onContextMouseOut: function (e, obj) {
            var el = this;

            if (obj._tempTitle) {
                el.title = obj._tempTitle;
                obj._tempTitle = null;
            }

            if (obj.showProcId) {
                clearTimeout(obj.showProcId);
                obj.logger.log("Clearing show timer: " + obj.showProcId, "time");
                obj.showProcId = null;
            }

            if (obj.hideProcId) {
                clearTimeout(obj.hideProcId);
                obj.logger.log("Clearing hide timer: " + obj.hideProcId, "time");
                obj.hideProcId = null;
            }

            obj.fireEvent("contextMouseOut", el, e);

            obj.hideProcId = setTimeout(function () {
                obj.hide();
            }, obj.cfg.getProperty("hidedelay"));
        },

        // END BUILT-IN DOM EVENT HANDLERS //

        /**
        * Processes the showing of the Tooltip by setting the timeout delay 
        * and offset of the Tooltip.
        * @method doShow
        * @param {DOMEvent} e The current DOM event
        * @param {HTMLElement} context The current context element
        * @return {Number} The process ID of the timeout function associated 
        * with doShow
        */
        doShow: function (e, context) {

            var offset = this.cfg.getProperty("xyoffset"),
                xOffset = offset[0],
                yOffset = offset[1],
                me = this;

            if (UA.opera && context.tagName && 
                context.tagName.toUpperCase() == "A") {
                yOffset += 12;
            }

            return setTimeout(function () {

                var txt = me.cfg.getProperty("text");

                // title does not over-ride text
                if (me._tempTitle && (txt === "" || YAHOO.lang.isUndefined(txt) || YAHOO.lang.isNull(txt))) {
                    me.setBody(me._tempTitle);
                } else {
                    me.cfg.refireEvent("text");
                }

                me.logger.log("Show tooltip", "time");
                me.moveTo(me.pageX + xOffset, me.pageY + yOffset);

                if (me.cfg.getProperty("preventoverlap")) {
                    me.preventOverlap(me.pageX, me.pageY);
                }

                Event.removeListener(context, "mousemove", me.onContextMouseMove);

                me.contextTriggerEvent.fire(context);

                me.show();

                me.hideProcId = me.doHide();
                me.logger.log("Hide tooltip time active: " + me.hideProcId, "time");

            }, this.cfg.getProperty("showdelay"));
        },

        /**
        * Sets the timeout for the auto-dismiss delay, which by default is 5 
        * seconds, meaning that a tooltip will automatically dismiss itself 
        * after 5 seconds of being displayed.
        * @method doHide
        */
        doHide: function () {

            var me = this;

            me.logger.log("Setting hide tooltip timeout", "time");

            return setTimeout(function () {

                me.logger.log("Hide tooltip", "time");
                me.hide();

            }, this.cfg.getProperty("autodismissdelay"));

        },

        /**
        * Fired when the Tooltip is moved, this event handler is used to 
        * prevent the Tooltip from overlapping with its context element.
        * @method preventOverlay
        * @param {Number} pageX The x coordinate position of the mouse pointer
        * @param {Number} pageY The y coordinate position of the mouse pointer
        */
        preventOverlap: function (pageX, pageY) {
        
            var height = this.element.offsetHeight,
                mousePoint = new YAHOO.util.Point(pageX, pageY),
                elementRegion = Dom.getRegion(this.element);
        
            elementRegion.top -= 5;
            elementRegion.left -= 5;
            elementRegion.right += 5;
            elementRegion.bottom += 5;
        
            this.logger.log("context " + elementRegion, "ttip");
            this.logger.log("mouse " + mousePoint, "ttip");
        
            if (elementRegion.contains(mousePoint)) {
                this.logger.log("OVERLAP", "warn");
                this.cfg.setProperty("y", (pageY - height - 5));
            }
        },


        /**
        * @method onRender
        * @description "render" event handler for the Tooltip.
        * @param {String} p_sType String representing the name of the event  
        * that was fired.
        * @param {Array} p_aArgs Array of arguments sent when the event 
        * was fired.
        */
        onRender: function (p_sType, p_aArgs) {
    
            function sizeShadow() {
    
                var oElement = this.element,
                    oShadow = this.underlay;
            
                if (oShadow) {
                    oShadow.style.width = (oElement.offsetWidth + 6) + "px";
                    oShadow.style.height = (oElement.offsetHeight + 1) + "px"; 
                }
            
            }

            function addShadowVisibleClass() {
                Dom.addClass(this.underlay, "yui-tt-shadow-visible");

                if (UA.ie) {
                    this.forceUnderlayRedraw();
                }
            }

            function removeShadowVisibleClass() {
                Dom.removeClass(this.underlay, "yui-tt-shadow-visible");
            }

            function createShadow() {
    
                var oShadow = this.underlay,
                    oElement,
                    Module,
                    nIE,
                    me;
    
                if (!oShadow) {
    
                    oElement = this.element;
                    Module = YAHOO.widget.Module;
                    nIE = UA.ie;
                    me = this;

                    if (!m_oShadowTemplate) {
                        m_oShadowTemplate = document.createElement("div");
                        m_oShadowTemplate.className = "yui-tt-shadow";
                    }

                    oShadow = m_oShadowTemplate.cloneNode(false);

                    oElement.appendChild(oShadow);

                    this.underlay = oShadow;

                    // Backward compatibility, even though it's probably 
                    // intended to be "private", it isn't marked as such in the api docs
                    this._shadow = this.underlay;

                    addShadowVisibleClass.call(this);

                    this.subscribe("beforeShow", addShadowVisibleClass);
                    this.subscribe("hide", removeShadowVisibleClass);

                    if (bIEQuirks) {
                        window.setTimeout(function () { 
                            sizeShadow.call(me); 
                        }, 0);
    
                        this.cfg.subscribeToConfigEvent("width", sizeShadow);
                        this.cfg.subscribeToConfigEvent("height", sizeShadow);
                        this.subscribe("changeContent", sizeShadow);

                        Module.textResizeEvent.subscribe(sizeShadow, this, true);
                        this.subscribe("destroy", function () {
                            Module.textResizeEvent.unsubscribe(sizeShadow, this);
                        });
                    }
                }
            }

            function onBeforeShow() {
                createShadow.call(this);
                this.unsubscribe("beforeShow", onBeforeShow);
            }

            if (this.cfg.getProperty("visible")) {
                createShadow.call(this);
            } else {
                this.subscribe("beforeShow", onBeforeShow);
            }
        
        },

        /**
         * Forces the underlay element to be repainted, through the application/removal
         * of a yui-force-redraw class to the underlay element.
         * 
         * @method forceUnderlayRedraw
         */
        forceUnderlayRedraw : function() {
            var tt = this;
            Dom.addClass(tt.underlay, "yui-force-redraw");
            setTimeout(function() {Dom.removeClass(tt.underlay, "yui-force-redraw");}, 0);
        },

        /**
        * Removes the Tooltip element from the DOM and sets all child 
        * elements to null.
        * @method destroy
        */
        destroy: function () {
        
            // Remove any existing mouseover/mouseout listeners
            this._removeEventListeners();

            Tooltip.superclass.destroy.call(this);  
        
        },
        
        /**
        * Returns a string representation of the object.
        * @method toString
        * @return {String} The string representation of the Tooltip
        */
        toString: function () {
            return "Tooltip " + this.id;
        }
    
    });

}());
