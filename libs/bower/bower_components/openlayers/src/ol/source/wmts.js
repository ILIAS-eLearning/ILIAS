goog.provide('ol.source.WMTS');

goog.require('ol');
goog.require('ol.TileUrlFunction');
goog.require('ol.array');
goog.require('ol.extent');
goog.require('ol.obj');
goog.require('ol.proj');
goog.require('ol.source.TileImage');
goog.require('ol.source.WMTSRequestEncoding');
goog.require('ol.tilegrid.WMTS');
goog.require('ol.uri');


/**
 * @classdesc
 * Layer source for tile data from WMTS servers.
 *
 * @constructor
 * @extends {ol.source.TileImage}
 * @param {olx.source.WMTSOptions} options WMTS options.
 * @api
 */
ol.source.WMTS = function(options) {

  // TODO: add support for TileMatrixLimits

  /**
   * @private
   * @type {string}
   */
  this.version_ = options.version !== undefined ? options.version : '1.0.0';

  /**
   * @private
   * @type {string}
   */
  this.format_ = options.format !== undefined ? options.format : 'image/jpeg';

  /**
   * @private
   * @type {!Object}
   */
  this.dimensions_ = options.dimensions !== undefined ? options.dimensions : {};

  /**
   * @private
   * @type {string}
   */
  this.layer_ = options.layer;

  /**
   * @private
   * @type {string}
   */
  this.matrixSet_ = options.matrixSet;

  /**
   * @private
   * @type {string}
   */
  this.style_ = options.style;

  var urls = options.urls;
  if (urls === undefined && options.url !== undefined) {
    urls = ol.TileUrlFunction.expandUrl(options.url);
  }

  // FIXME: should we guess this requestEncoding from options.url(s)
  //        structure? that would mean KVP only if a template is not provided.

  /**
   * @private
   * @type {ol.source.WMTSRequestEncoding}
   */
  this.requestEncoding_ = options.requestEncoding !== undefined ?
    /** @type {ol.source.WMTSRequestEncoding} */ (options.requestEncoding) :
    ol.source.WMTSRequestEncoding.KVP;

  var requestEncoding = this.requestEncoding_;

  // FIXME: should we create a default tileGrid?
  // we could issue a getCapabilities xhr to retrieve missing configuration
  var tileGrid = options.tileGrid;

  // context property names are lower case to allow for a case insensitive
  // replacement as some services use different naming conventions
  var context = {
    'layer': this.layer_,
    'style': this.style_,
    'tilematrixset': this.matrixSet_
  };

  if (requestEncoding == ol.source.WMTSRequestEncoding.KVP) {
    ol.obj.assign(context, {
      'Service': 'WMTS',
      'Request': 'GetTile',
      'Version': this.version_,
      'Format': this.format_
    });
  }

  var dimensions = this.dimensions_;

  /**
   * @param {string} template Template.
   * @return {ol.TileUrlFunctionType} Tile URL function.
   * @private
   */
  this.createFromWMTSTemplate_ = function(template) {

    // TODO: we may want to create our own appendParams function so that params
    // order conforms to wmts spec guidance, and so that we can avoid to escape
    // special template params

    template = (requestEncoding == ol.source.WMTSRequestEncoding.KVP) ?
      ol.uri.appendParams(template, context) :
      template.replace(/\{(\w+?)\}/g, function(m, p) {
        return (p.toLowerCase() in context) ? context[p.toLowerCase()] : m;
      });

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
          var localContext = {
            'TileMatrix': tileGrid.getMatrixId(tileCoord[0]),
            'TileCol': tileCoord[1],
            'TileRow': -tileCoord[2] - 1
          };
          ol.obj.assign(localContext, dimensions);
          var url = template;
          if (requestEncoding == ol.source.WMTSRequestEncoding.KVP) {
            url = ol.uri.appendParams(url, localContext);
          } else {
            url = url.replace(/\{(\w+?)\}/g, function(m, p) {
              return localContext[p];
            });
          }
          return url;
        }
      });
  };

  var tileUrlFunction = (urls && urls.length > 0) ?
    ol.TileUrlFunction.createFromTileUrlFunctions(
        urls.map(this.createFromWMTSTemplate_)) :
    ol.TileUrlFunction.nullTileUrlFunction;

  ol.source.TileImage.call(this, {
    attributions: options.attributions,
    cacheSize: options.cacheSize,
    crossOrigin: options.crossOrigin,
    logo: options.logo,
    projection: options.projection,
    reprojectionErrorThreshold: options.reprojectionErrorThreshold,
    tileClass: options.tileClass,
    tileGrid: tileGrid,
    tileLoadFunction: options.tileLoadFunction,
    tilePixelRatio: options.tilePixelRatio,
    tileUrlFunction: tileUrlFunction,
    urls: urls,
    wrapX: options.wrapX !== undefined ? options.wrapX : false,
    transition: options.transition
  });

  this.setKey(this.getKeyForDimensions_());

};
ol.inherits(ol.source.WMTS, ol.source.TileImage);

