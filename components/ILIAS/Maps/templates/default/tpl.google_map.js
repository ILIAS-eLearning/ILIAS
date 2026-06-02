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
 */

ilMapData = Array();
ilMap = Array();
ilMapOptions = [];
ilCM = Array();
ilMapUserMarker = Array();
ilMapData["{MAP_ID}"] = new Array({LAT},{LONG},{ZOOM},{TYPE_CONTROL},{NAV_CONTROL},{UPDATE_LISTENER},{LARGE_CONTROL},{CENTRAL_MARKER});
ilMapUserMarker["{MAP_ID}"] = Array();
<!-- BEGIN user_marker -->
ilMapUserMarker["{UMAP_ID}"][{CNT}] = new Array({ULAT},{ULONG}, "<div style='width:220px;'><img style='float:right; margin-right:10px;' className='ilUserXXSmall' src='{IMG_USER}'\/><span className='small'>{USER_INFO}<\/span><\/div>");
<!-- END user_marker -->

var ilAdvancedMarkerElement = null;
if (typeof google !== "undefined" && google.maps)
{
    ilAdvancedMarkerElement = google.maps.marker && google.maps.marker.AdvancedMarkerElement
        ? google.maps.marker.AdvancedMarkerElement
        : null;
}

if (typeof google !== "undefined" && google.maps)
{
	ilInitMaps();
}

/**
 * Init all maps
 */
function ilInitMaps()
{
    var obj;

    // get all spans
    obj = document.getElementsByTagName('div');

    // run through them
    for (var i=0;i<obj.length;i++)
    {
        // if it has a class of helpLink
        if(/ilGoogleMap/.test(obj[i].className))
        {
            ilInitMap(obj[i].id, ilMapData[obj[i].id][0], ilMapData[obj[i].id][1],
                ilMapData[obj[i].id][2], ilMapData[obj[i].id][3], ilMapData[obj[i].id][4],
                ilMapData[obj[i].id][5], ilMapData[obj[i].id][6], ilMapData[obj[i].id][7]);
        }
    }
}

/**
 * Init a goole map
 */
function ilInitMap(id, latitude, longitude, zoom, type_control,
                   nav_control, update_listener, large_map_control, central_marker)
{
    var mapLatLng = new google.maps.LatLng(latitude, longitude);
    var mapOptions = {
        zoom: zoom,
        center: mapLatLng,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        streetViewControl: false,
        mapTypeControl: type_control,
        scaleControl: true,
        panControl: (nav_control || large_map_control),
        mapId: "{GOOGLE_MAP_ID}"
    }
    var map = new google.maps.Map(document.getElementById(id), mapOptions);

    ilGetUserMarkers(id, map);

    if (update_listener)
    {
        google.maps.event.addListener(map, "zoom_changed", function() {
            ilUpdateZoomInput(id, map)});
    }

    if (central_marker)
    {
        ilCM[id] = ilCreateMarker(map, latitude, longitude);

        google.maps.event.addListener(map, "click", function(event){
            ilMapClicked(id, map, event.latLng);
        });
    }

    ilMap[id] = map;
    ilMapOptions[id] = mapOptions;

    // if map is in subform we have to redraw on subform activation
    $("#"+id).closest("form").on("subform_activated", function() {
        google.maps.event.trigger(map, 'resize');
    });
}

// re-render all maps in element
function ilMapRerender(el) {
    $(el).find(".ilGoogleMap").each(function (o) {
        google.maps.event.trigger(ilMap[this.id],'resize');
        ilMap[this.id].setCenter(ilMapOptions[this.id].center);
    });
}

/**
 *  Update input fields from map properties
 */
function ilUpdateLocationInput(id, map, loc, address)
{
    zoom = map.getZoom();
    lat_input = document.getElementById(id + "_lat");
    if (!lat_input)
    {
        return;
    }
    lat_input.value = loc.lat();
    lng_input = document.getElementById(id + "_lng");
    lng_input.value = loc.lng();
    zoom_input = document.getElementById(id + "_zoom");
    zoom_input.selectedIndex = zoom;

    if(address != "undefined")
    {
        addr_input = document.getElementById(id + "_addr");
        addr_input.value = address;
    }

    if (ilCM[id])
    {
        ilSetMarkerPosition(ilCM[id], loc);
    }
}

