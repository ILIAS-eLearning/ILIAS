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

/* eslint-disable */
il.OLMaps.confInvalidAddress('{MAP_ID}', '{INVALID_ADDRESS_STRING}');
il.OLMaps.confMapData(
  '{MAP_ID}',
  [
    {LAT},
    {LONG},
    {ZOOM},
    {CENTRAL_MARKER},
    {NAV_CONTROL},
    {REPLACE_MARKER},
    {TILES},
    '{GEOLOCATION}',
  ]
);

// <!-- BEGIN user_marker -->
il.OLMaps.confUserMarker(
  '{MAP_ID}',
  [
    {ULONG},
    {ULAT},
    "<img style='float:right; margin-right:10px; margin-left:10px;' className='ilUserXXSmall' src='{IMG_USER}'\/><span className='small'>{USER_INFO}<\/span>"
  ]
);
// <!-- END user_marker -->

il.OLMaps.init('{MAP_ID}', jQuery);

ilLookupAddress = function (id, address) {
  return il.OLMaps.registry.maps[id].jumpToAddress(id, address);
};
ilUpdateMap = function (id) {
  return il.OLMaps.registry.maps[id].updateMap(id);
};
ilShowUserMarker = function (id, counter) {
  return il.OLMaps.registry.maps[id].moveToUserMarkerAndOpen(id, counter);
};

checkOLMapRendered = function(id) {
  if(! il.OLMaps.registry.maps[id].map.isRendered()) {
    window.setTimeout(
      function() {
        il.OLMaps.registry.maps[id].map.updateSize();
        checkOLMapRendered(id);
      },
      1000
    );
  }
}
checkOLMapRendered('{MAP_ID}');
