goog.provide('ol.interaction.Pointer');

goog.require('ol');
goog.require('ol.functions');
goog.require('ol.MapBrowserEventType');
goog.require('ol.MapBrowserPointerEvent');
goog.require('ol.interaction.Interaction');
goog.require('ol.obj');


/**
 * @classdesc
 * Base class that calls user-defined functions on `down`, `move` and `up`
 * events. This class also manages "drag sequences".
 *
 * When the `handleDownEvent` user function returns `true` a drag sequence is
 * started. During a drag sequence the `handleDragEvent` user function is
 * called on `move` events. The drag sequence ends when the `handleUpEvent`
 * user function is called and returns `false`.
 *
 * @constructor
 * @param {olx.interaction.PointerOptions=} opt_options Options.
 * @extends {ol.interaction.Interaction}
 * @api
 */
ol.interaction.Pointer = function(opt_options) {

  var options = opt_options ? opt_options : {};

  var handleEvent = options.handleEvent ?
    options.handleEvent : ol.interaction.Pointer.handleEvent;

  ol.interaction.Interaction.call(this, {
    handleEvent: handleEvent
  });

  /**
   * @type {function(ol.MapBrowserPointerEvent):boolean}
   * @private
   */
  this.handleDownEvent_ = options.handleDownEvent ?
    options.handleDownEvent : ol.interaction.Pointer.handleDownEvent;

  /**
   * @type {function(ol.MapBrowserPointerEvent)}
   * @private
   */
  this.handleDragEvent_ = options.handleDragEvent ?
    options.handleDragEvent : ol.interaction.Pointer.handleDragEvent;

  /**
   * @type {function(ol.MapBrowserPointerEvent)}
   * @private
   */
  this.handleMoveEvent_ = options.handleMoveEvent ?
    options.handleMoveEvent : ol.interaction.Pointer.handleMoveEvent;

  /**
   * @type {function(ol.MapBrowserPointerEvent):boolean}
   * @private
   */
  this.handleUpEvent_ = options.handleUpEvent ?
    options.handleUpEvent : ol.interaction.Pointer.handleUpEvent;

  /**
   * @type {boolean}
   * @protected
   */
  this.handlingDownUpSequence = false;

  /**
   * @type {Object.<string, ol.pointer.PointerEvent>}
   * @private
   */
  this.trackedPointers_ = {};

  /**
   * @type {Array.<ol.pointer.PointerEvent>}
   * @protected
   */
  this.targetPointers = [];

};
ol.inherits(ol.interaction.Pointer, ol.interaction.Interaction);


/**
 * @param {Array.<ol.pointer.PointerEvent>} pointerEvents List of events.
 * @return {ol.Pixel} Centroid pixel.
 */
ol.interaction.Pointer.centroid = function(pointerEvents) {
  var length = pointerEvents.length;
  var clientX = 0;
  var clientY = 0;
  for (var i = 0; i < length; i++) {
    clientX += pointerEvents[i].clientX;
    clientY += pointerEvents[i].clientY;
  }
  return [clientX / length, clientY / length];
};


/**
 * @param {ol.MapBrowserPointerEvent} mapBrowserEvent Event.
 * @return {boolean} Whether the event is a pointerdown, pointerdrag
 *     or pointerup event.
 * @private
 */
ol.interaction.Pointer.prototype.isPointerDraggingEvent_ = function(mapBrowserEvent) {
  var type = mapBrowserEvent.type;
  return (
    type === ol.MapBrowserEventType.POINTERDOWN ||
      type === ol.MapBrowserEventType.POINTERDRAG ||
      type === ol.MapBrowserEventType.POINTERUP);
};


/**
 * @param {ol.MapBrowserPointerEvent} mapBrowserEvent Event.
 * @private
 */
ol.interaction.Pointer.prototype.updateTrackedPointers_ = function(mapBrowserEvent) {
  if (this.isPointerDraggingEvent_(mapBrowserEvent)) {
    var event = mapBrowserEvent.pointerEvent;

    var id = event.pointerId.toString();
    if (mapBrowserEvent.type == ol.MapBrowserEventType.POINTERUP) {
      delete this.trackedPointers_[id];
    } else if (mapBrowserEvent.type ==
        ol.MapBrowserEventType.POINTERDOWN) {
      this.trackedPointers_[id] = event;
    } else if (id in this.trackedPointers_) {
      // update only when there was a pointerdown event for this pointer
      this.trackedPointers_[id] = event;
    }
    this.targetPointers = ol.obj.getValues(this.trackedPointers_);
  }
};


/**
 * @param {ol.MapBrowserPointerEvent} mapBrowserEvent Event.
 * @this {ol.interaction.Pointer}
 */
ol.interaction.Pointer.handleDragEvent = ol.nullFunction;


/**
 * @param {ol.MapBrowserPointerEvent} mapBrowserEvent Event.
 * @return {boolean} Capture dragging.
 * @this {ol.interaction.Pointer}
 */
ol.interaction.Pointer.handleUpEvent = ol.functions.FALSE;


/**
 * @param {ol.MapBrowserPointerEvent} mapBrowserEvent Event.
 * @return {boolean} Capture dragging.
 * @this {ol.interaction.Pointer}
 */
ol.interaction.Pointer.handleDownEvent = ol.functions.FALSE;


/**
 * @param {ol.MapBrowserPointerEvent} mapBrowserEvent Event.
 * @this {ol.interaction.Pointer}
 */
ol.interaction.Pointer.handleMoveEvent = ol.nullFunction;


/**
 * Handles the {@link ol.MapBrowserEvent map browser event} and may call into
 * other functions, if event sequences like e.g. 'drag' or 'down-up' etc. are
 * detected.
 * @param {ol.MapBrowserEvent} mapBrowserEvent Map browser event.
 * @return {boolean} `false` to stop event propagation.
 * @this {ol.interaction.Pointer}
 * @api
 */
ol.interaction.Pointer.handleEvent = function(mapBrowserEvent) {
  if (!(mapBrowserEvent instanceof ol.MapBrowserPointerEvent)) {
    return true;
  }

  var stopEvent = false;
  this.updateTrackedPointers_(mapBrowserEvent);
  if (this.handlingDownUpSequence) {
    if (mapBrowserEvent.type == ol.MapBrowserEventType.POINTERDRAG) {
      this.handleDragEvent_(mapBrowserEvent);
    } else if (mapBrowserEvent.type == ol.MapBrowserEventType.POINTERUP) {
      var handledUp = this.handleUpEvent_(mapBrowserEvent);
      this.handlingDownUpSequence = handledUp && this.targetPointers.length > 0;
    }
  } else {
    if (mapBrowserEvent.type == ol.MapBrowserEventType.POINTERDOWN) {
      var handled = this.handleDownEvent_(mapBrowserEvent);
      this.handlingDownUpSequence = handled;
      stopEvent = this.shouldStopEvent(handled);
    } else if (mapBrowserEvent.type == ol.MapBrowserEventType.POINTERMOVE) {
      this.handleMoveEvent_(mapBrowserEvent);
    }
  }
  return !stopEvent;
};


/**
 * This method is used to determine if "down" events should be propagated to
 * other interactions or should be stopped.
 *
 * The method receives the return code of the "handleDownEvent" function.
 *
 * By default this function is the "identity" function. It's overidden in
 * child classes.
 *
 * @param {boolean} handled Was the event handled by the interaction?
 * @return {boolean} Should the event be stopped?
 * @protected
 */
ol.interaction.Pointer.prototype.shouldStopEvent = function(handled) {
  return handled;
};
