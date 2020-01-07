// FIXME check order of async callbacks

/**
 * @see http://mapbox.com/developers/api/
 */

goog.provide('ol.source.TileJSON');

goog.require('ol');
goog.require('ol.TileUrlFunction');
goog.require('ol.asserts');
goog.require('ol.extent');
goog.require('ol.net');
goog.require('ol.proj');
goog.require('ol.source.State');
goog.require('ol.source.TileImage');
goog.require('ol.tilegrid');


/**
 * @classdesc
 * Layer source for tile data in TileJSON format.
 *
 * @constructor
 * @extends {ol.source.TileImage}
 * @param {olx.source.TileJSONOptions} options TileJSON options.
 * @api
 */
ol.source.TileJSON = function(options) {

  /**
   * @type {TileJSON}
   * @private
   */
  this.tileJSON_ = null;

  ol.source.TileImage.call(this, {
    attributions: options.attributions,
    cacheSize: options.cacheSize,
    crossOrigin: options.crossOrigin,
    projection: ol.proj.get('EPSG:3857'),
    reprojectionErrorThreshold: options.reprojectionErrorThreshold,
    state: ol.source.State.LOADING,
    tileLoadFunction: options.tileLoadFunction,
    wrapX: options.wrapX !== undefined ? options.wrapX : true,
    transition: options.transition
  });

  if (options.url) {
    if (options.jsonp) {
      ol.net.jsonp(options.url, this.handleTileJSONResponse.bind(this),
          this.handleTileJSONError.bind(this));
    } else {
      var client = new XMLHttpRequest();
      client.addEventListener('load', this.onXHRLoad_.bind(this));
      client.addEventListener('error', this.onXHRError_.bind(this));
      client.open('GET', options.url);
      client.send();
    }
  } else if (options.tileJSON) {
    this.handleTileJSONResponse(options.tileJSON);
  } else {
    ol.asserts.assert(false, 51); // Either `url` or `tileJSON` options must be provided
  }

};
ol.inherits(ol.source.TileJSON, ol.source.TileImage);


/**
 * @private
 * @param {Event} event The load event.
 */
ol.source.TileJSON.prototype.onXHRLoad_ = function(event) {
  var client = /** @type {XMLHttpRequest} */ (event.target);
  // status will be 0 for file:// urls
  if (!client.status || client.status >= 200 && client.status < 300) {
    var response;
    try {
      response = /** @type {TileJSON} */(JSON.parse(client.responseText));
    } catch (err) {
      this.handleTileJSONError();
      return;
    }
    this.handleTileJSONResponse(response);
  } else {
    this.handleTileJSONError();
  }
};


/**
 * @private
 * @param {Event} event The error event.
 */
ol.source.TileJSON.prototype.onXHRError_ = function(event) {
  this.handleTileJSONError();
};


/**
 * @return {TileJSON} The tilejson object.
 * @api
 */
ol.source.TileJSON.prototype.getTileJSON = function() {
  return this.tileJSON_;
};


/**
 * @protected
 * @param {TileJSON} tileJSON Tile JSON.
 */
ol.source.TileJSON.prototype.handleTileJSONResponse = function(tileJSON) {

  var epsg4326Projection = ol.proj.get('EPSG:4326');

  var sourceProjection = this.getProjection();
  var extent;
  if (tileJSON.bounds !== undefined) {
    var transform = ol.proj.getTransformFromProjections(
        epsg4326Projection, sourceProjection);
    extent = ol.extent.applyTransform(tileJSON.bounds, transform);
  }

  var minZoom = tileJSON.minzoom || 0;
  var maxZoom = tileJSON.maxzoom || 22;
  var tileGrid = ol.tilegrid.createXYZ({
    extent: ol.tilegrid.extentFromProjection(sourceProjection),
    maxZoom: maxZoom,
    minZoom: minZoom
  });
  this.tileGrid = tileGrid;

  this.tileUrlFunction =
      ol.TileUrlFunction.createFromTemplates(tileJSON.tiles, tileGrid);

  if (tileJSON.attribution !== undefined && !this.getAttributions2()) {
    var attributionExtent = extent !== undefined ?
      extent : epsg4326Projection.getExtent();

    this.setAttributions(function(frameState) {
      if (ol.extent.intersects(attributionExtent, frameState.extent)) {
        return [tileJSON.attribution];
      }
      return null;
    });

  }
  this.tileJSON_ = tileJSON;
  this.setState(ol.source.State.READY);

};


/**
 * @protected
 */
ol.source.TileJSON.prototype.handleTileJSONError = function() {
  this.setState(ol.source.State.ERROR);
};
