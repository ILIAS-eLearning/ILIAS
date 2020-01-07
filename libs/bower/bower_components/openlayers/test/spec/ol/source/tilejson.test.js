

goog.require('ol.source.Source');
goog.require('ol.source.TileJSON');
goog.require('ol.Observable');


describe('ol.source.TileJSON', function() {

  describe('constructor', function() {

    it('returns a tileJSON source', function() {
      var source = new ol.source.TileJSON({
        url: 'spec/ol/data/tilejson.json'
      });
      expect(source).to.be.a(ol.source.Source);
      expect(source).to.be.a(ol.source.TileJSON);
    });
  });

  describe('#getTileJSON', function() {

    it('parses the tilejson file', function() {
      var source = new ol.source.TileJSON({
        url: 'spec/ol/data/tilejson.json'
      });
      source.on('change', function() {
        if (source.getState() === 'ready') {
          var tileJSON = source.getTileJSON();
          expect(tileJSON.name).to.eql('Geography Class');
          expect(tileJSON.version).to.eql('1.0.0');
        }
      });
    });

    it ('parses inline TileJSON', function() {
      var tileJSON = {
        bounds: [
          -180,
          -85.05112877980659,
          180,
          85.05112877980659
        ],
        center: [
          0,
          0,
          4
        ],
        created: 1322764050886,
        description: 'One of the example maps that comes with TileMill - a bright & colorful world map that blends retro and high-tech with its folded paper texture and interactive flag tooltips. ',
        download: 'https://a.tiles.mapbox.com/v3/mapbox.geography-class.mbtiles',
        embed: 'https://a.tiles.mapbox.com/v3/mapbox.geography-class.html',
        id: 'mapbox.geography-class',
        mapbox_logo: true,
        maxzoom: 8,
        minzoom: 0,
        name: 'Geography Class',
        private: false,
        scheme: 'xyz',
        tilejson: '2.2.0',
        tiles: [
          'https://a.tiles.mapbox.com/v3/mapbox.geography-class/{z}/{x}/{y}.png',
          'https://b.tiles.mapbox.com/v3/mapbox.geography-class/{z}/{x}/{y}.png'
        ],
        version: '1.0.0',
        webpage: 'https://a.tiles.mapbox.com/v3/mapbox.geography-class/page.html'
      };
      var source = new ol.source.TileJSON({
        tileJSON: tileJSON
      });
      expect(source.getState()).to.be('ready');
      expect(source.getTileUrlFunction()([0, 0, -1])).to.be('https://b.tiles.mapbox.com/v3/mapbox.geography-class/0/0/0.png');
    });
  });

  describe('#getState', function() {

    it('returns error on HTTP 404', function() {
      var source = new ol.source.TileJSON({
        url: 'invalid.jsonp'
      });
      source.on('change', function() {
        expect(source.getState()).to.eql('error');
        expect(source.getTileJSON()).to.eql(null);
      });
    });

    it('returns error on CORS issues', function() {
      var source = new ol.source.TileJSON({
        url: 'http://example.com'
      });
      source.on('change', function() {
        expect(source.getState()).to.eql('error');
        expect(source.getTileJSON()).to.eql(null);
      });
    });

    it('returns error on JSON parsing issues', function() {
      var source = new ol.source.TileJSON({
        url: '/'
      });
      source.on('change', function() {
        expect(source.getState()).to.eql('error');
        expect(source.getTileJSON()).to.eql(null);
      });
    });

  });

  describe('tileUrlFunction', function() {

    var source, tileGrid;

    beforeEach(function(done) {
      source = new ol.source.TileJSON({
        url: 'spec/ol/data/tilejson.json'
      });
      var key = source.on('change', function() {
        if (source.getState() === 'ready') {
          ol.Observable.unByKey(key);
          tileGrid = source.getTileGrid();
          done();
        }
      });
    });

    it('uses the correct tile coordinates', function() {

      var coordinate = [829330.2064098881, 5933916.615134273];
      var regex = /\/([0-9]*\/[0-9]*\/[0-9]*)\.png$/;
      var tileUrl;

      tileUrl = source.tileUrlFunction(
          tileGrid.getTileCoordForCoordAndZ(coordinate, 0));
      expect(tileUrl.match(regex)[1]).to.eql('0/0/0');

      tileUrl = source.tileUrlFunction(
          tileGrid.getTileCoordForCoordAndZ(coordinate, 1));
      expect(tileUrl.match(regex)[1]).to.eql('1/1/0');

      tileUrl = source.tileUrlFunction(
          tileGrid.getTileCoordForCoordAndZ(coordinate, 2));
      expect(tileUrl.match(regex)[1]).to.eql('2/2/1');

      tileUrl = source.tileUrlFunction(
          tileGrid.getTileCoordForCoordAndZ(coordinate, 3));
      expect(tileUrl.match(regex)[1]).to.eql('3/4/2');

      tileUrl = source.tileUrlFunction(
          tileGrid.getTileCoordForCoordAndZ(coordinate, 4));
      expect(tileUrl.match(regex)[1]).to.eql('4/8/5');

      tileUrl = source.tileUrlFunction(
          tileGrid.getTileCoordForCoordAndZ(coordinate, 5));
      expect(tileUrl.match(regex)[1]).to.eql('5/16/11');

      tileUrl = source.tileUrlFunction(
          tileGrid.getTileCoordForCoordAndZ(coordinate, 6));
      expect(tileUrl.match(regex)[1]).to.eql('6/33/22');

    });

  });

});
