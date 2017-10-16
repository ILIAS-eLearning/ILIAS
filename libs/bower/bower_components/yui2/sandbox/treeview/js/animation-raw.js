/* Copyright (c) 2006 Yahoo! Inc. All rights reserved. */

/**
 *
 * Base class for animated DOM objects.
 * @class Base animation class that provides the interface for building animated effects.
 * @requires YAHOO.util.AnimMgr
 * @requires YAHOO.util.Easing
 * @requires YAHOO.util.Dom
 * @requires YAHOO.util.Event
 * @constructor
 * @param {HTMLElement | String} el Reference to the element that will be animated
 * @param {String | Array}       attributes attributes that will be animated
 * @param {Float}                duration    Length of animation (frames or seconds), defaults to time-based
 * @param {Function}             method      Executes every frame
 */

YAHOO.util.Anim = function(el, attributes, duration, method) 
{  
   if (el) {
      this.init(el, attributes, duration, method); 
   }
};

YAHOO.util.Anim.prototype = {
   /**
    * Fires every tween frame.  Applies current value returned from method to attributes, defaults to easing style args
    */
   doTween: function() { // TODO: validate point lengths
      var start;
      var end = null;
      var val;
      var unit;
      var attributes = this['attributes'];
      
      for (var attribute in attributes) {
         unit = (attributes[attribute]['unit'] === null || typeof attributes[attribute]['unit'] == 'undefined') ? this.defaultUnits[attribute] : attributes[attribute]['unit'];

         if (typeof attributes[attribute]['from'] != 'undefined') {
            start = attributes[attribute]['from'];
         } else {
            start = this.getDefault(attribute);
         }

         // To beats by, per SMIL 2.1 spec
         if (typeof attributes[attribute]['to'] != 'undefined') {
            end = attributes[attribute]['to'];
         } else if (typeof attributes[attribute]['by'] != 'undefined') {
            end = start + attributes[attribute]['by'];
         }

         // if end is null, dont change value
         if (end !== null && typeof end != 'undefined') {

            val = this.doMethod(attribute, start, end);
            
            // negative not allowed for these (others too, but these are most common)
            if ( (attribute == 'width' || attribute == 'height' || attribute == 'opacity') && val < 0 ) {
               val = 0;
            }
            
            this.setValue(attribute, val, unit); 
         }
      }
   },

   doMethod: function(attribute, start, end) {
      return this.method(this.currentFrame, start, end - start, this.totalFrames);
   },
   
   /**
    * Applies a value to a attribute by index
    * @param {String} val Value to apply
    * @param {Int} index  Index of attribute
    */
   setValue: function(attribute, val, unit) {
      YAHOO.util.Dom.setStyle(this.getEl(), attribute, val + unit); 
   },                  
   
   getValue: function(attribute) {
      var val = parseFloat( YAHOO.util.Dom.getStyle(this.getEl(), attribute));
      //debug(values);
      return val;
   },
   
   defaultUnits: {
      left: 'px',
      top: 'px',
      width: 'px',
      height: 'px',
      opacity: ''
   },

   /**
    * @constructor
    * @param {HTMLElement | String} el Reference to the element that will be tweened
    * @param {String | Array}       attributes attributes that will be animated
    * @param {String | Array}       start       attributes starting values 
    * @param {String | Array}       end         attributes end values  
    * @param {Float}                duration    Length of animation (frames or seconds), defaults to time-based
    * @param {Function}             method      Executes every frame
    * @param {Object literal}       units       Units to use when setting attribute value(s)
    */   
   init: function(el, attributes, duration, method)
   {  
      var isAnimated = false;
      var startTime = null;
      var endTime = null;
      var actualFrames = 0;
      var defaultValues = {};      

      this.attributes = attributes || null;
      this.duration = duration || 1;  // default to 1 second; TODO: what about frames?
      this.method = method || YAHOO.util.Easing.easeNone;

      this.useSeconds = true; // default to seconds
      this.currentFrame = 0;
      this.totalFrames = YAHOO.util.AnimMgr.fps;
      
      el = YAHOO.util.Dom.get(el);
      
      this.getEl = function() { return el; };
      
      this.getDefault = function(attribute) {
         return defaultValues[attribute];
      };
      
      /**
       * Checks whether the element is currently animated
       * @return {bool} current value of isAnimated
       */
      this.isAnimated = function() {
         return isAnimated;
      };
      
      /**
       * Returns the animation start time
       * @return {Date} current value of startTime
       */
      this.getStartTime = function() {
         return startTime;
      };      
      
      /**
       * Start animation by registering animation element with manager
       */
      this.animate = function() {
         this.onBeforeStart.fire();
         
         this.totalFrames = ( this.useSeconds ) ? YAHOO.util.AnimMgr.fps * this.duration : this.duration;
         YAHOO.util.AnimMgr.registerElement(this);
         
         // get starting values or use defaults
         var attributes = this.attributes;
         var el = this.getEl();
         var val;
         
         for (var attribute in attributes) {
            val = this.getValue(attribute);
         
            if ( isNaN(val) ) { // if 'auto' or other non-number, default to client/offset size
               if (attribute == 'width') {
                  val = el.clientWidth || el.offsetWidth; 
               }
               else if (attribute == 'height') {
                  val = el.clientHeight || el.offsetHeight; 
               }
               else { val = 0; }
            }
            
            defaultValues[attribute] = val;
         }
         
         isAnimated = true;
         actualFrames = 0;
         startTime = new Date();   
         
         var data = {
            time: startTime
         };
         
         this.onStart.fire(data);
      };
         
      this.stop = function() {
         this.currentFrame = 0;
         
         endTime = new Date();
         
         var data = {
            time: endTime,
            duration: endTime - startTime,
            frames: actualFrames,
            fps: actualFrames / this.duration
         };

         isAnimated = false;  
         actualFrames = 0;
         
         this.onComplete.fire(data);
      };
      
      
      var onTween = function() {
         actualFrames += 1;
      };
      
      /**
       * Custom event that fires when animation begins
       * Listen via subscribe method
       * DO NOT OVERRIDE (please)
       */   
      this.onBeforeStart = new YAHOO.util.CustomEvent('beforeStart', this);
      
      /**
       * Custom event that fires when animation begins
       * Listen via subscribe method
       * DO NOT OVERRIDE (please)
       */   
      this.onStart = new YAHOO.util.CustomEvent('start', this);
      
      /**
       * Custom event that fires between each frame
       * Listen via subscribe method
       * DO NOT OVERRIDE (please)
       */
      this.onTween = new YAHOO.util.CustomEvent('tween', this);
      
      /**
       * Custom event that fires when animation ends
       * Listen via subscribe method       
       * DO NOT OVERRIDE (please)
       */
      this.onComplete = new YAHOO.util.CustomEvent('complete', this);

      this.onTween.subscribe(onTween); // keeping doTween in prototype
   }
};