/**
 * Set the URLs to use for requests.
 * URLs may contain OCG conform URL Template Variables: {TileMatrix}, {TileRow}, {TileCol}.
 * @override
 */
ol.source.WMTS.prototype.setUrls = function(urls) {
  this.urls = urls;
  var key = urls.join('\n');
  this.setTileUrlFunction(this.fixedTileUrlFunction ?
    this.fixedTileUrlFunction.bind(this) :
    ol.TileUrlFunction.createFromTileUrlFunctions(urls.map(this.createFromWMTSTemplate_.bind(this))), key);
};

/**
 * Get the dimensions, i.e. those passed to the constructor through the
 * "dimensions" option, and possibly updated using the updateDimensions
 * method.
 * @return {!Object} Dimensions.
 * @api
 */
ol.source.WMTS.prototype.getDimensions = function() {
  return this.dimensions_;
};


/**
 * Return the image format of the WMTS source.
 * @return {string} Format.
 * @api
 */
ol.source.WMTS.prototype.getFormat = function() {
  return this.format_;
};


/**
 * Return the layer of the WMTS source.
 * @return {string} Layer.
 * @api
 */
ol.source.WMTS.prototype.getLayer = function() {
  return this.layer_;
};


/**
 * Return the matrix set of the WMTS source.
 * @return {string} MatrixSet.
 * @api
 */
ol.source.WMTS.prototype.getMatrixSet = function() {
  return this.matrixSet_;
};


/**
 * Return the request encoding, either "KVP" or "REST".
 * @return {ol.source.WMTSRequestEncoding} Request encoding.
 * @api
 */
ol.source.WMTS.prototype.getRequestEncoding = function() {
  return this.requestEncoding_;
};


/**
 * Return the style of the WMTS source.
 * @return {string} Style.
 * @api
 */
ol.source.WMTS.prototype.getStyle = function() {
  return this.style_;
};


/**
 * Return the version of the WMTS source.
 * @return {string} Version.
 * @api
 */
ol.source.WMTS.prototype.getVersion = function() {
  return this.version_;
};


/**
 * @private
 * @return {string} The key for the current dimensions.
 */
ol.source.WMTS.prototype.getKeyForDimensions_ = function() {
  var i = 0;
  var res = [];
  for (var key in this.dimensions_) {
    res[i++] = key + '-' + this.dimensions_[key];
  }
  return res.join('/');
};


/**
 * Update the dimensions.
 * @param {Object} dimensions Dimensions.
 * @api
 */
ol.source.WMTS.prototype.updateDimensions = function(dimensions) {
  ol.obj.assign(this.dimensions_, dimensions);
  this.setKey(this.getKeyForDimensions_());
};


/**
 * Generate source options from a capabilities object.
 * @param {Object} wmtsCap An object representing the capabilities document.
 * @param {Object} config Configuration properties for the layer.  Defaults for
 *                  the layer will apply if not provided.
 *
 * Required config properties:
 *  - layer - {string} The layer identifier.
 *
 * Optional config properties:
 *  - matrixSet - {string} The matrix set identifier, required if there is
 *       more than one matrix set in the layer capabilities.
 *  - projection - {string} The desired CRS when no matrixSet is specified.
 *       eg: "EPSG:3857". If the desired projection is not available,
 *       an error is thrown.
 *  - requestEncoding - {string} url encoding format for the layer. Default is
 *       the first tile url format found in the GetCapabilities response.
 *  - style - {string} The name of the style
 *  - format - {string} Image format for the layer. Default is the first
 *       format returned in the GetCapabilities response.
 *  - crossOrigin - {string|null|undefined} Cross origin. Default is `undefined`.
 * @return {?olx.source.WMTSOptions} WMTS source options object or `null` if the layer was not found.
 * @api
 */
