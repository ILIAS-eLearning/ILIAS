// FIXME offset panning

goog.provide('ol.renderer.canvas.Map');

goog.require('ol.transform');
goog.require('ol');
goog.require('ol.array');
goog.require('ol.css');
goog.require('ol.dom');
goog.require('ol.layer.Layer');
goog.require('ol.render.Event');
goog.require('ol.render.EventType');
goog.require('ol.render.canvas');
goog.require('ol.render.canvas.Immediate');
goog.require('ol.renderer.Map');
goog.require('ol.renderer.Type');
goog.require('ol.source.State');


/**
 * @constructor
 * @extends {ol.renderer.Map}
 * @param {Element} container Container.
 * @param {ol.PluggableMap} map Map.
 * @api
 */
ol.renderer.canvas.Map = function(container, map) {

  ol.renderer.Map.call(this, container, map);

  /**
   * @private
   * @type {CanvasRenderingContext2D}
   */
  this.context_ = ol.dom.createCanvasContext2D();

  /**
   * @private
   * @type {HTMLCanvasElement}
   */
  this.canvas_ = this.context_.canvas;

  this.canvas_.style.width = '100%';
  this.canvas_.style.height = '100%';
  this.canvas_.style.display = 'block';
  this.canvas_.className = ol.css.CLASS_UNSELECTABLE;
  container.insertBefore(this.canvas_, container.childNodes[0] || null);

  /**
   * @private
   * @type {boolean}
   */
  this.renderedVisible_ = true;

  /**
   * @private
   * @type {ol.Transform}
   */
  this.transform_ = ol.transform.create();

};
ol.inherits(ol.renderer.canvas.Map, ol.renderer.Map);


/**
 * Determine if this renderer handles the provided layer.
 * @param {ol.renderer.Type} type The renderer type.
 * @return {boolean} The renderer can render the layer.
 */
ol.renderer.canvas.Map['handles'] = function(type) {
  return type === ol.renderer.Type.CANVAS;
};


/**
 * Create the map renderer.
 * @param {Element} container Container.
 * @param {ol.PluggableMap} map Map.
 * @return {ol.renderer.canvas.Map} The map renderer.
 */
ol.renderer.canvas.Map['create'] = function(container, map) {
  return new ol.renderer.canvas.Map(container, map);
};


/**
 * @param {ol.render.EventType} type Event type.
 * @param {olx.FrameState} frameState Frame state.
 * @private
 */
ol.renderer.canvas.Map.prototype.dispatchComposeEvent_ = function(type, frameState) {
  var map = this.getMap();
  var context = this.context_;
  if (map.hasListener(type)) {
    var extent = frameState.extent;
    var pixelRatio = frameState.pixelRatio;
    var viewState = frameState.viewState;
    var rotation = viewState.rotation;

    var transform = this.getTransform(frameState);

    var vectorContext = new ol.render.canvas.Immediate(context, pixelRatio,
        extent, transform, rotation);
    var composeEvent = new ol.render.Event(type, vectorContext,
        frameState, context, null);
    map.dispatchEvent(composeEvent);
  }
};


/**
 * @param {olx.FrameState} frameState Frame state.
 * @protected
 * @return {!ol.Transform} Transform.
 */
ol.renderer.canvas.Map.prototype.getTransform = function(frameState) {
  var viewState = frameState.viewState;
  var dx1 = this.canvas_.width / 2;
  var dy1 = this.canvas_.height / 2;
  var sx = frameState.pixelRatio / viewState.resolution;
  var sy = -sx;
  var angle = -viewState.rotation;
  var dx2 = -viewState.center[0];
  var dy2 = -viewState.center[1];
  return ol.transform.compose(this.transform_, dx1, dy1, sx, sy, angle, dx2, dy2);
};


/**
 * @inheritDoc
 */
ol.renderer.canvas.Map.prototype.getType = function() {
  return ol.renderer.Type.CANVAS;
};


/**
 * @inheritDoc
 */
ol.renderer.canvas.Map.prototype.renderFrame = function(frameState) {

  if (!frameState) {
    if (this.renderedVisible_) {
      this.canvas_.style.display = 'none';
      this.renderedVisible_ = false;
    }
    return;
  }

  var context = this.context_;
  var pixelRatio = frameState.pixelRatio;
  var width = Math.round(frameState.size[0] * pixelRatio);
  var height = Math.round(frameState.size[1] * pixelRatio);
  if (this.canvas_.width != width || this.canvas_.height != height) {
    this.canvas_.width = width;
    this.canvas_.height = height;
  } else {
    context.clearRect(0, 0, width, height);
  }

  var rotation = frameState.viewState.rotation;

  this.calculateMatrices2D(frameState);

  this.dispatchComposeEvent_(ol.render.EventType.PRECOMPOSE, frameState);

  var layerStatesArray = frameState.layerStatesArray;
  ol.array.stableSort(layerStatesArray, ol.renderer.Map.sortByZIndex);

  if (rotation) {
    context.save();
    ol.render.canvas.rotateAtOffset(context, rotation, width / 2, height / 2);
  }

  var viewResolution = frameState.viewState.resolution;
  var i, ii, layer, layerRenderer, layerState;
  for (i = 0, ii = layerStatesArray.length; i < ii; ++i) {
    layerState = layerStatesArray[i];
    layer = layerState.layer;
    layerRenderer = /** @type {ol.renderer.canvas.Layer} */ (this.getLayerRenderer(layer));
    if (!ol.layer.Layer.visibleAtResolution(layerState, viewResolution) ||
        layerState.sourceState != ol.source.State.READY) {
      continue;
    }
    if (layerRenderer.prepareFrame(frameState, layerState)) {
      layerRenderer.composeFrame(frameState, layerState, context);
    }
  }

  if (rotation) {
    context.restore();
  }

  this.dispatchComposeEvent_(
      ol.render.EventType.POSTCOMPOSE, frameState);

  if (!this.renderedVisible_) {
    this.canvas_.style.display = '';
    this.renderedVisible_ = true;
  }

  this.scheduleRemoveUnusedLayerRenderers(frameState);
  this.scheduleExpireIconCache(frameState);
};


/**
 * @inheritDoc
 */
ol.renderer.canvas.Map.prototype.forEachLayerAtPixel = function(pixel, frameState, callback, thisArg,
    layerFilter, thisArg2) {
  var result;
  var viewState = frameState.viewState;
  var viewResolution = viewState.resolution;

  var layerStates = frameState.layerStatesArray;
  var numLayers = layerStates.length;

  var coordinate = ol.transform.apply(
      frameState.pixelToCoordinateTransform, pixel.slice());

  var i;
  for (i = numLayers - 1; i >= 0; --i) {
    var layerState = layerStates[i];
    var layer = layerState.layer;
    if (ol.layer.Layer.visibleAtResolution(layerState, viewResolution) &&
        layerFilter.call(thisArg2, layer)) {
      var layerRenderer = /** @type {ol.renderer.canvas.Layer} */ (this.getLayerRenderer(layer));
      result = layerRenderer.forEachLayerAtCoordinate(
          coordinate, frameState, callback, thisArg);
      if (result) {
        return result;
      }
    }
  }
  return undefined;
};
