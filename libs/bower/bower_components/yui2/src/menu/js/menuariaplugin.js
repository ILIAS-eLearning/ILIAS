(function () {

    var Event = YAHOO.util.Event,
        Dom = YAHOO.util.Dom,
        Lang = YAHOO.lang,
        UA = YAHOO.env.ua,
        ContextMenu = YAHOO.widget.ContextMenu,		

        MenuPrototype = YAHOO.widget.Menu.prototype,
        fnMenuInitDefaultConfig = MenuPrototype.initDefaultConfig,

        // The currently focused MenuItem label, or the MenuItem label that can be focused 
        // by the user.

        m_oCurrentItemLabel,
        

        // Private constants for strings
        
        _ARIA_PREFIX = "aria-",
        _HAS_POPUP = "haspopup",
        _ROLE = "role",
        _PRESENTATION = "presentation",
        _MENUITEM = "menuitem",
        _HREF = "href",
        _SUBMENU = "submenu",
        _MENU = "menu",
        _MENUBAR = "menubar",
        _LABELLED_BY = "labelledby",
        _FOCUS = "focus", 
        _BLUR = "blur",
        _ITEM_ADDED = "itemAdded",
        _USE_ARIA = "usearia"
        _TRIGGER = "trigger";


    // Menu ARIA plugin		

    var setARIARole = function (element, role) {
    
        element.setAttribute(_ROLE, role);
    
    };


    var setARIAProperty = function (element, property, value) {

        element.setAttribute((_ARIA_PREFIX + property), value);
    
    };


    var addHasPopupRole = function (element) {
    
        if (element.nodeType === 1) {
            setARIAProperty(element, _HAS_POPUP, true);
        }
    
    };


    var removeHasPopupRole = function (element) {

        if (element.nodeType === 1) {
            element.removeAttribute(_ARIA_PREFIX + _HAS_POPUP);
        }
        
    };


    var onFocus = function (type, args) {
    
        var oEvent = args[0],
            oTarget = Event.getTarget(oEvent);	// The currently focused element

        // Modify value of the tabIndex attribute so that the currently 
        // focused MenuItem label is in the browser's default tab order.	

        if (m_oCurrentItemLabel) {
            m_oCurrentItemLabel.tabIndex = -1;
        }

        m_oCurrentItemLabel = oTarget;
        m_oCurrentItemLabel.tabIndex = 0;

    };
    

    var onBlur = function (type, args) {

        var oEvent = args[0],
            oTarget = Event.getTarget(oEvent);	// The currently focused element

        // If the focus has moved to an element on the page that is not a 
        // part of the Menu, restore the Menu to its original state 
        // so that the first item is in the browser's default tab index.
    
        m_oCurrentItemLabel.tabIndex = -1;

        m_oCurrentItemLabel = Dom.getFirstChild(this.getItem(0).element);
        m_oCurrentItemLabel.tabIndex = 0;
    
    };


    var onConfigSubmenu = function (type, args) {
            
        var oSubmenu = args[0];
            
        if (oSubmenu) {
            addHasPopupRole(Dom.getFirstChild(this.element));
        }
    
    };
    
    
    var addMenuItemRole = function (menuitem) {

        var oMenuItemEl = menuitem.element;

        // For NVDA: add the role of "presentation" to reach LI and UL
        // element to prevent NVDA from announcing the "list" role
        // as well as the menu-related roles.

        setARIARole(oMenuItemEl.parentNode, _PRESENTATION);
        setARIARole(oMenuItemEl, _PRESENTATION);


        // Retrieve a reference to the anchor element that serves as the 
        // label for each MenuItem.

        var oMenuItemLabel = Dom.getFirstChild(oMenuItemEl);


        // Set the "role" attribute of the label to "menuitem"

        setARIARole(oMenuItemLabel, _MENUITEM);


        // Remove the label from the browser's default tab order
        
        oMenuItemLabel.tabIndex = -1;


        // JAWS & NVDA announce the value of each anchor 
        // element's "href" attribute when it recieves focus, so remove  
        // the attribute so that its value isn't announced.

        oMenuItemLabel.removeAttribute(_HREF);


        // If the MenuItem has a submenu, set the "aria-haspopup" 
        // attribute to true so that the screen reader can announce 

        if (menuitem.cfg.getProperty(_SUBMENU)) {
            addHasPopupRole(oMenuItemLabel);
        }
        else {
            menuitem.cfg.subscribeToConfigEvent(_SUBMENU, onConfigSubmenu);
        }
    
    };


    var onItemAdded = function (type, args) {

        addMenuItemRole(args[0]);
    
    };


    Lang.augmentObject(MenuPrototype, {

        configUseARIA: function (type, args) {
        
            var bUseARIA = args[0],
                oParent = this.parent,
                oElement = this.element,
                sMenuRole = (this instanceof YAHOO.widget.MenuBar) ? _MENUBAR : _MENU,
                aMenuItems,
                nMenuItems,
                oMenuItem,
                oMenuItemLabel,
                sId,
                i;
    
    
            if (bUseARIA) {
        
                // Apply the "role" attribute of "menu" or "menubar" depending on the 
                // type of the Menu control being rendered.
    
                setARIARole(oElement, sMenuRole);
    

        
                // Use the "aria-labelledby" attribute to label each submenu.  This 
                // will provide the user with the name of the submenu the first time
                // one of its items receives focus.
    
                if (oParent) {
    
                    sId = Dom.generateId(Dom.getFirstChild(oParent.element));
    
                    setARIAProperty(oElement, _LABELLED_BY, sId);
                    
                }
        
        
                // Apply the appropriate "role" and "aria-[state]" attributes to the 
                // label of each MenuItem instance.
    
                aMenuItems = this.getItems();
                nMenuItems = aMenuItems.length;

                if (nMenuItems > 0) {

                    i = nMenuItems - 1;
                        
            
                    do {
                        oMenuItem = aMenuItems[i];
                        addMenuItemRole(oMenuItem);
                        i = i - 1;
                    }
                    while ((i > -1));
                        
            
                    // Set the "tabindex" of the first MenuItem's label to 0 so the user 
                    // can easily tab into and out of the control.
        
                    if (this.getRoot() === this) {
                        m_oCurrentItemLabel = Dom.getFirstChild(this.getItem(0).element);
                        m_oCurrentItemLabel.tabIndex = 0;
                    }
    
                }
    
                if (this === this.getRoot()) {
                    this.subscribe(_FOCUS, onFocus);
                    this.subscribe(_BLUR, onBlur);
                }
                
                this.subscribe(_ITEM_ADDED, onItemAdded);
        
            }	
    
        },
    
    
        initDefaultConfig: function () {
        
            fnMenuInitDefaultConfig.call(this);
        
            this.cfg.addProperty(
                _USE_ARIA, 
                {
                    handler: this.configUseARIA, 
                    value: (UA.gecko && UA.gecko >= 1.9) || (UA.ie && UA.ie >= 8), 
                    validator: Lang.isBoolean
                }
             );
    
        }
    
    }, "initDefaultConfig", "configUseARIA");



    // ContextMenu ARIA plugin

    var m_oTriggers = {};

    var toggleARIAForTrigger = function () {

        var sMenuId = this.element.id,
            oTrigger = this.cfg.getProperty(_TRIGGER),
            oCurrentTrigger = m_oTriggers[sMenuId],
            oElement;


        if (oCurrentTrigger) {

            if (Lang.isString(oCurrentTrigger)) {	// String id
    
                oElement = Dom.get(oCurrentTrigger);
                
                if (oElement) {
                    removeHasPopupRole(oElement);						
                }
    
            }
            else if (oCurrentTrigger.nodeType === 1) {	// Element reference
                removeHasPopupRole(oCurrentTrigger);
            }				
            else if (oCurrentTrigger.length) { // NodeList or Array
                Dom.batch(oCurrentTrigger, removeHasPopupRole);
            }

        }


        if (oTrigger) {

            if (Lang.isString(oTrigger)) {	// String id

                oElement = Dom.get(oTrigger);
                
                if (oElement) {
                    addHasPopupRole(oElement);						
                }

            }
            else if (oTrigger.nodeType === 1) {	// Element reference
                addHasPopupRole(oTrigger);
            }
            else if (oTrigger.length) {	// NodeList or Array
                Dom.batch(oTrigger, addHasPopupRole);
            }
                
            m_oTriggers[sMenuId] = oTrigger;

        }
    
    };


    ContextMenu.prototype.configUseARIA = function (type, args) {

        ContextMenu.superclass.configUseARIA.apply(this, arguments);

        toggleARIAForTrigger.call(this);

        this.cfg.subscribeToConfigEvent(_TRIGGER, toggleARIAForTrigger);
    
    };

}());