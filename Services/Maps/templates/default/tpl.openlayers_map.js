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
const ilOLInvalidAddress = '{INVALID_ADDRESS_STRING}';
const ilOLUserMarkers = { '{MAP_ID}': [] };
const ilOLMapData = {
  '{MAP_ID}': [
    {LAT},
    {LONG},
    {ZOOM},
    {CENTRAL_MARKER},
    {NAV_CONTROL},
    {REPLACE_MARKER},
    {TILES},
    '{GEOLOCATION}',
  ],
};

// <!-- BEGIN user_marker -->
ilOLUserMarkers['{UMAP_ID}'][{CNT}] = new Array(
  {ULONG},
  {ULAT},
  "<img style='float:right; margin-right:10px; margin-left:10px;' className='ilUserXXSmall' src='{IMG_USER}'\/><span className='small'>{USER_INFO}<\/span>",
);
// <!-- END user_marker -->

const openLayer = il.OLMaps.init(jQuery, ilOLInvalidAddress, ilOLMapData, ilOLUserMarkers);
openLayer.forceResize(jQuery);
openLayer.init(ilOLMapData);

ilLookupAddress = function (id, address) {
  return openLayer.jumpToAddress(id, address);
};
ilUpdateMap = function (id) {
  return openLayer.updateMap(id);
};
ilShowUserMarker = function (id, counter) {
  return openLayer.moveToUserMarkerAndOpen(id, counter);
};