/**
 * @class Handles animation queueing and threading.
 * Used by ygAnim and subclasses.
 */
YAHOO.util.AnimMgr = new function() {
   /** 
    * Reference to the animation Interval
    * @private
    * @type int
    */
   var thread = null;
   
   /** 
    * The current queue of registered animation objects
    * @private
    * @type array
    */   
   var queue = [];

   /** 
    * The number of active animations
    * @private
    * @type int
    */      
   var tweenCount = 0;

   /** 
    * Base frame rate (frames per second)
    * @type int
    * Arbitrarily high for better x-browser calibration (slower browsers drop more frames)
    */
   this.fps = 200;

   /** 
    * Interval delay
    * @type int, in milliseconds
    * defaults to fastest possible
    */
   this.delay = 1;

   /**
    * Adds an animation instance to the animation queue.
    * All animation instances must be registered in order to animate.
    * @param {object} o the animation instance to be be registered
    */
   this.registerElement = function(tween) {
      if ( tween.isAnimated() ) { return false; }// but not if already animating
      
      queue[queue.length] = tween;
      tweenCount += 1;

      this.start();
   };
   
   /**
    * Starts the animation thread.
	 * Only one thread can run at a time.
    */   
   this.start = function() {
      if (thread === null) { thread = setInterval(this.run, this.delay); }
   };

   /**
    * Stops the animation thread or a specific animation instance.
    * @param {object} tween A specific instance to stop (optional)
    * If no instance given, Manager stops thread and all animations
    */   
   this.stop = function(tween) {
      if (!tween)
      {
         clearInterval(thread);
         for (var i = 0, len = queue.length; i < len; ++i) {
            if (queue[i].isAnimated()) {
               queue[i].stop();  
            }
         }
         queue = [];
         thread = null;
         tweenCount = 0;
      }
      else {
         tween.stop();     
         tweenCount -= 1;
         
         if (tweenCount <= 0) { this.stop(); }
      }
   };
   
   /**
    * Called per Interval to handle each animation frame.
    */   
   this.run = function() {
      for (var i = queue.length - 1; i >= 0; --i) {
         var tween = queue[i];
         if ( !tween || !tween.isAnimated() ) { continue; }

         if (tween.currentFrame < tween.totalFrames || tween.totalFrames === null)
         {
            tween.currentFrame += 1;
            
            if (tween.useSeconds) {
               correctFrame(tween);
            }
            
            tween.onTween.fire();     
            tween.doTween();        
         }
         else { YAHOO.util.AnimMgr.stop(tween); }
      }
   };
   
   /**
    * On the fly frame correction to keep animation on time
    */
   var correctFrame = function(tween) {
      var frames = tween.totalFrames;
      var frame = tween.currentFrame;
      var expected = (tween.currentFrame * tween.duration * 1000 / tween.totalFrames);
      var elapsed = (new Date() - tween.getStartTime());
      var tweak = 0;
      
      if (elapsed < tween.duration * 1000) { // check if falling behind
         tweak = Math.round((elapsed / expected - 1) * tween.currentFrame);
      } else { // went over duration, so jump to end
         tweak = frames - (frame + 1); 
      }
      
      if (tweak > 0 && isFinite(tweak)) { // adjust if needed
         if (tween.currentFrame + tweak >= frames) {// dont go past last frame
            tweak = frames - (frame + 1);
         }
         
         tween.currentFrame += tweak;     
      }
   };
}

