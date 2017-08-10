/**
 * A drag and drop implementation to be used as the thumb of a slider.
 * @class SliderThumb
 * @extends YAHOO.util.DD
 * @constructor
 * @param {String} id the id of the slider html element
 * @param {String} sGroup the group of related DragDrop items
 * @param {int} iLeft the number of pixels the element can move left
 * @param {int} iRight the number of pixels the element can move right
 * @param {int} iUp the number of pixels the element can move up
 * @param {int} iDown the number of pixels the element can move down
 * @param {int} iTickSize optional parameter for specifying that the element 
 * should move a certain number pixels at a time.
 */
YAHOO.widget.SliderThumb = function(id, sGroup, iLeft, iRight, iUp, iDown, iTickSize) {

    if (id) {
        YAHOO.widget.SliderThumb.superclass.constructor.call(this, id, sGroup);

        /**
         * The id of the thumbs parent HTML element (the slider background 
         * element).
         * @property parentElId
         * @type string
         */
        this.parentElId = sGroup;
    }


    this.logger = new YAHOO.widget.LogWriter(this.toString());

    /**
     * Overrides the isTarget property in YAHOO.util.DragDrop
     * @property isTarget
     * @private
     */
    this.isTarget = false;

    /**
     * The tick size for this slider
     * @property tickSize
     * @type int
     * @private
     */
    this.tickSize = iTickSize;

    /**
     * Informs the drag and drop util that the offsets should remain when
     * resetting the constraints.  This preserves the slider value when
     * the constraints are reset
     * @property maintainOffset
     * @type boolean
     * @private
     */
    this.maintainOffset = true;

    this.initSlider(iLeft, iRight, iUp, iDown, iTickSize);

    /**
     * Turns off the autoscroll feature in drag and drop
     * @property scroll
     * @private
     */
    this.scroll = false;

}; 

