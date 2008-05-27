ilMapData = Array();
ilMap = Array();
ilMgr = Array();
ilCM = Array();
ilMapUserMarker = Array();

// if (typeof(GIcon) == "function") // would do the same
if (window.GIcon)
{
	ilMarkerIcon = new GIcon();
	ilMarkerIcon.image = "./Services/GoogleMaps/images/mm_20_blue.png";
	ilMarkerIcon.shadow = "./Services/GoogleMaps/images/mm_20_shadow.png";
	ilMarkerIcon.iconSize = new GSize(12, 20);
	ilMarkerIcon.shadowSize = new GSize(22, 20);
	ilMarkerIcon.iconAnchor = new GPoint(6, 20);
	ilMarkerIcon.infoWindowAnchor = new GPoint(5, 1);
}

if (window.GIcon)
{
	// init all maps on load
	ilAddOnLoad(ilInitMaps)

	// Call google unload function
	ilAddOnUnload(GUnload)
}

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
	if (GBrowserIsCompatible())
	{
		// IMPORTANT: setCenter MUST be the first thing we
		// do with the map, do not add any code between the next two lines
		var map = new GMap2(document.getElementById(id));
		map.setCenter(new GLatLng(latitude, longitude), zoom);
		
		var mgr = new GMarkerManager(map);
		mgr.addMarkers(ilGetUserMarkers(id, map), 1);
		
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
				ilUpdateZoomInput(id, map)});
		}
		if (central_marker)
		{
			//var cm = [];
			point = new GLatLng(latitude, longitude);
			var marker = new GMarker(point, {icon: ilMarkerIcon});
			//cm.push(marker);
			//mgr.addMarkers(cm, 1);
			ilCM[id] = marker;
			map.addOverlay(marker);
			
			GEvent.addListener(map, "click", function(marker, point)
			{
				ilMapClicked(map, point, id);
			});
		}

		ilMap[id] = map;
		ilMgr[id] = mgr;

		mgr.refresh();
	}
}

/**
*  Update input fields from map properties
*/
function ilUpdateLocationInput(id, map, loc)
{
	//loc = map.getCenter();
	zoom = map.getZoom();
	lat_input = document.getElementById(id + "_lat");
	if (!lat_input)
	{
		return;
	}
	//lat_input.setAttribute("value", loc.lat());
	lat_input.value = loc.lat();
	lng_input = document.getElementById(id + "_lng");
	//lng_input.setAttribute("value", loc.lng());
	lng_input.value = loc.lng();
	zoom_input = document.getElementById(id + "_zoom");
	zoom_input.selectedIndex = zoom;
	
	if (ilCM[id])
	{
		ilCM[id].setPoint(loc);
		map.removeOverlay(ilCM[id]);
		var marker = new GMarker(loc, {icon: ilMarkerIcon});
		ilCM[id] = marker;
		map.addOverlay(marker);
	}
}

/**
*  Update input fields from map properties
*/
function ilUpdateZoomInput(id, map, loc)
{
	//loc = map.getCenter();
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
	map.panTo(new GLatLng(lat, lng));
	lng_input.value = lng;
	lat_input.value = lat;
	if (ilCM[id])
	{
		map.removeOverlay(ilCM[id]);
		point = new GLatLng(lat, lng);
		var marker = new GMarker(point, {icon: ilMarkerIcon});
		ilCM[id] = marker;
		map.addOverlay(marker);
	}
}

/**
* Get set of user markers for a map
*/
function ilGetUserMarkers(id, map)
{
	var batch = [];
	var t;
	var j;
	
	// Creates a marker at the given point with the given number label
        function ilCreateMarker(id, point, number) {
          var marker = new GMarker(point, {icon: ilMarkerIcon});
          GEvent.addListener(marker, "click", function() {
			ilMapOpenInfoWindow(marker, id, number);
          });
          return marker;
        }

	if (ilMapUserMarker[id])
	{
	
		for (var i=0;i<ilMapUserMarker[id].length;i++)
		{
			point = new GLatLng(ilMapUserMarker[id][i][0],
				ilMapUserMarker[id][i][1]);
			marker = ilCreateMarker(id, point, i);
			ilMapUserMarker[id][i][3] = marker;
			batch.push(marker);
		}
		
		/* we do not know, why this is not working...
		for (var i=0;i<ilMapUserMarker[id].length;i++)
		{
			point = new GLatLng(ilMapUserMarker[id][i][0],
				ilMapUserMarker[id][i][1]);
			marker = new GMarker(point, {icon: ilMarkerIcon});
			
			GEvent.addListener(marker, "click", function() {
				ilMapOpenInfoWindow(marker, id, i);
			});
			batch.push(marker);
		}*/
	}
	return batch;
}

function ilMapOpenInfoWindow(marker, id, j)
{
	marker.openInfoWindowHtml(ilMapUserMarker[id][j][2]);
}

function ilShowUserMarker(id, j)
{
	ilMap[id].panTo(new GLatLng(ilMapUserMarker[id][j][0], ilMapUserMarker[id][j][1]));
	ilMapUserMarker[id][j][3].openInfoWindowHtml(ilMapUserMarker[id][j][2]);
	return false;
}

function ilMapClicked(map, point, id)
{
	//alert("hallo " + id);
	map.panTo(point);
	ilUpdateLocationInput(id, map, point);
}