// TODO: account for margin
YAHOO.util.Motion = function(el, attributes, duration, method) {
   if (el) {
      this.initMotion(el, attributes, duration, method);
   }
     
      //debug(YAHOO.util.Dom.getEl(el).offsetTop); // TODO: bug in ygPos when abs w/o "top" and offsetParent == BODY
};

YAHOO.util.Motion.prototype = new YAHOO.util.Anim();

YAHOO.util.Motion.prototype.defaultUnits.points = 'px';

YAHOO.util.Motion.prototype.doMethod = function(attribute, start, end) {
   var val = null;
   
   if (attribute == 'points') {
      var translatedPoints = this.getTranslatedPoints();
      var t = this.method(this.currentFrame, 0, 100, this.totalFrames) / 100;				
   
      if (translatedPoints) {
         val = YAHOO.util.Bezier.getPosition(translatedPoints, t);
      }
      
   } else {
      val = this.method(this.currentFrame, start, end - start, this.totalFrames);
   }
   
   return val;
};

YAHOO.util.Motion.prototype.getValue = function(attribute) {
   var val = null;
   
   if (attribute == 'points') {
      val = [ this.getValue('left'), this.getValue('top') ];
      if ( isNaN(val[0]) ) { val[0] = 0; }
      if ( isNaN(val[1]) ) { val[1] = 0; }
   } else {
      val = parseFloat( YAHOO.util.Dom.getStyle(this.getEl(), attribute) );
   }
   
   return val;
};