/**
 *  Update input fields from map properties
 */
function ilUpdateZoomInput(id, map)
{
    zoom = map.getZoom();
    zoom_input = document.getElementById(id + "_zoom");
    zoom_input.selectedIndex = zoom;
}

/**
 *  Update map properties from input fields
 */
function ilUpdateMap(id)
{
    var lat;
    var lng;

    map = ilMap[id];
    lat_input = document.getElementById(id + "_lat");
    lng_input = document.getElementById(id + "_lng");

    if (isNaN(parseFloat(lat_input.value)))
    {
        lat = 0;
    }
    else
    {
        lat = parseFloat(lat_input.value);
    }

    if (isNaN(parseFloat(lng_input.value)))
    {
        lng = 0;
    }
    else
    {
        lng = parseFloat(lng_input.value);
    }

    zoom_input = document.getElementById(id + "_zoom");
    var zoom = zoom_input.value;
    map.setZoom(parseInt(zoom));

    var loc = new google.maps.LatLng(lat, lng);
    map.setCenter(loc);
    lng_input.value = lng;
    lat_input.value = lat;

    if (ilCM[id])
    {
        ilSetMarkerPosition(ilCM[id], loc);
    }
}

function ilCreateMarkerContent()
{
    var markerContent = document.createElement("img");
    markerContent.src = "./assets/images/standard/icon_mapm.svg";
    markerContent.width = 24;
    markerContent.height = 40;
    markerContent.alt = "";
    return markerContent;
}

function ilCreateMarker(map, latitude, longitude)
{
    var point = new google.maps.LatLng(latitude, longitude);
    if (ilAdvancedMarkerElement)
    {
        return new ilAdvancedMarkerElement({
            position: point,
            map: map,
            content: ilCreateMarkerContent(),
            anchorLeft: "-50%",
            anchorTop: "-100%"
        });
    }

    return new google.maps.Marker({
        position: point,
        icon: {
            url: "./assets/images/standard/icon_mapm.svg",
            scaledSize: new google.maps.Size(48, 80),
            anchor: new google.maps.Point(24, 80)
        },
        map: map
    });
}

function ilSetMarkerPosition(marker, loc)
{
    if (typeof marker.setPosition === "function")
    {
        marker.setPosition(loc);
        return;
    }
    marker.position = loc;
}

/**
 * Get set of user markers for a map
 */
function ilGetUserMarkers(id, map)
{
    if (ilMapUserMarker[id])
    {
        for (var i=0;i<ilMapUserMarker[id].length;i++)
        {
            var number = i;
            var marker = ilCreateMarker(map, ilMapUserMarker[id][i][0],
                ilMapUserMarker[id][i][1]);
            ilMapUserMarker[id][i][3] = marker;

            google.maps.event.addListener(marker, "click", function() {
                ilMapOpenInfoWindow(id, map, marker, number);
            });
        }
    }
}

function ilMapOpenInfoWindow(id, map, marker, j)
{
    var infowindow = new google.maps.InfoWindow({
        content: ilMapUserMarker[id][j][2]
    });
    infowindow.open({
        anchor: marker,
        map: map
    });
}

function ilShowUserMarker(id, j)
{
    var loc = new google.maps.LatLng(ilMapUserMarker[id][j][0], ilMapUserMarker[id][j][1]);
    ilMap[id].setCenter(loc);

    var infowindow = new google.maps.InfoWindow({
        content: ilMapUserMarker[id][j][2]
    });
    infowindow.open({
        anchor: ilMapUserMarker[id][j][3],
        map: ilMap[id]
    });

    return false;
}

function ilMapClicked(id, map, location)
{
    map.setCenter(location);
    ilUpdateLocationInput(id, map, location, "");
}

function ilLookupAddress(id, address)
{
    var map = ilMap[id];

    var geocoder = new google.maps.Geocoder();
    geocoder.geocode({address: address}, function(result)  {
        if (Array.isArray(result) && result.length > 0 && typeof result[0] === "object" && "geometry" in result[0]) {
            map.setCenter(result[0]["geometry"]["location"]);
            ilUpdateLocationInput(id, map, result[0]["geometry"]["location"],
                result[0]["formatted_address"]);
        }
        else
        {
            alert("Address: '" + address + "' not found");
        }
    });
}
window.ilLookupAddress = ilLookupAddress;
