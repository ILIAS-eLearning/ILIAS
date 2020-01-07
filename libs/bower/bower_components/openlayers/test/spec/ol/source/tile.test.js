goog.require('ol');
goog.require('ol.Tile');
goog.require('ol.TileRange');
goog.require('ol.proj');
goog.require('ol.proj.Projection');
goog.require('ol.source.Source');
goog.require('ol.source.Tile');
goog.require('ol.tilecoord');
goog.require('ol.tilegrid.TileGrid');


/**
 * Tile source for tests that uses a EPSG:4326 based grid with 4 resolutions and
 * 256x256 tiles.
 *
 * @constructor
 * @extends {ol.source.Tile}
 * @param {Object.<string, ol.TileState>} tileStates Lookup of tile key to
 *     tile state.
 */
var MockTile = function(tileStates) {
  var tileGrid = new ol.tilegrid.TileGrid({
    resolutions: [360 / 256, 180 / 256, 90 / 256, 45 / 256],
    origin: [-180, -180],
    tileSize: 256
  });

  ol.source.Tile.call(this, {
    projection: ol.proj.get('EPSG:4326'),
    tileGrid: tileGrid
  });

  for (var key in tileStates) {
    this.tileCache.set(key, new ol.Tile(key.split('/'), tileStates[key]));
  }

};
ol.inherits(MockTile, ol.source.Tile);


/**
 * @inheritDoc
 */
MockTile.prototype.getTile = function(z, x, y) {
  var key = ol.tilecoord.getKeyZXY(z, x, y);
  if (this.tileCache.containsKey(key)) {
    return /** @type {!ol.Tile} */ (this.tileCache.get(key));
  } else {
    var tile = new ol.Tile(key, 0); // IDLE
    this.tileCache.set(key, tile);
    return tile;
  }
};

