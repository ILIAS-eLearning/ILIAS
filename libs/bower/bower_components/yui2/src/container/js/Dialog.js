(function () {

    /**
    * <p>
    * Dialog is an implementation of Panel that can be used to submit form 
    * data.
    * </p>
    * <p>
    * Built-in functionality for buttons with event handlers is included. 
    * If the optional YUI Button dependancy is included on the page, the buttons
    * created will be instances of YAHOO.widget.Button, otherwise regular HTML buttons
    * will be created.
    * </p>
    * <p>
    * Forms can be processed in 3 ways -- via an asynchronous Connection utility call, 
    * a simple form POST or GET, or manually. The YUI Connection utility should be
    * included if you're using the default "async" postmethod, but is not required if
    * you're using any of the other postmethod values.
    * </p>
    * @namespace YAHOO.widget
    * @class Dialog
    * @extends YAHOO.widget.Panel
    * @constructor
    * @param {String} el The element ID representing the Dialog <em>OR</em>
    * @param {HTMLElement} el The element representing the Dialog
    * @param {Object} userConfig The configuration object literal containing 
    * the configuration that should be set for this Dialog. See configuration 
    * documentation for more details.
    */
    YAHOO.widget.Dialog = function (el, userConfig) {
        YAHOO.widget.Dialog.superclass.constructor.call(this, el, userConfig);
    };

    var Event = YAHOO.util.Event,
        CustomEvent = YAHOO.util.CustomEvent,
        Dom = YAHOO.util.Dom,
        Dialog = YAHOO.widget.Dialog,
        Lang = YAHOO.lang,

        /**
         * Constant representing the name of the Dialog's events
         * @property EVENT_TYPES
         * @private
         * @final
         * @type Object
         */
        EVENT_TYPES = {
            "BEFORE_SUBMIT": "beforeSubmit",
            "SUBMIT": "submit",
            "MANUAL_SUBMIT": "manualSubmit",
            "ASYNC_SUBMIT": "asyncSubmit",
            "FORM_SUBMIT": "formSubmit",
            "CANCEL": "cancel"
        },

        /**
        * Constant representing the Dialog's configuration properties
        * @property DEFAULT_CONFIG
        * @private
        * @final
        * @type Object
        */
        DEFAULT_CONFIG = {

            "POST_METHOD": { 
                key: "postmethod", 
                value: "async"
            },

            "POST_DATA" : {
                key: "postdata",
                value: null
            },

            "BUTTONS": {
                key: "buttons",
                value: "none",
                supercedes: ["visible"]
            },

            "HIDEAFTERSUBMIT" : {
                key: "hideaftersubmit",
                value: true
            }

        };

    /**
    * Constant representing the default CSS class used for a Dialog
    * @property YAHOO.widget.Dialog.CSS_DIALOG
    * @static
    * @final
    * @type String
    */
    Dialog.CSS_DIALOG = "yui-dialog";

    function removeButtonEventHandlers() {

        var aButtons = this._aButtons,
            nButtons,
            oButton,
            i;

        if (Lang.isArray(aButtons)) {
            nButtons = aButtons.length;

            if (nButtons > 0) {
                i = nButtons - 1;
                do {
                    oButton = aButtons[i];

                    if (YAHOO.widget.Button && oButton instanceof YAHOO.widget.Button) {
                        oButton.destroy();
                    }
                    else if (oButton.tagName.toUpperCase() == "BUTTON") {
                        Event.purgeElement(oButton);
                        Event.purgeElement(oButton, false);
                    }
                }
                while (i--);
            }
        }
    }

    YAHOO.extend(Dialog, YAHOO.widget.Panel, { 

        /**
        * @property form
        * @description Object reference to the Dialog's 
        * <code>&#60;form&#62;</code> element.
        * @default null 
        * @type <a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/
        * level-one-html.html#ID-40002357">HTMLFormElement</a>
        */
        form: null,
    
        /**
        * Initializes the class's configurable properties which can be changed 
        * using the Dialog's Config object (cfg).
        * @method initDefaultConfig
        */
        initDefaultConfig: function () {
            Dialog.superclass.initDefaultConfig.call(this);

            /**
            * The internally maintained callback object for use with the 
            * Connection utility. The format of the callback object is 
            * similar to Connection Manager's callback object and is 
            * simply passed through to Connection Manager when the async 
            * request is made.
            * @property callback
            * @type Object
            */
            this.callback = {

                /**
                * The function to execute upon success of the 
                * Connection submission (when the form does not
                * contain a file input element).
                * 
                * @property callback.success
                * @type Function
                */
                success: null,

                /**
                * The function to execute upon failure of the 
                * Connection submission
                * @property callback.failure
                * @type Function
                */
                failure: null,

                /**
                *<p>
                * The function to execute upon success of the 
                * Connection submission, when the form contains
                * a file input element.
                * </p>
                * <p>
                * <em>NOTE:</em> Connection manager will not
                * invoke the success or failure handlers for the file
                * upload use case. This will be the only callback
                * handler invoked.
                * </p>
                * <p>
                * For more information, see the <a href="http://developer.yahoo.com/yui/connection/#file">
                * Connection Manager documenation on file uploads</a>.
                * </p>
                * @property callback.upload
                * @type Function
                */

                /**
                * The arbitrary argument or arguments to pass to the Connection 
                * callback functions
                * @property callback.argument
                * @type Object
                */
                argument: null

            };

            // Add form dialog config properties //
            /**
            * The method to use for posting the Dialog's form. Possible values 
            * are "async", "form", and "manual".
            * @config postmethod
            * @type String
            * @default async
            */
            this.cfg.addProperty(DEFAULT_CONFIG.POST_METHOD.key, {
                handler: this.configPostMethod, 
                value: DEFAULT_CONFIG.POST_METHOD.value, 
                validator: function (val) {
                    if (val != "form" && val != "async" && val != "none" && 
                        val != "manual") {
                        return false;
                    } else {
                        return true;
                    }
                }
            });

            /**
            * Any additional post data which needs to be sent when using the 
            * <a href="#config_postmethod">async</a> postmethod for dialog POST submissions.
            * The format for the post data string is defined by Connection Manager's 
            * <a href="YAHOO.util.Connect.html#method_asyncRequest">asyncRequest</a> 
            * method.
            * @config postdata
            * @type String
            * @default null
            */
            this.cfg.addProperty(DEFAULT_CONFIG.POST_DATA.key, {
                value: DEFAULT_CONFIG.POST_DATA.value
            });

            /**
            * This property is used to configure whether or not the 
            * dialog should be automatically hidden after submit.
            * 
            * @config hideaftersubmit
            * @type Boolean
            * @default true
            */
            this.cfg.addProperty(DEFAULT_CONFIG.HIDEAFTERSUBMIT.key, {
                value: DEFAULT_CONFIG.HIDEAFTERSUBMIT.value
            });

            /**
            * Array of object literals, each containing a set of properties 
            * defining a button to be appended into the Dialog's footer.
            *
            * <p>Each button object in the buttons array can have three properties:</p>
            * <dl>
            *    <dt>text:</dt>
            *    <dd>
            *       The text that will display on the face of the button. The text can 
            *       include HTML, as long as it is compliant with HTML Button specifications. The text is added to the DOM as HTML,
            *       and should be escaped by the implementor if coming from an external source. 
            *    </dd>
            *    <dt>handler:</dt>
            *    <dd>Can be either:
            *    <ol>
            *       <li>A reference to a function that should fire when the 
            *       button is clicked.  (In this case scope of this function is 
            *       always its Dialog instance.)</li>
            *
            *       <li>An object literal representing the code to be 
            *       executed when the button is clicked.
            *       
            *       <p>Format:</p>
            *
            *       <p>
            *       <code>{
            *       <br>
            *       <strong>fn:</strong> Function, &#47;&#47;
            *       The handler to call when  the event fires.
            *       <br>
            *       <strong>obj:</strong> Object, &#47;&#47; 
            *       An  object to pass back to the handler.
            *       <br>
            *       <strong>scope:</strong> Object &#47;&#47; 
            *       The object to use for the scope of the handler.
            *       <br>
            *       }</code>
            *       </p>
            *       </li>
            *     </ol>
            *     </dd>
            *     <dt>isDefault:</dt>
            *     <dd>
            *        An optional boolean value that specifies that a button 
            *        should be highlighted and focused by default.
            *     </dd>
            * </dl>
            *
            * <em>NOTE:</em>If the YUI Button Widget is included on the page, 
            * the buttons created will be instances of YAHOO.widget.Button. 
            * Otherwise, HTML Buttons (<code>&#60;BUTTON&#62;</code>) will be 
            * created.
            *
            * @config buttons
            * @type {Array|String}
            * @default "none"
            */
            this.cfg.addProperty(DEFAULT_CONFIG.BUTTONS.key, {
                handler: this.configButtons,
                value: DEFAULT_CONFIG.BUTTONS.value,
                supercedes : DEFAULT_CONFIG.BUTTONS.supercedes
            }); 

        },

        /**
        * Initializes the custom events for Dialog which are fired 
        * automatically at appropriate times by the Dialog class.
        * @method initEvents
        */
        initEvents: function () {
            Dialog.superclass.initEvents.call(this);

            var SIGNATURE = CustomEvent.LIST;

            /**
            * CustomEvent fired prior to submission
            * @event beforeSubmitEvent
            */ 
            this.beforeSubmitEvent = 
                this.createEvent(EVENT_TYPES.BEFORE_SUBMIT);
            this.beforeSubmitEvent.signature = SIGNATURE;
            
            /**
            * CustomEvent fired after submission
            * @event submitEvent
            */
            this.submitEvent = this.createEvent(EVENT_TYPES.SUBMIT);
            this.submitEvent.signature = SIGNATURE;
        
            /**
            * CustomEvent fired for manual submission, before the generic submit event is fired
            * @event manualSubmitEvent
            */
            this.manualSubmitEvent = 
                this.createEvent(EVENT_TYPES.MANUAL_SUBMIT);
            this.manualSubmitEvent.signature = SIGNATURE;

            /**
            * CustomEvent fired after asynchronous submission, before the generic submit event is fired
            *
            * @event asyncSubmitEvent
            * @param {Object} conn The connection object, returned by YAHOO.util.Connect.asyncRequest
            */
            this.asyncSubmitEvent = this.createEvent(EVENT_TYPES.ASYNC_SUBMIT);
            this.asyncSubmitEvent.signature = SIGNATURE;

            /**
            * CustomEvent fired after form-based submission, before the generic submit event is fired
            * @event formSubmitEvent
            */
            this.formSubmitEvent = this.createEvent(EVENT_TYPES.FORM_SUBMIT);
            this.formSubmitEvent.signature = SIGNATURE;

            /**
            * CustomEvent fired after cancel
            * @event cancelEvent
            */
            this.cancelEvent = this.createEvent(EVENT_TYPES.CANCEL);
            this.cancelEvent.signature = SIGNATURE;
        
        },
        
        /**
        * The Dialog initialization method, which is executed for Dialog and 
        * all of its subclasses. This method is automatically called by the 
        * constructor, and  sets up all DOM references for pre-existing markup, 
        * and creates required markup if it is not already present.
        * 
        * @method init
        * @param {String} el The element ID representing the Dialog <em>OR</em>
        * @param {HTMLElement} el The element representing the Dialog
        * @param {Object} userConfig The configuration object literal 
        * containing the configuration that should be set for this Dialog. 
        * See configuration documentation for more details.
        */
        init: function (el, userConfig) {

            /*
                 Note that we don't pass the user config in here yet because 
                 we only want it executed once, at the lowest subclass level
            */

            Dialog.superclass.init.call(this, el/*, userConfig*/); 

            this.beforeInitEvent.fire(Dialog);

            Dom.addClass(this.element, Dialog.CSS_DIALOG);

            this.cfg.setProperty("visible", false);

            if (userConfig) {
                this.cfg.applyConfig(userConfig, true);
            }

            //this.showEvent.subscribe(this.focusFirst, this, true);
            this.beforeHideEvent.subscribe(this.blurButtons, this, true);

            this.subscribe("changeBody", this.registerForm);

            this.initEvent.fire(Dialog);
        },

        /**
        * Submits the Dialog's form depending on the value of the 
        * "postmethod" configuration property.  <strong>Please note:
        * </strong> As of version 2.3 this method will automatically handle 
        * asyncronous file uploads should the Dialog instance's form contain 
        * <code>&#60;input type="file"&#62;</code> elements.  If a Dialog 
        * instance will be handling asyncronous file uploads, its 
        * <code>callback</code> property will need to be setup with a 
        * <code>upload</code> handler rather than the standard 
        * <code>success</code> and, or <code>failure</code> handlers.  For more 
        * information, see the <a href="http://developer.yahoo.com/yui/
        * connection/#file">Connection Manager documenation on file uploads</a>.
        * @method doSubmit
        */
        doSubmit: function () {

            var Connect = YAHOO.util.Connect,
                oForm = this.form,
                bUseFileUpload = false,
                bUseSecureFileUpload = false,
                aElements,
                nElements,
                i,
                formAttrs;

            switch (this.cfg.getProperty("postmethod")) {

                case "async":
                    aElements = oForm.elements;
                    nElements = aElements.length;

                    if (nElements > 0) {
                        i = nElements - 1;
                        do {
                            if (aElements[i].type == "file") {
                                bUseFileUpload = true;
                                break;
                            }
                        }
                        while(i--);
                    }

                    if (bUseFileUpload && YAHOO.env.ua.ie && this.isSecure) {
                        bUseSecureFileUpload = true;
                    }

                    formAttrs = this._getFormAttributes(oForm);

                    Connect.setForm(oForm, bUseFileUpload, bUseSecureFileUpload);

                    var postData = this.cfg.getProperty("postdata");
                    var c = Connect.asyncRequest(formAttrs.method, formAttrs.action, this.callback, postData);

                    this.asyncSubmitEvent.fire(c);

                    break;

                case "form":
                    oForm.submit();
                    this.formSubmitEvent.fire();
                    break;

                case "none":
                case "manual":
                    this.manualSubmitEvent.fire();
                    break;
            }
        },

        /**
         * Retrieves important attributes (currently method and action) from
         * the form element, accounting for any elements which may have the same name 
         * as the attributes. Defaults to "POST" and "" for method and action respectively
         * if the attribute cannot be retrieved.
         *
         * @method _getFormAttributes
         * @protected
         * @param {HTMLFormElement} oForm The HTML Form element from which to retrieve the attributes
         * @return {Object} Object literal, with method and action String properties.
         */
        _getFormAttributes : function(oForm){
            var attrs = {
                method : null,
                action : null
            };

            if (oForm) {
                if (oForm.getAttributeNode) {
                    var action = oForm.getAttributeNode("action");
                    var method = oForm.getAttributeNode("method");

                    if (action) {
                        attrs.action = action.value;
                    }

                    if (method) {
                        attrs.method = method.value;
                    }

                } else {
                    attrs.action = oForm.getAttribute("action");
                    attrs.method = oForm.getAttribute("method");
                }
            }

            attrs.method = (Lang.isString(attrs.method) ? attrs.method : "POST").toUpperCase();
            attrs.action = Lang.isString(attrs.action) ? attrs.action : "";

            return attrs;
        },

        /**
        * Prepares the Dialog's internal FORM object, creating one if one is
        * not currently present.
        * @method registerForm
        */
        registerForm: function() {

            var form = this.element.getElementsByTagName("form")[0];

            if (this.form) {
                if (this.form == form && Dom.isAncestor(this.element, this.form)) {
                    return;
                } else {
                    Event.purgeElement(this.form);
                    this.form = null;
                }
            }

            if (!form) {
                form = document.createElement("form");
                form.name = "frm_" + this.id;
                this.body.appendChild(form);
            }

            if (form) {
                this.form = form;
                Event.on(form, "submit", this._submitHandler, this, true);
            }
        },

        /**
         * Internal handler for the form submit event
         *
         * @method _submitHandler
         * @protected
         * @param {DOMEvent} e The DOM Event object
         */
        _submitHandler : function(e) {
            Event.stopEvent(e);
            this.submit();
            this.form.blur();
        },

        /**
         * Sets up a tab, shift-tab loop between the first and last elements
         * provided. NOTE: Sets up the preventBackTab and preventTabOut KeyListener
         * instance properties, which are reset everytime this method is invoked.
         *
         * @method setTabLoop
         * @param {HTMLElement} firstElement
         * @param {HTMLElement} lastElement
         *
         */
        setTabLoop : function(firstElement, lastElement) {

            firstElement = firstElement || this.firstButton;
            lastElement = lastElement || this.lastButton;

            Dialog.superclass.setTabLoop.call(this, firstElement, lastElement);
        },

        /**
         * Protected internal method for setTabLoop, which can be used by 
         * subclasses to jump in and modify the arguments passed in if required.
         *
         * @method _setTabLoop
         * @param {HTMLElement} firstElement
         * @param {HTMLElement} lastElement
         * @protected
         */
        _setTabLoop : function(firstElement, lastElement) {
            firstElement = firstElement || this.firstButton;
            lastElement = this.lastButton || lastElement;

            this.setTabLoop(firstElement, lastElement);
        },

        /**
         * Configures instance properties, pointing to the 
         * first and last focusable elements in the Dialog's form.
         *
         * @method setFirstLastFocusable
         */
        setFirstLastFocusable : function() {

            Dialog.superclass.setFirstLastFocusable.call(this);

            var i, l, el, elements = this.focusableElements;

            this.firstFormElement = null;
            this.lastFormElement = null;

            if (this.form && elements && elements.length > 0) {
                l = elements.length;

                for (i = 0; i < l; ++i) {
                    el = elements[i];
                    if (this.form === el.form) {
                        this.firstFormElement = el;
                        break;
                    }
                }

                for (i = l-1; i >= 0; --i) {
                    el = elements[i];
                    if (this.form === el.form) {
                        this.lastFormElement = el;
                        break;
                    }
                }
            }
        },

        // BEGIN BUILT-IN PROPERTY EVENT HANDLERS //
        /**
        * The default event handler fired when the "close" property is 
        * changed. The method controls the appending or hiding of the close
        * icon at the top right of the Dialog.
        * @method configClose
        * @param {String} type The CustomEvent type (usually the property name)
        * @param {Object[]} args The CustomEvent arguments. For 
        * configuration handlers, args[0] will equal the newly applied value 
        * for the property.
        * @param {Object} obj The scope object. For configuration handlers, 
        * this will usually equal the owner.
        */
        configClose: function (type, args, obj) {
            Dialog.superclass.configClose.apply(this, arguments);
        },

        /**
         * Event handler for the close icon
         * 
         * @method _doClose
         * @protected
         * 
         * @param {DOMEvent} e
         */
         _doClose : function(e) {
            Event.preventDefault(e);
            this.cancel();
        },

        /**
        * The default event handler for the "buttons" configuration property
        * @method configButtons
        * @param {String} type The CustomEvent type (usually the property name)
        * @param {Object[]} args The CustomEvent arguments. For configuration 
        * handlers, args[0] will equal the newly applied value for the property.
        * @param {Object} obj The scope object. For configuration handlers, 
        * this will usually equal the owner.
        */
        configButtons: function (type, args, obj) {

            var Button = YAHOO.widget.Button,
                aButtons = args[0],
                oInnerElement = this.innerElement,
                oButton,
                oButtonEl,
                oYUIButton,
                nButtons,
                oSpan,
                oFooter,
                i;

            removeButtonEventHandlers.call(this);

            this._aButtons = null;

            if (Lang.isArray(aButtons)) {

                oSpan = document.createElement("span");
                oSpan.className = "button-group";
                nButtons = aButtons.length;

                this._aButtons = [];
                this.defaultHtmlButton = null;

                for (i = 0; i < nButtons; i++) {
                    oButton = aButtons[i];

                    if (Button) {
                        oYUIButton = new Button({ label: oButton.text, type:oButton.type });
                        oYUIButton.appendTo(oSpan);

                        oButtonEl = oYUIButton.get("element");

                        if (oButton.isDefault) {
                            oYUIButton.addClass("default");
                            this.defaultHtmlButton = oButtonEl;
                        }

                        if (Lang.isFunction(oButton.handler)) {

                            oYUIButton.set("onclick", { 
                                fn: oButton.handler, 
                                obj: this, 
                                scope: this 
                            });

                        } else if (Lang.isObject(oButton.handler) && Lang.isFunction(oButton.handler.fn)) {

                            oYUIButton.set("onclick", { 
                                fn: oButton.handler.fn, 
                                obj: ((!Lang.isUndefined(oButton.handler.obj)) ? oButton.handler.obj : this), 
                                scope: (oButton.handler.scope || this) 
                            });

                        }

                        this._aButtons[this._aButtons.length] = oYUIButton;

                    } else {

                        oButtonEl = document.createElement("button");
                        oButtonEl.setAttribute("type", "button");

                        if (oButton.isDefault) {
                            oButtonEl.className = "default";
                            this.defaultHtmlButton = oButtonEl;
                        }

                        oButtonEl.innerHTML = oButton.text;

                        if (Lang.isFunction(oButton.handler)) {
                            Event.on(oButtonEl, "click", oButton.handler, this, true);
                        } else if (Lang.isObject(oButton.handler) && 
                            Lang.isFunction(oButton.handler.fn)) {
    
                            Event.on(oButtonEl, "click", 
                                oButton.handler.fn, 
                                ((!Lang.isUndefined(oButton.handler.obj)) ? oButton.handler.obj : this), 
                                (oButton.handler.scope || this));
                        }

                        oSpan.appendChild(oButtonEl);
                        this._aButtons[this._aButtons.length] = oButtonEl;
                    }

                    oButton.htmlButton = oButtonEl;

                    if (i === 0) {
                        this.firstButton = oButtonEl;
                    }

                    if (i == (nButtons - 1)) {
                        this.lastButton = oButtonEl;
                    }
                }

                this.setFooter(oSpan);

                oFooter = this.footer;

                if (Dom.inDocument(this.element) && !Dom.isAncestor(oInnerElement, oFooter)) {
                    oInnerElement.appendChild(oFooter);
                }

                this.buttonSpan = oSpan;

            } else { // Do cleanup
                oSpan = this.buttonSpan;
                oFooter = this.footer;
                if (oSpan && oFooter) {
                    oFooter.removeChild(oSpan);
                    this.buttonSpan = null;
                    this.firstButton = null;
                    this.lastButton = null;
                    this.defaultHtmlButton = null;
                }
            }

            this.changeContentEvent.fire();
        },

        /**
        * @method getButtons
        * @description Returns an array containing each of the Dialog's 
        * buttons, by default an array of HTML <code>&#60;BUTTON&#62;</code> 
        * elements.  If the Dialog's buttons were created using the 
        * YAHOO.widget.Button class (via the inclusion of the optional Button 
        * dependency on the page), an array of YAHOO.widget.Button instances 
        * is returned.
        * @return {Array}
        */
        getButtons: function () {
            return this._aButtons || null;
        },

        /**
         * <p>
         * Sets focus to the first focusable element in the Dialog's form if found, 
         * else, the default button if found, else the first button defined via the 
         * "buttons" configuration property.
         * </p>
         * <p>
         * This method is invoked when the Dialog is made visible.
         * </p>
         * @method focusFirst
         * @return {Boolean} true, if focused. false if not
         */
        focusFirst: function (type, args, obj) {

            var el = this.firstFormElement, 
                focused = false;

            if (args && args[1]) {
                Event.stopEvent(args[1]);

                // When tabbing here, use firstElement instead of firstFormElement
                if (args[0] === 9 && this.firstElement) {
                    el = this.firstElement;
                }
            }

            if (el) {
                try {
                    el.focus();
                    focused = true;
                } catch(oException) {
                    // Ignore
                }
            } else {
                if (this.defaultHtmlButton) {
                    focused = this.focusDefaultButton();
                } else {
                    focused = this.focusFirstButton();
                }
            }
            return focused;
        },

        /**
        * Sets focus to the last element in the Dialog's form or the last 
        * button defined via the "buttons" configuration property.
        * @method focusLast
        * @return {Boolean} true, if focused. false if not
        */
        focusLast: function (type, args, obj) {

            var aButtons = this.cfg.getProperty("buttons"),
                el = this.lastFormElement,
                focused = false;

            if (args && args[1]) {
                Event.stopEvent(args[1]);

                // When tabbing here, use lastElement instead of lastFormElement
                if (args[0] === 9 && this.lastElement) {
                    el = this.lastElement;
                }
            }

            if (aButtons && Lang.isArray(aButtons)) {
                focused = this.focusLastButton();
            } else {
                if (el) {
                    try {
                        el.focus();
                        focused = true;
                    } catch(oException) {
                        // Ignore
                    }
                }
            }

            return focused;
        },

        /**
         * Helper method to normalize button references. It either returns the 
         * YUI Button instance for the given element if found,
         * or the passes back the HTMLElement reference if a corresponding YUI Button
         * reference is not found or YAHOO.widget.Button does not exist on the page.
         *
         * @method _getButton
         * @private
         * @param {HTMLElement} button
         * @return {YAHOO.widget.Button|HTMLElement}
         */
        _getButton : function(button) {
            var Button = YAHOO.widget.Button;

            // If we have an HTML button and YUI Button is on the page, 
            // get the YUI Button reference if available.
            if (Button && button && button.nodeName && button.id) {
                button = Button.getButton(button.id) || button;
            }

            return button;
        },

        /**
        * Sets the focus to the button that is designated as the default via 
        * the "buttons" configuration property. By default, this method is 
        * called when the Dialog is made visible.
        * @method focusDefaultButton
        * @return {Boolean} true if focused, false if not
        */
        focusDefaultButton: function () {
            var button = this._getButton(this.defaultHtmlButton), 
                         focused = false;
            
            if (button) {
                /*
                    Place the call to the "focus" method inside a try/catch
                    block to prevent IE from throwing JavaScript errors if
                    the element is disabled or hidden.
                */
                try {
                    button.focus();
                    focused = true;
                } catch(oException) {
                }
            }
            return focused;
        },

        /**
        * Blurs all the buttons defined via the "buttons" 
        * configuration property.
        * @method blurButtons
        */
        blurButtons: function () {
            
            var aButtons = this.cfg.getProperty("buttons"),
                nButtons,
                oButton,
                oElement,
                i;

            if (aButtons && Lang.isArray(aButtons)) {
                nButtons = aButtons.length;
                if (nButtons > 0) {
                    i = (nButtons - 1);
                    do {
                        oButton = aButtons[i];
                        if (oButton) {
                            oElement = this._getButton(oButton.htmlButton);
                            if (oElement) {
                                /*
                                    Place the call to the "blur" method inside  
                                    a try/catch block to prevent IE from  
                                    throwing JavaScript errors if the element 
                                    is disabled or hidden.
                                */
                                try {
                                    oElement.blur();
                                } catch(oException) {
                                    // ignore
                                }
                            }
                        }
                    } while(i--);
                }
            }
        },

        /**
        * Sets the focus to the first button created via the "buttons"
        * configuration property.
        * @method focusFirstButton
        * @return {Boolean} true, if focused. false if not
        */
        focusFirstButton: function () {

            var aButtons = this.cfg.getProperty("buttons"),
                oButton,
                oElement,
                focused = false;

            if (aButtons && Lang.isArray(aButtons)) {
                oButton = aButtons[0];
                if (oButton) {
                    oElement = this._getButton(oButton.htmlButton);
                    if (oElement) {
                        /*
                            Place the call to the "focus" method inside a 
                            try/catch block to prevent IE from throwing 
                            JavaScript errors if the element is disabled 
                            or hidden.
                        */
                        try {
                            oElement.focus();
                            focused = true;
                        } catch(oException) {
                            // ignore
                        }
                    }
                }
            }

            return focused;
        },

        /**
        * Sets the focus to the last button created via the "buttons" 
        * configuration property.
        * @method focusLastButton
        * @return {Boolean} true, if focused. false if not
        */
        focusLastButton: function () {

            var aButtons = this.cfg.getProperty("buttons"),
                nButtons,
                oButton,
                oElement, 
                focused = false;

            if (aButtons && Lang.isArray(aButtons)) {
                nButtons = aButtons.length;
                if (nButtons > 0) {
                    oButton = aButtons[(nButtons - 1)];

                    if (oButton) {
                        oElement = this._getButton(oButton.htmlButton);
                        if (oElement) {
                            /*
                                Place the call to the "focus" method inside a 
                                try/catch block to prevent IE from throwing 
                                JavaScript errors if the element is disabled
                                or hidden.
                            */
        
                            try {
                                oElement.focus();
                                focused = true;
                            } catch(oException) {
                                // Ignore
                            }
                        }
                    }
                }
            }

            return focused;
        },

        /**
        * The default event handler for the "postmethod" configuration property
        * @method configPostMethod
        * @param {String} type The CustomEvent type (usually the property name)
        * @param {Object[]} args The CustomEvent arguments. For 
        * configuration handlers, args[0] will equal the newly applied value 
        * for the property.
        * @param {Object} obj The scope object. For configuration handlers, 
        * this will usually equal the owner.
        */
        configPostMethod: function (type, args, obj) {
            this.registerForm();
        },

        // END BUILT-IN PROPERTY EVENT HANDLERS //
        
        /**
        * Built-in function hook for writing a validation function that will 
        * be checked for a "true" value prior to a submit. This function, as 
        * implemented by default, always returns true, so it should be 
        * overridden if validation is necessary.
        * @method validate
        */
        validate: function () {
            return true;
        },

        /**
        * Executes a submit of the Dialog if validation 
        * is successful. By default the Dialog is hidden
        * after submission, but you can set the "hideaftersubmit"
        * configuration property to false, to prevent the Dialog
        * from being hidden.
        * 
        * @method submit
        */
        submit: function () {
            if (this.validate()) {
                if (this.beforeSubmitEvent.fire()) {
                    this.doSubmit();
                    this.submitEvent.fire();
    
                    if (this.cfg.getProperty("hideaftersubmit")) {
                        this.hide();
                    }
    
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        },

        /**
        * Executes the cancel of the Dialog followed by a hide.
        * @method cancel
        */
        cancel: function () {
            this.cancelEvent.fire();
            this.hide();
        },
        
        /**
        * Returns a JSON-compatible data structure representing the data 
        * currently contained in the form.
        * @method getData
        * @return {Object} A JSON object reprsenting the data of the 
        * current form.
        */
        getData: function () {

            var oForm = this.form,
                aElements,
                nTotalElements,
                oData,
                sName,
                oElement,
                nElements,
                sType,
                sTagName,
                aOptions,
                nOptions,
                aValues,
                oOption,
                oRadio,
                oCheckbox,
                valueAttr,
                i,
                n;    
    
            function isFormElement(p_oElement) {
                var sTag = p_oElement.tagName.toUpperCase();
                return ((sTag == "INPUT" || sTag == "TEXTAREA" || 
                        sTag == "SELECT") && p_oElement.name == sName);
            }

            if (oForm) {

                aElements = oForm.elements;
                nTotalElements = aElements.length;
                oData = {};

                for (i = 0; i < nTotalElements; i++) {
                    sName = aElements[i].name;

                    /*
                        Using "Dom.getElementsBy" to safeguard user from JS 
                        errors that result from giving a form field (or set of 
                        fields) the same name as a native method of a form 
                        (like "submit") or a DOM collection (such as the "item"
                        method). Originally tried accessing fields via the 
                        "namedItem" method of the "element" collection, but 
                        discovered that it won't return a collection of fields 
                        in Gecko.
                    */

                    oElement = Dom.getElementsBy(isFormElement, "*", oForm);
                    nElements = oElement.length;

                    if (nElements > 0) {
                        if (nElements == 1) {
                            oElement = oElement[0];

                            sType = oElement.type;
                            sTagName = oElement.tagName.toUpperCase();

                            switch (sTagName) {
                                case "INPUT":
                                    if (sType == "checkbox") {
                                        oData[sName] = oElement.checked;
                                    } else if (sType != "radio") {
                                        oData[sName] = oElement.value;
                                    }
                                    break;

                                case "TEXTAREA":
                                    oData[sName] = oElement.value;
                                    break;
    
                                case "SELECT":
                                    aOptions = oElement.options;
                                    nOptions = aOptions.length;
                                    aValues = [];
    
                                    for (n = 0; n < nOptions; n++) {
                                        oOption = aOptions[n];
                                        if (oOption.selected) {
                                            valueAttr = oOption.attributes.value;
                                            aValues[aValues.length] = (valueAttr && valueAttr.specified) ? oOption.value : oOption.text;
                                        }
                                    }
                                    oData[sName] = aValues;
                                    break;
                            }
        
                        } else {
                            sType = oElement[0].type;
                            switch (sType) {
                                case "radio":
                                    for (n = 0; n < nElements; n++) {
                                        oRadio = oElement[n];
                                        if (oRadio.checked) {
                                            oData[sName] = oRadio.value;
                                            break;
                                        }
                                    }
                                    break;
        
                                case "checkbox":
                                    aValues = [];
                                    for (n = 0; n < nElements; n++) {
                                        oCheckbox = oElement[n];
                                        if (oCheckbox.checked) {
                                            aValues[aValues.length] =  oCheckbox.value;
                                        }
                                    }
                                    oData[sName] = aValues;
                                    break;
                            }
                        }
                    }
                }
            }

            return oData;
        },

        /**
        * Removes the Panel element from the DOM and sets all child elements 
        * to null.
        * @method destroy
        * @param {boolean} shallowPurge If true, only the parent element's DOM event listeners are purged. If false, or not provided, all children are also purged of DOM event listeners. 
        * NOTE: The flag is a "shallowPurge" flag, as opposed to what may be a more intuitive "purgeChildren" flag to maintain backwards compatibility with behavior prior to 2.9.0.
        */
        destroy: function (shallowPurge) {
            removeButtonEventHandlers.call(this);

            this._aButtons = null;

            var aForms = this.element.getElementsByTagName("form"),
                oForm;

            if (aForms.length > 0) {
                oForm = aForms[0];

                if (oForm) {
                    Event.purgeElement(oForm);
                    if (oForm.parentNode) {
                        oForm.parentNode.removeChild(oForm);
                    }
                    this.form = null;
                }
            }
            Dialog.superclass.destroy.call(this, shallowPurge);
        },

        /**
        * Returns a string representation of the object.
        * @method toString
        * @return {String} The string representation of the Dialog
        */
        toString: function () {
            return "Dialog " + this.id;
        }
    
    });

}());