/*
ol-mapbox-style - Use Mapbox Style objects with OpenLayers
Copyright 2016-present ol-mapbox-style contributors
License: https://raw.githubusercontent.com/openlayers/ol-mapbox-style/master/LICENSE
*/

import GeoJSON from 'ol/format/GeoJSON.js';
import MVT from 'ol/format/MVT.js';
import Map from 'ol/Map.js';
import TileGrid from 'ol/tilegrid/TileGrid.js';
import TileJSON from 'ol/source/TileJSON.js';
import TileLayer from 'ol/layer/Tile.js';
import VectorLayer from 'ol/layer/Vector.js';
import VectorSource from 'ol/source/Vector.js';
import VectorTileLayer from 'ol/layer/VectorTile.js';
import VectorTileSource from 'ol/source/VectorTile.js';
import View from 'ol/View.js';
import applyStyleFunction, {
  _colorWithOpacity,
  getValue,
} from './stylefunction.js';
import googleFonts from 'webfont-matcher/lib/fonts/google.js';
import mb2css from 'mapbox-to-css-font';
import {Color} from '@mapbox/mapbox-gl-style-spec';
import {assign, defaultResolutions} from './util.js';
import {createXYZ} from 'ol/tilegrid.js';
import {fromLonLat} from 'ol/proj.js';
import {unByKey} from 'ol/Observable.js';

/**
 * @typedef {import("ol/Map").default} PluggableMap
 * @typedef {import("ol/layer/Layer").default} Layer
 * @typedef {import("ol/source/Source").default} Source
 * @private
 */

const tilejsonCache = {};

