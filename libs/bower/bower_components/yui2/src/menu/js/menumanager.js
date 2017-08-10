

/**
* @module menu
* @description <p>The Menu family of components features a collection of 
* controls that make it easy to add menus to your website or web application.  
* With the Menu Controls you can create website fly-out menus, customized 
* context menus, or application-style menu bars with just a small amount of 
* scripting.</p><p>The Menu family of controls features:</p>
* <ul>
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
(function () {

    var UA = YAHOO.env.ua,
        Dom = YAHOO.util.Dom,
        Event = YAHOO.util.Event,
        Lang = YAHOO.lang,

        _DIV = "DIV",
        _HD = "hd",
        _BD = "bd",
        _FT = "ft",
        _LI = "LI",
        _DISABLED = "disabled",
        _MOUSEOVER = "mouseover",
        _MOUSEOUT = "mouseout",
        _MOUSEDOWN = "mousedown",
        _MOUSEUP = "mouseup",
        _CLICK = "click",
        _KEYDOWN = "keydown",
        _KEYUP = "keyup",
        _KEYPRESS = "keypress",
        _CLICK_TO_HIDE = "clicktohide",
        _POSITION = "position", 
        _DYNAMIC = "dynamic",
        _SHOW_DELAY = "showdelay",
        _SELECTED = "selected",
        _VISIBLE = "visible",
        _UL = "UL",
        _MENUMANAGER = "MenuManager";


    /**
    * Singleton that manages a collection of all menus and menu items.  Listens 
    * for DOM events at the document level and dispatches the events to the 
    * corresponding menu or menu item.
    *
    * @namespace YAHOO.widget
    * @class MenuManager
    * @static
    */
    YAHOO.widget.MenuManager = function () {
    
        // Private member variables
    
    
        // Flag indicating if the DOM event handlers have been attached
    
        var m_bInitializedEventHandlers = false,
    
    
        // Collection of menus

        m_oMenus = {},


        // Collection of visible menus
    
        m_oVisibleMenus = {},
    
    
        //  Collection of menu items 

        m_oItems = {},


        // Map of DOM event types to their equivalent CustomEvent types
        
        m_oEventTypes = {
            "click": "clickEvent",
            "mousedown": "mouseDownEvent",
            "mouseup": "mouseUpEvent",
            "mouseover": "mouseOverEvent",
            "mouseout": "mouseOutEvent",
            "keydown": "keyDownEvent",
            "keyup": "keyUpEvent",
            "keypress": "keyPressEvent",
            "focus": "focusEvent",
            "focusin": "focusEvent",
            "blur": "blurEvent",
            "focusout": "blurEvent"
        },
    
    
        m_oFocusedMenuItem = null;
    
    
    
        // Private methods
    
    
        /**
        * @method getMenuRootElement
        * @description Finds the root DIV node of a menu or the root LI node of 
        * a menu item.
        * @private
        * @param {<a href="http://www.w3.org/TR/2000/WD-DOM-Level-1-20000929/
        * level-one-html.html#ID-58190037">HTMLElement</a>} p_oElement Object 
        * specifying an HTML element.
        */
        function getMenuRootElement(p_oElement) {
        
            var oParentNode,
                returnVal;
    
            if (p_oElement && p_oElement.tagName) {
            
                switch (p_oElement.tagName.toUpperCase()) {
                        
                case _DIV:
    
                    oParentNode = p_oElement.parentNode;
    
                    // Check if the DIV is the inner "body" node of a menu

                    if ((
                            Dom.hasClass(p_oElement, _HD) ||
                            Dom.hasClass(p_oElement, _BD) ||
                            Dom.hasClass(p_oElement, _FT)
                        ) && 
                        oParentNode && 
                        oParentNode.tagName && 
                        oParentNode.tagName.toUpperCase() == _DIV) {
                    
                        returnVal = oParentNode;
                    
                    }
                    else {
                    
                        returnVal = p_oElement;
                    
                    }
                
                    break;

                case _LI:
    
                    returnVal = p_oElement;
                    
                    break;

                default:
    
                    oParentNode = p_oElement.parentNode;
    
                    if (oParentNode) {
                    
                        returnVal = getMenuRootElement(oParentNode);
                    
                    }
                
                    break;
                
                }
    
            }
            
            return returnVal;
            
        }
    
    
    
        // Private event handlers
    
    
        /**
        * @method onDOMEvent
        * @description Generic, global event handler for all of a menu's 
        * DOM-based events.  This listens for events against the document 
        * object.  If the target of a given event is a member of a menu or 
        * menu item's DOM, the instance's corresponding Custom Event is fired.
        * @private
        * @param {Event} p_oEvent Object representing the DOM event object  
        * passed back by the event utility (YAHOO.util.Event).
        */
        function onDOMEvent(p_oEvent) {
    
            // Get the target node of the DOM event
        
            var oTarget = Event.getTarget(p_oEvent),
                
            // See if the target of the event was a menu, or a menu item
    
            oElement = getMenuRootElement(oTarget),
            bFireEvent = true,
            sEventType = p_oEvent.type,
            sCustomEventType,
            sTagName,
            sId,
            oMenuItem,
            oMenu; 
    
    
            if (oElement) {
    
                sTagName = oElement.tagName.toUpperCase();
        
                if (sTagName == _LI) {
            
                    sId = oElement.id;
            
                    if (sId && m_oItems[sId]) {
            
                        oMenuItem = m_oItems[sId];
                        oMenu = oMenuItem.parent;
            
                    }
                
                }
                else if (sTagName == _DIV) {
                
                    if (oElement.id) {
                    
                        oMenu = m_oMenus[oElement.id];
                    
                    }
                
                }
    
            }
    
    
            if (oMenu) {
    
                sCustomEventType = m_oEventTypes[sEventType];

                /*
                    There is an inconsistency between Firefox for Mac OS X and 
                    Firefox Windows & Linux regarding the triggering of the 
                    display of the browser's context menu and the subsequent 
                    firing of the "click" event. In Firefox for Windows & Linux, 
                    when the user triggers the display of the browser's context 
                    menu the "click" event also fires for the document object, 
                    even though the "click" event did not fire for the element 
                    that was the original target of the "contextmenu" event. 
                    This is unique to Firefox on Windows & Linux.  For all 
                    other A-Grade browsers, including Firefox for Mac OS X, the 
                    "click" event doesn't fire for the document object. 

                    This bug in Firefox for Windows affects Menu, as Menu 
                    instances listen for events at the document level and 
                    dispatches Custom Events of the same name.  Therefore users
                    of Menu will get an unwanted firing of the "click" 
                    custom event.  The following line fixes this bug.
                */
                


                if (sEventType == "click" && 
                    (UA.gecko && oMenu.platform != "mac") && 
                    p_oEvent.button > 0) {

                    bFireEvent = false;

                }
    
                // Fire the Custom Event that corresponds the current DOM event    
        
                if (bFireEvent && oMenuItem && !oMenuItem.cfg.getProperty(_DISABLED)) {
                    oMenuItem[sCustomEventType].fire(p_oEvent);                   
                }
        
                if (bFireEvent) {
                    oMenu[sCustomEventType].fire(p_oEvent, oMenuItem);
                }
            
            }
            else if (sEventType == _MOUSEDOWN) {
    
                /*
                    If the target of the event wasn't a menu, hide all 
                    dynamically positioned menus
                */
                
                for (var i in m_oVisibleMenus) {
        
                    if (Lang.hasOwnProperty(m_oVisibleMenus, i)) {
        
                        oMenu = m_oVisibleMenus[i];

                        if (oMenu.cfg.getProperty(_CLICK_TO_HIDE) && 
                            !(oMenu instanceof YAHOO.widget.MenuBar) && 
                            oMenu.cfg.getProperty(_POSITION) == _DYNAMIC) {

                            oMenu.hide();

                            //	In IE when the user mouses down on a focusable 
                            //	element that element will be focused and become 
                            //	the "activeElement".
                            //	(http://msdn.microsoft.com/en-us/library/ms533065(VS.85).aspx)
                            //	However, there is a bug in IE where if there is 
                            //	a positioned element with a focused descendant 
                            //	that is hidden in response to the mousedown 
                            //	event, the target of the mousedown event will 
                            //	appear to have focus, but will not be set as 
                            //	the activeElement.  This will result in the 
                            //	element not firing key events, even though it
                            //	appears to have focus.  The following call to 
                            //	"setActive" fixes this bug.

                            if (UA.ie && oTarget.focus && (UA.ie < 9)) {
                                oTarget.setActive();
                            }
        
                        }
                        else {
                            
                            if (oMenu.cfg.getProperty(_SHOW_DELAY) > 0) {
                            
                                oMenu._cancelShowDelay();
                            
                            }


                            if (oMenu.activeItem) {
                        
                                oMenu.activeItem.blur();
                                oMenu.activeItem.cfg.setProperty(_SELECTED, false);
                        
                                oMenu.activeItem = null;            
                        
                            }
        
                        }
        
                    }
        
                } 
    
            }
            
        }
    
    
        /**
        * @method onMenuDestroy
        * @description "destroy" event handler for a menu.
        * @private
        * @param {String} p_sType String representing the name of the event 
        * that was fired.
        * @param {Array} p_aArgs Array of arguments sent when the event 
        * was fired.
        * @param {YAHOO.widget.Menu} p_oMenu The menu that fired the event.
        */
        function onMenuDestroy(p_sType, p_aArgs, p_oMenu) {
    
            if (m_oMenus[p_oMenu.id]) {
    
                this.removeMenu(p_oMenu);
    
            }
    
        }
    
    
        /**
        * @method onMenuFocus
        * @description "focus" event handler for a MenuItem instance.
        * @private
        * @param {String} p_sType String representing the name of the event 
        * that was fired.
        * @param {Array} p_aArgs Array of arguments sent when the event 
        * was fired.
        */
        function onMenuFocus(p_sType, p_aArgs) {
    
            var oItem = p_aArgs[1];
    
            if (oItem) {
    
                m_oFocusedMenuItem = oItem;
            
            }
    
        }
    
    
        /**
        * @method onMenuBlur
        * @description "blur" event handler for a MenuItem instance.
        * @private
        * @param {String} p_sType String representing the name of the event  
        * that was fired.
        * @param {Array} p_aArgs Array of arguments sent when the event 
        * was fired.
        */
        function onMenuBlur(p_sType, p_aArgs) {
    
            m_oFocusedMenuItem = null;
    
        }

    
        /**
        * @method onMenuVisibleConfigChange
        * @description Event handler for when the "visible" configuration  
        * property of a Menu instance changes.
        * @private
        * @param {String} p_sType String representing the name of the event  
        * that was fired.
        * @param {Array} p_aArgs Array of arguments sent when the event 
        * was fired.
        */
        function onMenuVisibleConfigChange(p_sType, p_aArgs) {
    
            var bVisible = p_aArgs[0],
                sId = this.id;
            
            if (bVisible) {
    
                m_oVisibleMenus[sId] = this;
                
                YAHOO.log(this + " added to the collection of visible menus.", 
                    "info", _MENUMANAGER);
            
            }
            else if (m_oVisibleMenus[sId]) {
            
                delete m_oVisibleMenus[sId];
                
                YAHOO.log(this + " removed from the collection of visible menus.", 
                    "info", _MENUMANAGER);
            
            }
        
        }
    
    
        /**
        * @method onItemDestroy
        * @description "destroy" event handler for a MenuItem instance.
        * @private
        * @param {String} p_sType String representing the name of the event  
        * that was fired.
        * @param {Array} p_aArgs Array of arguments sent when the event 
        * was fired.
        */
        function onItemDestroy(p_sType, p_aArgs) {
    
            removeItem(this);
    
        }


        /**
        * @method removeItem
        * @description Removes a MenuItem instance from the MenuManager's collection of MenuItems.
        * @private
        * @param {MenuItem} p_oMenuItem The MenuItem instance to be removed.
        */    
        function removeItem(p_oMenuItem) {

            var sId = p_oMenuItem.id;
    
            if (sId && m_oItems[sId]) {
    
                if (m_oFocusedMenuItem == p_oMenuItem) {
    
                    m_oFocusedMenuItem = null;
    
                }
    
                delete m_oItems[sId];
                
                p_oMenuItem.destroyEvent.unsubscribe(onItemDestroy);
    
                YAHOO.log(p_oMenuItem + " successfully unregistered.", "info", _MENUMANAGER);
    
            }

        }
    
    
        /**
        * @method onItemAdded
        * @description "itemadded" event handler for a Menu instance.
        * @private
        * @param {String} p_sType String representing the name of the event  
        * that was fired.
        * @param {Array} p_aArgs Array of arguments sent when the event 
        * was fired.
        */
        function onItemAdded(p_sType, p_aArgs) {
    
            var oItem = p_aArgs[0],
                sId;
    
            if (oItem instanceof YAHOO.widget.MenuItem) { 
    
                sId = oItem.id;
        
                if (!m_oItems[sId]) {
            
                    m_oItems[sId] = oItem;
        
                    oItem.destroyEvent.subscribe(onItemDestroy);
        
                    YAHOO.log(oItem + " successfully registered.", "info", _MENUMANAGER);
        
                }
    
            }
        
        }
    
    
        return {
    
            // Privileged methods
    
    
            /**
            * @method addMenu
            * @description Adds a menu to the collection of known menus.
            * @param {YAHOO.widget.Menu} p_oMenu Object specifying the Menu  
            * instance to be added.
            */
            addMenu: function (p_oMenu) {
    
                var oDoc;
    
                if (p_oMenu instanceof YAHOO.widget.Menu && p_oMenu.id && 
                    !m_oMenus[p_oMenu.id]) {
        
                    m_oMenus[p_oMenu.id] = p_oMenu;
                
            
                    if (!m_bInitializedEventHandlers) {
            
                        oDoc = document;
                
                        Event.on(oDoc, _MOUSEOVER, onDOMEvent, this, true);
                        Event.on(oDoc, _MOUSEOUT, onDOMEvent, this, true);
                        Event.on(oDoc, _MOUSEDOWN, onDOMEvent, this, true);
                        Event.on(oDoc, _MOUSEUP, onDOMEvent, this, true);
                        Event.on(oDoc, _CLICK, onDOMEvent, this, true);
                        Event.on(oDoc, _KEYDOWN, onDOMEvent, this, true);
                        Event.on(oDoc, _KEYUP, onDOMEvent, this, true);
                        Event.on(oDoc, _KEYPRESS, onDOMEvent, this, true);
    
                        Event.onFocus(oDoc, onDOMEvent, this, true);
                        Event.onBlur(oDoc, onDOMEvent, this, true);						
    
                        m_bInitializedEventHandlers = true;
                        
                        YAHOO.log("DOM event handlers initialized.", "info", _MENUMANAGER);
            
                    }
            
                    p_oMenu.cfg.subscribeToConfigEvent(_VISIBLE, onMenuVisibleConfigChange);
                    p_oMenu.destroyEvent.subscribe(onMenuDestroy, p_oMenu, this);
                    p_oMenu.itemAddedEvent.subscribe(onItemAdded);
                    p_oMenu.focusEvent.subscribe(onMenuFocus);
                    p_oMenu.blurEvent.subscribe(onMenuBlur);
        
                    YAHOO.log(p_oMenu + " successfully registered.", "info", _MENUMANAGER);
        
                }
        
            },
    
        
            /**
            * @method removeMenu
            * @description Removes a menu from the collection of known menus.
            * @param {YAHOO.widget.Menu} p_oMenu Object specifying the Menu  
            * instance to be removed.
            */
            removeMenu: function (p_oMenu) {
    
                var sId,
                    aItems,
                    i;
        
                if (p_oMenu) {
    
                    sId = p_oMenu.id;
        
                    if ((sId in m_oMenus) && (m_oMenus[sId] == p_oMenu)) {

                        // Unregister each menu item

                        aItems = p_oMenu.getItems();

                        if (aItems && aItems.length > 0) {

                            i = aItems.length - 1;

                            do {

                                removeItem(aItems[i]);

                            }
                            while (i--);

                        }


                        // Unregister the menu

                        delete m_oMenus[sId];
            
                        YAHOO.log(p_oMenu + " successfully unregistered.", "info", _MENUMANAGER);
        

                        /*
                             Unregister the menu from the collection of 
                             visible menus
                        */

                        if ((sId in m_oVisibleMenus) && (m_oVisibleMenus[sId] == p_oMenu)) {
            
                            delete m_oVisibleMenus[sId];
                            
                            YAHOO.log(p_oMenu + " unregistered from the" + 
                                        " collection of visible menus.", "info", _MENUMANAGER);
       
                        }


                        // Unsubscribe event listeners

                        if (p_oMenu.cfg) {

                            p_oMenu.cfg.unsubscribeFromConfigEvent(_VISIBLE, 
                                onMenuVisibleConfigChange);
                            
                        }

                        p_oMenu.destroyEvent.unsubscribe(onMenuDestroy, 
                            p_oMenu);
                
                        p_oMenu.itemAddedEvent.unsubscribe(onItemAdded);
                        p_oMenu.focusEvent.unsubscribe(onMenuFocus);
                        p_oMenu.blurEvent.unsubscribe(onMenuBlur);

                    }
                
                }
    
            },
        
        
            /**
            * @method hideVisible
            * @description Hides all visible, dynamically positioned menus 
            * (excluding instances of YAHOO.widget.MenuBar).
            */
            hideVisible: function () {
        
                var oMenu;
        
                for (var i in m_oVisibleMenus) {
        
                    if (Lang.hasOwnProperty(m_oVisibleMenus, i)) {
        
                        oMenu = m_oVisibleMenus[i];
        
                        if (!(oMenu instanceof YAHOO.widget.MenuBar) && 
                            oMenu.cfg.getProperty(_POSITION) == _DYNAMIC) {
        
                            oMenu.hide();
        
                        }
        
                    }
        
                }        
    
            },


            /**
            * @method getVisible
            * @description Returns a collection of all visible menus registered
            * with the menu manger.
            * @return {Object}
            */
            getVisible: function () {
            
                return m_oVisibleMenus;
            
            },

    
            /**
            * @method getMenus
            * @description Returns a collection of all menus registered with the 
            * menu manger.
            * @return {Object}
            */
            getMenus: function () {
    
                return m_oMenus;
            
            },
    
    
            /**
            * @method getMenu
            * @description Returns a menu with the specified id.
            * @param {String} p_sId String specifying the id of the 
            * <code>&#60;div&#62;</code> element representing the menu to
            * be retrieved.
            * @return {YAHOO.widget.Menu}
            */
            getMenu: function (p_sId) {
                
                var returnVal;
                
                if (p_sId in m_oMenus) {
                
                    returnVal = m_oMenus[p_sId];
                
                }
            
                return returnVal;
            
            },
    
    
            /**
            * @method getMenuItem
            * @description Returns a menu item with the specified id.
            * @param {String} p_sId String specifying the id of the 
            * <code>&#60;li&#62;</code> element representing the menu item to
            * be retrieved.
            * @return {YAHOO.widget.MenuItem}
            */
            getMenuItem: function (p_sId) {
    
                var returnVal;
    
                if (p_sId in m_oItems) {
    
                    returnVal = m_oItems[p_sId];
                
                }
                
                return returnVal;
            
            },


            /**
            * @method getMenuItemGroup
            * @description Returns an array of menu item instances whose 
            * corresponding <code>&#60;li&#62;</code> elements are child 
            * nodes of the <code>&#60;ul&#62;</code> element with the 
            * specified id.
            * @param {String} p_sId String specifying the id of the 
            * <code>&#60;ul&#62;</code> element representing the group of 
            * menu items to be retrieved.
            * @return {Array}
            */
            getMenuItemGroup: function (p_sId) {

                var oUL = Dom.get(p_sId),
                    aItems,
                    oNode,
                    oItem,
                    sId,
                    returnVal;
    

                if (oUL && oUL.tagName && oUL.tagName.toUpperCase() == _UL) {

                    oNode = oUL.firstChild;

                    if (oNode) {

                        aItems = [];
                        
                        do {

                            sId = oNode.id;

                            if (sId) {
                            
                                oItem = this.getMenuItem(sId);
                                
                                if (oItem) {
                                
                                    aItems[aItems.length] = oItem;
                                
                                }
                            
                            }
                        
                        }
                        while ((oNode = oNode.nextSibling));


                        if (aItems.length > 0) {

                            returnVal = aItems;
                        
                        }

                    }
                
                }

                return returnVal;
            
            },

    
            /**
            * @method getFocusedMenuItem
            * @description Returns a reference to the menu item that currently 
            * has focus.
            * @return {YAHOO.widget.MenuItem}
            */
            getFocusedMenuItem: function () {
    
                return m_oFocusedMenuItem;
    
            },
    
    
            /**
            * @method getFocusedMenu
            * @description Returns a reference to the menu that currently 
            * has focus.
            * @return {YAHOO.widget.Menu}
            */
            getFocusedMenu: function () {

                var returnVal;
    
                if (m_oFocusedMenuItem) {
    
                    returnVal = m_oFocusedMenuItem.parent.getRoot();
                
                }
    
                return returnVal;
    
            },
    
        
            /**
            * @method toString
            * @description Returns a string representing the menu manager.
            * @return {String}
            */
            toString: function () {
            
                return _MENUMANAGER;
            
            }
    
        };
    
    }();

})();
