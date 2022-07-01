/*
ol-mapbox-style - Use Mapbox Style objects with OpenLayers
Copyright 2016-present ol-mapbox-style contributors
License: https://raw.githubusercontent.com/openlayers/ol-mapbox-style/master/LICENSE
*/
import Circle from 'ol/style/Circle.js';
import Fill from 'ol/style/Fill.js';
import Icon from 'ol/style/Icon.js';
import RenderFeature from 'ol/render/Feature.js';
import Stroke from 'ol/style/Stroke.js';
import Style from 'ol/style/Style.js';
import Text from 'ol/style/Text.js';
import mb2css from 'mapbox-to-css-font';
import { Color, featureFilter as createFilter, derefLayers, expression, function as fn, latest as spec, } from '@mapbox/mapbox-gl-style-spec';
import { applyLetterSpacing, createCanvas, defaultResolutions, deg2rad, getZoomForResolution, wrapText, } from './util.js';
/**
 * @typedef {import("ol/layer/Vector").default} VectorLayer
 * @typedef {import("ol/layer/VectorTile").default} VectorTileLayer
 * @typedef {import("ol/style/Style").StyleFunction} StyleFunction
 */
var isFunction = fn.isFunction;
var convertFunction = fn.convertFunction;
var isExpression = expression.isExpression;
var createPropertyExpression = expression.createPropertyExpression;
var types = {
    'Point': 1,
    'MultiPoint': 1,
    'LineString': 2,
    'MultiLineString': 2,
    'Polygon': 3,
    'MultiPolygon': 3,
};
var anchor = {
    'center': [0.5, 0.5],
    'left': [0, 0.5],
    'right': [1, 0.5],
    'top': [0.5, 0],
    'bottom': [0.5, 1],
    'top-left': [0, 0],
    'top-right': [1, 0],
    'bottom-left': [0, 1],
    'bottom-right': [1, 1],
};
var expressionData = function (rawExpression, propertySpec) {
    var compiledExpression = createPropertyExpression(rawExpression, propertySpec);
    if (compiledExpression.result === 'error') {
        throw new Error(compiledExpression.value
            .map(function (err) { return "".concat(err.key, ": ").concat(err.message); })
            .join(', '));
    }
    return compiledExpression.value;
};
var emptyObj = {};
var zoomObj = { zoom: 0 };
var renderFeatureCoordinates, renderFeature;
/**
 * @private
 * @param {Object} layer Gl object layer.
 * @param {string} layoutOrPaint 'layout' or 'paint'.
 * @param {string} property Feature property.
 * @param {number} zoom Zoom.
 * @param {Object} feature Gl feature.
 * @param {Object} [functionCache] Function cache.
 * @return {?} Value.
 */
export function getValue(layer, layoutOrPaint, property, zoom, feature, functionCache) {
    var layerId = layer.id;
    if (!functionCache) {
        functionCache = {};
        console.warn('No functionCache provided to getValue()'); //eslint-disable-line no-console
    }
    if (!functionCache[layerId]) {
        functionCache[layerId] = {};
    }
    var functions = functionCache[layerId];
    if (!functions[property]) {
        var value_1 = (layer[layoutOrPaint] || emptyObj)[property];
        var propertySpec = spec["".concat(layoutOrPaint, "_").concat(layer.type)][property];
        if (value_1 === undefined) {
            value_1 = propertySpec.default;
        }
        var isExpr = isExpression(value_1);
        if (!isExpr && isFunction(value_1)) {
            value_1 = convertFunction(value_1, propertySpec);
            isExpr = true;
        }
        if (isExpr) {
            var compiledExpression = expressionData(value_1, propertySpec);
            functions[property] =
                compiledExpression.evaluate.bind(compiledExpression);
        }
        else {
            if (propertySpec.type == 'color') {
                value_1 = Color.parse(value_1);
            }
            functions[property] = function () {
                return value_1;
            };
        }
    }
    zoomObj.zoom = zoom;
    return functions[property](zoomObj, feature);
}
/**
 * @private
 * @param {string} layerId Layer id.
 * @param {?} filter Filter.
 * @param {Object} feature Feature.
 * @param {number} zoom Zoom.
 * @param {Object} [filterCache] Filter cache.
 * @return {boolean} Filter result.
 */