YAHOO.extend(YAHOO.widget.SliderThumb, YAHOO.util.DD, {

    /**
     * The (X and Y) difference between the thumb location and its parent 
     * (the slider background) when the control is instantiated.
     * @property startOffset
     * @type [int, int]
     */
    startOffset: null,

    /**
     * Override the default setting of dragOnly to true.
     * @property dragOnly
     * @type boolean
     * @default true
     */
    dragOnly : true,

    /**
     * Flag used to figure out if this is a horizontal or vertical slider
     * @property _isHoriz
     * @type boolean
     * @private
     */
    _isHoriz: false,

    /**
     * Cache the last value so we can check for change
     * @property _prevVal
     * @type int
     * @private
     */
    _prevVal: 0,

    /**
     * The slider is _graduated if there is a tick interval defined
     * @property _graduated
     * @type boolean
     * @private
     */
    _graduated: false,


    /**
     * Returns the difference between the location of the thumb and its parent.
     * @method getOffsetFromParent
     * @param {[int, int]} parentPos Optionally accepts the position of the parent
     * @type [int, int]
     */
    getOffsetFromParent0: function(parentPos) {
        var myPos = YAHOO.util.Dom.getXY(this.getEl()),
            ppos  = parentPos || YAHOO.util.Dom.getXY(this.parentElId);

        return [ (myPos[0] - ppos[0]), (myPos[1] - ppos[1]) ];
    },

    getOffsetFromParent: function(parentPos) {

        var el = this.getEl(), newOffset,
            myPos,ppos,l,t,deltaX,deltaY,newLeft,newTop;

        if (!this.deltaOffset) {

            myPos = YAHOO.util.Dom.getXY(el);
            ppos  = parentPos || YAHOO.util.Dom.getXY(this.parentElId);

            newOffset = [ (myPos[0] - ppos[0]), (myPos[1] - ppos[1]) ];

            l = parseInt( YAHOO.util.Dom.getStyle(el, "left"), 10 );
            t = parseInt( YAHOO.util.Dom.getStyle(el, "top" ), 10 );

            deltaX = l - newOffset[0];
            deltaY = t - newOffset[1];

            if (isNaN(deltaX) || isNaN(deltaY)) {
                this.logger.log("element does not have a position style def yet");
            } else {
                this.deltaOffset = [deltaX, deltaY];
            }

        } else {
            newLeft = parseInt( YAHOO.util.Dom.getStyle(el, "left"), 10 );
            newTop  = parseInt( YAHOO.util.Dom.getStyle(el, "top" ), 10 );

            newOffset  = [newLeft + this.deltaOffset[0], newTop + this.deltaOffset[1]];
        }

        return newOffset;
    },

    /**
     * Set up the slider, must be called in the constructor of all subclasses
     * @method initSlider
     * @param {int} iLeft the number of pixels the element can move left
     * @param {int} iRight the number of pixels the element can move right
     * @param {int} iUp the number of pixels the element can move up
     * @param {int} iDown the number of pixels the element can move down
     * @param {int} iTickSize the width of the tick interval.
     */
    initSlider: function (iLeft, iRight, iUp, iDown, iTickSize) {
        this.initLeft = iLeft;
        this.initRight = iRight;
        this.initUp = iUp;
        this.initDown = iDown;

        this.setXConstraint(iLeft, iRight, iTickSize);
        this.setYConstraint(iUp, iDown, iTickSize);

        if (iTickSize && iTickSize > 1) {
            this._graduated = true;
        }

        this._isHoriz  = (iLeft || iRight); 
        this._isVert   = (iUp   || iDown);
        this._isRegion = (this._isHoriz && this._isVert); 

    },

    /**
     * Clear's the slider's ticks
     * @method clearTicks
     */
    clearTicks: function () {
        YAHOO.widget.SliderThumb.superclass.clearTicks.call(this);
        this.tickSize = 0;
        this._graduated = false;
    },


    /**
     * Gets the current offset from the element's start position in
     * pixels.
     * @method getValue
     * @return {int} the number of pixels (positive or negative) the
     * slider has moved from the start position.
     */
    getValue: function () {
        return (this._isHoriz) ? this.getXValue() : this.getYValue();
    },

    /**
     * Gets the current X offset from the element's start position in
     * pixels.
     * @method getXValue
     * @return {int} the number of pixels (positive or negative) the
     * slider has moved horizontally from the start position.
     */
    getXValue: function () {
        if (!this.available) { 
            return 0; 
        }
        var newOffset = this.getOffsetFromParent();
        if (YAHOO.lang.isNumber(newOffset[0])) {
            this.lastOffset = newOffset;
            return (newOffset[0] - this.startOffset[0]);
        } else {
            this.logger.log("can't get offset, using old value: " + 
                this.lastOffset[0]);
            return (this.lastOffset[0] - this.startOffset[0]);
        }
    },

    /**
     * Gets the current Y offset from the element's start position in
     * pixels.
     * @method getYValue
     * @return {int} the number of pixels (positive or negative) the
     * slider has moved vertically from the start position.
     */
    getYValue: function () {
        if (!this.available) { 
            return 0; 
        }
        var newOffset = this.getOffsetFromParent();
        if (YAHOO.lang.isNumber(newOffset[1])) {
            this.lastOffset = newOffset;
            return (newOffset[1] - this.startOffset[1]);
        } else {
            this.logger.log("can't get offset, using old value: " + 
                this.lastOffset[1]);
            return (this.lastOffset[1] - this.startOffset[1]);
        }
    },

    /**
     * Thumb toString
     * @method toString
     * @return {string} string representation of the instance
     */
    toString: function () { 
        return "SliderThumb " + this.id;
    },

    /**
     * The onchange event for the handle/thumb is delegated to the YAHOO.widget.Slider
     * instance it belongs to.
     * @method onChange
     * @private
     */
    onChange: function (x, y) { 
    }

});
