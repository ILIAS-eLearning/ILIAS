ilMapData = Array();
ilMap = Array();
ilCM = Array();
ilMapUserMarker = Array();

if (google.maps)
{
	var ilMarkerImage = new google.maps.MarkerImage(
		"./Services/Maps/images/mm_20_blue.png",      
		new google.maps.Size(12, 20),
		new google.maps.Point(0,0),
		new google.maps.Point(6, 20));
		
	var ilMarkerShadow = new google.maps.MarkerImage(
		"./Services/Maps/images/mm_20_shadow.png",
		new google.maps.Size(22, 20),
		new google.maps.Point(0,0),
		new google.maps.Point(0, 32));
}

if (google.maps)
{
	il.Util.addOnLoad(ilInitMaps);
}
/** 
* Init all maps
*/
function ilInitMaps()
{
	var obj;

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
	var mapLatLng = new google.maps.LatLng(latitude, longitude);	
	var mapOptions = {
		zoom: zoom,
		center: mapLatLng,
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		streetViewControl: false,
		mapTypeControl: type_control,
		scaleControl: true,
		panControl: (nav_control || large_map_control)
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
		ilCM[id].setPosition(loc);
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
		ilCM[id].setPosition(loc);
	}
}

function ilCreateMarker(map, latitude, longitude)
{
	var point = new google.maps.LatLng(latitude, longitude);
	var marker = new google.maps.Marker({
			position: point,
			icon: ilMarkerImage,
			shadow: ilMarkerShadow,
			map: map
	});				
   return marker;
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
	infowindow.open(map, marker);
}

function ilShowUserMarker(id, j)
{
	var loc = new google.maps.LatLng(ilMapUserMarker[id][j][0], ilMapUserMarker[id][j][1]);
	ilMap[id].setCenter(loc);
	
	var infowindow = new google.maps.InfoWindow({
		content: ilMapUserMarker[id][j][2]
	});
	infowindow.open(ilMap[id], ilMapUserMarker[id][j][3]);
	
	return false;
}

function ilMapClicked(id, map, location)
{
	map.setCenter(location);
    ilUpdateLocationInput(id, map, location);
}

function ilLookupAddress(id, address)
{
	var map = ilMap[id];
	
	var geocoder = new google.maps.Geocoder();
	geocoder.geocode({address: address}, function(result)
	{
		if (result[0]["geometry"])
		{
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

