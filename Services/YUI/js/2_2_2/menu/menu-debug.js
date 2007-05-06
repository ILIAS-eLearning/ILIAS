/*
Copyright (c) 2007, Yahoo! Inc. All rights reserved.
Code licensed under the BSD License:
http://developer.yahoo.net/yui/license.txt
version: 2.2.2
*/


/**
* @module menu
* @description <p>The Menu family of components features a collection of 
* controls that make it easy to add menus to your website or web application.  
* With the Menu Controls you can create website fly-out menus, customized 
* context menus, or application-style menu bars with just a small amount of 
* scripting.</p><p>The Menu family of controls features:</p>
* <ul>
*    <li>Screen-reader accessibility.</li>
*    <li>Keyboard and mouse navigation.</li>
*    <li>A rich event model that provides access to all of a menu's 
*    interesting moments.</li>
*    <li>Support for 
*    <a href="http://en.wikipedia.org/wiki/Progressive_Enhancement">Progressive
*    Enhancement</a>; Menus can be created from simple, 
*    semantic markup on the page or purely through JavaScript.</li>
* </ul>
* @title Menu
* @namespace YAHOO.widget
* @requires Event, Dom, Container
*/
(function() {

var Dom = YAHOO.util.Dom,
    Event = YAHOO.util.Event;


/**
* Singleton that manages a collection of all menus and menu items.  Listens for 
* DOM events at the document level and dispatches the events to the 
* corresponding menu or menu item.
*
* @namespace YAHOO.widget
* @class MenuManager
* @static
*/
YAHOO.widget.MenuManager = function() {

    // Private member variables


    // Flag indicating if the DOM event handlers have been attached

    var m_bInitializedEventHandlers = false,


        // Collection of menus

        m_oMenus = {},
    
    
        //  Collection of menu items 

        m_oItems = {},


        // Collection of visible menus
    
        m_oVisibleMenus = {},


        // Map of DOM event types to their equivalent CustomEvent types
    
        m_oEventTypes =  {
            "click": "clickEvent",
            "mousedown": "mouseDownEvent",
            "mouseup": "mouseUpEvent",
            "mouseover": "mouseOverEvent",
            "mouseout": "mouseOutEvent",
            "keydown": "keyDownEvent",
            "keyup": "keyUpEvent",
            "keypress": "keyPressEvent"
        },


        m_oFocusedMenuItem = null;


    var m_oLogger = new YAHOO.widget.LogWriter("MenuManager");


    // Private methods


    /**
    * @method addItem
    * @description Adds an item to the collection of known menu items.
    * @private
    * @param {YAHOO.widget.MenuItem} p_oItem Object specifying the MenuItem 
    * instance to be added.
    */
    function addItem(p_oItem) {

        var sId = p_oItem.id;

        if(p_oItem && m_oItems[sId] != p_oItem) {
    
            m_oItems[sId] = p_oItem;

            p_oItem.destroyEvent.subscribe(onItemDestroy);

            m_oLogger.log("Item: " + 
                p_oItem.toString() + " successfully registered.");

        }
    
    }


    /**
    * @method removeItem
    * @description Removes an item from the collection of known menu items.
    * @private
    * @param {YAHOO.widget.MenuItem} p_oItem Object specifying the MenuItem 
    * instance to be removed.
    */
    function removeItem(p_oItem) {
    
        var sId = p_oItem.id;

        if(sId && m_oItems[sId]) {

            delete m_oItems[sId];

            m_oLogger.log("Item: " + 
                p_oItem.toString() + " successfully unregistered.");

        }
    
    }


    /**
    * @method getMenuRootElement
    * @description Finds the root DIV node of a menu or the root LI node of a 
    * menu item.
    * @private
    * @param {<a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/level-
    * one-html.html#ID-58190037">HTMLElement</a>} p_oElement Object specifying 
    * an HTML element.
    */
    function getMenuRootElement(p_oElement) {
    
        var oParentNode;

        if(p_oElement && p_oElement.tagName) {
        
            switch(p_oElement.tagName.toUpperCase()) {
                    
                case "DIV":
    
                    oParentNode = p_oElement.parentNode;
    
                    // Check if the DIV is the inner "body" node of a menu

                    if(
                        (
                            Dom.hasClass(p_oElement, "hd") ||
                            Dom.hasClass(p_oElement, "bd") ||
                            Dom.hasClass(p_oElement, "ft")
                        )
                        && 
                        oParentNode && 
                        oParentNode.tagName && 
                        oParentNode.tagName.toUpperCase() == "DIV"
                    ) {
                    
                        return oParentNode;
                    
                    }
                    else {
                    
                        return p_oElement;
                    
                    }
                
                break;

                case "LI":
    
                    return p_oElement;

                default:
    
                    oParentNode = p_oElement.parentNode;
    
                    if(oParentNode) {
                    
                        return getMenuRootElement(oParentNode);
                    
                    }
                
                break;
            
            }

        }
        
    }



    // Private event handlers


    /**
    * @method onDOMEvent
    * @description Generic, global event handler for all of a menu's DOM-based 
    * events.  This listens for events against the document object.  If the 
    * target of a given event is a member of a menu or menu item's DOM, the 
    * instance's corresponding Custom Event is fired.
    * @private
    * @param {Event} p_oEvent Object representing the DOM event object passed 
    * back by the event utility (YAHOO.util.Event).
    */
    function onDOMEvent(p_oEvent) {

        // Get the target node of the DOM event
    
        var oTarget = Event.getTarget(p_oEvent),


        // See if the target of the event was a menu, or a menu item

            oElement = getMenuRootElement(oTarget),
            oMenuItem,
            oMenu; 


        if(oElement) {

            var sTagName = oElement.tagName.toUpperCase();
    
            if(sTagName == "LI") {
        
                var sId = oElement.id;
        
                if(sId && m_oItems[sId]) {
        
                    oMenuItem = m_oItems[sId];
                    oMenu = oMenuItem.parent;
        
                }
            
            }
            else if(sTagName == "DIV") {
            
                if(oElement.id) {
                
                    oMenu = m_oMenus[oElement.id];
                
                }
            
            }

        }


        if(oMenu) {

            var sCustomEventType = m_oEventTypes[p_oEvent.type];


            // Fire the Custom Event that corresponds the current DOM event    
    
            if(oMenuItem && !oMenuItem.cfg.getProperty("disabled")) {

                oMenuItem[sCustomEventType].fire(p_oEvent);                   


                if (p_oEvent.type == "keyup" || p_oEvent.type == "mousedown") {

                    if (m_oFocusedMenuItem != oMenuItem) {
                    
                        if(m_oFocusedMenuItem) {

                            m_oFocusedMenuItem.blurEvent.fire();
                        
                        }

                        oMenuItem.focusEvent.fire();
                    
                    }
                
                }

            }
    
            oMenu[sCustomEventType].fire(p_oEvent, oMenuItem);
        
        }
        else if(p_oEvent.type == "mousedown") {

            if(m_oFocusedMenuItem) {

                m_oFocusedMenuItem.blurEvent.fire();

                m_oFocusedMenuItem = null;

            }


            /*
                If the target of the event wasn't a menu, hide all 
                dynamically positioned menus
            */
            
            for(var i in m_oMenus) {
    
                if(YAHOO.lang.hasOwnProperty(m_oMenus,i)) {
    
                    oMenu = m_oMenus[i];
    
                    if(
                        oMenu.cfg.getProperty("clicktohide") && 
                        oMenu.cfg.getProperty("position") == "dynamic"
                    ) {
    
                        oMenu.hide();
    
                    }
                    else {

                        oMenu.clearActiveItem(true);
    
                    }
    
                }
    
            } 

        }
        else if(p_oEvent.type == "keyup") { 

            if(m_oFocusedMenuItem) {

                m_oFocusedMenuItem.blurEvent.fire();

                m_oFocusedMenuItem = null;

            }

        }

    }


    /**
    * @method onMenuDestroy
    * @description "destroy" event handler for a menu.
    * @private
    * @param {String} p_sType String representing the name of the event that 
    * was fired.
    * @param {Array} p_aArgs Array of arguments sent when the event was fired.
    */
    function onMenuDestroy(p_sType, p_aArgs) {

        if(m_oMenus[this.id]) {

            delete m_oMenus[this.id];

            m_oLogger.log("Menu: " + 
                this.toString() + " successfully unregistered.");

        }

    }


    /**
    * @method onMenuFocus
    * @description "focus" event handler for a MenuItem instance.
    * @private
    * @param {String} p_sType String representing the name of the event that 
    * was fired.
    * @param {Array} p_aArgs Array of arguments sent when the event was fired.
    */
    function onMenuFocus(p_sType, p_aArgs) {

        var oItem = p_aArgs[0];
        
        if (oItem) {

            m_oFocusedMenuItem = oItem;
        
        }

    }


    /**
    * @method onMenuBlur
    * @description "blur" event handler for a MenuItem instance.
    * @private
    * @param {String} p_sType String representing the name of the event that 
    * was fired.
    * @param {Array} p_aArgs Array of arguments sent when the event was fired.
    */
    function onMenuBlur(p_sType, p_aArgs) {

        m_oFocusedMenuItem = null;

    }


    /**
    * @method onItemDestroy
    * @description "destroy" event handler for a MenuItem instance.
    * @private
    * @param {String} p_sType String representing the name of the event that 
    * was fired.
    * @param {Array} p_aArgs Array of arguments sent when the event was fired.
    */
    function onItemDestroy(p_sType, p_aArgs) {

        var sId = this.id;

        if(sId && m_oItems[sId]) {

            delete m_oItems[sId];

        }

    }


    /**
    * @method onMenuVisibleConfigChange
    * @description Event handler for when the "visible" configuration property 
    * of a Menu instance changes.
    * @private
    * @param {String} p_sType String representing the name of the event that 
    * was fired.
    * @param {Array} p_aArgs Array of arguments sent when the event was fired.
    */
    function onMenuVisibleConfigChange(p_sType, p_aArgs) {

        var bVisible = p_aArgs[0];
        
        if(bVisible) {

            m_oVisibleMenus[this.id] = this;
            
            m_oLogger.log("Menu: " + 
                this.toString() + 
                " registered with the collection of visible menus.");
        
        }
        else if(m_oVisibleMenus[this.id]) {
        
            delete m_oVisibleMenus[this.id];
            
            m_oLogger.log("Menu: " + 
                this.toString() + 
                " unregistered from the collection of visible menus.");
        
        }
    
    }


    /**
    * @method onItemAdded
    * @description "itemadded" event handler for a Menu instance.
    * @private
    * @param {String} p_sType String representing the name of the event that 
    * was fired.
    * @param {Array} p_aArgs Array of arguments sent when the event was fired.
    */
    function onItemAdded(p_sType, p_aArgs) {
    
        addItem(p_aArgs[0]);
    
    }
    

    /**
    * @method onItemRemoved
    * @description "itemremoved" event handler for a Menu instance.
    * @private
    * @param {String} p_sType String representing the name of the event that 
    * was fired.
    * @param {Array} p_aArgs Array of arguments sent when the event was fired.
    */
    function onItemRemoved(p_sType, p_aArgs) {

        removeItem(p_aArgs[0]);
    
    }



    return {

        // Privileged methods


        /**
        * @method addMenu
        * @description Adds a menu to the collection of known menus.
        * @param {YAHOO.widget.Menu} p_oMenu Object specifying the Menu  
        * instance to be added.
        */
        addMenu: function(p_oMenu) {
    
            if(p_oMenu && p_oMenu.id && !m_oMenus[p_oMenu.id]) {
    
                m_oMenus[p_oMenu.id] = p_oMenu;
            
        
                if(!m_bInitializedEventHandlers) {
        
                    var oDoc = document;
            
                    Event.on(oDoc, "mouseover", onDOMEvent, this, true);
                    Event.on(oDoc, "mouseout", onDOMEvent, this, true);
                    Event.on(oDoc, "mousedown", onDOMEvent, this, true);
                    Event.on(oDoc, "mouseup", onDOMEvent, this, true);
                    Event.on(oDoc, "click", onDOMEvent, this, true);
                    Event.on(oDoc, "keydown", onDOMEvent, this, true);
                    Event.on(oDoc, "keyup", onDOMEvent, this, true);
                    Event.on(oDoc, "keypress", onDOMEvent, this, true);


                    m_bInitializedEventHandlers = true;
                    
                    m_oLogger.log("DOM event handlers initialized.");
        
                }
        
                p_oMenu.destroyEvent.subscribe(onMenuDestroy);
                
                p_oMenu.cfg.subscribeToConfigEvent(
                    "visible", 
                    onMenuVisibleConfigChange
                );
        
                p_oMenu.itemAddedEvent.subscribe(onItemAdded);
                p_oMenu.itemRemovedEvent.subscribe(onItemRemoved);
                p_oMenu.focusEvent.subscribe(onMenuFocus);
                p_oMenu.blurEvent.subscribe(onMenuBlur);
    
                m_oLogger.log("Menu: " + 
                    p_oMenu.toString() + " successfully registered.");
    
            }
    
        },

    
        /**
        * @method removeMenu
        * @description Removes a menu from the collection of known menus.
        * @param {YAHOO.widget.Menu} p_oMenu Object specifying the Menu  
        * instance to be removed.
        */
        removeMenu: function(p_oMenu) {
    
            if(p_oMenu && m_oMenus[p_oMenu.id]) {
    
                delete m_oMenus[p_oMenu.id];
    
                m_oLogger.log("Menu: " + 
                    p_oMenu.toString() + " successfully unregistered.");
    
            }
    
        },
    
    
        /**
        * @method hideVisible
        * @description Hides all visible, dynamically positioned menus.
        */
        hideVisible: function() {
    
            var oMenu;
    
            for(var i in m_oVisibleMenus) {
    
                if(YAHOO.lang.hasOwnProperty(m_oVisibleMenus,i)) {
    
                    oMenu = m_oVisibleMenus[i];
    
                    if(oMenu.cfg.getProperty("position") == "dynamic") {
    
                        oMenu.hide();
    
                    }
    
                }
    
            }        
        
        },


        /**
        * @method getMenus
        * @description Returns an array of all menus registered with the 
        * menu manger.
        * @return {Array}
        */
        getMenus: function() {
        
            return m_oMenus;
        
        },


        /**
        * @method getMenu
        * @description Returns a menu with the specified id.
        * @param {String} p_sId String specifying the id of the menu to
        * be retrieved.
        * @return {YAHOO.widget.Menu}
        */
        getMenu: function(p_sId) {
    
            if(m_oMenus[p_sId]) {
            
                return m_oMenus[p_sId];
            
            }
        
        },


        /**
        * @method getFocusedMenuItem
        * @description Returns a reference to the menu item that currently 
        * has focus.
        * @return {YAHOO.widget.MenuItem}
        */
        getFocusedMenuItem: function() {

            return m_oFocusedMenuItem;

        },


        /**
        * @method getFocusedMenu
        * @description Returns a reference to the menu that currently has focus.
        * @return {YAHOO.widget.Menu}
        */
        getFocusedMenu: function() {

            if(m_oFocusedMenuItem) {

                return (m_oFocusedMenuItem.parent.getRoot());
            
            }

        },

    
        /**
        * @method toString
        * @description Returns a string representing the menu manager.
        * @return {String}
        */
        toString: function() {
        
            return ("MenuManager");
        
        }

    };

}();

})();



