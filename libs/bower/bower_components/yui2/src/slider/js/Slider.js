/**
 * The Slider component is a UI control that enables the user to adjust 
 * values in a finite range along one or two axes. Typically, the Slider 
 * control is used in a web application as a rich, visual replacement 
 * for an input box that takes a number as input. The Slider control can 
 * also easily accommodate a second dimension, providing x,y output for 
 * a selection point chosen from a rectangular region.
 *
 * @module    slider
 * @title     Slider Widget
 * @namespace YAHOO.widget
 * @requires  yahoo,dom,dragdrop,event
 * @optional  animation
 */
 (function () {

var getXY = YAHOO.util.Dom.getXY,
    Event = YAHOO.util.Event,
    _AS   = Array.prototype.slice;

/**
 * A DragDrop implementation that can be used as a background for a
 * slider.  It takes a reference to the thumb instance 
 * so it can delegate some of the events to it.  The goal is to make the 
 * thumb jump to the location on the background when the background is 
 * clicked.  
 *
 * @class Slider
 * @extends YAHOO.util.DragDrop
 * @uses YAHOO.util.EventProvider
 * @constructor
 * @param {String}      id     The id of the element linked to this instance
 * @param {String}      sGroup The group of related DragDrop items
 * @param {SliderThumb} oThumb The thumb for this slider
 * @param {String}      sType  The type of slider (horiz, vert, region)
 */
function Slider(sElementId, sGroup, oThumb, sType) {

    Slider.ANIM_AVAIL = (!YAHOO.lang.isUndefined(YAHOO.util.Anim));

    if (sElementId) {
        this.init(sElementId, sGroup, true);
        this.initSlider(sType);
        this.initThumb(oThumb);
    }
}

YAHOO.lang.augmentObject(Slider,{
    /**
     * Factory method for creating a horizontal slider
     * @method YAHOO.widget.Slider.getHorizSlider
     * @static
     * @param {String} sBGElId the id of the slider's background element
     * @param {String} sHandleElId the id of the thumb element
     * @param {int} iLeft the number of pixels the element can move left
     * @param {int} iRight the number of pixels the element can move right
     * @param {int} iTickSize optional parameter for specifying that the element 
     * should move a certain number pixels at a time.
     * @return {Slider} a horizontal slider control
     */
    getHorizSlider : 
        function (sBGElId, sHandleElId, iLeft, iRight, iTickSize) {
            return new Slider(sBGElId, sBGElId, 
                new YAHOO.widget.SliderThumb(sHandleElId, sBGElId, 
                                   iLeft, iRight, 0, 0, iTickSize), "horiz");
    },

    /**
     * Factory method for creating a vertical slider
     * @method YAHOO.widget.Slider.getVertSlider
     * @static
     * @param {String} sBGElId the id of the slider's background element
     * @param {String} sHandleElId the id of the thumb element
     * @param {int} iUp the number of pixels the element can move up
     * @param {int} iDown the number of pixels the element can move down
     * @param {int} iTickSize optional parameter for specifying that the element 
     * should move a certain number pixels at a time.
     * @return {Slider} a vertical slider control
     */
    getVertSlider :
        function (sBGElId, sHandleElId, iUp, iDown, iTickSize) {
            return new Slider(sBGElId, sBGElId, 
                new YAHOO.widget.SliderThumb(sHandleElId, sBGElId, 0, 0, 
                                   iUp, iDown, iTickSize), "vert");
    },

    /**
     * Factory method for creating a slider region like the one in the color
     * picker example
     * @method YAHOO.widget.Slider.getSliderRegion
     * @static
     * @param {String} sBGElId the id of the slider's background element
     * @param {String} sHandleElId the id of the thumb element
     * @param {int} iLeft the number of pixels the element can move left
     * @param {int} iRight the number of pixels the element can move right
     * @param {int} iUp the number of pixels the element can move up
     * @param {int} iDown the number of pixels the element can move down
     * @param {int} iTickSize optional parameter for specifying that the element 
     * should move a certain number pixels at a time.
     * @return {Slider} a slider region control
     */
    getSliderRegion : 
        function (sBGElId, sHandleElId, iLeft, iRight, iUp, iDown, iTickSize) {
            return new Slider(sBGElId, sBGElId, 
                new YAHOO.widget.SliderThumb(sHandleElId, sBGElId, iLeft, iRight, 
                                   iUp, iDown, iTickSize), "region");
    },

    /**
     * Constant for valueChangeSource, indicating that the user clicked or
     * dragged the slider to change the value.
     * @property Slider.SOURCE_UI_EVENT
     * @final
     * @static
     * @default 1
     */
    SOURCE_UI_EVENT : 1,

    /**
     * Constant for valueChangeSource, indicating that the value was altered
     * by a programmatic call to setValue/setRegionValue.
     * @property Slider.SOURCE_SET_VALUE
     * @final
     * @static
     * @default 2
     */
    SOURCE_SET_VALUE : 2,

    /**
     * Constant for valueChangeSource, indicating that the value was altered
     * by hitting any of the supported keyboard characters.
     * @property Slider.SOURCE_KEY_EVENT
     * @final
     * @static
     * @default 2
     */
    SOURCE_KEY_EVENT : 3,

    /**
     * By default, animation is available if the animation utility is detected.
     * @property Slider.ANIM_AVAIL
     * @static
     * @type boolean
     */
    ANIM_AVAIL : false
},true);

YAHOO.extend(Slider, YAHOO.util.DragDrop, {

    /**
     * Tracks the state of the mouse button to aid in when events are fired.
     *
     * @property _mouseDown
     * @type boolean
     * @default false
     * @private
     */
    _mouseDown : false,

    /**
     * Override the default setting of dragOnly to true.
     * @property dragOnly
     * @type boolean
     * @default true
     */
    dragOnly : true,

    /**
     * Initializes the slider.  Executed in the constructor
     * @method initSlider
     * @param {string} sType the type of slider (horiz, vert, region)
     */
    initSlider: function(sType) {

        /**
         * The type of the slider (horiz, vert, region)
         * @property type
         * @type string
         */
        this.type = sType;

        //this.removeInvalidHandleType("A");

        this.logger = new YAHOO.widget.LogWriter(this.toString());

        /**
         * Event the fires when the value of the control changes.  If 
         * the control is animated the event will fire every point
         * along the way.
         * @event change
         * @param {int} newOffset|x the new offset for normal sliders, or the new
         *                          x offset for region sliders
         * @param {int} y the number of pixels the thumb has moved on the y axis
         *                (region sliders only)
         */
        this.createEvent("change", this);

        /**
         * Event that fires at the beginning of a slider thumb move.
         * @event slideStart
         */
        this.createEvent("slideStart", this);

        /**
         * Event that fires at the end of a slider thumb move
         * @event slideEnd
         */
        this.createEvent("slideEnd", this);

        /**
         * Overrides the isTarget property in YAHOO.util.DragDrop
         * @property isTarget
         * @private
         */
        this.isTarget = false;
    
        /**
         * Flag that determines if the thumb will animate when moved
         * @property animate
         * @type boolean
         */
        this.animate = Slider.ANIM_AVAIL;

        /**
         * Set to false to disable a background click thumb move
         * @property backgroundEnabled
         * @type boolean
         */
        this.backgroundEnabled = true;

        /**
         * Adjustment factor for tick animation, the more ticks, the
         * faster the animation (by default)
         * @property tickPause
         * @type int
         */
        this.tickPause = 40;

        /**
         * Enables the arrow, home and end keys, defaults to true.
         * @property enableKeys
         * @type boolean
         */
        this.enableKeys = true;

        /**
         * Specifies the number of pixels the arrow keys will move the slider.
         * Default is 20.
         * @property keyIncrement
         * @type int
         */
        this.keyIncrement = 20;

        /**
         * moveComplete is set to true when the slider has moved to its final
         * destination.  For animated slider, this value can be checked in 
         * the onChange handler to make it possible to execute logic only
         * when the move is complete rather than at all points along the way.
         * Deprecated because this flag is only useful when the background is
         * clicked and the slider is animated.  If the user drags the thumb,
         * the flag is updated when the drag is over ... the final onDrag event
         * fires before the mouseup the ends the drag, so the implementer will
         * never see it.
         *
         * @property moveComplete
         * @type Boolean
         * @deprecated use the slideEnd event instead
         */
        this.moveComplete = true;

        /**
         * If animation is configured, specifies the length of the animation
         * in seconds.
         * @property animationDuration
         * @type int
         * @default 0.2
         */
        this.animationDuration = 0.2;

        /**
         * Constant for valueChangeSource, indicating that the user clicked or
         * dragged the slider to change the value.
         * @property SOURCE_UI_EVENT
         * @final
         * @default 1
         * @deprecated use static Slider.SOURCE_UI_EVENT
         */
        this.SOURCE_UI_EVENT = 1;

        /**
         * Constant for valueChangeSource, indicating that the value was altered
         * by a programmatic call to setValue/setRegionValue.
         * @property SOURCE_SET_VALUE
         * @final
         * @default 2
         * @deprecated use static Slider.SOURCE_SET_VALUE
         */
        this.SOURCE_SET_VALUE = 2;

        /**
         * When the slider value changes, this property is set to identify where
         * the update came from.  This will be either 1, meaning the slider was
         * clicked or dragged, or 2, meaning that it was set via a setValue() call.
         * This can be used within event handlers to apply some of the logic only
         * when dealing with one source or another.
         * @property valueChangeSource
         * @type int
         * @since 2.3.0
         */
        this.valueChangeSource = 0;

        /**
         * Indicates whether or not events will be supressed for the current
         * slide operation
         * @property _silent
         * @type boolean
         * @private
         */
        this._silent = false;

        /**
         * Saved offset used to protect against NaN problems when slider is
         * set to display:none
         * @property lastOffset
         * @type [int, int]
         */
        this.lastOffset = [0,0];
    },

    /**
     * Initializes the slider's thumb. Executed in the constructor.
     * @method initThumb
     * @param {YAHOO.widget.SliderThumb} t the slider thumb
     */
    initThumb: function(t) {

        var self = this;

        /**
         * A YAHOO.widget.SliderThumb instance that we will use to 
         * reposition the thumb when the background is clicked
         * @property thumb
         * @type YAHOO.widget.SliderThumb
         */
        this.thumb = t;

        t.cacheBetweenDrags = true;

        if (t._isHoriz && t.xTicks && t.xTicks.length) {
            this.tickPause = Math.round(360 / t.xTicks.length);
        } else if (t.yTicks && t.yTicks.length) {
            this.tickPause = Math.round(360 / t.yTicks.length);
        }

        this.logger.log("tickPause: " + this.tickPause);

        // delegate thumb methods
        t.onAvailable = function() { 
                return self.setStartSliderState(); 
            };
        t.onMouseDown = function () { 
                self._mouseDown = true;
                self.logger.log('thumb mousedown');
                return self.focus(); 
            };
        t.startDrag = function() { 
                self.logger.log('thumb startDrag');
                self._slideStart(); 
            };
        t.onDrag = function() { 
                self.logger.log('thumb drag');
                self.fireEvents(true); 
            };
        t.onMouseUp = function() { 
                self.thumbMouseUp(); 
            };

    },

    /**
     * Executed when the slider element is available
     * @method onAvailable
     */
    onAvailable: function() {
        this._bindKeyEvents();
    },
 
    /**
     * Sets up the listeners for keydown and key press events.
     *
     * @method _bindKeyEvents
     * @protected
     */
    _bindKeyEvents : function () {
        Event.on(this.id, "keydown",  this.handleKeyDown,  this, true);
        Event.on(this.id, "keypress", this.handleKeyPress, this, true);
    },

    /**
     * Executed when a keypress event happens with the control focused.
     * Prevents the default behavior for navigation keys.  The actual
     * logic for moving the slider thumb in response to a key event
     * happens in handleKeyDown.
     * @param {Event} e the keypress event
     */
    handleKeyPress: function(e) {
        if (this.enableKeys) {
            var kc = Event.getCharCode(e);

            switch (kc) {
                case 0x25: // left
                case 0x26: // up
                case 0x27: // right
                case 0x28: // down
                case 0x24: // home
                case 0x23: // end
                    Event.preventDefault(e);
                    break;
                default:
            }
        }
    },

    /**
     * Executed when a keydown event happens with the control focused.
     * Updates the slider value and display when the keypress is an
     * arrow key, home, or end as long as enableKeys is set to true.
     * @param {Event} e the keydown event
     */
    handleKeyDown: function(e) {
        if (this.enableKeys) {
            var kc = Event.getCharCode(e),
                t  = this.thumb,
                h  = this.getXValue(),
                v  = this.getYValue(),
                changeValue = true;

            switch (kc) {

                // left
                case 0x25: h -= this.keyIncrement; break;

                // up
                case 0x26: v -= this.keyIncrement; break;

                // right
                case 0x27: h += this.keyIncrement; break;

                // down
                case 0x28: v += this.keyIncrement; break;

                // home
                case 0x24: h = t.leftConstraint;    
                           v = t.topConstraint;    
                           break;

                // end
                case 0x23: h = t.rightConstraint; 
                           v = t.bottomConstraint;    
                           break;

                default:   changeValue = false;
            }

            if (changeValue) {
                if (t._isRegion) {
                    this._setRegionValue(Slider.SOURCE_KEY_EVENT, h, v, true);
                } else {
                    this._setValue(Slider.SOURCE_KEY_EVENT,
                        (t._isHoriz ? h : v), true);
                }
                Event.stopEvent(e);
            }

        }
    },

    /**
     * Initialization that sets up the value offsets once the elements are ready
     * @method setStartSliderState
     */
    setStartSliderState: function() {

        this.logger.log("Fixing state");

        this.setThumbCenterPoint();

        /**
         * The basline position of the background element, used
         * to determine if the background has moved since the last
         * operation.
         * @property baselinePos
         * @type [int, int]
         */
        this.baselinePos = getXY(this.getEl());

        this.thumb.startOffset = this.thumb.getOffsetFromParent(this.baselinePos);

        if (this.thumb._isRegion) {
            if (this.deferredSetRegionValue) {
                this._setRegionValue.apply(this, this.deferredSetRegionValue);
                this.deferredSetRegionValue = null;
            } else {
                this.setRegionValue(0, 0, true, true, true);
            }
        } else {
            if (this.deferredSetValue) {
                this._setValue.apply(this, this.deferredSetValue);
                this.deferredSetValue = null;
            } else {
                this.setValue(0, true, true, true);
            }
        }
    },

    /**
     * When the thumb is available, we cache the centerpoint of the element so
     * we can position the element correctly when the background is clicked
     * @method setThumbCenterPoint
     */
    setThumbCenterPoint: function() {

        var el = this.thumb.getEl();

        if (el) {
            /**
             * The center of the slider element is stored so we can 
             * place it in the correct position when the background is clicked.
             * @property thumbCenterPoint
             * @type {"x": int, "y": int}
             */
            this.thumbCenterPoint = { 
                    x: parseInt(el.offsetWidth/2, 10), 
                    y: parseInt(el.offsetHeight/2, 10) 
            };
        }

    },

    /**
     * Locks the slider, overrides YAHOO.util.DragDrop
     * @method lock
     */
    lock: function() {
        this.logger.log("locking");
        this.thumb.lock();
        this.locked = true;
    },

    /**
     * Unlocks the slider, overrides YAHOO.util.DragDrop
     * @method unlock
     */
    unlock: function() {
        this.logger.log("unlocking");
        this.thumb.unlock();
        this.locked = false;
    },

    /**
     * Handles mouseup event on the thumb
     * @method thumbMouseUp
     * @private
     */
    thumbMouseUp: function() {
        this._mouseDown = false;
        this.logger.log("thumb mouseup");
        if (!this.isLocked()) {
            this.endMove();
        }

    },

    onMouseUp: function() {
        this._mouseDown = false;
        this.logger.log("background mouseup");
        if (this.backgroundEnabled && !this.isLocked()) {
            this.endMove();
        }
    },

    /**
     * Returns a reference to this slider's thumb
     * @method getThumb
     * @return {SliderThumb} this slider's thumb
     */
    getThumb: function() {
        return this.thumb;
    },

    /**
     * Try to focus the element when clicked so we can add
     * accessibility features
     * @method focus
     * @private
     */
    focus: function() {
        this.logger.log("focus");
        this.valueChangeSource = Slider.SOURCE_UI_EVENT;

        // Focus the background element if possible
        var el = this.getEl();

        if (el.focus) {
            try {
                el.focus();
            } catch(e) {
                // Prevent permission denied unhandled exception in FF that can
                // happen when setting focus while another element is handling
                // the blur.  @TODO this is still writing to the error log 
                // (unhandled error) in FF1.5 with strict error checking on.
            }
        }

        this.verifyOffset();

        return !this.isLocked();
    },

    /**
     * Event that fires when the value of the slider has changed
     * @method onChange
     * @param {int} firstOffset the number of pixels the thumb has moved
     * from its start position. Normal horizontal and vertical sliders will only
     * have the firstOffset.  Regions will have both, the first is the horizontal
     * offset, the second the vertical.
     * @param {int} secondOffset the y offset for region sliders
     * @deprecated use instance.subscribe("change") instead
     */
    onChange: function (firstOffset, secondOffset) { 
        /* override me */ 
        this.logger.log("onChange: " + firstOffset + ", " + secondOffset);
    },

    /**
     * Event that fires when the at the beginning of the slider thumb move
     * @method onSlideStart
     * @deprecated use instance.subscribe("slideStart") instead
     */
    onSlideStart: function () { 
        /* override me */ 
        this.logger.log("onSlideStart");
    },

    /**
     * Event that fires at the end of a slider thumb move
     * @method onSliderEnd
     * @deprecated use instance.subscribe("slideEnd") instead
     */
    onSlideEnd: function () { 
        /* override me */ 
        this.logger.log("onSlideEnd");
    },

    /**
     * Returns the slider's thumb offset from the start position
     * @method getValue
     * @return {int} the current value
     */
    getValue: function () { 
        return this.thumb.getValue();
    },

    /**
     * Returns the slider's thumb X offset from the start position
     * @method getXValue
     * @return {int} the current horizontal offset
     */
    getXValue: function () { 
        return this.thumb.getXValue();
    },

    /**
     * Returns the slider's thumb Y offset from the start position
     * @method getYValue
     * @return {int} the current vertical offset
     */
    getYValue: function () { 
        return this.thumb.getYValue();
    },

    /**
     * Provides a way to set the value of the slider in code.
     *
     * @method setValue
     * @param {int} newOffset the number of pixels the thumb should be
     * positioned away from the initial start point 
     * @param {boolean} skipAnim set to true to disable the animation
     * for this move action (but not others).
     * @param {boolean} force ignore the locked setting and set value anyway
     * @param {boolean} silent when true, do not fire events
     * @return {boolean} true if the move was performed, false if it failed
     */
    setValue: function() {
        var args = _AS.call(arguments);
        args.unshift(Slider.SOURCE_SET_VALUE);
        return this._setValue.apply(this,args);
    },

    /**
     * Worker function to execute the value set operation.  Accepts type of
     * set operation in addition to the usual setValue params.
     *
     * @method _setValue
     * @param source {int} what triggered the set (e.g. Slider.SOURCE_SET_VALUE)
     * @param {int} newOffset the number of pixels the thumb should be
     * positioned away from the initial start point 
     * @param {boolean} skipAnim set to true to disable the animation
     * for this move action (but not others).
     * @param {boolean} force ignore the locked setting and set value anyway
     * @param {boolean} silent when true, do not fire events
     * @return {boolean} true if the move was performed, false if it failed
     * @protected
     */
    _setValue: function(source, newOffset, skipAnim, force, silent) {
        var t = this.thumb, newX, newY;

        if (!t.available) {
            this.logger.log("defer setValue until after onAvailble");
            this.deferredSetValue = arguments;
            return false;
        }

        if (this.isLocked() && !force) {
            this.logger.log("Can't set the value, the control is locked");
            return false;
        }

        if ( isNaN(newOffset) ) {
            this.logger.log("setValue, Illegal argument: " + newOffset);
            return false;
        }

        if (t._isRegion) {
            this.logger.log("Call to setValue for region Slider ignored. Use setRegionValue","warn");
            return false;
        }

        this.logger.log("setValue " + newOffset);

        this._silent = silent;
        this.valueChangeSource = source || Slider.SOURCE_SET_VALUE;

        t.lastOffset = [newOffset, newOffset];
        this.verifyOffset();

        this._slideStart();

        if (t._isHoriz) {
            newX = t.initPageX + newOffset + this.thumbCenterPoint.x;
            this.moveThumb(newX, t.initPageY, skipAnim);
        } else {
            newY = t.initPageY + newOffset + this.thumbCenterPoint.y;
            this.moveThumb(t.initPageX, newY, skipAnim);
        }

        return true;
    },

    /**
     * Provides a way to set the value of the region slider in code.
     * @method setRegionValue
     * @param {int} newOffset the number of pixels the thumb should be
     * positioned away from the initial start point (x axis for region)
     * @param {int} newOffset2 the number of pixels the thumb should be
     * positioned away from the initial start point (y axis for region)
     * @param {boolean} skipAnim set to true to disable the animation
     * for this move action (but not others).
     * @param {boolean} force ignore the locked setting and set value anyway
     * @param {boolean} silent when true, do not fire events
     * @return {boolean} true if the move was performed, false if it failed
     */
    setRegionValue : function () {
        var args = _AS.call(arguments);
        args.unshift(Slider.SOURCE_SET_VALUE);
        return this._setRegionValue.apply(this,args);
    },

    /**
     * Worker function to execute the value set operation.  Accepts type of
     * set operation in addition to the usual setValue params.
     *
     * @method _setRegionValue
     * @param source {int} what triggered the set (e.g. Slider.SOURCE_SET_VALUE)
     * @param {int} newOffset the number of pixels the thumb should be
     * positioned away from the initial start point (x axis for region)
     * @param {int} newOffset2 the number of pixels the thumb should be
     * positioned away from the initial start point (y axis for region)
     * @param {boolean} skipAnim set to true to disable the animation
     * for this move action (but not others).
     * @param {boolean} force ignore the locked setting and set value anyway
     * @param {boolean} silent when true, do not fire events
     * @return {boolean} true if the move was performed, false if it failed
     * @protected
     */
    _setRegionValue: function(source, newOffset, newOffset2, skipAnim, force, silent) {
        var t = this.thumb, newX, newY;

        if (!t.available) {
            this.logger.log("defer setRegionValue until after onAvailble");
            this.deferredSetRegionValue = arguments;
            return false;
        }

        if (this.isLocked() && !force) {
            this.logger.log("Can't set the value, the control is locked");
            return false;
        }

        if ( isNaN(newOffset) ) {
            this.logger.log("setRegionValue, Illegal argument: " + newOffset);
            return false;
        }

        if (!t._isRegion) {
            this.logger.log("Call to setRegionValue for non-region Slider ignored. Use setValue","warn");
            return false;
        }

        this._silent = silent;

        this.valueChangeSource = source || Slider.SOURCE_SET_VALUE;

        t.lastOffset = [newOffset, newOffset2];
        this.verifyOffset();

        this._slideStart();

        newX = t.initPageX + newOffset + this.thumbCenterPoint.x;
        newY = t.initPageY + newOffset2 + this.thumbCenterPoint.y;
        this.moveThumb(newX, newY, skipAnim);

        return true;
    },

    /**
     * Checks the background position element position.  If it has moved from the
     * baseline position, the constraints for the thumb are reset
     * @method verifyOffset
     * @return {boolean} True if the offset is the same as the baseline.
     */
    verifyOffset: function() {

        var xy = getXY(this.getEl()),
            t  = this.thumb;

        if (!this.thumbCenterPoint || !this.thumbCenterPoint.x) {
            this.setThumbCenterPoint();
        }

        if (xy) {

            this.logger.log("newPos: " + xy);

            if (xy[0] != this.baselinePos[0] || xy[1] != this.baselinePos[1]) {
                this.logger.log("background moved, resetting constraints");

                // Reset background
                this.setInitPosition();
                this.baselinePos = xy;

                // Reset thumb
                t.initPageX = this.initPageX + t.startOffset[0];
                t.initPageY = this.initPageY + t.startOffset[1];
                t.deltaSetXY = null;
                this.resetThumbConstraints();

                return false;
            }
        }

        return true;
    },

    /**
     * Move the associated slider moved to a timeout to try to get around the 
     * mousedown stealing moz does when I move the slider element between the 
     * cursor and the background during the mouseup event
     * @method moveThumb
     * @param {int} x the X coordinate of the click
     * @param {int} y the Y coordinate of the click
     * @param {boolean} skipAnim don't animate if the move happend onDrag
     * @param {boolean} midMove set to true if this is not terminating
     * the slider movement
     * @private
     */
    moveThumb: function(x, y, skipAnim, midMove) {

        var t = this.thumb,
            self = this,
            p,_p,anim;

        if (!t.available) {
            this.logger.log("thumb is not available yet, aborting move");
            return;
        }

        this.logger.log("move thumb, x: "  + x + ", y: " + y);

        t.setDelta(this.thumbCenterPoint.x, this.thumbCenterPoint.y);

        _p = t.getTargetCoord(x, y);
        p = [Math.round(_p.x), Math.round(_p.y)];

        if (this.animate && t._graduated && !skipAnim) {
            this.logger.log("graduated");
            this.lock();

            // cache the current thumb pos
            this.curCoord = getXY(this.thumb.getEl());
            this.curCoord = [Math.round(this.curCoord[0]), Math.round(this.curCoord[1])];

            setTimeout( function() { self.moveOneTick(p); }, this.tickPause );

        } else if (this.animate && Slider.ANIM_AVAIL && !skipAnim) {
            this.logger.log("animating to " + p);

            this.lock();

            anim = new YAHOO.util.Motion( 
                    t.id, { points: { to: p } }, 
                    this.animationDuration, 
                    YAHOO.util.Easing.easeOut );

            anim.onComplete.subscribe( function() { 
                    self.logger.log("Animation completed _mouseDown:" + self._mouseDown);
                    self.unlock();
                    if (!self._mouseDown) {
                        self.endMove(); 
                    }
                });
            anim.animate();

        } else {
            t.setDragElPos(x, y);
            if (!midMove && !this._mouseDown) {
                this.endMove();
            }
        }
    },

    _slideStart: function() {
        if (!this._sliding) {
            if (!this._silent) {
                this.onSlideStart();
                this.fireEvent("slideStart");
            }
            this._sliding = true;
            this.moveComplete = false; // for backward compatibility. Deprecated
        }
    },

    _slideEnd: function() {
        if (this._sliding) {
            // Reset state before firing slideEnd
            var silent = this._silent;
            this._sliding = false;
            this.moveComplete = true; // for backward compatibility. Deprecated
            this._silent = false;
            if (!silent) {
                this.onSlideEnd();
                this.fireEvent("slideEnd");
            }
        }
    },

    /**
     * Move the slider one tick mark towards its final coordinate.  Used
     * for the animation when tick marks are defined
     * @method moveOneTick
     * @param {int[]} the destination coordinate
     * @private
     */
    moveOneTick: function(finalCoord) {

        var t = this.thumb,
            self = this,
            nextCoord = null,
            tmpX, tmpY;

        if (t._isRegion) {
            nextCoord = this._getNextX(this.curCoord, finalCoord);
            tmpX = (nextCoord !== null) ? nextCoord[0] : this.curCoord[0];
            nextCoord = this._getNextY(this.curCoord, finalCoord);
            tmpY = (nextCoord !== null) ? nextCoord[1] : this.curCoord[1];

            nextCoord = tmpX !== this.curCoord[0] || tmpY !== this.curCoord[1] ?
                [ tmpX, tmpY ] : null;
        } else if (t._isHoriz) {
            nextCoord = this._getNextX(this.curCoord, finalCoord);
        } else {
            nextCoord = this._getNextY(this.curCoord, finalCoord);
        }

        this.logger.log("moveOneTick: " + 
                " finalCoord: " + finalCoord +
                " this.curCoord: " + this.curCoord +
                " nextCoord: " + nextCoord);

        if (nextCoord) {

            // cache the position
            this.curCoord = nextCoord;

            // move to the next coord
            this.thumb.alignElWithMouse(t.getEl(), nextCoord[0] + this.thumbCenterPoint.x, nextCoord[1] + this.thumbCenterPoint.y);
            
            // check if we are in the final position, if not make a recursive call
            if (!(nextCoord[0] == finalCoord[0] && nextCoord[1] == finalCoord[1])) {
                setTimeout(function() { self.moveOneTick(finalCoord); }, 
                        this.tickPause);
            } else {
                this.unlock();
                if (!this._mouseDown) {
                    this.endMove();
                }
            }
        } else {
            this.unlock();
            if (!this._mouseDown) {
                this.endMove();
            }
        }
    },

    /**
     * Returns the next X tick value based on the current coord and the target coord.
     * @method _getNextX
     * @private
     */
    _getNextX: function(curCoord, finalCoord) {
        this.logger.log("getNextX: " + curCoord + ", " + finalCoord);
        var t = this.thumb,
            thresh,
            tmp = [],
            nextCoord = null;

        if (curCoord[0] > finalCoord[0]) {
            thresh = t.tickSize - this.thumbCenterPoint.x;
            tmp = t.getTargetCoord( curCoord[0] - thresh, curCoord[1] );
            nextCoord = [tmp.x, tmp.y];
        } else if (curCoord[0] < finalCoord[0]) {
            thresh = t.tickSize + this.thumbCenterPoint.x;
            tmp = t.getTargetCoord( curCoord[0] + thresh, curCoord[1] );
            nextCoord = [tmp.x, tmp.y];
        } else {
            // equal, do nothing
        }

        return nextCoord;
    },

    /**
     * Returns the next Y tick value based on the current coord and the target coord.
     * @method _getNextY
     * @private
     */
    _getNextY: function(curCoord, finalCoord) {
        var t = this.thumb,
            thresh,
            tmp = [],
            nextCoord = null;

        if (curCoord[1] > finalCoord[1]) {
            thresh = t.tickSize - this.thumbCenterPoint.y;
            tmp = t.getTargetCoord( curCoord[0], curCoord[1] - thresh );
            nextCoord = [tmp.x, tmp.y];
        } else if (curCoord[1] < finalCoord[1]) {
            thresh = t.tickSize + this.thumbCenterPoint.y;
            tmp = t.getTargetCoord( curCoord[0], curCoord[1] + thresh );
            nextCoord = [tmp.x, tmp.y];
        } else {
            // equal, do nothing
        }

        return nextCoord;
    },

    /**
     * Resets the constraints before moving the thumb.
     * @method b4MouseDown
     * @private
     */
    b4MouseDown: function(e) {
        if (!this.backgroundEnabled) {
            return false;
        }

        this.thumb.autoOffset();
        this.baselinePos = [];
    },

    /**
     * Handles the mousedown event for the slider background
     * @method onMouseDown
     * @private
     */
    onMouseDown: function(e) {
        if (!this.backgroundEnabled || this.isLocked()) {
            return false;
        }

        this._mouseDown = true;

        var x = Event.getPageX(e),
            y = Event.getPageY(e);

        this.logger.log("bg mousedown: " + x + "," + y);

        this.focus();
        this._slideStart();
        this.moveThumb(x, y);
    },

    /**
     * Handles the onDrag event for the slider background
     * @method onDrag
     * @private
     */
    onDrag: function(e) {
        this.logger.log("background drag");
        if (this.backgroundEnabled && !this.isLocked()) {
            var x = Event.getPageX(e),
                y = Event.getPageY(e);
            this.moveThumb(x, y, true, true);
            this.fireEvents();
        }
    },

    /**
     * Fired when the slider movement ends
     * @method endMove
     * @private
     */
    endMove: function () {
        this.logger.log("endMove");
        this.unlock();
        this.fireEvents();
        this._slideEnd();
    },

    /**
     * Resets the X and Y contraints for the thumb.  Used in lieu of the thumb
     * instance's inherited resetConstraints because some logic was not
     * applicable.
     * @method resetThumbConstraints
     * @protected
     */
    resetThumbConstraints: function () {
        var t = this.thumb;

        t.setXConstraint(t.leftConstraint, t.rightConstraint, t.xTickSize);
        t.setYConstraint(t.topConstraint, t.bottomConstraint, t.xTickSize);
    },

    /**
     * Fires the change event if the value has been changed.  Ignored if we are in
     * the middle of an animation as the event will fire when the animation is
     * complete
     * @method fireEvents
     * @param {boolean} thumbEvent set to true if this event is fired from an event
     *                  that occurred on the thumb.  If it is, the state of the
     *                  thumb dd object should be correct.  Otherwise, the event
     *                  originated on the background, so the thumb state needs to
     *                  be refreshed before proceeding.
     * @private
     */
    fireEvents: function (thumbEvent) {

        var t = this.thumb, newX, newY, newVal;

        if (!thumbEvent) {
            t.cachePosition();
        }

        if (! this.isLocked()) {
            if (t._isRegion) {
                newX = t.getXValue();
                newY = t.getYValue();

                if (newX != this.previousX || newY != this.previousY) {
                    if (!this._silent) {
                        this.onChange(newX, newY);
                        this.fireEvent("change", { x: newX, y: newY });
                    }
                }

                this.previousX = newX;
                this.previousY = newY;

            } else {
                newVal = t.getValue();
                if (newVal != this.previousVal) {
                    this.logger.log("Firing onchange: " + newVal);
                    if (!this._silent) {
                        this.onChange( newVal );
                        this.fireEvent("change", newVal);
                    }
                }
                this.previousVal = newVal;
            }

        }
    },

    /**
     * Slider toString
     * @method toString
     * @return {string} string representation of the instance
     */
    toString: function () { 
        return ("Slider (" + this.type +") " + this.id);
    }

});

YAHOO.lang.augmentProto(Slider, YAHOO.util.EventProvider);

YAHOO.widget.Slider = Slider;
})();
