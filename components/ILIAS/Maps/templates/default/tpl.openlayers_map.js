let ilOLInvalidAddress = "{INVALID_ADDRESS_STRING}";
let ilOLUserMarkers = {"{MAP_ID}" : []};
let ilOLMapData = {
    "{MAP_ID}" : [
        {LAT},
        {LONG},
        {ZOOM},
        {CENTRAL_MARKER},
        {NAV_CONTROL},
        {REPLACE_MARKER},
        {TILES},
        "{GEOLOCATION}"
    ]
};

<!-- BEGIN user_marker -->
ilOLUserMarkers["{UMAP_ID}"][{CNT}] = new Array({ULONG}, {ULAT},
    "<img style='float:right; margin-right:10px; margin-left:10px;' className='ilUserXXSmall' src='{IMG_USER}'\/><span className='small'>{USER_INFO}<\/span>");
<!-- END user_marker -->

let openLayer = initIlOpenLayerMaps(jQuery, ilOLInvalidAddress, ilOLMapData, ilOLUserMarkers);
openLayer.forceResize(jQuery);
openLayer.init(ilOLMapData);

/*
 * Multi-map support:
 * This template runs once per map. On pages with multiple maps, global functions
 * would otherwise be overwritten by the last rendered map.
 *
 * Store each map instance by its MAP_ID and dispatch calls by id.
 */
window.ilOLMapInstances = window.ilOLMapInstances || {};
window.ilOLMapInstances["{MAP_ID}"] = {
    openLayer: openLayer,
    mapData: ilOLMapData
};

/* Define the global API only once; later template runs must not overwrite it. */
window.ilLookupAddress = window.ilLookupAddress || function(id, address) {
    const inst = window.ilOLMapInstances && window.ilOLMapInstances[id];
    if (!inst) {
        console.warn("ilLookupAddress: unknown map id", id);
        return;
    }
    return inst.openLayer.jumpToAddress(id, address);
};

window.ilUpdateMap = window.ilUpdateMap || function(id) {
    const inst = window.ilOLMapInstances && window.ilOLMapInstances[id];
    if (!inst) {
        console.warn("ilUpdateMap: unknown map id", id);
        return;
    }
    return inst.openLayer.updateMap(id);
};

window.ilShowUserMarker = window.ilShowUserMarker || function(id, counter) {
    const inst = window.ilOLMapInstances && window.ilOLMapInstances[id];
    if (!inst) {
        console.warn("ilShowUserMarker: unknown map id", id);
        return;
    }
    return inst.openLayer.moveToUserMarkerAndOpen(id, counter);
};