YAHOO.util.Motion.prototype.setValue = function(attribute, val, unit) {
   if (attribute == 'points') {
      YAHOO.util.Dom.setStyle(this.getEl(), 'left', val[0] + unit);
      YAHOO.util.Dom.setStyle(this.getEl(), 'top', val[1] + unit);
   } else {
      YAHOO.util.Dom.setStyle(this.getEl(), attribute, val + unit); 
   }
};

YAHOO.util.Motion.prototype.initMotion = function(el, attributes, duration, method) {
   YAHOO.util.Anim.call(this, el, attributes, duration, method);
   
   attributes = attributes || {};
   attributes.points = attributes.points || {};
   attributes.points.control = attributes.points.control || [];
   
   this.attributes = attributes;
   
   var start;
   var end = null;
   var translatedPoints = null;
   
   this.getTranslatedPoints = function() { return translatedPoints; };
   
   var translateValues = function(val, self) { // TODO: validate length
      var pageXY = YAHOO.util.Dom.getXY(self.getEl());
      val = [ val[0] - pageXY[0] + start[0], val[1] - pageXY[1] + start[1] ];
   
      return val; 
   };
   
   var onStart = function() {
      start = this.getValue('points');
      var attributes = this.attributes;
      var control =  attributes['points']['control'] || [];

      if (control.length > 0 && control[0].constructor != Array) { // could be single point or array of points
         control = [control];
      }
      
      if (YAHOO.util.Dom.getStyle(this.getEl(), 'position') == 'static') { // default to relative
         YAHOO.util.Dom.setStyle(this.getEl(), 'position', 'relative');
      }

      if ((start[0] === 0 || start[1] === 0)) { // these sometimes up when auto
         YAHOO.util.Dom.setXY(this.getEl(), YAHOO.util.Dom.getXY(this.getEl()));
         start = this.getValue('points');

      }

      if (typeof attributes['points']['from'] != 'undefined') {
         YAHOO.util.Dom.setXY(this.getEl(), attributes['points']['from']);
         start = this.getValue('points');
      }
      
      var i, len;
      // TO beats BY, per SMIL 2.1 spec
      if (typeof attributes['points']['to'] != 'undefined') {
         end = translateValues(attributes['points']['to'], this);
         
         for (i = 0, len = control.length; i < len; ++i) {
            control[i] = translateValues(control[i], this);
         }
         
      } else if (typeof attributes['points']['by'] != 'undefined') {
         end = [ start[0] + attributes['points']['by'][0], start[1] + attributes['points']['by'][1]];
         
         for (i = 0, len = control.length; i < len; ++i) {
            control[i] = [ start[0] + control[i][0], start[1] + control[i][1] ];
         }
      }

      if (end) {
         translatedPoints = [start];
         
         if (control.length > 0) { translatedPoints = translatedPoints.concat(control); }
         
         translatedPoints[translatedPoints.length] = end;
      }
   };
   
   this.onStart.subscribe(onStart);
};

YAHOO.util.Scroll = function(el, duration, attributes, method) {
   if (el) {
      this.initScroll(el, duration, attributes, method);
      }
     
      //debug(YAHOO.util.Dom.getEl(el).offsetTop); // TODO: bug in ygPos when abs w/o "top" and offsetParent == BODY
};

YAHOO.util.Scroll.prototype = new YAHOO.util.Anim();

YAHOO.util.Scroll.prototype.defaultUnits.scroll = '';

YAHOO.util.Scroll.prototype.doMethod = function(property, start, end) {
   var val = null;
   
   if (property == 'scroll') {
      val = [
         this.method(this.currentFrame, start[0], end[0] - start[0], this.totalFrames),
         this.method(this.currentFrame, start[1], end[1] - start[1], this.totalFrames)
      ];
      
   } else {
      val = this.method(this.currentFrame, start, end - start, this.totalFrames);
   }
   
   return val;
}

