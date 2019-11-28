goog.provide('ol.renderer.canvas.VectorLayer');

goog.require('ol');
goog.require('ol.LayerType');
goog.require('ol.ViewHint');
goog.require('ol.dom');
goog.require('ol.events');
goog.require('ol.events.EventType');
goog.require('ol.ext.rbush');
goog.require('ol.extent');
goog.require('ol.render.EventType');
goog.require('ol.render.canvas');
goog.require('ol.render.canvas.ReplayGroup');
goog.require('ol.renderer.Type');
goog.require('ol.renderer.canvas.Layer');
goog.require('ol.renderer.vector');


/**
 * @constructor
 * @extends {ol.renderer.canvas.Layer}
 * @param {ol.layer.Vector} vectorLayer Vector layer.
 * @api
 */
ol.renderer.canvas.VectorLayer = function(vectorLayer) {

  ol.renderer.canvas.Layer.call(this, vectorLayer);

  /**
   * Declutter tree.
   * @private
   */
  this.declutterTree_ = vectorLayer.getDeclutter() ?
    ol.ext.rbush(9) : null;

  /**
   * @private
   * @type {boolean}
   */
  this.dirty_ = false;

  /**
   * @private
   * @type {number}
   */
  this.renderedRevision_ = -1;

  /**
   * @private
   * @type {number}
   */
  this.renderedResolution_ = NaN;

  /**
   * @private
   * @type {ol.Extent}
   */
  this.renderedExtent_ = ol.extent.createEmpty();

  /**
   * @private
   * @type {function(ol.Feature, ol.Feature): number|null}
   */
  this.renderedRenderOrder_ = null;

  /**
   * @private
   * @type {ol.render.canvas.ReplayGroup}
   */
  this.replayGroup_ = null;

  /**
   * A new replay group had to be created by `prepareFrame()`
   * @type {boolean}
   */
  this.replayGroupChanged = true;

  /**
   * @type {CanvasRenderingContext2D}
   */
  this.context = ol.dom.createCanvasContext2D();

  ol.events.listen(ol.render.canvas.labelCache, ol.events.EventType.CLEAR, this.handleFontsChanged_, this);

};
ol.inherits(ol.renderer.canvas.VectorLayer, ol.renderer.canvas.Layer);


/**
 * Determine if this renderer handles the provided layer.
 * @param {ol.renderer.Type} type The renderer type.
 * @param {ol.layer.Layer} layer The candidate layer.
 * @return {boolean} The renderer can render the layer.
 */
ol.renderer.canvas.VectorLayer['handles'] = function(type, layer) {
  return type === ol.renderer.Type.CANVAS && layer.getType() === ol.LayerType.VECTOR;
};


/**
 * Create a layer renderer.
 * @param {ol.renderer.Map} mapRenderer The map renderer.
 * @param {ol.layer.Layer} layer The layer to be rendererd.
 * @return {ol.renderer.canvas.VectorLayer} The layer renderer.
 */
ol.renderer.canvas.VectorLayer['create'] = function(mapRenderer, layer) {
  return new ol.renderer.canvas.VectorLayer(/** @type {ol.layer.Vector} */ (layer));
};


/**
 * @inheritDoc
 */
ol.renderer.canvas.VectorLayer.prototype.disposeInternal = function() {
  ol.events.unlisten(ol.render.canvas.labelCache, ol.events.EventType.CLEAR, this.handleFontsChanged_, this);
  ol.renderer.canvas.Layer.prototype.disposeInternal.call(this);
};


/**
 * @inheritDoc
 */