describe('ol.source.Tile', function() {

  describe('constructor', function() {
    it('returns a tile source', function() {
      var source = new ol.source.Tile({
        projection: ol.proj.get('EPSG:4326')
      });
      expect(source).to.be.a(ol.source.Source);
      expect(source).to.be.a(ol.source.Tile);
    });
    it('sets a custom cache size', function() {
      var projection = ol.proj.get('EPSG:4326');
      var source = new ol.source.Tile({
        projection: projection,
        cacheSize: 42
      });
      expect(source.getTileCacheForProjection(projection).highWaterMark).to.be(42);
    });
  });

  describe('#setKey()', function() {
    it('sets the source key', function() {
      var source = new ol.source.Tile({});
      expect(source.getKey()).to.equal('');

      var key = 'foo';
      source.setKey(key);
      expect(source.getKey()).to.equal(key);
    });
  });

  describe('#setKey()', function() {
    it('dispatches a change event', function(done) {
      var source = new ol.source.Tile({});

      var key = 'foo';
      source.once('change', function() {
        done();
      });
      source.setKey(key);
    });

    it('does not dispatch change if key does not change', function(done) {
      var source = new ol.source.Tile({});

      var key = 'foo';
      source.once('change', function() {
        source.once('change', function() {
          done(new Error('Unexpected change event after source.setKey()'));
        });
        setTimeout(function() {
          done();
        }, 10);
        source.setKey(key); // this should not result in a change event
      });

      source.setKey(key); // this should result in a change event
    });

  });

  describe('#forEachLoadedTile()', function() {

    var callback;
    beforeEach(function() {
      callback = sinon.spy();
    });

    it('does not call the callback if no tiles are loaded', function() {
      var source = new MockTile({});
      var grid = source.getTileGrid();
      var extent = [-180, -180, 180, 180];
      var zoom = 3;
      var range = grid.getTileRangeForExtentAndZ(extent, zoom);

      source.forEachLoadedTile(source.getProjection(), zoom, range, callback);
      expect(callback.callCount).to.be(0);
    });

    it('does not call getTile() if no tiles are loaded', function() {
      var source = new MockTile({});
      sinon.spy(source, 'getTile');
      var grid = source.getTileGrid();
      var extent = [-180, -180, 180, 180];
      var zoom = 3;
      var range = grid.getTileRangeForExtentAndZ(extent, zoom);

      source.forEachLoadedTile(source.getProjection(), zoom, range, callback);
      expect(source.getTile.callCount).to.be(0);
      source.getTile.restore();
    });


    it('calls callback for each loaded tile', function() {
      var source = new MockTile({
        '1/0/0': 2, // LOADED
        '1/0/1': 2, // LOADED
        '1/1/0': 1, // LOADING,
        '1/1/1': 2 // LOADED
      });

      var zoom = 1;
      var range = new ol.TileRange(0, 1, 0, 1);

      source.forEachLoadedTile(source.getProjection(), zoom, range, callback);
      expect(callback.callCount).to.be(3);
    });

    it('returns true if range is fully loaded', function() {
      // a source with no loaded tiles
      var source = new MockTile({
        '1/0/0': 2, // LOADED,
        '1/0/1': 2, // LOADED,
        '1/1/0': 2, // LOADED,
        '1/1/1': 2 // LOADED
      });

      var zoom = 1;
      var range = new ol.TileRange(0, 1, 0, 1);

      var covered = source.forEachLoadedTile(
          source.getProjection(), zoom, range,
          function() {
            return true;
          });
      expect(covered).to.be(true);
    });

    it('returns false if range is not fully loaded', function() {
      // a source with no loaded tiles
      var source = new MockTile({
        '1/0/0': 2, // LOADED,
        '1/0/1': 2, // LOADED,
        '1/1/0': 1, // LOADING,
        '1/1/1': 2 // LOADED
      });

      var zoom = 1;
      var range = new ol.TileRange(0, 1, 0, 1);

      var covered = source.forEachLoadedTile(
          source.getProjection(), zoom,
          range, function() {
            return true;
          });
      expect(covered).to.be(false);
    });

    it('allows callback to override loaded check', function() {
      // a source with no loaded tiles
      var source = new MockTile({
        '1/0/0': 2, // LOADED,
        '1/0/1': 2, // LOADED,
        '1/1/0': 2, // LOADED,
        '1/1/1': 2 // LOADED
      });

      var zoom = 1;
      var range = new ol.TileRange(0, 1, 0, 1);

      var covered = source.forEachLoadedTile(
          source.getProjection(), zoom, range,
          function() {
            return false;
          });
      expect(covered).to.be(false);
    });

  });

  describe('#getTileCoordForTileUrlFunction()', function() {

    it('returns the expected tile coordinate - {wrapX: true}', function() {
      var tileSource = new ol.source.Tile({
        projection: 'EPSG:3857',
        wrapX: true
      });

      var tileCoord = tileSource.getTileCoordForTileUrlFunction([6, -31, -23]);
      expect(tileCoord).to.eql([6, 33, -23]);

      tileCoord = tileSource.getTileCoordForTileUrlFunction([6, 33, -23]);
      expect(tileCoord).to.eql([6, 33, -23]);

      tileCoord = tileSource.getTileCoordForTileUrlFunction([6, 97, -23]);
      expect(tileCoord).to.eql([6, 33, -23]);
    });

    it('returns the expected tile coordinate - {wrapX: false}', function() {
      var tileSource = new ol.source.Tile({
        projection: 'EPSG:3857',
        wrapX: false
      });

      var tileCoord = tileSource.getTileCoordForTileUrlFunction([6, -31, -23]);
      expect(tileCoord).to.eql(null);

      tileCoord = tileSource.getTileCoordForTileUrlFunction([6, 33, -23]);
      expect(tileCoord).to.eql([6, 33, -23]);

      tileCoord = tileSource.getTileCoordForTileUrlFunction([6, 97, -23]);
      expect(tileCoord).to.eql(null);
    });

    it('works with wrapX and custom projection without extent', function() {
      var tileSource = new ol.source.Tile({
        projection: new ol.proj.Projection({
          code: 'foo',
          global: true,
          units: 'm'
        }),
        wrapX: true
      });

      var tileCoord = tileSource.getTileCoordForTileUrlFunction([6, -31, -23]);
      expect(tileCoord).to.eql([6, 33, -23]);
    });
  });

  describe('#refresh()', function() {
    it('checks clearing of internal state', function() {
      // create a source with one loaded tile
      var source = new MockTile({
        '1/0/0': 2 // LOADED
      });
      // check the loaded tile is there
      var tile = source.getTile(1, 0, 0);
      expect(tile).to.be.a(ol.Tile);
      // check tile cache is filled
      expect(source.tileCache.getCount()).to.eql(1);
      // refresh the source
      source.refresh();
      // check tile cache after refresh (should be empty)
      expect(source.tileCache.getCount()).to.eql(0);
    });
  });

});


describe('MockTile', function() {

  describe('constructor', function() {
    it('creates a tile source', function() {
      var source = new MockTile({});
      expect(source).to.be.a(ol.source.Tile);
      expect(source).to.be.a(MockTile);
    });
  });

  describe('#getTile()', function() {
    it('returns a tile with state based on constructor arg', function() {
      var source = new MockTile({
        '0/0/0': 2, // LOADED,
        '1/0/0': 2 // LOADED
      });
      var tile;

      // check a loaded tile
      tile = source.getTile(0, 0, 0);
      expect(tile).to.be.a(ol.Tile);
      expect(tile.state).to.be(2); // LOADED

      // check a tile that is not loaded
      tile = source.getTile(1, 0, -1);
      expect(tile).to.be.a(ol.Tile);
      expect(tile.state).to.be(0); // IDLE

      // check another loaded tile
      tile = source.getTile(1, 0, 0);
      expect(tile).to.be.a(ol.Tile);
      expect(tile.state).to.be(2); // LOADED

    });
  });

});
