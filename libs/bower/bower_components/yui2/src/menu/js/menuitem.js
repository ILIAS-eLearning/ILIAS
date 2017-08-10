


(function () {

/**
* Creates an item for a menu.
* 
* @param {HTML} p_oObject Markup for the menu item content. The markup is inserted into the DOM as HTML, and should be escaped by the implementor if coming from an external source.
* @param {<a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/level-
* one-html.html#ID-74680021">HTMLLIElement</a>} p_oObject Object specifying 
* the <code>&#60;li&#62;</code> element of the menu item.
* @param {<a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/level-
* one-html.html#ID-38450247">HTMLOptGroupElement</a>} p_oObject Object 
* specifying the <code>&#60;optgroup&#62;</code> element of the menu item.
* @param {<a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/level-
* one-html.html#ID-70901257">HTMLOptionElement</a>} p_oObject Object 
* specifying the <code>&#60;option&#62;</code> element of the menu item.
* @param {Object} p_oConfig Optional. Object literal specifying the 
* configuration for the menu item. See configuration class documentation 
* for more details.
* @class MenuItem
* @constructor
*/
YAHOO.widget.MenuItem = function (p_oObject, p_oConfig) {

    if (p_oObject) {

        if (p_oConfig) {
    
            this.parent = p_oConfig.parent;
            this.value = p_oConfig.value;
            this.id = p_oConfig.id;

        }

        this.init(p_oObject, p_oConfig);

    }

};


var Dom = YAHOO.util.Dom,
    Module = YAHOO.widget.Module,
    Menu = YAHOO.widget.Menu,
    MenuItem = YAHOO.widget.MenuItem,
    CustomEvent = YAHOO.util.CustomEvent,
    UA = YAHOO.env.ua,
    Lang = YAHOO.lang,

    // Private string constants

    _TEXT = "text",
    _HASH = "#",
    _HYPHEN = "-",
    _HELP_TEXT = "helptext",
    _URL = "url",
    _TARGET = "target",
    _EMPHASIS = "emphasis",
    _STRONG_EMPHASIS = "strongemphasis",
    _CHECKED = "checked",
    _SUBMENU = "submenu",
    _DISABLED = "disabled",
    _SELECTED = "selected",
    _HAS_SUBMENU = "hassubmenu",
    _CHECKED_DISABLED = "checked-disabled",
    _HAS_SUBMENU_DISABLED = "hassubmenu-disabled",
    _HAS_SUBMENU_SELECTED = "hassubmenu-selected",
    _CHECKED_SELECTED = "checked-selected",
    _ONCLICK = "onclick",
    _CLASSNAME = "classname",
    _EMPTY_STRING = "",
    _OPTION = "OPTION",
    _OPTGROUP = "OPTGROUP",
    _LI_UPPERCASE = "LI",
    _HREF = "href",
    _SELECT = "SELECT",
    _DIV = "DIV",
    _START_HELP_TEXT = "<em class=\"helptext\">",
    _START_EM = "<em>",
    _END_EM = "</em>",
    _START_STRONG = "<strong>",
    _END_STRONG = "</strong>",
    _PREVENT_CONTEXT_OVERLAP = "preventcontextoverlap",
    _OBJ = "obj",
    _SCOPE = "scope",
    _NONE = "none",
    _VISIBLE = "visible",
    _SPACE = " ",
    _MENUITEM = "MenuItem",
    _CLICK = "click",
    _SHOW = "show",
    _HIDE = "hide",
    _LI_LOWERCASE = "li",
    _ANCHOR_TEMPLATE = "<a href=\"#\"></a>",

    EVENT_TYPES = [
    
        ["mouseOverEvent", "mouseover"],
        ["mouseOutEvent", "mouseout"],
        ["mouseDownEvent", "mousedown"],
        ["mouseUpEvent", "mouseup"],
        ["clickEvent", _CLICK],
        ["keyPressEvent", "keypress"],
        ["keyDownEvent", "keydown"],
        ["keyUpEvent", "keyup"],
        ["focusEvent", "focus"],
        ["blurEvent", "blur"],
        ["destroyEvent", "destroy"]
    
    ],

    TEXT_CONFIG = { 
        key: _TEXT, 
        value: _EMPTY_STRING, 
        validator: Lang.isString, 
        suppressEvent: true 
    }, 

    HELP_TEXT_CONFIG = { 
        key: _HELP_TEXT,
        supercedes: [_TEXT], 
        suppressEvent: true 
    },

    URL_CONFIG = { 
        key: _URL, 
        value: _HASH, 
        suppressEvent: true 
    }, 

    TARGET_CONFIG = { 
        key: _TARGET, 
        suppressEvent: true 
    }, 

    EMPHASIS_CONFIG = { 
        key: _EMPHASIS, 
        value: false, 
        validator: Lang.isBoolean, 
        suppressEvent: true, 
        supercedes: [_TEXT]
    }, 

    STRONG_EMPHASIS_CONFIG = { 
        key: _STRONG_EMPHASIS, 
        value: false, 
        validator: Lang.isBoolean, 
        suppressEvent: true,
        supercedes: [_TEXT]
    },

    CHECKED_CONFIG = { 
        key: _CHECKED, 
        value: false, 
        validator: Lang.isBoolean, 
        suppressEvent: true, 
        supercedes: [_DISABLED, _SELECTED]
    }, 

    SUBMENU_CONFIG = { 
        key: _SUBMENU,
        suppressEvent: true,
        supercedes: [_DISABLED, _SELECTED]
    },

    DISABLED_CONFIG = { 
        key: _DISABLED, 
        value: false, 
        validator: Lang.isBoolean, 
        suppressEvent: true,
        supercedes: [_TEXT, _SELECTED]
    },

    SELECTED_CONFIG = { 
        key: _SELECTED, 
        value: false, 
        validator: Lang.isBoolean, 
        suppressEvent: true
    },

    ONCLICK_CONFIG = { 
        key: _ONCLICK,
        suppressEvent: true
    },

    CLASS_NAME_CONFIG = { 
        key: _CLASSNAME, 
        value: null, 
        validator: Lang.isString,
        suppressEvent: true
    },
    
    KEY_LISTENER_CONFIG = {
        key: "keylistener", 
        value: null, 
        suppressEvent: true
    },

    m_oMenuItemTemplate = null,

    CLASS_NAMES = {};


/**
* @method getClassNameForState
* @description Returns a class name for the specified prefix and state.  If the class name does not 
* yet exist, it is created and stored in the CLASS_NAMES object to increase performance.
* @private
* @param {String} prefix String representing the prefix for the class name
* @param {String} state String representing a state - "disabled," "checked," etc.
*/  
var getClassNameForState = function (prefix, state) {

    var oClassNames = CLASS_NAMES[prefix];
    
    if (!oClassNames) {
        CLASS_NAMES[prefix] = {};
        oClassNames = CLASS_NAMES[prefix];
    }


    var sClassName = oClassNames[state];

    if (!sClassName) {
        sClassName = prefix + _HYPHEN + state;
        oClassNames[state] = sClassName;
    }

    return sClassName;
    
};


/**
* @method addClassNameForState
* @description Applies a class name to a MenuItem instance's &#60;LI&#62; and &#60;A&#62; elements
* that represents a MenuItem's state - "disabled," "checked," etc.
* @private
* @param {String} state String representing a state - "disabled," "checked," etc.
*/  
var addClassNameForState = function (state) {

    Dom.addClass(this.element, getClassNameForState(this.CSS_CLASS_NAME, state));
    Dom.addClass(this._oAnchor, getClassNameForState(this.CSS_LABEL_CLASS_NAME, state));

};

/**
* @method removeClassNameForState
* @description Removes a class name from a MenuItem instance's &#60;LI&#62; and &#60;A&#62; elements
* that represents a MenuItem's state - "disabled," "checked," etc.
* @private
* @param {String} state String representing a state - "disabled," "checked," etc.
*/  
var removeClassNameForState = function (state) {

    Dom.removeClass(this.element, getClassNameForState(this.CSS_CLASS_NAME, state));
    Dom.removeClass(this._oAnchor, getClassNameForState(this.CSS_LABEL_CLASS_NAME, state));

};


MenuItem.prototype = {

    /**
    * @property CSS_CLASS_NAME
    * @description String representing the CSS class(es) to be applied to the 
    * <code>&#60;li&#62;</code> element of the menu item.
    * @default "yuimenuitem"
    * @final
    * @type String
    */
    CSS_CLASS_NAME: "yuimenuitem",


    /**
    * @property CSS_LABEL_CLASS_NAME
    * @description String representing the CSS class(es) to be applied to the 
    * menu item's <code>&#60;a&#62;</code> element.
    * @default "yuimenuitemlabel"
    * @final
    * @type String
    */
    CSS_LABEL_CLASS_NAME: "yuimenuitemlabel",


    /**
    * @property SUBMENU_TYPE
    * @description Object representing the type of menu to instantiate and 
    * add when parsing the child nodes of the menu item's source HTML element.
    * @final
    * @type YAHOO.widget.Menu
    */
    SUBMENU_TYPE: null,



    // Private member variables
    

    /**
    * @property _oAnchor
    * @description Object reference to the menu item's 
    * <code>&#60;a&#62;</code> element.
    * @default null 
    * @private
    * @type <a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/level-
    * one-html.html#ID-48250443">HTMLAnchorElement</a>
    */
    _oAnchor: null,
    
    
    /**
    * @property _oHelpTextEM
    * @description Object reference to the menu item's help text 
    * <code>&#60;em&#62;</code> element.
    * @default null
    * @private
    * @type <a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/level-
    * one-html.html#ID-58190037">HTMLElement</a>
    */
    _oHelpTextEM: null,
    
    
    /**
    * @property _oSubmenu
    * @description Object reference to the menu item's submenu.
    * @default null
    * @private
    * @type YAHOO.widget.Menu
    */
    _oSubmenu: null,


    /** 
    * @property _oOnclickAttributeValue
    * @description Object reference to the menu item's current value for the 
    * "onclick" configuration attribute.
    * @default null
    * @private
    * @type Object
    */
    _oOnclickAttributeValue: null,


    /**
    * @property _sClassName
    * @description The current value of the "classname" configuration attribute.
    * @default null
    * @private
    * @type String
    */
    _sClassName: null,



    // Public properties


    /**
    * @property constructor
    * @description Object reference to the menu item's constructor function.
    * @default YAHOO.widget.MenuItem
    * @type YAHOO.widget.MenuItem
    */
    constructor: MenuItem,


    /**
    * @property index
    * @description Number indicating the ordinal position of the menu item in 
    * its group.
    * @default null
    * @type Number
    */
    index: null,


    /**
    * @property groupIndex
    * @description Number indicating the index of the group to which the menu 
    * item belongs.
    * @default null
    * @type Number
    */
    groupIndex: null,


    /**
    * @property parent
    * @description Object reference to the menu item's parent menu.
    * @default null
    * @type YAHOO.widget.Menu
    */
    parent: null,


    /**
    * @property element
    * @description Object reference to the menu item's 
    * <code>&#60;li&#62;</code> element.
    * @default <a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/level
    * -one-html.html#ID-74680021">HTMLLIElement</a>
    * @type <a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/level-
    * one-html.html#ID-74680021">HTMLLIElement</a>
    */
    element: null,


    /**
    * @property srcElement
    * @description Object reference to the HTML element (either 
    * <code>&#60;li&#62;</code>, <code>&#60;optgroup&#62;</code> or 
    * <code>&#60;option&#62;</code>) used create the menu item.
    * @default <a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/
    * level-one-html.html#ID-74680021">HTMLLIElement</a>|<a href="http://www.
    * w3.org/TR/2000/WD-DOM-Level-1-20000929/level-one-html.html#ID-38450247"
    * >HTMLOptGroupElement</a>|<a href="http://www.w3.org/TR/2000/WD-DOM-
    * Level-1-20000929/level-one-html.html#ID-70901257">HTMLOptionElement</a>
    * @type <a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/level-
    * one-html.html#ID-74680021">HTMLLIElement</a>|<a href="http://www.w3.
    * org/TR/2000/WD-DOM-Level-1-20000929/level-one-html.html#ID-38450247">
    * HTMLOptGroupElement</a>|<a href="http://www.w3.org/TR/2000/WD-DOM-
    * Level-1-20000929/level-one-html.html#ID-70901257">HTMLOptionElement</a>
    */
    srcElement: null,


    /**
    * @property value
    * @description Object reference to the menu item's value.
    * @default null
    * @type Object
    */
    value: null,


    /**
    * @property browser
    * @deprecated Use YAHOO.env.ua
    * @description String representing the browser.
    * @type String
    */
    browser: Module.prototype.browser,


    /**
    * @property id
    * @description Id of the menu item's root <code>&#60;li&#62;</code> 
    * element.  This property should be set via the constructor using the 
    * configuration object literal.  If an id is not specified, then one will 
    * be created using the "generateId" method of the Dom utility.
    * @default null
    * @type String
    */
    id: null,



    // Events


    /**
    * @event destroyEvent
    * @description Fires when the menu item's <code>&#60;li&#62;</code> 
    * element is removed from its parent <code>&#60;ul&#62;</code> element.
    * @type YAHOO.util.CustomEvent
    */


    /**
    * @event mouseOverEvent
    * @description Fires when the mouse has entered the menu item.  Passes 
    * back the DOM Event object as an argument.
    * @type YAHOO.util.CustomEvent
    */


    /**
    * @event mouseOutEvent
    * @description Fires when the mouse has left the menu item.  Passes back 
    * the DOM Event object as an argument.
    * @type YAHOO.util.CustomEvent
    */


    /**
    * @event mouseDownEvent
    * @description Fires when the user mouses down on the menu item.  Passes 
    * back the DOM Event object as an argument.
    * @type YAHOO.util.CustomEvent
    */


    /**
    * @event mouseUpEvent
    * @description Fires when the user releases a mouse button while the mouse 
    * is over the menu item.  Passes back the DOM Event object as an argument.
    * @type YAHOO.util.CustomEvent
    */


    /**
    * @event clickEvent
    * @description Fires when the user clicks the on the menu item.  Passes 
    * back the DOM Event object as an argument.
    * @type YAHOO.util.CustomEvent
    */


    /**
    * @event keyPressEvent
    * @description Fires when the user presses an alphanumeric key when the 
    * menu item has focus.  Passes back the DOM Event object as an argument.
    * @type YAHOO.util.CustomEvent
    */


    /**
    * @event keyDownEvent
    * @description Fires when the user presses a key when the menu item has 
    * focus.  Passes back the DOM Event object as an argument.
    * @type YAHOO.util.CustomEvent
    */


    /**
    * @event keyUpEvent
    * @description Fires when the user releases a key when the menu item has 
    * focus.  Passes back the DOM Event object as an argument.
    * @type YAHOO.util.CustomEvent
    */


    /**
    * @event focusEvent
    * @description Fires when the menu item receives focus.
    * @type YAHOO.util.CustomEvent
    */


    /**
    * @event blurEvent
    * @description Fires when the menu item loses the input focus.
    * @type YAHOO.util.CustomEvent
    */


    /**
    * @method init
    * @description The MenuItem class's initialization method. This method is 
    * automatically called by the constructor, and sets up all DOM references 
    * for pre-existing markup, and creates required markup if it is not 
    * already present.
    * @param {HTML} p_oObject Markup for the menu item content. The markup is inserted into the DOM as HTML, and should be escaped by the implementor if coming from an external source.
    * @param {<a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/level-
    * one-html.html#ID-74680021">HTMLLIElement</a>} p_oObject Object specifying 
    * the <code>&#60;li&#62;</code> element of the menu item.
    * @param {<a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/level-
    * one-html.html#ID-38450247">HTMLOptGroupElement</a>} p_oObject Object 
    * specifying the <code>&#60;optgroup&#62;</code> element of the menu item.
    * @param {<a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/level-
    * one-html.html#ID-70901257">HTMLOptionElement</a>} p_oObject Object 
    * specifying the <code>&#60;option&#62;</code> element of the menu item.
    * @param {Object} p_oConfig Optional. Object literal specifying the 
    * configuration for the menu item. See configuration class documentation 
    * for more details.
    */
    init: function (p_oObject, p_oConfig) {


        if (!this.SUBMENU_TYPE) {
    
            this.SUBMENU_TYPE = Menu;
    
        }


        // Create the config object

        this.cfg = new YAHOO.util.Config(this);

        this.initDefaultConfig();

        var oConfig = this.cfg,
            sURL = _HASH,
            oCustomEvent,
            aEventData,
            oAnchor,
            sTarget,
            sText,
            sId,
            i;


        if (Lang.isString(p_oObject)) {

            this._createRootNodeStructure();

            oConfig.queueProperty(_TEXT, p_oObject);

        }
        else if (p_oObject && p_oObject.tagName) {

            switch(p_oObject.tagName.toUpperCase()) {

                case _OPTION:

                    this._createRootNodeStructure();

                    oConfig.queueProperty(_TEXT, p_oObject.text);
                    oConfig.queueProperty(_DISABLED, p_oObject.disabled);

                    this.value = p_oObject.value;

                    this.srcElement = p_oObject;

                break;

                case _OPTGROUP:

                    this._createRootNodeStructure();

                    oConfig.queueProperty(_TEXT, p_oObject.label);
                    oConfig.queueProperty(_DISABLED, p_oObject.disabled);

                    this.srcElement = p_oObject;

                    this._initSubTree();

                break;

                case _LI_UPPERCASE:

                    // Get the anchor node (if it exists)
                    
                    oAnchor = Dom.getFirstChild(p_oObject);


                    // Capture the "text" and/or the "URL"

                    if (oAnchor) {

                        sURL = oAnchor.getAttribute(_HREF, 2);
                        sTarget = oAnchor.getAttribute(_TARGET);

                        sText = oAnchor.innerHTML;

                    }

                    this.srcElement = p_oObject;
                    this.element = p_oObject;
                    this._oAnchor = oAnchor;

                    /*
                        Set these properties silently to sync up the 
                        configuration object without making changes to the 
                        element's DOM
                    */ 

                    oConfig.setProperty(_TEXT, sText, true);
                    oConfig.setProperty(_URL, sURL, true);
                    oConfig.setProperty(_TARGET, sTarget, true);

                    this._initSubTree();

                break;

            }            

        }


        if (this.element) {

            sId = (this.srcElement || this.element).id;

            if (!sId) {

                sId = this.id || Dom.generateId();

                this.element.id = sId;

            }

            this.id = sId;


            Dom.addClass(this.element, this.CSS_CLASS_NAME);
            Dom.addClass(this._oAnchor, this.CSS_LABEL_CLASS_NAME);


            i = EVENT_TYPES.length - 1;

            do {

                aEventData = EVENT_TYPES[i];

                oCustomEvent = this.createEvent(aEventData[1]);
                oCustomEvent.signature = CustomEvent.LIST;
                
                this[aEventData[0]] = oCustomEvent;

            }
            while (i--);


            if (p_oConfig) {
    
                oConfig.applyConfig(p_oConfig);
    
            }        

            oConfig.fireQueue();

        }

    },



    // Private methods

    /**
    * @method _createRootNodeStructure
    * @description Creates the core DOM structure for the menu item.
    * @private
    */
    _createRootNodeStructure: function () {

        var oElement,
            oAnchor;

        if (!m_oMenuItemTemplate) {

            m_oMenuItemTemplate = document.createElement(_LI_LOWERCASE);
            m_oMenuItemTemplate.innerHTML = _ANCHOR_TEMPLATE;

        }

        oElement = m_oMenuItemTemplate.cloneNode(true);
        oElement.className = this.CSS_CLASS_NAME;

        oAnchor = oElement.firstChild;
        oAnchor.className = this.CSS_LABEL_CLASS_NAME;

        this.element = oElement;
        this._oAnchor = oAnchor;

    },


    /**
    * @method _initSubTree
    * @description Iterates the source element's childNodes collection and uses 
    * the child nodes to instantiate other menus.
    * @private
    */
    _initSubTree: function () {

        var oSrcEl = this.srcElement,
            oConfig = this.cfg,
            oNode,
            aOptions,
            nOptions,
            oMenu,
            n;


        if (oSrcEl.childNodes.length > 0) {

            if (this.parent.lazyLoad && this.parent.srcElement && 
                this.parent.srcElement.tagName.toUpperCase() == _SELECT) {

                oConfig.setProperty(
                        _SUBMENU, 
                        { id: Dom.generateId(), itemdata: oSrcEl.childNodes }
                    );

            }
            else {

                oNode = oSrcEl.firstChild;
                aOptions = [];
    
                do {
    
                    if (oNode && oNode.tagName) {
    
                        switch(oNode.tagName.toUpperCase()) {
                
                            case _DIV:
                
                                oConfig.setProperty(_SUBMENU, oNode);
                
                            break;
         
                            case _OPTION:
        
                                aOptions[aOptions.length] = oNode;
        
                            break;
               
                        }
                    
                    }
                
                }        
                while((oNode = oNode.nextSibling));
    
    
                nOptions = aOptions.length;
    
                if (nOptions > 0) {
    
                    oMenu = new this.SUBMENU_TYPE(Dom.generateId());
                    
                    oConfig.setProperty(_SUBMENU, oMenu);
    
                    for(n=0; n<nOptions; n++) {
        
                        oMenu.addItem((new oMenu.ITEM_TYPE(aOptions[n])));
        
                    }
        
                }
            
            }

        }

    },



    // Event handlers for configuration properties


    /**
    * @method configText
    * @description Event handler for when the "text" configuration property of 
    * the menu item changes.
    * @param {String} p_sType String representing the name of the event that 
    * was fired.
    * @param {Array} p_aArgs Array of arguments sent when the event was fired.
    * @param {YAHOO.widget.MenuItem} p_oItem Object representing the menu item
    * that fired the event.
    */
    configText: function (p_sType, p_aArgs, p_oItem) {

        var sText = p_aArgs[0],
            oConfig = this.cfg,
            oAnchor = this._oAnchor,
            sHelpText = oConfig.getProperty(_HELP_TEXT),
            sHelpTextHTML = _EMPTY_STRING,
            sEmphasisStartTag = _EMPTY_STRING,
            sEmphasisEndTag = _EMPTY_STRING;


        if (sText) {


            if (sHelpText) {
                    
                sHelpTextHTML = _START_HELP_TEXT + sHelpText + _END_EM;
            
            }


            if (oConfig.getProperty(_EMPHASIS)) {

                sEmphasisStartTag = _START_EM;
                sEmphasisEndTag = _END_EM;

            }


            if (oConfig.getProperty(_STRONG_EMPHASIS)) {

                sEmphasisStartTag = _START_STRONG;
                sEmphasisEndTag = _END_STRONG;
            
            }


            oAnchor.innerHTML = (sEmphasisStartTag + sText + sEmphasisEndTag + sHelpTextHTML);

        }

    },


    /**
    * @method configHelpText
    * @description Event handler for when the "helptext" configuration property 
    * of the menu item changes.
    * @param {String} p_sType String representing the name of the event that 
    * was fired.
    * @param {Array} p_aArgs Array of arguments sent when the event was fired.
    * @param {YAHOO.widget.MenuItem} p_oItem Object representing the menu item
    * that fired the event.
    */    
    configHelpText: function (p_sType, p_aArgs, p_oItem) {

        this.cfg.refireEvent(_TEXT);

    },


    /**
    * @method configURL
    * @description Event handler for when the "url" configuration property of 
    * the menu item changes.
    * @param {String} p_sType String representing the name of the event that 
    * was fired.
    * @param {Array} p_aArgs Array of arguments sent when the event was fired.
    * @param {YAHOO.widget.MenuItem} p_oItem Object representing the menu item
    * that fired the event.
    */    
    configURL: function (p_sType, p_aArgs, p_oItem) {

        var sURL = p_aArgs[0];

        if (!sURL) {

            sURL = _HASH;

        }

        var oAnchor = this._oAnchor;

        if (UA.opera) {

            oAnchor.removeAttribute(_HREF);
        
        }

        oAnchor.setAttribute(_HREF, sURL);

    },


    /**
    * @method configTarget
    * @description Event handler for when the "target" configuration property 
    * of the menu item changes.  
    * @param {String} p_sType String representing the name of the event that 
    * was fired.
    * @param {Array} p_aArgs Array of arguments sent when the event was fired.
    * @param {YAHOO.widget.MenuItem} p_oItem Object representing the menu item
    * that fired the event.
    */    
    configTarget: function (p_sType, p_aArgs, p_oItem) {

        var sTarget = p_aArgs[0],
            oAnchor = this._oAnchor;

        if (sTarget && sTarget.length > 0) {

            oAnchor.setAttribute(_TARGET, sTarget);

        }
        else {

            oAnchor.removeAttribute(_TARGET);
        
        }

    },


    /**
    * @method configEmphasis
    * @description Event handler for when the "emphasis" configuration property
    * of the menu item changes.
    * @param {String} p_sType String representing the name of the event that 
    * was fired.
    * @param {Array} p_aArgs Array of arguments sent when the event was fired.
    * @param {YAHOO.widget.MenuItem} p_oItem Object representing the menu item
    * that fired the event.
    */    
    configEmphasis: function (p_sType, p_aArgs, p_oItem) {

        var bEmphasis = p_aArgs[0],
            oConfig = this.cfg;


        if (bEmphasis && oConfig.getProperty(_STRONG_EMPHASIS)) {

            oConfig.setProperty(_STRONG_EMPHASIS, false);

        }


        oConfig.refireEvent(_TEXT);

    },


    /**
    * @method configStrongEmphasis
    * @description Event handler for when the "strongemphasis" configuration 
    * property of the menu item changes.
    * @param {String} p_sType String representing the name of the event that 
    * was fired.
    * @param {Array} p_aArgs Array of arguments sent when the event was fired.
    * @param {YAHOO.widget.MenuItem} p_oItem Object representing the menu item
    * that fired the event.
    */    
    configStrongEmphasis: function (p_sType, p_aArgs, p_oItem) {

        var bStrongEmphasis = p_aArgs[0],
            oConfig = this.cfg;


        if (bStrongEmphasis && oConfig.getProperty(_EMPHASIS)) {

            oConfig.setProperty(_EMPHASIS, false);

        }

        oConfig.refireEvent(_TEXT);

    },


    /**
    * @method configChecked
    * @description Event handler for when the "checked" configuration property 
    * of the menu item changes. 
    * @param {String} p_sType String representing the name of the event that 
    * was fired.
    * @param {Array} p_aArgs Array of arguments sent when the event was fired.
    * @param {YAHOO.widget.MenuItem} p_oItem Object representing the menu item
    * that fired the event.
    */    
    configChecked: function (p_sType, p_aArgs, p_oItem) {

        var bChecked = p_aArgs[0],
            oConfig = this.cfg;


        if (bChecked) {

            addClassNameForState.call(this, _CHECKED);

        }
        else {

            removeClassNameForState.call(this, _CHECKED);
        }


        oConfig.refireEvent(_TEXT);


        if (oConfig.getProperty(_DISABLED)) {

            oConfig.refireEvent(_DISABLED);

        }


        if (oConfig.getProperty(_SELECTED)) {

            oConfig.refireEvent(_SELECTED);

        }

    },



    /**
    * @method configDisabled
    * @description Event handler for when the "disabled" configuration property 
    * of the menu item changes. 
    * @param {String} p_sType String representing the name of the event that 
    * was fired.
    * @param {Array} p_aArgs Array of arguments sent when the event was fired.
    * @param {YAHOO.widget.MenuItem} p_oItem Object representing the menu item
    * that fired the event.
    */    
    configDisabled: function (p_sType, p_aArgs, p_oItem) {

        var bDisabled = p_aArgs[0],
            oConfig = this.cfg,
            oSubmenu = oConfig.getProperty(_SUBMENU),
            bChecked = oConfig.getProperty(_CHECKED);


        if (bDisabled) {

            if (oConfig.getProperty(_SELECTED)) {

                oConfig.setProperty(_SELECTED, false);

            }


            addClassNameForState.call(this, _DISABLED);


            if (oSubmenu) {

                addClassNameForState.call(this, _HAS_SUBMENU_DISABLED);
            
            }
            

            if (bChecked) {

                addClassNameForState.call(this, _CHECKED_DISABLED);

            }

        }
        else {

            removeClassNameForState.call(this, _DISABLED);


            if (oSubmenu) {

                removeClassNameForState.call(this, _HAS_SUBMENU_DISABLED);
            
            }
            

            if (bChecked) {

                removeClassNameForState.call(this, _CHECKED_DISABLED);

            }

        }

    },


    /**
    * @method configSelected
    * @description Event handler for when the "selected" configuration property 
    * of the menu item changes. 
    * @param {String} p_sType String representing the name of the event that 
    * was fired.
    * @param {Array} p_aArgs Array of arguments sent when the event was fired.
    * @param {YAHOO.widget.MenuItem} p_oItem Object representing the menu item
    * that fired the event.
    */    
    configSelected: function (p_sType, p_aArgs, p_oItem) {

        var oConfig = this.cfg,
            oAnchor = this._oAnchor,
            
            bSelected = p_aArgs[0],
            bChecked = oConfig.getProperty(_CHECKED),
            oSubmenu = oConfig.getProperty(_SUBMENU);


        if (UA.opera) {

            oAnchor.blur();
        
        }


        if (bSelected && !oConfig.getProperty(_DISABLED)) {

            addClassNameForState.call(this, _SELECTED);


            if (oSubmenu) {

                addClassNameForState.call(this, _HAS_SUBMENU_SELECTED);
            
            }


            if (bChecked) {

                addClassNameForState.call(this, _CHECKED_SELECTED);

            }

        }
        else {

            removeClassNameForState.call(this, _SELECTED);


            if (oSubmenu) {

                removeClassNameForState.call(this, _HAS_SUBMENU_SELECTED);
            
            }


            if (bChecked) {

                removeClassNameForState.call(this, _CHECKED_SELECTED);

            }

        }


        if (this.hasFocus() && UA.opera) {
        
            oAnchor.focus();
        
        }

    },


    /**
    * @method _onSubmenuBeforeHide
    * @description "beforehide" Custom Event handler for a submenu.
    * @private
    * @param {String} p_sType String representing the name of the event that 
    * was fired.
    * @param {Array} p_aArgs Array of arguments sent when the event was fired.
    */
    _onSubmenuBeforeHide: function (p_sType, p_aArgs) {

        var oItem = this.parent,
            oMenu;

        function onHide() {

            oItem._oAnchor.blur();
            oMenu.beforeHideEvent.unsubscribe(onHide);
        
        }


        if (oItem.hasFocus()) {

            oMenu = oItem.parent;

            oMenu.beforeHideEvent.subscribe(onHide);
        
        }
    
    },


    /**
    * @method configSubmenu
    * @description Event handler for when the "submenu" configuration property 
    * of the menu item changes. 
    * @param {String} p_sType String representing the name of the event that 
    * was fired.
    * @param {Array} p_aArgs Array of arguments sent when the event was fired.
    * @param {YAHOO.widget.MenuItem} p_oItem Object representing the menu item
    * that fired the event.
    */
    configSubmenu: function (p_sType, p_aArgs, p_oItem) {

        var oSubmenu = p_aArgs[0],
            oConfig = this.cfg,
            bLazyLoad = this.parent && this.parent.lazyLoad,
            oMenu,
            sSubmenuId,
            oSubmenuConfig;


        if (oSubmenu) {

            if (oSubmenu instanceof Menu) {

                oMenu = oSubmenu;
                oMenu.parent = this;
                oMenu.lazyLoad = bLazyLoad;

            }
            else if (Lang.isObject(oSubmenu) && oSubmenu.id && !oSubmenu.nodeType) {

                sSubmenuId = oSubmenu.id;
                oSubmenuConfig = oSubmenu;

                oSubmenuConfig.lazyload = bLazyLoad;
                oSubmenuConfig.parent = this;

                oMenu = new this.SUBMENU_TYPE(sSubmenuId, oSubmenuConfig);


                // Set the value of the property to the Menu instance

                oConfig.setProperty(_SUBMENU, oMenu, true);

            }
            else {

                oMenu = new this.SUBMENU_TYPE(oSubmenu, { lazyload: bLazyLoad, parent: this });


                // Set the value of the property to the Menu instance
                
                oConfig.setProperty(_SUBMENU, oMenu, true);

            }


            if (oMenu) {

                oMenu.cfg.setProperty(_PREVENT_CONTEXT_OVERLAP, true);

                addClassNameForState.call(this, _HAS_SUBMENU);


                if (oConfig.getProperty(_URL) === _HASH) {
                
                    oConfig.setProperty(_URL, (_HASH + oMenu.id));
                
                }


                this._oSubmenu = oMenu;


                if (UA.opera) {
                
                    oMenu.beforeHideEvent.subscribe(this._onSubmenuBeforeHide);               
                
                }
            
            }

        }
        else {

            removeClassNameForState.call(this, _HAS_SUBMENU);

            if (this._oSubmenu) {

                this._oSubmenu.destroy();

            }

        }


        if (oConfig.getProperty(_DISABLED)) {

            oConfig.refireEvent(_DISABLED);

        }


        if (oConfig.getProperty(_SELECTED)) {

            oConfig.refireEvent(_SELECTED);

        }

    },


    /**
    * @method configOnClick
    * @description Event handler for when the "onclick" configuration property 
    * of the menu item changes. 
    * @param {String} p_sType String representing the name of the event that 
    * was fired.
    * @param {Array} p_aArgs Array of arguments sent when the event was fired.
    * @param {YAHOO.widget.MenuItem} p_oItem Object representing the menu item
    * that fired the event.
    */
    configOnClick: function (p_sType, p_aArgs, p_oItem) {

        var oObject = p_aArgs[0];

        /*
            Remove any existing listeners if a "click" event handler has 
            already been specified.
        */

        if (this._oOnclickAttributeValue && (this._oOnclickAttributeValue != oObject)) {

            this.clickEvent.unsubscribe(this._oOnclickAttributeValue.fn, 
                                this._oOnclickAttributeValue.obj);

            this._oOnclickAttributeValue = null;

        }


        if (!this._oOnclickAttributeValue && Lang.isObject(oObject) && 
            Lang.isFunction(oObject.fn)) {
            
            this.clickEvent.subscribe(oObject.fn, 
                ((_OBJ in oObject) ? oObject.obj : this), 
                ((_SCOPE in oObject) ? oObject.scope : null) );

            this._oOnclickAttributeValue = oObject;

        }
    
    },


    /**
    * @method configClassName
    * @description Event handler for when the "classname" configuration 
    * property of a menu item changes.
    * @param {String} p_sType String representing the name of the event that 
    * was fired.
    * @param {Array} p_aArgs Array of arguments sent when the event was fired.
    * @param {YAHOO.widget.MenuItem} p_oItem Object representing the menu item
    * that fired the event.
    */
    configClassName: function (p_sType, p_aArgs, p_oItem) {
    
        var sClassName = p_aArgs[0];
    
        if (this._sClassName) {
    
            Dom.removeClass(this.element, this._sClassName);
    
        }
    
        Dom.addClass(this.element, sClassName);
        this._sClassName = sClassName;
    
    },


    /**
    * @method _dispatchClickEvent
    * @description Dispatches a DOM "click" event to the anchor element of a 
    * MenuItem instance.
    * @private	
    */
    _dispatchClickEvent: function () {

        var oMenuItem = this,
            oAnchor;

        if (!oMenuItem.cfg.getProperty(_DISABLED)) {
            oAnchor = Dom.getFirstChild(oMenuItem.element);

            //	Dispatch a "click" event to the MenuItem's anchor so that its
            //	"click" event handlers will get called in response to the user 
            //	pressing the keyboard shortcut defined by the "keylistener"
            //	configuration property.

            this._dispatchDOMClick(oAnchor);
        }
    },

    /**
     * Utility method to dispatch a DOM click event on the HTMLElement passed in
     *
     * @method _dispatchDOMClick
     * @protected
     * @param {HTMLElement} el
     */    
    _dispatchDOMClick : function(el) {
        var oEvent;

        // Choose the standards path for IE9
        if (UA.ie && UA.ie < 9) {
            el.fireEvent(_ONCLICK);
        } else {
            if ((UA.gecko && UA.gecko >= 1.9) || UA.opera || UA.webkit) {
                oEvent = document.createEvent("HTMLEvents");
                oEvent.initEvent(_CLICK, true, true);
            } else {
                oEvent = document.createEvent("MouseEvents");
                oEvent.initMouseEvent(_CLICK, true, true, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
            }
            el.dispatchEvent(oEvent);
        }
    },

    /**
    * @method _createKeyListener
    * @description "show" event handler for a Menu instance - responsible for 
    * setting up the KeyListener instance for a MenuItem.
    * @private	
    * @param {String} type String representing the name of the event that 
    * was fired.
    * @param {Array} args Array of arguments sent when the event was fired.
    * @param {Array} keyData Array of arguments sent when the event was fired.
    */
    _createKeyListener: function (type, args, keyData) {

        var oMenuItem = this,
            oMenu = oMenuItem.parent;

        var oKeyListener = new YAHOO.util.KeyListener(
                                        oMenu.element.ownerDocument, 
                                        keyData, 
                                        {
                                            fn: oMenuItem._dispatchClickEvent, 
                                            scope: oMenuItem, 
                                            correctScope: true });


        if (oMenu.cfg.getProperty(_VISIBLE)) {
            oKeyListener.enable();
        }


        oMenu.subscribe(_SHOW, oKeyListener.enable, null, oKeyListener);
        oMenu.subscribe(_HIDE, oKeyListener.disable, null, oKeyListener);
        
        oMenuItem._keyListener = oKeyListener;
        
        oMenu.unsubscribe(_SHOW, oMenuItem._createKeyListener, keyData);
        
    },


    /**
    * @method configKeyListener
    * @description Event handler for when the "keylistener" configuration 
    * property of a menu item changes.
    * @param {String} p_sType String representing the name of the event that 
    * was fired.
    * @param {Array} p_aArgs Array of arguments sent when the event was fired.
    */
    configKeyListener: function (p_sType, p_aArgs) {

        var oKeyData = p_aArgs[0],
            oMenuItem = this,
            oMenu = oMenuItem.parent;

        if (oMenuItem._keyData) {

            //	Unsubscribe from the "show" event in case the keylistener 
            //	config was changed before the Menu was ever made visible.

            oMenu.unsubscribe(_SHOW, 
                    oMenuItem._createKeyListener, oMenuItem._keyData);

            oMenuItem._keyData = null;					
                    
        }


        //	Tear down for the previous value of the "keylistener" property

        if (oMenuItem._keyListener) {

            oMenu.unsubscribe(_SHOW, oMenuItem._keyListener.enable);
            oMenu.unsubscribe(_HIDE, oMenuItem._keyListener.disable);

            oMenuItem._keyListener.disable();
            oMenuItem._keyListener = null;

        }


        if (oKeyData) {
    
            oMenuItem._keyData = oKeyData;

            //	Defer the creation of the KeyListener instance until the 
            //	parent Menu is visible.  This is necessary since the 
            //	KeyListener instance needs to be bound to the document the 
            //	Menu has been rendered into.  Deferring creation of the 
            //	KeyListener instance also improves performance.

            oMenu.subscribe(_SHOW, oMenuItem._createKeyListener, 
                oKeyData, oMenuItem);
        }
    
    },


    // Public methods


    /**
    * @method initDefaultConfig
    * @description Initializes an item's configurable properties.
    */
    initDefaultConfig : function () {

        var oConfig = this.cfg;


        // Define the configuration attributes

        /**
        * @config text
        * @description String or markup specifying the text label for the menu item.  
        * When building a menu from existing HTML the value of this property
        * will be interpreted from the menu's markup. The text is inserted into the DOM as HTML, and should be escaped by the implementor if coming from an external source.
        * @default ""
        * @type HTML
        */
        oConfig.addProperty(
            TEXT_CONFIG.key, 
            { 
                handler: this.configText, 
                value: TEXT_CONFIG.value, 
                validator: TEXT_CONFIG.validator, 
                suppressEvent: TEXT_CONFIG.suppressEvent 
            }
        );
        

        /**
        * @config helptext
        * @description String or markup specifying additional instructional text to 
        * accompany the text for the menu item. The helptext is inserted into the DOM as HTML, and should be escaped by the implementor if coming from an external source.
        * @deprecated Use "text" configuration property to add help text markup.  
        * For example: <code>oMenuItem.cfg.setProperty("text", "Copy &#60;em 
        * class=\"helptext\"&#62;Ctrl + C&#60;/em&#62;");</code>
        * @default null
        * @type HTML|<a href="http://www.w3.org/TR/
        * 2000/WD-DOM-Level-1-20000929/level-one-html.html#ID-58190037">
        * HTMLElement</a>
        */
        oConfig.addProperty(
            HELP_TEXT_CONFIG.key,
            {
                handler: this.configHelpText, 
                supercedes: HELP_TEXT_CONFIG.supercedes,
                suppressEvent: HELP_TEXT_CONFIG.suppressEvent 
            }
        );


        /**
        * @config url
        * @description String specifying the URL for the menu item's anchor's 
        * "href" attribute.  When building a menu from existing HTML the value 
        * of this property will be interpreted from the menu's markup. Markup for the menu item content. The url is inserted into the DOM as an attribute value, and should be escaped by the implementor if coming from an external source.
        * @default "#"
        * @type String
        */        
        oConfig.addProperty(
            URL_CONFIG.key, 
            {
                handler: this.configURL, 
                value: URL_CONFIG.value, 
                suppressEvent: URL_CONFIG.suppressEvent
            }
        );


        /**
        * @config target
        * @description String specifying the value for the "target" attribute 
        * of the menu item's anchor element. <strong>Specifying a target will 
        * require the user to click directly on the menu item's anchor node in
        * order to cause the browser to navigate to the specified URL.</strong> 
        * When building a menu from existing HTML the value of this property 
        * will be interpreted from the menu's markup. The target is inserted into the DOM as an attribute value, and should be escaped by the implementor if coming from an external source.
        * @default null
        * @type String
        */        
        oConfig.addProperty(
            TARGET_CONFIG.key, 
            {
                handler: this.configTarget, 
                suppressEvent: TARGET_CONFIG.suppressEvent
            }
        );


        /**
        * @config emphasis
        * @description Boolean indicating if the text of the menu item will be 
        * rendered with emphasis.
        * @deprecated Use the "text" configuration property to add emphasis.  
        * For example: <code>oMenuItem.cfg.setProperty("text", "&#60;em&#62;Some 
        * Text&#60;/em&#62;");</code>
        * @default false
        * @type Boolean
        */
        oConfig.addProperty(
            EMPHASIS_CONFIG.key, 
            { 
                handler: this.configEmphasis, 
                value: EMPHASIS_CONFIG.value, 
                validator: EMPHASIS_CONFIG.validator, 
                suppressEvent: EMPHASIS_CONFIG.suppressEvent,
                supercedes: EMPHASIS_CONFIG.supercedes
            }
        );


        /**
        * @config strongemphasis
        * @description Boolean indicating if the text of the menu item will be 
        * rendered with strong emphasis.
        * @deprecated Use the "text" configuration property to add strong emphasis.  
        * For example: <code>oMenuItem.cfg.setProperty("text", "&#60;strong&#62; 
        * Some Text&#60;/strong&#62;");</code>
        * @default false
        * @type Boolean
        */
        oConfig.addProperty(
            STRONG_EMPHASIS_CONFIG.key,
            {
                handler: this.configStrongEmphasis,
                value: STRONG_EMPHASIS_CONFIG.value,
                validator: STRONG_EMPHASIS_CONFIG.validator,
                suppressEvent: STRONG_EMPHASIS_CONFIG.suppressEvent,
                supercedes: STRONG_EMPHASIS_CONFIG.supercedes
            }
        );


        /**
        * @config checked
        * @description Boolean indicating if the menu item should be rendered 
        * with a checkmark.
        * @default false
        * @type Boolean
        */
        oConfig.addProperty(
            CHECKED_CONFIG.key, 
            {
                handler: this.configChecked, 
                value: CHECKED_CONFIG.value, 
                validator: CHECKED_CONFIG.validator, 
                suppressEvent: CHECKED_CONFIG.suppressEvent,
                supercedes: CHECKED_CONFIG.supercedes
            } 
        );


        /**
        * @config disabled
        * @description Boolean indicating if the menu item should be disabled.  
        * (Disabled menu items are  dimmed and will not respond to user input 
        * or fire events.)
        * @default false
        * @type Boolean
        */
        oConfig.addProperty(
            DISABLED_CONFIG.key,
            {
                handler: this.configDisabled,
                value: DISABLED_CONFIG.value,
                validator: DISABLED_CONFIG.validator,
                suppressEvent: DISABLED_CONFIG.suppressEvent
            }
        );


        /**
        * @config selected
        * @description Boolean indicating if the menu item should 
        * be highlighted.
        * @default false
        * @type Boolean
        */
        oConfig.addProperty(
            SELECTED_CONFIG.key,
            {
                handler: this.configSelected,
                value: SELECTED_CONFIG.value,
                validator: SELECTED_CONFIG.validator,
                suppressEvent: SELECTED_CONFIG.suppressEvent
            }
        );


        /**
        * @config submenu
        * @description Object specifying the submenu to be appended to the 
        * menu item.  The value can be one of the following: <ul><li>Object 
        * specifying a Menu instance.</li><li>Object literal specifying the
        * menu to be created.  Format: <code>{ id: [menu id], itemdata: 
        * [<a href="YAHOO.widget.Menu.html#itemData">array of values for 
        * items</a>] }</code>.</li><li>String specifying the id attribute 
        * of the <code>&#60;div&#62;</code> element of the menu.</li><li>
        * Object specifying the <code>&#60;div&#62;</code> element of the 
        * menu.</li></ul>
        * @default null
        * @type Menu|String|Object|<a href="http://www.w3.org/TR/2000/
        * WD-DOM-Level-1-20000929/level-one-html.html#ID-58190037">
        * HTMLElement</a>
        */
        oConfig.addProperty(
            SUBMENU_CONFIG.key, 
            {
                handler: this.configSubmenu, 
                supercedes: SUBMENU_CONFIG.supercedes,
                suppressEvent: SUBMENU_CONFIG.suppressEvent
            }
        );


        /**
        * @config onclick
        * @description Object literal representing the code to be executed when 
        * the item is clicked.  Format:<br> <code> {<br> 
        * <strong>fn:</strong> Function,   &#47;&#47; The handler to call when 
        * the event fires.<br> <strong>obj:</strong> Object, &#47;&#47; An 
        * object to  pass back to the handler.<br> <strong>scope:</strong> 
        * Object &#47;&#47; The object to use for the scope of the handler.
        * <br> } </code>
        * @type Object
        * @default null
        */
        oConfig.addProperty(
            ONCLICK_CONFIG.key, 
            {
                handler: this.configOnClick, 
                suppressEvent: ONCLICK_CONFIG.suppressEvent 
            }
        );


        /**
        * @config classname
        * @description CSS class to be applied to the menu item's root 
        * <code>&#60;li&#62;</code> element.  The specified class(es) are 
        * appended in addition to the default class as specified by the menu 
        * item's CSS_CLASS_NAME constant.
        * @default null
        * @type String
        */
        oConfig.addProperty(
            CLASS_NAME_CONFIG.key, 
            { 
                handler: this.configClassName,
                value: CLASS_NAME_CONFIG.value, 
                validator: CLASS_NAME_CONFIG.validator,
                suppressEvent: CLASS_NAME_CONFIG.suppressEvent 
            }
        );


        /**
        * @config keylistener
        * @description Object literal representing the key(s) that can be used 
        * to trigger the MenuItem's "click" event.  Possible attributes are 
        * shift (boolean), alt (boolean), ctrl (boolean) and keys (either an int 
        * or an array of ints representing keycodes).
        * @default null
        * @type Object
        */
        oConfig.addProperty(
            KEY_LISTENER_CONFIG.key, 
            { 
                handler: this.configKeyListener,
                value: KEY_LISTENER_CONFIG.value, 
                suppressEvent: KEY_LISTENER_CONFIG.suppressEvent 
            }
        );

    },

    /**
    * @method getNextSibling
    * @description Finds the menu item's next sibling.
    * @return YAHOO.widget.MenuItem
    */
    getNextSibling: function () {
    
        var isUL = function (el) {
                return (el.nodeName.toLowerCase() === "ul");
            },
    
            menuitemEl = this.element,
            next = Dom.getNextSibling(menuitemEl),
            parent,
            sibling,
            list;
        
        if (!next) {
            
            parent = menuitemEl.parentNode;
            sibling = Dom.getNextSiblingBy(parent, isUL);
            
            if (sibling) {
                list = sibling;
            }
            else {
                list = Dom.getFirstChildBy(parent.parentNode, isUL);
            }
            
            next = Dom.getFirstChild(list);
            
        }

        return YAHOO.widget.MenuManager.getMenuItem(next.id);

    },

    /**
    * @method getNextEnabledSibling
    * @description Finds the menu item's next enabled sibling.
    * @return YAHOO.widget.MenuItem
    */
    getNextEnabledSibling: function () {
        
        var next = this.getNextSibling();
        
        return (next.cfg.getProperty(_DISABLED) || next.element.style.display == _NONE) ? next.getNextEnabledSibling() : next;
        
    },


    /**
    * @method getPreviousSibling
    * @description Finds the menu item's previous sibling.
    * @return {YAHOO.widget.MenuItem}
    */	
    getPreviousSibling: function () {

        var isUL = function (el) {
                return (el.nodeName.toLowerCase() === "ul");
            },

            menuitemEl = this.element,
            next = Dom.getPreviousSibling(menuitemEl),
            parent,
            sibling,
            list;
        
        if (!next) {
            
            parent = menuitemEl.parentNode;
            sibling = Dom.getPreviousSiblingBy(parent, isUL);
            
            if (sibling) {
                list = sibling;
            }
            else {
                list = Dom.getLastChildBy(parent.parentNode, isUL);
            }
            
            next = Dom.getLastChild(list);
            
        }

        return YAHOO.widget.MenuManager.getMenuItem(next.id);
        
    },


    /**
    * @method getPreviousEnabledSibling
    * @description Finds the menu item's previous enabled sibling.
    * @return {YAHOO.widget.MenuItem}
    */
    getPreviousEnabledSibling: function () {
        
        var next = this.getPreviousSibling();
        
        return (next.cfg.getProperty(_DISABLED) || next.element.style.display == _NONE) ? next.getPreviousEnabledSibling() : next;
        
    },


    /**
    * @method focus
    * @description Causes the menu item to receive the focus and fires the 
    * focus event.
    */
    focus: function () {

        var oParent = this.parent,
            oAnchor = this._oAnchor,
            oActiveItem = oParent.activeItem;


        function setFocus() {

            try {

                if (!(UA.ie && !document.hasFocus())) {
                
                    if (oActiveItem) {
        
                        oActiveItem.blurEvent.fire();
        
                    }
    
                    oAnchor.focus();
                    
                    this.focusEvent.fire();
                
                }

            }
            catch(e) {
            
            }

        }


        if (!this.cfg.getProperty(_DISABLED) && oParent && oParent.cfg.getProperty(_VISIBLE) && 
            this.element.style.display != _NONE) {


            /*
                Setting focus via a timer fixes a race condition in Firefox, IE 
                and Opera where the browser viewport jumps as it trys to 
                position and focus the menu.
            */

            Lang.later(0, this, setFocus);

        }

    },


    /**
    * @method blur
    * @description Causes the menu item to lose focus and fires the 
    * blur event.
    */    
    blur: function () {

        var oParent = this.parent;

        if (!this.cfg.getProperty(_DISABLED) && oParent && oParent.cfg.getProperty(_VISIBLE)) {

            Lang.later(0, this, function () {

                try {
    
                    this._oAnchor.blur();
                    this.blurEvent.fire();    

                } 
                catch (e) {
                
                }
                
            }, 0);

        }

    },


    /**
    * @method hasFocus
    * @description Returns a boolean indicating whether or not the menu item
    * has focus.
    * @return {Boolean}
    */
    hasFocus: function () {
    
        return (YAHOO.widget.MenuManager.getFocusedMenuItem() == this);
    
    },


    /**
    * @method destroy
    * @description Removes the menu item's <code>&#60;li&#62;</code> element 
    * from its parent <code>&#60;ul&#62;</code> element.
    */
    destroy: function () {

        var oEl = this.element,
            oSubmenu,
            oParentNode,
            aEventData,
            i;


        if (oEl) {


            // If the item has a submenu, destroy it first

            oSubmenu = this.cfg.getProperty(_SUBMENU);

            if (oSubmenu) {
            
                oSubmenu.destroy();
            
            }


            // Remove the element from the parent node

            oParentNode = oEl.parentNode;

            if (oParentNode) {

                oParentNode.removeChild(oEl);

                this.destroyEvent.fire();

            }


            // Remove CustomEvent listeners

            i = EVENT_TYPES.length - 1;

            do {

                aEventData = EVENT_TYPES[i];
                
                this[aEventData[0]].unsubscribeAll();

            }
            while (i--);
            
            
            this.cfg.configChangedEvent.unsubscribeAll();

        }

    },


    /**
    * @method toString
    * @description Returns a string representing the menu item.
    * @return {String}
    */
    toString: function () {

        var sReturnVal = _MENUITEM,
            sId = this.id;

        if (sId) {
    
            sReturnVal += (_SPACE + sId);
        
        }

        return sReturnVal;
    
    }

};

Lang.augmentProto(MenuItem, YAHOO.util.EventProvider);

})();