ol.renderer.canvas.VectorLayer.prototype.composeFrame = function(frameState, layerState, context) {

  var extent = frameState.extent;
  var pixelRatio = frameState.pixelRatio;
  var skippedFeatureUids = layerState.managed ?
    frameState.skippedFeatureUids : {};
  var viewState = frameState.viewState;
  var projection = viewState.projection;
  var rotation = viewState.rotation;
  var projectionExtent = projection.getExtent();
  var vectorSource = /** @type {ol.source.Vector} */ (this.getLayer().getSource());

  var transform = this.getTransform(frameState, 0);

  this.preCompose(context, frameState, transform);

  // clipped rendering if layer extent is set
  var clipExtent = layerState.extent;
  var clipped = clipExtent !== undefined;
  if (clipped) {
    this.clip(context, frameState,  /** @type {ol.Extent} */ (clipExtent));
  }
  var replayGroup = this.replayGroup_;
  if (replayGroup && !replayGroup.isEmpty()) {
    if (this.declutterTree_) {
      this.declutterTree_.clear();
    }
    var layer = /** @type {ol.layer.Vector} */ (this.getLayer());
    var drawOffsetX = 0;
    var drawOffsetY = 0;
    var replayContext;
    var transparentLayer = layerState.opacity !== 1;
    var hasRenderListeners = layer.hasListener(ol.render.EventType.RENDER);
    if (transparentLayer || hasRenderListeners) {
      var drawWidth = context.canvas.width;
      var drawHeight = context.canvas.height;
      if (rotation) {
        var drawSize = Math.round(Math.sqrt(drawWidth * drawWidth + drawHeight * drawHeight));
        drawOffsetX = (drawSize - drawWidth) / 2;
        drawOffsetY = (drawSize - drawHeight) / 2;
        drawWidth = drawHeight = drawSize;
      }
      // resize and clear
      this.context.canvas.width = drawWidth;
      this.context.canvas.height = drawHeight;
      replayContext = this.context;
    } else {
      replayContext = context;
    }

    var alpha = replayContext.globalAlpha;
    if (!transparentLayer) {
      // for performance reasons, context.save / context.restore is not used
      // to save and restore the transformation matrix and the opacity.
      // see http://jsperf.com/context-save-restore-versus-variable
      replayContext.globalAlpha = layerState.opacity;
    }

    if (replayContext != context) {
      replayContext.translate(drawOffsetX, drawOffsetY);
    }

    var width = frameState.size[0] * pixelRatio;
    var height = frameState.size[1] * pixelRatio;
    ol.render.canvas.rotateAtOffset(replayContext, -rotation,
        width / 2, height / 2);
    replayGroup.replay(replayContext, transform, rotation, skippedFeatureUids);
    if (vectorSource.getWrapX() && projection.canWrapX() &&
        !ol.extent.containsExtent(projectionExtent, extent)) {
      var startX = extent[0];
      var worldWidth = ol.extent.getWidth(projectionExtent);
      var world = 0;
      var offsetX;
      while (startX < projectionExtent[0]) {
        --world;
        offsetX = worldWidth * world;
        transform = this.getTransform(frameState, offsetX);
        replayGroup.replay(replayContext, transform, rotation, skippedFeatureUids);
        startX += worldWidth;
      }
      world = 0;
      startX = extent[2];
      while (startX > projectionExtent[2]) {
        ++world;
        offsetX = worldWidth * world;
        transform = this.getTransform(frameState, offsetX);
        replayGroup.replay(replayContext, transform, rotation, skippedFeatureUids);
        startX -= worldWidth;
      }
      // restore original transform for render and compose events
      transform = this.getTransform(frameState, 0);
    }
    ol.render.canvas.rotateAtOffset(replayContext, rotation,
        width / 2, height / 2);

    if (replayContext != context) {
      if (hasRenderListeners) {
        this.dispatchRenderEvent(replayContext, frameState, transform);
      }
      if (transparentLayer) {
        var mainContextAlpha = context.globalAlpha;
        context.globalAlpha = layerState.opacity;
        context.drawImage(replayContext.canvas, -drawOffsetX, -drawOffsetY);
        context.globalAlpha = mainContextAlpha;
      } else {
        context.drawImage(replayContext.canvas, -drawOffsetX, -drawOffsetY);
      }
      replayContext.translate(-drawOffsetX, -drawOffsetY);
    }

    if (!transparentLayer) {
      replayContext.globalAlpha = alpha;
    }
  }

  if (clipped) {
    context.restore();
  }
  this.postCompose(context, frameState, layerState, transform);

};


/**
 * @inheritDoc
 */
ol.renderer.canvas.VectorLayer.prototype.forEachFeatureAtCoordinate = function(coordinate, frameState, hitTolerance, callback, thisArg) {
  if (!this.replayGroup_) {
    return undefined;
  } else {
    var resolution = frameState.viewState.resolution;
    var rotation = frameState.viewState.rotation;
    var layer = /** @type {ol.layer.Vector} */ (this.getLayer());
    /** @type {Object.<string, boolean>} */
    var features = {};
    var result = this.replayGroup_.forEachFeatureAtCoordinate(coordinate, resolution,
        rotation, hitTolerance, {},
        /**
         * @param {ol.Feature|ol.render.Feature} feature Feature.
         * @return {?} Callback result.
         */
        function(feature) {
          var key = ol.getUid(feature).toString();
          if (!(key in features)) {
            features[key] = true;
            return callback.call(thisArg, feature, layer);
          }
        }, null);
    return result;
  }
};


/**
 * @param {ol.events.Event} event Event.
 */
ol.renderer.canvas.VectorLayer.prototype.handleFontsChanged_ = function(event) {
  var layer = this.getLayer();
  if (layer.getVisible() && this.replayGroup_) {
    layer.changed();
  }
};


/**
 * Handle changes in image style state.
 * @param {ol.events.Event} event Image style change event.
 * @private
 */
ol.renderer.canvas.VectorLayer.prototype.handleStyleImageChange_ = function(event) {
  this.renderIfReadyAndVisible();
};


/**
 * @inheritDoc
 */
