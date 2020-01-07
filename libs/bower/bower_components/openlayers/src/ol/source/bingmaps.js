goog.provide('ol.source.BingMaps');

goog.require('ol');
goog.require('ol.TileUrlFunction');
goog.require('ol.extent');
goog.require('ol.net');
goog.require('ol.proj');
goog.require('ol.source.State');
goog.require('ol.source.TileImage');
goog.require('ol.tilecoord');
goog.require('ol.tilegrid');


/**
 * @classdesc
 * Layer source for Bing Maps tile data.
 *
 * @constructor
 * @extends {ol.source.TileImage}
 * @param {olx.source.BingMapsOptions} options Bing Maps options.
 * @api
 */
ol.source.BingMaps = function(options) {

  /**
   * @private
   * @type {boolean}
   */
  this.hidpi_ = options.hidpi !== undefined ? options.hidpi : false;

  ol.source.TileImage.call(this, {
    cacheSize: options.cacheSize,
    crossOrigin: 'anonymous',
    opaque: true,
    projection: ol.proj.get('EPSG:3857'),
    reprojectionErrorThreshold: options.reprojectionErrorThreshold,
    state: ol.source.State.LOADING,
    tileLoadFunction: options.tileLoadFunction,
    tilePixelRatio: this.hidpi_ ? 2 : 1,
    wrapX: options.wrapX !== undefined ? options.wrapX : true,
    transition: options.transition
  });

  /**
   * @private
   * @type {string}
   */
  this.culture_ = options.culture !== undefined ? options.culture : 'en-us';

  /**
   * @private
   * @type {number}
   */
  this.maxZoom_ = options.maxZoom !== undefined ? options.maxZoom : -1;

  /**
   * @private
   * @type {string}
   */
  this.apiKey_ = options.key;

  /**
   * @private
   * @type {string}
   */
  this.imagerySet_ = options.imagerySet;

  var url = 'https://dev.virtualearth.net/REST/v1/Imagery/Metadata/' +
      this.imagerySet_ +
      '?uriScheme=https&include=ImageryProviders&key=' + this.apiKey_ +
      '&c=' + this.culture_;

  ol.net.jsonp(url, this.handleImageryMetadataResponse.bind(this), undefined,
      'jsonp');

};
ol.inherits(ol.source.BingMaps, ol.source.TileImage);


/**
 * The attribution containing a link to the Microsoft® Bing™ Maps Platform APIs’
 * Terms Of Use.
 * @const
 * @type {string}
 * @api
 */
ol.source.BingMaps.TOS_ATTRIBUTION = '<a class="ol-attribution-bing-tos" ' +
      'href="https://www.microsoft.com/maps/product/terms.html">' +
      'Terms of Use</a>';


/**
 * Get the api key used for this source.
 *
 * @return {string} The api key.
 * @api
 */
ol.source.BingMaps.prototype.getApiKey = function() {
  return this.apiKey_;
};


/**
 * Get the imagery set associated with this source.
 *
 * @return {string} The imagery set.
 * @api
 */
ol.source.BingMaps.prototype.getImagerySet = function() {
  return this.imagerySet_;
};


/**
 * @param {BingMapsImageryMetadataResponse} response Response.
 */
ol.source.BingMaps.prototype.handleImageryMetadataResponse = function(response) {
  if (response.statusCode != 200 ||
      response.statusDescription != 'OK' ||
      response.authenticationResultCode != 'ValidCredentials' ||
      response.resourceSets.length != 1 ||
      response.resourceSets[0].resources.length != 1) {
    this.setState(ol.source.State.ERROR);
    return;
  }

  var brandLogoUri = response.brandLogoUri;
  if (brandLogoUri.indexOf('https') == -1) {
    brandLogoUri = brandLogoUri.replace('http', 'https');
  }
  //var copyright = response.copyright;  // FIXME do we need to display this?
  var resource = response.resourceSets[0].resources[0];
  var maxZoom = this.maxZoom_ == -1 ? resource.zoomMax : this.maxZoom_;

  var sourceProjection = this.getProjection();
  var extent = ol.tilegrid.extentFromProjection(sourceProjection);
  var tileSize = resource.imageWidth == resource.imageHeight ?
    resource.imageWidth : [resource.imageWidth, resource.imageHeight];
  var tileGrid = ol.tilegrid.createXYZ({
    extent: extent,
    minZoom: resource.zoomMin,
    maxZoom: maxZoom,
    tileSize: tileSize / (this.hidpi_ ? 2 : 1)
  });
  this.tileGrid = tileGrid;

  var culture = this.culture_;
  var hidpi = this.hidpi_;
  this.tileUrlFunction = ol.TileUrlFunction.createFromTileUrlFunctions(
      resource.imageUrlSubdomains.map(function(subdomain) {
        var quadKeyTileCoord = [0, 0, 0];
        var imageUrl = resource.imageUrl
            .replace('{subdomain}', subdomain)
            .replace('{culture}', culture);
        return (
          /**
           * @param {ol.TileCoord} tileCoord Tile coordinate.
           * @param {number} pixelRatio Pixel ratio.
           * @param {ol.proj.Projection} projection Projection.
           * @return {string|undefined} Tile URL.
           */
          function(tileCoord, pixelRatio, projection) {
            if (!tileCoord) {
              return undefined;
            } else {
              ol.tilecoord.createOrUpdate(tileCoord[0], tileCoord[1],
                  -tileCoord[2] - 1, quadKeyTileCoord);
              var url = imageUrl;
              if (hidpi) {
                url += '&dpi=d1&device=mobile';
              }
              return url.replace('{quadkey}', ol.tilecoord.quadKey(
                  quadKeyTileCoord));
            }
          });
      }));

  if (resource.imageryProviders) {
    var transform = ol.proj.getTransformFromProjections(
        ol.proj.get('EPSG:4326'), this.getProjection());

    this.setAttributions(function(frameState) {
      var attributions = [];
      var zoom = frameState.viewState.zoom;
      resource.imageryProviders.map(function(imageryProvider) {
        var intersects = false;
        var coverageAreas = imageryProvider.coverageAreas;
        for (var i = 0, ii = coverageAreas.length; i < ii; ++i) {
          var coverageArea = coverageAreas[i];
          if (zoom >= coverageArea.zoomMin && zoom <= coverageArea.zoomMax) {
            var bbox = coverageArea.bbox;
            var epsg4326Extent = [bbox[1], bbox[0], bbox[3], bbox[2]];
            var extent = ol.extent.applyTransform(epsg4326Extent, transform);
            if (ol.extent.intersects(extent, frameState.extent)) {
              intersects = true;
              break;
            }
          }
        }
        if (intersects) {
          attributions.push(imageryProvider.attribution);
        }
      });

      attributions.push(ol.source.BingMaps.TOS_ATTRIBUTION);
      return attributions;
    });
  }

  this.setLogo(brandLogoUri);

  this.setState(ol.source.State.READY);
};
