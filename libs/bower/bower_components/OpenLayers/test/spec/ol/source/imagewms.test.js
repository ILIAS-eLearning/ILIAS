

goog.require('ol.source.ImageWMS');
goog.require('ol.proj');


describe('ol.source.ImageWMS', function() {

  var extent, pixelRatio, options, optionsReproj, projection, resolution;
  beforeEach(function() {
    extent = [10, 20, 30, 40];
    pixelRatio = 1;
    projection = ol.proj.get('EPSG:4326');
    resolution = 0.1;
    options = {
      params: {
        'LAYERS': 'layer'
      },
      ratio: 1,
      url: 'http://example.com/wms'
    };
    optionsReproj = {
      params: {
        'LAYERS': 'layer'
      },
      ratio: 1,
      url: 'http://example.com/wms',
      projection: 'EPSG:3857'
    };
  });

  describe('#getImage', function() {

    it('returns the expected image URL', function() {
      options.ratio = 1.5;
      var source = new ol.source.ImageWMS(options);
      var image = source.getImage([10, 20, 30.1, 39.9], resolution, pixelRatio, projection);
      var uri = new URL(image.src_);
      var queryData = uri.searchParams;
      var extent = queryData.get('BBOX').split(',').map(Number);
      var extentAspectRatio = (extent[3] - extent[1]) / (extent[2] - extent[0]);
      var imageAspectRatio = Number(queryData.get('WIDTH') / Number(queryData.get('HEIGHT')));
      expect(extentAspectRatio).to.roughlyEqual(imageAspectRatio, 1e-12);
    });

    it('uses correct WIDTH and HEIGHT for HiDPI devices', function() {
      pixelRatio = 2;
      options.serverType = 'geoserver';
      var source = new ol.source.ImageWMS(options);
      var image = source.getImage(extent, resolution, pixelRatio, projection);
      var uri = new URL(image.src_);
      var queryData = uri.searchParams;
      var width = Number(queryData.get('WIDTH'));
      var height = Number(queryData.get('HEIGHT'));
      expect(width).to.be(400);
      expect(height).to.be(400);
    });

    it('requests integer WIDTH and HEIGHT', function() {
      options.ratio = 1.5;
      var source = new ol.source.ImageWMS(options);
      var image = source.getImage([10, 20, 30.1, 39.9], resolution, pixelRatio, projection);
      var uri = new URL(image.src_);
      var queryData = uri.searchParams;
      var width = parseFloat(queryData.get('WIDTH'));
      var height = parseFloat(queryData.get('HEIGHT'));
      expect(width).to.be(Math.round(width));
      expect(height).to.be(Math.round(height));
    });

    it('sets WIDTH and HEIGHT to match the aspect ratio of BBOX', function() {
      var source = new ol.source.ImageWMS(options);
      var image = source.getImage(extent, resolution, pixelRatio, projection);
      var uri = new URL(image.src_);
      expect(uri.protocol).to.be('http:');
      expect(uri.hostname).to.be('example.com');
      expect(uri.pathname).to.be('/wms');
      var queryData = uri.searchParams;
      expect(queryData.get('BBOX')).to.be('20,10,40,30');
      expect(queryData.get('CRS')).to.be('EPSG:4326');
      expect(queryData.get('FORMAT')).to.be('image/png');
      expect(queryData.get('HEIGHT')).to.be('200');
      expect(queryData.get('LAYERS')).to.be('layer');
      expect(queryData.get('REQUEST')).to.be('GetMap');
      expect(queryData.get('SERVICE')).to.be('WMS');
      expect(queryData.get('SRS')).to.be(null);
      expect(queryData.get('STYLES')).to.be('');
      expect(queryData.get('TRANSPARENT')).to.be('true');
      expect(queryData.get('VERSION')).to.be('1.3.0');
      expect(queryData.get('WIDTH')).to.be('200');
      expect(uri.hash.replace('#', '')).to.be.empty();
    });

    it('sets the SRS query value instead of CRS if version < 1.3', function() {
      options.params.VERSION = '1.2';
      var source = new ol.source.ImageWMS(options);
      var image = source.getImage(extent, resolution, pixelRatio, projection);
      var uri = new URL(image.src_);
      var queryData = uri.searchParams;
      expect(queryData.get('CRS')).to.be(null);
      expect(queryData.get('SRS')).to.be('EPSG:4326');
    });

    it('allows various parameters to be overridden', function() {
      options.params.FORMAT = 'image/jpeg';
      options.params.TRANSPARENT = false;
      var source = new ol.source.ImageWMS(options);
      var image = source.getImage(extent, resolution, pixelRatio, projection);
      var uri = new URL(image.src_);
      var queryData = uri.searchParams;
      expect(queryData.get('FORMAT')).to.be('image/jpeg');
      expect(queryData.get('TRANSPARENT')).to.be('false');
    });

    it('does not add a STYLES= option if one is specified', function() {
      options.params.STYLES = 'foo';
      var source = new ol.source.ImageWMS(options);
      var image = source.getImage(extent, resolution, pixelRatio, projection);
      var uri = new URL(image.src_);
      var queryData = uri.searchParams;
      expect(queryData.get('STYLES')).to.be('foo');
    });

    it('changes the BBOX order for EN axis orientations', function() {
      var source = new ol.source.ImageWMS(options);
      projection = ol.proj.get('CRS:84');
      var image = source.getImage(extent, resolution, pixelRatio, projection);
      var uri = new URL(image.src_);
      var queryData = uri.searchParams;
      expect(queryData.get('BBOX')).to.be('10,20,30,40');
    });

    it('uses EN BBOX order if version < 1.3', function() {
      options.params.VERSION = '1.1.0';
      var source = new ol.source.ImageWMS(options);
      var image =
          source.getImage(extent, resolution, pixelRatio, projection);
      var uri = new URL(image.src_);
      var queryData = uri.searchParams;
      expect(queryData.get('BBOX')).to.be('10,20,30,40');
    });

    it('sets MAP_RESOLUTION when the server is MapServer', function() {
      options.serverType = 'mapserver';
      var source = new ol.source.ImageWMS(options);
      pixelRatio = 2;
      var image = source.getImage(extent, resolution, pixelRatio, projection);
      var uri = new URL(image.src_);
      var queryData = uri.searchParams;
      expect(queryData.get('MAP_RESOLUTION')).to.be('180');
    });

    it('sets FORMAT_OPTIONS when the server is GeoServer', function() {
      options.serverType = 'geoserver';
      var source = new ol.source.ImageWMS(options);
      pixelRatio = 2;
      var image = source.getImage(extent, resolution, pixelRatio, projection);
      var uri = new URL(image.src_);
      var queryData = uri.searchParams;
      expect(queryData.get('FORMAT_OPTIONS')).to.be('dpi:180');
    });

    it('extends FORMAT_OPTIONS if it is already present', function() {
      options.serverType = 'geoserver';
      var source = new ol.source.ImageWMS(options);
      options.params.FORMAT_OPTIONS = 'param1:value1';
      pixelRatio = 2;
      var image = source.getImage(extent, resolution, pixelRatio, projection);
      var uri = new URL(image.src_);
      var queryData = uri.searchParams;
      expect(queryData.get('FORMAT_OPTIONS')).to.be('param1:value1;dpi:180');
    });

    it('rounds FORMAT_OPTIONS to an integer when the server is GeoServer',
        function() {
          options.serverType = 'geoserver';
          var source = new ol.source.ImageWMS(options);
          pixelRatio = 1.325;
          var image =
             source.getImage(extent, resolution, pixelRatio, projection);
          var uri = new URL(image.src_);
          var queryData = uri.searchParams;
          expect(queryData.get('FORMAT_OPTIONS')).to.be('dpi:119');
        });

    it('sets DPI when the server is QGIS', function() {
      options.serverType = 'qgis';
      var source = new ol.source.ImageWMS(options);
      pixelRatio = 2;
      var image = source.getImage(extent, resolution, pixelRatio, projection);
      var uri = new URL(image.src_);
      var queryData = uri.searchParams;
      expect(queryData.get('DPI')).to.be('180');
    });

    it('creates an image with a custom imageLoadFunction', function() {
      var imageLoadFunction = sinon.spy();
      options.imageLoadFunction = imageLoadFunction;
      var source = new ol.source.ImageWMS(options);
      var image = source.getImage(extent, resolution, pixelRatio, projection);
      image.load();
      expect(imageLoadFunction).to.be.called();
      expect(imageLoadFunction.calledWith(image, image.src_)).to.be(true);
    });

    it('returns same image for consecutive calls with same args', function() {
      var extent = [10.01, 20, 30.01, 40];
      var source = new ol.source.ImageWMS(options);
      var image1 = source.getImage(extent, resolution, pixelRatio, projection);
      var image2 = source.getImage(extent, resolution, pixelRatio, projection);
      expect(image1).to.equal(image2);
    });

    it('returns same image for calls with similar extents', function() {
      options.ratio = 1.5;
      var source = new ol.source.ImageWMS(options);
      var extent = [10.01, 20, 30.01, 40];
      var image1 = source.getImage(extent, resolution, pixelRatio, projection);
      extent = [10.01, 20.1, 30.01, 40.1];
      var image2 = source.getImage(extent, resolution, pixelRatio, projection);
      expect(image1).to.equal(image2);
    });

    it('calculates correct image size with ratio', function() {
      options.ratio = 1.5;
      var source = new ol.source.ImageWMS(options);
      var extent = [10, 5, 30, 45];
      source.getImage(extent, resolution, pixelRatio, projection);
      expect(source.imageSize_).to.eql([300, 600]);
    });

  });

  describe('#getGetFeatureInfoUrl', function() {

    it('returns the expected GetFeatureInfo URL', function() {
      var source = new ol.source.ImageWMS(options);
      var url = source.getGetFeatureInfoUrl(
          [20, 30], resolution, projection,
          {INFO_FORMAT: 'text/plain'});
      var uri = new URL(url);
      expect(uri.protocol).to.be('http:');
      expect(uri.hostname).to.be('example.com');
      expect(uri.pathname).to.be('/wms');
      var queryData = uri.searchParams;
      expect(queryData.get('BBOX')).to.be('24.95,14.95,35.05,25.05');
      expect(queryData.get('CRS')).to.be('EPSG:4326');
      expect(queryData.get('FORMAT')).to.be('image/png');
      expect(queryData.get('HEIGHT')).to.be('101');
      expect(queryData.get('I')).to.be('50');
      expect(queryData.get('J')).to.be('50');
      expect(queryData.get('LAYERS')).to.be('layer');
      expect(queryData.get('QUERY_LAYERS')).to.be('layer');
      expect(queryData.get('REQUEST')).to.be('GetFeatureInfo');
      expect(queryData.get('SERVICE')).to.be('WMS');
      expect(queryData.get('SRS')).to.be(null);
      expect(queryData.get('STYLES')).to.be('');
      expect(queryData.get('TRANSPARENT')).to.be('true');
      expect(queryData.get('VERSION')).to.be('1.3.0');
      expect(queryData.get('WIDTH')).to.be('101');
      expect(uri.hash.replace('#', '')).to.be.empty();
    });

    it('returns the expected GetFeatureInfo URL when source\'s projection is different from the parameter', function() {
      var source = new ol.source.ImageWMS(optionsReproj);
      var url = source.getGetFeatureInfoUrl(
          [20, 30], resolution, projection,
          {INFO_FORMAT: 'text/plain'});
      var uri = new URL(url);
      expect(uri.protocol).to.be('http:');
      expect(uri.hostname).to.be('example.com');
      expect(uri.pathname).to.be('/wms');
      var queryData = uri.searchParams;
      expect(queryData.get('BBOX')).to.be('1577259.402312431,2854419.4299513334,2875520.229418512,4152680.2570574144');
      expect(queryData.get('CRS')).to.be('EPSG:3857');
      expect(queryData.get('FORMAT')).to.be('image/png');
      expect(queryData.get('HEIGHT')).to.be('101');
      expect(queryData.get('I')).to.be('50');
      expect(queryData.get('J')).to.be('50');
      expect(queryData.get('LAYERS')).to.be('layer');
      expect(queryData.get('QUERY_LAYERS')).to.be('layer');
      expect(queryData.get('REQUEST')).to.be('GetFeatureInfo');
      expect(queryData.get('SERVICE')).to.be('WMS');
      expect(queryData.get('SRS')).to.be(null);
      expect(queryData.get('STYLES')).to.be('');
      expect(queryData.get('TRANSPARENT')).to.be('true');
      expect(queryData.get('VERSION')).to.be('1.3.0');
      expect(queryData.get('WIDTH')).to.be('101');
      expect(uri.hash.replace('#', '')).to.be.empty();
    });

    it('sets the QUERY_LAYERS param as expected', function() {
      var source = new ol.source.ImageWMS(options);
      var url = source.getGetFeatureInfoUrl(
          [20, 30], resolution, projection,
          {INFO_FORMAT: 'text/plain', QUERY_LAYERS: 'foo,bar'});
      var uri = new URL(url);
      expect(uri.protocol).to.be('http:');
      expect(uri.hostname).to.be('example.com');
      expect(uri.pathname).to.be('/wms');
      var queryData = uri.searchParams;
      expect(queryData.get('BBOX')).to.be('24.95,14.95,35.05,25.05');
      expect(queryData.get('CRS')).to.be('EPSG:4326');
      expect(queryData.get('FORMAT')).to.be('image/png');
      expect(queryData.get('HEIGHT')).to.be('101');
      expect(queryData.get('I')).to.be('50');
      expect(queryData.get('J')).to.be('50');
      expect(queryData.get('LAYERS')).to.be('layer');
      expect(queryData.get('QUERY_LAYERS')).to.be('foo,bar');
      expect(queryData.get('REQUEST')).to.be('GetFeatureInfo');
      expect(queryData.get('SERVICE')).to.be('WMS');
      expect(queryData.get('SRS')).to.be(null);
      expect(queryData.get('STYLES')).to.be('');
      expect(queryData.get('TRANSPARENT')).to.be('true');
      expect(queryData.get('VERSION')).to.be('1.3.0');
      expect(queryData.get('WIDTH')).to.be('101');
      expect(uri.hash.replace('#', '')).to.be.empty();
    });
  });

});
