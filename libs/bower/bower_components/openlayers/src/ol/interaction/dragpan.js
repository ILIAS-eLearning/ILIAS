goog.provide('ol.interaction.DragPan');

goog.require('ol');
goog.require('ol.ViewHint');
goog.require('ol.coordinate');
goog.require('ol.easing');
goog.require('ol.events.condition');
goog.require('ol.functions');
goog.require('ol.interaction.Pointer');


/**
 * @classdesc
 * Allows the user to pan the map by dragging the map.
 *
 * @constructor
 * @extends {ol.interaction.Pointer}
 * @param {olx.interaction.DragPanOptions=} opt_options Options.
 * @api
 */
ol.interaction.DragPan = function(opt_options) {

  ol.interaction.Pointer.call(this, {
    handleDownEvent: ol.interaction.DragPan.handleDownEvent_,
    handleDragEvent: ol.interaction.DragPan.handleDragEvent_,
    handleUpEvent: ol.interaction.DragPan.handleUpEvent_
  });

  var options = opt_options ? opt_options : {};

  /**
   * @private
   * @type {ol.Kinetic|undefined}
   */
  this.kinetic_ = options.kinetic;

  /**
   * @type {ol.Pixel}
   */
  this.lastCentroid = null;

  /**
   * @type {number}
   */
  this.lastPointersCount_;

  /**
   * @private
   * @type {ol.EventsConditionType}
   */
  this.condition_ = options.condition ?
    options.condition : ol.events.condition.noModifierKeys;

  /**
   * @private
   * @type {boolean}
   */
  this.noKinetic_ = false;

};
ol.inherits(ol.interaction.DragPan, ol.interaction.Pointer);


/**
 * @param {ol.MapBrowserPointerEvent} mapBrowserEvent Event.
 * @this {ol.interaction.DragPan}
 * @private
 */
ol.interaction.DragPan.handleDragEvent_ = function(mapBrowserEvent) {
  var targetPointers = this.targetPointers;
  var centroid =
      ol.interaction.Pointer.centroid(targetPointers);
  if (targetPointers.length == this.lastPointersCount_) {
    if (this.kinetic_) {
      this.kinetic_.update(centroid[0], centroid[1]);
    }
    if (this.lastCentroid) {
      var deltaX = this.lastCentroid[0] - centroid[0];
      var deltaY = centroid[1] - this.lastCentroid[1];
      var map = mapBrowserEvent.map;
      var view = map.getView();
      var viewState = view.getState();
      var center = [deltaX, deltaY];
      ol.coordinate.scale(center, viewState.resolution);
      ol.coordinate.rotate(center, viewState.rotation);
      ol.coordinate.add(center, viewState.center);
      center = view.constrainCenter(center);
      view.setCenter(center);
    }
  } else if (this.kinetic_) {
    // reset so we don't overestimate the kinetic energy after
    // after one finger down, tiny drag, second finger down
    this.kinetic_.begin();
  }
  this.lastCentroid = centroid;
  this.lastPointersCount_ = targetPointers.length;
};


/**
 * @param {ol.MapBrowserPointerEvent} mapBrowserEvent Event.
 * @return {boolean} Stop drag sequence?
 * @this {ol.interaction.DragPan}
 * @private
 */
ol.interaction.DragPan.handleUpEvent_ = function(mapBrowserEvent) {
  var map = mapBrowserEvent.map;
  var view = map.getView();
  if (this.targetPointers.length === 0) {
    if (!this.noKinetic_ && this.kinetic_ && this.kinetic_.end()) {
      var distance = this.kinetic_.getDistance();
      var angle = this.kinetic_.getAngle();
      var center = /** @type {!ol.Coordinate} */ (view.getCenter());
      var centerpx = map.getPixelFromCoordinate(center);
      var dest = map.getCoordinateFromPixel([
        centerpx[0] - distance * Math.cos(angle),
        centerpx[1] - distance * Math.sin(angle)
      ]);
      view.animate({
        center: view.constrainCenter(dest),
        duration: 500,
        easing: ol.easing.easeOut
      });
    }
    view.setHint(ol.ViewHint.INTERACTING, -1);
    return false;
  } else {
    if (this.kinetic_) {
      // reset so we don't overestimate the kinetic energy after
      // after one finger up, tiny drag, second finger up
      this.kinetic_.begin();
    }
    this.lastCentroid = null;
    return true;
  }
};


/**
 * @param {ol.MapBrowserPointerEvent} mapBrowserEvent Event.
 * @return {boolean} Start drag sequence?
 * @this {ol.interaction.DragPan}
 * @private
 */
ol.interaction.DragPan.handleDownEvent_ = function(mapBrowserEvent) {
  if (this.targetPointers.length > 0 && this.condition_(mapBrowserEvent)) {
    var map = mapBrowserEvent.map;
    var view = map.getView();
    this.lastCentroid = null;
    if (!this.handlingDownUpSequence) {
      view.setHint(ol.ViewHint.INTERACTING, 1);
    }
    // stop any current animation
    if (view.getAnimating()) {
      view.setCenter(mapBrowserEvent.frameState.viewState.center);
    }
    if (this.kinetic_) {
      this.kinetic_.begin();
    }
    // No kinetic as soon as more than one pointer on the screen is
    // detected. This is to prevent nasty pans after pinch.
    this.noKinetic_ = this.targetPointers.length > 1;
    return true;
  } else {
    return false;
  }
};


/**
 * @inheritDoc
 */
ol.interaction.DragPan.prototype.shouldStopEvent = ol.functions.FALSE;
