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
export function getValue(layer: any, layoutOrPaint: string, property: string, zoom: number, feature: any, functionCache?: any): unknown;
/**
 * ```js
 * import {recordStyleLayer} from 'ol-mapbox-style/dist/stylefunction';
 * ```
 * Turns recording of the Mapbox Style's `layer` on and off. When turned on,
 * the layer that a rendered feature belongs to will be set as the feature's
 * `mapbox-layer` property.
 * @param {boolean} [record=false] Recording of the style layer is on.
 */
export function recordStyleLayer(record?: boolean): void;
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
export default function _default(olLayer: VectorLayer | VectorTileLayer, glStyle: string | any, source: string | Array<string>, resolutions?: Array<number>, spriteData?: any, spriteImageUrl?: string, getFonts?: (arg0: Array<string>) => Array<string>): any;
export type VectorLayer = any;
export type VectorTileLayer = any;
export type StyleFunction = any;
/**
 * @private
 * @param {?} color Color.
 * @param {number} opacity Opacity.
 * @return {string} Color.
 */
declare function colorWithOpacity(color: unknown, opacity: number): string;
/**
 * @private
 * @param {string} layerId Layer id.
 * @param {?} filter Filter.
 * @param {Object} feature Feature.
 * @param {number} zoom Zoom.
 * @param {Object} [filterCache] Filter cache.
 * @return {boolean} Filter result.
 */
declare function evaluateFilter(layerId: string, filter: unknown, feature: any, zoom: number, filterCache?: any): boolean;
/**
 * @private
 * @param {string} text Text.
 * @param {Object} properties Properties.
 * @return {string} Text.
 */
declare function fromTemplate(text: string, properties: any): string;
export { colorWithOpacity as _colorWithOpacity, evaluateFilter as _evaluateFilter, fromTemplate as _fromTemplate, getValue as _getValue };
//# sourceMappingURL=stylefunction.d.ts.map