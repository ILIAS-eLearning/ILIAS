import ServiceOpenLayers from './ServiceOpenLayers';

initIlOpenLayerMaps = function(jQuery, ilOLInvalidAddress, ilOLMapData, ilOLUserMarkers) {
    return new ServiceOpenLayers(jQuery, ilOLInvalidAddress, ilOLMapData, ilOLUserMarkers);
};
