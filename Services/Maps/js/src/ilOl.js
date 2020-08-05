import ServiceOpenLayers from './ServiceOpenLayers';

// For passing data from ilias to js
let ilOLMapData = [];
let ilOLUserMarkers = [];
let ilOLInvalidAddress = undefined;

if (jQuery && il.Util.addOnLoad) {
    il.Util.addOnLoad(function() {
        let openLayer = new ServiceOpenLayers(jQuery, ilOLInvalidAddress, ilOLMapData, ilOLUserMarkers);

        ilLookupAddress = function(id, address) {
            return openLayer.jumpToAddress(id, address);
        };

        ilUpdateMap = function (id) {
            return openLayer.updateMap(id);
        };

        ilShowUserMarker = function(id, counter) {
            return openLayer.moveToUserMarkerAndOpen(id, counter);
        };

        openLayer.forceResize(jQuery);
        openLayer.init(ilOLMapData);
    });
}