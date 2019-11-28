goog.require('ol.Map');
goog.require('ol.View');
goog.require('ol.extent');
goog.require('ol.layer.Tile');
goog.require('ol.proj');
goog.require('ol.source.OSM');
goog.require('ol.source.TileImage');


var map = new ol.Map({
  layers: [
    new ol.layer.Tile({
      source: new ol.source.OSM()
    })
  ],
  target: 'map',
  view: new ol.View({
    projection: 'EPSG:3857',
    center: [0, 0],
    zoom: 1
  })
});


var queryInput = document.getElementById('epsg-query');
var searchButton = document.getElementById('epsg-search');
var resultSpan = document.getElementById('epsg-result');
var renderEdgesCheckbox = document.getElementById('render-edges');

function setProjection(code, name, proj4def, bbox) {
  if (code === null || name === null || proj4def === null || bbox === null) {
    resultSpan.innerHTML = 'Nothing usable found, using EPSG:3857...';
    map.setView(new ol.View({
      projection: 'EPSG:3857',
      center: [0, 0],
      zoom: 1
    }));
    return;
  }

  resultSpan.innerHTML = '(' + code + ') ' + name;

  var newProjCode = 'EPSG:' + code;
  proj4.defs(newProjCode, proj4def);
  var newProj = ol.proj.get(newProjCode);
  var fromLonLat = ol.proj.getTransform('EPSG:4326', newProj);

  // very approximate calculation of projection extent
  var extent = ol.extent.applyTransform(
      [bbox[1], bbox[2], bbox[3], bbox[0]], fromLonLat);
  newProj.setExtent(extent);
  var newView = new ol.View({
    projection: newProj
  });
  map.setView(newView);
  newView.fit(extent);
}


function search(query) {
  resultSpan.innerHTML = 'Searching ...';
  fetch('https://epsg.io/?format=json&q=' + query).then(function(response) {
    return response.json();
  }).then(function(json) {
    var results = json['results'];
    if (results && results.length > 0) {
      for (var i = 0, ii = results.length; i < ii; i++) {
        var result = results[i];
        if (result) {
          var code = result['code'], name = result['name'],
              proj4def = result['proj4'], bbox = result['bbox'];
          if (code && code.length > 0 && proj4def && proj4def.length > 0 &&
              bbox && bbox.length == 4) {
            setProjection(code, name, proj4def, bbox);
            return;
          }
        }
      }
    }
    setProjection(null, null, null, null);
  });
}


/**
 * Handle click event.
 * @param {Event} event The event.
 */
searchButton.onclick = function(event) {
  search(queryInput.value);
  event.preventDefault();
};


/**
 * Handle change event.
 */
renderEdgesCheckbox.onchange = function() {
  map.getLayers().forEach(function(layer) {
    if (layer instanceof ol.layer.Tile) {
      var source = layer.getSource();
      if (source instanceof ol.source.TileImage) {
        source.setRenderReprojectionEdges(renderEdgesCheckbox.checked);
      }
    }
  });
};