YAHOO.util.Scroll.prototype.getValue = function(property) {
   var val = null;
   var el = this.getEl();
   
   if (property == 'scroll') {
      val = [ el.scrollLeft, el.scrollTop ];
   } else {
      val = parseFloat( YAHOO.util.Dom.getStyle(el, property) );
   }
   
   return val;
};

YAHOO.util.Scroll.prototype.setValue = function(property, val, unit) {
   var el = this.getEl();
   
   if (property == 'scroll') {
      el.scrollLeft = val[0];
      el.scrollTop = val[1];
   } else {
      YAHOO.util.Dom.setStyle(el, property, val + unit); 
   }
};

YAHOO.util.Scroll.prototype.initScroll = function(el, duration, attributes, method) {
   YAHOO.util.Anim.call(this, el, duration, method);
};

YAHOO.util.Easing = new function() {

   this.easeNone = function(t, b, c, d) {
	return b+c*(t/=d);
   //return c*(t/d) + b;   
   };
   
   this.easeIn = function(t, b, c, d) {
   	return b+c*((t/=d)*t*t);
      //return (t==0) ? b : c * Math.pow(2, 10 * (t/d - 1)) + b;
   };
   
   this.easeOut = function(t, b, c, d) {
   	var ts=(t/=d)*t;
   	var tc=ts*t;
   	return b+c*(tc + -3*ts + 3*t);
      //return (t==d) ? b+c : c * (-Math.pow(2, -10 * t/d) + 1) + b;
   };
   
   this.easeBoth = function(t, b, c, d) {
      /*
      if (t==0) return b;
      if (t==d) return b+c;
      if ((t/=d/2) < 1) return c/2 * Math.pow(2, 10 * (t - 1)) + b;
      return c/2 * (-Math.pow(2, -10 * --t) + 2) + b;
      */
   	var ts=(t/=d)*t;
   	var tc=ts*t;
   	return b+c*(-2*tc + 3*ts);
   };
   
   this.backIn = function(t, b, c, d, s) {
   	var ts=(t/=d)*t;
   	var tc=ts*t;
   	return b+c*(-3.4005*tc*ts + 10.2*ts*ts + -6.2*tc + 0.4*ts);
      //var s = s || 1.70158;
      //return c*(t/=d)*t*((s+1)*t - s) + b;
   };
   
   this.backOut = function(t, b, c, d, s) {
   	var ts=(t/=d)*t;
   	var tc=ts*t;
   	return b+c*(8.292*tc*ts + -21.88*ts*ts + 22.08*tc + -12.69*ts + 5.1975*t);
      //var s = s || 1.70158;
      //return c*((t=t/d-1)*t*((s+1)*t + s) + 1) + b;
   };
   
   this.backBoth = function(t, b, c, d, s) {
   	var ts=(t/=d)*t;
   	var tc=ts*t;
   	return b+c*(0.402*tc*ts + -2.1525*ts*ts + -3.2*tc + 8*ts + -2.05*t);
      /*
      var s = s || 1.70158;
		if ((t/=d/2) < 1) return c/2*(t*t*(((s*=(1.525))+1)*t - s)) + b;
		return c/2*((t-=2)*t*(((s*=(1.525))+1)*t + s) + 2) + b;
      */
   };
   
   this.elasticIn = function(t, b, c, d, a, p) {
   	var ts=(t/=d)*t;
   	var tc=ts*t;
   	return b+c*(33*tc*ts + -59*ts*ts + 32*tc + -5*ts);
      /*
      if (t==0) return b;  if ((t/=d)==1) return b+c;  if (!p) p=d*.3;
      if (!a || a < Math.abs(c)) { a=c; var s=p/4; }
      else var s = p/(2*Math.PI) * Math.asin (c/a);
      return -(a*Math.pow(2,10*(t-=1)) * Math.sin( (t*d-s)*(2*Math.PI)/p )) + b;
      */
   };
   
   this.elasticOut = function(t, b, c, d, a, p) {
   	var ts=(t/=d)*t;
   	var tc=ts*t;
   	return b+c*(31.997*tc*ts + -101.99*ts*ts + 119.985*tc + -62.99*ts + 13.9975*t);
      
      /*
      if (t==0) return b;  if ((t/=d)==1) return b+c;  if (!p) p=d*.3;
      if (!a || a < Math.abs(c)) { a=c; var s=p/4; }
      else var s = p/(2*Math.PI) * Math.asin (c/a);
      return (a*Math.pow(2,-10*t) * Math.sin( (t*d-s)*(2*Math.PI)/p ) + c + b);
      */

   };
   
   // TODO: cant use
   this.elasticBoth = function(t, b, c, d, a, p) {
      var s;
      if (t===0) { return b; }
      if ((t/=d/2)==2) { return b+c; }
      if (!p) { p=d*(0.3*1.5); }
      if (!a || a < Math.abs(c)) { a=c; s=p/4; }
      else { s = p/(2*Math.PI) * Math.asin (c/a); }
      if (t < 1) {
         return -0.5*(a*Math.pow(2,10*(t-=1)) * Math.sin( (t*d-s)*(2*Math.PI)/p )) + b;
      }
      return a*Math.pow(2,-10*(t-=1)) * Math.sin( (t*d-s)*(2*Math.PI)/p )*0.5 + c + b;
   };
};

