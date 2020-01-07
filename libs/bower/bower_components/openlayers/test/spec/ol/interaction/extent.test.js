

goog.require('ol.Map');
goog.require('ol.MapBrowserPointerEvent');
goog.require('ol.View');
goog.require('ol.interaction.Extent');
goog.require('ol.pointer.PointerEvent');

describe('ol.interaction.Extent', function() {
  var map, interaction;

  var width = 360;
  var height = 180;

  beforeEach(function() {
    var target = createMapDiv(width, height);

    map = new ol.Map({
      target: target,
      layers: [],
      view: new ol.View({
        projection: 'EPSG:4326',
        center: [0, 0],
        resolution: 1
      })
    });
    map.renderSync();

    interaction = new ol.interaction.Extent();
    map.addInteraction(interaction);
  });

  afterEach(function() {
    if (map) {
      disposeMap(map);
    }
    map = null;
    interaction = null;
  });

  /**
   * Simulates a browser event on the map viewport.  The client x/y location
   * will be adjusted as if the map were centered at 0,0.
   * @param {string} type Event type.
   * @param {number} x Horizontal offset from map center.
   * @param {number} y Vertical offset from map center.
   * @param {boolean=} opt_shiftKey Shift key is pressed.
   * @param {number} button The mouse button.
   */
  function simulateEvent(type, x, y, opt_shiftKey, button) {
    var viewport = map.getViewport();
    // calculated in case body has top < 0 (test runner with small window)
    var position = viewport.getBoundingClientRect();
    var shiftKey = opt_shiftKey !== undefined ? opt_shiftKey : false;
    var pointerEvent = new ol.pointer.PointerEvent(type, {
      type: type,
      button: button,
      clientX: position.left + x + width / 2,
      clientY: position.top - y + height / 2,
      shiftKey: shiftKey
    });
    var event = new ol.MapBrowserPointerEvent(type, map, pointerEvent);
    event.pointerEvent.pointerId = 1;
    map.handleMapBrowserEvent(event);
  }

  describe('Constructor', function() {

    it('can be configured with an extent', function() {
      expect(function() {
        new ol.interaction.Extent({
          extent: [-10, -10, 10, 10]
        });
      }).to.not.throwException();
    });

  });

  describe('snap to vertex', function() {
    it('snap to vertex works', function() {
      interaction.setExtent([-50, -50, 50, 50]);

      expect(interaction.snapToVertex_([230, 40], map)).to.eql([50, 50]);
      expect(interaction.snapToVertex_([231, 41], map)).to.eql([50, 50]);
    });

    it('snap to edge works', function() {
      interaction.setExtent([-50, -50, 50, 50]);

      expect(interaction.snapToVertex_([230, 90], map)).to.eql([50, 0]);
      expect(interaction.snapToVertex_([230, 89], map)).to.eql([50, 1]);
      expect(interaction.snapToVertex_([231, 90], map)).to.eql([50, 0]);
    });
  });

  describe('draw extent', function() {

    it('drawing extent works', function() {
      simulateEvent('pointerdown', -50, -50, false, 0);
      simulateEvent('pointerdrag', 50, 50, false, 0);
      simulateEvent('pointerup', 50, 50, false, 0);

      expect(interaction.getExtent()).to.eql([-50, -50, 50, 50]);
    });

    it('clicking off extent nulls extent', function() {
      interaction.setExtent([-50, -50, 50, 50]);

      simulateEvent('pointerdown', -10, -10, false, 0);
      simulateEvent('pointerup', -10, -10, false, 0);

      expect(interaction.getExtent()).to.equal(null);
    });

    it('clicking on extent does not null extent', function() {
      interaction.setExtent([-50, -50, 50, 50]);

      simulateEvent('pointerdown', 50, 50, false, 0);
      simulateEvent('pointerup', 50, 50, false, 0);

      expect(interaction.getExtent()).to.eql([-50, -50, 50, 50]);
    });

    it('snap and drag vertex works', function() {
      interaction.setExtent([-50, -50, 50, 50]);

      simulateEvent('pointerdown', 51, 49, false, 0);
      simulateEvent('pointerdrag', -70, -40, false, 0);
      simulateEvent('pointerup', -70, -40, false, 0);

      expect(interaction.getExtent()).to.eql([-70, -50, -50, -40]);
    });

    it('snap and drag edge works', function() {
      interaction.setExtent([-50, -50, 50, 50]);

      simulateEvent('pointerdown', 51, 5, false, 0);
      simulateEvent('pointerdrag', 20, -30, false, 0);
      simulateEvent('pointerup', 20, -30, false, 0);

      expect(interaction.getExtent()).to.eql([-50, -50, 20, 50]);
    });
  });
});
