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

ilLookupAddress = function(id, address) {
    return openLayer.jumpToAddress(id, address);
};
ilUpdateMap = function (id) {
    return openLayer.updateMap(id);
};
ilShowUserMarker = function(id, counter) {
    return openLayer.moveToUserMarkerAndOpen(id, counter);
};