/**
* The Menu class creates a container that holds a vertical list representing 
* a set of options or commands.  Menu is the base class for all 
* menu containers. 
* @param {String} p_oElement String specifying the id attribute of the 
* <code>&#60;div&#62;</code> element of the menu.
* @param {String} p_oElement String specifying the id attribute of the 
* <code>&#60;select&#62;</code> element to be used as the data source 
* for the menu.
* @param {<a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/
* level-one-html.html#ID-22445964">HTMLDivElement</a>} p_oElement Object 
* specifying the <code>&#60;div&#62;</code> element of the menu.
* @param {<a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/
* level-one-html.html#ID-94282980">HTMLSelectElement</a>} p_oElement 
* Object specifying the <code>&#60;select&#62;</code> element to be used as 
* the data source for the menu.
* @param {Object} p_oConfig Optional. Object literal specifying the 
* configuration for the menu. See configuration class documentation for 
* more details.
* @namespace YAHOO.widget
* @class Menu
* @constructor
* @extends YAHOO.widget.Overlay
*/
(function() {

var Dom = YAHOO.util.Dom,
    Event = YAHOO.util.Event,
    CustomEvent = YAHOO.util.CustomEvent,
    Lang = YAHOO.lang;


YAHOO.widget.Menu = function(p_oElement, p_oConfig) {

    if(p_oConfig) {

        this.parent = p_oConfig.parent;
        this.lazyLoad = p_oConfig.lazyLoad || p_oConfig.lazyload;
        this.itemData = p_oConfig.itemData || p_oConfig.itemdata;

    }


    YAHOO.widget.Menu.superclass.constructor.call(
        this, 
        p_oElement, 
        p_oConfig
    );

};


/**
* Constant representing the name of the Menu's events
* @property YAHOO.widget.Menu._EVENT_TYPES
* @private
* @final
* @type Object
*/
YAHOO.widget.Menu._EVENT_TYPES = {

    "MOUSE_OVER": "mouseover",
    "MOUSE_OUT": "mouseout",
    "MOUSE_DOWN": "mousedown",
    "MOUSE_UP": "mouseup",
    "CLICK": "click",
    "KEY_PRESS": "keypress",
    "KEY_DOWN": "keydown",
    "KEY_UP": "keyup",
    "FOCUS": "focus",
    "BLUR": "blur",
    "ITEM_ADDED": "itemAdded",
    "ITEM_REMOVED": "itemRemoved"

};



/**
* @method _checkPosition
* @description Checks to make sure that the value of the "position" property 
* is one of the supported strings. Returns true if the position is supported.
* @private
* @param {Object} p_sPosition String specifying the position of the menu.
* @return {Boolean}
*/
YAHOO.widget.Menu._checkPosition = function(p_sPosition) {

    if(typeof p_sPosition == "string") {

        var sPosition = p_sPosition.toLowerCase();

        return ("dynamic,static".indexOf(sPosition) != -1);

    }

};



/**
* Constant representing the Menu's configuration properties
* @property YAHOO.widget.Menu._DEFAULT_CONFIG
* @private
* @final
* @type Object
*/
YAHOO.widget.Menu._DEFAULT_CONFIG = {

    "VISIBLE": { 
        key: "visible", 
        value: false, 
        validator: Lang.isBoolean
    }, 

    "CONSTRAIN_TO_VIEWPORT": {
        key: "constraintoviewport", 
        value: true, 
        validator: Lang.isBoolean, 
        supercedes: ["iframe","x","y","xy"]
    }, 

    "POSITION": { 
        key: "position", 
        value: "dynamic", 
        validator: YAHOO.widget.Menu._checkPosition, 
        supercedes: ["visible"] 
    }, 

    "SUBMENU_ALIGNMENT": { 
        key: "submenualignment", 
        value: ["tl","tr"]
    },

    "AUTO_SUBMENU_DISPLAY": { 
        key: "autosubmenudisplay", 
        value: true, 
        validator: Lang.isBoolean 
    }, 

    "SHOW_DELAY": { 
        key: "showdelay", 
        value: 250, 
        validator: Lang.isNumber 
    }, 

    "HIDE_DELAY": { 
        key: "hidedelay", 
        value: 0, 
        validator: Lang.isNumber, 
        suppressEvent: true
    }, 

    "SUBMENU_HIDE_DELAY": { 
        key: "submenuhidedelay", 
        value: 250, 
        validator: Lang.isNumber
    }, 

    "CLICK_TO_HIDE": { 
        key: "clicktohide", 
        value: true, 
        validator: Lang.isBoolean
    },

    "CONTAINER": { 
        key: "container"
    }, 

    "MAX_HEIGHT": { 
        key: "maxheight", 
        value: 0, 
        validator: Lang.isNumber
    }, 

    "CLASS_NAME": { 
        key: "classname", 
        value: null, 
        validator: Lang.isString
    }

};


YAHOO.lang.extend(YAHOO.widget.Menu, YAHOO.widget.Overlay, {


// Constants


/**
* @property CSS_CLASS_NAME
* @description String representing the CSS class(es) to be applied to the 
* menu's <code>&#60;div&#62;</code> element.
* @default "yuimenu"
* @final
* @type String
*/
CSS_CLASS_NAME: "yuimenu",


/**
* @property ITEM_TYPE
* @description Object representing the type of menu item to instantiate and 
* add when parsing the child nodes (either <code>&#60;li&#62;</code> element, 
* <code>&#60;optgroup&#62;</code> element or <code>&#60;option&#62;</code>) 
* of the menu's source HTML element.
* @default YAHOO.widget.MenuItem
* @final
* @type YAHOO.widget.MenuItem
*/
ITEM_TYPE: null,


/**
* @property GROUP_TITLE_TAG_NAME
* @description String representing the tagname of the HTML element used to 
* title the menu's item groups.
* @default H6
* @final
* @type String
*/
GROUP_TITLE_TAG_NAME: "h6",



// Private properties


/** 
* @property _nHideDelayId
* @description Number representing the time-out setting used to cancel the 
* hiding of a menu.
* @default null
* @private
* @type Number
*/
_nHideDelayId: null,


/** 
* @property _nShowDelayId
* @description Number representing the time-out setting used to cancel the 
* showing of a menu.
* @default null
* @private
* @type Number
*/
_nShowDelayId: null,


/** 
* @property _nSubmenuHideDelayId
* @description Number representing the time-out setting used to cancel the 
* hiding of a submenu.
* @default null
* @private
* @type Number
*/
_nSubmenuHideDelayId: null,


/** 
* @property _nBodyScrollId
* @description Number representing the time-out setting used to cancel the 
* scrolling of the menu's body element.
* @default null
* @private
* @type Number
*/
_nBodyScrollId: null,


/** 
* @property _bHideDelayEventHandlersAssigned
* @description Boolean indicating if the "mouseover" and "mouseout" event 
* handlers used for hiding the menu via a call to "window.setTimeout" have 
* already been assigned.
* @default false
* @private
* @type Boolean
*/
_bHideDelayEventHandlersAssigned: false,


/**
* @property _bHandledMouseOverEvent
* @description Boolean indicating the current state of the menu's 
* "mouseover" event.
* @default false
* @private
* @type Boolean
*/
_bHandledMouseOverEvent: false,


/**
* @property _bHandledMouseOutEvent
* @description Boolean indicating the current state of the menu's
* "mouseout" event.
* @default false
* @private
* @type Boolean
*/
_bHandledMouseOutEvent: false,


/**
* @property _aGroupTitleElements
* @description Array of HTML element used to title groups of menu items.
* @default []
* @private
* @type Array
*/
_aGroupTitleElements: null,


/**
* @property _aItemGroups
* @description Multi-dimensional Array representing the menu items as they
* are grouped in the menu.
* @default []
* @private
* @type Array
*/
_aItemGroups: null,


/**
* @property _aListElements
* @description Array of <code>&#60;ul&#62;</code> elements, each of which is 
* the parent node for each item's <code>&#60;li&#62;</code> element.
* @default []
* @private
* @type Array
*/
_aListElements: null,


/**
* @property _nCurrentMouseX
* @description The current x coordinate of the mouse inside the area of 
* the menu.
* @default 0
* @private
* @type Number
*/
_nCurrentMouseX: 0,


/**
* @property _nMaxHeight
* @description The original value of the "maxheight" configuration property 
* as set by the user.
* @default -1
* @private
* @type Number
*/
_nMaxHeight: -1,


/**
* @property _bStopMouseEventHandlers
* @description Stops "mouseover," "mouseout," and "mousemove" event handlers 
* from executing.
* @default false
* @private
* @type Boolean
*/
_bStopMouseEventHandlers: false,


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
* @property lazyLoad
* @description Boolean indicating if the menu's "lazy load" feature is 
* enabled.  If set to "true," initialization and rendering of the menu's 
* items will be deferred until the first time it is made visible.  This 
* property should be set via the constructor using the configuration 
* object literal.
* @default false
* @type Boolean
*/
lazyLoad: false,


/**
* @property itemData
* @description Array of items to be added to the menu.  The array can contain 
* strings representing the text for each item to be created, object literals 
* representing the menu item configuration properties, or MenuItem instances.  
* This property should be set via the constructor using the configuration 
* object literal.
* @default null
* @type Array
*/
itemData: null,


/**
* @property activeItem
* @description Object reference to the item in the menu that has is selected.
* @default null
* @type YAHOO.widget.MenuItem
*/
activeItem: null,


/**
* @property parent
* @description Object reference to the menu's parent menu or menu item.  
* This property can be set via the constructor using the configuration 
* object literal.
* @default null
* @type YAHOO.widget.MenuItem
*/
parent: null,


/**
* @property srcElement
* @description Object reference to the HTML element (either 
* <code>&#60;select&#62;</code> or <code>&#60;div&#62;</code>) used to 
* create the menu.
* @default null
* @type <a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/
* level-one-html.html#ID-94282980">HTMLSelectElement</a>|<a 
* href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/level-one-html.
* html#ID-22445964">HTMLDivElement</a>
*/
srcElement: null,



// Events


/**
* @event mouseOverEvent
* @description Fires when the mouse has entered the menu.  Passes back 
* the DOM Event object as an argument.
*/
mouseOverEvent: null,


/**
* @event mouseOutEvent
* @description Fires when the mouse has left the menu.  Passes back the DOM 
* Event object as an argument.
* @type YAHOO.util.CustomEvent
*/
mouseOutEvent: null,


/**
* @event mouseDownEvent
* @description Fires when the user mouses down on the menu.  Passes back the 
* DOM Event object as an argument.
* @type YAHOO.util.CustomEvent
*/
mouseDownEvent: null,


/**
* @event mouseUpEvent
* @description Fires when the user releases a mouse button while the mouse is 
* over the menu.  Passes back the DOM Event object as an argument.
* @type YAHOO.util.CustomEvent
*/
mouseUpEvent: null,


/**
* @event clickEvent
* @description Fires when the user clicks the on the menu.  Passes back the 
* DOM Event object as an argument.
* @type YAHOO.util.CustomEvent
*/
clickEvent: null,


/**
* @event keyPressEvent
* @description Fires when the user presses an alphanumeric key when one of the
* menu's items has focus.  Passes back the DOM Event object as an argument.
* @type YAHOO.util.CustomEvent
*/
keyPressEvent: null,


/**
* @event keyDownEvent
* @description Fires when the user presses a key when one of the menu's items 
* has focus.  Passes back the DOM Event object as an argument.
* @type YAHOO.util.CustomEvent
*/
keyDownEvent: null,


/**
* @event keyUpEvent
* @description Fires when the user releases a key when one of the menu's items 
* has focus.  Passes back the DOM Event object as an argument.
* @type YAHOO.util.CustomEvent
*/
keyUpEvent: null,


/**
* @event itemAddedEvent
* @description Fires when an item is added to the menu.
* @type YAHOO.util.CustomEvent
*/
itemAddedEvent: null,


/**
* @event itemRemovedEvent
* @description Fires when an item is removed to the menu.
* @type YAHOO.util.CustomEvent
*/
itemRemovedEvent: null,


/**
* @method init
* @description The Menu class's initialization method. This method is 
* automatically called by the constructor, and sets up all DOM references 
* for pre-existing markup, and creates required markup if it is not 
* already present.
* @param {String} p_oElement String specifying the id attribute of the 
* <code>&#60;div&#62;</code> element of the menu.
* @param {String} p_oElement String specifying the id attribute of the 
* <code>&#60;select&#62;</code> element to be used as the data source 
* for the menu.
* @param {<a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/
* level-one-html.html#ID-22445964">HTMLDivElement</a>} p_oElement Object 
* specifying the <code>&#60;div&#62;</code> element of the menu.
* @param {<a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/
* level-one-html.html#ID-94282980">HTMLSelectElement</a>} p_oElement 
* Object specifying the <code>&#60;select&#62;</code> element to be used as 
* the data source for the menu.
* @param {Object} p_oConfig Optional. Object literal specifying the 
* configuration for the menu. See configuration class documentation for 
* more details.
*/
init: function(p_oElement, p_oConfig) {

    this._aItemGroups = [];
    this._aListElements = [];
    this._aGroupTitleElements = [];

    if(!this.ITEM_TYPE) {

        this.ITEM_TYPE = YAHOO.widget.MenuItem;

    }


    var oElement;

    if(typeof p_oElement == "string") {

        oElement = document.getElementById(p_oElement);

    }
    else if(p_oElement.tagName) {

        oElement = p_oElement;

    }


    if(oElement && oElement.tagName) {

        switch(oElement.tagName.toUpperCase()) {
    
            case "DIV":

                this.srcElement = oElement;

                if(!oElement.id) {

                    oElement.setAttribute("id", Dom.generateId());

                }


                /* 
                    Note: we don't pass the user config in here yet 
                    because we only want it executed once, at the lowest 
                    subclass level.
                */ 
            
                YAHOO.widget.Menu.superclass.init.call(this, oElement);

                this.beforeInitEvent.fire(YAHOO.widget.Menu);

                this.logger = new YAHOO.widget.LogWriter(this.toString());

                this.logger.log("Source element: " + this.srcElement.tagName);
    
            break;
    
            case "SELECT":
    
                this.srcElement = oElement;

    
                /*
                    The source element is not something that we can use 
                    outright, so we need to create a new Overlay

                    Note: we don't pass the user config in here yet 
                    because we only want it executed once, at the lowest 
                    subclass level.
                */ 

                YAHOO.widget.Menu.superclass.init.call(this, Dom.generateId());

                this.beforeInitEvent.fire(YAHOO.widget.Menu);

                this.logger = new YAHOO.widget.LogWriter(this.toString());

                this.logger.log("Source element: " + this.srcElement.tagName);

            break;

        }

    }
    else {

        /* 
            Note: we don't pass the user config in here yet 
            because we only want it executed once, at the lowest 
            subclass level.
        */ 
    
        YAHOO.widget.Menu.superclass.init.call(this, p_oElement);

        this.beforeInitEvent.fire(YAHOO.widget.Menu);

        this.logger = new YAHOO.widget.LogWriter(this.toString());

        this.logger.log("No source element found.  " +
            "Created element with id: " + this.id);

    }


    if(this.element) {

        var oEl = this.element;

        Dom.addClass(oEl, this.CSS_CLASS_NAME);


        // Subscribe to Custom Events

        this.initEvent.subscribe(this._onInit, this, true);
        this.beforeRenderEvent.subscribe(this._onBeforeRender, this, true);
        this.renderEvent.subscribe(this._onRender);
        this.beforeShowEvent.subscribe(this._onBeforeShow, this, true);
        this.showEvent.subscribe(this._onShow, this, true);
        this.beforeHideEvent.subscribe(this._onBeforeHide, this, true);
        this.hideEvent.subscribe(this._onHide, this, true);
        this.mouseOverEvent.subscribe(this._onMouseOver, this, true);
        this.mouseOutEvent.subscribe(this._onMouseOut, this, true);
        this.clickEvent.subscribe(this._onClick, this, true);
        this.keyDownEvent.subscribe(this._onKeyDown, this, true);
        this.keyPressEvent.subscribe(this._onKeyPress, this, true);

        YAHOO.widget.Module.textResizeEvent.subscribe(
            this._onTextResize, 
            this, 
            true
        );


        if(p_oConfig) {
    
            this.cfg.applyConfig(p_oConfig, true);
    
        }


        // Register the Menu instance with the MenuManager

        YAHOO.widget.MenuManager.addMenu(this);
        

        this.initEvent.fire(YAHOO.widget.Menu);

    }

},



// Private methods


/**
* @method _initSubTree
* @description Iterates the childNodes of the source element to find nodes 
* used to instantiate menu and menu items.
* @private
*/
_initSubTree: function() {

    var oNode;

    if(this.srcElement.tagName.toUpperCase() == "DIV") {

        /*
            Populate the collection of item groups and item
            group titles
        */

        oNode = this.body.firstChild;

        var nGroup = 0,
            sGroupTitleTagName = this.GROUP_TITLE_TAG_NAME.toUpperCase();

        do {

            if(oNode && oNode.tagName) {

                switch(oNode.tagName.toUpperCase()) {

                    case sGroupTitleTagName:
                    
                        this._aGroupTitleElements[nGroup] = oNode;

                    break;

                    case "UL":

                        this._aListElements[nGroup] = oNode;
                        this._aItemGroups[nGroup] = [];
                        nGroup++;

                    break;

                }
            
            }

        }
        while((oNode = oNode.nextSibling));


        /*
            Apply the "first-of-type" class to the first UL to mimic 
            the "first-of-type" CSS3 psuedo class.
        */

        if(this._aListElements[0]) {

            Dom.addClass(this._aListElements[0], "first-of-type");

        }

    }


    oNode = null;

    this.logger.log("Searching DOM for items to initialize.");

    if(this.srcElement.tagName) {

        var sSrcElementTagName = this.srcElement.tagName.toUpperCase();


        switch(sSrcElementTagName) {
    
            case "DIV":
    
                if(this._aListElements.length > 0) {
    
                    this.logger.log("Found " + 
                        this._aListElements.length + 
                        " item groups to initialize.");
    
                    var i = this._aListElements.length - 1;
    
                    do {
    
                        oNode = this._aListElements[i].firstChild;
        
                        this.logger.log("Scanning " + 
                            this._aListElements[i].childNodes.length + 
                            " child nodes for items to initialize.");
    
                        do {
        
                            if(
                                oNode && 
                                oNode.tagName && 
                                oNode.tagName.toUpperCase() == "LI"
                            ) {
        
                                this.logger.log("Initializing " + 
                                    oNode.tagName + " node.");

                                this.addItem(
                                        new this.ITEM_TYPE(
                                            oNode, 
                                            { parent: this }
                                        ), 
                                        i
                                    );
    
                            }
                
                        }
                        while((oNode = oNode.nextSibling));
                
                    }
                    while(i--);
    
                }
    
            break;
    
            case "SELECT":
    
                this.logger.log("Scanning " +  
                    this.srcElement.childNodes.length + 
                    " child nodes for items to initialize.");
    
                oNode = this.srcElement.firstChild;
    
                do {
    
                    if(oNode && oNode.tagName) {
                    
                        switch(oNode.tagName.toUpperCase()) {
        
                            case "OPTGROUP":
                            case "OPTION":
        
                                this.logger.log("Initializing " +  
                                    oNode.tagName + " node.");
        
                                this.addItem(
                                        new this.ITEM_TYPE(
                                                oNode, 
                                                { parent: this }
                                            )
                                        );
        
                            break;
        
                        }

                    }
    
                }
                while((oNode = oNode.nextSibling));
    
            break;
    
        }

    }

},


/**
* @method _getFirstEnabledItem
* @description Returns the first enabled item in the menu.
* @return {YAHOO.widget.MenuItem}
* @private
*/
_getFirstEnabledItem: function() {

    var aItems = this.getItems(),
        nItems = aItems.length,
        oItem;
    
    for(var i=0; i<nItems; i++) {

        oItem = aItems[i];

        if(
            oItem && 
            !oItem.cfg.getProperty("disabled") && 
            oItem.element.style.display != "none"
        ) {

            return oItem;

        }
    
    }
    
},


/**
* @method _addItemToGroup
* @description Adds a menu item to a group.
* @private
* @param {Number} p_nGroupIndex Number indicating the group to which the 
* item belongs.
* @param {YAHOO.widget.MenuItem} p_oItem Object reference for the MenuItem 
* instance to be added to the menu.
* @param {String} p_oItem String specifying the text of the item to be added 
* to the menu.
* @param {Object} p_oItem Object literal containing a set of menu item 
* configuration properties.
* @param {Number} p_nItemIndex Optional. Number indicating the index at 
* which the menu item should be added.
* @return {YAHOO.widget.MenuItem}
*/
_addItemToGroup: function(p_nGroupIndex, p_oItem, p_nItemIndex) {

    var oItem;

    if(p_oItem instanceof this.ITEM_TYPE) {

        oItem = p_oItem;
        oItem.parent = this;

    }
    else if(typeof p_oItem == "string") {

        oItem = new this.ITEM_TYPE(p_oItem, { parent: this });
    
    }
    else if(typeof p_oItem == "object") {

        p_oItem.parent = this;

        oItem = new this.ITEM_TYPE(p_oItem.text, p_oItem);

    }


    if(oItem) {

        if (oItem.cfg.getProperty("selected")) {

            this.activeItem = oItem;
        
        }


        var nGroupIndex = typeof p_nGroupIndex == "number" ? p_nGroupIndex : 0,
            aGroup = this._getItemGroup(nGroupIndex),
            oGroupItem;


        if(!aGroup) {

            aGroup = this._createItemGroup(nGroupIndex);

        }


        if(typeof p_nItemIndex == "number") {

            var bAppend = (p_nItemIndex >= aGroup.length);            


            if(aGroup[p_nItemIndex]) {
    
                aGroup.splice(p_nItemIndex, 0, oItem);
    
            }
            else {
    
                aGroup[p_nItemIndex] = oItem;
    
            }


            oGroupItem = aGroup[p_nItemIndex];

            if(oGroupItem) {

                if(
                    bAppend && 
                    (
                        !oGroupItem.element.parentNode || 
                        oGroupItem.element.parentNode.nodeType == 11
                    )
                ) {
        
                    this._aListElements[nGroupIndex].appendChild(
                        oGroupItem.element
                    );
    
                }
                else {
  
                    function getNextItemSibling(p_aArray, p_nStartIndex) {
                
                            return (
                                    p_aArray[p_nStartIndex] || 
                                    getNextItemSibling(
                                        p_aArray, 
                                        (p_nStartIndex+1)
                                    )
                                );

                    }
    
    
                    var oNextItemSibling = 
                            getNextItemSibling(aGroup, (p_nItemIndex+1));
    
                    if(
                        oNextItemSibling && 
                        (
                            !oGroupItem.element.parentNode || 
                            oGroupItem.element.parentNode.nodeType == 11
                        )
                    ) {
            
                        this._aListElements[nGroupIndex].insertBefore(
                                oGroupItem.element, 
                                oNextItemSibling.element
                            );
        
                    }
    
                }
    

                oGroupItem.parent = this;
        
                this._subscribeToItemEvents(oGroupItem);
    
                this._configureSubmenu(oGroupItem);
                
                this._updateItemProperties(nGroupIndex);
        
                this.logger.log("Item inserted." + 
                    " Text: " + oGroupItem.cfg.getProperty("text") + ", " + 
                    " Index: " + oGroupItem.index + ", " + 
                    " Group Index: " + oGroupItem.groupIndex);

                this.itemAddedEvent.fire(oGroupItem);

                return oGroupItem;
    
            }

        }
        else {
    
            var nItemIndex = aGroup.length;
    
            aGroup[nItemIndex] = oItem;

            oGroupItem = aGroup[nItemIndex];
    

            if(oGroupItem) {
    
                if(
                    !Dom.isAncestor(
                        this._aListElements[nGroupIndex], 
                        oGroupItem.element
                    )
                ) {
    
                    this._aListElements[nGroupIndex].appendChild(
                        oGroupItem.element
                    );
    
                }
    
                oGroupItem.element.setAttribute("groupindex", nGroupIndex);
                oGroupItem.element.setAttribute("index", nItemIndex);
        
                oGroupItem.parent = this;
    
                oGroupItem.index = nItemIndex;
                oGroupItem.groupIndex = nGroupIndex;
        
                this._subscribeToItemEvents(oGroupItem);
    
                this._configureSubmenu(oGroupItem);
    
                if(nItemIndex === 0) {
        
                    Dom.addClass(oGroupItem.element, "first-of-type");
        
                }

                this.logger.log("Item added." + 
                    " Text: " + oGroupItem.cfg.getProperty("text") + ", " + 
                    " Index: " + oGroupItem.index + ", " + 
                    " Group Index: " + oGroupItem.groupIndex);
        

                this.itemAddedEvent.fire(oGroupItem);

                return oGroupItem;
    
            }
    
        }

    }
    
},


/**
* @method _removeItemFromGroupByIndex
* @description Removes a menu item from a group by index.  Returns the menu 
* item that was removed.
* @private
* @param {Number} p_nGroupIndex Number indicating the group to which the menu 
* item belongs.
* @param {Number} p_nItemIndex Number indicating the index of the menu item 
* to be removed.
* @return {YAHOO.widget.MenuItem}
*/
_removeItemFromGroupByIndex: function(p_nGroupIndex, p_nItemIndex) {

    var nGroupIndex = typeof p_nGroupIndex == "number" ? p_nGroupIndex : 0,
        aGroup = this._getItemGroup(nGroupIndex);

    if(aGroup) {

        var aArray = aGroup.splice(p_nItemIndex, 1),
            oItem = aArray[0];
    
        if(oItem) {
    
            // Update the index and className properties of each member        
            
            this._updateItemProperties(nGroupIndex);
    
            if(aGroup.length === 0) {
    
                // Remove the UL
    
                var oUL = this._aListElements[nGroupIndex];
    
                if(this.body && oUL) {
    
                    this.body.removeChild(oUL);
    
                }
    
                // Remove the group from the array of items
    
                this._aItemGroups.splice(nGroupIndex, 1);
    
    
                // Remove the UL from the array of ULs
    
                this._aListElements.splice(nGroupIndex, 1);
    
    
                /*
                     Assign the "first-of-type" class to the new first UL 
                     in the collection
                */
    
                oUL = this._aListElements[0];
    
                if(oUL) {
    
                    Dom.addClass(oUL, "first-of-type");
    
                }            
    
            }
    

            this.itemRemovedEvent.fire(oItem);    


            // Return a reference to the item that was removed
        
            return oItem;
    
        }

    }
    
},


/**
* @method _removeItemFromGroupByValue
* @description Removes a menu item from a group by reference.  Returns the 
* menu item that was removed.
* @private
* @param {Number} p_nGroupIndex Number indicating the group to which the
* menu item belongs.
* @param {YAHOO.widget.MenuItem} p_oItem Object reference for the MenuItem 
* instance to be removed.
* @return {YAHOO.widget.MenuItem}
*/    
_removeItemFromGroupByValue: function(p_nGroupIndex, p_oItem) {

    var aGroup = this._getItemGroup(p_nGroupIndex);

    if(aGroup) {

        var nItems = aGroup.length,
            nItemIndex = -1;
    
        if(nItems > 0) {
    
            var i = nItems-1;
        
            do {
        
                if(aGroup[i] == p_oItem) {
        
                    nItemIndex = i;
                    break;    
        
                }
        
            }
            while(i--);
        
            if(nItemIndex > -1) {
        
                return this._removeItemFromGroupByIndex(
                            p_nGroupIndex, 
                            nItemIndex
                        );
        
            }
    
        }
    
    }

},


/**
* @method _updateItemProperties
* @description Updates the "index," "groupindex," and "className" properties 
* of the menu items in the specified group. 
* @private
* @param {Number} p_nGroupIndex Number indicating the group of items to update.
*/
_updateItemProperties: function(p_nGroupIndex) {

    var aGroup = this._getItemGroup(p_nGroupIndex),
        nItems = aGroup.length;

    if(nItems > 0) {

        var i = nItems - 1,
            oItem,
            oLI;

        // Update the index and className properties of each member
    
        do {

            oItem = aGroup[i];

            if(oItem) {
    
                oLI = oItem.element;

                oItem.index = i;
                oItem.groupIndex = p_nGroupIndex;

                oLI.setAttribute("groupindex", p_nGroupIndex);
                oLI.setAttribute("index", i);

                Dom.removeClass(oLI, "first-of-type");

            }
    
        }
        while(i--);


        if(oLI) {

            Dom.addClass(oLI, "first-of-type");

        }

    }

},


/**
* @method _createItemGroup
* @description Creates a new menu item group (array) and its associated 
* <code>&#60;ul&#62;</code> element. Returns an aray of menu item groups.
* @private
* @param {Number} p_nIndex Number indicating the group to create.
* @return {Array}
*/
_createItemGroup: function(p_nIndex) {

    if(!this._aItemGroups[p_nIndex]) {

        this._aItemGroups[p_nIndex] = [];

        var oUL = document.createElement("ul");

        this._aListElements[p_nIndex] = oUL;

        return this._aItemGroups[p_nIndex];

    }

},


/**
* @method _getItemGroup
* @description Returns the menu item group at the specified index.
* @private
* @param {Number} p_nIndex Number indicating the index of the menu item group 
* to be retrieved.
* @return {Array}
*/
_getItemGroup: function(p_nIndex) {

    var nIndex = ((typeof p_nIndex == "number") ? p_nIndex : 0);

    return this._aItemGroups[nIndex];

},


/**
* @method _configureSubmenu
* @description Subscribes the menu item's submenu to its parent menu's events.
* @private
* @param {YAHOO.widget.MenuItem} p_oItem Object reference for the MenuItem 
* instance with the submenu to be configured.
*/
_configureSubmenu: function(p_oItem) {

    var oSubmenu = p_oItem.cfg.getProperty("submenu");

    if(oSubmenu) {
            
        /*
            Listen for configuration changes to the parent menu 
            so they they can be applied to the submenu.
        */

        this.cfg.configChangedEvent.subscribe(
                this._onParentMenuConfigChange, 
                oSubmenu, 
                true
            );

        this.renderEvent.subscribe(
                this._onParentMenuRender,
                oSubmenu, 
                true
            );

        oSubmenu.beforeShowEvent.subscribe(
                this._onSubmenuBeforeShow, 
                oSubmenu, 
                true
            );

        oSubmenu.showEvent.subscribe(this._onSubmenuShow, null, p_oItem);
        oSubmenu.hideEvent.subscribe(this._onSubmenuHide, null, p_oItem);

    }

},


/**
* @method _subscribeToItemEvents
* @description Subscribes a menu to a menu item's event.
* @private
* @param {YAHOO.widget.MenuItem} p_oItem Object reference for the MenuItem 
* instance whose events should be subscribed to.
*/
_subscribeToItemEvents: function(p_oItem) {

    p_oItem.focusEvent.subscribe(this._onMenuItemFocus);

    p_oItem.blurEvent.subscribe(this._onMenuItemBlur);

    p_oItem.cfg.configChangedEvent.subscribe(
        this._onMenuItemConfigChange,
        p_oItem,
        this
    );

},


/**
* @method _getOffsetWidth
* @description Returns the offset width of the menu's 
* <code>&#60;div&#62;</code> element.
* @private
*/
_getOffsetWidth: function() {

    var oClone = this.element.cloneNode(true);

    Dom.setStyle(oClone, "width", "");

    document.body.appendChild(oClone);

    var sWidth = oClone.offsetWidth;

    document.body.removeChild(oClone);

    return sWidth;

},


/**
* @method _setWidth
* @description Sets the width of the menu's root <code>&#60;div&#62;</code> 
* element to its offsetWidth.
* @private
*/
_setWidth: function() {

    var sWidth;

    if (this.element.parentNode.tagName.toUpperCase() == "BODY") {

        if (this.browser == "opera") {

            sWidth = this._getOffsetWidth();
        
        }
        else {

            Dom.setStyle(this.element, "width", "auto");
            
            sWidth = this.element.offsetWidth;
        
        }

    }
    else {
    
        sWidth = this._getOffsetWidth();
    
    }

    this.cfg.setProperty("width", (sWidth + "px"));

},


/**
* @method _onWidthChange
* @description Change event handler for the the menu's "width" configuration
* property.
* @private
* @param {String} p_sType String representing the name of the event that 
* was fired.
* @param {Array} p_aArgs Array of arguments sent when the event was fired.
*/
_onWidthChange: function(p_sType, p_aArgs) {

    var sWidth = p_aArgs[0];
    
    if (sWidth && !this._hasSetWidthHandlers) {

        this.itemAddedEvent.subscribe(this._setWidth);
        this.itemRemovedEvent.subscribe(this._setWidth);

        this._hasSetWidthHandlers = true;

    }
    else if (this._hasSetWidthHandlers) {

        this.itemAddedEvent.unsubscribe(this._setWidth);
        this.itemRemovedEvent.unsubscribe(this._setWidth);

        this._hasSetWidthHandlers = false;

    }

},


/**
* @method _onVisibleChange
* @description Change event handler for the the menu's "visible" configuration
* property.
* @private
* @param {String} p_sType String representing the name of the event that 
* was fired.
* @param {Array} p_aArgs Array of arguments sent when the event was fired.
*/
_onVisibleChange: function(p_sType, p_aArgs) {

    var bVisible = p_aArgs[0];
    
    if (bVisible) {

        Dom.addClass(this.element, "visible");

    }
    else {

        Dom.removeClass(this.element, "visible");

    }

},


/**
* @method _cancelHideDelay
* @description Cancels the call to "hideMenu."
* @private
*/
_cancelHideDelay: function() {

    var oRoot = this.getRoot();

    if(oRoot._nHideDelayId) {

        window.clearTimeout(oRoot._nHideDelayId);

    }

},


/**
* @method _execHideDelay
* @description Hides the menu after the number of milliseconds specified by 
* the "hidedelay" configuration property.
* @private
*/
_execHideDelay: function() {

    this._cancelHideDelay();

    var oRoot = this.getRoot(),
        me = this;

    function hideMenu() {
    
        if(oRoot.activeItem) {

            oRoot.clearActiveItem();

        }

        if(oRoot == me && me.cfg.getProperty("position") == "dynamic") {

            me.hide();            
        
        }
    
    }


    oRoot._nHideDelayId = 
        window.setTimeout(hideMenu, oRoot.cfg.getProperty("hidedelay"));

},


/**
* @method _cancelShowDelay
* @description Cancels the call to the "showMenu."
* @private
*/
_cancelShowDelay: function() {

    var oRoot = this.getRoot();

    if(oRoot._nShowDelayId) {

        window.clearTimeout(oRoot._nShowDelayId);

    }

},


/**
* @method _execShowDelay
* @description Shows the menu after the number of milliseconds specified by 
* the "showdelay" configuration property have ellapsed.
* @private
* @param {YAHOO.widget.Menu} p_oMenu Object specifying the menu that should 
* be made visible.
*/
_execShowDelay: function(p_oMenu) {

    var oRoot = this.getRoot();

    function showMenu() {

        if(p_oMenu.parent.cfg.getProperty("selected")) {

            p_oMenu.show();

        }

    }


    oRoot._nShowDelayId = 
        window.setTimeout(showMenu, oRoot.cfg.getProperty("showdelay"));

},


/**
* @method _execSubmenuHideDelay
* @description Hides a submenu after the number of milliseconds specified by 
* the "submenuhidedelay" configuration property have ellapsed.
* @private
* @param {YAHOO.widget.Menu} p_oSubmenu Object specifying the submenu that  
* should be hidden.
* @param {Number} p_nMouseX The x coordinate of the mouse when it left 
* the specified submenu's parent menu item.
* @param {Number} p_nHideDelay The number of milliseconds that should ellapse
* before the submenu is hidden.
*/
_execSubmenuHideDelay: function(p_oSubmenu, p_nMouseX, p_nHideDelay) {

    var me = this;

    p_oSubmenu._nSubmenuHideDelayId = window.setTimeout(function () {

        if(me._nCurrentMouseX > (p_nMouseX + 10)) {

            p_oSubmenu._nSubmenuHideDelayId = window.setTimeout(function () {
        
                p_oSubmenu.hide();

            }, p_nHideDelay);

        }
        else {

            p_oSubmenu.hide();
        
        }

    }, 50);

},



// Protected methods


/**
* @method _disableScrollHeader
* @description Disables the header used for scrolling the body of the menu.
* @protected
*/
_disableScrollHeader: function() {

    if(!this._bHeaderDisabled) {

        Dom.addClass(this.header, "topscrollbar_disabled");
        this._bHeaderDisabled = true;

    }

},


/**
* @method _disableScrollFooter
* @description Disables the footer used for scrolling the body of the menu.
* @protected
*/
_disableScrollFooter: function() {

    if(!this._bFooterDisabled) {

        Dom.addClass(this.footer, "bottomscrollbar_disabled");
        this._bFooterDisabled = true;

    }

},


/**
* @method _enableScrollHeader
* @description Enables the header used for scrolling the body of the menu.
* @protected
*/
_enableScrollHeader: function() {

    if(this._bHeaderDisabled) {

        Dom.removeClass(this.header, "topscrollbar_disabled");
        this._bHeaderDisabled = false;

    }

},


/**
* @method _enableScrollFooter
* @description Enables the footer used for scrolling the body of the menu.
* @protected
*/
_enableScrollFooter: function() {

    if(this._bFooterDisabled) {

        Dom.removeClass(this.footer, "bottomscrollbar_disabled");
        this._bFooterDisabled = false;

    }

},


/**
* @method _onMouseOver
* @description "mouseover" event handler for the menu.
* @protected
* @param {String} p_sType String representing the name of the event that 
* was fired.
* @param {Array} p_aArgs Array of arguments sent when the event was fired.
* @param {YAHOO.widget.Menu} p_oMenu Object representing the menu that 
* fired the event.
*/
_onMouseOver: function(p_sType, p_aArgs, p_oMenu) {

    if(this._bStopMouseEventHandlers) {
    
        return false;
    
    }


    var oEvent = p_aArgs[0],
        oItem = p_aArgs[1],
        oTarget = Event.getTarget(oEvent);


    if(
        !this._bHandledMouseOverEvent && 
        (oTarget == this.element || Dom.isAncestor(this.element, oTarget))
    ) {

        // Menu mouseover logic

        this._nCurrentMouseX = 0;

        Event.on(
                this.element, 
                "mousemove", 
                this._onMouseMove, 
                this, 
                true
            );


        this.clearActiveItem();


        if(this.parent && this._nSubmenuHideDelayId) {

            window.clearTimeout(this._nSubmenuHideDelayId);

            this.parent.cfg.setProperty("selected", true);

            var oParentMenu = this.parent.parent;

            oParentMenu._bHandledMouseOutEvent = true;
            oParentMenu._bHandledMouseOverEvent = false;

        }


        this._bHandledMouseOverEvent = true;
        this._bHandledMouseOutEvent = false;
    
    }


    if(
        oItem && !oItem.handledMouseOverEvent && 
        !oItem.cfg.getProperty("disabled") && 
        (oTarget == oItem.element || Dom.isAncestor(oItem.element, oTarget))
    ) {

        // Menu Item mouseover logic

        var nShowDelay = this.cfg.getProperty("showdelay"),
            bShowDelay = (nShowDelay > 0);


        if(bShowDelay) {
        
            this._cancelShowDelay();
        
        }


        var oActiveItem = this.activeItem;
    
        if(oActiveItem) {
    
            oActiveItem.cfg.setProperty("selected", false);
    
        }


        var oItemCfg = oItem.cfg;
    
        // Select and focus the current menu item
    
        oItemCfg.setProperty("selected", true);


        if (this.hasFocus()) {
        
            oItem.focus();
        
        }


        if(this.cfg.getProperty("autosubmenudisplay")) {

            // Show the submenu this menu item

            var oSubmenu = oItemCfg.getProperty("submenu");
        
            if(oSubmenu) {
        
                if(bShowDelay) {

                    this._execShowDelay(oSubmenu);
        
                }
                else {

                    oSubmenu.show();

                }

            }

        }                        

        oItem.handledMouseOverEvent = true;
        oItem.handledMouseOutEvent = false;

    }

},


/**
* @method _onMouseOut
* @description "mouseout" event handler for the menu.
* @protected
* @param {String} p_sType String representing the name of the event that 
* was fired.
* @param {Array} p_aArgs Array of arguments sent when the event was fired.
* @param {YAHOO.widget.Menu} p_oMenu Object representing the menu that 
* fired the event.
*/
_onMouseOut: function(p_sType, p_aArgs, p_oMenu) {

    if(this._bStopMouseEventHandlers) {
    
        return false;
    
    }


    var oEvent = p_aArgs[0],
        oItem = p_aArgs[1],
        oRelatedTarget = Event.getRelatedTarget(oEvent),
        bMovingToSubmenu = false;


    if(oItem && !oItem.cfg.getProperty("disabled")) {

        var oItemCfg = oItem.cfg,
            oSubmenu = oItemCfg.getProperty("submenu");


        if(
            oSubmenu && 
            (
                oRelatedTarget == oSubmenu.element ||
                Dom.isAncestor(oSubmenu.element, oRelatedTarget)
            )
        ) {

            bMovingToSubmenu = true;

        }


        if( 
            !oItem.handledMouseOutEvent && 
            (
                (
                    oRelatedTarget != oItem.element &&  
                    !Dom.isAncestor(oItem.element, oRelatedTarget)
                ) || bMovingToSubmenu
            )
        ) {

            // Menu Item mouseout logic

            if(!bMovingToSubmenu) {

                oItem.cfg.setProperty("selected", false);


                if(oSubmenu) {

                    var nSubmenuHideDelay = 
                            this.cfg.getProperty("submenuhidedelay"),

                        nShowDelay = this.cfg.getProperty("showdelay");

                    if(
                        !(this instanceof YAHOO.widget.MenuBar) && 
                        nSubmenuHideDelay > 0 && 
                        nShowDelay >= nSubmenuHideDelay
                    ) {

                        this._execSubmenuHideDelay(
                                oSubmenu, 
                                Event.getPageX(oEvent),
                                nSubmenuHideDelay
                            );

                    }
                    else {

                        oSubmenu.hide();

                    }

                }

            }


            oItem.handledMouseOutEvent = true;
            oItem.handledMouseOverEvent = false;
    
        }

    }


    if(
        !this._bHandledMouseOutEvent && 
        (
            (
                oRelatedTarget != this.element &&  
                !Dom.isAncestor(this.element, oRelatedTarget)
            ) 
            || bMovingToSubmenu
        )
    ) {

        // Menu mouseout logic

        Event.removeListener(this.element, "mousemove", this._onMouseMove);

        this._nCurrentMouseX = Event.getPageX(oEvent);

        this._bHandledMouseOutEvent = true;
        this._bHandledMouseOverEvent = false;

    }

},


/**
* @method _onMouseMove
* @description "click" event handler for the menu.
* @protected
* @param {Event} p_oEvent Object representing the DOM event object passed 
* back by the event utility (YAHOO.util.Event).
* @param {YAHOO.widget.Menu} p_oMenu Object representing the menu that 
* fired the event.
*/
_onMouseMove: function(p_oEvent, p_oMenu) {

    if(this._bStopMouseEventHandlers) {
    
        return false;
    
    }

    this._nCurrentMouseX = Event.getPageX(p_oEvent);

},


/**
* @method _onClick
* @description "click" event handler for the menu.
* @protected
* @param {String} p_sType String representing the name of the event that 
* was fired.
* @param {Array} p_aArgs Array of arguments sent when the event was fired.
* @param {YAHOO.widget.Menu} p_oMenu Object representing the menu that 
* fired the event.
*/
_onClick: function(p_sType, p_aArgs, p_oMenu) {

    var oEvent = p_aArgs[0],
        oItem = p_aArgs[1],
        oTarget = Event.getTarget(oEvent);

    if(oItem && !oItem.cfg.getProperty("disabled")) {

        var oItemCfg = oItem.cfg,
            oSubmenu = oItemCfg.getProperty("submenu");


        /*
            ACCESSIBILITY FEATURE FOR SCREEN READERS: 
            Expand/collapse the submenu when the user clicks 
            on the submenu indicator image.
        */        

        if(oTarget == oItem.submenuIndicator && oSubmenu) {

            if(oSubmenu.cfg.getProperty("visible")) {

                oSubmenu.hide();
                
                oSubmenu.parent.focus();
    
            }
            else {

                this.clearActiveItem();

                oItem.cfg.setProperty("selected", true);

                oSubmenu.show();
                
                oSubmenu.setInitialFocus();
    
            }
    
        }
        else {

            var sURL = oItemCfg.getProperty("url"),
                bCurrentPageURL = (sURL.substr((sURL.length-1),1) == "#"),
                sTarget = oItemCfg.getProperty("target"),
                bHasTarget = (sTarget && sTarget.length > 0);

            /*
                Prevent the browser from following links 
                equal to "#"
            */
            
            if(
                oTarget.tagName.toUpperCase() == "A" && 
                bCurrentPageURL && !bHasTarget
            ) {

                Event.preventDefault(oEvent);

                oItem.focus();
            
            }

            if(
                oTarget.tagName.toUpperCase() != "A" && 
                !bCurrentPageURL && !bHasTarget
            ) {
                
                /*
                    Follow the URL of the item regardless of 
                    whether or not the user clicked specifically
                    on the anchor element.
                */
    
                document.location = sURL;
        
            }


            /*
                If the item doesn't navigate to a URL and it doesn't have
                a submenu, then collapse the menu tree.
            */

            if(bCurrentPageURL && !oSubmenu) {
    
                var oRoot = this.getRoot();
                
                if(oRoot.cfg.getProperty("position") == "static") {
    
                    oRoot.clearActiveItem();
    
                }
                else if(oRoot.cfg.getProperty("clicktohide")) {

                    oRoot.hide();
                
                }
    
            }

        }                    
    
    }

},


/**
* @method _onKeyDown
* @description "keydown" event handler for the menu.
* @protected
* @param {String} p_sType String representing the name of the event that 
* was fired.
* @param {Array} p_aArgs Array of arguments sent when the event was fired.
* @param {YAHOO.widget.Menu} p_oMenu Object representing the menu that 
* fired the event.
*/
_onKeyDown: function(p_sType, p_aArgs, p_oMenu) {

    var oEvent = p_aArgs[0],
        oItem = p_aArgs[1],
        me = this,
        oSubmenu;


    /*
        This function is called to prevent a bug in Firefox.  In Firefox,
        moving a DOM element into a stationary mouse pointer will cause the 
        browser to fire mouse events.  This can result in the menu mouse
        event handlers being called uncessarily, especially when menus are 
        moved into a stationary mouse pointer as a result of a 
        key event handler.
    */
    function stopMouseEventHandlers() {

        me._bStopMouseEventHandlers = true;
        
        window.setTimeout(function() {
        
            me._bStopMouseEventHandlers = false;
        
        }, 10);

    }


    if(oItem && !oItem.cfg.getProperty("disabled")) {

        var oItemCfg = oItem.cfg,
            oParentItem = this.parent,
            oRoot,
            oNextItem;


        switch(oEvent.keyCode) {
    
            case 38:    // Up arrow
            case 40:    // Down arrow
    
                oNextItem = (oEvent.keyCode == 38) ? 
                    oItem.getPreviousEnabledSibling() : 
                    oItem.getNextEnabledSibling();
        
                if(oNextItem) {

                    this.clearActiveItem();

                    oNextItem.cfg.setProperty("selected", true);
                    oNextItem.focus();


                    if(this.cfg.getProperty("maxheight") > 0) {

                        var oBody = this.body;

                        oBody.scrollTop = 

                            (
                                oNextItem.element.offsetTop + 
                                oNextItem.element.offsetHeight
                            ) - oBody.offsetHeight;


                        var nScrollTop = oBody.scrollTop,
                            nScrollTarget = 
                                oBody.scrollHeight - oBody.offsetHeight;

                        if(nScrollTop === 0) {

                            this._disableScrollHeader();
                            this._enableScrollFooter();

                        }
                        else if(nScrollTop == nScrollTarget) {

                             this._enableScrollHeader();
                             this._disableScrollFooter();

                        }
                        else {

                            this._enableScrollHeader();
                            this._enableScrollFooter();

                        }

                    }

                }

    
                Event.preventDefault(oEvent);

                stopMouseEventHandlers();
    
            break;
            
    
            case 39:    // Right arrow
    
                oSubmenu = oItemCfg.getProperty("submenu");
    
                if(oSubmenu) {
    
                    if(!oItemCfg.getProperty("selected")) {
        
                        oItemCfg.setProperty("selected", true);
        
                    }
    
                    oSubmenu.show();
                    oSubmenu.setInitialFocus();
                    oSubmenu.setInitialSelection();
    
                }
                else {
    
                    oRoot = this.getRoot();
                    
                    if(oRoot instanceof YAHOO.widget.MenuBar) {
    
                        oNextItem = oRoot.activeItem.getNextEnabledSibling();
    
                        if(oNextItem) {
                        
                            oRoot.clearActiveItem();
    
                            oNextItem.cfg.setProperty("selected", true);
    
                            oSubmenu = oNextItem.cfg.getProperty("submenu");
    
                            if(oSubmenu) {
    
                                oSubmenu.show();
                            
                            }
    
                            oNextItem.focus();
                        
                        }
                    
                    }
                
                }
    
    
                Event.preventDefault(oEvent);

                stopMouseEventHandlers();

            break;
    
    
            case 37:    // Left arrow
    
                if(oParentItem) {
    
                    var oParentMenu = oParentItem.parent;
    
                    if(oParentMenu instanceof YAHOO.widget.MenuBar) {
    
                        oNextItem = 
                            oParentMenu.activeItem.getPreviousEnabledSibling();
    
                        if(oNextItem) {
                        
                            oParentMenu.clearActiveItem();
    
                            oNextItem.cfg.setProperty("selected", true);
    
                            oSubmenu = oNextItem.cfg.getProperty("submenu");
    
                            if(oSubmenu) {
                            
                                oSubmenu.show();
                            
                            }
    
                            oNextItem.focus();
                        
                        } 
                    
                    }
                    else {
    
                        this.hide();
    
                        oParentItem.focus();
                    
                    }
    
                }
    
                Event.preventDefault(oEvent);

                stopMouseEventHandlers();

            break;        
    
        }


    }


    if(oEvent.keyCode == 27) { // Esc key

        if(this.cfg.getProperty("position") == "dynamic") {
        
            this.hide();

            if(this.parent) {

                this.parent.focus();
            
            }

        }
        else if(this.activeItem) {

            oSubmenu = this.activeItem.cfg.getProperty("submenu");

            if(oSubmenu && oSubmenu.cfg.getProperty("visible")) {
            
                oSubmenu.hide();
                this.activeItem.focus();
            
            }
            else {

                this.activeItem.blur();
                this.activeItem.cfg.setProperty("selected", false);
        
            }
        
        }


        Event.preventDefault(oEvent);
    
    }
    
},


/**
* @method _onKeyPress
* @description "keypress" event handler for a Menu instance.
* @protected
* @param {String} p_sType The name of the event that was fired.
* @param {Array} p_aArgs Collection of arguments sent when the event 
* was fired.
* @param {YAHOO.widget.Menu} p_oMenu The Menu instance that fired the event.
*/
_onKeyPress: function(p_sType, p_aArgs, p_oMenu) {
    
    var oEvent = p_aArgs[0];


    if(oEvent.keyCode == 40 || oEvent.keyCode == 38) {

        YAHOO.util.Event.preventDefault(oEvent);

    }

},


/**
* @method _onTextResize
* @description "textresize" event handler for the menu.
* @protected
* @param {String} p_sType String representing the name of the event that 
* was fired.
* @param {Array} p_aArgs Array of arguments sent when the event was fired.
* @param {YAHOO.widget.Menu} p_oMenu Object representing the menu that 
* fired the event.
*/
_onTextResize: function(p_sType, p_aArgs, p_oMenu) {

    if(this.browser == "gecko" && !this._handleResize) {

        this._handleResize = true;
        return;
    
    }


    var oConfig = this.cfg;

    if(oConfig.getProperty("position") == "dynamic") {

        oConfig.setProperty("width", (this._getOffsetWidth() + "px"));

    }

},


/**
* @method _onScrollTargetMouseOver
* @description "mouseover" event handler for the menu's "header" and "footer" 
* elements.  Used to scroll the body of the menu up and down when the 
* menu's "maxheight" configuration property is set to a value greater than 0.
* @protected
* @param {Event} p_oEvent Object representing the DOM event object passed 
* back by the event utility (YAHOO.util.Event).
* @param {YAHOO.widget.Menu} p_oMenu Object representing the menu that 
* fired the event.
*/
_onScrollTargetMouseOver: function(p_oEvent, p_oMenu) {

    this._cancelHideDelay();

    var oTarget = Event.getTarget(p_oEvent),
        oBody = this.body,
        me = this,
        nScrollTarget,
        fnScrollFunction;


    function scrollBodyDown() {

        var nScrollTop = oBody.scrollTop;


        if(nScrollTop < nScrollTarget) {

            oBody.scrollTop = (nScrollTop + 1);

            me._enableScrollHeader();

        }
        else {

            oBody.scrollTop = nScrollTarget;
            
            window.clearInterval(me._nBodyScrollId);

            me._disableScrollFooter();

        }

    }


    function scrollBodyUp() {

        var nScrollTop = oBody.scrollTop;


        if(nScrollTop > 0) {

            oBody.scrollTop = (nScrollTop - 1);

            me._enableScrollFooter();

        }
        else {

            oBody.scrollTop = 0;
            
            window.clearInterval(me._nBodyScrollId);

            me._disableScrollHeader();

        }

    }

    
    if(Dom.hasClass(oTarget, "hd")) {

        fnScrollFunction = scrollBodyUp;
    
    }
    else {

        nScrollTarget = oBody.scrollHeight - oBody.offsetHeight;

        fnScrollFunction = scrollBodyDown;
    
    }


    this._nBodyScrollId = window.setInterval(fnScrollFunction, 10);

},


/**
* @method _onScrollTargetMouseOut
* @description "mouseout" event handler for the menu's "header" and "footer" 
* elements.  Used to stop scrolling the body of the menu up and down when the 
* menu's "maxheight" configuration property is set to a value greater than 0.
* @protected
* @param {Event} p_oEvent Object representing the DOM event object passed 
* back by the event utility (YAHOO.util.Event).
* @param {YAHOO.widget.Menu} p_oMenu Object representing the menu that 
* fired the event.
*/
_onScrollTargetMouseOut: function(p_oEvent, p_oMenu) {

    window.clearInterval(this._nBodyScrollId);

    this._cancelHideDelay();

},



// Private methods


/**
* @method _onInit
* @description "init" event handler for the menu.
* @private
* @param {String} p_sType String representing the name of the event that 
* was fired.
* @param {Array} p_aArgs Array of arguments sent when the event was fired.
* @param {YAHOO.widget.Menu} p_oMenu Object representing the menu that 
* fired the event.
*/
_onInit: function(p_sType, p_aArgs, p_oMenu) {

    this.cfg.subscribeToConfigEvent("width", this._onWidthChange);
    this.cfg.subscribeToConfigEvent("visible", this._onVisibleChange);

    if(
        (
            (this.parent && !this.lazyLoad) || 
            (!this.parent && this.cfg.getProperty("position") == "static") ||
            (
                !this.parent && 
                !this.lazyLoad && 
                this.cfg.getProperty("position") == "dynamic"
            ) 
        ) && 
        this.getItemGroups().length === 0
    ) {
 
        if(this.srcElement) {

            this._initSubTree();
        
        }


        if(this.itemData) {

            this.addItems(this.itemData);

        }
    
    }
    else if(this.lazyLoad) {

        this.cfg.fireQueue();
    
    }

},


/**
* @method _onBeforeRender
* @description "beforerender" event handler for the menu.  Appends all of the 
* <code>&#60;ul&#62;</code>, <code>&#60;li&#62;</code> and their accompanying 
* title elements to the body element of the menu.
* @private
* @param {String} p_sType String representing the name of the event that 
* was fired.
* @param {Array} p_aArgs Array of arguments sent when the event was fired.
* @param {YAHOO.widget.Menu} p_oMenu Object representing the menu that 
* fired the event.
*/
_onBeforeRender: function(p_sType, p_aArgs, p_oMenu) {

    var oConfig = this.cfg,
        oEl = this.element,
        nListElements = this._aListElements.length;


    if(nListElements > 0) {

        var i = 0,
            bFirstList = true,
            oUL,
            oGroupTitle;


        do {

            oUL = this._aListElements[i];

            if(oUL) {

                if(bFirstList) {
        
                    Dom.addClass(oUL, "first-of-type");
                    bFirstList = false;
        
                }


                if(!Dom.isAncestor(oEl, oUL)) {

                    this.appendToBody(oUL);

                }


                oGroupTitle = this._aGroupTitleElements[i];

                if(oGroupTitle) {

                    if(!Dom.isAncestor(oEl, oGroupTitle)) {

                        oUL.parentNode.insertBefore(oGroupTitle, oUL);

                    }


                    Dom.addClass(oUL, "hastitle");

                }

            }

            i++;

        }
        while(i < nListElements);

    }

},


/**
* @method _onRender
* @description "render" event handler for the menu.
* @private
* @param {String} p_sType String representing the name of the event that 
* was fired.
* @param {Array} p_aArgs Array of arguments sent when the event was fired.
*/
_onRender: function(p_sType, p_aArgs) {

    if (
        this.cfg.getProperty("position") == "dynamic" && 
        !this.cfg.getProperty("width")
    ) {

        this._setWidth();
    
    }

},


/**
* @method _onBeforeShow
* @description "beforeshow" event handler for the menu.
* @private
* @param {String} p_sType String representing the name of the event that 
* was fired.
* @param {Array} p_aArgs Array of arguments sent when the event was fired.
* @param {YAHOO.widget.Menu} p_oMenu Object representing the menu that 
* fired the event.
*/
_onBeforeShow: function(p_sType, p_aArgs, p_oMenu) {

    if(this.lazyLoad && this.getItemGroups().length === 0) {

        if(this.srcElement) {
        
            this._initSubTree();

        }


        if(this.itemData) {

            if(
                this.parent && this.parent.parent && 
                this.parent.parent.srcElement && 
                this.parent.parent.srcElement.tagName.toUpperCase() == "SELECT"
            ) {

                var nOptions = this.itemData.length;
    
                for(var n=0; n<nOptions; n++) {

                    if(this.itemData[n].tagName) {

                        this.addItem((new this.ITEM_TYPE(this.itemData[n])));
    
                    }
    
                }
            
            }
            else {

                this.addItems(this.itemData);
            
            }
        
        }


        var oSrcElement = this.srcElement;

        if(oSrcElement) {

            if(oSrcElement.tagName.toUpperCase() == "SELECT") {

                if(Dom.inDocument(oSrcElement)) {

                    this.render(oSrcElement.parentNode);
                
                }
                else {
                
                    this.render(this.cfg.getProperty("container"));
                
                }

            }
            else {

                this.render();

            }

        }
        else {

            if(this.parent) {

                this.render(this.parent.element);            

            }
            else {

                this.render(this.cfg.getProperty("container"));
                this.cfg.refireEvent("xy");

            }                

        }

    }


    if(this.cfg.getProperty("position") == "dynamic") {

        var nViewportHeight = Dom.getViewportHeight();


        if(this.parent && this.parent.parent instanceof YAHOO.widget.MenuBar) {
           
            var oRegion = YAHOO.util.Region.getRegion(this.parent.element);
            
            nViewportHeight = (nViewportHeight - oRegion.bottom);

        }


        if(this.element.offsetHeight >= nViewportHeight) {
    
            var nMaxHeight = this.cfg.getProperty("maxheight");

            /*
                Cache the original value for the "maxheight" configuration  
                property so that we can set it back when the menu is hidden.
            */
    
            this._nMaxHeight = nMaxHeight;

            this.cfg.setProperty("maxheight", (nViewportHeight - 20));
        
        }
    
    
        if(this.cfg.getProperty("maxheight") > 0) {
    
            var oBody = this.body;
    
            if(oBody.scrollTop > 0) {
    
                oBody.scrollTop = 0;
    
            }

            this._disableScrollHeader();
            this._enableScrollFooter();
    
        }

    }


},


/**
* @method _onShow
* @description "show" event handler for the menu.
* @private
* @param {String} p_sType String representing the name of the event that 
* was fired.
* @param {Array} p_aArgs Array of arguments sent when the event was fired.
* @param {YAHOO.widget.Menu} p_oMenu Object representing the menu that fired 
* the event.
*/
_onShow: function(p_sType, p_aArgs, p_oMenu) {

    var oParent = this.parent;
    
    if(oParent) {

        var oParentMenu = oParent.parent,
            aParentAlignment = oParentMenu.cfg.getProperty("submenualignment"),
            aAlignment = this.cfg.getProperty("submenualignment");


        if(
            (aParentAlignment[0] != aAlignment[0]) &&
            (aParentAlignment[1] != aAlignment[1])
        ) {

            this.cfg.setProperty(
                "submenualignment", 
                [ aParentAlignment[0], aParentAlignment[1] ]
            );
        
        }


        if(
            !oParentMenu.cfg.getProperty("autosubmenudisplay") && 
            oParentMenu.cfg.getProperty("position") == "static"
        ) {

            oParentMenu.cfg.setProperty("autosubmenudisplay", true);


            function disableAutoSubmenuDisplay(p_oEvent) {

                if(
                    p_oEvent.type == "mousedown" || 
                    (p_oEvent.type == "keydown" && p_oEvent.keyCode == 27)
                ) {

                    /*  
                        Set the "autosubmenudisplay" to "false" if the user
                        clicks outside the menu bar.
                    */

                    var oTarget = Event.getTarget(p_oEvent);

                    if(
                        oTarget != oParentMenu.element || 
                        !YAHOO.util.Dom.isAncestor(oParentMenu.element, oTarget)
                    ) {

                        oParentMenu.cfg.setProperty(
                            "autosubmenudisplay", 
                            false
                        );

                        Event.removeListener(
                                document, 
                                "mousedown", 
                                disableAutoSubmenuDisplay
                            );

                        Event.removeListener(
                                document, 
                                "keydown", 
                                disableAutoSubmenuDisplay
                            );

                    }
                
                }

            }

            Event.on(document, "mousedown", disableAutoSubmenuDisplay);                             
            Event.on(document, "keydown", disableAutoSubmenuDisplay);

        }

    }

},


/**
* @method _onBeforeHide
* @description "beforehide" event handler for the menu.
* @private
* @param {String} p_sType String representing the name of the event that 
* was fired.
* @param {Array} p_aArgs Array of arguments sent when the event was fired.
* @param {YAHOO.widget.Menu} p_oMenu Object representing the menu that fired 
* the event.
*/
_onBeforeHide: function(p_sType, p_aArgs, p_oMenu) {

    var oActiveItem = this.activeItem;

    if(oActiveItem) {

        var oConfig = oActiveItem.cfg;

        oConfig.setProperty("selected", false);

        var oSubmenu = oConfig.getProperty("submenu");

        if(oSubmenu) {

            oSubmenu.hide();

        }

    }

    if (this == this.getRoot()) {

        this.blur();
    
    }

},


/**
* @method _onHide
* @description "hide" event handler for the menu.
* @private
* @param {String} p_sType String representing the name of the event that 
* was fired.
* @param {Array} p_aArgs Array of arguments sent when the event was fired.
* @param {YAHOO.widget.Menu} p_oMenu Object representing the menu that fired 
* the event.
*/
_onHide: function(p_sType, p_aArgs, p_oMenu) {

    if(this._nMaxHeight != -1) {

        this.cfg.setProperty("maxheight", this._nMaxHeight);

        this._nMaxHeight = -1;

    }

},


/**
* @method _onParentMenuConfigChange
* @description "configchange" event handler for a submenu.
* @private
* @param {String} p_sType String representing the name of the event that 
* was fired.
* @param {Array} p_aArgs Array of arguments sent when the event was fired.
* @param {YAHOO.widget.Menu} p_oSubmenu Object representing the submenu that 
* subscribed to the event.
*/
_onParentMenuConfigChange: function(p_sType, p_aArgs, p_oSubmenu) {
    
    var sPropertyName = p_aArgs[0][0],
        oPropertyValue = p_aArgs[0][1];

    switch(sPropertyName) {

        case "iframe":
        case "constraintoviewport":
        case "hidedelay":
        case "showdelay":
        case "submenuhidedelay":
        case "clicktohide":
        case "effect":
        case "classname":

            p_oSubmenu.cfg.setProperty(sPropertyName, oPropertyValue);
                
        break;        
        
    }
    
},


/**
* @method _onParentMenuRender
* @description "render" event handler for a submenu.  Renders a  
* submenu in response to the firing of its parent's "render" event.
* @private
* @param {String} p_sType String representing the name of the event that 
* was fired.
* @param {Array} p_aArgs Array of arguments sent when the event was fired.
* @param {YAHOO.widget.Menu} p_oSubmenu Object representing the submenu that 
* subscribed to the event.
*/
_onParentMenuRender: function(p_sType, p_aArgs, p_oSubmenu) {

    var oParentMenu = p_oSubmenu.parent.parent,

        oConfig = {

            constraintoviewport: 
                oParentMenu.cfg.getProperty("constraintoviewport"),

            xy: [0,0],
                
            clicktohide: oParentMenu.cfg.getProperty("clicktohide"),
                
            effect: oParentMenu.cfg.getProperty("effect"),

            showdelay: oParentMenu.cfg.getProperty("showdelay"),
            
            hidedelay: oParentMenu.cfg.getProperty("hidedelay"),

            submenuhidedelay: oParentMenu.cfg.getProperty("submenuhidedelay"),

            classname: oParentMenu.cfg.getProperty("classname")

        };


    /*
        Only sync the "iframe" configuration property if the parent
        menu's "position" configuration is the same.
    */

    if(
        this.cfg.getProperty("position") == 
        oParentMenu.cfg.getProperty("position")
    ) {

        oConfig.iframe = oParentMenu.cfg.getProperty("iframe");
    
    }
               

    p_oSubmenu.cfg.applyConfig(oConfig);


    if(!this.lazyLoad) {

        var oLI = this.parent.element;

        if(this.element.parentNode == oLI) {
    
            this.render();
    
        }
        else {

            this.render(oLI);
    
        }

    }
    
},


/**
* @method _onSubmenuBeforeShow
* @description "beforeshow" event handler for a submenu.
* @private
* @param {String} p_sType String representing the name of the event that 
* was fired.
* @param {Array} p_aArgs Array of arguments sent when the event was fired.
* @param {YAHOO.widget.Menu} p_oSubmenu Object representing the submenu that 
* subscribed to the event.
*/
_onSubmenuBeforeShow: function(p_sType, p_aArgs, p_oSubmenu) {
    
    var oParent = this.parent,
        aAlignment = oParent.parent.cfg.getProperty("submenualignment");

    this.cfg.setProperty(
        "context", 
        [oParent.element, aAlignment[0], aAlignment[1]]
    );


    var nScrollTop = oParent.parent.body.scrollTop;

    if(
        (this.browser == "gecko" || this.browser == "safari") 
        && nScrollTop > 0
    ) {

         this.cfg.setProperty("y", (this.cfg.getProperty("y") - nScrollTop));
    
    }

},


/**
* @method _onSubmenuShow
* @description "show" event handler for a submenu.
* @private
* @param {String} p_sType String representing the name of the event that 
* was fired.
* @param {Array} p_aArgs Array of arguments sent when the event was fired.
*/
_onSubmenuShow: function(p_sType, p_aArgs) {
    
    this.submenuIndicator.firstChild.nodeValue = 
        this.EXPANDED_SUBMENU_INDICATOR_TEXT;

},


/**
* @method _onSubmenuHide
* @description "hide" Custom Event handler for a submenu.
* @private
* @param {String} p_sType String representing the name of the event that 
* was fired.
* @param {Array} p_aArgs Array of arguments sent when the event was fired.
*/
_onSubmenuHide: function(p_sType, p_aArgs) {
    
    this.submenuIndicator.firstChild.nodeValue =
        this.COLLAPSED_SUBMENU_INDICATOR_TEXT;

},


/**
* @method _onMenuItemFocus
* @description "focus" event handler for the menu's items.
* @private
* @param {String} p_sType String representing the name of the event that 
* was fired.
* @param {Array} p_aArgs Array of arguments sent when the event was fired.
*/
_onMenuItemFocus: function(p_sType, p_aArgs) {

    this.parent.focusEvent.fire(this);

},


/**
* @method _onMenuItemBlur
* @description "blur" event handler for the menu's items.
* @private
* @param {String} p_sType String representing the name of the event 
* that was fired.
* @param {Array} p_aArgs Array of arguments sent when the event was fired.
*/
_onMenuItemBlur: function(p_sType, p_aArgs) {

    this.parent.blurEvent.fire(this);

},


/**
* @method _onMenuItemConfigChange
* @description "configchange" event handler for the menu's items.
* @private
* @param {String} p_sType String representing the name of the event that 
* was fired.
* @param {Array} p_aArgs Array of arguments sent when the event was fired.
* @param {YAHOO.widget.MenuItem} p_oItem Object representing the menu item 
* that fired the event.
*/
_onMenuItemConfigChange: function(p_sType, p_aArgs, p_oItem) {

    var sPropertyName = p_aArgs[0][0],
        oPropertyValue = p_aArgs[0][1];

    switch(sPropertyName) {

        case "selected":

            if (oPropertyValue === true) {

                this.activeItem = p_oItem;
            
            }

        break;

        case "submenu":

            var oSubmenu = p_aArgs[0][1];

            if(oSubmenu) {

                this._configureSubmenu(p_oItem);

            }

        break;

        case "text":
        case "helptext":

            /*
                A change to an item's "text" or "helptext"
                configuration properties requires the width of the parent
                menu to be recalculated.
            */

            if(this.element.style.width) {
    
                var sWidth = this._getOffsetWidth() + "px";

                Dom.setStyle(this.element, "width", sWidth);

            }

        break;

    }

},



// Public event handlers for configuration properties


/**
* @method enforceConstraints
* @description The default event handler executed when the moveEvent is fired,  
* if the "constraintoviewport" configuration property is set to true.
* @param {String} type The name of the event that was fired.
* @param {Array} args Collection of arguments sent when the 
* event was fired.
* @param {Array} obj Array containing the current Menu instance 
* and the item that fired the event.
*/
enforceConstraints: function(type, args, obj) {

    if(this.parent && !(this.parent.parent instanceof YAHOO.widget.MenuBar)) {
    
        var oConfig = this.cfg,
            pos = args[0],
            
            x = pos[0],
            y = pos[1],
            
            offsetHeight = this.element.offsetHeight,
            offsetWidth = this.element.offsetWidth,
            
            viewPortWidth = YAHOO.util.Dom.getViewportWidth(),
            viewPortHeight = YAHOO.util.Dom.getViewportHeight(),
            
            scrollX = Math.max(
                    document.documentElement.scrollLeft, 
                    document.body.scrollLeft
                ),
            
            scrollY = Math.max(
                    document.documentElement.scrollTop, 
                    document.body.scrollTop
                ),
            
            nPadding = (
                            this.parent && 
                            this.parent.parent instanceof YAHOO.widget.MenuBar
                        ) ? 0 : 10,
            
            topConstraint = scrollY + nPadding,
            leftConstraint = scrollX + nPadding,
            bottomConstraint = 
                scrollY + viewPortHeight - offsetHeight - nPadding,
            rightConstraint = scrollX + viewPortWidth - offsetWidth - nPadding,
            
            aContext = oConfig.getProperty("context"),
            oContextElement = aContext ? aContext[0] : null;
    
    
        if (x < 10) {
    
            x = leftConstraint;
    
        } else if ((x + offsetWidth) > viewPortWidth) {
    
            if(
                oContextElement &&
                ((x - oContextElement.offsetWidth) > offsetWidth)
            ) {
    
                x = (x - (oContextElement.offsetWidth + offsetWidth));
    
            }
            else {
    
                x = rightConstraint;
    
            }
    
        }
    
        if (y < 10) {
    
            y = topConstraint;
    
        } else if (y > bottomConstraint) {
    
            if(oContextElement && (y > offsetHeight)) {
    
                y = ((y + oContextElement.offsetHeight) - offsetHeight);
    
            }
            else {
    
                y = bottomConstraint;
    
            }
    
        }
    
        oConfig.setProperty("x", x, true);
        oConfig.setProperty("y", y, true);
        oConfig.setProperty("xy", [x,y], true);
    
    }

},


/**
* @method configVisible
* @description Event handler for when the "visible" configuration property 
* the menu changes.
* @param {String} p_sType String representing the name of the event that 
* was fired.
* @param {Array} p_aArgs Array of arguments sent when the event was fired.
* @param {YAHOO.widget.Menu} p_oMenu Object representing the menu that 
* fired the event.
*/
configVisible: function(p_sType, p_aArgs, p_oMenu) {

    if(this.cfg.getProperty("position") == "dynamic") {

        YAHOO.widget.Menu.superclass.configVisible.call(
            this, 
            p_sType, 
            p_aArgs, 
            p_oMenu
        );

    }
    else {

        var bVisible = p_aArgs[0],
    	    sDisplay = Dom.getStyle(this.element, "display");

        if(bVisible) {

            if(sDisplay != "block") {
                this.beforeShowEvent.fire();
                Dom.setStyle(this.element, "display", "block");
                this.showEvent.fire();
            }
        
        }
        else {

			if(sDisplay == "block") {
				this.beforeHideEvent.fire();
				Dom.setStyle(this.element, "display", "none");
				this.hideEvent.fire();
			}
        
        }

    }

},


/**
* @method configPosition
* @description Event handler for when the "position" configuration property 
* of the menu changes.
* @param {String} p_sType String representing the name of the event that 
* was fired.
* @param {Array} p_aArgs Array of arguments sent when the event was fired.
* @param {YAHOO.widget.Menu} p_oMenu Object representing the menu that 
* fired the event.
*/
configPosition: function(p_sType, p_aArgs, p_oMenu) {

    var sCSSPosition = p_aArgs[0] == "static" ? "static" : "absolute",
        oCfg = this.cfg;

    Dom.setStyle(this.element, "position", sCSSPosition);


    if(sCSSPosition == "static") {

        /*
            Remove the iframe for statically positioned menus since it will 
            intercept mouse events.
        */

        oCfg.setProperty("iframe", false);


        // Statically positioned menus are visible by default
        
        Dom.setStyle(this.element, "display", "block");

        oCfg.setProperty("visible", true);

    }
    else {

        /*
            Even though the "visible" property is queued to 
            "false" by default, we need to set the "visibility" property to 
            "hidden" since Overlay's "configVisible" implementation checks the 
            element's "visibility" style property before deciding whether 
            or not to show an Overlay instance.
        */

        Dom.setStyle(this.element, "visibility", "hidden");
    
    }


    if(sCSSPosition == "absolute") {

        var nZIndex = oCfg.getProperty("zindex");

        if(!nZIndex || nZIndex === 0) {

            nZIndex = this.parent ? 
                (this.parent.parent.cfg.getProperty("zindex") + 1) : 1;

            oCfg.setProperty("zindex", nZIndex);

        }

    }

},


/**
* @method configIframe
* @description Event handler for when the "iframe" configuration property of 
* the menu changes.
* @param {String} p_sType String representing the name of the event that 
* was fired.
* @param {Array} p_aArgs Array of arguments sent when the event was fired.
* @param {YAHOO.widget.Menu} p_oMenu Object representing the menu that 
* fired the event.
*/
configIframe: function(p_sType, p_aArgs, p_oMenu) {    

    if(this.cfg.getProperty("position") == "dynamic") {

        YAHOO.widget.Menu.superclass.configIframe.call(
            this, 
            p_sType, 
            p_aArgs, 
            p_oMenu
        );

    }

},


/**
* @method configHideDelay
* @description Event handler for when the "hidedelay" configuration property 
* of the menu changes.
* @param {String} p_sType String representing the name of the event that 
* was fired.
* @param {Array} p_aArgs Array of arguments sent when the event was fired.
* @param {YAHOO.widget.Menu} p_oMenu Object representing the menu that 
* fired the event.
*/
configHideDelay: function(p_sType, p_aArgs, p_oMenu) {

    var nHideDelay = p_aArgs[0],
        oMouseOutEvent = this.mouseOutEvent,
        oMouseOverEvent = this.mouseOverEvent,
        oKeyDownEvent = this.keyDownEvent;

    if(nHideDelay > 0) {

        /*
            Only assign event handlers once. This way the user change 
            the value for the hidedelay as many times as they want.
        */

        if(!this._bHideDelayEventHandlersAssigned) {

            oMouseOutEvent.subscribe(this._execHideDelay, this);
            oMouseOverEvent.subscribe(this._cancelHideDelay, this, true);
            oKeyDownEvent.subscribe(this._cancelHideDelay, this, true);

            this._bHideDelayEventHandlersAssigned = true;
        
        }

    }
    else {

        oMouseOutEvent.unsubscribe(this._execHideDelay, this);
        oMouseOverEvent.unsubscribe(this._cancelHideDelay, this);
        oKeyDownEvent.unsubscribe(this._cancelHideDelay, this);

        this._bHideDelayEventHandlersAssigned = false;

    }

},


/**
* @method configContainer
* @description Event handler for when the "container" configuration property 
of the menu changes.
* @param {String} p_sType String representing the name of the event that 
* was fired.
* @param {Array} p_aArgs Array of arguments sent when the event was fired.
* @param {YAHOO.widget.Menu} p_oMenu Object representing the menu that 
* fired the event.
*/
configContainer: function(p_sType, p_aArgs, p_oMenu) {

	var oElement = p_aArgs[0];

	if(typeof oElement == 'string') {

        this.cfg.setProperty(
                "container", 
                document.getElementById(oElement), 
                true
            );

	}

},


/**
* @method _setMaxHeight
* @description "renderEvent" handler used to defer the setting of the 
* "maxheight" configuration property until the menu is rendered in lazy 
* load scenarios.
* @param {String} p_sType The name of the event that was fired.
* @param {Array} p_aArgs Collection of arguments sent when the event 
* was fired.
* @param {Number} p_nMaxHeight Number representing the value to set for the 
* "maxheight" configuration property.
* @private
*/
_setMaxHeight: function(p_sType, p_aArgs, p_nMaxHeight) {

    this.cfg.setProperty("maxheight", p_nMaxHeight);
    this.renderEvent.unsubscribe(this._setMaxHeight);

},


/**
* @method configMaxHeight
* @description Event handler for when the "maxheight" configuration property of 
* a Menu changes.
* @param {String} p_sType The name of the event that was fired.
* @param {Array} p_aArgs Collection of arguments sent when the event 
* was fired.
* @param {YAHOO.widget.Menu} p_oMenu The Menu instance fired
* the event.
*/
configMaxHeight: function(p_sType, p_aArgs, p_oMenu) {

    var nMaxHeight = p_aArgs[0],
        oBody = this.body;


    if(this.lazyLoad && !oBody) {

        this.renderEvent.unsubscribe(this._setMaxHeight);
    
        if(nMaxHeight > 0) {

            this.renderEvent.subscribe(this._setMaxHeight, nMaxHeight, this);

        }

        return;
    
    }

    Dom.setStyle(oBody, "height", "auto");
    Dom.setStyle(oBody, "overflow", "visible");    

    var oHeader = this.header,
        oFooter = this.footer,
        fnMouseOver = this._onScrollTargetMouseOver,
        fnMouseOut = this._onScrollTargetMouseOut;


    if((nMaxHeight > 0) && (oBody.offsetHeight > nMaxHeight)) {

        if(!this.cfg.getProperty("width")) {

            this._setWidth();

        }

        if(!oHeader && !oFooter) {

            this.setHeader("&#32;");
            this.setFooter("&#32;");

            oHeader = this.header;
            oFooter = this.footer;

            Dom.addClass(oHeader, "topscrollbar");
            Dom.addClass(oFooter, "bottomscrollbar");
            
            this.element.insertBefore(oHeader, oBody);
            this.element.appendChild(oFooter);

            Event.on(oHeader, "mouseover", fnMouseOver, this, true);
            Event.on(oHeader, "mouseout", fnMouseOut, this, true);
            Event.on(oFooter, "mouseover", fnMouseOver, this, true);
            Event.on(oFooter, "mouseout", fnMouseOut, this, true);
        
        }

        var nHeight = 

                (
                    nMaxHeight - 
                    (this.footer.offsetHeight + this.header.offsetHeight)
                );

        Dom.setStyle(oBody, "height", (nHeight + "px"));
        Dom.setStyle(oBody, "overflow", "hidden");

    }
    else if(oHeader && oFooter) {

        Dom.setStyle(oBody, "height", "auto");
        Dom.setStyle(oBody, "overflow", "visible");

        Event.removeListener(oHeader, "mouseover", fnMouseOver);
        Event.removeListener(oHeader, "mouseout", fnMouseOut);
        Event.removeListener(oFooter, "mouseover", fnMouseOver);
        Event.removeListener(oFooter, "mouseout", fnMouseOut);

        this.element.removeChild(oHeader);
        this.element.removeChild(oFooter);
    
        this.header = null;
        this.footer = null;
    
    }

},


/**
* @method configClassName
* @description Event handler for when the "classname" configuration property of 
* a menu changes.
* @param {String} p_sType The name of the event that was fired.
* @param {Array} p_aArgs Collection of arguments sent when the event was fired.
* @param {YAHOO.widget.Menu} p_oMenu The Menu instance fired the event.
*/
configClassName: function(p_sType, p_aArgs, p_oMenu) {

    var sClassName = p_aArgs[0];

    if(this._sClassName) {

        Dom.removeClass(this.element, this._sClassName);

    }

    Dom.addClass(this.element, sClassName);
    this._sClassName = sClassName;

},



// Public methods



/**
* @method initEvents
* @description Initializes the custom events for the menu.
*/
initEvents: function() {

	YAHOO.widget.Menu.superclass.initEvents.call(this);

    // Create custom events

    var EVENT_TYPES = YAHOO.widget.Menu._EVENT_TYPES;

    this.mouseOverEvent = new CustomEvent(EVENT_TYPES.MOUSE_OVER, this);
    this.mouseOutEvent = new CustomEvent(EVENT_TYPES.MOUSE_OUT, this);
    this.mouseDownEvent = new CustomEvent(EVENT_TYPES.MOUSE_DOWN, this);
    this.mouseUpEvent = new CustomEvent(EVENT_TYPES.MOUSE_UP, this);
    this.clickEvent = new CustomEvent(EVENT_TYPES.CLICK, this);
    this.keyPressEvent = new CustomEvent(EVENT_TYPES.KEY_PRESS, this);
    this.keyDownEvent = new CustomEvent(EVENT_TYPES.KEY_DOWN, this);
    this.keyUpEvent = new CustomEvent(EVENT_TYPES.KEY_UP, this);
    this.focusEvent = new CustomEvent(EVENT_TYPES.FOCUS, this);
    this.blurEvent = new CustomEvent(EVENT_TYPES.BLUR, this);
    this.itemAddedEvent = new CustomEvent(EVENT_TYPES.ITEM_ADDED, this);
    this.itemRemovedEvent = new CustomEvent(EVENT_TYPES.ITEM_REMOVED, this);

},


/**
* @method getRoot
* @description Finds the menu's root menu.
*/
getRoot: function() {

    var oItem = this.parent;

    if(oItem) {

        var oParentMenu = oItem.parent;

        return oParentMenu ? oParentMenu.getRoot() : this;

    }
    else {
    
        return this;
    
    }

},


/**
* @method toString
* @description Returns a string representing the menu.
* @return {String}
*/
toString: function() {

    var sReturnVal = "Menu",
        sId = this.id;

    if(sId) {

        sReturnVal += (" " + sId);
    
    }

    return sReturnVal;

},


/**
* @method setItemGroupTitle
* @description Sets the title of a group of menu items.
* @param {String} p_sGroupTitle String specifying the title of the group.
* @param {Number} p_nGroupIndex Optional. Number specifying the group to which
* the title belongs.
*/
setItemGroupTitle: function(p_sGroupTitle, p_nGroupIndex) {
        
    if(typeof p_sGroupTitle == "string" && p_sGroupTitle.length > 0) {

        var nGroupIndex = typeof p_nGroupIndex == "number" ? p_nGroupIndex : 0,
            oTitle = this._aGroupTitleElements[nGroupIndex];


        if(oTitle) {

            oTitle.innerHTML = p_sGroupTitle;
            
        }
        else {

            oTitle = document.createElement(this.GROUP_TITLE_TAG_NAME);
                    
            oTitle.innerHTML = p_sGroupTitle;

            this._aGroupTitleElements[nGroupIndex] = oTitle;

        }


        var i = this._aGroupTitleElements.length - 1,
            nFirstIndex;

        do {

            if(this._aGroupTitleElements[i]) {

                Dom.removeClass(this._aGroupTitleElements[i], "first-of-type");

                nFirstIndex = i;

            }

        }
        while(i--);


        if(nFirstIndex !== null) {

            Dom.addClass(
                this._aGroupTitleElements[nFirstIndex], 
                "first-of-type"
            );

        }

    }

},



/**
* @method addItem
* @description Appends an item to the menu.
* @param {YAHOO.widget.MenuItem} p_oItem Object reference for the MenuItem 
* instance to be added to the menu.
* @param {String} p_oItem String specifying the text of the item to be added 
* to the menu.
* @param {Object} p_oItem Object literal containing a set of menu item 
* configuration properties.
* @param {Number} p_nGroupIndex Optional. Number indicating the group to
* which the item belongs.
* @return {YAHOO.widget.MenuItem}
*/
addItem: function(p_oItem, p_nGroupIndex) {

    if(p_oItem) {

        return this._addItemToGroup(p_nGroupIndex, p_oItem);
        
    }

},


/**
* @method addItems
* @description Adds an array of items to the menu.
* @param {Array} p_aItems Array of items to be added to the menu.  The array 
* can contain strings specifying the text for each item to be created, object
* literals specifying each of the menu item configuration properties, 
* or MenuItem instances.
* @param {Number} p_nGroupIndex Optional. Number specifying the group to 
* which the items belongs.
* @return {Array}
*/
addItems: function(p_aItems, p_nGroupIndex) {

    if(Lang.isArray(p_aItems)) {

        var nItems = p_aItems.length,
            aItems = [],
            oItem;


        for(var i=0; i<nItems; i++) {

            oItem = p_aItems[i];

            if(oItem) {

                if(Lang.isArray(oItem)) {
    
                    aItems[aItems.length] = this.addItems(oItem, i);
    
                }
                else {
    
                    aItems[aItems.length] = 
                        this._addItemToGroup(p_nGroupIndex, oItem);
                
                }

            }
    
        }


        if(aItems.length) {
        
            return aItems;
        
        }

    }

},


/**
* @method insertItem
* @description Inserts an item into the menu at the specified index.
* @param {YAHOO.widget.MenuItem} p_oItem Object reference for the MenuItem 
* instance to be added to the menu.
* @param {String} p_oItem String specifying the text of the item to be added 
* to the menu.
* @param {Object} p_oItem Object literal containing a set of menu item 
* configuration properties.
* @param {Number} p_nItemIndex Number indicating the ordinal position at which
* the item should be added.
* @param {Number} p_nGroupIndex Optional. Number indicating the group to which 
* the item belongs.
* @return {YAHOO.widget.MenuItem}
*/
insertItem: function(p_oItem, p_nItemIndex, p_nGroupIndex) {
    
    if(p_oItem) {

        return this._addItemToGroup(p_nGroupIndex, p_oItem, p_nItemIndex);

    }

},


/**
* @method removeItem
* @description Removes the specified item from the menu.
* @param {YAHOO.widget.MenuItem} p_oObject Object reference for the MenuItem 
* instance to be removed from the menu.
* @param {Number} p_oObject Number specifying the index of the item 
* to be removed.
* @param {Number} p_nGroupIndex Optional. Number specifying the group to 
* which the item belongs.
* @return {YAHOO.widget.MenuItem}
*/
removeItem: function(p_oObject, p_nGroupIndex) {
    
    if(typeof p_oObject != "undefined") {

        var oItem;

        if(p_oObject instanceof YAHOO.widget.MenuItem) {

            oItem = this._removeItemFromGroupByValue(p_nGroupIndex, p_oObject);           

        }
        else if(typeof p_oObject == "number") {

            oItem = this._removeItemFromGroupByIndex(p_nGroupIndex, p_oObject);

        }

        if(oItem) {

            oItem.destroy();

            this.logger.log("Item removed." + 
                " Text: " + oItem.cfg.getProperty("text") + ", " + 
                " Index: " + oItem.index + ", " + 
                " Group Index: " + oItem.groupIndex);

            return oItem;

        }

    }

},


/**
* @method getItems
* @description Returns an array of all of the items in the menu.
* @return {Array}
*/
getItems: function() {

    var aGroups = this._aItemGroups,
        nGroups = aGroups.length;

    return (
                (nGroups == 1) ? aGroups[0] : 
                    (Array.prototype.concat.apply([], aGroups))
            );

},


/**
* @method getItemGroups
* @description Multi-dimensional Array representing the menu items as they 
* are grouped in the menu.
* @return {Array}
*/        
getItemGroups: function() {

    return this._aItemGroups;

},


/**
* @method getItem
* @description Returns the item at the specified index.
* @param {Number} p_nItemIndex Number indicating the ordinal position of the 
* item to be retrieved.
* @param {Number} p_nGroupIndex Optional. Number indicating the group to which 
* the item belongs.
* @return {YAHOO.widget.MenuItem}
*/
getItem: function(p_nItemIndex, p_nGroupIndex) {
    
    if(typeof p_nItemIndex == "number") {

        var aGroup = this._getItemGroup(p_nGroupIndex);

        if(aGroup) {

            return aGroup[p_nItemIndex];
        
        }

    }
    
},


/**
* @method clearContent
* @description Removes all of the content from the menu, including the menu 
* items, group titles, header and footer.
*/
clearContent: function() {

    var aItems = this.getItems(),
        nItems = aItems.length,
        oElement = this.element,
        oBody = this.body,
        oHeader = this.header,
        oFooter = this.footer;


    if(nItems > 0) {

        var i = nItems - 1,
            oItem,
            oSubmenu;

        do {

            oItem = aItems[i];

            if(oItem) {

                oSubmenu = oItem.cfg.getProperty("submenu");

                if(oSubmenu) {

                    this.cfg.configChangedEvent.unsubscribe(
                                this._onParentMenuConfigChange, 
                                oSubmenu
                            );

                    this.renderEvent.unsubscribe(
                                        this._onParentMenuRender, 
                                        oSubmenu
                                    );

                }

                oItem.destroy();

            }
        
        }
        while(i--);

    }


    if(oHeader) {

        Event.purgeElement(oHeader);
        oElement.removeChild(oHeader);

    }
    

    if(oFooter) {

        Event.purgeElement(oFooter);
        oElement.removeChild(oFooter);
    }


    if(oBody) {

        Event.purgeElement(oBody);

        oBody.innerHTML = "";

    }


    this._aItemGroups = [];
    this._aListElements = [];
    this._aGroupTitleElements = [];
    
    this.cfg.setProperty("width", null);

},


/**
* @method destroy
* @description Removes the menu's <code>&#60;div&#62;</code> element 
* (and accompanying child nodes) from the document.
*/
destroy: function() {

    // Remove all DOM event listeners

    Event.purgeElement(this.element);


    // Remove Custom Event listeners

    this.mouseOverEvent.unsubscribeAll();
    this.mouseOutEvent.unsubscribeAll();
    this.mouseDownEvent.unsubscribeAll();
    this.mouseUpEvent.unsubscribeAll();
    this.clickEvent.unsubscribeAll();
    this.keyPressEvent.unsubscribeAll();
    this.keyDownEvent.unsubscribeAll();
    this.keyUpEvent.unsubscribeAll();
    this.focusEvent.unsubscribeAll();
    this.blurEvent.unsubscribeAll();
    this.itemAddedEvent.unsubscribeAll();
    this.itemRemovedEvent.unsubscribeAll();
    this.cfg.unsubscribeFromConfigEvent("width", this._onWidthChange);
    this.cfg.unsubscribeFromConfigEvent("visible", this._onVisibleChange);

    if (this._hasSetWidthHandlers) {

        this.itemAddedEvent.unsubscribe(this._setWidth);
        this.itemRemovedEvent.unsubscribe(this._setWidth);

        this._hasSetWidthHandlers = false;

    }

    YAHOO.widget.Module.textResizeEvent.unsubscribe(this._onTextResize, this);


    // Remove all items

    this.clearContent();


    this._aItemGroups = null;
    this._aListElements = null;
    this._aGroupTitleElements = null;


    // Continue with the superclass implementation of this method

    YAHOO.widget.Menu.superclass.destroy.call(this);
    
    this.logger.log("Destroyed.");

},


/**
* @method setInitialFocus
* @description Sets focus to the menu's first enabled item.
*/
setInitialFocus: function() {

    var oItem = this._getFirstEnabledItem();
    
    if (oItem) {

        oItem.focus();

    }
    
},


/**
* @method setInitialSelection
* @description Sets the "selected" configuration property of the menu's first 
* enabled item to "true."
*/
setInitialSelection: function() {

    var oItem = this._getFirstEnabledItem();
    
    if(oItem) {
    
        oItem.cfg.setProperty("selected", true);
    }        

},


/**
* @method clearActiveItem
* @description Sets the "selected" configuration property of the menu's active
* item to "false" and hides the item's submenu.
* @param {Boolean} p_bBlur Boolean indicating if the menu's active item 
* should be blurred.  
*/
clearActiveItem: function(p_bBlur) {

    if(this.cfg.getProperty("showdelay") > 0) {
    
        this._cancelShowDelay();
    
    }


    var oActiveItem = this.activeItem;

    if(oActiveItem) {

        var oConfig = oActiveItem.cfg;

        if(p_bBlur) {

            oActiveItem.blur();
        
        }

        oConfig.setProperty("selected", false);

        var oSubmenu = oConfig.getProperty("submenu");

        if(oSubmenu) {

            oSubmenu.hide();

        }

        this.activeItem = null;            

    }

},


/**
* @method focus
* @description Causes the menu to receive focus and fires the "focus" event.
*/
focus: function() {

    if (!this.hasFocus()) {

        this.setInitialFocus();
    
    }

},


/**
* @method blur
* @description Causes the menu to lose focus and fires the "blur" event.
*/    
blur: function() {

    if (this.hasFocus()) {
    
        var oItem = YAHOO.widget.MenuManager.getFocusedMenuItem();
        
        if (oItem) {

            oItem.blur();

        }

    }

},


/**
* @method hasFocus
* @description Returns a boolean indicating whether or not the menu has focus.
* @return {Boolean}
*/
hasFocus: function() {

    return (YAHOO.widget.MenuManager.getFocusedMenu() == this.getRoot());

},


/**
* @description Initializes the class's configurable properties which can be
* changed using the menu's Config object ("cfg").
* @method initDefaultConfig
*/
initDefaultConfig: function() {

    YAHOO.widget.Menu.superclass.initDefaultConfig.call(this);

    var oConfig = this.cfg,
        DEFAULT_CONFIG = YAHOO.widget.Menu._DEFAULT_CONFIG;

	// Add configuration attributes

    /*
        Change the default value for the "visible" configuration 
        property to "false" by re-adding the property.
    */

    /**
    * @config visible
    * @description Boolean indicating whether or not the menu is visible.  If 
    * the menu's "position" configuration property is set to "dynamic" (the 
    * default), this property toggles the menu's <code>&#60;div&#62;</code> 
    * element's "visibility" style property between "visible" (true) or 
    * "hidden" (false).  If the menu's "position" configuration property is 
    * set to "static" this property toggles the menu's 
    * <code>&#60;div&#62;</code> element's "display" style property 
    * between "block" (true) or "none" (false).
    * @default false
    * @type Boolean
    */
    oConfig.addProperty(
        DEFAULT_CONFIG.VISIBLE.key, 
        {
            handler: this.configVisible, 
            value: DEFAULT_CONFIG.VISIBLE.value, 
            validator: DEFAULT_CONFIG.VISIBLE.validator
         }
     );


    /*
        Change the default value for the "constraintoviewport" configuration 
        property to "true" by re-adding the property.
    */

    /**
    * @config constraintoviewport
    * @description Boolean indicating if the menu will try to remain inside 
    * the boundaries of the size of viewport.
    * @default true
    * @type Boolean
    */
    oConfig.addProperty(
        DEFAULT_CONFIG.CONSTRAIN_TO_VIEWPORT.key, 
        {
            handler: this.configConstrainToViewport, 
            value: DEFAULT_CONFIG.CONSTRAIN_TO_VIEWPORT.value, 
            validator: DEFAULT_CONFIG.CONSTRAIN_TO_VIEWPORT.validator, 
            supercedes: DEFAULT_CONFIG.CONSTRAIN_TO_VIEWPORT.supercedes 
        } 
    );


    /**
    * @config position
    * @description String indicating how a menu should be positioned on the 
    * screen.  Possible values are "static" and "dynamic."  Static menus are 
    * visible by default and reside in the normal flow of the document 
    * (CSS position: static).  Dynamic menus are hidden by default, reside 
    * out of the normal flow of the document (CSS position: absolute), and 
    * can overlay other elements on the screen.
    * @default dynamic
    * @type String
    */
    oConfig.addProperty(
        DEFAULT_CONFIG.POSITION.key, 
        {
            handler: this.configPosition,
            value: DEFAULT_CONFIG.POSITION.value, 
            validator: DEFAULT_CONFIG.POSITION.validator,
            supercedes: DEFAULT_CONFIG.POSITION.supercedes
        }
    );


    /**
    * @config submenualignment
    * @description Array defining how submenus should be aligned to their 
    * parent menu item. The format is: [itemCorner, submenuCorner]. By default
    * a submenu's top left corner is aligned to its parent menu item's top 
    * right corner.
    * @default ["tl","tr"]
    * @type Array
    */
    oConfig.addProperty(
        DEFAULT_CONFIG.SUBMENU_ALIGNMENT.key, 
        { 
            value: DEFAULT_CONFIG.SUBMENU_ALIGNMENT.value 
        }
    );


    /**
    * @config autosubmenudisplay
    * @description Boolean indicating if submenus are automatically made 
    * visible when the user mouses over the menu's items.
    * @default true
    * @type Boolean
    */
	oConfig.addProperty(
	   DEFAULT_CONFIG.AUTO_SUBMENU_DISPLAY.key, 
	   { 
	       value: DEFAULT_CONFIG.AUTO_SUBMENU_DISPLAY.value, 
	       validator: DEFAULT_CONFIG.AUTO_SUBMENU_DISPLAY.validator
       } 
    );


    /**
    * @config showdelay
    * @description Number indicating the time (in milliseconds) that should 
    * expire before a submenu is made visible when the user mouses over 
    * the menu's items.
    * @default 250
    * @type Number
    */
	oConfig.addProperty(
	   DEFAULT_CONFIG.SHOW_DELAY.key, 
	   { 
	       value: DEFAULT_CONFIG.SHOW_DELAY.value, 
	       validator: DEFAULT_CONFIG.SHOW_DELAY.validator
       } 
    );


    /**
    * @config hidedelay
    * @description Number indicating the time (in milliseconds) that should 
    * expire before the menu is hidden.
    * @default 0
    * @type Number
    */
	oConfig.addProperty(
	   DEFAULT_CONFIG.HIDE_DELAY.key, 
	   { 
	       handler: this.configHideDelay,
	       value: DEFAULT_CONFIG.HIDE_DELAY.value, 
	       validator: DEFAULT_CONFIG.HIDE_DELAY.validator, 
	       suppressEvent: DEFAULT_CONFIG.HIDE_DELAY.suppressEvent
       } 
    );


    /**
    * @config submenuhidedelay
    * @description Number indicating the time (in milliseconds) that should 
    * expire before a submenu is hidden when the user mouses out of a menu item 
    * heading in the direction of a submenu.  The value must be greater than or 
    * equal to the value specified for the "showdelay" configuration property.
    * @default 250
    * @type Number
    */
	oConfig.addProperty(
	   DEFAULT_CONFIG.SUBMENU_HIDE_DELAY.key, 
	   { 
	       value: DEFAULT_CONFIG.SUBMENU_HIDE_DELAY.value, 
	       validator: DEFAULT_CONFIG.SUBMENU_HIDE_DELAY.validator
       } 
    );


    /**
    * @config clicktohide
    * @description Boolean indicating if the menu will automatically be 
    * hidden if the user clicks outside of it.
    * @default true
    * @type Boolean
    */
    oConfig.addProperty(
        DEFAULT_CONFIG.CLICK_TO_HIDE.key,
        {
            value: DEFAULT_CONFIG.CLICK_TO_HIDE.value,
            validator: DEFAULT_CONFIG.CLICK_TO_HIDE.validator
        }
    );


	/**
	* @config container
	* @description HTML element reference or string specifying the id 
	* attribute of the HTML element that the menu's markup should be 
	* rendered into.
	* @type <a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/
	* level-one-html.html#ID-58190037">HTMLElement</a>|String
	* @default document.body
	*/
	oConfig.addProperty(
	   DEFAULT_CONFIG.CONTAINER.key, 
	   { 
	       handler: this.configContainer,
	       value: document.body
       } 
   );


    /**
    * @config maxheight
    * @description Defines the maximum height (in pixels) for a menu before the
    * contents of the body are scrolled.
    * @default 0
    * @type Number
    */
    oConfig.addProperty(
       DEFAULT_CONFIG.MAX_HEIGHT.key, 
       {
            handler: this.configMaxHeight,
            value: DEFAULT_CONFIG.MAX_HEIGHT.value,
            validator: DEFAULT_CONFIG.MAX_HEIGHT.validator
       } 
    );


    /**
    * @config classname
    * @description CSS class to be applied to the menu's root 
    * <code>&#60;div&#62;</code> element.  The specified class(es) are 
    * appended in addition to the default class as specified by the menu's
    * CSS_CLASS_NAME constant.
    * @default null
    * @type String
    */
    oConfig.addProperty(
        DEFAULT_CONFIG.CLASS_NAME.key, 
        { 
            handler: this.configClassName,
            value: DEFAULT_CONFIG.CLASS_NAME.value, 
            validator: DEFAULT_CONFIG.CLASS_NAME.validator
        }
    );

}

}); // END YAHOO.lang.extend

})();



