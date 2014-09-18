

// Prototype for OpenLayers bindings.
// Needs libraries OpenLayers and jQuery.
// addressInvalid is a string that will be displayed, when a searched address wasn't found
// mapData is a dictionary containing the maps to be displayed as arrays with entrys
//      [central_latitude, central_longitude, zoom_level, display_central_marker, allow_zoom_and_pan, allow_replace_central_marker]

var _ilOpenLayers = function(OpenLayers, jQuery, addressInvalid, mapData, userMarkers) {
	// Maps
	this.maps = [];

	// Central markers
	this.centralMarkers = [];

	// Additional markers from user
	this.userMarkers = [];

	// That is the projection used by google maps and therefore by current ilias
	this.userProjection = new OpenLayers.Projection("EPSG:4326");

	// Thats the Projection used by OSM
	this.osmProjection = new OpenLayers.Projection("EPSG:900913");

	// URL for geolocation lookup
	this.geolocationURL = window.location.protocol + "//open.mapquestapi.com/nominatim/v1/search.php?format=json&q=";

	// this gets displayed in the address search field
	// if the address could not be found
	this.addressInvalid = addressInvalid;

	// Get location in OSM projection from longitude and latitude
	this.OSMPosFromLonLat = function (longitude, latitude) {
		var userPos = new OpenLayers.LonLat(longitude, latitude);
		return this.toOSMProjection(userPos);
	};

	// Transforms location from OSM to user projection
	this.toUserProjection = function (osm) {
		var _osm = osm.clone();
		_osm.transform(this.osmProjection, this.userProjection);
		return _osm;
	};

	// Transforms location from user projection to OSM projection
	this.toOSMProjection = function (user) {
		var _user = user.clone();
		_user.transform(this.userProjection, this.osmProjection);
		return _user;
	};

	// Image for markers...
	this.getMarkerImage = function() { return new OpenLayers.Icon(
		"./Services/Maps/images/mm_20_blue.png",
		new OpenLayers.Size(12, 20),
		new OpenLayers.Pixel(-6,-20)); };

	// ... and the shadow for it.
	this.getMarkerShadow = function() { return new OpenLayers.Icon(
		"./Services/Maps/images/mm_20_shadow.png",
		new OpenLayers.Size(22, 20),
		new OpenLayers.Pixel(-7,-20)); };

	// OpenLayers seems to define Icons in a way that only allows
	// the icon to be placed in one map at one location. Thats
	// why it is getMarkerImage/Shadow and not a plain icon.



	// Create a marker on map at latitude, longitude.
	// Returns an object for marker that has method
	// setPosition.
	this.createMarker = function(id, latitude, longitude)
	{
		var map = this.maps[id];

		var marker = function (map, latitude, longitude,  module) {
			var pos = module.OSMPosFromLonLat(longitude, latitude);

			this.marker = new OpenLayers.Marker( pos, module.getMarkerImage() );
			this.shadow = new OpenLayers.Marker( pos, module.getMarkerShadow() );

			map.__markerLayer__.addMarker(this.shadow);
			map.__markerLayer__.addMarker(this.marker);

			this.map = map;

			this.pos = pos;

			this.setPosition = (function (self) { return function (loc) {
				self.map.__markerLayer__.removeMarker(self.marker);
				self.map.__markerLayer__.removeMarker(self.shadow);

				self.marker.erase();
				self.shadow.erase();

				self.marker = new OpenLayers.Marker( loc, module.getMarkerImage() );
				self.shadow = new OpenLayers.Marker( loc, module.getMarkerShadow() );

				self.map.__markerLayer__.addMarker(self.shadow);
				self.map.__markerLayer__.addMarker(self.marker);

				self.pos = loc;
			};})(this);
		};

		return new marker(map, latitude, longitude, this);
	};

	// Sets the central position (and central marker if used) to the given position.
	///Sets inputs accordingly.
	this.jumpTo = function(id, pos)
	{
		var map = this.maps[id];
		map.panTo(pos);

		var userPos = this.toUserProjection(pos);

		jQuery("#" + id + "_address").val("");
		jQuery("#" + id + "_lat").val(userPos.lat);
		jQuery("#" + id + "_lng").val(userPos.lon);

		if (map.__replace_marker__) {
			this.setCentralMarker(id, pos);
		}

		map.__longitude__ = userPos.lon;
		map.__latitude__ = userPos.lat;
	};
	
	this.setCentralMarker = function (id, pos) {
		var map = this.maps[id];
		if (map.__centralMarker__) {
			map.__centralMarker__.setPosition(pos);
		}
	};

	// Look up address in geolocation service and jump
	// to that position.
	this.jumpToAddress = function (id, address) {
		var map = this.maps[id];

		jQuery("#" + id + "_address").attr("disabled", "disabled");
		jQuery("#" + id + "_search_address").attr("disabled", "disabled");
		jQuery("#" + id + "_lat").attr("disabled", "disabled");
		jQuery("#" + id + "_lng").attr("disabled", "disabled");

		jQuery.ajax({
			url: this.geolocationURL + address,
			data: {},
			dataType : "json" })
			.done( (function(module) {	return function (data) {
				if (data.length === 0) {
					jQuery("#" + id + "_address").val(module.addressInvalid);
					return;
				}

				var lon = parseInt(data[0].lon, 10);
				var lat = parseInt(data[0].lat, 10);

				var pos = module.OSMPosFromLonLat(data[0].lon, data[0].lat);

				module.jumpTo(id, pos);

				jQuery("#" + id + "_address").val(address);
			};})(this))
			.fail( function() {
				jQuery("#" + id + "_address").val("");
			})
			.always( function() {
				jQuery("#" + id + "_address").removeAttr("disabled");
				jQuery("#" + id + "_search_address").removeAttr("disabled");
				jQuery("#" + id + "_lat").removeAttr("disabled");
				jQuery("#" + id + "_lng").removeAttr("disabled");
			});
	};


	// Add a user marker at lon/lat to map with id.
	// User marker will display html.
	this.addUserMarker = function(id, lon, lat, html) {
		var map = this.maps[id];

		if (map.__userMarkers__ === undefined) {
			map.__userMarkers__ = [];
		}

		var marker = this.createMarker(id, lon, lat);
		map.__userMarkers__.push(marker);

		var feature = new OpenLayers.Feature(map.__markerLayer__, marker.pos);
		feature.closeBox = true;
		feature.popupClass = OpenLayers.Class(OpenLayers.Popup.Anchored, { autoSize : true, closeOnMove : false});
		feature.data.popupContentHTML = html;

		marker.feature = feature;

		feature.popup = feature.createPopup(true);
		map.addPopup(feature.popup);
		feature.popup.hide();
		marker.marker.events.register("click", feature, function(ev) {
			OpenLayers.Event.stop(ev);
			this.popup.toggle();
		});
	};
	
	// move to a user marker and open card
	this.moveToUserMarkerAndOpen = function(id, j) {
		var map = this.maps[id];
		var user_marker = map.__userMarkers__[j];
		if (user_marker) {
			var repl = map.__replace_marker__;
			map.__replace_marker__ = false;
			this.jumpTo(id, user_marker.pos);
			map.__replace_marker__ = repl;
			user_marker.feature.popup.show();
		}
		else {
			console.log("No user marker no. "+j+" for map "+id);
		}
	};
	
	var self = this;
	
	// Update map after changes in input fields
	this.updateMap = function(id) {
		var map = self.maps[id];
		
		var latitude = jQuery("#" + id + "_lat").val();
		var longitude = jQuery("#" + id + "_lng").val();
		var zoom = jQuery("#"+id+"_zoom").val();
		var pos = self.OSMPosFromLonLat(longitude, latitude);

		map.setCenter( pos, zoom );
		self.jumpTo(id, pos);
	};

	// Initialise a map on the page.
	// id - identifier of div to fill map in
	// latitude, longitude - central position to show in the map
	// zoom - zooming-level between 1 and 18
	// central_marker - boolean, display marker at central position?
	// nav_control - boolean, should the user be able to pan and zoom map?
	// replace_marker - boolean, should the user be able to replace the central marker by a click?
	//
	// Searches for inputs named $id_zoom, $id_latitude and $id_longitude.
	// If there are such inputs automatically updates the central position
	// of the map when inputs change and otherway round.
	// Also searches for input with id $id_address and and button with id
	// $id_search_address. If map position changes, the address will be set to
	// empty. If user clicks on $id_search_address, a geolocation will be searched
	// by the input of the address field and the central position of the map
	// will be set to that place.
	// If a central marker is used it can be replaced by a single click on
	// the map if replace_marker is set to true.
	this.initMap = function (id, latitude, longitude, zoom, central_marker, nav_control, replace_marker)
	{
		var mapControls = null;

		if (nav_control || nav_control === undefined) {
			mapControls = [ new OpenLayers.Control.Navigation(),
							new OpenLayers.Control.PanZoom() ];
		}
		else
			mapControls = [];

		var map = new OpenLayers.Map( id, {
			controls : mapControls
		});

		// layer for the actual map
		var mapLayer = new OpenLayers.Layer.OSM("OpenStreetMap");

		// central position of the map
		var mapPos = this.OSMPosFromLonLat(longitude, latitude);

		// show layer at central position with central position
		// and zooming level applied
		map.addLayer( mapLayer );
		map.setCenter( mapPos, zoom );


		// layer for central marker and user markers
		var markerLayer = new OpenLayers.Layer.Markers( "MarkerLayer" );
		map.addLayer( markerLayer );

		// Store layer marker at map object for later use
		map.__markerLayer__ = markerLayer;

		var zoomInput = jQuery("#" + id + "_zoom");

		// If theres an input it should be bound to the
		// zooming level of the map. Apply all changes two way.
		if(zoomInput.length > 0) {
			map.events.register("zoomend", this, function(ev) {
				var zoom = map.getZoom();
				zoomInput.val(zoom);
			});

			zoomInput.change( function(ev) {
				var zoom = parseInt(ev.target.value, 10);
				map.setCenter(undefined, zoom);
			});
		}

		var addressInput = jQuery("#" + id + "_address");

		// Automatically update map on change longitude input
		var lngInput = jQuery("#" + id + "_longitude");

		lngInput.change( (function (module) { return function(ev) {
			var lng = parseFloat(ev.target.value);

			// User inserted something we don't understand
			if (isNaN(lng)) {
				return;
			}

			var pos = module.OSMPosFromLonLat(lng, map.__latitude__);

			module.jumpTo(id, pos);
		};})(this));


		// Automatically update map on change of latitude input
		var latInput = jQuery("#" + id + "_latitude");

		latInput.change( ( function(module) { return function (ev) {
			var lat = parseFloat(ev.target.value);

			if(isNaN(lat)) {
				return;
			}

			var pos = module.OSMPosFromLonLat(map.__longitude__, lat);

			module.jumpTo(id, pos);
		};})(this));


		// Address search button
		var addressSearchButton = jQuery("#" + id + "_search_address");

		// event handler for jumping to address
		var addressJump = ( function(self, addressInput) { return function(ev) {
			ev.preventDefault();
			self.jumpToAddress(id, addressInput.val());
		};})(this, addressInput);

		addressSearchButton.click(addressJump);

		// Bind key press of enter in address input to the same function
		addressInput.keypress( function(ev) {
			if (ev.keyCode == 13) {
				addressJump(ev);
			}
		});

		map.__longitude__ = longitude;
		map.__latitude__ = latitude;
		map.__zoom__ = zoom;
		map.__id__ = id;
		map.__replace_marker__ = replace_marker;

		this.maps[id] = map;

		// Create central marker if users wants one.
		if (central_marker) {
			map.__centralMarker__ = this.createMarker(id, latitude, longitude);
			}


		if (replace_marker) {
			map.events.register("click", map, (function(module) { return function(ev) {
				ev.preventDefault();
				var lonLat = this.getLonLatFromViewPortPx(ev.xy);
				module.jumpTo(this.__id__, lonLat);
			};})(this));

		}
	};

	for (var id in mapData) {
		var map = mapData[id];

		this.initMap(id, map[0], map[1], map[2], map[3], map[4], map[5]);

		var mapUserMarkers = userMarkers[id];

		for (var cnt in mapUserMarkers) {
			var userMarkerData = mapUserMarkers[cnt];

			this.addUserMarker(id, userMarkerData[0], userMarkerData[1], userMarkerData[2]);
		}
	}
};

// For passing data from ilias to js
ilOLMapData = [];
ilOLUserMarkers = [];
ilOLInvalidAddress = undefined;

if (OpenLayers && jQuery && il.Util.addOnLoad)
{
	il.Util.addOnLoad(function () {
		ilOpenLayers = new _ilOpenLayers(OpenLayers, jQuery, ilOLInvalidAddress, ilOLMapData, ilOLUserMarkers);
		ilLookupAddress = function(id, address) {
			return ilOpenLayers.jumpToAddress(id, address);
		};
		ilUpdateMap = function (id) {
			return ilOpenLayers.updateMap(id);
		};
		ilShowUserMarker = function(id, j) {
			return ilOpenLayers.moveToUserMarkerAndOpen(id, j);
		};
	});
}