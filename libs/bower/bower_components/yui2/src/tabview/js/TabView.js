(function() {

    /**
     * The tabview module provides a widget for managing content bound to tabs.
     * @module tabview
     * @requires yahoo, dom, event, element
     *
     */

    var Y = YAHOO.util,
        Dom = Y.Dom,
        Event = Y.Event,
        document = window.document,
    
        // STRING CONSTANTS
        ACTIVE = 'active',
        ACTIVE_INDEX = 'activeIndex',
        ACTIVE_TAB = 'activeTab',
        DISABLED = 'disabled',
        CONTENT_EL = 'contentEl',
        ELEMENT = 'element',
    
    /**
     * A widget to control tabbed views.
     * @namespace YAHOO.widget
     * @class TabView
     * @extends YAHOO.util.Element
     * @constructor
     * @param {HTMLElement | String | Object} el(optional) The html 
     * element that represents the TabView, or the attribute object to use. 
     * An element will be created if none provided.
     * @param {Object} attr (optional) A key map of the tabView's 
     * initial attributes.  Ignored if first arg is attributes object.
     */
    TabView = function(el, attr) {
        attr = attr || {};
        if (arguments.length == 1 && !YAHOO.lang.isString(el) && !el.nodeName) {
            attr = el; // treat first arg as attr object
            el = attr.element || null;
        }
        
        if (!el && !attr.element) { // create if we dont have one
            el = this._createTabViewElement(attr);
        }
        TabView.superclass.constructor.call(this, el, attr); 
    };

    YAHOO.extend(TabView, Y.Element, {
        /**
         * The className to add when building from scratch. 
         * @property CLASSNAME
         * @default "navset"
         */
        CLASSNAME: 'yui-navset',
        
        /**
         * The className of the HTMLElement containing the TabView's tab elements
         * to look for when building from existing markup, or to add when building
         * from scratch. 
         * All childNodes of the tab container are treated as Tabs when building
         * from existing markup.
         * @property TAB_PARENT_CLASSNAME
         * @default "nav"
         */
        TAB_PARENT_CLASSNAME: 'yui-nav',
        
        /**
         * The className of the HTMLElement containing the TabView's label elements
         * to look for when building from existing markup, or to add when building
         * from scratch. 
         * All childNodes of the content container are treated as content elements when
         * building from existing markup.
         * @property CONTENT_PARENT_CLASSNAME
         * @default "nav-content"
         */
        CONTENT_PARENT_CLASSNAME: 'yui-content',
        
        _tabParent: null,
        _contentParent: null,
        
        /**
         * Adds a Tab to the TabView instance.  
         * If no index is specified, the tab is added to the end of the tab list.
         * @method addTab
         * @param {YAHOO.widget.Tab} tab A Tab instance to add.
         * @param {Integer} index The position to add the tab. 
         * @return void
         */
        addTab: function(tab, index) {
            var tabs = this.get('tabs'),
                tabParent = this._tabParent,
                contentParent = this._contentParent,
                tabElement = tab.get(ELEMENT),
                contentEl = tab.get(CONTENT_EL),
                activeIndex = this.get(ACTIVE_INDEX),
                before;

            if (!tabs) { // not ready yet
                this._queue[this._queue.length] = ['addTab', arguments];
                return false;
            }
            
            before = this.getTab(index);
            index = (index === undefined) ? tabs.length : index;
            
            tabs.splice(index, 0, tab);

            if (before) {
                tabParent.insertBefore(tabElement, before.get(ELEMENT));
                if (contentEl) {
                    contentParent.appendChild(contentEl);
                }
            } else {
                tabParent.appendChild(tabElement);
                if (contentEl) {
                    contentParent.appendChild(contentEl);
                }
            }

            if ( !tab.get(ACTIVE) ) {
                tab.set('contentVisible', false, true); /* hide if not active */
                if (index <= activeIndex) {
                    this.set(ACTIVE_INDEX, activeIndex + 1, true);
                }  
            } else {
                this.set(ACTIVE_TAB, tab, true);
                this.set('activeIndex', index, true);
            }

            this._initTabEvents(tab);
        },

        _initTabEvents: function(tab) {
            tab.addListener( tab.get('activationEvent'), tab._onActivate, this, tab);
            tab.addListener('activationEventChange', tab._onActivationEventChange, this, tab);
        },

        _removeTabEvents: function(tab) {
            tab.removeListener(tab.get('activationEvent'), tab._onActivate, this, tab);
            tab.removeListener('activationEventChange', tab._onActivationEventChange, this, tab);
        },

        /**
         * Routes childNode events.
         * @method DOMEventHandler
         * @param {event} e The Dom event that is being handled.
         * @return void
         */
        DOMEventHandler: function(e) {
            var target = Event.getTarget(e),
                tabParent = this._tabParent,
                tabs = this.get('tabs'),
                tab,
                tabEl,
                contentEl;

            
            if (Dom.isAncestor(tabParent, target) ) {
                for (var i = 0, len = tabs.length; i < len; i++) {
                    tabEl = tabs[i].get(ELEMENT);
                    contentEl = tabs[i].get(CONTENT_EL);

                    if ( target == tabEl || Dom.isAncestor(tabEl, target) ) {
                        tab = tabs[i];
                        break; // note break
                    }
                } 
                
                if (tab) {
                    tab.fireEvent(e.type, e);
                }
            }
        },
        
        /**
         * Returns the Tab instance at the specified index.
         * @method getTab
         * @param {Integer} index The position of the Tab.
         * @return YAHOO.widget.Tab
         */
        getTab: function(index) {
            return this.get('tabs')[index];
        },
        
        /**
         * Returns the index of given tab.
         * @method getTabIndex
         * @param {YAHOO.widget.Tab} tab The tab whose index will be returned.
         * @return int
         */
        getTabIndex: function(tab) {
            var index = null,
                tabs = this.get('tabs');
            for (var i = 0, len = tabs.length; i < len; ++i) {
                if (tab == tabs[i]) {
                    index = i;
                    break;
                }
            }
            
            return index;
        },
        
        /**
         * Removes the specified Tab from the TabView.
         * @method removeTab
         * @param {YAHOO.widget.Tab} item The Tab instance to be removed.
         * @return void
         */
        removeTab: function(tab) {
            var tabCount = this.get('tabs').length,
                activeIndex = this.get(ACTIVE_INDEX),
                index = this.getTabIndex(tab);

            if ( tab === this.get(ACTIVE_TAB) ) { 
                if (tabCount > 1) { // select another tab
                    if (index + 1 === tabCount) { // if last, activate previous
                        this.set(ACTIVE_INDEX, index - 1);
                    } else { // activate next tab
                        this.set(ACTIVE_INDEX, index + 1);
                    }
                } else { // no more tabs
                    this.set(ACTIVE_TAB, null);
                }
            } else if (index < activeIndex) {
                this.set(ACTIVE_INDEX, activeIndex - 1, true);
            }
            
            this._removeTabEvents(tab);
            this._tabParent.removeChild( tab.get(ELEMENT) );
            this._contentParent.removeChild( tab.get(CONTENT_EL) );
            this._configs.tabs.value.splice(index, 1);

            tab.fireEvent('remove', { type: 'remove', tabview: this });
        },
        
        /**
         * Provides a readable name for the TabView instance.
         * @method toString
         * @return String
         */
        toString: function() {
            var name = this.get('id') || this.get('tagName');
            return "TabView " + name; 
        },
        
        /**
         * The transiton to use when switching between tabs.
         * @method contentTransition
         */
        contentTransition: function(newTab, oldTab) {
            if (newTab) {
                newTab.set('contentVisible', true);
            }
            if (oldTab) {
                oldTab.set('contentVisible', false);
            }
        },
        
        /**
         * setAttributeConfigs TabView specific properties.
         * @method initAttributes
         * @param {Object} attr Hash of initial attributes
         */
        initAttributes: function(attr) {
            TabView.superclass.initAttributes.call(this, attr);
            
            if (!attr.orientation) {
                attr.orientation = 'top';
            }
            
            var el = this.get(ELEMENT);

            if (!this.hasClass(this.CLASSNAME)) {
                this.addClass(this.CLASSNAME);        
            }
            
            /**
             * The Tabs belonging to the TabView instance.
             * @attribute tabs
             * @type Array
             */
            this.setAttributeConfig('tabs', {
                value: [],
                readOnly: true
            });

            /**
             * The container of the tabView's label elements.
             * @property _tabParent
             * @private
             * @type HTMLElement
             */
            this._tabParent = 
                    this.getElementsByClassName(this.TAB_PARENT_CLASSNAME,
                            'ul' )[0] || this._createTabParent();
                
            /**
             * The container of the tabView's content elements.
             * @property _contentParent
             * @type HTMLElement
             * @private
             */
            this._contentParent = 
                    this.getElementsByClassName(this.CONTENT_PARENT_CLASSNAME,
                            'div')[0] ||  this._createContentParent();
            
            /**
             * How the Tabs should be oriented relative to the TabView.
             * Valid orientations are "top", "left", "bottom", and "right"
             * @attribute orientation
             * @type String
             * @default "top"
             */
            this.setAttributeConfig('orientation', {
                value: attr.orientation,
                method: function(value) {
                    var current = this.get('orientation');
                    this.addClass('yui-navset-' + value);
                    
                    if (current != value) {
                        this.removeClass('yui-navset-' + current);
                    }
                    
                    if (value === 'bottom') {
                        this.appendChild(this._tabParent);
                    }
                }
            });
            
            /**
             * The index of the tab currently active.
             * @attribute activeIndex
             * @type Int
             */
            this.setAttributeConfig(ACTIVE_INDEX, {
                value: attr.activeIndex,
                validator: function(value) {
                    var ret = true,
                        tab;
                    if (value) { // cannot activate if disabled
                        tab = this.getTab(value);
                        if (tab && tab.get(DISABLED)) {
                            ret = false;
                        }
                    }
                    return ret;
                }
            });
            
            /**
             * The tab currently active.
             * @attribute activeTab
             * @type YAHOO.widget.Tab
             */
            this.setAttributeConfig(ACTIVE_TAB, {
                value: attr[ACTIVE_TAB],
                method: function(tab) {
                    var activeTab = this.get(ACTIVE_TAB);
                    
                    if (tab) {
                        tab.set(ACTIVE, true);
                    }
                    
                    if (activeTab && activeTab !== tab) {
                        activeTab.set(ACTIVE, false);
                    }
                    
                    if (activeTab && tab !== activeTab) { // no transition if only 1
                        this.contentTransition(tab, activeTab);
                    } else if (tab) {
                        tab.set('contentVisible', true);
                    }
                },
                validator: function(value) {
                    var ret = true;
                    if (value && value.get(DISABLED)) { // cannot activate if disabled
                        ret = false;
                    }
                    return ret;
                }
            });

            this.on('activeTabChange', this._onActiveTabChange);
            this.on('activeIndexChange', this._onActiveIndexChange);

            YAHOO.log('attributes initialized', 'info', 'TabView');
            if ( this._tabParent ) {
                this._initTabs();
            }
            
            // Due to delegation we add all DOM_EVENTS to the TabView container
            // but IE will leak when unsupported events are added, so remove these
            this.DOM_EVENTS.submit = false;
            this.DOM_EVENTS.focus = false;
            this.DOM_EVENTS.blur = false;
            this.DOM_EVENTS.change = false;

            for (var type in this.DOM_EVENTS) {
                if ( YAHOO.lang.hasOwnProperty(this.DOM_EVENTS, type) ) {
                    this.addListener.call(this, type, this.DOMEventHandler);
                }
            }
        },

        /**
         * Removes selected state from the given tab if it is the activeTab
         * @method deselectTab
         * @param {Int} index The tab index to deselect 
         */
        deselectTab: function(index) {
            if (this.getTab(index) === this.get(ACTIVE_TAB)) {
                this.set(ACTIVE_TAB, null);
            }
        },

        /**
         * Makes the tab at the given index the active tab
         * @method selectTab
         * @param {Int} index The tab index to be made active
         */
        selectTab: function(index) {
            this.set(ACTIVE_TAB, this.getTab(index));
        },

        _onActiveTabChange: function(e) {
            var activeIndex = this.get(ACTIVE_INDEX),
                newIndex = this.getTabIndex(e.newValue);

            if (activeIndex !== newIndex) {
                if (!(this.set(ACTIVE_INDEX, newIndex)) ) { // NOTE: setting
                     // revert if activeIndex update fails (cancelled via beforeChange) 
                    this.set(ACTIVE_TAB, e.prevValue);
                }
            }
        },
        
        _onActiveIndexChange: function(e) {
            // no set if called from ActiveTabChange event
            if (e.newValue !== this.getTabIndex(this.get(ACTIVE_TAB))) {
                if (!(this.set(ACTIVE_TAB, this.getTab(e.newValue))) ) { // NOTE: setting
                     // revert if activeTab update fails (cancelled via beforeChange) 
                    this.set(ACTIVE_INDEX, e.prevValue);
                }
            }
        },

        /**
         * Creates Tab instances from a collection of HTMLElements.
         * @method _initTabs
         * @private
         * @return void
         */
        _initTabs: function() {
            var tabs = Dom.getChildren(this._tabParent),
                contentElements = Dom.getChildren(this._contentParent),
                activeIndex = this.get(ACTIVE_INDEX),
                tab,
                attr,
                active;

            for (var i = 0, len = tabs.length; i < len; ++i) {
                attr = {};
                
                if (contentElements[i]) {
                    attr.contentEl = contentElements[i];
                }

                tab = new YAHOO.widget.Tab(tabs[i], attr);
                this.addTab(tab);
                
                if (tab.hasClass(tab.ACTIVE_CLASSNAME) ) {
                    active = tab;
                }
            }
            if (activeIndex != undefined) { // not null or undefined
                this.set(ACTIVE_TAB, this.getTab(activeIndex));
            } else {
                this._configs[ACTIVE_TAB].value = active; // dont invoke method
                this._configs[ACTIVE_INDEX].value = this.getTabIndex(active);
            }
        },

        _createTabViewElement: function(attr) {
            var el = document.createElement('div');

            if ( this.CLASSNAME ) {
                el.className = this.CLASSNAME;
            }
            
            YAHOO.log('TabView Dom created', 'info', 'TabView');
            return el;
        },

        _createTabParent: function(attr) {
            var el = document.createElement('ul');

            if ( this.TAB_PARENT_CLASSNAME ) {
                el.className = this.TAB_PARENT_CLASSNAME;
            }
            
            this.get(ELEMENT).appendChild(el);
            
            return el;
        },
        
        _createContentParent: function(attr) {
            var el = document.createElement('div');

            if ( this.CONTENT_PARENT_CLASSNAME ) {
                el.className = this.CONTENT_PARENT_CLASSNAME;
            }
            
            this.get(ELEMENT).appendChild(el);
            
            return el;
        }
    });
    
    
    YAHOO.widget.TabView = TabView;
})();