(function() {

var Dom = YAHOO.util.Dom,
    Module = YAHOO.widget.Module,
    Menu = YAHOO.widget.Menu,
    CustomEvent = YAHOO.util.CustomEvent,
    Lang = YAHOO.lang;

/**
* Creates an item for a menu.
* 
* @param {String} p_oObject String specifying the text of the menu item.
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
YAHOO.widget.MenuItem = function(p_oObject, p_oConfig) {

    if(p_oObject) {

        if(p_oConfig) {
    
            this.parent = p_oConfig.parent;
            this.value = p_oConfig.value;
            this.id = p_oConfig.id;

        }

        this.init(p_oObject, p_oConfig);

    }

};


/**
* Constant representing the name of the MenuItem's events
* @property YAHOO.widget.MenuItem._EVENT_TYPES
* @private
* @final
* @type Object
*/
YAHOO.widget.MenuItem._EVENT_TYPES = {

    "MOUSE_OVER": "mouseover",
    "MOUSE_OUT": "mouseout",
    "MOUSE_DOWN": "mousedown",
    "MOUSE_UP": "mouseup",
    "CLICK": "click",
    "KEY_PRESS": "keypress",
    "KEY_DOWN": "keydown",
    "KEY_UP": "keyup",
    "ITEM_ADDED": "itemAdded",
    "ITEM_REMOVED": "itemRemoved",
    "FOCUS": "focus",
    "BLUR": "blur",
    "DESTROY": "destroy"

};


/**
* Constant representing the MenuItem's configuration properties
* @property YAHOO.widget.MenuItem._DEFAULT_CONFIG
* @private
* @final
* @type Object
*/
YAHOO.widget.MenuItem._DEFAULT_CONFIG = {

    "TEXT": { 
        key: "text", 
        value: "", 
        validator: Lang.isString, 
        suppressEvent: true 
    }, 

    "HELP_TEXT": { 
        key: "helptext" 
    },

    "URL": { 
        key: "url", 
        value: "#", 
        suppressEvent: true 
    }, 

    "TARGET": { 
        key: "target", 
        suppressEvent: true 
    }, 

    "EMPHASIS": { 
        key: "emphasis", 
        value: false, 
        validator: Lang.isBoolean, 
        suppressEvent: true 
    }, 

    "STRONG_EMPHASIS": { 
        key: "strongemphasis", 
        value: false, 
        validator: Lang.isBoolean, 
        suppressEvent: true 
    },

    "CHECKED": { 
        key: "checked", 
        value: false, 
        validator: Lang.isBoolean, 
        suppressEvent: true, 
        supercedes:["disabled"]
    }, 

    "DISABLED": { 
        key: "disabled", 
        value: false, 
        validator: Lang.isBoolean, 
        suppressEvent: true
    },

    "SELECTED": { 
        key: "selected", 
        value: false, 
        validator: Lang.isBoolean, 
        suppressEvent: true
    },

    "SUBMENU": { 
        key: "submenu"
    },

    "ONCLICK": { 
        key: "onclick"
    },

    "CLASS_NAME": { 
        key: "classname", 
        value: null, 
        validator: Lang.isString
    }

};


YAHOO.widget.MenuItem.prototype = {

    // Constants


    /**
    * @property COLLAPSED_SUBMENU_INDICATOR_TEXT
    * @description String representing the text for the <code>&#60;em&#62;</code>
    * element used for the submenu arrow indicator.
    * @default "Submenu collapsed.  Click to expand submenu."
    * @final
    * @type String
    */
    COLLAPSED_SUBMENU_INDICATOR_TEXT: 
        "Submenu collapsed.  Click to expand submenu.",


    /**
    * @property EXPANDED_SUBMENU_INDICATOR_TEXT
    * @description String representing the text for the submenu arrow indicator 
    * element (<code>&#60;em&#62;</code>) when the submenu is visible.
    * @default "Submenu expanded.  Click to collapse submenu."
    * @final
    * @type String
    */
    EXPANDED_SUBMENU_INDICATOR_TEXT: 
        "Submenu expanded.  Click to collapse submenu.",


    /**
    * @property DISABLED_SUBMENU_INDICATOR_TEXT
    * @description String representing the text for the submenu arrow indicator 
    * element (<code>&#60;em&#62;</code>) when the menu item is disabled.
    * @default "Submenu collapsed.  (Item disabled.)."
    * @final
    * @type String
    */
    DISABLED_SUBMENU_INDICATOR_TEXT: "Submenu collapsed.  (Item disabled.)",


    /**
    * @property CHECKED_TEXT
    * @description String representing the text to be used for the checked 
    * indicator element (<code>&#60;em&#62;</code>).
    * @default "Checked."
    * @final
    * @type String
    */
    CHECKED_TEXT: "Menu item checked.",
    
    
    /**
    * @property DISABLED_CHECKED_TEXT
    * @description String representing the text to be used for the checked 
    * indicator element (<code>&#60;em&#62;</code>) when the menu item 
    * is disabled.
    * @default "Checked. (Item disabled.)"
    * @final
    * @type String
    */
    DISABLED_CHECKED_TEXT: "Checked. (Item disabled.)",


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
    * @property _oText
    * @description Object reference to the menu item's text node.
    * @default null
    * @private
    * @type TextNode
    */
    _oText: null,
    
    
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
    * @property _oCheckedIndicator
    * @description Object reference to the menu item's checkmark image.
    * @default <a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/
    * level-one-html.html#ID-58190037">HTMLElement</a>
    * @private
    * @type <a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/
    * level-one-html.html#ID-58190037">HTMLElement</a>
    */
    _oCheckedIndicator: null,


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
	constructor: YAHOO.widget.MenuItem,


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
    * @property submenuIndicator
    * @description Object reference to the <code>&#60;em&#62;</code> element 
    * used to create the submenu indicator for the menu item.
    * @default <a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/
    * level-one-html.html#ID-58190037">HTMLElement</a>
    * @type <a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/
    * level-one-html.html#ID-58190037">HTMLElement</a>
    */
    submenuIndicator: null,


	/**
    * @property browser
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
    destroyEvent: null,


    /**
    * @event mouseOverEvent
    * @description Fires when the mouse has entered the menu item.  Passes 
    * back the DOM Event object as an argument.
    * @type YAHOO.util.CustomEvent
    */
    mouseOverEvent: null,


    /**
    * @event mouseOutEvent
    * @description Fires when the mouse has left the menu item.  Passes back 
    * the DOM Event object as an argument.
    * @type YAHOO.util.CustomEvent
    */
    mouseOutEvent: null,


    /**
    * @event mouseDownEvent
    * @description Fires when the user mouses down on the menu item.  Passes 
    * back the DOM Event object as an argument.
    * @type YAHOO.util.CustomEvent
    */
    mouseDownEvent: null,


    /**
    * @event mouseUpEvent
    * @description Fires when the user releases a mouse button while the mouse 
    * is over the menu item.  Passes back the DOM Event object as an argument.
    * @type YAHOO.util.CustomEvent
    */
    mouseUpEvent: null,


    /**
    * @event clickEvent
    * @description Fires when the user clicks the on the menu item.  Passes 
    * back the DOM Event object as an argument.
    * @type YAHOO.util.CustomEvent
    */
    clickEvent: null,


    /**
    * @event keyPressEvent
    * @description Fires when the user presses an alphanumeric key when the 
    * menu item has focus.  Passes back the DOM Event object as an argument.
    * @type YAHOO.util.CustomEvent
    */
    keyPressEvent: null,


    /**
    * @event keyDownEvent
    * @description Fires when the user presses a key when the menu item has 
    * focus.  Passes back the DOM Event object as an argument.
    * @type YAHOO.util.CustomEvent
    */
    keyDownEvent: null,


    /**
    * @event keyUpEvent
    * @description Fires when the user releases a key when the menu item has 
    * focus.  Passes back the DOM Event object as an argument.
    * @type YAHOO.util.CustomEvent
    */
    keyUpEvent: null,


    /**
    * @event focusEvent
    * @description Fires when the menu item receives focus.
    * @type YAHOO.util.CustomEvent
    */
    focusEvent: null,


    /**
    * @event blurEvent
    * @description Fires when the menu item loses the input focus.
    * @type YAHOO.util.CustomEvent
    */
    blurEvent: null,


    /**
    * @method init
    * @description The MenuItem class's initialization method. This method is 
    * automatically called by the constructor, and sets up all DOM references 
    * for pre-existing markup, and creates required markup if it is not 
    * already present.
    * @param {String} p_oObject String specifying the text of the menu item.
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
    init: function(p_oObject, p_oConfig) {


        if(!this.SUBMENU_TYPE) {
    
            this.SUBMENU_TYPE = Menu;
    
        }


        // Create the config object

        this.cfg = new YAHOO.util.Config(this);

        this.initDefaultConfig();

        var oConfig = this.cfg;


        if(Lang.isString(p_oObject)) {

            this._createRootNodeStructure();

            oConfig.setProperty("text", p_oObject);

        }
        else if(this._checkDOMNode(p_oObject)) {

            switch(p_oObject.tagName.toUpperCase()) {

                case "OPTION":

                    this._createRootNodeStructure();

                    oConfig.setProperty("text", p_oObject.text);

                    this.srcElement = p_oObject;

                break;

                case "OPTGROUP":

                    this._createRootNodeStructure();

                    oConfig.setProperty("text", p_oObject.label);

                    this.srcElement = p_oObject;

                    this._initSubTree();

                break;

                case "LI":

                    // Get the anchor node (if it exists)

                    var oAnchor = this._getFirstElement(p_oObject, "A"),
                        sURL = "#",
                        sTarget,
                        sText;


                    // Capture the "text" and/or the "URL"

                    if(oAnchor) {

                        sURL = oAnchor.getAttribute("href");
                        sTarget = oAnchor.getAttribute("target");

                        if(oAnchor.innerText) {
                
                            sText = oAnchor.innerText;
                
                        }
                        else {
                
                            var oRange = oAnchor.ownerDocument.createRange();
                
                            oRange.selectNodeContents(oAnchor);
                
                            sText = oRange.toString();             
                
                        }

                    }
                    else {

                        var oText = p_oObject.firstChild;

                        sText = oText.nodeValue;

                        oAnchor = document.createElement("a");
                        
                        oAnchor.setAttribute("href", sURL);

                        p_oObject.replaceChild(oAnchor, oText);
                        
                        oAnchor.appendChild(oText);

                    }


                    this.srcElement = p_oObject;
                    this.element = p_oObject;
                    this._oAnchor = oAnchor;
    

                    // Check if emphasis has been applied to the MenuItem

                    var oEmphasisNode = this._getFirstElement(oAnchor),
                        bEmphasis = false,
                        bStrongEmphasis = false;

                    if(oEmphasisNode) {

                        // Set a reference to the text node 

                        this._oText = oEmphasisNode.firstChild;

                        switch(oEmphasisNode.tagName.toUpperCase()) {

                            case "EM":

                                bEmphasis = true;

                            break;

                            case "STRONG":

                                bStrongEmphasis = true;

                            break;

                        }

                    }
                    else {

                        // Set a reference to the text node 

                        this._oText = oAnchor.firstChild;

                    }


                    /*
                        Set these properties silently to sync up the 
                        configuration object without making changes to the 
                        element's DOM
                    */ 

                    oConfig.setProperty("text", sText, true);
                    oConfig.setProperty("url", sURL, true);
                    oConfig.setProperty("target", sTarget, true);
                    oConfig.setProperty("emphasis", bEmphasis, true);
                    oConfig.setProperty(
                        "strongemphasis", 
                        bStrongEmphasis, 
                        true
                    );

                    this._initSubTree();

                break;

            }            

        }


        if(this.element) {

            var sId = this.element.id;

            if(!sId) {

                sId = this.id || Dom.generateId();

                this.element.id = sId;

            }

            this.id = sId;


            Dom.addClass(this.element, this.CSS_CLASS_NAME);


            // Create custom events

            var EVENT_TYPES = YAHOO.widget.MenuItem._EVENT_TYPES;

            this.mouseOverEvent = new CustomEvent(EVENT_TYPES.MOUSE_OVER, this);
            this.mouseOutEvent = new CustomEvent(EVENT_TYPES.MOUSE_OUT, this);
            this.mouseDownEvent = new CustomEvent(EVENT_TYPES.MOUSE_DOWN, this);
            this.mouseUpEvent = new CustomEvent(EVENT_TYPES.MOUSE_UP, this);
            this.clickEvent = new CustomEvent(EVENT_TYPES.CLICK, this);
            this.keyPressEvent = new CustomEvent(EVENT_TYPES.KEY_PRESS, this);
            this.keyDownEvent = new CustomEvent(EVENT_TYPES.KEY_DOWN, this);
            this.keyUpEvent = new CustomEvent(EVENT_TYPES.KEY_UP, this);
            this.focusEvent = new CustomEvent(EVENT_TYPES.FOCUS, this);
            this.blurEvent = new CustomEvent(EVENT_TYPES.BLUR, this);
            this.destroyEvent = new CustomEvent(EVENT_TYPES.DESTROY, this);

            if(p_oConfig) {
    
                oConfig.applyConfig(p_oConfig);
    
            }        

            oConfig.fireQueue();

        }

    },



    // Private methods


    /**
    * @method _getFirstElement
    * @description Returns an HTML element's first HTML element node.
    * @private
    * @param {<a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/
    * level-one-html.html#ID-58190037">HTMLElement</a>} p_oElement Object 
    * reference specifying the element to be evaluated.
    * @param {String} p_sTagName Optional. String specifying the tagname of 
    * the element to be retrieved.
    * @return {<a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/
    * level-one-html.html#ID-58190037">HTMLElement</a>}
    */
    _getFirstElement: function(p_oElement, p_sTagName) {
    
        var oFirstChild = p_oElement.firstChild,
            oElement;
    
        if(oFirstChild) {
    
            if(oFirstChild.nodeType == 1) {
    
                oElement = oFirstChild;
    
            }
            else {
    
                var oNextSibling = oFirstChild.nextSibling;
    
                if(oNextSibling && oNextSibling.nodeType == 1) {
                
                    oElement = oNextSibling;
                
                }
    
            }
    
        }


        if(p_sTagName) {

            return (oElement && oElement.tagName.toUpperCase() == p_sTagName) ? 
                oElement : false;

        }
        
        return oElement;

    },    


    /**
    * @method _checkDOMNode
    * @description Determines if an object is an HTML element.
    * @private
    * @param {Object} p_oObject Object to be evaluated.
    * @return {Boolean}
    */
    _checkDOMNode: function(p_oObject) {

        return (p_oObject && p_oObject.tagName);

    },


    /**
    * @method _createRootNodeStructure
    * @description Creates the core DOM structure for the menu item.
    * @private
    */
    _createRootNodeStructure: function () {

        var oTemplate = YAHOO.widget.MenuItem._MenuItemTemplate;

        if(!oTemplate) {

            oTemplate = document.createElement("li");
            oTemplate.innerHTML = "<a href=\"#\">s</a>";

            YAHOO.widget.MenuItem._MenuItemTemplate = oTemplate;

        }

        this.element = oTemplate.cloneNode(true);
        this._oAnchor = this.element.firstChild;
        this._oText = this._oAnchor.firstChild;

        this.element.appendChild(this._oAnchor);

    },


    /**
    * @method _initSubTree
    * @description Iterates the source element's childNodes collection and uses 
    * the child nodes to instantiate other menus.
    * @private
    */
    _initSubTree: function() {

        var oSrcEl = this.srcElement,
            oConfig = this.cfg;


        if(oSrcEl.childNodes.length > 0) {

            if(
                this.parent.lazyLoad && 
                this.parent.srcElement && 
                this.parent.srcElement.tagName.toUpperCase() == "SELECT"
            ) {

                oConfig.setProperty(
                        "submenu", 
                        { id: Dom.generateId(), itemdata: oSrcEl.childNodes }
                    );

            }
            else {

                var oNode = oSrcEl.firstChild,
                    aOptions = [];
    
                do {
    
                    if(oNode && oNode.tagName) {
    
                        switch(oNode.tagName.toUpperCase()) {
                
                            case "DIV":
                
                                oConfig.setProperty("submenu", oNode);
                
                            break;
         
                            case "OPTION":
        
                                aOptions[aOptions.length] = oNode;
        
                            break;
               
                        }
                    
                    }
                
                }        
                while((oNode = oNode.nextSibling));
    
    
                var nOptions = aOptions.length;
    
                if(nOptions > 0) {
    
                    var oMenu = new this.SUBMENU_TYPE(Dom.generateId());
                    
                    oConfig.setProperty("submenu", oMenu);
    
                    for(var n=0; n<nOptions; n++) {
        
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
    configText: function(p_sType, p_aArgs, p_oItem) {

        var sText = p_aArgs[0];


        if(this._oText) {

            this._oText.nodeValue = sText;

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
    configHelpText: function(p_sType, p_aArgs, p_oItem) {

        var me = this,
            oHelpText = p_aArgs[0],
            oEl = this.element,
            oConfig = this.cfg,
            aNodes = [oEl, this._oAnchor],
            oSubmenuIndicator = this.submenuIndicator;


        function initHelpText() {

            Dom.addClass(aNodes, "hashelptext");

            if(oConfig.getProperty("disabled")) {

                oConfig.refireEvent("disabled");

            }

            if(oConfig.getProperty("selected")) {

                oConfig.refireEvent("selected");

            }                

        }


        function removeHelpText() {

            Dom.removeClass(aNodes, "hashelptext");

            oEl.removeChild(me._oHelpTextEM);
            me._oHelpTextEM = null;

        }


        if(this._checkDOMNode(oHelpText)) {

            oHelpText.className = "helptext";

            if(this._oHelpTextEM) {
            
                this._oHelpTextEM.parentNode.replaceChild(
                    oHelpText, 
                    this._oHelpTextEM
                );

            }
            else {

                this._oHelpTextEM = oHelpText;

                oEl.insertBefore(this._oHelpTextEM, oSubmenuIndicator);

            }

            initHelpText();

        }
        else if(Lang.isString(oHelpText)) {

            if(oHelpText.length === 0) {

                removeHelpText();

            }
            else {

                if(!this._oHelpTextEM) {

                    this._oHelpTextEM = document.createElement("em");
                    this._oHelpTextEM.className = "helptext";

                    oEl.insertBefore(this._oHelpTextEM, oSubmenuIndicator);

                }

                this._oHelpTextEM.innerHTML = oHelpText;

                initHelpText();

            }

        }
        else if(!oHelpText && this._oHelpTextEM) {

            removeHelpText();

        }

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
    configURL: function(p_sType, p_aArgs, p_oItem) {

        var sURL = p_aArgs[0];

        if(!sURL) {

            sURL = "#";

        }

        this._oAnchor.setAttribute("href", sURL);

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
    configTarget: function(p_sType, p_aArgs, p_oItem) {

        var sTarget = p_aArgs[0],
            oAnchor = this._oAnchor;

        if(sTarget && sTarget.length > 0) {

            oAnchor.setAttribute("target", sTarget);

        }
        else {

            oAnchor.removeAttribute("target");
        
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
    configEmphasis: function(p_sType, p_aArgs, p_oItem) {

        var bEmphasis = p_aArgs[0],
            oAnchor = this._oAnchor,
            oText = this._oText,
            oConfig = this.cfg,
            oEM;


        if(bEmphasis && oConfig.getProperty("strongemphasis")) {

            oConfig.setProperty("strongemphasis", false);

        }


        if(oAnchor) {

            if(bEmphasis) {

                oEM = document.createElement("em");
                oEM.appendChild(oText);

                oAnchor.appendChild(oEM);

            }
            else {

                oEM = this._getFirstElement(oAnchor, "EM");

                if(oEM) {

                    oAnchor.removeChild(oEM);
                    oAnchor.appendChild(oText);

                }

            }

        }

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
    configStrongEmphasis: function(p_sType, p_aArgs, p_oItem) {

        var bStrongEmphasis = p_aArgs[0],
            oAnchor = this._oAnchor,
            oText = this._oText,
            oConfig = this.cfg,
            oStrong;

        if(bStrongEmphasis && oConfig.getProperty("emphasis")) {

            oConfig.setProperty("emphasis", false);

        }

        if(oAnchor) {

            if(bStrongEmphasis) {

                oStrong = document.createElement("strong");
                oStrong.appendChild(oText);

                oAnchor.appendChild(oStrong);

            }
            else {

                oStrong = this._getFirstElement(oAnchor, "STRONG");

                if(oStrong) {

                    oAnchor.removeChild(oStrong);
                    oAnchor.appendChild(oText);

                }

            }

        }

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
    configChecked: function(p_sType, p_aArgs, p_oItem) {
    
        var bChecked = p_aArgs[0],
            oEl = this.element,
            oConfig = this.cfg,
            oEM;


        if(bChecked) {

            var oTemplate = YAHOO.widget.MenuItem._CheckedIndicatorTemplate;

            if(!oTemplate) {

                oTemplate = document.createElement("em");
                oTemplate.innerHTML = this.CHECKED_TEXT;
                oTemplate.className = "checkedindicator";

                YAHOO.widget.MenuItem._CheckedIndicatorTemplate = oTemplate;

            }

            oEM = oTemplate.cloneNode(true);

            var oSubmenu = this.cfg.getProperty("submenu");

            if(oSubmenu && oSubmenu.element) {

                oEl.insertBefore(oEM, oSubmenu.element);

            }
            else {

                oEl.appendChild(oEM);

            }


            Dom.addClass(oEl, "checked");

            this._oCheckedIndicator = oEM;

            if(oConfig.getProperty("disabled")) {

                oConfig.refireEvent("disabled");

            }

            if(oConfig.getProperty("selected")) {

                oConfig.refireEvent("selected");

            }
        
        }
        else {

            oEM = this._oCheckedIndicator;

            Dom.removeClass(oEl, "checked");

            if(oEM) {

                oEl.removeChild(oEM);

            }

            this._oCheckedIndicator = null;
        
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
    configDisabled: function(p_sType, p_aArgs, p_oItem) {

        var bDisabled = p_aArgs[0],
            oConfig = this.cfg,
            oAnchor = this._oAnchor,
            aNodes = [this.element, oAnchor],
            oHelpText = this._oHelpTextEM,
            oCheckedIndicator = this._oCheckedIndicator,
            oSubmenuIndicator = this.submenuIndicator,
            i = 1;


        if(oHelpText) {

            i++;
            aNodes[i] = oHelpText;

        }


        if(oCheckedIndicator) {
            
            oCheckedIndicator.firstChild.nodeValue = bDisabled ? 
                this.DISABLED_CHECKED_TEXT : 
                this.CHECKED_TEXT;

            i++;
            aNodes[i] = oCheckedIndicator;
            
        }    


        if(oSubmenuIndicator) {

            oSubmenuIndicator.firstChild.nodeValue = bDisabled ? 
                this.DISABLED_SUBMENU_INDICATOR_TEXT : 
                this.COLLAPSED_SUBMENU_INDICATOR_TEXT;

            i++;
            aNodes[i] = oSubmenuIndicator;
        
        }


        if(bDisabled) {

            if(oConfig.getProperty("selected")) {

                oConfig.setProperty("selected", false);

            }

            oAnchor.removeAttribute("href");

            Dom.addClass(aNodes, "disabled");

        }
        else {

            oAnchor.setAttribute("href", oConfig.getProperty("url"));

            Dom.removeClass(aNodes, "disabled");

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
    configSelected: function(p_sType, p_aArgs, p_oItem) {

        if(!this.cfg.getProperty("disabled")) {

            var bSelected = p_aArgs[0],
                oHelpText = this._oHelpTextEM,
                oSubmenuIndicator = this.submenuIndicator,
                oCheckedIndicator = this._oCheckedIndicator,
                aNodes = [this.element, this._oAnchor],
                i = 1;


            if(oHelpText) {
    
                i++;
                aNodes[i] = oHelpText;
    
            }
            

            if(oSubmenuIndicator) {

                i++;
                aNodes[i] = oSubmenuIndicator;

            }


            if(oCheckedIndicator) {

                i++;
                aNodes[i] = oCheckedIndicator;
            
            }


            if(bSelected) {
    
                Dom.addClass(aNodes, "selected");
    
            }
            else {
    
                Dom.removeClass(aNodes, "selected");
    
            }

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
    configSubmenu: function(p_sType, p_aArgs, p_oItem) {

        var oEl = this.element,
            oSubmenu = p_aArgs[0],
            oSubmenuIndicator = this.submenuIndicator,
            oConfig = this.cfg,
            aNodes = [this.element, this._oAnchor],
            bLazyLoad = this.parent && this.parent.lazyLoad,
            oMenu;


        if(oSubmenu) {

            if(oSubmenu instanceof Menu) {

                oMenu = oSubmenu;
                oMenu.parent = this;
                oMenu.lazyLoad = bLazyLoad;

            }
            else if(
                typeof oSubmenu == "object" && 
                oSubmenu.id && 
                !oSubmenu.nodeType
            ) {

                var sSubmenuId = oSubmenu.id,
                    oSubmenuConfig = oSubmenu;

                oSubmenuConfig.lazyload = bLazyLoad;
                oSubmenuConfig.parent = this;

                oMenu = new this.SUBMENU_TYPE(sSubmenuId, oSubmenuConfig);


                // Set the value of the property to the Menu instance
                
                this.cfg.setProperty("submenu", oMenu, true);

            }
            else {

                oMenu = new this.SUBMENU_TYPE(
                                oSubmenu,
                                { lazyload: bLazyLoad, parent: this }                
                            );


                // Set the value of the property to the Menu instance
                
                this.cfg.setProperty("submenu", oMenu, true);

            }


            if(oMenu) {

                this._oSubmenu = oMenu;


                if(!oSubmenuIndicator) { 

                    var oTemplate = 
                            YAHOO.widget.MenuItem._oSubmenuIndicatorTemplate;

                    if(!oTemplate) {
                   
                        oTemplate = document.createElement("em");
                        oTemplate.innerHTML =  
                            this.COLLAPSED_SUBMENU_INDICATOR_TEXT;
                        oTemplate.className = "submenuindicator";
                        
                        YAHOO.widget.MenuItem._oSubmenuIndicatorTemplate = 
                            oTemplate;

                    }


                    oSubmenuIndicator = oTemplate.cloneNode(true);


                    if(oMenu.element.parentNode == oEl) {

                        if(this.browser == "opera") {

                            oEl.appendChild(oSubmenuIndicator);
                            
                            oMenu.renderEvent.subscribe(function() {

                                oSubmenuIndicator.parentNode.insertBefore(
                                                            oSubmenuIndicator, 
                                                            oMenu.element
                                                        );
                            
                            });
                
                        }
                        else {

                            oEl.insertBefore(oSubmenuIndicator, oMenu.element);
                        
                        }
                
                    }
                    else {

                        oEl.appendChild(oSubmenuIndicator);
                    
                    }

                    this.submenuIndicator = oSubmenuIndicator;

                }


                Dom.addClass(aNodes, "hassubmenu");


                if(oConfig.getProperty("disabled")) {

                    oConfig.refireEvent("disabled");

                }

                if(oConfig.getProperty("selected")) {

                    oConfig.refireEvent("selected");

                }                
            
            }

        }
        else {

            Dom.removeClass(aNodes, "hassubmenu");

            if(oSubmenuIndicator) {

                oEl.removeChild(oSubmenuIndicator);

            }

            if(this._oSubmenu) {

                this._oSubmenu.destroy();

            }

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
    configOnClick: function(p_sType, p_aArgs, p_oItem) {

        var oObject = p_aArgs[0];

        /*
            Remove any existing listeners if a "click" event handler has 
            already been specified.
        */

        if(
            this._oOnclickAttributeValue && 
            (this._oOnclickAttributeValue != oObject)
        ) {

            this.clickEvent.unsubscribe(
                                this._oOnclickAttributeValue.fn, 
                                this._oOnclickAttributeValue.obj
                            );

            this._oOnclickAttributeValue = null;

        }


        if(
            !this._oOnclickAttributeValue && 
            typeof oObject == "object" && 
            typeof oObject.fn == "function"
        ) {

            this.clickEvent.subscribe(
                    oObject.fn, 
                    (oObject.obj || this), 
                    oObject.scope
                );

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
    configClassName: function(p_sType, p_aArgs, p_oItem) {
    
        var sClassName = p_aArgs[0];
    
        if(this._sClassName) {
    
            Dom.removeClass(this.element, this._sClassName);
    
        }
    
        Dom.addClass(this.element, sClassName);
        this._sClassName = sClassName;
    
    },



    // Public methods


	/**
    * @method initDefaultConfig
	* @description Initializes an item's configurable properties.
	*/
	initDefaultConfig : function() {

        var oConfig = this.cfg,
            DEFAULT_CONFIG = YAHOO.widget.MenuItem._DEFAULT_CONFIG;


        // Define the configuration attributes

        /**
        * @config text
        * @description String specifying the text label for the menu item.  
        * When building a menu from existing HTML the value of this property
        * will be interpreted from the menu's markup.
        * @default ""
        * @type String
        */
        oConfig.addProperty(
            DEFAULT_CONFIG.TEXT.key, 
            { 
                handler: this.configText, 
                value: DEFAULT_CONFIG.TEXT.value, 
                validator: DEFAULT_CONFIG.TEXT.validator, 
                suppressEvent: DEFAULT_CONFIG.TEXT.suppressEvent 
            }
        );
        

        /**
        * @config helptext
        * @description String specifying additional instructional text to 
        * accompany the text for the nenu item.
        * @default null
        * @type String|<a href="http://www.w3.org/TR/
        * 2000/WD-DOM-Level-1-20000929/level-one-html.html#ID-58190037">
        * HTMLElement</a>
        */
        oConfig.addProperty(
            DEFAULT_CONFIG.HELP_TEXT.key,
            { handler: this.configHelpText }
        );


        /**
        * @config url
        * @description String specifying the URL for the menu item's anchor's 
        * "href" attribute.  When building a menu from existing HTML the value 
        * of this property will be interpreted from the menu's markup.
        * @default "#"
        * @type String
        */        
        oConfig.addProperty(
            DEFAULT_CONFIG.URL.key, 
            {
                handler: this.configURL, 
                value: DEFAULT_CONFIG.URL.value, 
                suppressEvent: DEFAULT_CONFIG.URL.suppressEvent
            }
        );


        /**
        * @config target
        * @description String specifying the value for the "target" attribute 
        * of the menu item's anchor element. <strong>Specifying a target will 
        * require the user to click directly on the menu item's anchor node in
        * order to cause the browser to navigate to the specified URL.</strong> 
        * When building a menu from existing HTML the value of this property 
        * will be interpreted from the menu's markup.
        * @default null
        * @type String
        */        
        oConfig.addProperty(
            DEFAULT_CONFIG.TARGET.key, 
            {
                handler: this.configTarget, 
                suppressEvent: DEFAULT_CONFIG.TARGET.suppressEvent
            }
        );


        /**
        * @config emphasis
        * @description Boolean indicating if the text of the menu item will be 
        * rendered with emphasis.  When building a menu from existing HTML the 
        * value of this property will be interpreted from the menu's markup.
        * @default false
        * @type Boolean
        */
        oConfig.addProperty(
            DEFAULT_CONFIG.EMPHASIS.key, 
            { 
                handler: this.configEmphasis, 
                value: DEFAULT_CONFIG.EMPHASIS.value, 
                validator: DEFAULT_CONFIG.EMPHASIS.validator, 
                suppressEvent: DEFAULT_CONFIG.EMPHASIS.suppressEvent 
            }
        );


        /**
        * @config strongemphasis
        * @description Boolean indicating if the text of the menu item will be 
        * rendered with strong emphasis.  When building a menu from existing 
        * HTML the value of this property will be interpreted from the
        * menu's markup.
        * @default false
        * @type Boolean
        */
        oConfig.addProperty(
            DEFAULT_CONFIG.STRONG_EMPHASIS.key,
            {
                handler: this.configStrongEmphasis,
                value: DEFAULT_CONFIG.STRONG_EMPHASIS.value,
                validator: DEFAULT_CONFIG.STRONG_EMPHASIS.validator,
                suppressEvent: DEFAULT_CONFIG.STRONG_EMPHASIS.suppressEvent
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
            DEFAULT_CONFIG.CHECKED.key, 
            {
                handler: this.configChecked, 
                value: DEFAULT_CONFIG.CHECKED.value, 
                validator: DEFAULT_CONFIG.CHECKED.validator, 
                suppressEvent: DEFAULT_CONFIG.CHECKED.suppressEvent,
                supercedes: DEFAULT_CONFIG.CHECKED.supercedes
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
            DEFAULT_CONFIG.DISABLED.key,
            {
                handler: this.configDisabled,
                value: DEFAULT_CONFIG.DISABLED.value,
                validator: DEFAULT_CONFIG.DISABLED.validator,
                suppressEvent: DEFAULT_CONFIG.DISABLED.suppressEvent
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
            DEFAULT_CONFIG.SELECTED.key,
            {
                handler: this.configSelected,
                value: DEFAULT_CONFIG.SELECTED.value,
                validator: DEFAULT_CONFIG.SELECTED.validator,
                suppressEvent: DEFAULT_CONFIG.SELECTED.suppressEvent
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
            DEFAULT_CONFIG.SUBMENU.key, 
            { handler: this.configSubmenu }
        );


        /**
        * @config onclick
        * @description Object literal representing the code to be executed when 
        * the button is clicked.  Format:<br> <code> {<br> 
        * <strong>fn:</strong> Function,   &#47;&#47; The handler to call when 
        * the event fires.<br> <strong>obj:</strong> Object, &#47;&#47; An 
        * object to  pass back to the handler.<br> <strong>scope:</strong> 
        * Object &#47;&#47; The object to use for the scope of the handler.
        * <br> } </code>
        * @type Object
        * @default null
        */
        oConfig.addProperty(
            DEFAULT_CONFIG.ONCLICK.key, 
            { handler: this.configOnClick }
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
            DEFAULT_CONFIG.CLASS_NAME.key, 
            { 
                handler: this.configClassName,
                value: DEFAULT_CONFIG.CLASS_NAME.value, 
                validator: DEFAULT_CONFIG.CLASS_NAME.validator
            }
        );

	},


    /**
    * @method getNextEnabledSibling
    * @description Finds the menu item's next enabled sibling.
    * @return YAHOO.widget.MenuItem
    */
    getNextEnabledSibling: function() {

        if(this.parent instanceof Menu) {

            var nGroupIndex = this.groupIndex;

            function getNextArrayItem(p_aArray, p_nStartIndex) {
    
                return p_aArray[p_nStartIndex] || 
                    getNextArrayItem(p_aArray, (p_nStartIndex+1));
    
            }
    
    
            var aItemGroups = this.parent.getItemGroups(),
                oNextItem;
    
    
            if(this.index < (aItemGroups[nGroupIndex].length - 1)) {
    
                oNextItem = getNextArrayItem(
                        aItemGroups[nGroupIndex], 
                        (this.index+1)
                    );
    
            }
            else {
    
                var nNextGroupIndex;
    
                if(nGroupIndex < (aItemGroups.length - 1)) {
    
                    nNextGroupIndex = nGroupIndex + 1;
    
                }
                else {
    
                    nNextGroupIndex = 0;
    
                }
    
                var aNextGroup = getNextArrayItem(aItemGroups, nNextGroupIndex);
    
                // Retrieve the first menu item in the next group
    
                oNextItem = getNextArrayItem(aNextGroup, 0);
    
            }
    
            return (
                oNextItem.cfg.getProperty("disabled") || 
                oNextItem.element.style.display == "none"
            ) ? 
            oNextItem.getNextEnabledSibling() : oNextItem;

        }

    },


    /**
    * @method getPreviousEnabledSibling
    * @description Finds the menu item's previous enabled sibling.
    * @return {YAHOO.widget.MenuItem}
    */
    getPreviousEnabledSibling: function() {

       if(this.parent instanceof Menu) {

            var nGroupIndex = this.groupIndex;

            function getPreviousArrayItem(p_aArray, p_nStartIndex) {
    
                return p_aArray[p_nStartIndex] || 
                    getPreviousArrayItem(p_aArray, (p_nStartIndex-1));
    
            }

            function getFirstItemIndex(p_aArray, p_nStartIndex) {
    
                return p_aArray[p_nStartIndex] ? 
                    p_nStartIndex : 
                    getFirstItemIndex(p_aArray, (p_nStartIndex+1));
    
            }
    
            var aItemGroups = this.parent.getItemGroups(),
                oPreviousItem;
    
            if(
                this.index > getFirstItemIndex(aItemGroups[nGroupIndex], 0)
            ) {
    
                oPreviousItem = 
                    getPreviousArrayItem(
                        aItemGroups[nGroupIndex], 
                        (this.index-1)
                    );
    
            }
            else {
    
                var nPreviousGroupIndex;
    
                if(nGroupIndex > getFirstItemIndex(aItemGroups, 0)) {
    
                    nPreviousGroupIndex = nGroupIndex - 1;
    
                }
                else {
    
                    nPreviousGroupIndex = aItemGroups.length - 1;
    
                }
    
                var aPreviousGroup = 
                        getPreviousArrayItem(aItemGroups, nPreviousGroupIndex);
    
                oPreviousItem = 
                    getPreviousArrayItem(
                        aPreviousGroup, 
                        (aPreviousGroup.length - 1)
                    );
    
            }

            return (
                oPreviousItem.cfg.getProperty("disabled") || 
                oPreviousItem.element.style.display == "none"
            ) ? 
            oPreviousItem.getPreviousEnabledSibling() : oPreviousItem;

        }

    },


    /**
    * @method focus
    * @description Causes the menu item to receive the focus and fires the 
    * focus event.
    */
    focus: function() {

        var oParent = this.parent,
            oAnchor = this._oAnchor,
            oActiveItem = oParent.activeItem,
            me = this;


        function setFocus() {

            try {

                if (
                    (me.browser == "ie" || me.browser == "ie7") && 
                    !document.hasFocus()
                ) {
                
                    return;
                
                }

                oAnchor.focus();

            }
            catch(e) {
            
            }

        }


        if(
            !this.cfg.getProperty("disabled") && 
            oParent && 
            oParent.cfg.getProperty("visible") && 
            this.element.style.display != "none"
        ) {

            if(oActiveItem) {

                oActiveItem.blur();

            }


            /*
                Setting focus via a timer fixes a race condition in Firefox, IE 
                and Opera where the browser viewport jumps as it trys to 
                position and focus the menu.
            */

            window.setTimeout(setFocus, 0);
            
            this.focusEvent.fire();

        }

    },


    /**
    * @method blur
    * @description Causes the menu item to lose focus and fires the 
    * blur event.
    */    
    blur: function() {

        var oParent = this.parent;

        if(
            !this.cfg.getProperty("disabled") && 
            oParent && 
            Dom.getStyle(oParent.element, "visibility") == "visible"
        ) {

            this._oAnchor.blur();

            this.blurEvent.fire();

        }

    },


    /**
    * @method hasFocus
    * @description Returns a boolean indicating whether or not the menu item
    * has focus.
    * @return {Boolean}
    */
    hasFocus: function() {
    
        return (YAHOO.widget.MenuManager.getFocusedMenuItem() == this);
    
    },


	/**
    * @method destroy
	* @description Removes the menu item's <code>&#60;li&#62;</code> element 
	* from its parent <code>&#60;ul&#62;</code> element.
	*/
    destroy: function() {

        var oEl = this.element;

        if(oEl) {


            // If the item has a submenu, destroy it first

            var oSubmenu = this.cfg.getProperty("submenu");

            if(oSubmenu) {
            
                oSubmenu.destroy();
            
            }


            // Remove CustomEvent listeners
    
            this.mouseOverEvent.unsubscribeAll();
            this.mouseOutEvent.unsubscribeAll();
            this.mouseDownEvent.unsubscribeAll();
            this.mouseUpEvent.unsubscribeAll();
            this.clickEvent.unsubscribeAll();
            this.keyPressEvent.unsubscribeAll();
            this.keyDownEvent.unsubscribeAll();
            this.keyUpEvent.unsubscribeAll();
            this.focusEvent.unsubscribeAll();
            this.blurEvent.unsubscribeAll();
            this.cfg.configChangedEvent.unsubscribeAll();


            // Remove the element from the parent node

            var oParentNode = oEl.parentNode;

            if(oParentNode) {

                oParentNode.removeChild(oEl);

                this.destroyEvent.fire();

            }

            this.destroyEvent.unsubscribeAll();

        }

    },


    /**
    * @method toString
    * @description Returns a string representing the menu item.
    * @return {String}
    */
    toString: function() {

        var sReturnVal = "MenuItem";

        if(this.cfg && this.cfg.getProperty("text")) {
    
            sReturnVal += (": " + this.cfg.getProperty("text"));
    
        }

        return sReturnVal;
    
    }

};

})();



/**
* Creates a list of options or commands which are made visible in response to 
* an HTML element's "contextmenu" event ("mousedown" for Opera).
*
* @param {String} p_oElement String specifying the id attribute of the 
* <code>&#60;div&#62;</code> element of the context menu.
* @param {String} p_oElement String specifying the id attribute of the 
* <code>&#60;select&#62;</code> element to be used as the data source for the 
* context menu.
* @param {<a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/level-one-
* html.html#ID-22445964">HTMLDivElement</a>} p_oElement Object specifying the 
* <code>&#60;div&#62;</code> element of the context menu.
* @param {<a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/level-one-
* html.html#ID-94282980">HTMLSelectElement</a>} p_oElement Object specifying 
* the <code>&#60;select&#62;</code> element to be used as the data source for 
* the context menu.
* @param {Object} p_oConfig Optional. Object literal specifying the 
* configuration for the context menu. See configuration class documentation 
* for more details.
* @class ContextMenu
* @constructor
* @extends YAHOO.widget.Menu
* @namespace YAHOO.widget
*/
YAHOO.widget.ContextMenu = function(p_oElement, p_oConfig) {

    YAHOO.widget.ContextMenu.superclass.constructor.call(
            this, 
            p_oElement,
            p_oConfig
        );

};


/**
* Constant representing the name of the ContextMenu's events
* @property YAHOO.widget.ContextMenu._EVENT_TYPES
* @private
* @final
* @type Object
*/
YAHOO.widget.ContextMenu._EVENT_TYPES = {

    "TRIGGER_CONTEXT_MENU": "triggerContextMenu",

    "CONTEXT_MENU": (
                        (YAHOO.widget.Module.prototype.browser == "opera" ? 
                            "mousedown" : "contextmenu")
                    ),
    "CLICK": "click"

};


/**
* Constant representing the ContextMenu's configuration properties
* @property YAHOO.widget.ContextMenu._DEFAULT_CONFIG
* @private
* @final
* @type Object
*/
YAHOO.widget.ContextMenu._DEFAULT_CONFIG = {

    "TRIGGER": { 
        key: "trigger" 
    }

};


YAHOO.lang.extend(YAHOO.widget.ContextMenu, YAHOO.widget.Menu, {



// Private properties


/**
* @property _oTrigger
* @description Object reference to the current value of the "trigger" 
* configuration property.
* @default null
* @private
* @type String|<a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/leve
* l-one-html.html#ID-58190037">HTMLElement</a>|Array
*/
_oTrigger: null,


/**
* @property _bCancelled
* @description Boolean indicating if the display of the context menu should 
* be cancelled.
* @default false
* @private
* @type Boolean
*/
_bCancelled: false,



// Public properties


/**
* @property contextEventTarget
* @description Object reference for the HTML element that was the target of the
* "contextmenu" DOM event ("mousedown" for Opera) that triggered the display of 
* the context menu.
* @default null
* @type <a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/level-one-
* html.html#ID-58190037">HTMLElement</a>
*/
contextEventTarget: null,



// Events


/**
* @event triggerContextMenuEvent
* @description Custom Event wrapper for the "contextmenu" DOM event 
* ("mousedown" for Opera) fired by the element(s) that trigger the display of 
* the context menu.
*/
triggerContextMenuEvent: null,



/**
* @method init
* @description The ContextMenu class's initialization method. This method is 
* automatically called by the constructor, and sets up all DOM references for 
* pre-existing markup, and creates required markup if it is not already present.
* @param {String} p_oElement String specifying the id attribute of the 
* <code>&#60;div&#62;</code> element of the context menu.
* @param {String} p_oElement String specifying the id attribute of the 
* <code>&#60;select&#62;</code> element to be used as the data source for 
* the context menu.
* @param {<a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/level-one-
* html.html#ID-22445964">HTMLDivElement</a>} p_oElement Object specifying the 
* <code>&#60;div&#62;</code> element of the context menu.
* @param {<a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/level-one-
* html.html#ID-94282980">HTMLSelectElement</a>} p_oElement Object specifying 
* the <code>&#60;select&#62;</code> element to be used as the data source for 
* the context menu.
* @param {Object} p_oConfig Optional. Object literal specifying the 
* configuration for the context menu. See configuration class documentation 
* for more details.
*/
init: function(p_oElement, p_oConfig) {

    if(!this.ITEM_TYPE) {

        this.ITEM_TYPE = YAHOO.widget.ContextMenuItem;

    }


    // Call the init of the superclass (YAHOO.widget.Menu)

    YAHOO.widget.ContextMenu.superclass.init.call(this, p_oElement);


    this.beforeInitEvent.fire(YAHOO.widget.ContextMenu);


    if(p_oConfig) {

        this.cfg.applyConfig(p_oConfig, true);

    }
    
    
    this.initEvent.fire(YAHOO.widget.ContextMenu);
    
},


/**
* @method initEvents
* @description Initializes the custom events for the context menu.
*/
initEvents: function() {

	YAHOO.widget.ContextMenu.superclass.initEvents.call(this);

    // Create custom events

    this.triggerContextMenuEvent = 

            new YAHOO.util.CustomEvent(
                    YAHOO.widget.ContextMenu._EVENT_TYPES.TRIGGER_CONTEXT_MENU, 
                    this
                );

},


/**
* @method cancel
* @description Cancels the display of the context menu.
*/
cancel: function() {

    this._bCancelled = true;

},



// Private methods


/**
* @method _removeEventHandlers
* @description Removes all of the DOM event handlers from the HTML element(s) 
* whose "context menu" event ("click" for Opera) trigger the display of 
* the context menu.
* @private
*/
_removeEventHandlers: function() {

    var Event = YAHOO.util.Event,
        oTrigger = this._oTrigger;


    // Remove the event handlers from the trigger(s)

    if (oTrigger) {

        Event.removeListener(
            oTrigger, 
            YAHOO.widget.ContextMenu._EVENT_TYPES.CONTEXT_MENU, 
            this._onTriggerContextMenu
        );    
        
        if(this.browser == "opera") {
        
            Event.removeListener(
                oTrigger, 
                YAHOO.widget.ContextMenu._EVENT_TYPES.CLICK, 
                this._onTriggerClick
            );
    
        }

    }

},



// Private event handlers


/**
* @method _onTriggerClick
* @description "click" event handler for the HTML element(s) identified as the 
* "trigger" for the context menu.  Used to cancel default behaviors in Opera.
* @private
* @param {Event} p_oEvent Object representing the DOM event object passed back 
* by the event utility (YAHOO.util.Event).
* @param {YAHOO.widget.ContextMenu} p_oMenu Object representing the context 
* menu that is handling the event.
*/
_onTriggerClick: function(p_oEvent, p_oMenu) {

    if(p_oEvent.ctrlKey) {
    
        YAHOO.util.Event.stopEvent(p_oEvent);

    }
    
},


/**
* @method _onTriggerContextMenu
* @description "contextmenu" event handler ("mousedown" for Opera) for the HTML 
* element(s) that trigger the display of the context menu.
* @private
* @param {Event} p_oEvent Object representing the DOM event object passed back 
* by the event utility (YAHOO.util.Event).
* @param {YAHOO.widget.ContextMenu} p_oMenu Object representing the context 
* menu that is handling the event.
*/
_onTriggerContextMenu: function(p_oEvent, p_oMenu) {

    var Event = YAHOO.util.Event;

    if(p_oEvent.type == "mousedown" && !p_oEvent.ctrlKey) {

        return;

    }


    /*
        Prevent the browser's default context menu from appearing and 
        stop the propagation of the "contextmenu" event so that 
        other ContextMenu instances are not displayed.
    */

    Event.stopEvent(p_oEvent);


    // Hide any other ContextMenu instances that might be visible

    YAHOO.widget.MenuManager.hideVisible();


    this.contextEventTarget = Event.getTarget(p_oEvent);

    this.triggerContextMenuEvent.fire(p_oEvent);


    if(!this._bCancelled) {

        // Position and display the context menu
    
        this.cfg.setProperty("xy", Event.getXY(p_oEvent));

        this.show();

    }

    this._bCancelled = false;

},



// Public methods


/**
* @method toString
* @description Returns a string representing the context menu.
* @return {String}
*/
toString: function() {

    var sReturnVal = "ContextMenu",
        sId = this.id;

    if(sId) {

        sReturnVal += (" " + sId);
    
    }

    return sReturnVal;

},


/**
* @method initDefaultConfig
* @description Initializes the class's configurable properties which can be 
* changed using the context menu's Config object ("cfg").
*/
initDefaultConfig: function() {

    YAHOO.widget.ContextMenu.superclass.initDefaultConfig.call(this);

    /**
    * @config trigger
    * @description The HTML element(s) whose "contextmenu" event ("mousedown" 
    * for Opera) trigger the display of the context menu.  Can be a string 
    * representing the id attribute of the HTML element, an object reference 
    * for the HTML element, or an array of strings or HTML element references.
    * @default null
    * @type String|<a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/
    * level-one-html.html#ID-58190037">HTMLElement</a>|Array
    */
    this.cfg.addProperty(
        YAHOO.widget.ContextMenu._DEFAULT_CONFIG.TRIGGER.key, 
        { handler: this.configTrigger }
    );

},


/**
* @method destroy
* @description Removes the context menu's <code>&#60;div&#62;</code> element 
* (and accompanying child nodes) from the document.
*/
destroy: function() {

    // Remove the DOM event handlers from the current trigger(s)

    this._removeEventHandlers();
    

    // Continue with the superclass implementation of this method

    YAHOO.widget.ContextMenu.superclass.destroy.call(this);

},



// Public event handlers for configuration properties


/**
* @method configTrigger
* @description Event handler for when the value of the "trigger" configuration 
* property changes. 
* @param {String} p_sType String representing the name of the event that 
* was fired.
* @param {Array} p_aArgs Array of arguments sent when the event was fired.
* @param {YAHOO.widget.ContextMenu} p_oMenu Object representing the context 
* menu that fired the event.
*/
configTrigger: function(p_sType, p_aArgs, p_oMenu) {
    
    var Event = YAHOO.util.Event,
        oTrigger = p_aArgs[0];

    if(oTrigger) {

        /*
            If there is a current "trigger" - remove the event handlers 
            from that element(s) before assigning new ones
        */

        if(this._oTrigger) {
        
            this._removeEventHandlers();

        }

        this._oTrigger = oTrigger;


        /*
            Listen for the "mousedown" event in Opera b/c it does not 
            support the "contextmenu" event
        */ 
  
        Event.on(
            oTrigger, 
            YAHOO.widget.ContextMenu._EVENT_TYPES.CONTEXT_MENU, 
            this._onTriggerContextMenu,
            this,
            true
        );


        /*
            Assign a "click" event handler to the trigger element(s) for
            Opera to prevent default browser behaviors.
        */

        if(this.browser == "opera") {
        
            Event.on(
                oTrigger, 
                YAHOO.widget.ContextMenu._EVENT_TYPES.CLICK, 
                this._onTriggerClick,
                this,
                true
            );

        }

    }
    else {
   
        this._removeEventHandlers();
    
    }
    
}

}); // END YAHOO.lang.extend



/**
* Creates an item for a context menu.
* 
* @param {String} p_oObject String specifying the text of the context menu item.
* @param {<a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/level-
* one-html.html#ID-74680021">HTMLLIElement</a>} p_oObject Object specifying the 
* <code>&#60;li&#62;</code> element of the context menu item.
* @param {<a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/level-
* one-html.html#ID-38450247">HTMLOptGroupElement</a>} p_oObject Object 
* specifying the <code>&#60;optgroup&#62;</code> element of the context 
* menu item.
* @param {<a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/level-
* one-html.html#ID-70901257">HTMLOptionElement</a>} p_oObject Object specifying 
* the <code>&#60;option&#62;</code> element of the context menu item.
* @param {Object} p_oConfig Optional. Object literal specifying the 
* configuration for the context menu item. See configuration class 
* documentation for more details.
* @class ContextMenuItem
* @constructor
* @extends YAHOO.widget.MenuItem
*/
YAHOO.widget.ContextMenuItem = function(p_oObject, p_oConfig) {

    YAHOO.widget.ContextMenuItem.superclass.constructor.call(
        this, 
        p_oObject, 
        p_oConfig
    );

};

YAHOO.lang.extend(YAHOO.widget.ContextMenuItem, YAHOO.widget.MenuItem, {


/**
* @method init
* @description The ContextMenuItem class's initialization method. This method 
* is automatically called by the constructor, and sets up all DOM references 
* for pre-existing markup, and creates required markup if it is not 
* already present.
* @param {String} p_oObject String specifying the text of the context menu item.
* @param {<a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/level-
* one-html.html#ID-74680021">HTMLLIElement</a>} p_oObject Object specifying the 
* <code>&#60;li&#62;</code> element of the context menu item.
* @param {<a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/level-
* one-html.html#ID-38450247">HTMLOptGroupElement</a>} p_oObject Object 
* specifying the <code>&#60;optgroup&#62;</code> element of the context 
* menu item.
* @param {<a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/level-
* one-html.html#ID-70901257">HTMLOptionElement</a>} p_oObject Object specifying 
* the <code>&#60;option&#62;</code> element of the context menu item.
* @param {Object} p_oConfig Optional. Object literal specifying the 
* configuration for the context menu item. See configuration class 
* documentation for more details.
*/
init: function(p_oObject, p_oConfig) {
    
    if(!this.SUBMENU_TYPE) {

        this.SUBMENU_TYPE = YAHOO.widget.ContextMenu;

    }


    /* 
        Call the init of the superclass (YAHOO.widget.MenuItem)
        Note: We don't pass the user config in here yet 
        because we only want it executed once, at the lowest 
        subclass level.
    */ 

    YAHOO.widget.ContextMenuItem.superclass.init.call(this, p_oObject);

    var oConfig = this.cfg;

    if(p_oConfig) {

        oConfig.applyConfig(p_oConfig, true);

    }

    oConfig.fireQueue();

},



// Public methods


/**
* @method toString
* @description Returns a string representing the context menu item.
* @return {String}
*/
toString: function() {

    var sReturnVal = "ContextMenuItem";

    if(this.cfg && this.cfg.getProperty("text")) {

        sReturnVal += (": " + this.cfg.getProperty("text"));

    }

    return sReturnVal;

}
    
}); // END YAHOO.lang.extend



/**
* Horizontal collection of items, each of which can contain a submenu.
* 
* @param {String} p_oElement String specifying the id attribute of the 
* <code>&#60;div&#62;</code> element of the menu bar.
* @param {String} p_oElement String specifying the id attribute of the 
* <code>&#60;select&#62;</code> element to be used as the data source for the 
* menu bar.
* @param {<a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/level-
* one-html.html#ID-22445964">HTMLDivElement</a>} p_oElement Object specifying 
* the <code>&#60;div&#62;</code> element of the menu bar.
* @param {<a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/level-
* one-html.html#ID-94282980">HTMLSelectElement</a>} p_oElement Object 
* specifying the <code>&#60;select&#62;</code> element to be used as the data 
* source for the menu bar.
* @param {Object} p_oConfig Optional. Object literal specifying the 
* configuration for the menu bar. See configuration class documentation for
* more details.
* @class Menubar
* @constructor
* @extends YAHOO.widget.Menu
* @namespace YAHOO.widget
*/
YAHOO.widget.MenuBar = function(p_oElement, p_oConfig) {

    YAHOO.widget.MenuBar.superclass.constructor.call(
            this, 
            p_oElement,
            p_oConfig
        );

};


/**
* Constant representing the MenuBar's configuration properties
* @property YAHOO.widget.MenuBar._DEFAULT_CONFIG
* @private
* @final
* @type Object
*/
YAHOO.widget.MenuBar._DEFAULT_CONFIG = {

    "POSITION": { 
        key: "position", 
        value: "static", 
        validator: YAHOO.widget.Menu._checkPosition, 
        supercedes: ["visible"] 
    }, 

    "SUBMENU_ALIGNMENT": { 
        key: "submenualignment", 
        value: ["tl","bl"] 
    },

    "AUTO_SUBMENU_DISPLAY": { 
        key: "autosubmenudisplay", 
        value: false, 
        validator: YAHOO.lang.isBoolean 
    }

};



YAHOO.lang.extend(YAHOO.widget.MenuBar, YAHOO.widget.Menu, {

/**
* @method init
* @description The MenuBar class's initialization method. This method is 
* automatically called by the constructor, and sets up all DOM references for 
* pre-existing markup, and creates required markup if it is not already present.
* @param {String} p_oElement String specifying the id attribute of the 
* <code>&#60;div&#62;</code> element of the menu bar.
* @param {String} p_oElement String specifying the id attribute of the 
* <code>&#60;select&#62;</code> element to be used as the data source for the 
* menu bar.
* @param {<a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/level-
* one-html.html#ID-22445964">HTMLDivElement</a>} p_oElement Object specifying 
* the <code>&#60;div&#62;</code> element of the menu bar.
* @param {<a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/level-
* one-html.html#ID-94282980">HTMLSelectElement</a>} p_oElement Object 
* specifying the <code>&#60;select&#62;</code> element to be used as the data 
* source for the menu bar.
* @param {Object} p_oConfig Optional. Object literal specifying the 
* configuration for the menu bar. See configuration class documentation for
* more details.
*/
init: function(p_oElement, p_oConfig) {

    if(!this.ITEM_TYPE) {

        this.ITEM_TYPE = YAHOO.widget.MenuBarItem;

    }


    // Call the init of the superclass (YAHOO.widget.Menu)

    YAHOO.widget.MenuBar.superclass.init.call(this, p_oElement);


    this.beforeInitEvent.fire(YAHOO.widget.MenuBar);


    if(p_oConfig) {

        this.cfg.applyConfig(p_oConfig, true);

    }

    this.initEvent.fire(YAHOO.widget.MenuBar);

},



// Constants


/**
* @property CSS_CLASS_NAME
* @description String representing the CSS class(es) to be applied to the menu 
* bar's <code>&#60;div&#62;</code> element.
* @default "yuimenubar"
* @final
* @type String
*/
CSS_CLASS_NAME: "yuimenubar",



// Protected event handlers


/**
* @method _onKeyDown
* @description "keydown" Custom Event handler for the menu bar.
* @private
* @param {String} p_sType String representing the name of the event that 
* was fired.
* @param {Array} p_aArgs Array of arguments sent when the event was fired.
* @param {YAHOO.widget.MenuBar} p_oMenuBar Object representing the menu bar 
* that fired the event.
*/
_onKeyDown: function(p_sType, p_aArgs, p_oMenuBar) {

    var Event = YAHOO.util.Event,
        oEvent = p_aArgs[0],
        oItem = p_aArgs[1],
        oSubmenu;


    if(oItem && !oItem.cfg.getProperty("disabled")) {

        var oItemCfg = oItem.cfg;

        switch(oEvent.keyCode) {
    
            case 37:    // Left arrow
            case 39:    // Right arrow
    
                if(
                    oItem == this.activeItem && 
                    !oItemCfg.getProperty("selected")
                ) {
    
                    oItemCfg.setProperty("selected", true);
    
                }
                else {
    
                    var oNextItem = (oEvent.keyCode == 37) ? 
                            oItem.getPreviousEnabledSibling() : 
                            oItem.getNextEnabledSibling();
            
                    if(oNextItem) {
    
                        this.clearActiveItem();
    
                        oNextItem.cfg.setProperty("selected", true);
    
    
                        if(this.cfg.getProperty("autosubmenudisplay")) {
                        
                            oSubmenu = oNextItem.cfg.getProperty("submenu");
                            
                            if(oSubmenu) {
                        
                                oSubmenu.show();
                            
                            }
                
                        }           
    
                        oNextItem.focus();
    
                    }
    
                }
    
                Event.preventDefault(oEvent);
    
            break;
    
            case 40:    // Down arrow
    
                if(this.activeItem != oItem) {
    
                    this.clearActiveItem();
    
                    oItemCfg.setProperty("selected", true);
                    oItem.focus();
                
                }
    
                oSubmenu = oItemCfg.getProperty("submenu");
    
                if(oSubmenu) {
    
                    if(oSubmenu.cfg.getProperty("visible")) {
    
                        oSubmenu.setInitialSelection();
                        oSubmenu.setInitialFocus();
                    
                    }
                    else {
    
                        oSubmenu.show();
                    
                    }
    
                }
    
                Event.preventDefault(oEvent);
    
            break;
    
        }

    }


    if(oEvent.keyCode == 27 && this.activeItem) { // Esc key

        oSubmenu = this.activeItem.cfg.getProperty("submenu");

        if(oSubmenu && oSubmenu.cfg.getProperty("visible")) {
        
            oSubmenu.hide();
            this.activeItem.focus();
        
        }
        else {

            this.activeItem.cfg.setProperty("selected", false);
            this.activeItem.blur();
    
        }

        Event.preventDefault(oEvent);
    
    }

},


/**
* @method _onClick
* @description "click" event handler for the menu bar.
* @protected
* @param {String} p_sType String representing the name of the event that 
* was fired.
* @param {Array} p_aArgs Array of arguments sent when the event was fired.
* @param {YAHOO.widget.MenuBar} p_oMenuBar Object representing the menu bar 
* that fired the event.
*/
_onClick: function(p_sType, p_aArgs, p_oMenuBar) {

    YAHOO.widget.MenuBar.superclass._onClick.call(
        this, 
        p_sType, 
        p_aArgs, 
        p_oMenuBar
    );


    var oItem = p_aArgs[1];
    
    if(oItem && !oItem.cfg.getProperty("disabled")) {

         var Event = YAHOO.util.Event,
             Dom = YAHOO.util.Dom,
    
             oEvent = p_aArgs[0],
             oTarget = Event.getTarget(oEvent),
    
             oActiveItem = this.activeItem,
             oConfig = this.cfg;


        // Hide any other submenus that might be visible
    
        if(oActiveItem && oActiveItem != oItem) {
    
            this.clearActiveItem();
    
        }

    
        oItem.cfg.setProperty("selected", true);
    

        // Show the submenu for the item
    
        var oSubmenu = oItem.cfg.getProperty("submenu");


        if(oSubmenu && oTarget != oItem.submenuIndicator) {
        
            if(oSubmenu.cfg.getProperty("visible")) {
            
                oSubmenu.hide();
            
            }
            else {
            
                oSubmenu.show();                    
            
            }
        
        }
    
    }

},



// Public methods


/**
* @method toString
* @description Returns a string representing the menu bar.
* @return {String}
*/
toString: function() {

    var sReturnVal = "MenuBar",
        sId = this.id;

    if(sId) {

        sReturnVal += (" " + sId);
    
    }

    return sReturnVal;

},


/**
* @description Initializes the class's configurable properties which can be
* changed using the menu bar's Config object ("cfg").
* @method initDefaultConfig
*/
initDefaultConfig: function() {

    YAHOO.widget.MenuBar.superclass.initDefaultConfig.call(this);

    var oConfig = this.cfg,
        DEFAULT_CONFIG = YAHOO.widget.MenuBar._DEFAULT_CONFIG;

	// Add configuration properties


    /*
        Set the default value for the "position" configuration property
        to "static" by re-adding the property.
    */


    /**
    * @config position
    * @description String indicating how a menu bar should be positioned on the 
    * screen.  Possible values are "static" and "dynamic."  Static menu bars 
    * are visible by default and reside in the normal flow of the document 
    * (CSS position: static).  Dynamic menu bars are hidden by default, reside
    * out of the normal flow of the document (CSS position: absolute), and can 
    * overlay other elements on the screen.
    * @default static
    * @type String
    */
    oConfig.addProperty(
        DEFAULT_CONFIG.POSITION.key, 
        {
            handler: this.configPosition, 
            value: DEFAULT_CONFIG.POSITION.value, 
            validator: DEFAULT_CONFIG.POSITION.validator,
            supercedes: DEFAULT_CONFIG.POSITION.supercedes
        }
    );


    /*
        Set the default value for the "submenualignment" configuration property
        to ["tl","bl"] by re-adding the property.
    */

    /**
    * @config submenualignment
    * @description Array defining how submenus should be aligned to their 
    * parent menu bar item. The format is: [itemCorner, submenuCorner].
    * @default ["tl","bl"]
    * @type Array
    */
    oConfig.addProperty(
        DEFAULT_CONFIG.SUBMENU_ALIGNMENT.key, 
        {
            value: DEFAULT_CONFIG.SUBMENU_ALIGNMENT.value
        }
    );


    /*
        Change the default value for the "autosubmenudisplay" configuration 
        property to "false" by re-adding the property.
    */

    /**
    * @config autosubmenudisplay
    * @description Boolean indicating if submenus are automatically made 
    * visible when the user mouses over the menu bar's items.
    * @default false
    * @type Boolean
    */
	oConfig.addProperty(
	   DEFAULT_CONFIG.AUTO_SUBMENU_DISPLAY.key, 
	   {
	       value: DEFAULT_CONFIG.AUTO_SUBMENU_DISPLAY.value, 
	       validator: DEFAULT_CONFIG.AUTO_SUBMENU_DISPLAY.validator
       } 
    );

}
 
}); // END YAHOO.lang.extend



/**
* Creates an item for a menu bar.
* 
* @param {String} p_oObject String specifying the text of the menu bar item.
* @param {<a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/level-
* one-html.html#ID-74680021">HTMLLIElement</a>} p_oObject Object specifying the 
* <code>&#60;li&#62;</code> element of the menu bar item.
* @param {<a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/level-
* one-html.html#ID-38450247">HTMLOptGroupElement</a>} p_oObject Object 
* specifying the <code>&#60;optgroup&#62;</code> element of the menu bar item.
* @param {<a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/level-
* one-html.html#ID-70901257">HTMLOptionElement</a>} p_oObject Object specifying 
* the <code>&#60;option&#62;</code> element of the menu bar item.
* @param {Object} p_oConfig Optional. Object literal specifying the 
* configuration for the menu bar item. See configuration class documentation 
* for more details.
* @class MenuBarItem
* @constructor
* @extends YAHOO.widget.MenuItem
*/
YAHOO.widget.MenuBarItem = function(p_oObject, p_oConfig) {

    YAHOO.widget.MenuBarItem.superclass.constructor.call(
        this, 
        p_oObject, 
        p_oConfig
    );

};

YAHOO.lang.extend(YAHOO.widget.MenuBarItem, YAHOO.widget.MenuItem, {


/**
* @method init
* @description The MenuBarItem class's initialization method. This method is 
* automatically called by the constructor, and sets up all DOM references for 
* pre-existing markup, and creates required markup if it is not already present.
* @param {String} p_oObject String specifying the text of the menu bar item.
* @param {<a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/level-
* one-html.html#ID-74680021">HTMLLIElement</a>} p_oObject Object specifying the 
* <code>&#60;li&#62;</code> element of the menu bar item.
* @param {<a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/level-
* one-html.html#ID-38450247">HTMLOptGroupElement</a>} p_oObject Object 
* specifying the <code>&#60;optgroup&#62;</code> element of the menu bar item.
* @param {<a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/level-
* one-html.html#ID-70901257">HTMLOptionElement</a>} p_oObject Object specifying 
* the <code>&#60;option&#62;</code> element of the menu bar item.
* @param {Object} p_oConfig Optional. Object literal specifying the 
* configuration for the menu bar item. See configuration class documentation 
* for more details.
*/
init: function(p_oObject, p_oConfig) {

    if(!this.SUBMENU_TYPE) {

        this.SUBMENU_TYPE = YAHOO.widget.Menu;

    }


    /* 
        Call the init of the superclass (YAHOO.widget.MenuItem)
        Note: We don't pass the user config in here yet 
        because we only want it executed once, at the lowest 
        subclass level.
    */ 

    YAHOO.widget.MenuBarItem.superclass.init.call(this, p_oObject);  


    var oConfig = this.cfg;

    if(p_oConfig) {

        oConfig.applyConfig(p_oConfig, true);

    }

    oConfig.fireQueue();

},



// Constants

/**
* @property CSS_CLASS_NAME
* @description String representing the CSS class(es) to be applied to the 
* <code>&#60;li&#62;</code> element of the menu bar item.
* @default "yuimenubaritem"
* @final
* @type String
*/
CSS_CLASS_NAME: "yuimenubaritem",



// Public methods


/**
* @method toString
* @description Returns a string representing the menu bar item.
* @return {String}
*/
toString: function() {

    var sReturnVal = "MenuBarItem";

    if(this.cfg && this.cfg.getProperty("text")) {

        sReturnVal += (": " + this.cfg.getProperty("text"));

    }

    return sReturnVal;

}
    
}); // END YAHOO.lang.extend
YAHOO.register("menu", YAHOO.widget.Menu, {version: "2.2.2", build: "204"});
