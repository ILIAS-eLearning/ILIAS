ServiceOpenLayers = {
	ol: null,
	$: null,
	map_data: null,
	user_markers: null,
	view: null,

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
		obj.view = new ol.View();
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
			this.initMap(id, nav_control);
			this.initUserMarkers(id, pos, central_marker, replace_marker);
		}
	},

	/**
	 * Init the user_markers array.
	 *
	 * @param 	{string} 	id
	 * @param 	{number} 	pos
	 * @param 	{boolean} 	central_marker
	 * @param 	{boolean} 	replace_marker
	 * @returns {void}
	 */
	initUserMarkers: function(
		id,
		pos,
		central_marker,
		replace_marker
	) {
		console.log(central_marker);
		console.log(replace_marker);
		if(replace_marker) {
			this.deleteAllMarkers(id);
			this.setMarker(id, pos);
			return;
		}

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
		this.view.setCenter(pos);
		this.view.setMaxZoom(18);
		this.view.setMinZoom(0);
		this.view.setZoom(zoom);

		// Bind the maps zoom level to the select box zoom.
		this.view.on("propertychange", function(e) {
			if(e.key === 'resolution') {
				$("#" + id + "_zoom").val(Math.floor(this.view.getZoom()));
			}
		}, this);
	},

	/**
	 * Initialise a map on the page.
	 *
	 * @param 	{string} 	id
	 * @param 	{boolean} 	nav_control
	 */
	initMap: function (id, nav_control) {
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
			view: this.view
		});

		this.map.on("click", function(e) {
			e.preventDefault();
			var center = e.coordinate;
			this.jumpTo(id, center);
			this.deleteAllMarkers(id);
			this.setMarker(id, center);
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
		this.view.animate({
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
		$("#" + id + "_address").attr("disabled", "disabled");
		$("#" + id + "_search_address").attr("disabled", "disabled");
		$("#" + id + "_lng").attr("disabled", "disabled");
		$("#" + id + "_lat").attr("disabled", "disabled");

		$.ajax({
			url: this.geolocationURL.replace("[QUERY]", address),
			data: {},
			dataType : "json"
		}).done((function(module) {
			return function (data) {
				if (data.length === 0) {
					$("#" + id + "_address").val(module.addressInvalid);
					return;
				}
				lon = parseFloat(data[0].lon, 10);
				lat = parseFloat(data[0].lat, 10);

				pos = module.posToOSM([lon, lat]);

				module.jumpTo(id, pos, 16);
				module.deleteAllMarkers(id);
				module.setMarker(id, pos);
				module.updateInputFields(id, pos);

				$("#" + id + "_address").val(address);
			};
		})(this))
		.fail(function() {
			$("#" + id + "_address").val("");
		})
		.always( function() {
			$("#" + id + "_address").removeAttr("disabled");
			$("#" + id + "_search_address").removeAttr("disabled");
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
	setMarker: function(id, pos, elem = null) {
		var clicked = false;
		var container = document.getElementById(id);
		var element = document.createElement("div");
		element.className = "marker";
		container.appendChild(element);
		var popup = new this.ol.Overlay({
			element: element
		});
		popup.setOffset([-7.5, -23.5]);
		popup.setPosition(pos);
		this.map.addOverlay(popup);

		element.innerHTML = "<img src='./Services/Maps/images/mm_20_blue.png'></img>";
		element.addEventListener("click", (function(module) {
			return function() {
				if(elem && !clicked) {
					var container = document.getElementById(id);
					var append = document.createElement("div");
					append.className = "arrow_box";
					append.innerHTML = elem;
					container.appendChild(append);

					var user = new module.ol.Overlay({
						element: append
					});
					user.setOffset([15.5, -57.5]);
					user.setPosition(pos);
					module.map.addOverlay(user);
				}
				clicked = true;
			}
		})(this));
	},

	/**
	 * Remove all child elements.
	 *
	 * @param 	{stirng} id
	 *
	 */
	deleteAllMarkers: function(id) {
		marker = document.getElementsByClassName('marker');
		for (var i = 0; i < marker.length; i++) {
			marker[i].remove();
		}
	},

	// move to a user marker and open card
	moveToUserMarkerAndOpen: function(id, j) {
		var user_marker = this.user_markers[j];
		if (user_marker) {
			this.jumpTo(id, user_marker[0], 16);
		}
		else {
			console.log("No user marker no. "+j+" for map "+id);
		}
	},

	/**
	 * Update the longitute, langitude and the zoom of the map.
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
		this.view.setZoom(zoom);
		this.jumpTo(id, pos);
		this.updateInputFields(id, pos);
	},

	/**
	 * Update the input fields.
	 *
	 * @param 	{string} 	id
	 * @param 	{array} 	pos
	 * @return 	{void}
	 */
	updateInputFields: function(id, pos) {
		var human_pos = this.posToHuman(pos);
		$("#" + id + "_address").val("");
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