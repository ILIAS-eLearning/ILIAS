(function() {
    var Y = YAHOO.util, 
        Dom = Y.Dom,
        Lang = YAHOO.lang,
    

    // STRING CONSTANTS
        ACTIVE_TAB = 'activeTab',
        LABEL = 'label',
        LABEL_EL = 'labelEl',
        CONTENT = 'content',
        CONTENT_EL = 'contentEl',
        ELEMENT = 'element',
        CACHE_DATA = 'cacheData',
        DATA_SRC = 'dataSrc',
        DATA_LOADED = 'dataLoaded',
        DATA_TIMEOUT = 'dataTimeout',
        LOAD_METHOD = 'loadMethod',
        POST_DATA = 'postData',
        DISABLED = 'disabled',
    
    /**
     * A representation of a Tab's label and content.
     * @namespace YAHOO.widget
     * @class Tab
     * @extends YAHOO.util.Element
     * @constructor
     * @param element {HTMLElement | String} (optional) The html element that 
     * represents the Tab. An element will be created if none provided.
     * @param {Object} properties A key map of initial properties
     */
    Tab = function(el, attr) {
        attr = attr || {};
        if (arguments.length == 1 && !Lang.isString(el) && !el.nodeName) {
            attr = el;
            el = attr.element;
        }

        if (!el && !attr.element) {
            el = this._createTabElement(attr);
        }

        this.loadHandler =  {
            success: function(o) {
                this.set(CONTENT, o.responseText);
            },
            failure: function(o) {
            }
        };
        
        Tab.superclass.constructor.call(this, el, attr);
        
        this.DOM_EVENTS = {}; // delegating to tabView
    };

    YAHOO.extend(Tab, YAHOO.util.Element, {
        /**
         * The default tag name for a Tab's inner element.
         * @property LABEL_INNER_TAGNAME
         * @type String
         * @default "em"
         */
        LABEL_TAGNAME: 'em',
        
        /**
         * The class name applied to active tabs.
         * @property ACTIVE_CLASSNAME
         * @type String
         * @default "selected"
         */
        ACTIVE_CLASSNAME: 'selected',
        
        /**
         * The class name applied to active tabs.
         * @property HIDDEN_CLASSNAME
         * @type String
         * @default "yui-hidden"
         */
        HIDDEN_CLASSNAME: 'yui-hidden',
        
        /**
         * The title applied to active tabs.
         * @property ACTIVE_TITLE
         * @type String
         * @default "active"
         */
        ACTIVE_TITLE: 'active',

        /**
         * The class name applied to disabled tabs.
         * @property DISABLED_CLASSNAME
         * @type String
         * @default "disabled"
         */
        DISABLED_CLASSNAME: DISABLED,
        
        /**
         * The class name applied to dynamic tabs while loading.
         * @property LOADING_CLASSNAME
         * @type String
         * @default "disabled"
         */
        LOADING_CLASSNAME: 'loading',

        /**
         * Provides a reference to the connection request object when data is
         * loaded dynamically.
         * @property dataConnection
         * @type Object
         */
        dataConnection: null,
        
        /**
         * Object containing success and failure callbacks for loading data.
         * @property loadHandler
         * @type object
         */
        loadHandler: null,

        _loading: false,
        
        /**
         * Provides a readable name for the tab.
         * @method toString
         * @return String
         */
        toString: function() {
            var el = this.get(ELEMENT),
                id = el.id || el.tagName;
            return "Tab " + id; 
        },
        
        /**
         * setAttributeConfigs Tab specific properties.
         * @method initAttributes
         * @param {Object} attr Hash of initial attributes
         */
        initAttributes: function(attr) {
            attr = attr || {};
            Tab.superclass.initAttributes.call(this, attr);
            
            /**
             * The event that triggers the tab's activation.
             * @attribute activationEvent
             * @type String
             */
            this.setAttributeConfig('activationEvent', {
                value: attr.activationEvent || 'click'
            });        

            /**
             * The element that contains the tab's label.
             * @attribute labelEl
             * @type HTMLElement
             */
            this.setAttributeConfig(LABEL_EL, {
                value: attr[LABEL_EL] || this._getLabelEl(),
                method: function(value) {
                    value = Dom.get(value);
                    var current = this.get(LABEL_EL);

                    if (current) {
                        if (current == value) {
                            return false; // already set
                        }
                        
                        current.parentNode.replaceChild(value, current);
                        this.set(LABEL, value.innerHTML);
                    }
                } 
            });

            /**
             * The tab's label text (or innerHTML).
             * @attribute label
             * @type String
             */
            this.setAttributeConfig(LABEL, {
                value: attr.label || this._getLabel(),
                method: function(value) {
                    var labelEl = this.get(LABEL_EL);
                    if (!labelEl) { // create if needed
                        this.set(LABEL_EL, this._createLabelEl());
                    }
                    
                    labelEl.innerHTML = value;
                }
            });
            
            /**
             * The HTMLElement that contains the tab's content.
             * @attribute contentEl
             * @type HTMLElement
             */
            this.setAttributeConfig(CONTENT_EL, {
                value: attr[CONTENT_EL] || document.createElement('div'),
                method: function(value) {
                    value = Dom.get(value);
                    var current = this.get(CONTENT_EL);

                    if (current) {
                        if (current === value) {
                            return false; // already set
                        }
                        if (!this.get('selected')) {
                            Dom.addClass(value, this.HIDDEN_CLASSNAME);
                        }
                        current.parentNode.replaceChild(value, current);
                        this.set(CONTENT, value.innerHTML);
                    }
                }
            });
            
            /**
             * The tab's content.
             * @attribute content
             * @type String
             */
            this.setAttributeConfig(CONTENT, {
                value: attr[CONTENT] || this.get(CONTENT_EL).innerHTML,
                method: function(value) {
                    this.get(CONTENT_EL).innerHTML = value;
                }
            });

            /**
             * The tab's data source, used for loading content dynamically.
             * @attribute dataSrc
             * @type String
             */
            this.setAttributeConfig(DATA_SRC, {
                value: attr.dataSrc
            });
            
            /**
             * Whether or not content should be reloaded for every view.
             * @attribute cacheData
             * @type Boolean
             * @default false
             */
            this.setAttributeConfig(CACHE_DATA, {
                value: attr.cacheData || false,
                validator: Lang.isBoolean
            });
            
            /**
             * The method to use for the data request.
             * @attribute loadMethod
             * @type String
             * @default "GET"
             */
            this.setAttributeConfig(LOAD_METHOD, {
                value: attr.loadMethod || 'GET',
                validator: Lang.isString
            });

            /**
             * Whether or not any data has been loaded from the server.
             * @attribute dataLoaded
             * @type Boolean
             */        
            this.setAttributeConfig(DATA_LOADED, {
                value: false,
                validator: Lang.isBoolean,
                writeOnce: true
            });
            
            /**
             * Number if milliseconds before aborting and calling failure handler.
             * @attribute dataTimeout
             * @type Number
             * @default null
             */
            this.setAttributeConfig(DATA_TIMEOUT, {
                value: attr.dataTimeout || null,
                validator: Lang.isNumber
            });
            
            /**
             * Arguments to pass when POST method is used 
             * @attribute postData
             * @default null
             */
            this.setAttributeConfig(POST_DATA, {
                value: attr.postData || null
            });

            /**
             * Whether or not the tab is currently active.
             * If a dataSrc is set for the tab, the content will be loaded from
             * the given source.
             * @attribute active
             * @type Boolean
             */
            this.setAttributeConfig('active', {
                value: attr.active || this.hasClass(this.ACTIVE_CLASSNAME),
                method: function(value) {
                    if (value === true) {
                        this.addClass(this.ACTIVE_CLASSNAME);
                        this.set('title', this.ACTIVE_TITLE);
                    } else {
                        this.removeClass(this.ACTIVE_CLASSNAME);
                        this.set('title', '');
                    }
                },
                validator: function(value) {
                    return Lang.isBoolean(value) && !this.get(DISABLED) ;
                }
            });
            
            /**
             * Whether or not the tab is disabled.
             * @attribute disabled
             * @type Boolean
             */
            this.setAttributeConfig(DISABLED, {
                value: attr.disabled || this.hasClass(this.DISABLED_CLASSNAME),
                method: function(value) {
                    if (value === true) {
                        this.addClass(this.DISABLED_CLASSNAME);
                    } else {
                        this.removeClass(this.DISABLED_CLASSNAME);
                    }
                },
                validator: Lang.isBoolean
            });
            
            /**
             * The href of the tab's anchor element.
             * @attribute href
             * @type String
             * @default '#'
             */
            this.setAttributeConfig('href', {
                value: attr.href ||
                        this.getElementsByTagName('a')[0].getAttribute('href', 2) || '#',
                method: function(value) {
                    this.getElementsByTagName('a')[0].href = value;
                },
                validator: Lang.isString
            });
            
            /**
             * The Whether or not the tab's content is visible.
             * @attribute contentVisible
             * @type Boolean
             * @default false
             */
            this.setAttributeConfig('contentVisible', {
                value: attr.contentVisible,
                method: function(value) {
                    if (value) {
                        Dom.removeClass(this.get(CONTENT_EL), this.HIDDEN_CLASSNAME);
                        
                        if ( this.get(DATA_SRC) ) {
                         // load dynamic content unless already loading or loaded and caching
                            if ( !this._loading && !(this.get(DATA_LOADED) && this.get(CACHE_DATA)) ) {
                                this._dataConnect();
                            }
                        }
                    } else {
                        Dom.addClass(this.get(CONTENT_EL), this.HIDDEN_CLASSNAME);
                    }
                },
                validator: Lang.isBoolean
            });
            YAHOO.log('attributes initialized', 'info', 'Tab');
        },
        
        _dataConnect: function() {
            if (!Y.Connect) {
                YAHOO.log('YAHOO.util.Connect dependency not met',
                        'error', 'Tab');
                return false;
            }

            Dom.addClass(this.get(CONTENT_EL).parentNode, this.LOADING_CLASSNAME);
            this._loading = true; 
            this.dataConnection = Y.Connect.asyncRequest(
                this.get(LOAD_METHOD),
                this.get(DATA_SRC), 
                {
                    success: function(o) {
                        YAHOO.log('content loaded successfully', 'info', 'Tab');
                        this.loadHandler.success.call(this, o);
                        this.set(DATA_LOADED, true);
                        this.dataConnection = null;
                        Dom.removeClass(this.get(CONTENT_EL).parentNode,
                                this.LOADING_CLASSNAME);
                        this._loading = false;
                    },
                    failure: function(o) {
                        YAHOO.log('loading failed: ' + o.statusText, 'error', 'Tab');
                        this.loadHandler.failure.call(this, o);
                        this.dataConnection = null;
                        Dom.removeClass(this.get(CONTENT_EL).parentNode,
                                this.LOADING_CLASSNAME);
                        this._loading = false;
                    },
                    scope: this,
                    timeout: this.get(DATA_TIMEOUT)
                },

                this.get(POST_DATA)
            );
        },
        _createTabElement: function(attr) {
            var el = document.createElement('li'),
                a = document.createElement('a'),
                label = attr.label || null,
                labelEl = attr.labelEl || null;
            
            a.href = attr.href || '#'; // TODO: Use Dom.setAttribute?
            el.appendChild(a);
            
            if (labelEl) { // user supplied labelEl
                if (!label) { // user supplied label
                    label = this._getLabel();
                }
            } else {
                labelEl = this._createLabelEl();
            }
            
            a.appendChild(labelEl);
            
            YAHOO.log('creating Tab Dom', 'info', 'Tab');
            return el;
        },

        _getLabelEl: function() {
            return this.getElementsByTagName(this.LABEL_TAGNAME)[0];
        },

        _createLabelEl: function() {
            var el = document.createElement(this.LABEL_TAGNAME);
            return el;
        },
    
        
        _getLabel: function() {
            var el = this.get(LABEL_EL);
                
                if (!el) {
                    return undefined;
                }
            
            return el.innerHTML;
        },

        _onActivate: function(e, tabview) {
            var tab = this,
                silent = false;

            Y.Event.preventDefault(e);
            if (tab === tabview.get(ACTIVE_TAB)) {
                silent = true; // dont fire activeTabChange if already active
            }
            tabview.set(ACTIVE_TAB, tab, silent);
        },

        _onActivationEventChange: function(e) {
            var tab = this;

            if (e.prevValue != e.newValue) {
                tab.removeListener(e.prevValue, tab._onActivate);
                tab.addListener(e.newValue, tab._onActivate, this, tab);
            }
        }
    });
    
    
    /**
     * Fires when a tab is removed from the tabview
     * @event remove
     * @type CustomEvent
     * @param {Event} An event object with fields for "type" ("remove")
     * and "tabview" (the tabview instance it was removed from) 
     */
    
    YAHOO.widget.Tab = Tab;
})();

