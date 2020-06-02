/**
 * Holds all functionality to work with openlayer maps.
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
var ServiceOpenLayers = {
	ol: null,
	$: null,
	map_data: null,
	user_markers: null,
	views: {},

	/**
	 * Create a ServiceOpenLayers object.
	 *
	 * @param 	{object} 	ol
	 * @param 	{object} 	jQuery
	 * @param 	{array} 	map_data
	 * @param 	{string} 	invalid_address
	 * @param 	{array} 	user_markers
	 * @return 	{object}
	 */
	create: function(ol, jQuery, map_data, invalid_address, user_markers) {
		var obj = Object.create(this);
		obj.ol = ol;
		obj.$ = jQuery;
		obj.map_data = map_data;
		obj.invalid_address = invalid_address;
		obj.user_markers = user_markers;
		return obj;
	},

	/**
	 * Init Object.
	 *
	 * @return {void}
	 */
	init: function() {
		for (var id in ilOLMapData) {
			var map = ilOLMapData[id];
			var zoom = map[2];
			var central_marker = map[3];
			var nav_control = map[4];
			var replace_marker = map[5];
			var geo = map[7];
			var pos = this.posToOSM([map[1], map[0]]);

			this.geolocationURL = window.location.protocol + "//"+ geo;
			this.initView(id, pos, zoom);
			this.initMap(id, replace_marker);
			this.initUserMarkers(id, pos, replace_marker, central_marker);
		}
	},

	/**
	 * Init the user_markers array.
	 *
	 * @param 	{string} 	id
	 * @param 	{number} 	pos
	 * @param 	{boolean} 	replace_marker
	 * @param 	{boolean} 	central_marker
	 * @returns {void}
	 */
	initUserMarkers: function(
		id,
		pos,
		replace_marker,
		central_marker
	) {
		if(replace_marker || central_marker) {
			this.deleteAllMarkers(id);
			this.setMarker(id, pos);
			return;
		}

		// Only for participants overview.
		// Navigation is managed by participants-buttons here.
		this.map.removeEventListener('click');
		var mapUserMarkers = this.user_markers[id];
		for (var cnt in mapUserMarkers) {
			var userMarkerData = mapUserMarkers[cnt];
			var pos = this.posToOSM([userMarkerData[0], userMarkerData[1]]);
			this.user_markers[cnt] = [pos, userMarkerData[2]];
			this.setMarker(id, pos, userMarkerData[2]);
		}
	},

	/**
	 * Init a view object.
	 *
	 * @param 	{string} id
	 * @param 	{number} pos
	 * @param 	{number} zoom
	 * @returns {void}
	 */
	initView: function(id, pos, zoom) {
		this.views[id] = new ol.View();
		this.views[id].setCenter(pos);
		this.views[id].setMaxZoom(18);
		this.views[id].setMinZoom(0);
		this.views[id].setZoom(zoom);

		// Bind the maps zoom level to the select box zoom.
		this.views[id].on("propertychange", function(e) {
			if(e.key === 'resolution') {
				$("#" + id + "_zoom").val(Math.floor(this.views[id].getZoom()));
			}
		}, this);
	},

	/**
	 * Initialise a map on the page.
	 *
	 * @param 	{string} 	id
	 * @param 	{boolean} 	replace_marker
	 * @return 	{void}
	 */
	initMap: function (id, replace_marker) {
		this.map = new this.ol.Map({
			layers: [
				new this.ol.layer.Tile({
					preload: 4,
					source: new this.ol.source.OSM()
				}),
			],
			target: id,
			controls: this.ol.control.defaults().extend([
				new this.ol.control.FullScreen()
			]),
			loadTilesWhileAnimating: true,
			view: this.views[id]
		});

		this.map.on("click", function(e) {
			e.preventDefault();
			var center = e.coordinate;
			this.jumpTo(id, center);
			if (replace_marker) {
				this.deleteAllMarkers(id);
				this.setMarker(id, center);
			}
			this.updateInputFields(id, center);
		}, this);
	},

	/**
	 * Transform a coordinate from OSM projection to human readable projection.
	 *
	 * @param 	{array} pos 	[longitude, latitude]
	 * @return 	{array} 		[longitude, latitude]
	 */
	posToHuman: function(pos) {
		return this.ol.proj.transform(pos, "EPSG:3857", "EPSG:4326")
	},

	/**
	 * Transform a coordinate from human readable projection to OSM projection.
	 *
	 * @param 	{array} pos 	[longitude, latitude]
	 * @return 	{array} 		[longitude, latitude]
	 */
	posToOSM: function(pos) {
		return this.ol.proj.transform(pos, "EPSG:4326", "EPSG:3857");
	},

	/**
	 * Jump to a position on the map.
	 *
	 * @param 	{string} 	id
	 * @param 	{array} 	pos 	[longitude, latitude]
	 * @param 	{number} 	zoom
	 * @return 	{void}
	 */
	jumpTo: function(id, pos, zoom) {
		this.views[id].animate({
			center: pos,
			duration: 2000,
			zoom: zoom
		});
	},

	/**
	 * Looks up for an user given Address.
	 *
	 * @param 	{string} id
	 * @param 	{stirng} address
	 * @return 	{void}
	 */
	jumpToAddress: function(id, address) {
		$("#" + id + "_addr").attr("disabled", "disabled");
		$("#" + id + "_lng").attr("disabled", "disabled");
		$("#" + id + "_lat").attr("disabled", "disabled");

		$.ajax({
			url: this.geolocationURL.replace("[QUERY]", address),
			data: {},
			dataType : "json"
		}).done((function(module) {
			return function (data) {
				if (data.length === 0) {
					$("#" + id + "_addr").val(module.addressInvalid);
					return;
				}
				var lon = parseFloat(data[0].lon, 10);
				var lat = parseFloat(data[0].lat, 10);

				var pos = module.posToOSM([lon, lat]);

				module.jumpTo(id, pos, 16);
				module.deleteAllMarkers(id);
				module.setMarker(id, pos);
				module.updateInputFields(id, pos, address);
			};
		})(this))
		.fail(function() {
			$("#" + id + "_address").val("");
		})
		.always( function() {
			$("#" + id + "_addr").removeAttr("disabled");
			$("#" + id + "_lng").removeAttr("disabled");
			$("#" + id + "_lat").removeAttr("disabled");
		});
	},

	/**
	 * Force throwing a resize event.
	 *
	 * @return 	{void}
	 */
	forceResize: function() {
		$('input[onclick*="il.Form.showSubForm"]').each(function() {
			$(this).attr(
				'onclick',
				$('input[onclick*="il.Form.showSubForm"]')
				.attr('onclick') + "window.dispatchEvent(new Event('resize'));");
		});
	},

	/**
	 * Set a marker at the given position at the map.
	 *
	 * @param 	{string} 	id
	 * @param 	{array} 	pos 	[longitude, latitude]
	 * @return 	{void}
	 */
	setMarker: function(id, pos) {
		var clicked = false;
		var container = document.getElementById(id);
		var element = document.createElement("div");
		element.className = "marker";
		container.appendChild(element);
		var marker = new this.ol.Overlay({
			element: element
		});
		marker.setOffset([-7.5, -23.5]);
		marker.setPosition(pos);
		this.map.addOverlay(marker);

		element.innerHTML = "<img src='./Services/Maps/images/mm_20_blue.png'>";
	},

	/**
	 * Remove all child elements.
	 *
	 * @param 	{string} 	id
	 * @returns 	{void}
	 */
	deleteAllMarkers: function(id) {
		var marker = document.getElementById(id).querySelectorAll('.marker');
		for (var i = 0; i < marker.length; i++) {
			marker[i].remove();
		}
	},

	/**
	 * Move to a user marker and open popup.
	 *
	 * @param 	{string} id
	 * @param 	{number} j 	Counter for user_markers.
	 * @returns {void}
	 */
	moveToUserMarkerAndOpen: function(id, j) {
		var user_marker = this.user_markers[j];
		if (user_marker) {
			this.deleteAllPopups();
			this.jumpTo(id, user_marker[0], 16);
			this.setPopup(id, user_marker[0], user_marker[1]);
		}
		else {
			console.log("No user marker no. "+j+" for map "+id);
		}
	},

	/**
	 * Set a popup window to pos.
	 *
	 * @param 	{string} 	id
	 * @param 	{array} 	pos
	 * @param 	{string} 	elem 	Can hold html or pure text.
	 * @returns {void}
	 */
	setPopup: function(id, pos, elem) {
		var container = document.getElementById(id);
		var append = document.createElement("div");
		append.className = "arrow_box";
		append.addEventListener('click', (function(module) {
			return function() {
				module.deleteAllPopups();
			}
		})(this));
		append.innerHTML = elem;
		container.appendChild(append);

		var popup = new this.ol.Overlay({
			element: append,
			insertFirst: false
		});
		popup.setOffset([15.5, -53.5]);
		popup.setPosition(pos);
		this.map.addOverlay(popup);
	},

	/**
	 * Delete all popups with class arrow_box.
	 *
	 * @returns 	{void}
	 */
	deleteAllPopups: function() {
		var popups = document.getElementsByClassName('arrow_box');
		for (var i = 0; i < popups.length; i++) {
			popups[i].remove();
		}
	},

	/**
	 * Update the longitude, latitude and the zoom of the map.
	 *
	 * @param 	{string} id
	 * @return 	{void}
	 */
	updateMap: function(id) {
		var lat = parseFloat($("#" + id + "_lat").val());
		var lon = parseFloat($("#" + id + "_lng").val());
		var zoom = $("#"+id+"_zoom").val();
		var pos = this.posToOSM([lon, lat]);

		//this.updateMarkers(id);
		this.views[id].setZoom(zoom);
		this.jumpTo(id, pos);
		this.updateInputFields(id, pos);
	},

	/**
	 * Update the input fields.
	 *
	 * @param 	{string} 	id
	 * @param 	{array} 	pos 	[longitude, latitude]
	 * @param 	{string} 	address
	 * @return 	{void}
	 */
	updateInputFields: function(id, pos, address) {
		address = address || "undefined";
		var human_pos = this.posToHuman(pos);
		$("#" + id + "_addr").val(address);
		$("#" + id + "_lng").val(human_pos[0]);
		$("#" + id + "_lat").val(human_pos[1]);
	},
}

// For passing data from ilias to js
var ilOLMapData = [];
var ilOLUserMarkers = [];
var ilOLInvalidAddress = undefined;

if (ol && jQuery && il.Util.addOnLoad) {
	il.Util.addOnLoad(function() {
		var openLayer = ServiceOpenLayers.create(ol, jQuery, ilOLInvalidAddress, ilOLMapData, ilOLUserMarkers);

		ilLookupAddress = function(id, address) {
			return openLayer.jumpToAddress(id, address);
		};

		ilUpdateMap = function (id) {
			return openLayer.updateMap(id);
		};

		ilShowUserMarker = function(id, counter) {
			return openLayer.moveToUserMarkerAndOpen(id, counter);
		};

		openLayer.forceResize(jQuery);
		openLayer.init();
	});
}
