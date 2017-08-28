/*
Copyright (c) 2006, Yahoo! Inc. All rights reserved.
Code licensed under the BSD License:
http://developer.yahoo.net/yui/license.txt
Version: 0.10.0
*/

/**
 * @class Provides helper methods for DOM elements.
 */
YAHOO.util.Dom = function() {
   var ua = navigator.userAgent.toLowerCase();
   var id_counter = 0;
   
   return {
      /**
       * Returns an HTMLElement reference
       * @param {String or HTMLElement} Accepts either a string to use as an ID for getting a DOM reference, or an actual DOM reference.
       * @return {HTMLElement} A DOM reference to an HTML element.
       */
      get: function(el) {
         if (typeof el == 'string') { // accept object or id
            el = document.getElementById(el);
         }
         
         return el;
      },
   
      /**
       * Normalizes currentStyle and ComputedStyle.
       * @param {String or HTMLElement} Accepts either a string to use as an ID for getting a DOM reference, or an actual DOM reference.
       * @param {String} property The style property whose value is returned.
       * @return {String} The current value of the style property.
       */
      getStyle: function(el, property) {
         var value = null;
         var dv = document.defaultView;
      
         el = this.get(el);
         
         if (property == 'opacity' && el.filters) 
         {// IE opacity
            value = 1;
            try {
               value = el.filters.item('DXImageTransform.Microsoft.Alpha').opacity / 100;
            } catch(e) {
               try {
                  value = el.filters.item('alpha').opacity / 100;
               } catch(e) {}
            }
         }
         else if (el.style[property]) 
         {
            value = el.style[property];    
         }
         else if (el.currentStyle && el.currentStyle[property]) {
            value = el.currentStyle[property];
         }
         else if ( dv && dv.getComputedStyle )
         {  // convert camelCase to hyphen-case
            
            var converted = '';
            for(i = 0, len = property.length;i < len; ++i) {
               if (property.charAt(i) == property.charAt(i).toUpperCase()) 
               {
                  converted = converted + '-' + property.charAt(i).toLowerCase();
               } else {
                  converted = converted + property.charAt(i);
               }
            }
            
            if (dv.getComputedStyle(el, '').getPropertyValue(converted)) {
               value = dv.getComputedStyle(el, '').getPropertyValue(converted);
            }
         }
   
         return value;
      },
   
      /**
       * Wrapper for setting style properties of HTMLElements.  Normalizes "opacity" across modern browsers.
       * @param {String or HTMLElement} Accepts either a string to use as an ID for getting a DOM reference, or an actual DOM reference.
       * @param {String} property The style property to be set.
       * @param {String} val The value to apply to the given property.
       */
      setStyle: function(el, property, val) {
         el = this.get(el);
         switch(property) {
            case 'opacity' :
               if (el.filters) {
                  el.style.filter = 'alpha(opacity=' + val * 100 + ')';
                  
                  if (!el.currentStyle.hasLayout) {
                     el.style.zoom = 1;
                  }
               } else {
                  el.style.opacity = val;
                  el.style['-moz-opacity'] = val;
                  el.style['-khtml-opacity'] = val;
               }
               break;
            default :
               el.style[property] = val;
         }
      },
      
      /**
       * Gets the current position of an element based on page coordinates.  Element must be part of the DOM tree to have page coordinates (display:none or elements not appended return false).
       * @param {String or HTMLElement} Accepts either a string to use as an ID for getting a DOM reference, or an actual DOM reference.
       */
      getXY: function(el) {
         el = this.get(el);
   
         // has to be part of document to have pageXY
         if (el.parentNode === null || this.getStyle(el, 'display') == 'none') {
            return false;
         }
         
         /**
          * Position of the html element (x, y)
          * @private
          * @type Array
          */
         var parent = null;
         var pos = [];
         var box;
         
         if (el.getBoundingClientRect) { // IE
            box = el.getBoundingClientRect();
            Math.max ( document.documentElement.scrollTop  , document.body.scrollTop );
            var scrollTop = Math.max(document.documentElement.scrollTop, document.body.scrollTop);
            var scrollLeft = Math.max(document.documentElement.scrollLeft, document.body.scrollLeft);
            
            return [box.left + scrollLeft, box.top + scrollTop];
         }
         else if (document.getBoxObjectFor) { // gecko
            box = document.getBoxObjectFor(el);
            pos = [box.x, box.y];
         }
         else { // safari/opera
            pos = [el.offsetLeft, el.offsetTop];
            parent = el.offsetParent;
            if (parent != el) {
               while (parent) {
                  pos[0] += parent.offsetLeft;
                  pos[1] += parent.offsetTop;
                  parent = parent.offsetParent;
               }
            }
            
            // opera (& safari absolute) incorrectly account for body offsetTop
            if (
               ua.indexOf('opera') != -1 
               || ( ua.indexOf('safari') != -1 && this.getStyle(el, 'position') == 'absolute' ) 
            ) {
               pos[0] -= document.body.offsetLeft;
               pos[1] -= document.body.offsetTop;
            } 
         }
         
         if (el.parentNode) { parent = el.parentNode; }
         else { parent = null; }
   
         while (parent && parent.tagName != 'BODY' && parent.tagName != 'HTML') {
            pos[0] -= parent.scrollLeft;
            pos[1] -= parent.scrollTop;
   
            if (parent.parentNode) { parent = parent.parentNode; } 
            else { parent = null; }
         }
   
         return pos;
      },
      
      /**
       * Gets the current X position of an element based on page coordinates.  The element must be part of the DOM tree to have page coordinates (display:none or elements not appended return false).
       * @param {String or HTMLElement} Accepts either a string to use as an ID for getting a DOM reference, or an actual DOM reference.
       */
      getX: function(el) {
         return this.getXY(el)[0];
      },
      
      /**
       * Gets the current Y position of an element based on page coordinates.  Element must be part of the DOM tree to have page coordinates (display:none or elements not appended return false).
       * @param {String or HTMLElement} Accepts either a string to use as an ID for getting a DOM reference, or an actual DOM reference.
       */
      getY: function(el) {
         return this.getXY(el)[1];
      },
      
      /**
       * Set the position of an html element in page coordinates, regardless of how the element is positioned.
       * The element must be part of the DOM tree to have page coordinates (display:none or elements not appended return false).
       * @param {String or HTMLElement} Accepts either a string to use as an ID for getting a DOM reference, or an actual DOM reference.
       * @param {array} pos Contains X & Y values for new position (coordinates are page-based)
       */
      setXY: function(el, pos, noRetry) {
         el = this.get(el);
         var pageXY = YAHOO.util.Dom.getXY(el);
         if (pageXY === false) { return false; } // has to be part of doc to have pageXY
   
         if (this.getStyle(el, 'position') == 'static') { // default to relative
            this.setStyle(el, 'position', 'relative');
         }
         
         var delta = [
            parseInt( YAHOO.util.Dom.getStyle(el, 'left'), 10 ),
            parseInt( YAHOO.util.Dom.getStyle(el, 'top'), 10 )
         ];
      
         if ( isNaN(delta[0]) ) { delta[0] = 0; } // defalts to 'auto'
         if ( isNaN(delta[1]) ) { delta[1] = 0; }
   
         if (pos[0] !== null) { el.style.left = pos[0] - pageXY[0] + delta[0] + 'px'; }
         if (pos[1] !== null) { el.style.top = pos[1] - pageXY[1] + delta[1] + 'px'; }
   
         var newXY = this.getXY(el);
   
         // if retry is true, try one more time if we miss
         if (!noRetry && (newXY[0] != pos[0] || newXY[1] != pos[1]) ) {
            var f = function() { YAHOO.util.Dom.setXY(el, pos, true) };
            setTimeout(f, 0); // "delay" for IE resize timing issue
         }
      },
      
      /**
       * Set the X position of an html element in page coordinates, regardless of how the element is positioned.
       * The element must be part of the DOM tree to have page coordinates (display:none or elements not appended return false).
       * @param {String or HTMLElement} Accepts either a string to use as an ID for getting a DOM reference, or an actual DOM reference.
       * @param {Int} x to use as the X coordinate.
       */
      setX: function(el, x) {
         this.setXY(el, [x, null]);
      },
      
      /**
       * Set the Y position of an html element in page coordinates, regardless of how the element is positioned.
       * The element must be part of the DOM tree to have page coordinates (display:none or elements not appended return false).
       * @param {String or HTMLElement} Accepts either a string to use as an ID for getting a DOM reference, or an actual DOM reference.
       * @param {Int} Value to use as the Y coordinate.
       */
      setY: function(el, y) {
         this.setXY(el, [null, y]);
      },
      
      /**
       * Returns the region position of the given element.
       * The element must be part of the DOM tree to have a region (display:none or elements not appended return false).
       * @param {String or HTMLElement} Accepts either a string to use as an ID for getting a DOM reference, or an actual DOM reference.
       * @return {Region} A Region instance containing "top, left, bottom, right" member data.
       */
      getRegion: function(el) {
         el = this.get(el);
         return new YAHOO.util.Region.getRegion(el);
      },
      
      /**
       * Returns the width of the client (viewport).
       * @return {Int} The width of the viewable area of the page.
       */
      getClientWidth: function() {
         return (
            document.documentElement.offsetWidth
            || document.body.offsetWidth
         );
      },
      
      /**
       * Returns the height of the client (viewport).
       * @return {Int} The height of the viewable area of the page.
       */
      getClientHeight: function() {
         return (
            self.innerHeight 
            || document.documentElement.clientHeight
            || document.body.clientHeight
         );
      },
      
      getElementsByClassName: function(className, tag, root) {
         var re = new RegExp('(^|\\s)' + className + '(\\s|$)');
         
         var method = function(el) {
            return re.test(el['className']);
         };
         
         return this.getElementsBy(method, tag, root);
      },
   
      hasClass: function(el, className) {
         el = this.get(el);
         var re = new RegExp('(^|\\s)' + className + '(\\s|$)');
         return re.test(el['className']);
      },
   
      addClass: function(el, className) {
         if (this.hasClass(el, className)) { return; } // already present
         
         el = this.get(el);
         el['className'] = [el['className'], className].join(' ');
      },
   
      removeClass: function(el, className) {
         if (!this.hasClass(el, className)) { return; } // not present
         
         el = this.get(el);
         var re = new RegExp('(^|\\s)' + className + '(\\s|$)');
         var c = el['className'];
         
         el['className'] = c.replace( re, ' ');
      },
      
      replaceClass: function(el, oldClassName, newClassName) {
         el = this.get(el);
         
         this.removeClass(el, oldClassName);
         this.addClass(el, newClassName);
      },
      
      generateId: function(el, prefix) {
         prefix = prefix || 'yui-gen';
         if (!el.id) {
            el.id = prefix + id_counter++;
         }
         
         return el.id;
      },
      
      isChild: function(needle, haystack) {
         needle = this.get(needle);
         haystack = this.get(haystack) || document.documentElement;
         
         if (haystack.contains && ua.indexOf('safari') < 0) 
         { // safari "contains" is broken
            return haystack.contains(needle);
         }
         else if ( haystack.compareDocumentPosition ) 
         {
            return !!(haystack.compareDocumentPosition(needle) & 16);
         }
         else 
         { // loop up and test each parent
            var parent = needle.parentNode;
            
            while (parent) {
               if (parent == haystack) {
                  return true;
               }
               else if (parent.tagName == 'HTML') {
                  return false;
               }
               
               parent = parent.parentNode;
            }
            
            return false;
         }         
      },
      
      getElementsBy: function(method, tag, root) {
         tag = tag || '*';
         root = this.get(root) || document;
         
         var nodes = [];
         var elements = root.getElementsByTagName(tag);
   
         for ( var i = 0, len = elements.length; i < len; ++i) {
               if ( method(elements[i]) ) {
                  nodes[nodes.length] = elements[i];
               }
         }

         return nodes;
      }
   }
}();

