(function () {

    // Shorthard for utilities
    
    var Dom = YAHOO.util.Dom,
        Event = YAHOO.util.Event,
        Lang = YAHOO.lang,
        Button = YAHOO.widget.Button,  
    
        // Private collection of radio buttons
    
        m_oButtons = {};



    /**
    * The ButtonGroup class creates a set of buttons that are mutually 
    * exclusive; checking one button in the set will uncheck all others in the 
    * button group.
    * @param {String} p_oElement String specifying the id attribute of the 
    * <code>&#60;div&#62;</code> element of the button group.
    * @param {<a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/
    * level-one-html.html#ID-22445964">HTMLDivElement</a>} p_oElement Object 
    * specifying the <code>&#60;div&#62;</code> element of the button group.
    * @param {Object} p_oElement Object literal specifying a set of 
    * configuration attributes used to create the button group.
    * @param {Object} p_oAttributes Optional. Object literal specifying a set 
    * of configuration attributes used to create the button group.
    * @namespace YAHOO.widget
    * @class ButtonGroup
    * @constructor
    * @extends YAHOO.util.Element
    */
    YAHOO.widget.ButtonGroup = function (p_oElement, p_oAttributes) {
    
        var fnSuperClass = YAHOO.widget.ButtonGroup.superclass.constructor,
            sNodeName,
            oElement,
            sId;
    
        if (arguments.length == 1 && !Lang.isString(p_oElement) && 
            !p_oElement.nodeName) {
    
            if (!p_oElement.id) {
    
                sId = Dom.generateId();
    
                p_oElement.id = sId;
    
                YAHOO.log("No value specified for the button group's \"id\"" +
                    " attribute. Setting button group id to \"" + sId + "\".",
                    "info");
    
            }
    
            this.logger = new YAHOO.widget.LogWriter("ButtonGroup " + sId);
    
            this.logger.log("No source HTML element.  Building the button " +
                    "group using the set of configuration attributes.");
    
            fnSuperClass.call(this, (this._createGroupElement()), p_oElement);
    
        }
        else if (Lang.isString(p_oElement)) {
    
            oElement = Dom.get(p_oElement);
    
            if (oElement) {
            
                if (oElement.nodeName.toUpperCase() == this.NODE_NAME) {
    
                    this.logger = 
                        new YAHOO.widget.LogWriter("ButtonGroup " + p_oElement);
            
                    fnSuperClass.call(this, oElement, p_oAttributes);
    
                }
    
            }
        
        }
        else {
    
            sNodeName = p_oElement.nodeName.toUpperCase();
    
            if (sNodeName && sNodeName == this.NODE_NAME) {
        
                if (!p_oElement.id) {
        
                    p_oElement.id = Dom.generateId();
        
                    YAHOO.log("No value specified for the button group's" +
                        " \"id\" attribute. Setting button group id " +
                        "to \"" + p_oElement.id + "\".", "warn");
        
                }
        
                this.logger = 
                    new YAHOO.widget.LogWriter("ButtonGroup " + p_oElement.id);
        
                fnSuperClass.call(this, p_oElement, p_oAttributes);
    
            }
    
        }
    
    };
    
    
    YAHOO.extend(YAHOO.widget.ButtonGroup, YAHOO.util.Element, {
    
    
        // Protected properties
        
        
        /** 
        * @property _buttons
        * @description Array of buttons in the button group.
        * @default null
        * @protected
        * @type Array
        */
        _buttons: null,
        
        
        
        // Constants
        
        
        /**
        * @property NODE_NAME
        * @description The name of the tag to be used for the button 
        * group's element. 
        * @default "DIV"
        * @final
        * @type String
        */
        NODE_NAME: "DIV",


        /**
        * @property CLASS_NAME_PREFIX
        * @description Prefix used for all class names applied to a ButtonGroup.
        * @default "yui-"
        * @final
        * @type String
        */
        CLASS_NAME_PREFIX: "yui-",
        
        
        /**
        * @property CSS_CLASS_NAME
        * @description String representing the CSS class(es) to be applied  
        * to the button group's element.
        * @default "buttongroup"
        * @final
        * @type String
        */
        CSS_CLASS_NAME: "buttongroup",
    
    
    
        // Protected methods
        
        
        /**
        * @method _createGroupElement
        * @description Creates the button group's element.
        * @protected
        * @return {<a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/
        * level-one-html.html#ID-22445964">HTMLDivElement</a>}
        */
        _createGroupElement: function () {
        
            var oElement = document.createElement(this.NODE_NAME);
        
            return oElement;
        
        },
        
        
        
        // Protected attribute setter methods
        
        
        /**
        * @method _setDisabled
        * @description Sets the value of the button groups's 
        * "disabled" attribute.
        * @protected
        * @param {Boolean} p_bDisabled Boolean indicating the value for
        * the button group's "disabled" attribute.
        */
        _setDisabled: function (p_bDisabled) {
        
            var nButtons = this.getCount(),
                i;
        
            if (nButtons > 0) {
        
                i = nButtons - 1;
                
                do {
        
                    this._buttons[i].set("disabled", p_bDisabled);
                
                }
                while (i--);
        
            }
        
        },
        
        
        
        // Protected event handlers
        
        
        /**
        * @method _onKeyDown
        * @description "keydown" event handler for the button group.
        * @protected
        * @param {Event} p_oEvent Object representing the DOM event object  
        * passed back by the event utility (YAHOO.util.Event).
        */
        _onKeyDown: function (p_oEvent) {
        
            var oTarget = Event.getTarget(p_oEvent),
                nCharCode = Event.getCharCode(p_oEvent),
                sId = oTarget.parentNode.parentNode.id,
                oButton = m_oButtons[sId],
                nIndex = -1;
        
        
            if (nCharCode == 37 || nCharCode == 38) {
        
                nIndex = (oButton.index === 0) ? 
                            (this._buttons.length - 1) : (oButton.index - 1);
            
            }
            else if (nCharCode == 39 || nCharCode == 40) {
        
                nIndex = (oButton.index === (this._buttons.length - 1)) ? 
                            0 : (oButton.index + 1);
        
            }
        
        
            if (nIndex > -1) {
        
                this.check(nIndex);
                this.getButton(nIndex).focus();
            
            }        
        
        },
        
        
        /**
        * @method _onAppendTo
        * @description "appendTo" event handler for the button group.
        * @protected
        * @param {Event} p_oEvent Object representing the event that was fired.
        */
        _onAppendTo: function (p_oEvent) {
        
            var aButtons = this._buttons,
                nButtons = aButtons.length,
                i;
        
            for (i = 0; i < nButtons; i++) {
        
                aButtons[i].appendTo(this.get("element"));
        
            }
        
        },
        
        
        /**
        * @method _onButtonCheckedChange
        * @description "checkedChange" event handler for each button in the 
        * button group.
        * @protected
        * @param {Event} p_oEvent Object representing the event that was fired.
        * @param {<a href="YAHOO.widget.Button.html">YAHOO.widget.Button</a>}  
        * p_oButton Object representing the button that fired the event.
        */
        _onButtonCheckedChange: function (p_oEvent, p_oButton) {
        
            var bChecked = p_oEvent.newValue,
                oCheckedButton = this.get("checkedButton");
        
            if (bChecked && oCheckedButton != p_oButton) {
        
                if (oCheckedButton) {
        
                    oCheckedButton.set("checked", false, true);
        
                }
        
                this.set("checkedButton", p_oButton);
                this.set("value", p_oButton.get("value"));
        
            }
            else if (oCheckedButton && !oCheckedButton.set("checked")) {
        
                oCheckedButton.set("checked", true, true);
        
            }
           
        },
        
        
        
        // Public methods
        
        
        /**
        * @method init
        * @description The ButtonGroup class's initialization method.
        * @param {String} p_oElement String specifying the id attribute of the 
        * <code>&#60;div&#62;</code> element of the button group.
        * @param {<a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/
        * level-one-html.html#ID-22445964">HTMLDivElement</a>} p_oElement Object 
        * specifying the <code>&#60;div&#62;</code> element of the button group.
        * @param {Object} p_oElement Object literal specifying a set of  
        * configuration attributes used to create the button group.
        * @param {Object} p_oAttributes Optional. Object literal specifying a
        * set of configuration attributes used to create the button group.
        */
        init: function (p_oElement, p_oAttributes) {
        
            this._buttons = [];
        
            YAHOO.widget.ButtonGroup.superclass.init.call(this, p_oElement, 
                    p_oAttributes);
        
            this.addClass(this.CLASS_NAME_PREFIX + this.CSS_CLASS_NAME);

        
            var sClass = (YAHOO.widget.Button.prototype.CLASS_NAME_PREFIX + "radio-button"),
				aButtons = this.getElementsByClassName(sClass);

            this.logger.log("Searching for child nodes with the class name " +
                sClass + " to add to the button group.");
        
        
            if (aButtons.length > 0) {
        
                this.logger.log("Found " + aButtons.length + 
                    " child nodes with the class name " + sClass + 
                    "  Attempting to add to button group.");
        
                this.addButtons(aButtons);
        
            }
        
        
            this.logger.log("Searching for child nodes with the type of " +
                " \"radio\" to add to the button group.");
        
            function isRadioButton(p_oElement) {
        
                return (p_oElement.type == "radio");
        
            }
        
            aButtons = 
                Dom.getElementsBy(isRadioButton, "input", this.get("element"));
        
        
            if (aButtons.length > 0) {
        
                this.logger.log("Found " + aButtons.length + " child nodes" +
                    " with the type of \"radio.\"  Attempting to add to" +
                    " button group.");
        
                this.addButtons(aButtons);
        
            }
        
            this.on("keydown", this._onKeyDown);
            this.on("appendTo", this._onAppendTo);
        

            var oContainer = this.get("container");

            if (oContainer) {
        
                if (Lang.isString(oContainer)) {
        
                    Event.onContentReady(oContainer, function () {
        
                        this.appendTo(oContainer);            
                    
                    }, null, this);
        
                }
                else {
        
                    this.appendTo(oContainer);
        
                }
        
            }
        
        
            this.logger.log("Initialization completed.");
        
        },
        
        
        /**
        * @method initAttributes
        * @description Initializes all of the configuration attributes used to  
        * create the button group.
        * @param {Object} p_oAttributes Object literal specifying a set of 
        * configuration attributes used to create the button group.
        */
        initAttributes: function (p_oAttributes) {
        
            var oAttributes = p_oAttributes || {};
        
            YAHOO.widget.ButtonGroup.superclass.initAttributes.call(
                this, oAttributes);
        
        
            /**
            * @attribute name
            * @description String specifying the name for the button group.  
            * This name will be applied to each button in the button group.
            * @default null
            * @type String
            */
            this.setAttributeConfig("name", {
        
                value: oAttributes.name,
                validator: Lang.isString
        
            });
        
        
            /**
            * @attribute disabled
            * @description Boolean indicating if the button group should be 
            * disabled.  Disabling the button group will disable each button 
            * in the button group.  Disabled buttons are dimmed and will not 
            * respond to user input or fire events.
            * @default false
            * @type Boolean
            */
            this.setAttributeConfig("disabled", {
        
                value: (oAttributes.disabled || false),
                validator: Lang.isBoolean,
                method: this._setDisabled
        
            });
        
        
            /**
            * @attribute value
            * @description Object specifying the value for the button group.
            * @default null
            * @type Object
            */
            this.setAttributeConfig("value", {
        
                value: oAttributes.value
        
            });
        
        
            /**
            * @attribute container
            * @description HTML element reference or string specifying the id 
            * attribute of the HTML element that the button group's markup
            * should be rendered into.
            * @type <a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/
            * level-one-html.html#ID-58190037">HTMLElement</a>|String
            * @default null
			* @writeonce
            */
            this.setAttributeConfig("container", {
        
                value: oAttributes.container,
                writeOnce: true
        
            });
        
        
            /**
            * @attribute checkedButton
            * @description Reference for the button in the button group that 
            * is checked.
            * @type {<a href="YAHOO.widget.Button.html">YAHOO.widget.Button</a>}
            * @default null
            */
            this.setAttributeConfig("checkedButton", {
        
                value: null
        
            });
        
        },
        
        
        /**
        * @method addButton
        * @description Adds the button to the button group.
        * @param {<a href="YAHOO.widget.Button.html">YAHOO.widget.Button</a>}  
        * p_oButton Object reference for the <a href="YAHOO.widget.Button.html">
        * YAHOO.widget.Button</a> instance to be added to the button group.
        * @param {String} p_oButton String specifying the id attribute of the 
        * <code>&#60;input&#62;</code> or <code>&#60;span&#62;</code> element 
        * to be used to create the button to be added to the button group.
        * @param {<a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/
        * level-one-html.html#ID-6043025">HTMLInputElement</a>|<a href="
        * http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/level-one-html.html#
        * ID-33759296">HTMLElement</a>} p_oButton Object reference for the 
        * <code>&#60;input&#62;</code> or <code>&#60;span&#62;</code> element 
        * to be used to create the button to be added to the button group.
        * @param {Object} p_oButton Object literal specifying a set of 
        * <a href="YAHOO.widget.Button.html">YAHOO.widget.Button</a> 
        * configuration attributes used to configure the button to be added to 
        * the button group.
        * @return {<a href="YAHOO.widget.Button.html">YAHOO.widget.Button</a>} 
        */
        addButton: function (p_oButton) {
        
            var oButton,
                oButtonElement,
                oGroupElement,
                nIndex,
                sButtonName,
                sGroupName;
        
        
            if (p_oButton instanceof Button && 
                p_oButton.get("type") == "radio") {
        
                oButton = p_oButton;
        
            }
            else if (!Lang.isString(p_oButton) && !p_oButton.nodeName) {
        
                p_oButton.type = "radio";
        
                oButton = new Button(p_oButton);

            }
            else {
        
                oButton = new Button(p_oButton, { type: "radio" });
        
            }
        
        
            if (oButton) {
        
                nIndex = this._buttons.length;
                sButtonName = oButton.get("name");
                sGroupName = this.get("name");
        
                oButton.index = nIndex;
        
                this._buttons[nIndex] = oButton;
                m_oButtons[oButton.get("id")] = oButton;
        
        
                if (sButtonName != sGroupName) {
        
                    oButton.set("name", sGroupName);
                
                }
        
        
                if (this.get("disabled")) {
        
                    oButton.set("disabled", true);
        
                }
        
        
                if (oButton.get("checked")) {
        
                    this.set("checkedButton", oButton);
        
                }

                
                oButtonElement = oButton.get("element");
                oGroupElement = this.get("element");
                
                if (oButtonElement.parentNode != oGroupElement) {
                
                    oGroupElement.appendChild(oButtonElement);
                
                }
        
                
                oButton.on("checkedChange", 
                    this._onButtonCheckedChange, oButton, this);
        
                this.logger.log("Button " + oButton.get("id") + " added.");
        
            }

			return oButton;
        
        },
        
        
        /**
        * @method addButtons
        * @description Adds the array of buttons to the button group.
        * @param {Array} p_aButtons Array of <a href="YAHOO.widget.Button.html">
        * YAHOO.widget.Button</a> instances to be added 
        * to the button group.
        * @param {Array} p_aButtons Array of strings specifying the id 
        * attribute of the <code>&#60;input&#62;</code> or <code>&#60;span&#62;
        * </code> elements to be used to create the buttons to be added to the 
        * button group.
        * @param {Array} p_aButtons Array of object references for the 
        * <code>&#60;input&#62;</code> or <code>&#60;span&#62;</code> elements 
        * to be used to create the buttons to be added to the button group.
        * @param {Array} p_aButtons Array of object literals, each containing
        * a set of <a href="YAHOO.widget.Button.html">YAHOO.widget.Button</a>  
        * configuration attributes used to configure each button to be added 
        * to the button group.
        * @return {Array}
        */
        addButtons: function (p_aButtons) {
    
            var nButtons,
                oButton,
                aButtons,
                i;
        
            if (Lang.isArray(p_aButtons)) {
            
                nButtons = p_aButtons.length;
                aButtons = [];
        
                if (nButtons > 0) {
        
                    for (i = 0; i < nButtons; i++) {
        
                        oButton = this.addButton(p_aButtons[i]);
                        
                        if (oButton) {
        
                            aButtons[aButtons.length] = oButton;
        
                        }
                    
                    }
                
                }
        
            }

			return aButtons;
        
        },
        
        
        /**
        * @method removeButton
        * @description Removes the button at the specified index from the 
        * button group.
        * @param {Number} p_nIndex Number specifying the index of the button 
        * to be removed from the button group.
        */
        removeButton: function (p_nIndex) {
        
            var oButton = this.getButton(p_nIndex),
                nButtons,
                i;
            
            if (oButton) {
        
                this.logger.log("Removing button " + oButton.get("id") + ".");
        
                this._buttons.splice(p_nIndex, 1);
                delete m_oButtons[oButton.get("id")];
        
                oButton.removeListener("checkedChange", 
                    this._onButtonCheckedChange);

                oButton.destroy();
        
        
                nButtons = this._buttons.length;
                
                if (nButtons > 0) {
        
                    i = this._buttons.length - 1;
                    
                    do {
        
                        this._buttons[i].index = i;
        
                    }
                    while (i--);
                
                }
        
                this.logger.log("Button " + oButton.get("id") + " removed.");
        
            }
        
        },
        
        
        /**
        * @method getButton
        * @description Returns the button at the specified index.
        * @param {Number} p_nIndex The index of the button to retrieve from the 
        * button group.
        * @return {<a href="YAHOO.widget.Button.html">YAHOO.widget.Button</a>}
        */
        getButton: function (p_nIndex) {
        
            return this._buttons[p_nIndex];
        
        },
        
        
        /**
        * @method getButtons
        * @description Returns an array of the buttons in the button group.
        * @return {Array}
        */
        getButtons: function () {
        
            return this._buttons;
        
        },
        
        
        /**
        * @method getCount
        * @description Returns the number of buttons in the button group.
        * @return {Number}
        */
        getCount: function () {
        
            return this._buttons.length;
        
        },
        
        
        /**
        * @method focus
        * @description Sets focus to the button at the specified index.
        * @param {Number} p_nIndex Number indicating the index of the button 
        * to focus. 
        */
        focus: function (p_nIndex) {
        
            var oButton,
                nButtons,
                i;
        
            if (Lang.isNumber(p_nIndex)) {
        
                oButton = this._buttons[p_nIndex];
                
                if (oButton) {
        
                    oButton.focus();
        
                }
            
            }
            else {
        
                nButtons = this.getCount();
        
                for (i = 0; i < nButtons; i++) {
        
                    oButton = this._buttons[i];
        
                    if (!oButton.get("disabled")) {
        
                        oButton.focus();
                        break;
        
                    }
        
                }
        
            }
        
        },
        
        
        /**
        * @method check
        * @description Checks the button at the specified index.
        * @param {Number} p_nIndex Number indicating the index of the button 
        * to check. 
        */
        check: function (p_nIndex) {
        
            var oButton = this.getButton(p_nIndex);
            
            if (oButton) {
        
                oButton.set("checked", true);
            
            }
        
        },
        
        
        /**
        * @method destroy
        * @description Removes the button group's element from its parent 
        * element and removes all event handlers.
        */
        destroy: function () {
        
            this.logger.log("Destroying...");
        
            var nButtons = this._buttons.length,
                oElement = this.get("element"),
                oParentNode = oElement.parentNode,
                i;
            
            if (nButtons > 0) {
        
                i = this._buttons.length - 1;
        
                do {
        
                    this._buttons[i].destroy();
        
                }
                while (i--);
            
            }
        
            this.logger.log("Removing DOM event handlers.");
        
            Event.purgeElement(oElement);
            
            this.logger.log("Removing from document.");
        
            oParentNode.removeChild(oElement);
        
        },
        
        
        /**
        * @method toString
        * @description Returns a string representing the button group.
        * @return {String}
        */
        toString: function () {
        
            return ("ButtonGroup " + this.get("id"));
        
        }
    
    });

})();