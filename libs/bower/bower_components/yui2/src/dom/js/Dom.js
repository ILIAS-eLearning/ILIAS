/**
 * The dom module provides helper methods for manipulating Dom elements.
 * @module dom
 *
 */

(function() {
    // for use with generateId (global to save state if Dom is overwritten)
    YAHOO.env._id_counter = YAHOO.env._id_counter || 0;

    // internal shorthand
    var Y = YAHOO.util,
        lang = YAHOO.lang,
        UA = YAHOO.env.ua,
        trim = YAHOO.lang.trim,
        propertyCache = {}, // for faster hyphen converts
        reCache = {}, // cache className regexes
        RE_TABLE = /^t(?:able|d|h)$/i, // for _calcBorders
        RE_COLOR = /color$/i,

        // DOM aliases 
        document = window.document,     
        documentElement = document.documentElement,

        // string constants
        OWNER_DOCUMENT = 'ownerDocument',
        DEFAULT_VIEW = 'defaultView',
        DOCUMENT_ELEMENT = 'documentElement',
        COMPAT_MODE = 'compatMode',
        OFFSET_LEFT = 'offsetLeft',
        OFFSET_TOP = 'offsetTop',
        OFFSET_PARENT = 'offsetParent',
        PARENT_NODE = 'parentNode',
        NODE_TYPE = 'nodeType',
        TAG_NAME = 'tagName',
        SCROLL_LEFT = 'scrollLeft',
        SCROLL_TOP = 'scrollTop',
        GET_BOUNDING_CLIENT_RECT = 'getBoundingClientRect',
        GET_COMPUTED_STYLE = 'getComputedStyle',
        CURRENT_STYLE = 'currentStyle',
        CSS1_COMPAT = 'CSS1Compat',
        _BACK_COMPAT = 'BackCompat',
        _CLASS = 'class', // underscore due to reserved word
        CLASS_NAME = 'className',
        EMPTY = '',
        SPACE = ' ',
        C_START = '(?:^|\\s)',
        C_END = '(?= |$)',
        G = 'g',
        POSITION = 'position',
        FIXED = 'fixed',
        RELATIVE = 'relative',
        LEFT = 'left',
        TOP = 'top',
        MEDIUM = 'medium',
        BORDER_LEFT_WIDTH = 'borderLeftWidth',
        BORDER_TOP_WIDTH = 'borderTopWidth',
    
    // brower detection
        isOpera = UA.opera,
        isSafari = UA.webkit, 
        isGecko = UA.gecko, 
        isIE = UA.ie; 
    
    /**
     * Provides helper methods for DOM elements.
     * @namespace YAHOO.util
     * @class Dom
     * @requires yahoo, event
     */
    Y.Dom = {
        CUSTOM_ATTRIBUTES: (!documentElement.hasAttribute) ? { // IE < 8
            'for': 'htmlFor',
            'class': CLASS_NAME
        } : { // w3c
            'htmlFor': 'for',
            'className': _CLASS
        },

        DOT_ATTRIBUTES: {
            checked: true 
        },

        /**
         * Returns an HTMLElement reference.
         * @method get
         * @param {String | HTMLElement |Array} el Accepts a string to use as an ID for getting a DOM reference, an actual DOM reference, or an Array of IDs and/or HTMLElements.
         * @return {HTMLElement | Array} A DOM reference to an HTML element or an array of HTMLElements.
         */
        get: function(el) {
            var id, nodes, c, i, len, attr, ret = null;

            if (el) {
                if (typeof el == 'string' || typeof el == 'number') { // id
                    id = el + '';
                    el = document.getElementById(el);
                    attr = (el) ? el.attributes : null;
                    if (el && attr && attr.id && attr.id.value === id) { // IE: avoid false match on "name" attribute
                        return el;
                    } else if (el && document.all) { // filter by name
                        el = null;
                        nodes = document.all[id];
                        if (nodes && nodes.length) {
                            for (i = 0, len = nodes.length; i < len; ++i) {
                                if (nodes[i].id === id) {
                                    return nodes[i];
                                }
                            }
                        }
                    }
                } else if (Y.Element && el instanceof Y.Element) {
                    el = el.get('element');
                } else if (!el.nodeType && 'length' in el) { // array-like 
                    c = [];
                    for (i = 0, len = el.length; i < len; ++i) {
                        c[c.length] = Y.Dom.get(el[i]);
                    }
                    
                    el = c;
                }

                ret = el;
            }

            return ret;
        },
    
        getComputedStyle: function(el, property) {
            if (window[GET_COMPUTED_STYLE]) {
                return el[OWNER_DOCUMENT][DEFAULT_VIEW][GET_COMPUTED_STYLE](el, null)[property];
            } else if (el[CURRENT_STYLE]) {
                return Y.Dom.IE_ComputedStyle.get(el, property);
            }
        },

        /**
         * Normalizes currentStyle and ComputedStyle.
         * @method getStyle
         * @param {String | HTMLElement |Array} el Accepts a string to use as an ID, an actual DOM reference, or an Array of IDs and/or HTMLElements.
         * @param {String} property The style property whose value is returned.
         * @return {String | Array} The current value of the style property for the element(s).
         */
        getStyle: function(el, property) {
            return Y.Dom.batch(el, Y.Dom._getStyle, property);
        },

        // branching at load instead of runtime
        _getStyle: function() {
            if (window[GET_COMPUTED_STYLE]) { // W3C DOM method
                return function(el, property) {
                    property = (property === 'float') ? property = 'cssFloat' :
                            Y.Dom._toCamel(property);

                    var value = el.style[property],
                        computed;
                    
                    if (!value) {
                        computed = el[OWNER_DOCUMENT][DEFAULT_VIEW][GET_COMPUTED_STYLE](el, null);
                        if (computed) { // test computed before touching for safari
                            value = computed[property];
                        }
                    }
                    
                    return value;
                };
            } else if (documentElement[CURRENT_STYLE]) {
                return function(el, property) {                         
                    var value;

                    switch(property) {
                        case 'opacity' :// IE opacity uses filter
                            value = 100;
                            try { // will error if no DXImageTransform
                                value = el.filters['DXImageTransform.Microsoft.Alpha'].opacity;

                            } catch(e) {
                                try { // make sure its in the document
                                    value = el.filters('alpha').opacity;
                                } catch(err) {
                                    YAHOO.log('getStyle: IE filter failed',
                                            'error', 'Dom');
                                }
                            }
                            return value / 100;
                        case 'float': // fix reserved word
                            property = 'styleFloat'; // fall through
                        default: 
                            property = Y.Dom._toCamel(property);
                            value = el[CURRENT_STYLE] ? el[CURRENT_STYLE][property] : null;
                            return ( el.style[property] || value );
                    }
                };
            }
        }(),
    
        /**
         * Wrapper for setting style properties of HTMLElements.  Normalizes "opacity" across modern browsers.
         * @method setStyle
         * @param {String | HTMLElement | Array} el Accepts a string to use as an ID, an actual DOM reference, or an Array of IDs and/or HTMLElements.
         * @param {String} property The style property to be set.
         * @param {String} val The value to apply to the given property.
         */
        setStyle: function(el, property, val) {
            Y.Dom.batch(el, Y.Dom._setStyle, { prop: property, val: val });
        },

        _setStyle: function() {
            if (!window.getComputedStyle && document.documentElement.currentStyle) {
                return function(el, args) {
                    var property = Y.Dom._toCamel(args.prop),
                        val = args.val;

                    if (el) {
                        switch (property) {
                            case 'opacity':
                                // remove filter if unsetting or full opacity
                                if (val === '' || val === null || val === 1) {
                                    el.style.removeAttribute('filter');
                                } else if ( lang.isString(el.style.filter) ) { // in case not appended
                                    el.style.filter = 'alpha(opacity=' + val * 100 + ')';
                                    
                                    if (!el[CURRENT_STYLE] || !el[CURRENT_STYLE].hasLayout) {
                                        el.style.zoom = 1; // when no layout or cant tell
                                    }
                                }
                                break;
                            case 'float':
                                property = 'styleFloat';
                            default:
                            el.style[property] = val;
                        }
                    } else {
                        YAHOO.log('element ' + el + ' is undefined', 'error', 'Dom');
                    }
                };
            } else {
                return function(el, args) {
                    var property = Y.Dom._toCamel(args.prop),
                        val = args.val;
                    if (el) {
                        if (property == 'float') {
                            property = 'cssFloat';
                        }
                        el.style[property] = val;
                    } else {
                        YAHOO.log('element ' + el + ' is undefined', 'error', 'Dom');
                    }
                };
            }

        }(),
        
        /**
         * Gets the current position of an element based on page coordinates. 
         * Element must be part of the DOM tree to have page coordinates (display:none or elements not appended return false).
         * @method getXY
         * @param {String | HTMLElement | Array} el Accepts a string to use as an ID, an actual DOM
         * reference, or an Array of IDs and/or HTMLElements
         * @return {Array} The XY position of the element(s)
         */
        getXY: function(el) {
            return Y.Dom.batch(el, Y.Dom._getXY);
        },

        _canPosition: function(el) {
            return ( Y.Dom._getStyle(el, 'display') !== 'none' && Y.Dom._inDoc(el) );
        },

        _getXY: function(node) {
            var scrollLeft, scrollTop, box, doc,
                clientTop, clientLeft,
                round = Math.round, // TODO: round?
                xy = false;

            if (Y.Dom._canPosition(node)) {
                box = node[GET_BOUNDING_CLIENT_RECT]();
                doc = node[OWNER_DOCUMENT];
                scrollLeft = Y.Dom.getDocumentScrollLeft(doc);
                scrollTop = Y.Dom.getDocumentScrollTop(doc);
                xy = [box[LEFT], box[TOP]];

                // remove IE default documentElement offset (border)
                if (clientTop || clientLeft) {
                    xy[0] -= clientLeft;
                    xy[1] -= clientTop;
                }

                if ((scrollTop || scrollLeft)) {
                    xy[0] += scrollLeft;
                    xy[1] += scrollTop;
                }

                // gecko may return sub-pixel (non-int) values
                xy[0] = round(xy[0]);
                xy[1] = round(xy[1]);
            } else {
                YAHOO.log('getXY failed: element not positionable (either not in a document or not displayed)', 'error', 'Dom');
            }

            return xy;
        },
        
        /**
         * Gets the current X position of an element based on page coordinates.  The element must be part of the DOM tree to have page coordinates (display:none or elements not appended return false).
         * @method getX
         * @param {String | HTMLElement | Array} el Accepts a string to use as an ID, an actual DOM reference, or an Array of IDs and/or HTMLElements
         * @return {Number | Array} The X position of the element(s)
         */
        getX: function(el) {
            var f = function(el) {
                return Y.Dom.getXY(el)[0];
            };
            
            return Y.Dom.batch(el, f, Y.Dom, true);
        },
        
        /**
         * Gets the current Y position of an element based on page coordinates.  Element must be part of the DOM tree to have page coordinates (display:none or elements not appended return false).
         * @method getY
         * @param {String | HTMLElement | Array} el Accepts a string to use as an ID, an actual DOM reference, or an Array of IDs and/or HTMLElements
         * @return {Number | Array} The Y position of the element(s)
         */
        getY: function(el) {
            var f = function(el) {
                return Y.Dom.getXY(el)[1];
            };
            
            return Y.Dom.batch(el, f, Y.Dom, true);
        },
        
        /**
         * Set the position of an html element in page coordinates, regardless of how the element is positioned.
         * The element(s) must be part of the DOM tree to have page coordinates (display:none or elements not appended return false).
         * @method setXY
         * @param {String | HTMLElement | Array} el Accepts a string to use as an ID, an actual DOM reference, or an Array of IDs and/or HTMLElements
         * @param {Array} pos Contains X & Y values for new position (coordinates are page-based)
         * @param {Boolean} noRetry By default we try and set the position a second time if the first fails
         */
        setXY: function(el, pos, noRetry) {
            Y.Dom.batch(el, Y.Dom._setXY, { pos: pos, noRetry: noRetry });
        },

        _setXY: function(node, args) {
            var pos = Y.Dom._getStyle(node, POSITION),
                setStyle = Y.Dom.setStyle,
                xy = args.pos,
                noRetry = args.noRetry,

                delta = [ // assuming pixels; if not we will have to retry
                    parseInt( Y.Dom.getComputedStyle(node, LEFT), 10 ),
                    parseInt( Y.Dom.getComputedStyle(node, TOP), 10 )
                ],

                currentXY,
                newXY;
        
            currentXY = Y.Dom._getXY(node);

            if (!xy || currentXY === false) { // has to be part of doc to have xy
                YAHOO.log('xy failed: node not available', 'error', 'Node');
                return false; 
            }
            
            if (pos == 'static') { // default to relative
                pos = RELATIVE;
                setStyle(node, POSITION, pos);
            }

            if ( isNaN(delta[0]) ) {// in case of 'auto'
                delta[0] = (pos == RELATIVE) ? 0 : node[OFFSET_LEFT];
            } 
            if ( isNaN(delta[1]) ) { // in case of 'auto'
                delta[1] = (pos == RELATIVE) ? 0 : node[OFFSET_TOP];
            } 

            if (xy[0] !== null) { // from setX
                setStyle(node, LEFT, xy[0] - currentXY[0] + delta[0] + 'px');
            }

            if (xy[1] !== null) { // from setY
                setStyle(node, TOP, xy[1] - currentXY[1] + delta[1] + 'px');
            }
          
            if (!noRetry) {
                newXY = Y.Dom._getXY(node);

                // if retry is true, try one more time if we miss 
               if ( (xy[0] !== null && newXY[0] != xy[0]) || 
                    (xy[1] !== null && newXY[1] != xy[1]) ) {
                   Y.Dom._setXY(node, { pos: xy, noRetry: true });
               }
            }        

            YAHOO.log('setXY setting position to ' + xy, 'info', 'Node');
        },
        
        /**
         * Set the X position of an html element in page coordinates, regardless of how the element is positioned.
         * The element must be part of the DOM tree to have page coordinates (display:none or elements not appended return false).
         * @method setX
         * @param {String | HTMLElement | Array} el Accepts a string to use as an ID, an actual DOM reference, or an Array of IDs and/or HTMLElements.
         * @param {Int} x The value to use as the X coordinate for the element(s).
         */
        setX: function(el, x) {
            Y.Dom.setXY(el, [x, null]);
        },
        
        /**
         * Set the Y position of an html element in page coordinates, regardless of how the element is positioned.
         * The element must be part of the DOM tree to have page coordinates (display:none or elements not appended return false).
         * @method setY
         * @param {String | HTMLElement | Array} el Accepts a string to use as an ID, an actual DOM reference, or an Array of IDs and/or HTMLElements.
         * @param {Int} x To use as the Y coordinate for the element(s).
         */
        setY: function(el, y) {
            Y.Dom.setXY(el, [null, y]);
        },
        
        /**
         * Returns the region position of the given element.
         * The element must be part of the DOM tree to have a region (display:none or elements not appended return false).
         * @method getRegion
         * @param {String | HTMLElement | Array} el Accepts a string to use as an ID, an actual DOM reference, or an Array of IDs and/or HTMLElements.
         * @return {Region | Array} A Region or array of Region instances containing "top, left, bottom, right" member data.
         */
        getRegion: function(el) {
            var f = function(el) {
                var region = false;
                if ( Y.Dom._canPosition(el) ) {
                    region = Y.Region.getRegion(el);
                    YAHOO.log('getRegion returning ' + region, 'info', 'Dom');
                } else {
                    YAHOO.log('getRegion failed: element not positionable (either not in a document or not displayed)', 'error', 'Dom');
                }

                return region;
            };
            
            return Y.Dom.batch(el, f, Y.Dom, true);
        },
        
        /**
         * Returns the width of the client (viewport).
         * @method getClientWidth
         * @deprecated Now using getViewportWidth.  This interface left intact for back compat.
         * @return {Int} The width of the viewable area of the page.
         */
        getClientWidth: function() {
            return Y.Dom.getViewportWidth();
        },
        
        /**
         * Returns the height of the client (viewport).
         * @method getClientHeight
         * @deprecated Now using getViewportHeight.  This interface left intact for back compat.
         * @return {Int} The height of the viewable area of the page.
         */
        getClientHeight: function() {
            return Y.Dom.getViewportHeight();
        },

        /**
         * Returns an array of HTMLElements with the given class.
         * For optimized performance, include a tag and/or root node when possible.
         * Note: This method operates against a live collection, so modifying the 
         * collection in the callback (removing/appending nodes, etc.) will have
         * side effects.  Instead you should iterate the returned nodes array,
         * as you would with the native "getElementsByTagName" method. 
         * @method getElementsByClassName
         * @param {String} className The class name to match against
         * @param {String} tag (optional) The tag name of the elements being collected
         * @param {String | HTMLElement} root (optional) The HTMLElement or an ID to use as the starting point.
         * This element is not included in the className scan.
         * @param {Function} apply (optional) A function to apply to each element when found 
         * @param {Any} o (optional) An optional arg that is passed to the supplied method
         * @param {Boolean} overrides (optional) Whether or not to override the scope of "method" with "o"
         * @return {Array} An array of elements that have the given class name
         */
        getElementsByClassName: function(className, tag, root, apply, o, overrides) {
            tag = tag || '*';
            root = (root) ? Y.Dom.get(root) : null || document; 
            if (!root) {
                return [];
            }

            var nodes = [],
                elements = root.getElementsByTagName(tag),
                hasClass = Y.Dom.hasClass;

            for (var i = 0, len = elements.length; i < len; ++i) {
                if ( hasClass(elements[i], className) ) {
                    nodes[nodes.length] = elements[i];
                }
            }
            
            if (apply) {
                Y.Dom.batch(nodes, apply, o, overrides);
            }

            return nodes;
        },

        /**
         * Determines whether an HTMLElement has the given className.
         * @method hasClass
         * @param {String | HTMLElement | Array} el The element or collection to test
         * @param {String | RegExp} className the class name to search for, or a regular
         * expression to match against
         * @return {Boolean | Array} A boolean value or array of boolean values
         */
        hasClass: function(el, className) {
            return Y.Dom.batch(el, Y.Dom._hasClass, className);
        },

        _hasClass: function(el, className) {
            var ret = false,
                current;
            
            if (el && className) {
                current = Y.Dom._getAttribute(el, CLASS_NAME) || EMPTY;
                if (current) { // convert line breaks, tabs and other delims to spaces
                    current = current.replace(/\s+/g, SPACE);
                }

                if (className.exec) {
                    ret = className.test(current);
                } else {
                    ret = className && (SPACE + current + SPACE).
                        indexOf(SPACE + className + SPACE) > -1;
                }
            } else {
                YAHOO.log('hasClass called with invalid arguments', 'warn', 'Dom');
            }

            return ret;
        },
    
        /**
         * Adds a class name to a given element or collection of elements.
         * @method addClass         
         * @param {String | HTMLElement | Array} el The element or collection to add the class to
         * @param {String} className the class name to add to the class attribute
         * @return {Boolean | Array} A pass/fail boolean or array of booleans
         */
        addClass: function(el, className) {
            return Y.Dom.batch(el, Y.Dom._addClass, className);
        },

        _addClass: function(el, className) {
            var ret = false,
                current;

            if (el && className) {
                current = Y.Dom._getAttribute(el, CLASS_NAME) || EMPTY;
                if ( !Y.Dom._hasClass(el, className) ) {
                    Y.Dom.setAttribute(el, CLASS_NAME, trim(current + SPACE + className));
                    ret = true;
                }
            } else {
                YAHOO.log('addClass called with invalid arguments', 'warn', 'Dom');
            }

            return ret;
        },
    
        /**
         * Removes a class name from a given element or collection of elements.
         * @method removeClass         
         * @param {String | HTMLElement | Array} el The element or collection to remove the class from
         * @param {String} className the class name to remove from the class attribute
         * @return {Boolean | Array} A pass/fail boolean or array of booleans
         */
        removeClass: function(el, className) {
            return Y.Dom.batch(el, Y.Dom._removeClass, className);
        },
        
        _removeClass: function(el, className) {
            var ret = false,
                current,
                newClass,
                attr;

            if (el && className) {
                current = Y.Dom._getAttribute(el, CLASS_NAME) || EMPTY;
                Y.Dom.setAttribute(el, CLASS_NAME, current.replace(Y.Dom._getClassRegex(className), EMPTY));

                newClass = Y.Dom._getAttribute(el, CLASS_NAME);
                if (current !== newClass) { // else nothing changed
                    Y.Dom.setAttribute(el, CLASS_NAME, trim(newClass)); // trim after comparing to current class
                    ret = true;

                    if (Y.Dom._getAttribute(el, CLASS_NAME) === '') { // remove class attribute if empty
                        attr = (el.hasAttribute && el.hasAttribute(_CLASS)) ? _CLASS : CLASS_NAME;
                        YAHOO.log('removeClass removing empty class attribute', 'info', 'Dom');
                        el.removeAttribute(attr);
                    }
                }

            } else {
                YAHOO.log('removeClass called with invalid arguments', 'warn', 'Dom');
            }

            return ret;
        },
        
        /**
         * Replace a class with another class for a given element or collection of elements.
         * If no oldClassName is present, the newClassName is simply added.
         * @method replaceClass  
         * @param {String | HTMLElement | Array} el The element or collection to remove the class from
         * @param {String} oldClassName the class name to be replaced
         * @param {String} newClassName the class name that will be replacing the old class name
         * @return {Boolean | Array} A pass/fail boolean or array of booleans
         */
        replaceClass: function(el, oldClassName, newClassName) {
            return Y.Dom.batch(el, Y.Dom._replaceClass, { from: oldClassName, to: newClassName });
        },

        _replaceClass: function(el, classObj) {
            var className,
                from,
                to,
                ret = false,
                current;

            if (el && classObj) {
                from = classObj.from;
                to = classObj.to;

                if (!to) {
                    ret = false;
                }  else if (!from) { // just add if no "from"
                    ret = Y.Dom._addClass(el, classObj.to);
                } else if (from !== to) { // else nothing to replace
                    // May need to lead with DBLSPACE?
                    current = Y.Dom._getAttribute(el, CLASS_NAME) || EMPTY;
                    className = (SPACE + current.replace(Y.Dom._getClassRegex(from), SPACE + to).
                            replace(/\s+/g, SPACE)). // normalize white space
                            split(Y.Dom._getClassRegex(to));

                    // insert to into what would have been the first occurrence slot
                    className.splice(1, 0, SPACE + to);
                    Y.Dom.setAttribute(el, CLASS_NAME, trim(className.join(EMPTY)));
                    ret = true;
                }
            } else {
                YAHOO.log('replaceClass called with invalid arguments', 'warn', 'Dom');
            }

            return ret;
        },
        
        /**
         * Returns an ID and applies it to the element "el", if provided.
         * @method generateId  
         * @param {String | HTMLElement | Array} el (optional) An optional element array of elements to add an ID to (no ID is added if one is already present).
         * @param {String} prefix (optional) an optional prefix to use (defaults to "yui-gen").
         * @return {String | Array} The generated ID, or array of generated IDs (or original ID if already present on an element)
         */
        generateId: function(el, prefix) {
            prefix = prefix || 'yui-gen';

            var f = function(el) {
                if (el && el.id) { // do not override existing ID
                    YAHOO.log('generateId returning existing id ' + el.id, 'info', 'Dom');
                    return el.id;
                }

                var id = prefix + YAHOO.env._id_counter++;
                YAHOO.log('generateId generating ' + id, 'info', 'Dom');

                if (el) {
                    if (el[OWNER_DOCUMENT] && el[OWNER_DOCUMENT].getElementById(id)) { // in case one already exists
                        // use failed id plus prefix to help ensure uniqueness
                        return Y.Dom.generateId(el, id + prefix);
                    }
                    el.id = id;
                }
                
                return id;
            };

            // batch fails when no element, so just generate and return single ID
            return Y.Dom.batch(el, f, Y.Dom, true) || f.apply(Y.Dom, arguments);
        },
        
        /**
         * Determines whether an HTMLElement is an ancestor of another HTML element in the DOM hierarchy.
         * @method isAncestor
         * @param {String | HTMLElement} haystack The possible ancestor
         * @param {String | HTMLElement} needle The possible descendent
         * @return {Boolean} Whether or not the haystack is an ancestor of needle
         */
        isAncestor: function(haystack, needle) {
            haystack = Y.Dom.get(haystack);
            needle = Y.Dom.get(needle);
            
            var ret = false;

            if ( (haystack && needle) && (haystack[NODE_TYPE] && needle[NODE_TYPE]) ) {
                if (haystack.contains && haystack !== needle) { // contains returns true when equal
                    ret = haystack.contains(needle);
                }
                else if (haystack.compareDocumentPosition) { // gecko
                    ret = !!(haystack.compareDocumentPosition(needle) & 16);
                }
            } else {
                YAHOO.log('isAncestor failed; invalid input: ' + haystack + ',' + needle, 'error', 'Dom');
            }
            YAHOO.log('isAncestor(' + haystack + ',' + needle + ' returning ' + ret, 'info', 'Dom');
            return ret;
        },
        
        /**
         * Determines whether an HTMLElement is present in the current document.
         * @method inDocument         
         * @param {String | HTMLElement} el The element to search for
         * @param {Object} doc An optional document to search, defaults to element's owner document 
         * @return {Boolean} Whether or not the element is present in the current document
         */
        inDocument: function(el, doc) {
            return Y.Dom._inDoc(Y.Dom.get(el), doc);
        },

        _inDoc: function(el, doc) {
            var ret = false;
            if (el && el[TAG_NAME]) {
                doc = doc || el[OWNER_DOCUMENT]; 
                ret = Y.Dom.isAncestor(doc[DOCUMENT_ELEMENT], el);
            } else {
                YAHOO.log('inDocument failed: invalid input', 'error', 'Dom');
            }
            return ret;
        },
        
        /**
         * Returns an array of HTMLElements that pass the test applied by supplied boolean method.
         * For optimized performance, include a tag and/or root node when possible.
         * Note: This method operates against a live collection, so modifying the 
         * collection in the callback (removing/appending nodes, etc.) will have
         * side effects.  Instead you should iterate the returned nodes array,
         * as you would with the native "getElementsByTagName" method. 
         * @method getElementsBy
         * @param {Function} method - A boolean method for testing elements which receives the element as its only argument.
         * @param {String} tag (optional) The tag name of the elements being collected
         * @param {String | HTMLElement} root (optional) The HTMLElement or an ID to use as the starting point 
         * @param {Function} apply (optional) A function to apply to each element when found 
         * @param {Any} o (optional) An optional arg that is passed to the supplied method
         * @param {Boolean} overrides (optional) Whether or not to override the scope of "method" with "o"
         * @return {Array} Array of HTMLElements
         */
        getElementsBy: function(method, tag, root, apply, o, overrides, firstOnly) {
            tag = tag || '*';
            root = (root) ? Y.Dom.get(root) : null || document; 

                var ret = (firstOnly) ? null : [],
                    elements;
            
            // in case Dom.get() returns null
            if (root) {
                elements = root.getElementsByTagName(tag);
                for (var i = 0, len = elements.length; i < len; ++i) {
                    if ( method(elements[i]) ) {
                        if (firstOnly) {
                            ret = elements[i]; 
                            break;
                        } else {
                            ret[ret.length] = elements[i];
                        }
                    }
                }

                if (apply) {
                    Y.Dom.batch(ret, apply, o, overrides);
                }
            }

            YAHOO.log('getElementsBy returning ' + ret, 'info', 'Dom');
            
            return ret;
        },
        
        /**
         * Returns the first HTMLElement that passes the test applied by the supplied boolean method.
         * @method getElementBy
         * @param {Function} method - A boolean method for testing elements which receives the element as its only argument.
         * @param {String} tag (optional) The tag name of the elements being collected
         * @param {String | HTMLElement} root (optional) The HTMLElement or an ID to use as the starting point 
         * @return {HTMLElement}
         */
        getElementBy: function(method, tag, root) {
            return Y.Dom.getElementsBy(method, tag, root, null, null, null, true); 
        },

        /**
         * Runs the supplied method against each item in the Collection/Array.
         * The method is called with the element(s) as the first arg, and the optional param as the second ( method(el, o) ).
         * @method batch
         * @param {String | HTMLElement | Array} el (optional) An element or array of elements to apply the method to
         * @param {Function} method The method to apply to the element(s)
         * @param {Any} o (optional) An optional arg that is passed to the supplied method
         * @param {Boolean} overrides (optional) Whether or not to override the scope of "method" with "o"
         * @return {Any | Array} The return value(s) from the supplied method
         */
        batch: function(el, method, o, overrides) {
            var collection = [],
                scope = (overrides) ? o : null;
                
            el = (el && (el[TAG_NAME] || el.item)) ? el : Y.Dom.get(el); // skip get() when possible
            if (el && method) {
                if (el[TAG_NAME] || el.length === undefined) { // element or not array-like 
                    return method.call(scope, el, o);
                } 

                for (var i = 0; i < el.length; ++i) {
                    collection[collection.length] = method.call(scope || el[i], el[i], o);
                }
            } else {
                YAHOO.log('batch called with invalid arguments', 'warn', 'Dom');
                return false;
            } 
            return collection;
        },
        
        /**
         * Returns the height of the document.
         * @method getDocumentHeight
         * @return {Int} The height of the actual document (which includes the body and its margin).
         */
        getDocumentHeight: function() {
            var scrollHeight = (document[COMPAT_MODE] != CSS1_COMPAT || isSafari) ? document.body.scrollHeight : documentElement.scrollHeight,
                h = Math.max(scrollHeight, Y.Dom.getViewportHeight());

            YAHOO.log('getDocumentHeight returning ' + h, 'info', 'Dom');
            return h;
        },
        
        /**
         * Returns the width of the document.
         * @method getDocumentWidth
         * @return {Int} The width of the actual document (which includes the body and its margin).
         */
        getDocumentWidth: function() {
            var scrollWidth = (document[COMPAT_MODE] != CSS1_COMPAT || isSafari) ? document.body.scrollWidth : documentElement.scrollWidth,
                w = Math.max(scrollWidth, Y.Dom.getViewportWidth());
            YAHOO.log('getDocumentWidth returning ' + w, 'info', 'Dom');
            return w;
        },

        /**
         * Returns the current height of the viewport.
         * @method getViewportHeight
         * @return {Int} The height of the viewable area of the page (excludes scrollbars).
         */
        getViewportHeight: function() {
            var height = self.innerHeight, // Safari, Opera
                mode = document[COMPAT_MODE];
        
            if ( (mode || isIE) && !isOpera ) { // IE, Gecko
                height = (mode == CSS1_COMPAT) ?
                        documentElement.clientHeight : // Standards
                        document.body.clientHeight; // Quirks
            }
        
            YAHOO.log('getViewportHeight returning ' + height, 'info', 'Dom');
            return height;
        },
        
        /**
         * Returns the current width of the viewport.
         * @method getViewportWidth
         * @return {Int} The width of the viewable area of the page (excludes scrollbars).
         */
        
        getViewportWidth: function() {
            var width = self.innerWidth,  // Safari
                mode = document[COMPAT_MODE];
            
            if (mode || isIE) { // IE, Gecko, Opera
                width = (mode == CSS1_COMPAT) ?
                        documentElement.clientWidth : // Standards
                        document.body.clientWidth; // Quirks
            }
            YAHOO.log('getViewportWidth returning ' + width, 'info', 'Dom');
            return width;
        },

       /**
         * Returns the nearest ancestor that passes the test applied by supplied boolean method.
         * For performance reasons, IDs are not accepted and argument validation omitted.
         * @method getAncestorBy
         * @param {HTMLElement} node The HTMLElement to use as the starting point 
         * @param {Function} method - A boolean method for testing elements which receives the element as its only argument.
         * @return {Object} HTMLElement or null if not found
         */
        getAncestorBy: function(node, method) {
            while ( (node = node[PARENT_NODE]) ) { // NOTE: assignment
                if ( Y.Dom._testElement(node, method) ) {
                    YAHOO.log('getAncestorBy returning ' + node, 'info', 'Dom');
                    return node;
                }
            } 

            YAHOO.log('getAncestorBy returning null (no ancestor passed test)', 'error', 'Dom');
            return null;
        },
        
        /**
         * Returns the nearest ancestor with the given className.
         * @method getAncestorByClassName
         * @param {String | HTMLElement} node The HTMLElement or an ID to use as the starting point 
         * @param {String} className
         * @return {Object} HTMLElement
         */
        getAncestorByClassName: function(node, className) {
            node = Y.Dom.get(node);
            if (!node) {
                YAHOO.log('getAncestorByClassName failed: invalid node argument', 'error', 'Dom');
                return null;
            }
            var method = function(el) { return Y.Dom.hasClass(el, className); };
            return Y.Dom.getAncestorBy(node, method);
        },

        /**
         * Returns the nearest ancestor with the given tagName.
         * @method getAncestorByTagName
         * @param {String | HTMLElement} node The HTMLElement or an ID to use as the starting point 
         * @param {String} tagName
         * @return {Object} HTMLElement
         */
        getAncestorByTagName: function(node, tagName) {
            node = Y.Dom.get(node);
            if (!node) {
                YAHOO.log('getAncestorByTagName failed: invalid node argument', 'error', 'Dom');
                return null;
            }
            var method = function(el) {
                 return el[TAG_NAME] && el[TAG_NAME].toUpperCase() == tagName.toUpperCase();
            };

            return Y.Dom.getAncestorBy(node, method);
        },

        /**
         * Returns the previous sibling that is an HTMLElement. 
         * For performance reasons, IDs are not accepted and argument validation omitted.
         * Returns the nearest HTMLElement sibling if no method provided.
         * @method getPreviousSiblingBy
         * @param {HTMLElement} node The HTMLElement to use as the starting point 
         * @param {Function} method A boolean function used to test siblings
         * that receives the sibling node being tested as its only argument
         * @return {Object} HTMLElement or null if not found
         */
        getPreviousSiblingBy: function(node, method) {
            while (node) {
                node = node.previousSibling;
                if ( Y.Dom._testElement(node, method) ) {
                    return node;
                }
            }
            return null;
        }, 

        /**
         * Returns the previous sibling that is an HTMLElement 
         * @method getPreviousSibling
         * @param {String | HTMLElement} node The HTMLElement or an ID to use as the starting point 
         * @return {Object} HTMLElement or null if not found
         */
        getPreviousSibling: function(node) {
            node = Y.Dom.get(node);
            if (!node) {
                YAHOO.log('getPreviousSibling failed: invalid node argument', 'error', 'Dom');
                return null;
            }

            return Y.Dom.getPreviousSiblingBy(node);
        }, 

        /**
         * Returns the next HTMLElement sibling that passes the boolean method. 
         * For performance reasons, IDs are not accepted and argument validation omitted.
         * Returns the nearest HTMLElement sibling if no method provided.
         * @method getNextSiblingBy
         * @param {HTMLElement} node The HTMLElement to use as the starting point 
         * @param {Function} method A boolean function used to test siblings
         * that receives the sibling node being tested as its only argument
         * @return {Object} HTMLElement or null if not found
         */
        getNextSiblingBy: function(node, method) {
            while (node) {
                node = node.nextSibling;
                if ( Y.Dom._testElement(node, method) ) {
                    return node;
                }
            }
            return null;
        }, 

        /**
         * Returns the next sibling that is an HTMLElement 
         * @method getNextSibling
         * @param {String | HTMLElement} node The HTMLElement or an ID to use as the starting point 
         * @return {Object} HTMLElement or null if not found
         */
        getNextSibling: function(node) {
            node = Y.Dom.get(node);
            if (!node) {
                YAHOO.log('getNextSibling failed: invalid node argument', 'error', 'Dom');
                return null;
            }

            return Y.Dom.getNextSiblingBy(node);
        }, 

        /**
         * Returns the first HTMLElement child that passes the test method. 
         * @method getFirstChildBy
         * @param {HTMLElement} node The HTMLElement to use as the starting point 
         * @param {Function} method A boolean function used to test children
         * that receives the node being tested as its only argument
         * @return {Object} HTMLElement or null if not found
         */
        getFirstChildBy: function(node, method) {
            var child = ( Y.Dom._testElement(node.firstChild, method) ) ? node.firstChild : null;
            return child || Y.Dom.getNextSiblingBy(node.firstChild, method);
        }, 

        /**
         * Returns the first HTMLElement child. 
         * @method getFirstChild
         * @param {String | HTMLElement} node The HTMLElement or an ID to use as the starting point 
         * @return {Object} HTMLElement or null if not found
         */
        getFirstChild: function(node, method) {
            node = Y.Dom.get(node);
            if (!node) {
                YAHOO.log('getFirstChild failed: invalid node argument', 'error', 'Dom');
                return null;
            }
            return Y.Dom.getFirstChildBy(node);
        }, 

        /**
         * Returns the last HTMLElement child that passes the test method. 
         * @method getLastChildBy
         * @param {HTMLElement} node The HTMLElement to use as the starting point 
         * @param {Function} method A boolean function used to test children
         * that receives the node being tested as its only argument
         * @return {Object} HTMLElement or null if not found
         */
        getLastChildBy: function(node, method) {
            if (!node) {
                YAHOO.log('getLastChild failed: invalid node argument', 'error', 'Dom');
                return null;
            }
            var child = ( Y.Dom._testElement(node.lastChild, method) ) ? node.lastChild : null;
            return child || Y.Dom.getPreviousSiblingBy(node.lastChild, method);
        }, 

        /**
         * Returns the last HTMLElement child. 
         * @method getLastChild
         * @param {String | HTMLElement} node The HTMLElement or an ID to use as the starting point 
         * @return {Object} HTMLElement or null if not found
         */
        getLastChild: function(node) {
            node = Y.Dom.get(node);
            return Y.Dom.getLastChildBy(node);
        }, 

        /**
         * Returns an array of HTMLElement childNodes that pass the test method. 
         * @method getChildrenBy
         * @param {HTMLElement} node The HTMLElement to start from
         * @param {Function} method A boolean function used to test children
         * that receives the node being tested as its only argument
         * @return {Array} A static array of HTMLElements
         */
        getChildrenBy: function(node, method) {
            var child = Y.Dom.getFirstChildBy(node, method),
                children = child ? [child] : [];

            Y.Dom.getNextSiblingBy(child, function(node) {
                if ( !method || method(node) ) {
                    children[children.length] = node;
                }
                return false; // fail test to collect all children
            });

            return children;
        },
 
        /**
         * Returns an array of HTMLElement childNodes. 
         * @method getChildren
         * @param {String | HTMLElement} node The HTMLElement or an ID to use as the starting point 
         * @return {Array} A static array of HTMLElements
         */
        getChildren: function(node) {
            node = Y.Dom.get(node);
            if (!node) {
                YAHOO.log('getChildren failed: invalid node argument', 'error', 'Dom');
            }

            return Y.Dom.getChildrenBy(node);
        },

        /**
         * Returns the left scroll value of the document 
         * @method getDocumentScrollLeft
         * @param {HTMLDocument} document (optional) The document to get the scroll value of
         * @return {Int}  The amount that the document is scrolled to the left
         */
        getDocumentScrollLeft: function(doc) {
            doc = doc || document;
            return Math.max(doc[DOCUMENT_ELEMENT].scrollLeft, doc.body.scrollLeft);
        }, 

        /**
         * Returns the top scroll value of the document 
         * @method getDocumentScrollTop
         * @param {HTMLDocument} document (optional) The document to get the scroll value of
         * @return {Int}  The amount that the document is scrolled to the top
         */
        getDocumentScrollTop: function(doc) {
            doc = doc || document;
            return Math.max(doc[DOCUMENT_ELEMENT].scrollTop, doc.body.scrollTop);
        },

        /**
         * Inserts the new node as the previous sibling of the reference node 
         * @method insertBefore
         * @param {String | HTMLElement} newNode The node to be inserted
         * @param {String | HTMLElement} referenceNode The node to insert the new node before 
         * @return {HTMLElement} The node that was inserted (or null if insert fails) 
         */
        insertBefore: function(newNode, referenceNode) {
            newNode = Y.Dom.get(newNode); 
            referenceNode = Y.Dom.get(referenceNode); 
            
            if (!newNode || !referenceNode || !referenceNode[PARENT_NODE]) {
                YAHOO.log('insertAfter failed: missing or invalid arg(s)', 'error', 'Dom');
                return null;
            }       

            return referenceNode[PARENT_NODE].insertBefore(newNode, referenceNode); 
        },

        /**
         * Inserts the new node as the next sibling of the reference node 
         * @method insertAfter
         * @param {String | HTMLElement} newNode The node to be inserted
         * @param {String | HTMLElement} referenceNode The node to insert the new node after 
         * @return {HTMLElement} The node that was inserted (or null if insert fails) 
         */
        insertAfter: function(newNode, referenceNode) {
            newNode = Y.Dom.get(newNode); 
            referenceNode = Y.Dom.get(referenceNode); 
            
            if (!newNode || !referenceNode || !referenceNode[PARENT_NODE]) {
                YAHOO.log('insertAfter failed: missing or invalid arg(s)', 'error', 'Dom');
                return null;
            }       

            if (referenceNode.nextSibling) {
                return referenceNode[PARENT_NODE].insertBefore(newNode, referenceNode.nextSibling); 
            } else {
                return referenceNode[PARENT_NODE].appendChild(newNode);
            }
        },

        /**
         * Creates a Region based on the viewport relative to the document. 
         * @method getClientRegion
         * @return {Region} A Region object representing the viewport which accounts for document scroll
         */
        getClientRegion: function() {
            var t = Y.Dom.getDocumentScrollTop(),
                l = Y.Dom.getDocumentScrollLeft(),
                r = Y.Dom.getViewportWidth() + l,
                b = Y.Dom.getViewportHeight() + t;

            return new Y.Region(t, r, b, l);
        },

        /**
         * Provides a normalized attribute interface. 
         * @method setAttribute
         * @param {String | HTMLElement} el The target element for the attribute.
         * @param {String} attr The attribute to set.
         * @param {String} val The value of the attribute.
         */
        setAttribute: function(el, attr, val) {
            Y.Dom.batch(el, Y.Dom._setAttribute, { attr: attr, val: val });
        },

        _setAttribute: function(el, args) {
            var attr = Y.Dom._toCamel(args.attr),
                val = args.val;

            if (el && el.setAttribute) {
                // set as DOM property, except for BUTTON, which errors on property setter
                if (Y.Dom.DOT_ATTRIBUTES[attr] && el.tagName && el.tagName != 'BUTTON') {
                    el[attr] = val;
                } else {
                    attr = Y.Dom.CUSTOM_ATTRIBUTES[attr] || attr;
                    el.setAttribute(attr, val);
                }
            } else {
                YAHOO.log('setAttribute method not available for ' + el, 'error', 'Dom');
            }
        },

        /**
         * Provides a normalized attribute interface. 
         * @method getAttribute
         * @param {String | HTMLElement} el The target element for the attribute.
         * @param {String} attr The attribute to get.
         * @return {String} The current value of the attribute. 
         */
        getAttribute: function(el, attr) {
            return Y.Dom.batch(el, Y.Dom._getAttribute, attr);
        },


        _getAttribute: function(el, attr) {
            var val;
            attr = Y.Dom.CUSTOM_ATTRIBUTES[attr] || attr;

            if (Y.Dom.DOT_ATTRIBUTES[attr]) {
                val = el[attr];
            } else if (el && 'getAttribute' in el) {
                if (/^(?:href|src)$/.test(attr)) { // use IE flag to return exact value
                    val = el.getAttribute(attr, 2);
                } else {
                    val = el.getAttribute(attr);
                }
            } else {
                YAHOO.log('getAttribute method not available for ' + el, 'error', 'Dom');
            }

            return val;
        },

        _toCamel: function(property) {
            var c = propertyCache;

            function tU(x,l) {
                return l.toUpperCase();
            }

            return c[property] || (c[property] = property.indexOf('-') === -1 ? 
                                    property :
                                    property.replace( /-([a-z])/gi, tU ));
        },

        _getClassRegex: function(className) {
            var re;
            if (className !== undefined) { // allow empty string to pass
                if (className.exec) { // already a RegExp
                    re = className;
                } else {
                    re = reCache[className];
                    if (!re) {
                        // escape special chars (".", "[", etc.)
                        className = className.replace(Y.Dom._patterns.CLASS_RE_TOKENS, '\\$1');
                        className = className.replace(/\s+/g, SPACE); // convert line breaks and other delims
                        re = reCache[className] = new RegExp(C_START + className + C_END, G);
                    }
                }
            }
            return re;
        },

        _patterns: {
            ROOT_TAG: /^body|html$/i, // body for quirks mode, html for standards,
            CLASS_RE_TOKENS: /([\.\(\)\^\$\*\+\?\|\[\]\{\}\\])/g
        },


        _testElement: function(node, method) {
            return node && node[NODE_TYPE] == 1 && ( !method || method(node) );
        },

        _calcBorders: function(node, xy2) {
            var t = parseInt(Y.Dom[GET_COMPUTED_STYLE](node, BORDER_TOP_WIDTH), 10) || 0,
                l = parseInt(Y.Dom[GET_COMPUTED_STYLE](node, BORDER_LEFT_WIDTH), 10) || 0;
            if (isGecko) {
                if (RE_TABLE.test(node[TAG_NAME])) {
                    t = 0;
                    l = 0;
                }
            }
            xy2[0] += l;
            xy2[1] += t;
            return xy2;
        }
    };
        
    var _getComputedStyle = Y.Dom[GET_COMPUTED_STYLE];
    // fix opera computedStyle default color unit (convert to rgb)
    if (UA.opera) {
        Y.Dom[GET_COMPUTED_STYLE] = function(node, att) {
            var val = _getComputedStyle(node, att);
            if (RE_COLOR.test(att)) {
                val = Y.Dom.Color.toRGB(val);
            }

            return val;
        };

    }

    // safari converts transparent to rgba(), others use "transparent"
    if (UA.webkit) {
        Y.Dom[GET_COMPUTED_STYLE] = function(node, att) {
            var val = _getComputedStyle(node, att);

            if (val === 'rgba(0, 0, 0, 0)') {
                val = 'transparent'; 
            }

            return val;
        };

    }

    if (UA.ie && UA.ie >= 8) {
        Y.Dom.DOT_ATTRIBUTES.type = true; // IE 8 errors on input.setAttribute('type')
    }
})();
