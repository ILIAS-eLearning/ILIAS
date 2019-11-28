goog.require('ol');
goog.require('ol.Map');
goog.require('ol.View');
goog.require('ol.control.ScaleLine');
goog.require('ol.proj');
goog.require('ol.proj.Projection');

describe('ol.control.ScaleLine', function() {
  var map;
  beforeEach(function() {
    var target = document.createElement('div');
    document.body.appendChild(target);
    map = new ol.Map({
      target: target
    });
  });
  afterEach(function() {
    disposeMap(map);
    map = null;
  });

  describe('constructor', function() {
    it('can be constructed without arguments', function() {
      var ctrl = new ol.control.ScaleLine();
      expect(ctrl).to.be.an(ol.control.ScaleLine);
    });
  });

  describe('configuration options', function() {

    describe('className', function() {
      it('defaults to "ol-scale-line"', function() {
        var ctrl = new ol.control.ScaleLine();
        ctrl.setMap(map);
        var element = document.querySelector('.ol-scale-line', map.getTarget());
        expect(element).to.not.be(null);
        expect(element).to.be.a(HTMLDivElement);
      });
      it('can be configured', function() {
        var ctrl = new ol.control.ScaleLine({
          className: 'humpty-dumpty'
        });
        ctrl.setMap(map);

        // check that the default was not chosen
        var element1 = document.querySelector('.ol-scale-line', map.getTarget());
        expect(element1).to.be(null);
        // check if the configured classname was chosen
        var element2 = document.querySelector('.humpty-dumpty', map.getTarget());
        expect(element2).to.not.be(null);
        expect(element2).to.be.a(HTMLDivElement);
      });
    });

    describe('minWidth', function() {
      it('defaults to 64', function() {
        var ctrl = new ol.control.ScaleLine();
        expect(ctrl.minWidth_).to.be(64);
      });
      it('can be configured', function() {
        var ctrl = new ol.control.ScaleLine({
          minWidth: 4711
        });
        expect(ctrl.minWidth_).to.be(4711);
      });
    });

    describe('render', function() {
      it('defaults to `ol.control.ScaleLine.render`', function() {
        var ctrl = new ol.control.ScaleLine();
        expect(ctrl.render).to.be(ol.control.ScaleLine.render);
      });
      it('can be configured', function() {
        var myRender = function() {

        };
        var ctrl = new ol.control.ScaleLine({
          render: myRender
        });
        expect(ctrl.render).to.be(myRender);
      });
    });

  });

  describe('synchronisation with map view', function() {
    it('calls `render` as soon as the map is rendered', function(done) {
      var renderSpy = sinon.spy();
      var ctrl = new ol.control.ScaleLine({
        render: renderSpy
      });
      expect(renderSpy.called).to.be(false);
      ctrl.setMap(map);
      expect(renderSpy.called).to.be(false);
      map.setView(new ol.View({
        center: [0, 0],
        zoom: 0
      }));
      expect(renderSpy.called).to.be(false);
      map.once('postrender', function() {
        expect(renderSpy.called).to.be(true);
        expect(renderSpy.callCount).to.be(1);
        done();
      });
    });
    it('calls `render` as often as the map is rendered', function() {
      var renderSpy = sinon.spy();
      var ctrl = new ol.control.ScaleLine({
        render: renderSpy
      });
      ctrl.setMap(map);
      map.setView(new ol.View({
        center: [0, 0],
        zoom: 0
      }));
      map.renderSync();
      expect(renderSpy.callCount).to.be(1);
      map.renderSync();
      expect(renderSpy.callCount).to.be(2);
      map.renderSync();
      expect(renderSpy.callCount).to.be(3);
    });
    it('calls `render` as when the view changes', function(done) {
      var renderSpy = sinon.spy();
      var ctrl = new ol.control.ScaleLine({
        render: renderSpy
      });
      ctrl.setMap(map);
      map.setView(new ol.View({
        center: [0, 0],
        zoom: 0
      }));
      map.renderSync();
      map.once('postrender', function() {
        expect(renderSpy.callCount).to.be(2);
        done();
      });
      map.getView().setCenter([1, 1]);
    });
  });

  describe('static method `render`', function() {
    it('updates the rendered text', function() {
      var ctrl = new ol.control.ScaleLine();
      expect(ctrl.element.innerText).to.be('');
      ctrl.setMap(map);
      map.setView(new ol.View({
        center: [0, 0],
        zoom: 0
      }));
      map.renderSync();
      expect(ctrl.element.innerText).to.be('10000 km');
    });
  });

  describe('#getUnits', function() {
    it('returns "metric" by default', function() {
      var ctrl = new ol.control.ScaleLine();
      expect(ctrl.getUnits()).to.be('metric');
    });
    it('returns what is configured via `units` property', function() {
      var ctrl = new ol.control.ScaleLine({
        units: 'nautical'
      });
      expect(ctrl.getUnits()).to.be('nautical');
    });
    it('returns what is configured `setUnits` method', function() {
      var ctrl = new ol.control.ScaleLine();
      ctrl.setUnits('nautical');
      expect(ctrl.getUnits()).to.be('nautical');
    });
  });

  describe('#setUnits', function() {
    it('triggers rerendering', function() {
      var ctrl = new ol.control.ScaleLine();
      map.setView(new ol.View({
        center: [0, 0],
        zoom: 0
      }));
      ctrl.setMap(map);

      map.renderSync();
      expect(ctrl.element.innerText).to.be('10000 km');

      ctrl.setUnits('nautical');
      map.renderSync();
      expect(ctrl.element.innerText).to.be('10000 nm');
    });
  });

  describe('different units result in different contents', function() {
    var ctrl;
    var metricHtml;
    var nauticalHtml;
    var degreesHtml;
    var imperialHtml;
    var usHtml;
    beforeEach(function(done) {
      ctrl = new ol.control.ScaleLine();
      ctrl.setMap(map);
      map.setView(new ol.View({
        center: [0, 0],
        zoom: 0
      }));
      map.once('postrender', function() {
        metricHtml = ctrl.element_.innerHTML;
        done();
      });
    });
    afterEach(function() {
      map.setView(null);
      map.removeControl(ctrl);
    });

    it('renders a scaleline for "metric"', function() {
      expect(metricHtml).to.not.be(undefined);
    });
    it('renders a different scaleline for "nautical"', function() {
      ctrl.setUnits('nautical');
      nauticalHtml = ctrl.element_.innerHTML;
      expect(nauticalHtml).to.not.be(metricHtml);
    });
    it('renders a different scaleline for "degrees"', function() {
      ctrl.setUnits('degrees');
      degreesHtml = ctrl.element_.innerHTML;
      expect(degreesHtml).to.not.be(metricHtml);
      expect(degreesHtml).to.not.be(nauticalHtml);
    });
    it('renders a different scaleline for "imperial"', function() {
      ctrl.setUnits('imperial');
      imperialHtml = ctrl.element_.innerHTML;
      expect(imperialHtml).to.not.be(metricHtml);
      expect(imperialHtml).to.not.be(nauticalHtml);
      expect(imperialHtml).to.not.be(degreesHtml);
    });
    it('renders a different scaleline for "us"', function() {
      ctrl.setUnits('us');
      usHtml = ctrl.element_.innerHTML;
      expect(usHtml).to.not.be(metricHtml);
      expect(usHtml).to.not.be(nauticalHtml);
      expect(usHtml).to.not.be(degreesHtml);
      // it's hard to actually find a difference in rendering between
      // usHtml and imperialHtml
    });
  });

  describe('projections affect the scaleline', function() {
    it('is rendered differently for different projections', function() {
      var ctrl = new ol.control.ScaleLine();
      ctrl.setMap(map);
      map.setView(new ol.View({
        center: ol.proj.fromLonLat([7, 52]),
        zoom: 2,
        projection: 'EPSG:3857'
      }));
      map.renderSync();
      var innerHtml3857 = ctrl.element_.innerHTML;
      map.setView(new ol.View({
        center: [7, 52],
        zoom: 2,
        projection: 'EPSG:4326'
      }));
      map.renderSync();
      var innerHtml4326 = ctrl.element_.innerHTML;
      expect(innerHtml4326).to.not.be(innerHtml3857);
    });

    it('Projection\'s metersPerUnit affect scale for non-degree units', function() {
      var ctrl = new ol.control.ScaleLine();
      ctrl.setMap(map);
      map.setView(new ol.View({
        center: [0, 0],
        zoom: 0,
        resolutions: [1],
        projection: new ol.proj.Projection({
          code: 'METERS',
          units: 'm',
          getPointResolution: function(r) {
            return r;
          }
        })
      }));
      map.renderSync();
      expect(ctrl.element_.innerText).to.be('100 m');
      map.setView(new ol.View({
        center: [0, 0],
        zoom: 0,
        resolutions: [1],
        projection: new ol.proj.Projection({
          code: 'PIXELS',
          units: 'pixels',
          metersPerUnit: 1 / 1000,
          getPointResolution: function(r) {
            return r;
          }
        })
      }));
      map.renderSync();
      expect(ctrl.element_.innerText).to.be('100 mm');
    });
  });

  describe('latitude may affect scale line in EPSG:4326', function() {

    it('is rendered differently at different latitudes for metric', function() {
      var ctrl = new ol.control.ScaleLine();
      ctrl.setMap(map);
      map.setView(new ol.View({
        center: ol.proj.fromLonLat([7, 0]),
        zoom: 2,
        projection: 'EPSG:4326'
      }));
      map.renderSync();
      var innerHtml0 = ctrl.element_.innerHTML;
      map.getView().setCenter([7, 52]);
      map.renderSync();
      var innerHtml52 = ctrl.element_.innerHTML;
      expect(innerHtml0).to.not.be(innerHtml52);
    });

    it('is rendered the same at different latitudes for degrees', function() {
      var ctrl = new ol.control.ScaleLine({
        units: 'degrees'
      });
      ctrl.setMap(map);
      map.setView(new ol.View({
        center: ol.proj.fromLonLat([7, 0]),
        zoom: 2,
        projection: 'EPSG:4326'
      }));
      map.renderSync();
      var innerHtml0 = ctrl.element_.innerHTML;
      map.getView().setCenter([7, 52]);
      map.renderSync();
      var innerHtml52 = ctrl.element_.innerHTML;
      expect(innerHtml0).to.be(innerHtml52);
    });

  });

  describe('zoom affects the scaleline', function() {
    var currentZoom;
    var ctrl;
    var renderedHtmls;
    var mapView;

    var getMetricUnit = function(zoom) {
      if (zoom > 30) {
        return 'μm';
      } else if (zoom > 20) {
        return 'mm';
      } else if (zoom > 10) {
        return 'm';
      } else {
        return 'km';
      }
    };

    beforeEach(function() {
      currentZoom = 33;
      renderedHtmls = {};
      ctrl = new ol.control.ScaleLine({
        minWidth: 10
      });
      ctrl.setMap(map);
      map.setView(new ol.View({
        center: [0, 0],
        zoom: currentZoom,
        maxZoom: currentZoom
      }));
      mapView = map.getView();
      map.renderSync();

    });
    afterEach(function() {
      map.removeControl(ctrl);
      map.setView(null);
    });

    it('metric: is rendered differently for different zoomlevels', function() {
      ctrl.setUnits('metric');
      map.renderSync();
      renderedHtmls[ctrl.element_.innerHTML] = true;
      while (--currentZoom >= 0) {
        mapView.setZoom(currentZoom);
        map.renderSync();
        var currentHtml = ctrl.element_.innerHTML;
        expect(currentHtml in renderedHtmls).to.be(false);
        renderedHtmls[currentHtml] = true;

        var unit = ctrl.innerElement_.textContent.match(/\d+ (.+)/)[1];
        expect(unit).to.eql(getMetricUnit(currentZoom));
      }
    });
    it('degrees: is rendered differently for different zoomlevels', function() {
      ctrl.setUnits('degrees');
      map.renderSync();
      renderedHtmls[ctrl.element_.innerHTML] = true;
      while (--currentZoom >= 0) {
        mapView.setZoom(currentZoom);
        map.renderSync();
        var currentHtml = ctrl.element_.innerHTML;
        expect(currentHtml in renderedHtmls).to.be(false);
        renderedHtmls[currentHtml] = true;
      }
    });
    it('imperial: is rendered differently for different zoomlevels', function() {
      ctrl.setUnits('imperial');
      map.renderSync();
      renderedHtmls[ctrl.element_.innerHTML] = true;
      while (--currentZoom >= 0) {
        mapView.setZoom(currentZoom);
        map.renderSync();
        var currentHtml = ctrl.element_.innerHTML;
        expect(currentHtml in renderedHtmls).to.be(false);
        renderedHtmls[currentHtml] = true;
      }
    });
    it('nautical: is rendered differently for different zoomlevels', function() {
      ctrl.setUnits('nautical');
      map.renderSync();
      renderedHtmls[ctrl.element_.innerHTML] = true;
      while (--currentZoom >= 0) {
        mapView.setZoom(currentZoom);
        map.renderSync();
        var currentHtml = ctrl.element_.innerHTML;
        expect(currentHtml in renderedHtmls).to.be(false);
        renderedHtmls[currentHtml] = true;
      }
    });
    it('us: is rendered differently for different zoomlevels', function() {
      ctrl.setUnits('us');
      map.renderSync();
      renderedHtmls[ctrl.element_.innerHTML] = true;
      while (--currentZoom >= 0) {
        mapView.setZoom(currentZoom);
        map.renderSync();
        var currentHtml = ctrl.element_.innerHTML;
        expect(currentHtml in renderedHtmls).to.be(false);
        renderedHtmls[currentHtml] = true;
      }
    });
  });

});
