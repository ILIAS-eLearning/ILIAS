

goog.require('ol.Map');
goog.require('ol.MapBrowserEvent');
goog.require('ol.View');
goog.require('ol.events.Event');
goog.require('ol.has');
goog.require('ol.interaction.Interaction');
goog.require('ol.interaction.MouseWheelZoom');


describe('ol.interaction.MouseWheelZoom', function() {
  var map, interaction;

  beforeEach(function() {
    interaction = new ol.interaction.MouseWheelZoom();
    map = new ol.Map({
      target: createMapDiv(100, 100),
      interactions: [interaction],
      view: new ol.View({
        center: [0, 0],
        resolutions: [2, 1, 0.5],
        zoom: 1
      })
    });
    map.renderSync();
  });

  afterEach(function() {
    disposeMap(map);
    map = null;
    interaction = null;
  });

  describe('timeout duration', function() {
    var clock;
    beforeEach(function() {
      sinon.spy(ol.interaction.Interaction, 'zoomByDelta');
      clock = sinon.useFakeTimers();
    });

    afterEach(function() {
      clock.restore();
      ol.interaction.Interaction.zoomByDelta.restore();
    });

    it('works with the defaut value', function(done) {
      var event = new ol.MapBrowserEvent('mousewheel', map, {
        type: 'mousewheel',
        target: map.getViewport(),
        preventDefault: ol.events.Event.prototype.preventDefault
      });
      map.handleMapBrowserEvent(event);
      clock.tick(50);
      // default timeout is 80 ms, not called yet
      expect(ol.interaction.Interaction.zoomByDelta.called).to.be(false);
      clock.tick(30);
      expect(ol.interaction.Interaction.zoomByDelta.called).to.be(true);

      done();
    });

  });

  describe('handleEvent()', function() {

    it('works on Firefox in DOM_DELTA_PIXEL mode (trackpad)', function(done) {
      var origHasFirefox = ol.has.FIREFOX;
      ol.has.FIREFOX = true;
      map.once('postrender', function() {
        expect(interaction.mode_).to.be(ol.interaction.MouseWheelZoom.Mode_.TRACKPAD);
        ol.has.FIREFOX = origHasFirefox;
        done();
      });
      var event = new ol.MapBrowserEvent('wheel', map, {
        type: 'wheel',
        deltaMode: WheelEvent.DOM_DELTA_PIXEL,
        deltaY: ol.has.DEVICE_PIXEL_RATIO,
        target: map.getViewport(),
        preventDefault: ol.events.Event.prototype.preventDefault
      });
      event.coordinate = [0, 0];
      map.handleMapBrowserEvent(event);
    });

    it('works in DOM_DELTA_PIXEL mode (trackpad)', function(done) {
      var origHasFirefox = ol.has.FIREFOX;
      ol.has.FIREFOX = false;
      map.once('postrender', function() {
        expect(interaction.mode_).to.be(ol.interaction.MouseWheelZoom.Mode_.TRACKPAD);
        ol.has.FIREFOX = origHasFirefox;
        done();
      });
      var event = new ol.MapBrowserEvent('wheel', map, {
        type: 'wheel',
        deltaMode: WheelEvent.DOM_DELTA_PIXEL,
        deltaY: 1,
        target: map.getViewport(),
        preventDefault: ol.events.Event.prototype.preventDefault
      });
      event.coordinate = [0, 0];
      map.handleMapBrowserEvent(event);
    });

    describe('spying on ol.interaction.Interaction.zoomByDelta', function() {
      beforeEach(function() {
        sinon.spy(ol.interaction.Interaction, 'zoomByDelta');
      });
      afterEach(function() {
        ol.interaction.Interaction.zoomByDelta.restore();
      });

      it('works in DOM_DELTA_LINE mode (wheel)', function(done) {
        map.once('postrender', function() {
          var call = ol.interaction.Interaction.zoomByDelta.getCall(0);
          expect(call.args[1]).to.be(-1);
          expect(call.args[2]).to.eql([0, 0]);
          done();
        });
        var event = new ol.MapBrowserEvent('wheel', map, {
          type: 'wheel',
          deltaMode: WheelEvent.DOM_DELTA_LINE,
          deltaY: 3.714599609375,
          target: map.getViewport(),
          preventDefault: ol.events.Event.prototype.preventDefault
        });
        event.coordinate = [0, 0];
        map.handleMapBrowserEvent(event);
      });

      it('works on Safari (wheel)', function(done) {
        var origHasSafari = ol.has.SAFARI;
        ol.has.SAFARI = true;
        map.once('postrender', function() {
          var call = ol.interaction.Interaction.zoomByDelta.getCall(0);
          expect(call.args[1]).to.be(-1);
          expect(call.args[2]).to.eql([0, 0]);
          ol.has.SAFARI = origHasSafari;
          done();
        });
        var event = new ol.MapBrowserEvent('mousewheel', map, {
          type: 'mousewheel',
          wheelDeltaY: -50,
          target: map.getViewport(),
          preventDefault: ol.events.Event.prototype.preventDefault
        });
        event.coordinate = [0, 0];
        map.handleMapBrowserEvent(event);
      });

      it('works on other browsers (wheel)', function(done) {
        var origHasSafari = ol.has.SAFARI;
        ol.has.SAFARI = false;
        map.once('postrender', function() {
          var call = ol.interaction.Interaction.zoomByDelta.getCall(0);
          expect(call.args[1]).to.be(-1);
          expect(call.args[2]).to.eql([0, 0]);
          ol.has.SAFARI = origHasSafari;
          done();
        });
        var event = new ol.MapBrowserEvent('mousewheel', map, {
          type: 'mousewheel',
          wheelDeltaY: -120,
          target: map.getViewport(),
          preventDefault: ol.events.Event.prototype.preventDefault
        });
        event.coordinate = [0, 0];
        map.handleMapBrowserEvent(event);
      });

    });

  });

});