function evaluateFilter(layerId, filter, feature, zoom, filterCache) {
    if (!filterCache) {
        console.warn('No filterCache provided to evaluateFilter()'); //eslint-disable-line no-console
    }
    if (!(layerId in filterCache)) {
        filterCache[layerId] = createFilter(filter).filter;
    }
    zoomObj.zoom = zoom;
    return filterCache[layerId](zoomObj, feature);
}
var renderTransparentEnabled = false;
/**
 * ```js
 * import {renderTransparent} from 'ol-mapbox-style/dist/stylefunction';
 * ```
 * Configure whether features with a transparent style should be rendered. When
 * set to `true`, it will be possible to hit detect content that is not visible,
 * like transparent fills of polygons, using `ol/layer/Layer#getFeatures()` or
 * `ol/Map#getFeaturesAtPixel()`
 * @param {boolean} enabled Rendering of transparent elements is enabled.
 * Default is `false`.
 */
export function renderTransparent(enabled) {
    renderTransparentEnabled = enabled;
}
/**
 * @private
 * @param {?} color Color.
 * @param {number} opacity Opacity.
 * @return {string} Color.
 */
function colorWithOpacity(color, opacity) {
    if (color) {
        if (!renderTransparentEnabled && (color.a === 0 || opacity === 0)) {
            return undefined;
        }
        var a = color.a;
        opacity = opacity === undefined ? 1 : opacity;
        return a === 0
            ? 'transparent'
            : 'rgba(' +
                Math.round((color.r * 255) / a) +
                ',' +
                Math.round((color.g * 255) / a) +
                ',' +
                Math.round((color.b * 255) / a) +
                ',' +
                a * opacity +
                ')';
    }
    return color;
}
var templateRegEx = /^([^]*)\{(.*)\}([^]*)$/;
/**
 * @private
 * @param {string} text Text.
 * @param {Object} properties Properties.
 * @return {string} Text.
 */
function fromTemplate(text, properties) {
    var parts;
    do {
        parts = text.match(templateRegEx);
        if (parts) {
            var value = properties[parts[2]] || '';
            text = parts[1] + value + parts[3];
        }
    } while (parts);
    return text;
}
var recordLayer = false;
/**
 * ```js
 * import {recordStyleLayer} from 'ol-mapbox-style/dist/stylefunction';
 * ```
 * Turns recording of the Mapbox Style's `layer` on and off. When turned on,
 * the layer that a rendered feature belongs to will be set as the feature's
 * `mapbox-layer` property.
 * @param {boolean} [record=false] Recording of the style layer is on.
 */
export function recordStyleLayer(record) {
    recordLayer = record;
}
/**
 * ```js
 * import stylefunction from 'ol-mapbox-style/dist/stylefunction';
 * ```
 * Creates a style function from the `glStyle` object for all layers that use
 * the specified `source`, which needs to be a `"type": "vector"` or
 * `"type": "geojson"` source and applies it to the specified OpenLayers layer.
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
 * This function also works in a web worker. In worker mode, the main thread needs
 * to listen to messages from the worker and respond with another message to make
 * sure that sprite image loading works:
 *
 * ```js
 *  worker.addEventListener('message', event => {
 *   if (event.data.action === 'loadImage') {
 *     const image = new Image();
 *     image.crossOrigin = 'anonymous';
 *     image.addEventListener('load', function() {
 *       createImageBitmap(image, 0, 0, image.width, image.height).then(imageBitmap => {
 *         worker.postMessage({
 *           action: 'imageLoaded',
 *           image: imageBitmap,
 *           src: event.data.src
 *         }, [imageBitmap]);
 *       });
 *     });
 *     image.src = event.data.src;
 *   }
 * });
 * ```
 *
 * @param {VectorLayer|VectorTileLayer} olLayer OpenLayers layer to
 * apply the style to. In addition to the style, the layer will get two
 * properties: `mapbox-source` will be the `id` of the `glStyle`'s source used
 * for the layer, and `mapbox-layers` will be an array of the `id`s of the
 * `glStyle`'s layers.
 * @param {string|Object} glStyle Mapbox Style object.
 * @param {string|Array<string>} source `source` key or an array of layer `id`s
 * from the Mapbox Style object. When a `source` key is provided, all layers for
 * the specified source will be included in the style function. When layer `id`s
 * are provided, they must be from layers that use the same source.
 * @param {Array<number>} [resolutions=[78271.51696402048, 39135.75848201024, 19567.87924100512, 9783.93962050256, 4891.96981025128, 2445.98490512564, 1222.99245256282, 611.49622628141, 305.748113140705, 152.8740565703525, 76.43702828517625, 38.21851414258813, 19.109257071294063, 9.554628535647032, 4.777314267823516, 2.388657133911758, 1.194328566955879, 0.5971642834779395, 0.29858214173896974, 0.14929107086948487, 0.07464553543474244]]
 * Resolutions for mapping resolution to zoom level.
 * @param {Object} [spriteData=undefined] Sprite data from the url specified in
 * the Mapbox Style object's `sprite` property. Only required if a `sprite`
 * property is specified in the Mapbox Style object.
 * @param {string} [spriteImageUrl=undefined] Sprite image url for the sprite
 * specified in the Mapbox Style object's `sprite` property. Only required if a
 * `sprite` property is specified in the Mapbox Style object.
 * @param {function(Array<string>):Array<string>} [getFonts=undefined] Function that
 * receives a font stack as arguments, and returns a (modified) font stack that
 * is available. Font names are the names used in the Mapbox Style object. If
 * not provided, the font stack will be used as-is. This function can also be
 * used for loading web fonts.
 * @return {StyleFunction} Style function for use in
 * `ol.layer.Vector` or `ol.layer.VectorTile`.
 */