/*
Copyright (c) 2006, Yahoo! Inc. All rights reserved.
Code licensed under the BSD License:
http://developer.yahoo.net/yui/license.txt
Version: 0.10.0
*/

/**
 * @class A region is a representation of an object on a grid.  It is defined
 * by the top, right, bottom, left extents, so is rectangular by default.  If 
 * other shapes are required, this class could be extended to support it.
 *
 * @param {int} t the top extent
 * @param {int} r the right extent
 * @param {int} b the bottom extent
 * @param {int} l the left extent
 * @constructor
 */
YAHOO.util.Region = function(t, r, b, l) {

    /**
     * The region's top extent
     * @type int
     */
    this.top = t;

    /**
     * The region's right extent
     * @type int
     */
    this.right = r;

    /**
     * The region's bottom extent
     * @type int
     */
    this.bottom = b;

    /**
     * The region's left extent
     * @type int
     */
    this.left = l;
};

/**
 * Returns true if this region contains the region passed in
 *
 * @param  {Region}  region The region to evaluate
 * @return {boolean}        True if the region is contained with this region, 
 *                          else false
 */
YAHOO.util.Region.prototype.contains = function(region) {
    return ( region.left   >= this.left   && 
             region.right  <= this.right  && 
             region.top    >= this.top    && 
             region.bottom <= this.bottom    );

    // this.logger.debug("does " + this + " contain " + region + " ... " + ret);
};

