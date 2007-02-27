ilMapData = Array();
ilMap = Array();

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
			ilInitMap(obj[i].id, ilMapData[obj[i].id][0], ilMapData[obj[i].id][1], ilMapData[obj[i].id][2]);
		}
	}
}


/** 
* Init a goole map
*/
function ilInitMap(id, latitude, longitude, zoom)
{
	if (GBrowserIsCompatible())
	{
		var map = new GMap2(document.getElementById(id));
		map.addControl(new GSmallMapControl());
		map.addControl(new GMapTypeControl());
		GEvent.addListener(map, "moveend", function() {
			ilUpdateLocationInput(id, map)});
		map.setCenter(new GLatLng(latitude, longitude), zoom);
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
	lat_input.setAttribute("value", loc.lat()); 
	lng_input = document.getElementById(id + "_lng");
	lng_input.setAttribute("value", loc.lng());
	zoom_input = document.getElementById(id + "_zoom");
	zoom_input.selectedIndex = zoom;
}

/**
*  Update map properties from input fields
*/
function ilUpdateMap(id)
{
	map = ilMap[id];
	lat_input = document.getElementById(id + "_lat");
	lng_input = document.getElementById(id + "_lng");
	zoom_input = document.getElementById(id + "_zoom");
	var zoom = zoom_input.value;
	map.panTo(new GLatLng(lat_input.value, lng_input.value));
	map.setZoom(parseInt(zoom_input.value));
}