/**
 *
 * @class Used to calculate Bezier splines.
 *
 */
YAHOO.util.Bezier = new function() 
{
   /**
    * Get the current position of the animated element based on t.
    * @param {array} points An array containing Bezier points.
    * Each point is an array of "x" and "y" values (0 = x, 1 = y)
    * At least 2 points are required (start and end).
    * First point is start. Last point is end.
    * Additional control points are optional.    
    * @param {float} t Basis for determining current position (0 < t < 1)
    * @return {object} An object containing int x and y member data
    */
   this.getPosition = function(points, t)
   {  
      var n = points.length;
      var tmp = [];

      for (var i = 0; i < n; ++i){
         tmp[i] = [points[i][0], points[i][1]]; // save input
      }
      
      for (var j = 1; j < n; ++j) {
         for (i = 0; i < n - j; ++i) {
            tmp[i][0] = (1 - t) * tmp[i][0] + t * tmp[parseInt(i + 1, 10)][0];
            tmp[i][1] = (1 - t) * tmp[i][1] + t * tmp[parseInt(i + 1, 10)][1]; 
         }
      }
   
      return [ tmp[0][0], tmp[0][1] ]; 
   
   };
   
   this.curve = function(c, t) {
      var x, y;
      var n = c.length - 1;

      var b = new Array(c.length);      
      var u = t;
      
      b[0] = [];
      b[0][0] = c[0][0];
      b[0][1] = c[0][1];

      for (var i = 1; i <=n; i++) {

         b[i] = [];
         b[i][0] = c[i][0] * u;
         b[i][1] = c[i][1] * u;
         u = u * t;
      }
      
      x = b[n][0];  
      y = b[n][1];
      var t1 = 1 - t;
      var tt = t1;

      for (i = n - 1; i >= 0; i--) {
         x += b[i][0] * tt;
         y += b[i][1] * tt;
         tt = tt * t1;
      
      }
      //debug(x + ',' + y);
      return [x, y];
   };
   
   this.bezToCurve = function(begin, end, controls) {	
      var b = begin;
      var e = end;
   	var p = [];
      controls = controls || [];
      
      for (var i = 0, len = controls.length; i < len; ++i) {
         p[i] = [];
         
         if (i > 0) {
            //b = controls[i - 1];
            //e = controls[i + 1] || end;
         }
         
      	p[i][0] = (2 * controls[i][0]) - (b[0] + e[0]) / 2;
      	p[i][1] = (2 * controls[i][1]) - (b[1] + e[1]) / 2;	 
      }
   
   	return p;
   };
};