/**
 * Returns the area of the region
 *
 * @return {int} the region's area
 */
YAHOO.util.Region.prototype.getArea = function() {
    return ( (this.bottom - this.top) * (this.right - this.left) );
};

/**
 * Returns the region where the passed in region overlaps with this one
 *
 * @param  {Region} region The region that intersects
 * @return {Region}        The overlap region, or null if there is no overlap
 */
YAHOO.util.Region.prototype.intersect = function(region) {
    var t = Math.max( this.top,    region.top    );
    var r = Math.min( this.right,  region.right  );
    var b = Math.min( this.bottom, region.bottom );
    var l = Math.max( this.left,   region.left   );
    
    if (b >= t && r >= l) {
        return new YAHOO.util.Region(t, r, b, l);
    } else {
        return null;
    }
};

/**
 * Returns the region representing the smallest region that can contain both
 * the passed in region and this region.
 *
 * @param  {Region} region The region that to create the union with
 * @return {Region}        The union region
 */
YAHOO.util.Region.prototype.union = function(region) {
    var t = Math.min( this.top,    region.top    );
    var r = Math.max( this.right,  region.right  );
    var b = Math.max( this.bottom, region.bottom );
    var l = Math.min( this.left,   region.left   );

    return new YAHOO.util.Region(t, r, b, l);
};