ol.source.WMTS.optionsFromCapabilities = function(wmtsCap, config) {
  var layers = wmtsCap['Contents']['Layer'];
  var l = ol.array.find(layers, function(elt, index, array) {
    return elt['Identifier'] == config['layer'];
  });
  if (l === null) {
    return null;
  }
  var tileMatrixSets = wmtsCap['Contents']['TileMatrixSet'];
  var idx, matrixSet, matrixLimits;
  if (l['TileMatrixSetLink'].length > 1) {
    if ('projection' in config) {
      idx = ol.array.findIndex(l['TileMatrixSetLink'],
          function(elt, index, array) {
            var tileMatrixSet = ol.array.find(tileMatrixSets, function(el) {
              return el['Identifier'] == elt['TileMatrixSet'];
            });
            var supportedCRS = tileMatrixSet['SupportedCRS'];
            var proj1 = ol.proj.get(supportedCRS.replace(/urn:ogc:def:crs:(\w+):(.*:)?(\w+)$/, '$1:$3')) ||
                ol.proj.get(supportedCRS);
            var proj2 = ol.proj.get(config['projection']);
            if (proj1 && proj2) {
              return ol.proj.equivalent(proj1, proj2);
            } else {
              return supportedCRS == config['projection'];
            }
          });
    } else {
      idx = ol.array.findIndex(l['TileMatrixSetLink'],
          function(elt, index, array) {
            return elt['TileMatrixSet'] == config['matrixSet'];
          });
    }
  } else {
    idx = 0;
  }
  if (idx < 0) {
    idx = 0;
  }
  matrixSet = /** @type {string} */
    (l['TileMatrixSetLink'][idx]['TileMatrixSet']);
  matrixLimits = /** @type {Array.<Object>} */
    (l['TileMatrixSetLink'][idx]['TileMatrixSetLimits']);

  var format = /** @type {string} */ (l['Format'][0]);
  if ('format' in config) {
    format = config['format'];
  }
  idx = ol.array.findIndex(l['Style'], function(elt, index, array) {
    if ('style' in config) {
      return elt['Title'] == config['style'];
    } else {
      return elt['isDefault'];
    }
  });
  if (idx < 0) {
    idx = 0;
  }
  var style = /** @type {string} */ (l['Style'][idx]['Identifier']);

  var dimensions = {};
  if ('Dimension' in l) {
    l['Dimension'].forEach(function(elt, index, array) {
      var key = elt['Identifier'];
      var value = elt['Default'];
      if (value === undefined) {
        value = elt['Value'][0];
      }
      dimensions[key] = value;
    });
  }

  var matrixSets = wmtsCap['Contents']['TileMatrixSet'];
  var matrixSetObj = ol.array.find(matrixSets, function(elt, index, array) {
    return elt['Identifier'] == matrixSet;
  });

  var projection;
  var code = matrixSetObj['SupportedCRS'];
  if (code) {
    projection = ol.proj.get(code.replace(/urn:ogc:def:crs:(\w+):(.*:)?(\w+)$/, '$1:$3')) ||
        ol.proj.get(code);
  }
  if ('projection' in config) {
    var projConfig = ol.proj.get(config['projection']);
    if (projConfig) {
      if (!projection || ol.proj.equivalent(projConfig, projection)) {
        projection = projConfig;
      }
    }
  }

  var wgs84BoundingBox = l['WGS84BoundingBox'];
  var extent, wrapX;
  if (wgs84BoundingBox !== undefined) {
    var wgs84ProjectionExtent = ol.proj.get('EPSG:4326').getExtent();
    wrapX = (wgs84BoundingBox[0] == wgs84ProjectionExtent[0] &&
        wgs84BoundingBox[2] == wgs84ProjectionExtent[2]);
    extent = ol.proj.transformExtent(
        wgs84BoundingBox, 'EPSG:4326', projection);
    var projectionExtent = projection.getExtent();
    if (projectionExtent) {
      // If possible, do a sanity check on the extent - it should never be
      // bigger than the validity extent of the projection of a matrix set.
      if (!ol.extent.containsExtent(projectionExtent, extent)) {
        extent = undefined;
      }
    }
  }

  var tileGrid = ol.tilegrid.WMTS.createFromCapabilitiesMatrixSet(
      matrixSetObj, extent, matrixLimits);

  /** @type {!Array.<string>} */
  var urls = [];
  var requestEncoding = config['requestEncoding'];
  requestEncoding = requestEncoding !== undefined ? requestEncoding : '';

  if ('OperationsMetadata' in wmtsCap && 'GetTile' in wmtsCap['OperationsMetadata']) {
    var gets = wmtsCap['OperationsMetadata']['GetTile']['DCP']['HTTP']['Get'];

    for (var i = 0, ii = gets.length; i < ii; ++i) {
      if (gets[i]['Constraint']) {
        var constraint = ol.array.find(gets[i]['Constraint'], function(element) {
          return element['name'] == 'GetEncoding';
        });
        var encodings = constraint['AllowedValues']['Value'];

        if (requestEncoding === '') {
          // requestEncoding not provided, use the first encoding from the list
          requestEncoding = encodings[0];
        }
        if (requestEncoding === ol.source.WMTSRequestEncoding.KVP) {
          if (ol.array.includes(encodings, ol.source.WMTSRequestEncoding.KVP)) {
            urls.push(/** @type {string} */ (gets[i]['href']));
          }
        } else {
          break;
        }
      } else if (gets[i]['href']) {
        requestEncoding = ol.source.WMTSRequestEncoding.KVP;
        urls.push(/** @type {string} */ (gets[i]['href']));
      }
    }
  }
  if (urls.length === 0) {
    requestEncoding = ol.source.WMTSRequestEncoding.REST;
    l['ResourceURL'].forEach(function(element) {
      if (element['resourceType'] === 'tile') {
        format = element['format'];
        urls.push(/** @type {string} */ (element['template']));
      }
    });
  }

  return {
    urls: urls,
    layer: config['layer'],
    matrixSet: matrixSet,
    format: format,
    projection: projection,
    requestEncoding: requestEncoding,
    tileGrid: tileGrid,
    style: style,
    dimensions: dimensions,
    wrapX: wrapX,
    crossOrigin: config['crossOrigin']
  };
};
