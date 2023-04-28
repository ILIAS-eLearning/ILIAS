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
export function applyStyle(layer: VectorTileLayer | VectorLayer, glStyle: string | any, source: string | Array<string>, path?: string, resolutions?: Array<number>): Promise<any>;
/**
 * ```js
 * import {applyBackground} from 'ol-mapbox-style';
 * ```
 * Applies properties of the Mapbox Style's first `background` layer to the
 * provided map or VectorTile layer.
 * @param {PluggableMap|VectorTileLayer} mapOrLayer OpenLayers Map or VectorTile layer.
 * @param {Object} glStyle Mapbox Style object.
 */
export function applyBackground(mapOrLayer: PluggableMap | VectorTileLayer, glStyle: any): void;
/**
 * Creates an OpenLayers VectorTile source for a gl source entry.
 * @param {Object} glSource "source" entry from a Mapbox Style object.
 * @param {string|undefined} url URL to use for the source. This is expected to be the complete http(s) url,
 * with access key applied.When not provided, `glSource.tiles` has to be set.
 * @return {Promise<import("ol/source/VectorTile").default>} Promise resolving to a VectorTile source.
 * @private
 */
export function setupVectorSource(glSource: any, url: string | undefined): Promise<any>;
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
export default function olms(map: PluggableMap | HTMLElement | string, style: string | any): Promise<any>;
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
export function apply(map: PluggableMap | HTMLElement | string, style: string | any): any;
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
export function getLayer(map: any, layerId: string): any;
/**
 * ```js
 * import {getLayers} from 'ol-mapbox-style';
 * ```
 * Get the OpenLayers layer instances for the provided Mapbox Style `source`.
 * @param {PluggableMap} map OpenLayers Map.
 * @param {string} sourceId Mapbox Style source id.
 * @return {Array<Layer>} OpenLayers layer instances.
 */
export function getLayers(map: any, sourceId: string): Array<Layer>;
/**
 * ```js
 * import {getSource} from 'ol-mapbox-style';
 * ```
 * Get the OpenLayers source instance for the provided Mapbox Style `source`.
 * @param {PluggableMap} map OpenLayers Map.
 * @param {string} sourceId Mapbox Style source id.
 * @return {Source} OpenLayers source instance.
 */
export function getSource(map: any, sourceId: string): any;
export type PluggableMap = any;
export type Layer = any;
export type Source = any;
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
declare function finalizeLayer(layer: any, layerIds: Array<string>, glStyle: any, path: string | undefined, map: any): Promise<any>;
/**
 * @private
 * @param {Array} fonts Fonts.
 * @return {Array} Processed fonts.
 */
declare function getFonts(fonts: any[]): any[];
export { finalizeLayer as _finalizeLayer, getFonts as _getFonts };
//# sourceMappingURL=index.d.ts.map