/**
 * toString
 * @return string the region properties
 */
YAHOO.util.Region.prototype.toString = function() {
    return ( "Region {" +
             "  t: "    + this.top    + 
             ", r: "    + this.right  + 
             ", b: "    + this.bottom + 
             ", l: "    + this.left   + 
             "}" );
};

/**
 * Returns a region that is occupied by the DOM element
 *
 * @param  {HTMLElement} el The element
 * @return {Region}         The region that the element occupies
 * @static
 */
YAHOO.util.Region.getRegion = function(el) {
    var p = YAHOO.util.Dom.getXY(el);

    var t = p[1];
    var r = p[0] + el.offsetWidth;
    var b = p[1] + el.offsetHeight;
    var l = p[0];

    return new YAHOO.util.Region(t, r, b, l);
};

/////////////////////////////////////////////////////////////////////////////


/**
 * @class
 *
 * A point is a region that is special in that it represents a single point on 
 * the grid.
 *
 * @param {int} x The X position of the point
 * @param {int} y The Y position of the point
 * @constructor
 * @extends Region
 */
YAHOO.util.Point = function(x, y) {
    /**
     * The X position of the point
     * @type int
     */
    this.x      = x;

    /**
     * The Y position of the point
     * @type int
     */
    this.y      = y;

    this.top    = y;
    this.right  = x;
    this.bottom = y;
    this.left   = x;
};

YAHOO.util.Point.prototype = new YAHOO.util.Region();
