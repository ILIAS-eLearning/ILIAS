(function() {
// internal shorthand
var Dom = YAHOO.util.Dom,
    AttributeProvider = YAHOO.util.AttributeProvider,
	specialTypes = {
		mouseenter: true,
		mouseleave: true
	};

/**
 * Element provides an wrapper object to simplify adding
 * event listeners, using dom methods, and managing attributes. 
 * @module element
 * @namespace YAHOO.util
 * @requires yahoo, dom, event
 */

/**
 * Element provides an wrapper object to simplify adding
 * event listeners, using dom methods, and managing attributes. 
 * @class Element
 * @uses YAHOO.util.AttributeProvider
 * @constructor
 * @param el {HTMLElement | String} The html element that 
 * represents the Element.
 * @param {Object} map A key-value map of initial config names and values
 */
var Element = function(el, map) {
    this.init.apply(this, arguments);
};

Element.DOM_EVENTS = {
    'click': true,
    'dblclick': true,
    'keydown': true,
    'keypress': true,
    'keyup': true,
    'mousedown': true,
    'mousemove': true,
    'mouseout': true, 
    'mouseover': true, 
    'mouseup': true,
    'mouseenter': true, 
    'mouseleave': true,
    'focus': true,
    'blur': true,
    'submit': true,
    'change': true
};

Element.prototype = {
    /**
     * Dom events supported by the Element instance.
     * @property DOM_EVENTS
     * @type Object
     */
    DOM_EVENTS: null,

    DEFAULT_HTML_SETTER: function(value, key) {
        var el = this.get('element');
        
        if (el) {
            el[key] = value;
        }

		return value;

    },

    DEFAULT_HTML_GETTER: function(key) {
        var el = this.get('element'),
            val;

        if (el) {
            val = el[key];
        }

        return val;
    },

    /**
     * Wrapper for HTMLElement method.
     * @method appendChild
     * @param {YAHOO.util.Element || HTMLElement} child The element to append. 
     * @return {HTMLElement} The appended DOM element. 
     */
    appendChild: function(child) {
        child = child.get ? child.get('element') : child;
        return this.get('element').appendChild(child);
    },
    
    /**
     * Wrapper for HTMLElement method.
     * @method getElementsByTagName
     * @param {String} tag The tagName to collect
     * @return {HTMLCollection} A collection of DOM elements. 
     */
    getElementsByTagName: function(tag) {
        return this.get('element').getElementsByTagName(tag);
    },
    
    /**
     * Wrapper for HTMLElement method.
     * @method hasChildNodes
     * @return {Boolean} Whether or not the element has childNodes
     */
    hasChildNodes: function() {
        return this.get('element').hasChildNodes();
    },
    
    /**
     * Wrapper for HTMLElement method.
     * @method insertBefore
     * @param {HTMLElement} element The HTMLElement to insert
     * @param {HTMLElement} before The HTMLElement to insert
     * the element before.
     * @return {HTMLElement} The inserted DOM element. 
     */
    insertBefore: function(element, before) {
        element = element.get ? element.get('element') : element;
        before = (before && before.get) ? before.get('element') : before;
        
        return this.get('element').insertBefore(element, before);
    },
    
    /**
     * Wrapper for HTMLElement method.
     * @method removeChild
     * @param {HTMLElement} child The HTMLElement to remove
     * @return {HTMLElement} The removed DOM element. 
     */
    removeChild: function(child) {
        child = child.get ? child.get('element') : child;
        return this.get('element').removeChild(child);
    },
    
    /**
     * Wrapper for HTMLElement method.
     * @method replaceChild
     * @param {HTMLElement} newNode The HTMLElement to insert
     * @param {HTMLElement} oldNode The HTMLElement to replace
     * @return {HTMLElement} The replaced DOM element. 
     */
    replaceChild: function(newNode, oldNode) {
        newNode = newNode.get ? newNode.get('element') : newNode;
        oldNode = oldNode.get ? oldNode.get('element') : oldNode;
        return this.get('element').replaceChild(newNode, oldNode);
    },

    
    /**
     * Registers Element specific attributes.
     * @method initAttributes
     * @param {Object} map A key-value map of initial attribute configs
     */
    initAttributes: function(map) {
    },

    /**
     * Adds a listener for the given event.  These may be DOM or 
     * customEvent listeners.  Any event that is fired via fireEvent
     * can be listened for.  All handlers receive an event object. 
     * @method addListener
     * @param {String} type The name of the event to listen for
     * @param {Function} fn The handler to call when the event fires
     * @param {Any} obj A variable to pass to the handler
     * @param {Object} scope The object to use for the scope of the handler 
     */
    addListener: function(type, fn, obj, scope) {

        scope = scope || this;

        var Event = YAHOO.util.Event,
			el = this.get('element') || this.get('id'),
        	self = this;


		if (specialTypes[type] && !Event._createMouseDelegate) {
	        YAHOO.log("Using a " + type + " event requires the event-mouseenter module", "error", "Event");
	        return false;	
		}


        if (!this._events[type]) { // create on the fly

            if (el && this.DOM_EVENTS[type]) {
				Event.on(el, type, function(e, matchedEl) {

					// Supplement IE with target, currentTarget relatedTarget

	                if (e.srcElement && !e.target) { 
	                    e.target = e.srcElement;
	                }

					if ((e.toElement && !e.relatedTarget) || (e.fromElement && !e.relatedTarget)) {
						e.relatedTarget = Event.getRelatedTarget(e);
					}
					
					if (!e.currentTarget) {
						e.currentTarget = el;
					}

					//	Note: matchedEl el is passed back for delegated listeners
		            self.fireEvent(type, e, matchedEl);

		        }, obj, scope);
            }
            this.createEvent(type, {scope: this});
        }
        
        return YAHOO.util.EventProvider.prototype.subscribe.apply(this, arguments); // notify via customEvent
    },


    /**
     * Alias for addListener
     * @method on
     * @param {String} type The name of the event to listen for
     * @param {Function} fn The function call when the event fires
     * @param {Any} obj A variable to pass to the handler
     * @param {Object} scope The object to use for the scope of the handler 
     */
    on: function() {
        return this.addListener.apply(this, arguments);
    },
    
    /**
     * Alias for addListener
     * @method subscribe
     * @param {String} type The name of the event to listen for
     * @param {Function} fn The function call when the event fires
     * @param {Any} obj A variable to pass to the handler
     * @param {Object} scope The object to use for the scope of the handler 
     */
    subscribe: function() {
        return this.addListener.apply(this, arguments);
    },
    
    /**
     * Remove an event listener
     * @method removeListener
     * @param {String} type The name of the event to listen for
     * @param {Function} fn The function call when the event fires
     */
    removeListener: function(type, fn) {
        return this.unsubscribe.apply(this, arguments);
    },
    
    /**
     * Wrapper for Dom method.
     * @method addClass
     * @param {String} className The className to add
     */
    addClass: function(className) {
        Dom.addClass(this.get('element'), className);
    },
    
    /**
     * Wrapper for Dom method.
     * @method getElementsByClassName
     * @param {String} className The className to collect
     * @param {String} tag (optional) The tag to use in
     * conjunction with class name
     * @return {Array} Array of HTMLElements
     */
    getElementsByClassName: function(className, tag) {
        return Dom.getElementsByClassName(className, tag,
                this.get('element') );
    },
    
    /**
     * Wrapper for Dom method.
     * @method hasClass
     * @param {String} className The className to add
     * @return {Boolean} Whether or not the element has the class name
     */
    hasClass: function(className) {
        return Dom.hasClass(this.get('element'), className); 
    },
    
    /**
     * Wrapper for Dom method.
     * @method removeClass
     * @param {String} className The className to remove
     */
    removeClass: function(className) {
        return Dom.removeClass(this.get('element'), className);
    },
    
    /**
     * Wrapper for Dom method.
     * @method replaceClass
     * @param {String} oldClassName The className to replace
     * @param {String} newClassName The className to add
     */
    replaceClass: function(oldClassName, newClassName) {
        return Dom.replaceClass(this.get('element'), 
                oldClassName, newClassName);
    },
    
    /**
     * Wrapper for Dom method.
     * @method setStyle
     * @param {String} property The style property to set
     * @param {String} value The value to apply to the style property
     */
    setStyle: function(property, value) {
        return Dom.setStyle(this.get('element'),  property, value); // TODO: always queuing?
    },
    
    /**
     * Wrapper for Dom method.
     * @method getStyle
     * @param {String} property The style property to retrieve
     * @return {String} The current value of the property
     */
    getStyle: function(property) {
        return Dom.getStyle(this.get('element'),  property);
    },
    
    /**
     * Apply any queued set calls.
     * @method fireQueue
     */
    fireQueue: function() {
        var queue = this._queue;
        for (var i = 0, len = queue.length; i < len; ++i) {
            this[queue[i][0]].apply(this, queue[i][1]);
        }
    },
    
    /**
     * Appends the HTMLElement into either the supplied parentNode.
     * @method appendTo
     * @param {HTMLElement | Element} parentNode The node to append to
     * @param {HTMLElement | Element} before An optional node to insert before
     * @return {HTMLElement} The appended DOM element. 
     */
    appendTo: function(parent, before) {
        parent = (parent.get) ?  parent.get('element') : Dom.get(parent);
        
        this.fireEvent('beforeAppendTo', {
            type: 'beforeAppendTo',
            target: parent
        });
        
        
        before = (before && before.get) ? 
                before.get('element') : Dom.get(before);
        var element = this.get('element');
        
        if (!element) {
            YAHOO.log('appendTo failed: element not available',
                    'error', 'Element');
            return false;
        }
        
        if (!parent) {
            YAHOO.log('appendTo failed: parent not available',
                    'error', 'Element');
            return false;
        }
        
        if (element.parent != parent) {
            if (before) {
                parent.insertBefore(element, before);
            } else {
                parent.appendChild(element);
            }
        }
        
        YAHOO.log(element + 'appended to ' + parent);
        
        this.fireEvent('appendTo', {
            type: 'appendTo',
            target: parent
        });

        return element;
    },
    
    get: function(key) {
        var configs = this._configs || {},
            el = configs.element; // avoid loop due to 'element'

        if (el && !configs[key] && !YAHOO.lang.isUndefined(el.value[key]) ) {
            this._setHTMLAttrConfig(key);
        }

        return AttributeProvider.prototype.get.call(this, key);
    },

    setAttributes: function(map, silent) {
        // set based on configOrder
        var done = {},
            configOrder = this._configOrder;

        // set based on configOrder
        for (var i = 0, len = configOrder.length; i < len; ++i) {
            if (map[configOrder[i]] !== undefined) {
                done[configOrder[i]] = true;
                this.set(configOrder[i], map[configOrder[i]], silent);
            }
        }

        // unconfigured (e.g. Dom attributes)
        for (var att in map) {
            if (map.hasOwnProperty(att) && !done[att]) {
                this.set(att, map[att], silent);
            }
        }
    },

    set: function(key, value, silent) {
        var el = this.get('element');
        if (!el) {
            this._queue[this._queue.length] = ['set', arguments];
            if (this._configs[key]) {
                this._configs[key].value = value; // so "get" works while queueing
            
            }
            return;
        }
        
        // set it on the element if not configured and is an HTML attribute
        if ( !this._configs[key] && !YAHOO.lang.isUndefined(el[key]) ) {
            this._setHTMLAttrConfig(key);
        }

        return AttributeProvider.prototype.set.apply(this, arguments);
    },
    
    setAttributeConfig: function(key, map, init) {
        this._configOrder.push(key);
        AttributeProvider.prototype.setAttributeConfig.apply(this, arguments);
    },

    createEvent: function(type, config) {
        this._events[type] = true;
        return AttributeProvider.prototype.createEvent.apply(this, arguments);
    },
    
    init: function(el, attr) {
        this._initElement(el, attr); 
    },

    destroy: function() {
        var el = this.get('element');
        YAHOO.util.Event.purgeElement(el, true); // purge DOM listeners recursively
        this.unsubscribeAll(); // unsubscribe all custom events

        if (el && el.parentNode) {
            el.parentNode.removeChild(el); // pull from the DOM
        }

        // revert initial configs
        this._queue = [];
        this._events = {};
        this._configs = {};
        this._configOrder = []; 
    },

    _initElement: function(el, attr) {
        this._queue = this._queue || [];
        this._events = this._events || {};
        this._configs = this._configs || {};
        this._configOrder = []; 
        attr = attr || {};
        attr.element = attr.element || el || null;

        var isReady = false;  // to determine when to init HTMLElement and content

        var DOM_EVENTS = Element.DOM_EVENTS;
        this.DOM_EVENTS = this.DOM_EVENTS || {};

        for (var event in DOM_EVENTS) {
            if (DOM_EVENTS.hasOwnProperty(event)) {
                this.DOM_EVENTS[event] = DOM_EVENTS[event];
            }
        }

        if (typeof attr.element === 'string') { // register ID for get() access
            this._setHTMLAttrConfig('id', { value: attr.element });
        }

        if (Dom.get(attr.element)) {
            isReady = true;
            this._initHTMLElement(attr);
            this._initContent(attr);
        }

        YAHOO.util.Event.onAvailable(attr.element, function() {
            if (!isReady) { // otherwise already done
                this._initHTMLElement(attr);
            }

            this.fireEvent('available', { type: 'available', target: Dom.get(attr.element) });  
        }, this, true);
        
        YAHOO.util.Event.onContentReady(attr.element, function() {
            if (!isReady) { // otherwise already done
                this._initContent(attr);
            }
            this.fireEvent('contentReady', { type: 'contentReady', target: Dom.get(attr.element) });  
        }, this, true);
    },

    _initHTMLElement: function(attr) {
        /**
         * The HTMLElement the Element instance refers to.
         * @attribute element
         * @type HTMLElement
         */
        this.setAttributeConfig('element', {
            value: Dom.get(attr.element),
            readOnly: true
         });
    },

    _initContent: function(attr) {
        this.initAttributes(attr);
        this.setAttributes(attr, true);
        this.fireQueue();

    },

    /**
     * Sets the value of the property and fires beforeChange and change events.
     * @private
     * @method _setHTMLAttrConfig
     * @param {YAHOO.util.Element} element The Element instance to
     * register the config to.
     * @param {String} key The name of the config to register
     * @param {Object} map A key-value map of the config's params
     */
    _setHTMLAttrConfig: function(key, map) {
        var el = this.get('element');
        map = map || {};
        map.name = key;

        map.setter = map.setter || this.DEFAULT_HTML_SETTER;
        map.getter = map.getter || this.DEFAULT_HTML_GETTER;

        map.value = map.value || el[key];
        this._configs[key] = new YAHOO.util.Attribute(map, this);
    }
};

/**
 * Fires when the Element's HTMLElement can be retrieved by Id.
 * <p>See: <a href="#addListener">Element.addListener</a></p>
 * <p><strong>Event fields:</strong><br>
 * <code>&lt;String&gt; type</code> available<br>
 * <code>&lt;HTMLElement&gt;
 * target</code> the HTMLElement bound to this Element instance<br>
 * <p><strong>Usage:</strong><br>
 * <code>var handler = function(e) {var target = e.target};<br>
 * myTabs.addListener('available', handler);</code></p>
 * @event available
 */
 
/**
 * Fires when the Element's HTMLElement subtree is rendered.
 * <p>See: <a href="#addListener">Element.addListener</a></p>
 * <p><strong>Event fields:</strong><br>
 * <code>&lt;String&gt; type</code> contentReady<br>
 * <code>&lt;HTMLElement&gt;
 * target</code> the HTMLElement bound to this Element instance<br>
 * <p><strong>Usage:</strong><br>
 * <code>var handler = function(e) {var target = e.target};<br>
 * myTabs.addListener('contentReady', handler);</code></p>
 * @event contentReady
 */

/**
 * Fires before the Element is appended to another Element.
 * <p>See: <a href="#addListener">Element.addListener</a></p>
 * <p><strong>Event fields:</strong><br>
 * <code>&lt;String&gt; type</code> beforeAppendTo<br>
 * <code>&lt;HTMLElement/Element&gt;
 * target</code> the HTMLElement/Element being appended to 
 * <p><strong>Usage:</strong><br>
 * <code>var handler = function(e) {var target = e.target};<br>
 * myTabs.addListener('beforeAppendTo', handler);</code></p>
 * @event beforeAppendTo
 */

/**
 * Fires after the Element is appended to another Element.
 * <p>See: <a href="#addListener">Element.addListener</a></p>
 * <p><strong>Event fields:</strong><br>
 * <code>&lt;String&gt; type</code> appendTo<br>
 * <code>&lt;HTMLElement/Element&gt;
 * target</code> the HTMLElement/Element being appended to 
 * <p><strong>Usage:</strong><br>
 * <code>var handler = function(e) {var target = e.target};<br>
 * myTabs.addListener('appendTo', handler);</code></p>
 * @event appendTo
 */

YAHOO.augment(Element, AttributeProvider);
YAHOO.util.Element = Element;
})();