ol.renderer.canvas.VectorLayer.prototype.prepareFrame = function(frameState, layerState) {

  var vectorLayer = /** @type {ol.layer.Vector} */ (this.getLayer());
  var vectorSource = vectorLayer.getSource();

  this.updateLogos(frameState, vectorSource);

  var animating = frameState.viewHints[ol.ViewHint.ANIMATING];
  var interacting = frameState.viewHints[ol.ViewHint.INTERACTING];
  var updateWhileAnimating = vectorLayer.getUpdateWhileAnimating();
  var updateWhileInteracting = vectorLayer.getUpdateWhileInteracting();

  if (!this.dirty_ && (!updateWhileAnimating && animating) ||
      (!updateWhileInteracting && interacting)) {
    return true;
  }

  var frameStateExtent = frameState.extent;
  var viewState = frameState.viewState;
  var projection = viewState.projection;
  var resolution = viewState.resolution;
  var pixelRatio = frameState.pixelRatio;
  var vectorLayerRevision = vectorLayer.getRevision();
  var vectorLayerRenderBuffer = vectorLayer.getRenderBuffer();
  var vectorLayerRenderOrder = vectorLayer.getRenderOrder();

  if (vectorLayerRenderOrder === undefined) {
    vectorLayerRenderOrder = ol.renderer.vector.defaultOrder;
  }

  var extent = ol.extent.buffer(frameStateExtent,
      vectorLayerRenderBuffer * resolution);
  var projectionExtent = viewState.projection.getExtent();

  if (vectorSource.getWrapX() && viewState.projection.canWrapX() &&
      !ol.extent.containsExtent(projectionExtent, frameState.extent)) {
    // For the replay group, we need an extent that intersects the real world
    // (-180° to +180°). To support geometries in a coordinate range from -540°
    // to +540°, we add at least 1 world width on each side of the projection
    // extent. If the viewport is wider than the world, we need to add half of
    // the viewport width to make sure we cover the whole viewport.
    var worldWidth = ol.extent.getWidth(projectionExtent);
    var buffer = Math.max(ol.extent.getWidth(extent) / 2, worldWidth);
    extent[0] = projectionExtent[0] - buffer;
    extent[2] = projectionExtent[2] + buffer;
  }

  if (!this.dirty_ &&
      this.renderedResolution_ == resolution &&
      this.renderedRevision_ == vectorLayerRevision &&
      this.renderedRenderOrder_ == vectorLayerRenderOrder &&
      ol.extent.containsExtent(this.renderedExtent_, extent)) {
    this.replayGroupChanged = false;
    return true;
  }

  this.replayGroup_ = null;

  this.dirty_ = false;

  var replayGroup = new ol.render.canvas.ReplayGroup(
      ol.renderer.vector.getTolerance(resolution, pixelRatio), extent, resolution,
      pixelRatio, vectorSource.getOverlaps(), this.declutterTree_, vectorLayer.getRenderBuffer());
  vectorSource.loadFeatures(extent, resolution, projection);
  /**
   * @param {ol.Feature} feature Feature.
   * @this {ol.renderer.canvas.VectorLayer}
   */
  var renderFeature = function(feature) {
    var styles;
    var styleFunction = feature.getStyleFunction();
    if (styleFunction) {
      styles = styleFunction.call(feature, resolution);
    } else {
      styleFunction = vectorLayer.getStyleFunction();
      if (styleFunction) {
        styles = styleFunction(feature, resolution);
      }
    }
    if (styles) {
      var dirty = this.renderFeature(
          feature, resolution, pixelRatio, styles, replayGroup);
      this.dirty_ = this.dirty_ || dirty;
    }
  }.bind(this);
  if (vectorLayerRenderOrder) {
    /** @type {Array.<ol.Feature>} */
    var features = [];
    vectorSource.forEachFeatureInExtent(extent,
        /**
         * @param {ol.Feature} feature Feature.
         */
        function(feature) {
          features.push(feature);
        }, this);
    features.sort(vectorLayerRenderOrder);
    for (var i = 0, ii = features.length; i < ii; ++i) {
      renderFeature(features[i]);
    }
  } else {
    vectorSource.forEachFeatureInExtent(extent, renderFeature, this);
  }
  replayGroup.finish();

  this.renderedResolution_ = resolution;
  this.renderedRevision_ = vectorLayerRevision;
  this.renderedRenderOrder_ = vectorLayerRenderOrder;
  this.renderedExtent_ = extent;
  this.replayGroup_ = replayGroup;

  this.replayGroupChanged = true;
  return true;
};


/**
 * @param {ol.Feature} feature Feature.
 * @param {number} resolution Resolution.
 * @param {number} pixelRatio Pixel ratio.
 * @param {(ol.style.Style|Array.<ol.style.Style>)} styles The style or array of
 *     styles.
 * @param {ol.render.canvas.ReplayGroup} replayGroup Replay group.
 * @return {boolean} `true` if an image is loading.
 */
ol.renderer.canvas.VectorLayer.prototype.renderFeature = function(feature, resolution, pixelRatio, styles, replayGroup) {
  if (!styles) {
    return false;
  }
  var loading = false;
  if (Array.isArray(styles)) {
    for (var i = 0, ii = styles.length; i < ii; ++i) {
      loading = ol.renderer.vector.renderFeature(
          replayGroup, feature, styles[i],
          ol.renderer.vector.getSquaredTolerance(resolution, pixelRatio),
          this.handleStyleImageChange_, this) || loading;
    }
  } else {
    loading = ol.renderer.vector.renderFeature(
        replayGroup, feature, styles,
        ol.renderer.vector.getSquaredTolerance(resolution, pixelRatio),
        this.handleStyleImageChange_, this);
  }
  return loading;
};
