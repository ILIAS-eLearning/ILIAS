/**
 * A slider with two thumbs, one that represents the min value and 
 * the other the max.  Actually a composition of two sliders, both with
 * the same background.  The constraints for each slider are adjusted
 * dynamically so that the min value of the max slider is equal or greater
 * to the current value of the min slider, and the max value of the min
 * slider is the current value of the max slider.
 * Constructor assumes both thumbs are positioned absolutely at the 0 mark on
 * the background.
 *
 * @namespace YAHOO.widget
 * @class DualSlider
 * @uses YAHOO.util.EventProvider
 * @constructor
 * @param {Slider} minSlider The Slider instance used for the min value thumb
 * @param {Slider} maxSlider The Slider instance used for the max value thumb
 * @param {int}    range The number of pixels the thumbs may move within
 * @param {Array}  initVals (optional) [min,max] Initial thumb placement
 */
(function () {

var Event = YAHOO.util.Event,
    YW = YAHOO.widget;

function DualSlider(minSlider, maxSlider, range, initVals) {

    var self  = this,
        ready = { min : false, max : false },
        minThumbOnMouseDown, maxThumbOnMouseDown;

    /**
     * A slider instance that keeps track of the lower value of the range.
     * <strong>read only</strong>
     * @property minSlider
     * @type Slider
     */
    this.minSlider = minSlider;

    /**
     * A slider instance that keeps track of the upper value of the range.
     * <strong>read only</strong>
     * @property maxSlider
     * @type Slider
     */
    this.maxSlider = maxSlider;

    /**
     * The currently active slider (min or max). <strong>read only</strong>
     * @property activeSlider
     * @type Slider
     */
    this.activeSlider = minSlider;

    /**
     * Is the DualSlider oriented horizontally or vertically?
     * <strong>read only</strong>
     * @property isHoriz
     * @type boolean
     */
    this.isHoriz = minSlider.thumb._isHoriz;

    //FIXME: this is horrible
    minThumbOnMouseDown = this.minSlider.thumb.onMouseDown;
    maxThumbOnMouseDown = this.maxSlider.thumb.onMouseDown;
    this.minSlider.thumb.onMouseDown = function() {
        self.activeSlider = self.minSlider;
        minThumbOnMouseDown.apply(this,arguments);
    };
    this.maxSlider.thumb.onMouseDown = function () {
        self.activeSlider = self.maxSlider;
        maxThumbOnMouseDown.apply(this,arguments);
    };

    this.minSlider.thumb.onAvailable = function () {
        minSlider.setStartSliderState();
        ready.min = true;
        if (ready.max) {
            self.fireEvent('ready',self);
        }
    };
    this.maxSlider.thumb.onAvailable = function () {
        maxSlider.setStartSliderState();
        ready.max = true;
        if (ready.min) {
            self.fireEvent('ready',self);
        }
    };

    // dispatch mousedowns to the active slider
    minSlider.onMouseDown =
    maxSlider.onMouseDown = function(e) {
        return this.backgroundEnabled && self._handleMouseDown(e);
    };

    // Fix the drag behavior so that only the active slider
    // follows the drag
    minSlider.onDrag =
    maxSlider.onDrag = function(e) {
        self._handleDrag(e);
    };

    // Likely only the minSlider's onMouseUp will be executed, but both are
    // overridden just to be safe
    minSlider.onMouseUp =
    maxSlider.onMouseUp = function (e) {
        self._handleMouseUp(e);
    };

    // Replace the _bindKeyEvents for the minSlider and remove that for the
    // maxSlider since they share the same bg element.
    minSlider._bindKeyEvents = function () {
        self._bindKeyEvents(this);
    };
    maxSlider._bindKeyEvents = function () {};

    // The core events for each slider are handled so we can expose a single
    // event for when the event happens on either slider
    minSlider.subscribe("change", this._handleMinChange, minSlider, this);
    minSlider.subscribe("slideStart", this._handleSlideStart, minSlider, this);
    minSlider.subscribe("slideEnd", this._handleSlideEnd, minSlider, this);

    maxSlider.subscribe("change", this._handleMaxChange, maxSlider, this);
    maxSlider.subscribe("slideStart", this._handleSlideStart, maxSlider, this);
    maxSlider.subscribe("slideEnd", this._handleSlideEnd, maxSlider, this);

    /**
     * Event that fires when the slider is finished setting up
     * @event ready
     * @param {DualSlider} dualslider the DualSlider instance
     */
    this.createEvent("ready", this);

    /**
     * Event that fires when either the min or max value changes
     * @event change
     * @param {DualSlider} dualslider the DualSlider instance
     */
    this.createEvent("change", this);

    /**
     * Event that fires when one of the thumbs begins to move
     * @event slideStart
     * @param {Slider} activeSlider the moving slider
     */
    this.createEvent("slideStart", this);

    /**
     * Event that fires when one of the thumbs finishes moving
     * @event slideEnd
     * @param {Slider} activeSlider the moving slider
     */
    this.createEvent("slideEnd", this);

    // Validate initial values
    initVals = YAHOO.lang.isArray(initVals) ? initVals : [0,range];
    initVals[0] = Math.min(Math.max(parseInt(initVals[0],10)|0,0),range);
    initVals[1] = Math.max(Math.min(parseInt(initVals[1],10)|0,range),0);
    // Swap initVals if min > max
    if (initVals[0] > initVals[1]) {
        initVals.splice(0,2,initVals[1],initVals[0]);
    }
    this.minVal = initVals[0];
    this.maxVal = initVals[1];

    // Set values so initial assignment when the slider thumbs are ready will
    // use these values
    this.minSlider.setValue(this.minVal,true,true,true);
    this.maxSlider.setValue(this.maxVal,true,true,true);

    YAHOO.log("Setting initial values " + this.minVal + ", " + this.maxVal,"info","DualSlider");
}

DualSlider.prototype = {

    /**
     * The current value of the min thumb. <strong>read only</strong>.
     * @property minVal
     * @type int
     */
    minVal : -1,

    /**
     * The current value of the max thumb. <strong>read only</strong>.
     * @property maxVal
     * @type int
     */
    maxVal : -1,

    /**
     * Pixel distance to maintain between thumbs.
     * @property minRange
     * @type int
     * @default 0
     */
    minRange : 0,

    /**
     * Executed when one of the sliders fires the slideStart event
     * @method _handleSlideStart
     * @private
     */
    _handleSlideStart: function(data, slider) {
        this.fireEvent("slideStart", slider);
    },

    /**
     * Executed when one of the sliders fires the slideEnd event
     * @method _handleSlideEnd
     * @private
     */
    _handleSlideEnd: function(data, slider) {
        this.fireEvent("slideEnd", slider);
    },

    /**
     * Overrides the onDrag method for both sliders
     * @method _handleDrag
     * @private
     */
    _handleDrag: function(e) {
        YW.Slider.prototype.onDrag.call(this.activeSlider, e);
    },

    /**
     * Executed when the min slider fires the change event
     * @method _handleMinChange
     * @private
     */
    _handleMinChange: function() {
        this.activeSlider = this.minSlider;
        this.updateValue();
    },

    /**
     * Executed when the max slider fires the change event
     * @method _handleMaxChange
     * @private
     */
    _handleMaxChange: function() {
        this.activeSlider = this.maxSlider;
        this.updateValue();
    },

    /**
     * Set up the listeners for the keydown and keypress events.
     *
     * @method _bindKeyEvents
     * @protected
     */
    _bindKeyEvents : function (slider) {
        Event.on(slider.id,'keydown', this._handleKeyDown, this,true);
        Event.on(slider.id,'keypress',this._handleKeyPress,this,true);
    },

    /**
     * Delegate event handling to the active Slider.  See Slider.handleKeyDown.
     *
     * @method _handleKeyDown
     * @param e {Event} the mousedown DOM event
     * @protected
     */
    _handleKeyDown : function (e) {
        this.activeSlider.handleKeyDown.apply(this.activeSlider,arguments);
    },

    /**
     * Delegate event handling to the active Slider.  See Slider.handleKeyPress.
     *
     * @method _handleKeyPress
     * @param e {Event} the mousedown DOM event
     * @protected
     */
    _handleKeyPress : function (e) {
        this.activeSlider.handleKeyPress.apply(this.activeSlider,arguments);
    },

    /**
     * Sets the min and max thumbs to new values.
     * @method setValues
     * @param min {int} Pixel offset to assign to the min thumb
     * @param max {int} Pixel offset to assign to the max thumb
     * @param skipAnim {boolean} (optional) Set to true to skip thumb animation.
     * Default false
     * @param force {boolean} (optional) ignore the locked setting and set
     * value anyway. Default false
     * @param silent {boolean} (optional) Set to true to skip firing change
     * events.  Default false
     */
    setValues : function (min, max, skipAnim, force, silent) {
        var mins = this.minSlider,
            maxs = this.maxSlider,
            mint = mins.thumb,
            maxt = maxs.thumb,
            self = this,
            done = { min : false, max : false };

        // Clear constraints to prevent animated thumbs from prematurely
        // stopping when hitting a constraint that's moving with the other
        // thumb.
        if (mint._isHoriz) {
            mint.setXConstraint(mint.leftConstraint,maxt.rightConstraint,mint.tickSize);
            maxt.setXConstraint(mint.leftConstraint,maxt.rightConstraint,maxt.tickSize);
        } else {
            mint.setYConstraint(mint.topConstraint,maxt.bottomConstraint,mint.tickSize);
            maxt.setYConstraint(mint.topConstraint,maxt.bottomConstraint,maxt.tickSize);
        }

        // Set up one-time slideEnd callbacks to call updateValue when both
        // thumbs have been set
        this._oneTimeCallback(mins,'slideEnd',function () {
            done.min = true;
            if (done.max) {
                self.updateValue(silent);
                // Clean the slider's slideEnd events on a timeout since this
                // will be executed from inside the event's fire
                setTimeout(function () {
                    self._cleanEvent(mins,'slideEnd');
                    self._cleanEvent(maxs,'slideEnd');
                },0);
            }
        });

        this._oneTimeCallback(maxs,'slideEnd',function () {
            done.max = true;
            if (done.min) {
                self.updateValue(silent);
                // Clean both sliders' slideEnd events on a timeout since this
                // will be executed from inside one of the event's fire
                setTimeout(function () {
                    self._cleanEvent(mins,'slideEnd');
                    self._cleanEvent(maxs,'slideEnd');
                },0);
            }
        });

        // Must emit Slider slideEnd event to propagate to updateValue
        mins.setValue(min,skipAnim,force,false);
        maxs.setValue(max,skipAnim,force,false);
    },

    /**
     * Set the min thumb position to a new value.
     * @method setMinValue
     * @param min {int} Pixel offset for min thumb
     * @param skipAnim {boolean} (optional) Set to true to skip thumb animation.
     * Default false
     * @param force {boolean} (optional) ignore the locked setting and set
     * value anyway. Default false
     * @param silent {boolean} (optional) Set to true to skip firing change
     * events.  Default false
     */
    setMinValue : function (min, skipAnim, force, silent) {
        var mins = this.minSlider,
            self = this;

        this.activeSlider = mins;

        // Use a one-time event callback to delay the updateValue call
        // until after the slide operation is done
        self = this;
        this._oneTimeCallback(mins,'slideEnd',function () {
            self.updateValue(silent);
            // Clean the slideEnd event on a timeout since this
            // will be executed from inside the event's fire
            setTimeout(function () { self._cleanEvent(mins,'slideEnd'); }, 0);
        });

        mins.setValue(min, skipAnim, force);
    },

    /**
     * Set the max thumb position to a new value.
     * @method setMaxValue
     * @param max {int} Pixel offset for max thumb
     * @param skipAnim {boolean} (optional) Set to true to skip thumb animation.
     * Default false
     * @param force {boolean} (optional) ignore the locked setting and set
     * value anyway. Default false
     * @param silent {boolean} (optional) Set to true to skip firing change
     * events.  Default false
     */
    setMaxValue : function (max, skipAnim, force, silent) {
        var maxs = this.maxSlider,
            self = this;

        this.activeSlider = maxs;

        // Use a one-time event callback to delay the updateValue call
        // until after the slide operation is done
        this._oneTimeCallback(maxs,'slideEnd',function () {
            self.updateValue(silent);
            // Clean the slideEnd event on a timeout since this
            // will be executed from inside the event's fire
            setTimeout(function () { self._cleanEvent(maxs,'slideEnd'); }, 0);
        });

        maxs.setValue(max, skipAnim, force);
    },

    /**
     * Executed when one of the sliders is moved
     * @method updateValue
     * @param silent {boolean} (optional) Set to true to skip firing change
     * events.  Default false
     * @private
     */
    updateValue: function(silent) {
        var min     = this.minSlider.getValue(),
            max     = this.maxSlider.getValue(),
            changed = false,
            mint,maxt,dim,minConstraint,maxConstraint,thumbInnerWidth;

        if (min != this.minVal || max != this.maxVal) {
            changed = true;

            mint = this.minSlider.thumb;
            maxt = this.maxSlider.thumb;
            dim  = this.isHoriz ? 'x' : 'y';

            thumbInnerWidth = this.minSlider.thumbCenterPoint[dim] +
                              this.maxSlider.thumbCenterPoint[dim];

            // Establish barriers within the respective other thumb's edge, less
            // the minRange.  Limit to the Slider's range in the case of
            // negative minRanges.
            minConstraint = Math.max(max-thumbInnerWidth-this.minRange,0);
            maxConstraint = Math.min(-min-thumbInnerWidth-this.minRange,0);

            if (this.isHoriz) {
                minConstraint = Math.min(minConstraint,maxt.rightConstraint);

                mint.setXConstraint(mint.leftConstraint,minConstraint, mint.tickSize);

                maxt.setXConstraint(maxConstraint,maxt.rightConstraint, maxt.tickSize);
            } else {
                minConstraint = Math.min(minConstraint,maxt.bottomConstraint);
                mint.setYConstraint(mint.leftConstraint,minConstraint, mint.tickSize);

                maxt.setYConstraint(maxConstraint,maxt.bottomConstraint, maxt.tickSize);
            }
        }

        this.minVal = min;
        this.maxVal = max;

        if (changed && !silent) {
            this.fireEvent("change", this);
        }
    },

    /**
     * A background click will move the slider thumb nearest to the click.
     * Override if you need different behavior.
     * @method selectActiveSlider
     * @param e {Event} the mousedown event
     * @private
     */
    selectActiveSlider: function(e) {
        var min = this.minSlider,
            max = this.maxSlider,
            minLocked = min.isLocked() || !min.backgroundEnabled,
            maxLocked = max.isLocked() || !min.backgroundEnabled,
            Ev  = YAHOO.util.Event,
            d;

        if (minLocked || maxLocked) {
            this.activeSlider = minLocked ? max : min;
        } else {
            if (this.isHoriz) {
                d = Ev.getPageX(e)-min.thumb.initPageX-min.thumbCenterPoint.x;
            } else {
                d = Ev.getPageY(e)-min.thumb.initPageY-min.thumbCenterPoint.y;
            }
                    
            this.activeSlider = d*2 > max.getValue()+min.getValue() ? max : min;
        }
    },

    /**
     * Delegates the onMouseDown to the appropriate Slider
     *
     * @method _handleMouseDown
     * @param e {Event} mouseup event
     * @protected
     */
    _handleMouseDown: function(e) {
        if (!e._handled && !this.minSlider._sliding && !this.maxSlider._sliding) {
            e._handled = true;
            this.selectActiveSlider(e);
            return YW.Slider.prototype.onMouseDown.call(this.activeSlider, e);
        } else {
            return false;
        }
    },

    /**
     * Delegates the onMouseUp to the active Slider
     *
     * @method _handleMouseUp
     * @param e {Event} mouseup event
     * @protected
     */
    _handleMouseUp : function (e) {
        YW.Slider.prototype.onMouseUp.apply(
            this.activeSlider, arguments);
    },

    /**
     * Schedule an event callback that will execute once, then unsubscribe
     * itself.
     * @method _oneTimeCallback
     * @param o {EventProvider} Object to attach the event to
     * @param evt {string} Name of the event
     * @param fn {Function} function to execute once
     * @private
     */
    _oneTimeCallback : function (o,evt,fn) {
        var sub = function () {
            // Unsubscribe myself
            o.unsubscribe(evt, sub);
            // Pass the event handler arguments to the one time callback
            fn.apply({},arguments);
        };
        o.subscribe(evt,sub);
    },

    /**
     * Clean up the slideEnd event subscribers array, since each one-time
     * callback will be replaced in the event's subscribers property with
     * null.  This will cause memory bloat and loss of performance.
     * @method _cleanEvent
     * @param o {EventProvider} object housing the CustomEvent
     * @param evt {string} name of the CustomEvent
     * @private
     */
    _cleanEvent : function (o,evt) {
        var ce,i,len,j,subs,newSubs;

        if (o.__yui_events && o.events[evt]) {
            for (i = o.__yui_events.length; i >= 0; --i) {
                if (o.__yui_events[i].type === evt) {
                    ce = o.__yui_events[i];
                    break;
                }
            }
            if (ce) {
                subs    = ce.subscribers;
                newSubs = [];
                j = 0;
                for (i = 0, len = subs.length; i < len; ++i) {
                    if (subs[i]) {
                        newSubs[j++] = subs[i];
                    }
                }
                ce.subscribers = newSubs;
            }
        }
    }

};

YAHOO.lang.augmentProto(DualSlider, YAHOO.util.EventProvider);


/**
 * Factory method for creating a horizontal dual-thumb slider
 * @for YAHOO.widget.Slider
 * @method YAHOO.widget.Slider.getHorizDualSlider
 * @static
 * @param {String} bg the id of the slider's background element
 * @param {String} minthumb the id of the min thumb
 * @param {String} maxthumb the id of the thumb thumb
 * @param {int} range the number of pixels the thumbs can move within
 * @param {int} iTickSize (optional) the element should move this many pixels
 * at a time
 * @param {Array}  initVals (optional) [min,max] Initial thumb placement
 * @return {DualSlider} a horizontal dual-thumb slider control
 */
YW.Slider.getHorizDualSlider = 
    function (bg, minthumb, maxthumb, range, iTickSize, initVals) {
        var mint = new YW.SliderThumb(minthumb, bg, 0, range, 0, 0, iTickSize),
            maxt = new YW.SliderThumb(maxthumb, bg, 0, range, 0, 0, iTickSize);

        return new DualSlider(
                    new YW.Slider(bg, bg, mint, "horiz"),
                    new YW.Slider(bg, bg, maxt, "horiz"),
                    range, initVals);
};

/**
 * Factory method for creating a vertical dual-thumb slider.
 * @for YAHOO.widget.Slider
 * @method YAHOO.widget.Slider.getVertDualSlider
 * @static
 * @param {String} bg the id of the slider's background element
 * @param {String} minthumb the id of the min thumb
 * @param {String} maxthumb the id of the thumb thumb
 * @param {int} range the number of pixels the thumbs can move within
 * @param {int} iTickSize (optional) the element should move this many pixels
 * at a time
 * @param {Array}  initVals (optional) [min,max] Initial thumb placement
 * @return {DualSlider} a vertical dual-thumb slider control
 */
YW.Slider.getVertDualSlider = 
    function (bg, minthumb, maxthumb, range, iTickSize, initVals) {
        var mint = new YW.SliderThumb(minthumb, bg, 0, 0, 0, range, iTickSize),
            maxt = new YW.SliderThumb(maxthumb, bg, 0, 0, 0, range, iTickSize);

        return new YW.DualSlider(
                    new YW.Slider(bg, bg, mint, "vert"),
                    new YW.Slider(bg, bg, maxt, "vert"),
                    range, initVals);
};

YAHOO.widget.DualSlider = DualSlider;

})();
