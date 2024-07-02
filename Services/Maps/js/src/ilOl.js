/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 ******************************************************************** */

import il from 'il';
import ServiceOpenLayers from './ServiceOpenLayers';

il.OLMaps = il.OLMaps || {};

il.OLMaps.registry = {
  config : [],
  maps : [],
};

il.OLMaps.confInvalidAddress = function (id, invalid_address_string) {
  il.OLMaps.registry.config[id] = il.OLMaps.registry.config[id] || [];
  il.OLMaps.registry.config[id].ilOLInvalidAddress = invalid_address_string;
}
il.OLMaps.confMapData = function (id, data) {
  il.OLMaps.registry.config[id] = il.OLMaps.registry.config[id] || [];
  il.OLMaps.registry.config[id].ilOLMapData = [];
  il.OLMaps.registry.config[id].ilOLMapData[id] = data;
}
il.OLMaps.confUserMarker = function (id, data) {
  il.OLMaps.registry.config[id] = il.OLMaps.registry.config[id] || [];
  il.OLMaps.registry.config[id].ilOLUserMarkers = il.OLMaps.registry.config[id].ilOLUserMarkers || [];
  il.OLMaps.registry.config[id].ilOLUserMarkers.push(data);
}

il.OLMaps.init = function (id, jQuery) {
  let umarkers = [];
  umarkers[id] = il.OLMaps.registry.config[id].ilOLUserMarkers;
  il.OLMaps.registry.maps[id] = new ServiceOpenLayers(
    jQuery, 
    il.OLMaps.registry.config[id].ilOLInvalidAddress,
    il.OLMaps.registry.config[id].ilOLMapData,
    umarkers
  );

  il.OLMaps.registry.maps[id].forceResize(jQuery);
  il.OLMaps.registry.maps[id].init(il.OLMaps.registry.config[id].ilOLMapData);
};
