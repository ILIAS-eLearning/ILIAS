ilMapData = Array();
ilMap = Array();
ilMapUserMarker = Array();
ilMarkerIcon = new GIcon();
ilMarkerIcon.image = "./Services/GoogleMaps/images/mm_20_blue.png";
ilMarkerIcon.shadow = "./Services/GoogleMaps/images/mm_20_shadow.png";
ilMarkerIcon.iconSize = new GSize(12, 20);
ilMarkerIcon.shadowSize = new GSize(22, 20);
ilMarkerIcon.iconAnchor = new GPoint(6, 20);
ilMarkerIcon.infoWindowAnchor = new GPoint(5, 1);

// init all maps on load
ilAddOnLoad(ilInitMaps)

// Call google unload function
ilAddOnUnload(GUnload)

/** 
* Init all maps
*/
function ilInitMaps()
{
	var obj

	// get all spans
	obj = document.getElementsByTagName('div')
	
	// run through them
	for (var i=0;i<obj.length;i++)
	{
		// if it has a class of helpLink
		if(/ilGoogleMap/.test(obj[i].className))
		{
			ilInitMap(obj[i].id, ilMapData[obj[i].id][0], ilMapData[obj[i].id][1],
				ilMapData[obj[i].id][2], ilMapData[obj[i].id][3], ilMapData[obj[i].id][4],
				ilMapData[obj[i].id][5], ilMapData[obj[i].id][6]);
		}
	}
}


/** 
* Init a goole map
*/
function ilInitMap(id, latitude, longitude, zoom, type_control,
	nav_control, update_listener, large_map_control)
{
	if (GBrowserIsCompatible())
	{
		// IMPORTANT: setCenter MUST be the first thing we
		// do with the map, do not add any code between the next two lines
		var map = new GMap2(document.getElementById(id));
		map.setCenter(new GLatLng(latitude, longitude), zoom);
		
		var mgr = new GMarkerManager(map);
		mgr.addMarkers(ilGetUserMarkers(id, map), 1);
		mgr.refresh();
		
		if (nav_control)
		{
			map.addControl(new GSmallMapControl());
		}
		if (type_control)
		{
			map.addControl(new GMapTypeControl());
		}
		if (large_map_control)
		{
			map.addControl(new GLargeMapControl());
		}
		if (update_listener)
		{
			GEvent.addListener(map, "moveend", function() {
				ilUpdateLocationInput(id, map)});
		}
		ilMap[id] = map;
	}
}

/**
*  Update input fields from map properties
*/
function ilUpdateLocationInput(id, map)
{
	loc = map.getCenter();
	zoom = map.getZoom();
	lat_input = document.getElementById(id + "_lat");
	//lat_input.setAttribute("value", loc.lat());
	lat_input.value = loc.lat();
	lng_input = document.getElementById(id + "_lng");
	//lng_input.setAttribute("value", loc.lng());
	lng_input.value = loc.lng();
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

	map.panTo(new GLatLng(lat, lng));
	map.setZoom(parseInt(zoom));
	lng_input.value = lng;
	lat_input.value = lat;
}

/**
* Get set of user markers for a map
*/
function ilGetUserMarkers(id, map)
{
	var batch = [];
	var t;
	if (ilMapUserMarker[id])
	{
		for (var i=0;i<ilMapUserMarker[id].length;i++)
		{
			point = new GLatLng(ilMapUserMarker[id][i][0],
				ilMapUserMarker[id][i][1]);
			marker = new GMarker(point, {icon: ilMarkerIcon});
			batch.push(marker);
			j = i;
			GEvent.addListener(marker, "click", function() {
				ilMapOpenInfoWindow(id, j);
			});
		}
	}
	return batch;
}

function ilMapOpenInfoWindow(id, j)
{
	marker.openInfoWindowHtml(ilMapUserMarker[id][j][2]);
}
