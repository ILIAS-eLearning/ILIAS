var ilMapData;

// init all maps on load
ilAddOnLoad(ilInitMaps)

// Call google unload function
ilAddOnUnload(GUnload)

/** 
* Hide all ilFormHelpLink elements
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
		zoom = 13
		var map = new GMap2(document.getElementById(id));
		map.setCenter(new GLatLng(latitude, longitude), zoom);
	}
}