export default function (olLayer, glStyle, source, resolutions, spriteData, spriteImageUrl, getFonts) {
    if (resolutions === void 0) { resolutions = defaultResolutions; }
    if (typeof glStyle == 'string') {
        glStyle = JSON.parse(glStyle);
    }
    if (glStyle.version != 8) {
        throw new Error('glStyle version 8 required.');
    }
    var spriteImage, spriteImgSize;
    if (spriteImageUrl) {
        if (typeof Image !== 'undefined') {
            var img_1 = new Image();
            img_1.crossOrigin = 'anonymous';
            img_1.onload = function () {
                spriteImage = img_1;
                spriteImgSize = [img_1.width, img_1.height];
                olLayer.changed();
                img_1.onload = null;
            };
            img_1.src = spriteImageUrl;
        }
        else if (typeof WorkerGlobalScope !== 'undefined' && self instanceof WorkerGlobalScope) { //eslint-disable-line
            var worker = /** @type {*} */ (self);
            // Main thread needs to handle 'loadImage' and dispatch 'imageLoaded'
            worker.postMessage({
                action: 'loadImage',
                src: spriteImageUrl,
            });
            worker.addEventListener('message', function handler(event) {
                if (event.data.action === 'imageLoaded' &&
                    event.data.src === spriteImageUrl) {
                    spriteImage = event.data.image;
                    spriteImgSize = [spriteImage.width, spriteImage.height];
                }
            });
        }
    }
    var allLayers = derefLayers(glStyle.layers);
    var layersBySourceLayer = {};
    var mapboxLayers = [];
    var iconImageCache = {};
    var patternCache = {};
    var functionCache = {};
    var filterCache = {};
    var mapboxSource;
    for (var i = 0, ii = allLayers.length; i < ii; ++i) {
        var layer = allLayers[i];
        var layerId = layer.id;
        if ((typeof source == 'string' && layer.source == source) ||
            source.indexOf(layerId) !== -1) {
            var sourceLayer = layer['source-layer'];
            if (!mapboxSource) {
                mapboxSource = layer.source;
                var source_1 = glStyle.sources[mapboxSource];
                if (!source_1) {
                    throw new Error("Source \"".concat(mapboxSource, "\" is not defined"));
                }
                var type = source_1.type;
                if (type !== 'vector' && type !== 'geojson') {
                    throw new Error("Source \"".concat(mapboxSource, "\" is not of type \"vector\" or \"geojson\", but \"").concat(type, "\""));
                }
            }
            var layers = layersBySourceLayer[sourceLayer];
            if (!layers) {
                layers = [];
                layersBySourceLayer[sourceLayer] = layers;
            }
            layers.push({
                layer: layer,
                index: i,
            });
            mapboxLayers.push(layerId);
        }
    }
    var textHalo = new Stroke();
    var textColor = new Fill();
    var styles = [];
    var styleFunction = function (feature, resolution) {
        var properties = feature.getProperties();
        var layers = layersBySourceLayer[properties.layer];
        if (!layers) {
            return;
        }
        var zoom = resolutions.indexOf(resolution);
        if (zoom == -1) {
            zoom = getZoomForResolution(resolution, resolutions);
        }
        var type = types[feature.getGeometry().getType()];
        var f = {
            properties: properties,
            type: type,
        };
        var stylesLength = -1;
        var featureBelongsToLayer;
        var _loop_1 = function (i, ii) {
            var layerData = layers[i];
            var layer = layerData.layer;
            var layerId = layer.id;
            var layout = layer.layout || emptyObj;
            var paint = layer.paint || emptyObj;
            if (layout.visibility === 'none' ||
                ('minzoom' in layer && zoom < layer.minzoom) ||
                ('maxzoom' in layer && zoom >= layer.maxzoom)) {
                return "continue";
            }
            var filter = layer.filter;
            if (!filter || evaluateFilter(layerId, filter, f, zoom, filterCache)) {
                featureBelongsToLayer = layer;
                var color = void 0, opacity = void 0, fill = void 0, stroke = void 0, strokeColor = void 0, style = void 0;
                var index = layerData.index;
                if (type == 3 &&
                    (layer.type == 'fill' || layer.type == 'fill-extrusion')) {
                    opacity = getValue(layer, 'paint', layer.type + '-opacity', zoom, f, functionCache);
                    if (layer.type + '-pattern' in paint) {
                        var fillIcon = getValue(layer, 'paint', layer.type + '-pattern', zoom, f, functionCache);
                        if (fillIcon) {
                            var icon_1 = typeof fillIcon === 'string'
                                ? fromTemplate(fillIcon, properties)
                                : fillIcon.toString();
                            if (spriteImage && spriteData && spriteData[icon_1]) {
                                ++stylesLength;
                                style = styles[stylesLength];
                                if (!style ||
                                    !style.getFill() ||
                                    style.getStroke() ||
                                    style.getText()) {
                                    style = new Style({
                                        fill: new Fill(),
                                    });
                                    styles[stylesLength] = style;
                                }
                                fill = style.getFill();
                                style.setZIndex(index);
                                var icon_cache_key = icon_1 + '.' + opacity;
                                var pattern = patternCache[icon_cache_key];
                                if (!pattern) {
                                    var spriteImageData = spriteData[icon_1];
                                    var canvas = createCanvas(spriteImageData.width, spriteImageData.height);
                                    var ctx = /** @type {CanvasRenderingContext2D} */ (canvas.getContext('2d'));
                                    ctx.globalAlpha = opacity;
                                    ctx.drawImage(spriteImage, spriteImageData.x, spriteImageData.y, spriteImageData.width, spriteImageData.height, 0, 0, spriteImageData.width, spriteImageData.height);
                                    pattern = ctx.createPattern(canvas, 'repeat');
                                    patternCache[icon_cache_key] = pattern;
                                }
                                fill.setColor(pattern);
                            }
                        }
                    }
                    else {
                        color = colorWithOpacity(getValue(layer, 'paint', layer.type + '-color', zoom, f, functionCache), opacity);
                        if (layer.type + '-outline-color' in paint) {
                            strokeColor = colorWithOpacity(getValue(layer, 'paint', layer.type + '-outline-color', zoom, f, functionCache), opacity);
                        }
                        if (!strokeColor) {
                            strokeColor = color;
                        }
                        if (color || strokeColor) {
                            ++stylesLength;
                            style = styles[stylesLength];
                            if (!style ||
                                (color && !style.getFill()) ||
                                (!color && style.getFill()) ||
                                (strokeColor && !style.getStroke()) ||
                                (!strokeColor && style.getStroke()) ||
                                style.getText()) {
                                style = new Style({
                                    fill: color ? new Fill() : undefined,
                                    stroke: strokeColor ? new Stroke() : undefined,
                                });
                                styles[stylesLength] = style;
                            }
                            if (color) {
                                fill = style.getFill();
                                fill.setColor(color);
                            }
                            if (strokeColor) {
                                stroke = style.getStroke();
                                stroke.setColor(strokeColor);
                                stroke.setWidth(0.5);
                            }
                            style.setZIndex(index);
                        }
                    }
                }
                if (type != 1 && layer.type == 'line') {
                    color =
                        !('line-pattern' in paint) && 'line-color' in paint
                            ? colorWithOpacity(getValue(layer, 'paint', 'line-color', zoom, f, functionCache), getValue(layer, 'paint', 'line-opacity', zoom, f, functionCache))
                            : undefined;
                    var width_1 = getValue(layer, 'paint', 'line-width', zoom, f, functionCache);
                    if (color && width_1 > 0) {
                        ++stylesLength;
                        style = styles[stylesLength];
                        if (!style ||
                            !style.getStroke() ||
                            style.getFill() ||
                            style.getText()) {
                            style = new Style({
                                stroke: new Stroke(),
                            });
                            styles[stylesLength] = style;
                        }
                        stroke = style.getStroke();
                        stroke.setLineCap(getValue(layer, 'layout', 'line-cap', zoom, f, functionCache));
                        stroke.setLineJoin(getValue(layer, 'layout', 'line-join', zoom, f, functionCache));
                        stroke.setMiterLimit(getValue(layer, 'layout', 'line-miter-limit', zoom, f, functionCache));
                        stroke.setColor(color);
                        stroke.setWidth(width_1);
                        stroke.setLineDash(paint['line-dasharray']
                            ? getValue(layer, 'paint', 'line-dasharray', zoom, f, functionCache).map(function (x) {
                                return x * width_1;
                            })
                            : null);
                        style.setZIndex(index);
                    }
                }
                var hasImage = false;
                var text = null;
                var placementAngle = 0;
                var icon = void 0, iconImg = void 0, skipLabel = void 0;
                if ((type == 1 || type == 2) && 'icon-image' in layout) {
                    var iconImage = getValue(layer, 'layout', 'icon-image', zoom, f, functionCache);
                    if (iconImage) {
                        icon =
                            typeof iconImage === 'string'
                                ? fromTemplate(iconImage, properties)
                                : iconImage.toString();
                        var styleGeom = undefined;
                        if (spriteImage && spriteData && spriteData[icon]) {
                            var iconRotationAlignment = getValue(layer, 'layout', 'icon-rotation-alignment', zoom, f, functionCache);
                            if (type == 2) {
                                var geom = feature.getGeometry();
                                // ol package and ol-debug.js only
                                if (geom.getFlatMidpoint || geom.getFlatMidpoints) {
                                    var extent = geom.getExtent();
                                    var size = Math.sqrt(Math.max(Math.pow((extent[2] - extent[0]) / resolution, 2), Math.pow((extent[3] - extent[1]) / resolution, 2)));
                                    if (size > 150) {
                                        //FIXME Do not hard-code a size of 150
                                        var midpoint = geom.getType() === 'MultiLineString'
                                            ? geom.getFlatMidpoints()
                                            : geom.getFlatMidpoint();
                                        if (!renderFeature) {
                                            renderFeatureCoordinates = [NaN, NaN];
                                            renderFeature = new RenderFeature('Point', renderFeatureCoordinates, [], {}, null);
                                        }
                                        styleGeom = renderFeature;
                                        renderFeatureCoordinates[0] = midpoint[0];
                                        renderFeatureCoordinates[1] = midpoint[1];
                                        var placement = getValue(layer, 'layout', 'symbol-placement', zoom, f, functionCache);
                                        if (placement === 'line' &&
                                            iconRotationAlignment === 'map') {
                                            var stride = geom.getStride();
                                            var coordinates = geom.getFlatCoordinates();
                                            for (var i_1 = 0, ii_1 = coordinates.length - stride; i_1 < ii_1; i_1 += stride) {
                                                var x1 = coordinates[i_1];
                                                var y1 = coordinates[i_1 + 1];
                                                var x2 = coordinates[i_1 + stride];
                                                var y2 = coordinates[i_1 + stride + 1];
                                                var minX = Math.min(x1, x2);
                                                var minY = Math.min(y1, y2);
                                                var maxX = Math.max(x1, x2);
                                                var maxY = Math.max(y1, y2);
                                                if (midpoint[0] >= minX &&
                                                    midpoint[0] <= maxX &&
                                                    midpoint[1] >= minY &&
                                                    midpoint[1] <= maxY) {
                                                    placementAngle = Math.atan2(y1 - y2, x2 - x1);
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            if (type !== 2 || styleGeom) {
                                var iconSize = getValue(layer, 'layout', 'icon-size', zoom, f, functionCache);
                                var iconColor = paint['icon-color'] !== undefined
                                    ? getValue(layer, 'paint', 'icon-color', zoom, f, functionCache)
                                    : null;
                                if (!iconColor || iconColor.a !== 0) {
                                    var icon_cache_key = icon + '.' + iconSize;
                                    if (iconColor !== null) {
                                        icon_cache_key += '.' + iconColor;
                                    }
                                    iconImg = iconImageCache[icon_cache_key];
                                    if (!iconImg) {
                                        var spriteImageData_1 = spriteData[icon];
                                        iconImg = new Icon({
                                            color: iconColor
                                                ? [
                                                    iconColor.r * 255,
                                                    iconColor.g * 255,
                                                    iconColor.b * 255,
                                                    iconColor.a,
                                                ]
                                                : undefined,
                                            img: spriteImage,
                                            imgSize: spriteImgSize,
                                            size: [spriteImageData_1.width, spriteImageData_1.height],
                                            offset: [spriteImageData_1.x, spriteImageData_1.y],
                                            rotateWithView: iconRotationAlignment === 'map',
                                            scale: iconSize / spriteImageData_1.pixelRatio,
                                            displacement: 'icon-offset' in layout
                                                ? getValue(layer, 'layout', 'icon-offset', zoom, f, functionCache).map(function (v) { return -v * spriteImageData_1.pixelRatio; })
                                                : undefined,
                                        });
                                        iconImageCache[icon_cache_key] = iconImg;
                                    }
                                }
                                if (iconImg) {
                                    ++stylesLength;
                                    style = styles[stylesLength];
                                    if (!style ||
                                        !style.getImage() ||
                                        style.getFill() ||
                                        style.getStroke()) {
                                        style = new Style();
                                        styles[stylesLength] = style;
                                    }
                                    style.setGeometry(styleGeom);
                                    iconImg.setRotation(placementAngle +
                                        deg2rad(getValue(layer, 'layout', 'icon-rotate', zoom, f, functionCache)));
                                    iconImg.setOpacity(getValue(layer, 'paint', 'icon-opacity', zoom, f, functionCache));
                                    iconImg.setAnchor(anchor[getValue(layer, 'layout', 'icon-anchor', zoom, f, functionCache)]);
                                    style.setImage(iconImg);
                                    text = style.getText();
                                    style.setText(undefined);
                                    style.setZIndex(index);
                                    hasImage = true;
                                    skipLabel = false;
                                }
                            }
                            else {
                                skipLabel = true;
                            }
                        }
                    }
                }
                if (type == 1 && layer.type === 'circle') {
                    ++stylesLength;
                    style = styles[stylesLength];
                    if (!style ||
                        !style.getImage() ||
                        style.getFill() ||
                        style.getStroke()) {
                        style = new Style();
                        styles[stylesLength] = style;
                    }
                    var circleRadius = 'circle-radius' in paint
                        ? getValue(layer, 'paint', 'circle-radius', zoom, f, functionCache)
                        : 5;
                    var circleStrokeColor = colorWithOpacity(getValue(layer, 'paint', 'circle-stroke-color', zoom, f, functionCache), getValue(layer, 'paint', 'circle-stroke-opacity', zoom, f, functionCache));
                    var circleColor = colorWithOpacity(getValue(layer, 'paint', 'circle-color', zoom, f, functionCache), getValue(layer, 'paint', 'circle-opacity', zoom, f, functionCache));
                    var circleStrokeWidth = getValue(layer, 'paint', 'circle-stroke-width', zoom, f, functionCache);
                    var cache_key = circleRadius +
                        '.' +
                        circleStrokeColor +
                        '.' +
                        circleColor +
                        '.' +
                        circleStrokeWidth;
                    iconImg = iconImageCache[cache_key];
                    if (!iconImg) {
                        iconImg = new Circle({
                            radius: circleRadius,
                            stroke: circleStrokeColor && circleStrokeWidth > 0
                                ? new Stroke({
                                    width: circleStrokeWidth,
                                    color: circleStrokeColor,
                                })
                                : undefined,
                            fill: circleColor
                                ? new Fill({
                                    color: circleColor,
                                })
                                : undefined,
                        });
                        iconImageCache[cache_key] = iconImg;
                    }
                    style.setImage(iconImg);
                    text = style.getText();
                    style.setText(undefined);
                    style.setGeometry(undefined);
                    style.setZIndex(index);
                    hasImage = true;
                }
                var label = void 0, font = void 0, textLineHeight_1, textSize_1, letterSpacing_1, maxTextWidth_1;
                if ('text-field' in layout) {
                    textSize_1 = Math.round(getValue(layer, 'layout', 'text-size', zoom, f, functionCache));
                    var fontArray_1 = getValue(layer, 'layout', 'text-font', zoom, f, functionCache);
                    textLineHeight_1 = getValue(layer, 'layout', 'text-line-height', zoom, f, functionCache);
                    font = mb2css(getFonts ? getFonts(fontArray_1) : fontArray_1, textSize_1, textLineHeight_1);
                    letterSpacing_1 = getValue(layer, 'layout', 'text-letter-spacing', zoom, f, functionCache);
                    maxTextWidth_1 = getValue(layer, 'layout', 'text-max-width', zoom, f, functionCache);
                    var textField = getValue(layer, 'layout', 'text-field', zoom, f, functionCache);
                    if (typeof textField === 'object' && textField.sections) {
                        if (textField.sections.length === 1) {
                            label = textField.toString();
                        }
                        else {
                            label = textField.sections.reduce(function (acc, chunk, i) {
                                var fonts = chunk.fontStack
                                    ? chunk.fontStack.split(',')
                                    : fontArray_1;
                                var chunkFont = mb2css(getFonts ? getFonts(fonts) : fonts, textSize_1 * (chunk.scale || 1), textLineHeight_1);
                                var text = chunk.text;
                                if (text === '\n') {
                                    acc.push('\n', '');
                                    return acc;
                                }
                                if (type == 2) {
                                    acc.push(applyLetterSpacing(text, letterSpacing_1), chunkFont);
                                    return;
                                }
                                text = wrapText(text, chunkFont, maxTextWidth_1, letterSpacing_1).split('\n');
                                for (var i_2 = 0, ii_2 = text.length; i_2 < ii_2; ++i_2) {
                                    if (i_2 > 0) {
                                        acc.push('\n', '');
                                    }
                                    acc.push(text[i_2], chunkFont);
                                }
                                return acc;
                            }, []);
                        }
                    }
                    else {
                        label = fromTemplate(textField, properties).trim();
                    }
                    opacity = getValue(layer, 'paint', 'text-opacity', zoom, f, functionCache);
                }
                if (label && opacity && !skipLabel) {
                    if (!hasImage) {
                        ++stylesLength;
                        style = styles[stylesLength];
                        if (!style ||
                            !style.getText() ||
                            style.getFill() ||
                            style.getStroke()) {
                            style = new Style();
                            styles[stylesLength] = style;
                        }
                        style.setImage(undefined);
                        style.setGeometry(undefined);
                    }
                    if (!style.getText()) {
                        style.setText(text ||
                            new Text({
                                padding: [2, 2, 2, 2],
                            }));
                    }
                    text = style.getText();
                    var textTransform = layout['text-transform'];
                    if (textTransform == 'uppercase') {
                        label = Array.isArray(label)
                            ? label.map(function (t, i) { return (i % 2 ? t : t.toUpperCase()); })
                            : label.toUpperCase();
                    }
                    else if (textTransform == 'lowercase') {
                        label = Array.isArray(label)
                            ? label.map(function (t, i) { return (i % 2 ? t : t.toLowerCase()); })
                            : label.toLowerCase();
                    }
                    var wrappedLabel = Array.isArray(label)
                        ? label
                        : type == 2
                            ? applyLetterSpacing(label, letterSpacing_1)
                            : wrapText(label, font, maxTextWidth_1, letterSpacing_1);
                    text.setText(wrappedLabel);
                    text.setFont(font);
                    text.setRotation(deg2rad(getValue(layer, 'layout', 'text-rotate', zoom, f, functionCache)));
                    var textAnchor = getValue(layer, 'layout', 'text-anchor', zoom, f, functionCache);
                    var placement = hasImage || type == 1
                        ? 'point'
                        : getValue(layer, 'layout', 'symbol-placement', zoom, f, functionCache);
                    text.setPlacement(placement);
                    text.setOverflow(placement === 'point');
                    var textHaloWidth = getValue(layer, 'paint', 'text-halo-width', zoom, f, functionCache);
                    var textOffset = getValue(layer, 'layout', 'text-offset', zoom, f, functionCache);
                    var textTranslate = getValue(layer, 'paint', 'text-translate', zoom, f, functionCache);
                    // Text offset has to take halo width and line height into account
                    var vOffset = 0;
                    var hOffset = 0;
                    if (placement == 'point') {
                        var textAlign = 'center';
                        if (textAnchor.indexOf('left') !== -1) {
                            textAlign = 'left';
                            hOffset = textHaloWidth;
                        }
                        else if (textAnchor.indexOf('right') !== -1) {
                            textAlign = 'right';
                            hOffset = -textHaloWidth;
                        }
                        text.setTextAlign(textAlign);
                        var textRotationAlignment = getValue(layer, 'layout', 'text-rotation-alignment', zoom, f, functionCache);
                        text.setRotateWithView(textRotationAlignment == 'map');
                    }
                    else {
                        text.setMaxAngle((deg2rad(getValue(layer, 'layout', 'text-max-angle', zoom, f, functionCache)) *
                            label.length) /
                            wrappedLabel.length);
                        text.setTextAlign();
                        text.setRotateWithView(false);
                    }
                    var textBaseline = 'middle';
                    if (textAnchor.indexOf('bottom') == 0) {
                        textBaseline = 'bottom';
                        vOffset = -textHaloWidth - 0.5 * (textLineHeight_1 - 1) * textSize_1;
                    }
                    else if (textAnchor.indexOf('top') == 0) {
                        textBaseline = 'top';
                        vOffset = textHaloWidth + 0.5 * (textLineHeight_1 - 1) * textSize_1;
                    }
                    text.setTextBaseline(textBaseline);
                    text.setOffsetX(textOffset[0] * textSize_1 + hOffset + textTranslate[0]);
                    text.setOffsetY(textOffset[1] * textSize_1 + vOffset + textTranslate[1]);
                    textColor.setColor(colorWithOpacity(getValue(layer, 'paint', 'text-color', zoom, f, functionCache), opacity));
                    text.setFill(textColor);
                    var haloColor = colorWithOpacity(getValue(layer, 'paint', 'text-halo-color', zoom, f, functionCache), opacity);
                    if (haloColor) {
                        textHalo.setColor(haloColor);
                        // spec here : https://docs.mapbox.com/mapbox-gl-js/style-spec/#paint-symbol-text-halo-width
                        // Halo width must be doubled because it is applied around the center of the text outline
                        textHaloWidth *= 2;
                        // 1/4 of text size (spec) x 2
                        var halfTextSize = 0.5 * textSize_1;
                        textHalo.setWidth(textHaloWidth <= halfTextSize ? textHaloWidth : halfTextSize);
                        text.setStroke(textHalo);
                    }
                    else {
                        text.setStroke(undefined);
                    }
                    var textPadding = getValue(layer, 'layout', 'text-padding', zoom, f, functionCache);
                    var padding = text.getPadding();
                    if (textPadding !== padding[0]) {
                        padding[0] = textPadding;
                        padding[1] = textPadding;
                        padding[2] = textPadding;
                        padding[3] = textPadding;
                    }
                    style.setZIndex(index);
                }
            }
        };
        for (var i = 0, ii = layers.length; i < ii; ++i) {
            _loop_1(i, ii);
        }
        if (stylesLength > -1) {
            styles.length = stylesLength + 1;
            if (recordLayer) {
                if (typeof feature.set === 'function') {
                    // ol/Feature
                    feature.set('mapbox-layer', featureBelongsToLayer);
                }
                else {
                    // ol/render/Feature
                    feature.getProperties()['mapbox-layer'] = featureBelongsToLayer;
                }
            }
            return styles;
        }
    };
    olLayer.setStyle(styleFunction);
    olLayer.set('mapbox-source', mapboxSource);
    olLayer.set('mapbox-layers', mapboxLayers);
    return styleFunction;
}
export { colorWithOpacity as _colorWithOpacity, evaluateFilter as _evaluateFilter, fromTemplate as _fromTemplate, getValue as _getValue, };
//# sourceMappingURL=stylefunction.js.map