const fontFamilyRegEx = /font-family: ?([^;]*);/;
const stripQuotesRegEx = /("|')/g;
let loadedFontFamilies;
function hasFontFamily(family) {
  if (!loadedFontFamilies) {
    loadedFontFamilies = {};
    const styleSheets = document.styleSheets;
    for (let i = 0, ii = styleSheets.length; i < ii; ++i) {
      const styleSheet = /** @type {CSSStyleSheet} */ (styleSheets[i]);
      try {
        const cssRules = styleSheet.rules || styleSheet.cssRules;
        if (cssRules) {
          for (let j = 0, jj = cssRules.length; j < jj; ++j) {
            const cssRule = cssRules[j];
            if (cssRule.type == 5) {
              const match = cssRule.cssText.match(fontFamilyRegEx);
              loadedFontFamilies[match[1].replace(stripQuotesRegEx, '')] = true;
            }
          }
        }
      } catch (e) {
        // empty catch block
      }
    }
  }
  return family in loadedFontFamilies;
}

const processedFontFamilies = {};
const googleFamilies = googleFonts.getNames();

/**
 * @private
 * @param {Array} fonts Fonts.
 * @return {Array} Processed fonts.
 */
function getFonts(fonts) {
  const fontsKey = fonts.toString();
  if (fontsKey in processedFontFamilies) {
    return fonts;
  }
  const googleFontDescriptions = fonts.map(function (font) {
    const parts = mb2css(font, 1).split(' ');
    return [parts.slice(3).join(' ').replace(/"/g, ''), parts[1] + parts[0]];
  });
  for (let i = 0, ii = googleFontDescriptions.length; i < ii; ++i) {
    const googleFontDescription = googleFontDescriptions[i];
    const family = googleFontDescription[0];
    if (!hasFontFamily(family) && googleFamilies.indexOf(family) !== -1) {
      const fontUrl =
        'https://fonts.googleapis.com/css?family=' +
        family.replace(/ /g, '+') +
        ':' +
        googleFontDescription[1];
      if (!document.querySelector('link[href="' + fontUrl + '"]')) {
        const markup = document.createElement('link');
        markup.href = fontUrl;
        markup.rel = 'stylesheet';
        document.head.appendChild(markup);
      }
    }
  }
  processedFontFamilies[fontsKey] = true;
  return fonts;
}

const spriteRegEx = /^(.*)(\?.*)$/;

function withPath(url, path) {
  if (path && url.indexOf('.') === 0) {
    url = path + url;
  }
  return url;
}

function toSpriteUrl(url, path, extension) {
  url = withPath(url, path);
  const parts = url.match(spriteRegEx);
  return parts
    ? parts[1] + extension + (parts.length > 2 ? parts[2] : '')
    : url + extension;
}

/**
 * ```js
 * import {applyStyle} from 'ol-mapbox-style';
 * ```
 *
 * Applies a style function to an `ol.layer.VectorTile` or `ol.layer.Vector`
 * with an `ol.source.VectorTile` or an `ol.source.Vector`. The style function
 * will render all layers from the `glStyle` object that use the specified
 * `source`, or a subset of layers from the same source. The source needs to be
 * a `"type": "vector"` or `"type": "geojson"` source.
 *
 * Two additional properties will be set on the provided layer:
 *
 *  * `mapbox-source`: The `id` of the Mapbox Style document's source that the
 *    OpenLayers layer was created from. Usually `apply()` creates one
 *    OpenLayers layer per Mapbox Style source, unless the layer stack has
 *    layers from different sources in between.
 *  * `mapbox-layers`: The `id`s of the Mapbox Style document's layers that are
 *    included in the OpenLayers layer.
 *
 * @param {VectorTileLayer|VectorLayer} layer OpenLayers layer.
 * @param {string|Object} glStyle Mapbox Style object.
 * @param {string|Array<string>} source `source` key or an array of layer `id`s from the
 * Mapbox Style object. When a `source` key is provided, all layers for the
 * specified source will be included in the style function. When layer `id`s
 * are provided, they must be from layers that use the same source.
 * @param {string} [path=undefined] Path of the style file. Only required when
 * a relative path is used with the `"sprite"` property of the style.
 * @param {Array<number>} [resolutions=undefined] Resolutions for mapping resolution to zoom level.
 * @return {Promise} Promise which will be resolved when the style can be used
 * for rendering.
 */
export function applyStyle(layer, glStyle, source, path, resolutions) {
  return new Promise(function (resolve, reject) {
    // TODO: figure out where best place to check source type is
    // Note that the source arg is an array of gl layer ids and each must be
    // dereferenced to get source type to validate
    if (typeof glStyle != 'object') {
      glStyle = JSON.parse(glStyle);
    }
    if (glStyle.version != 8) {
      return reject(new Error('glStyle version 8 required.'));
    }
    if (!(layer instanceof VectorLayer || layer instanceof VectorTileLayer)) {
      return reject(
        new Error('Can only apply to VectorLayer or VectorTileLayer')
      );
    }

    let spriteScale, spriteData, spriteImageUrl, style;
    function onChange() {
      if (!style && (!glStyle.sprite || spriteData)) {
        style = applyStyleFunction(
          layer,
          glStyle,
          source,
          resolutions,
          spriteData,
          spriteImageUrl,
          getFonts
        );
        if (!layer.getStyle()) {
          reject(new Error(`Nothing to show for source [${source}]`));
        } else {
          resolve();
        }
      } else if (style) {
        layer.setStyle(style);
        resolve();
      } else {
        reject(new Error('Something went wrong trying to apply style.'));
      }
    }

    if (glStyle.sprite) {
      spriteScale = window.devicePixelRatio >= 1.5 ? 0.5 : 1;
      const sizeFactor = spriteScale == 0.5 ? '@2x' : '';
      let spriteUrl = toSpriteUrl(glStyle.sprite, path, sizeFactor + '.json');

      fetch(spriteUrl, {credentials: 'same-origin'})
        .then(function (response) {
          if (!response.ok && sizeFactor !== '') {
            spriteUrl = toSpriteUrl(glStyle.sprite, path, '.json');
            return fetch(spriteUrl, {credentials: 'same-origin'});
          } else {
            return response;
          }
        })
        .then(function (response) {
          if (response.ok) {
            return response.json();
          } else {
            reject(
              new Error(
                `Problem fetching sprite from ${spriteUrl}: ${response.statusText}`
              )
            );
          }
        })
        .then(function (spritesJson) {
          if (spritesJson === undefined) {
            return reject(new Error('No sprites found.'));
          }
          spriteData = spritesJson;
          spriteImageUrl = toSpriteUrl(
            glStyle.sprite,
            path,
            sizeFactor + '.png'
          );
          onChange();
        })
        .catch(function (err) {
          reject(
            new Error(`Sprites cannot be loaded: ${spriteUrl}: ${err.message}`)
          );
        });
    } else {
      onChange();
    }
  });
}

const emptyObj = {};

function setBackground(mapOrLayer, layer) {
  const background = {
    id: layer.id,
    type: layer.type,
  };
  const functionCache = {};
  function updateStyle(resolution) {
    const layout = layer.layout || {};
    const paint = layer.paint || {};
    background['paint'] = paint;
    const zoom =
      typeof mapOrLayer.getSource === 'function'
        ? mapOrLayer.getSource().getTileGrid().getZForResolution(resolution)
        : mapOrLayer.getView().getZoom();
    const element =
      typeof mapOrLayer.getTargetElement === 'function'
        ? mapOrLayer.getTargetElement()
        : undefined;
    let bg, opacity;
    if (paint['background-color'] !== undefined) {
      bg = getValue(
        background,
        'paint',
        'background-color',
        zoom,
        emptyObj,
        functionCache
      );
      if (element) {
        element.style.background = Color.parse(bg).toString();
      }
    }
    if (paint['background-opacity'] !== undefined) {
      opacity = getValue(
        background,
        'paint',
        'background-opacity',
        zoom,
        emptyObj,
        functionCache
      );
      if (element) {
        element.style.opacity = opacity;
      }
    }
    if (layout.visibility == 'none') {
      if (element) {
        element.style.backgroundColor = '';
        element.style.opacity = '';
      }
      return undefined;
    }
    return _colorWithOpacity(bg, opacity);
  }
  if (typeof mapOrLayer.getTargetElement === 'function') {
    if (mapOrLayer.getTargetElement()) {
      updateStyle();
    }
    mapOrLayer.on(['change:resolution', 'change:target'], updateStyle);
  } else if (typeof mapOrLayer.setBackground === 'function') {
    mapOrLayer.setBackground(updateStyle);
  } else {
    throw new Error('Unable to apply background.');
  }
}

/**
 * ```js
 * import {applyBackground} from 'ol-mapbox-style';
 * ```
 * Applies properties of the Mapbox Style's first `background` layer to the
 * provided map or VectorTile layer.
 * @param {PluggableMap|VectorTileLayer} mapOrLayer OpenLayers Map or VectorTile layer.
 * @param {Object} glStyle Mapbox Style object.
 */
export function applyBackground(mapOrLayer, glStyle) {
  glStyle.layers.some(function (l) {
    if (l.type == 'background') {
      setBackground(mapOrLayer, l);
      return true;
    }
  });
}

function getSourceIdByRef(layers, ref) {
  let sourceId;
  layers.some(function (layer) {
    if (layer.id == ref) {
      sourceId = layer.source;
      return true;
    }
  });
  return sourceId;
}

function extentFromTileJSON(tileJSON) {
  const bounds = tileJSON.bounds;
  if (bounds) {
    const ll = fromLonLat([bounds[0], bounds[1]]);
    const tr = fromLonLat([bounds[2], bounds[3]]);
    return [ll[0], ll[1], tr[0], tr[1]];
  }
}

/**
 * Creates an OpenLayers VectorTile source for a gl source entry.
 * @param {Object} glSource "source" entry from a Mapbox Style object.
 * @param {string|undefined} url URL to use for the source. This is expected to be the complete http(s) url,
 * with access key applied.When not provided, `glSource.tiles` has to be set.
 * @return {Promise<import("ol/source/VectorTile").default>} Promise resolving to a VectorTile source.
 * @private
 */
export function setupVectorSource(glSource, url) {
  glSource = assign({}, glSource);
  const cacheKey = [url, JSON.stringify(glSource)].toString();
  let tilejson = tilejsonCache[cacheKey];
  if (!tilejson) {
    tilejson = new TileJSON({
      url: glSource.tiles ? undefined : url,
      tileJSON: glSource.tiles ? glSource : undefined,
    });
    tilejsonCache[cacheKey] = tilejson;
  }
  return new Promise((resolve) => {
    const key = tilejson.on('change', function () {
      const state = tilejson.getState();
      if (state === 'ready') {
        const tileJSONDoc = tilejson.getTileJSON();
        const tiles = Array.isArray(tileJSONDoc.tiles)
          ? tileJSONDoc.tiles
          : [tileJSONDoc.tiles];
        if (url) {
          for (let i = 0, ii = tiles.length; i < ii; ++i) {
            tiles[i] = decodeURI(new URL(tiles[i], url).href);
          }
        }
        const tileGrid = tilejson.getTileGrid();
        const extent = extentFromTileJSON(tileJSONDoc);
        const minZoom = tileJSONDoc.minzoom || 0;
        const maxZoom = tileJSONDoc.maxzoom || 22;
        let source = tilejson.get('ol-source');
        if (source === undefined) {
          source = new VectorTileSource({
            attributions: tilejson.getAttributions(),
            format: new MVT(),
            tileGrid: new TileGrid({
              origin: tileGrid.getOrigin(0),
              extent: extent || tileGrid.getExtent(),
              minZoom: minZoom,
              resolutions: defaultResolutions.slice(0, maxZoom + 1),
              tileSize: 512,
            }),
            urls: tiles,
          });
          tilejson.set('ol-source', source);
        }
        unByKey(key);
        resolve(source);
      } else if (state === 'error') {
        tilejson.set('ol-source', null);
        unByKey(key);
        resolve(undefined);
      }
    });
    if (tilejson.getState() === 'ready') {
      tilejson.changed();
    }
  });
}

function setupVectorLayer(glSource, url) {
  const layer = new VectorTileLayer({
    declutter: true,
    visible: false,
  });
  setupVectorSource(glSource, url).then((source) => {
    layer.setSource(source);
  });
  return layer;
}

function setupRasterLayer(glSource, url) {
  const layer = new TileLayer();
  const source = new TileJSON({
    transition: 0,
    url: glSource.tiles ? undefined : url,
    tileJSON: glSource.tiles ? glSource : undefined,
    crossOrigin: 'anonymous',
  });
  const key = source.on('change', function () {
    const state = source.getState();
    if (state === 'ready') {
      unByKey(key);
      const tileJSONDoc = /** @type {Object} */ (source.getTileJSON());
      const extent = extentFromTileJSON(tileJSONDoc);
      const tileGrid = source.getTileGrid();
      const tileSize = glSource.tileSize || tileJSONDoc.tileSize || 512;
      const minZoom = tileJSONDoc.minzoom || 0;
      const maxZoom = tileJSONDoc.maxzoom || 22;
      // Only works when using ES modules
      //@ts-ignore
      source.tileGrid = new TileGrid({
        origin: tileGrid.getOrigin(0),
        extent: extent || tileGrid.getExtent(),
        minZoom: minZoom,
        resolutions: createXYZ({
          maxZoom: maxZoom,
          tileSize: tileSize,
        }).getResolutions(),
        tileSize: tileSize,
      });
      layer.setSource(source);
    } else if (state === 'error') {
      unByKey(key);
      layer.setSource(undefined);
    }
  });
  source.setTileLoadFunction(function (tile, src) {
    if (src.indexOf('{bbox-epsg-3857}') != -1) {
      const bbox = source.getTileGrid().getTileCoordExtent(tile.getTileCoord());
      src = src.replace('{bbox-epsg-3857}', bbox.toString());
    }
    const img = /** @type {import("ol/ImageTile").default} */ (tile).getImage();
    /** @type {HTMLImageElement} */ (img).src = src;
  });
  return layer;
}

const geoJsonFormat = new GeoJSON();
function setupGeoJSONLayer(glSource, path) {
  const data = glSource.data;
  let features, geoJsonUrl;
  if (typeof data == 'string') {
    geoJsonUrl = withPath(data, path);
  } else {
    features = geoJsonFormat.readFeatures(data, {
      featureProjection: 'EPSG:3857',
    });
  }
  return new VectorLayer({
    declutter: true,
    source: new VectorSource({
      attributions: glSource.attribution,
      features: features,
      format: geoJsonFormat,
      url: geoJsonUrl,
    }),
    visible: false,
  });
}

function updateRasterLayerProperties(glLayer, layer, view, functionCache) {
  const zoom = view.getZoom();
  const opacity = getValue(
    glLayer,
    'paint',
    'raster-opacity',
    zoom,
    emptyObj,
    functionCache
  );
  layer.setOpacity(opacity);
}

function processStyle(glStyle, map, baseUrl, host, path, accessToken = '') {
  const promises = [];
  let view = map.getView();
  if (!view.isDef() && !view.getRotation() && !view.getResolutions()) {
    view = new View(
      assign(view.getProperties(), {
        maxResolution: defaultResolutions[0],
      })
    );
    map.setView(view);
  }

  if ('center' in glStyle && !view.getCenter()) {
    view.setCenter(fromLonLat(glStyle.center));
  }
  if ('zoom' in glStyle && view.getZoom() === undefined) {
    view.setResolution(defaultResolutions[0] / Math.pow(2, glStyle.zoom));
  }
  if (!view.getCenter() || view.getZoom() === undefined) {
    view.fit(view.getProjection().getExtent(), {
      nearest: true,
      size: map.getSize(),
    });
  }
  if (glStyle.sprite) {
    if (glStyle.sprite.indexOf('mapbox://') == 0) {
      glStyle.sprite = baseUrl + '/sprite' + accessToken;
    } else if (glStyle.sprite.indexOf('http') != 0) {
      glStyle.sprite = (host ? host + path : '') + glStyle.sprite + accessToken;
    }
  }

  const glLayers = glStyle.layers;
  let layerIds = [];

  let glLayer, glSource, glSourceId, id, layer, url;
  for (let i = 0, ii = glLayers.length; i < ii; ++i) {
    glLayer = glLayers[i];
    const type = glLayer.type;
    if (type == 'heatmap' || type == 'hillshade') {
      //FIXME Unsupported layer type
    } else if (type == 'background') {
      setBackground(map, glLayer);
    } else {
      id = glLayer.source || getSourceIdByRef(glLayers, glLayer.ref);
      // this technique assumes gl layers will be in a particular order
      if (id != glSourceId) {
        if (layerIds.length) {
          promises.push(finalizeLayer(layer, layerIds, glStyle, path, map));
          layerIds = [];
        }
        glSource = glStyle.sources[id];
        url = glSource.url;
        if (url) {
          url = withPath(url, path);
          if (url.indexOf('mapbox://') == 0) {
            const mapid = url.replace('mapbox://', '');
            glSource.tiles = ['a', 'b', 'c', 'd'].map(function (host) {
              return (
                'https://' +
                host +
                '.tiles.mapbox.com/v4/' +
                mapid +
                '/{z}/{x}/{y}.' +
                (glSource.type == 'vector' ? 'vector.pbf' : 'png') +
                accessToken
              );
            });
          } else if (url.indexOf('/') === 0 && host.indexOf('http') === 0) {
            url = host + url;
          }
        }
        if (glSource.tiles) {
          glSource.tiles = glSource.tiles.map((url) => withPath(url, path));
        }

        if (glSource.type == 'vector') {
          layer = setupVectorLayer(glSource, url);
        } else if (glSource.type == 'raster') {
          layer = setupRasterLayer(glSource, url);
          layer.setVisible(
            glLayer.layout ? glLayer.layout.visibility !== 'none' : true
          );
          const functionCache = {};
          view.on(
            'change:resolution',
            updateRasterLayerProperties.bind(
              this,
              glLayer,
              layer,
              view,
              functionCache
            )
          );
          updateRasterLayerProperties(glLayer, layer, view, functionCache);
        } else if (glSource.type == 'geojson') {
          layer = setupGeoJSONLayer(glSource, path);
        }
        glSourceId = id;
        if (layer) {
          layer.set('mapbox-source', glSourceId);
        }
      }
      layerIds.push(glLayer.id);
    }
  }
  promises.push(finalizeLayer(layer, layerIds, glStyle, path, map));
  map.set('mapbox-style', glStyle);
  return Promise.all(promises);
}

/**
 * ```js
 * import olms from 'ol-mapbox-style';
 * ```
 *
 * Loads and applies a Mapbox Style object to an OpenLayers Map. This includes
 * the map background, the layers, the center and the zoom.
 *
 * The center and zoom will only be set if present in the Mapbox Style document,
 * and if not already set on the OpenLayers map.
 *
 * Layers will be added to the OpenLayers map, without affecting any layers that
 * might already be set on the map.
 *
 * Layers added by `apply()` will have two additional properties:
 *
 *  * `mapbox-source`: The `id` of the Mapbox Style document's source that the
 *    OpenLayers layer was created from. Usually `apply()` creates one
 *    OpenLayers layer per Mapbox Style source, unless the layer stack has
 *    layers from different sources in between.
 *  * `mapbox-layers`: The `id`s of the Mapbox Style document's layers that are
 *    included in the OpenLayers layer.
 *
 * This function sets an additional `mapbox-style` property on the OpenLayers
 * map instance, which holds the Mapbox Style object.
 *
 * @param {PluggableMap|HTMLElement|string} map Either an existing OpenLayers Map
 * instance, or a HTML element, or the id of a HTML element that will be the
 * target of a new OpenLayers Map.
 * @param {string|Object} style JSON style object or style url pointing to a
 * Mapbox Style object. When using Mapbox APIs, the url must contain an access
 * token and look like
 * `https://api.mapbox.com/styles/v1/mapbox/bright-v9?access_token=[your_access_token_here]`.
 * When passed as JSON style object, all OpenLayers layers created by `apply()`
 * will be immediately available, but they may not have a source yet (i.e. when
 * they are defined by a TileJSON url in the Mapbox Style document). When passed
 * as style url, layers will be added to the map when the Mapbox Style document
 * is loaded and parsed.
 * @return {Promise} A promise that resolves after all layers have been added to
 * the OpenLayers Map instance, their sources set, and their styles applied. the
 * `resolve` callback will be called with the OpenLayers Map instance as
 * argument.
 */
export default function olms(map, style) {
  let promise,
    accessToken = '',
    baseUrl = '',
    host = '',
    path = '';

  if (typeof map === 'string' || map instanceof HTMLElement) {
    map = new Map({
      target: map,
    });
  }

  if (typeof style === 'string') {
    const parts = style.match(spriteRegEx);
    if (parts) {
      baseUrl = parts[1];
      accessToken = parts.length > 2 ? parts[2] : '';
    }
    promise = new Promise(function (resolve, reject) {
      fetch(style, {
        credentials: 'same-origin',
      })
        .then(function (response) {
          return response.json();
        })
        .then(function (glStyle) {
          const a = /** @type {HTMLAnchorElement} */ (
            document.createElement('A')
          );
          a.href = style;
          const href = a.href;
          path = a.pathname.split('/').slice(0, -1).join('/') + '/';
          host = href.substr(0, href.indexOf(path));

          processStyle(glStyle, map, baseUrl, host, path, accessToken)
            .then(function () {
              resolve(map);
            })
            .catch(reject);
        })
        .catch(function (err) {
          reject(new Error(`Could not load ${style}: ${err.message}`));
        });
    });
  } else {
    promise = new Promise(function (resolve, reject) {
      processStyle(style, map)
        .then(function () {
          resolve(map);
        })
        .catch(reject);
    });
  }

  return promise;
}

/**
 * ```js
 * import {apply} from 'ol-mapbox-style';
 * ```
 * Like `olms`, but returns an `ol/Map` instance instead of a `Promise`.
 *
 * @param {PluggableMap|HTMLElement|string} map Either an existing OpenLayers Map
 * instance, or a HTML element, or the id of a HTML element that will be the
 * target of a new OpenLayers Map.
 * @param {string|Object} style JSON style object or style url pointing to a
 * Mapbox Style object. When using Mapbox APIs, the url must contain an access
 * token and look like
 * `https://api.mapbox.com/styles/v1/mapbox/bright-v9?access_token=[your_access_token_here]`.
 * When passed as JSON style object, all OpenLayers layers created by `apply()`
 * will be immediately available, but they may not have a source yet (i.e. when
 * they are defined by a TileJSON url in the Mapbox Style document). When passed
 * as style url, layers will be added to the map when the Mapbox Style document
 * is loaded and parsed.
 * @return {PluggableMap} The OpenLayers Map instance that will be populated with the
 * contents described in the Mapbox Style object.
 */
export function apply(map, style) {
  if (typeof map === 'string' || map instanceof HTMLElement) {
    map = new Map({
      target: map,
    });
  }
  setTimeout(function () {
    olms(map, style);
  }, 0);
  return map;
}

/**
 * If layerIds is not empty, applies the style specified in glStyle to the layer,
 * and adds the layer to the map.
 *
 * The layer may not yet have a source when the function is called.  If so, the style
 * is applied to the layer via a once listener on the 'change:source' event.
 *
 * @param {Layer} layer An OpenLayers layer instance.
 * @param {Array<string>} layerIds Array containing layer ids of already-processed layers.
 * @param {Object} glStyle Style as a JSON object.
 * @param {string|undefined} path The path part of the style URL. Only required
 * when a relative path is used with the `"sprite"` property of the style.
 * @param {PluggableMap} map OpenLayers Map.
 * @return {Promise} Returns a promise that resolves after the source has
 * been set on the specified layer, and the style has been applied.
 * @private
 */
function finalizeLayer(layer, layerIds, glStyle, path, map) {
  let minZoom = 24;
  let maxZoom = 0;
  const glLayers = glStyle.layers;
  for (let i = 0, ii = glLayers.length; i < ii; ++i) {
    const glLayer = glLayers[i];
    if (layerIds.indexOf(glLayer.id) !== -1) {
      minZoom = Math.min('minzoom' in glLayer ? glLayer.minzoom : 0, minZoom);
      maxZoom = Math.max('maxzoom' in glLayer ? glLayer.maxzoom : 24, maxZoom);
    }
  }
  return new Promise(function (resolve, reject) {
    const setStyle = function () {
      const source = layer.getSource();
      if (!source || source.getState() === 'error') {
        reject(
          new Error(
            'Error accessing data for source ' + layer.get('mapbox-source')
          )
        );
        return;
      }
      if ('getTileGrid' in source) {
        const tileGrid =
          /** @type {import("ol/source/Tile.js").default|import("ol/source/VectorTile.js").default} */ (
            source
          ).getTileGrid();
        if (tileGrid) {
          const sourceMinZoom = tileGrid.getMinZoom();
          if (minZoom > 0 || sourceMinZoom > 0) {
            layer.setMaxResolution(
              Math.min(
                defaultResolutions[minZoom],
                tileGrid.getResolution(sourceMinZoom)
              ) + 1e-9
            );
          }
          if (maxZoom < 24) {
            layer.setMinResolution(defaultResolutions[maxZoom] + 1e-9);
          }
        }
      }
      if (
        source instanceof VectorSource ||
        source instanceof VectorTileSource
      ) {
        applyStyle(
          /** @type {import("ol/layer/Vector").default|import("ol/layer/VectorTile").default} */ (
            layer
          ),
          glStyle,
          layerIds,
          path
        ).then(
          function () {
            layer.setVisible(true);
            resolve();
          },
          function (e) {
            reject(e);
          }
        );
      } else {
        resolve();
      }
    };

    layer.set('mapbox-layers', layerIds);
    if (map.getLayers().getArray().indexOf(layer) === -1) {
      map.addLayer(layer);
    }

    if (layer.getSource()) {
      setStyle();
    } else {
      layer.once('change:source', setStyle);
    }
  });
}

/**
 * ```js
 * import {getLayer} from 'ol-mapbox-style';
 * ```
 * Get the OpenLayers layer instance that contains the provided Mapbox Style
 * `layer`. Note that multiple Mapbox Style layers are combined in a single
 * OpenLayers layer instance when they use the same Mapbox Style `source`.
 * @param {PluggableMap} map OpenLayers Map.
 * @param {string} layerId Mapbox Style layer id.
 * @return {Layer} OpenLayers layer instance.
 */
export function getLayer(map, layerId) {
  const layers = map.getLayers().getArray();
  for (let i = 0, ii = layers.length; i < ii; ++i) {
    const mapboxLayers = layers[i].get('mapbox-layers');
    if (mapboxLayers && mapboxLayers.indexOf(layerId) !== -1) {
      return /** @type {Layer} */ (layers[i]);
    }
  }
}

/**
 * ```js
 * import {getLayers} from 'ol-mapbox-style';
 * ```
 * Get the OpenLayers layer instances for the provided Mapbox Style `source`.
 * @param {PluggableMap} map OpenLayers Map.
 * @param {string} sourceId Mapbox Style source id.
 * @return {Array<Layer>} OpenLayers layer instances.
 */
export function getLayers(map, sourceId) {
  const result = [];
  const layers = map.getLayers().getArray();
  for (let i = 0, ii = layers.length; i < ii; ++i) {
    if (layers[i].get('mapbox-source') === sourceId) {
      result.push(/** @type {Layer} */ (layers[i]));
    }
  }
  return result;
}

/**
 * ```js
 * import {getSource} from 'ol-mapbox-style';
 * ```
 * Get the OpenLayers source instance for the provided Mapbox Style `source`.
 * @param {PluggableMap} map OpenLayers Map.
 * @param {string} sourceId Mapbox Style source id.
 * @return {Source} OpenLayers source instance.
 */
export function getSource(map, sourceId) {
  const layers = map.getLayers().getArray();
  for (let i = 0, ii = layers.length; i < ii; ++i) {
    const source = /** @type {Layer} */ (layers[i]).getSource();
    if (layers[i].get('mapbox-source') === sourceId) {
      return source;
    }
  }
}

export {finalizeLayer as _finalizeLayer, getFonts as _getFonts};
