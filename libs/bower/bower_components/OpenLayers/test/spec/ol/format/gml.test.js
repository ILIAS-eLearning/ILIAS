

goog.require('ol.Feature');
goog.require('ol.format.GML');
goog.require('ol.format.GML2');
goog.require('ol.geom.LineString');
goog.require('ol.geom.LinearRing');
goog.require('ol.geom.MultiLineString');
goog.require('ol.geom.MultiPoint');
goog.require('ol.geom.MultiPolygon');
goog.require('ol.geom.Point');
goog.require('ol.geom.Polygon');
goog.require('ol.proj');
goog.require('ol.xml');

var readGeometry = function(format, text, opt_options) {
  var doc = ol.xml.parse(text);
  // we need an intermediate node for testing purposes
  var node = document.createElement('PRE');
  node.appendChild(doc.documentElement);
  return format.readGeometryFromNode(node, opt_options);
};

describe('ol.format.GML2', function() {

  var format;
  beforeEach(function() {
    format = new ol.format.GML2({srsName: 'CRS:84'});
  });

  describe('#readFeatures', function() {
    var features;
    before(function(done) {
      var url = 'spec/ol/format/gml/osm-wfs-10.xml';
      afterLoadText(url, function(xml) {
        try {
          features = new ol.format.GML2().readFeatures(xml);
        } catch (e) {
          done(e);
        }
        done();
      });
    });

    it('reads all features', function() {
      expect(features.length).to.be(3);
    });

  });

  describe('#readGeometry', function() {

    describe('gml 2.1.2', function() {

      it('can read a point geometry', function() {
        var text = '<gml:Point xmlns:gml="http://www.opengis.net/gml" ' +
            '    srsName="urn:x-ogc:def:crs:EPSG:4326">' +
            '  <gml:coordinates>-90,-180</gml:coordinates>' +
            '</gml:Point>';

        var g = readGeometry(format, text);
        expect(g).to.be.an(ol.geom.Point);
        expect(g.getCoordinates()).to.eql([-180, -90, 0]);
      });

      it('can read a 3D point geometry', function() {
        var text = '<gml:Point xmlns:gml="http://www.opengis.net/gml" ' +
            '    srsName="urn:x-ogc:def:crs:EPSG:4326">' +
            '  <gml:coordinates>-90,-180,42</gml:coordinates>' +
            '</gml:Point>';

        var g = readGeometry(format, text);
        expect(g).to.be.an(ol.geom.Point);
        expect(g.getCoordinates()).to.eql([-180, -90, 42]);
      });

      it('can read a box element', function() {
        var text = '<gml:Box xmlns:gml="http://www.opengis.net/gml" ' +
            'srsName="EPSG:4326">' +
            '  <gml:coordinates>-0.768746,47.003018 ' +
            '    3.002191,47.925567</gml:coordinates>' +
            '</gml:Box>';

        var g = readGeometry(format, text);
        expect(g).to.eql([47.003018, -0.768746, 47.925567, 3.002191]);
      });

      it('can read a multipolygon with gml:coordinates', function() {

        var text =
            '<gml:MultiPolygon xmlns:gml="http://www.opengis.net/gml" ' +
            '        srsName="EPSG:4326">' +
            '  <gml:polygonMember>' +
            '    <gml:Polygon>' +
            '      <gml:outerBoundaryIs>' +
            '        <gml:LinearRing>' +
            '          <gml:coordinates>-0.318987,47.003018 ' +
            '             -0.768746,47.358268 ' +
            '             -0.574463,47.684285 -0.347374,47.854602 ' +
            '             -0.006740,47.925567 ' +
            '             0.135191,47.726864 0.149384,47.599127 ' +
            '             0.419052,47.670092 0.532597,47.428810 ' +
            '             0.305508,47.443003 0.475824,47.144948 ' +
            '             0.064225,47.201721 ' +
            '             -0.318987,47.003018 </gml:coordinates>' +
            '        </gml:LinearRing>' +
            '      </gml:outerBoundaryIs>' +
            '      <gml:innerBoundaryIs>' +
            '        <gml:LinearRing>' +
            '          <gml:coordinates>-0.035126,47.485582 ' +
            '             -0.035126,47.485582 ' +
            '             -0.049319,47.641706 -0.233829,47.655899 ' +
            '             -0.375760,47.457196 ' +
            '             -0.276408,47.286879 -0.035126,47.485582 ' +
            '          </gml:coordinates>' +
            '        </gml:LinearRing>' +
            '      </gml:innerBoundaryIs>' +
            '    </gml:Polygon>' +
            '  </gml:polygonMember>' +
            '</gml:MultiPolygon>';

        var g = readGeometry(format, text);
        expect(g).to.be.an(ol.geom.MultiPolygon);
        expect(g.getCoordinates()).to.eql([
          [
            [
              [47.003018, -0.318987, 0], [47.358268, -0.768746, 0],
              [47.684285, -0.574463, 0], [47.854602, -0.347374, 0],
              [47.925567, -0.00674, 0], [47.726864, 0.135191, 0],
              [47.599127, 0.149384, 0], [47.670092, 0.419052, 0],
              [47.42881, 0.532597, 0], [47.443003, 0.305508, 0],
              [47.144948, 0.475824, 0], [47.201721, 0.064225, 0],
              [47.003018, -0.318987, 0]
            ],
            [
              [47.485582, -0.035126, 0], [47.485582, -0.035126, 0],
              [47.641706, -0.049319, 0], [47.655899, -0.233829, 0],
              [47.457196, -0.37576, 0], [47.286879, -0.276408, 0],
              [47.485582, -0.035126, 0]
            ]
          ]
        ]);
      });
    });
  });

  describe('#writeFeatureElement', function() {
    var node;
    var featureNS = 'http://www.openlayers.org/';
    beforeEach(function() {
      node = ol.xml.createElementNS(featureNS, 'layer');
    });

    it('can serialize a LineString', function() {
      var expected =
        '<layer xmlns="http://www.openlayers.org/" fid="1">' +
        '  <geometry>' +
        '     <LineString xmlns="http://www.opengis.net/gml" ' +
        '                  srsName="EPSG:4326">' +
        '       <coordinates ' +
        '                     decimal="." cs="," ts=" ">' +
        '         2,1.1 4.2,3' +
        '       </coordinates>' +
        '      </LineString>' +
        '    </geometry>' +
        '  </layer>';

      var feature = new ol.Feature({
        geometry: new ol.geom.LineString([[1.1, 2], [3, 4.2]])
      });
      feature.setId(1);
      var objectStack = [{
        featureNS: featureNS,
        srsName: 'EPSG:4326'
      }];
      format.writeFeatureElement(node, feature, objectStack);

      expect(node).to.xmleql(ol.xml.parse(expected));
    });

    it('can serialize a Polygon', function() {
      var expected =
        '<layer xmlns="http://www.openlayers.org/" fid="1">' +
        '  <geometry>' +
        '     <Polygon xmlns="http://www.opengis.net/gml" ' +
        '                  srsName="EPSG:4326">' +
        '       <outerBoundaryIs>' +
        '         <LinearRing srsName="EPSG:4326">' +
        '           <coordinates ' +
        '                        decimal="." cs="," ts=" ">' +
        '              2,1.1 4.2,3 6,5.2' +
        '           </coordinates>' +
        '         </LinearRing>' +
        '       </outerBoundaryIs>' +
        '      </Polygon>' +
        '    </geometry>' +
        '  </layer>';

      var feature = new ol.Feature({
        geometry: new ol.geom.Polygon([[[1.1, 2], [3, 4.2], [5.2, 6]]])
      });
      feature.setId(1);
      var objectStack = [{
        featureNS: featureNS,
        srsName: 'EPSG:4326'
      }];
      format.writeFeatureElement(node, feature, objectStack);

      expect(node).to.xmleql(ol.xml.parse(expected));
    });

    it('can serialize a Point', function() {
      var expected =
        '<layer xmlns="http://www.openlayers.org/" fid="1">' +
        '  <geometry>' +
        '     <Point xmlns="http://www.opengis.net/gml" ' +
        '            srsName="EPSG:4326">' +
        '       <coordinates ' +
        '                    decimal="." cs="," ts=" ">' +
        '              2,1.1' +
        '       </coordinates>' +
        '      </Point>' +
        '    </geometry>' +
        '  </layer>';

      var feature = new ol.Feature({
        geometry: new ol.geom.Point([1.1, 2])
      });
      feature.setId(1);
      var objectStack = [{
        featureNS: featureNS,
        srsName: 'EPSG:4326'
      }];
      format.writeFeatureElement(node, feature, objectStack);

      expect(node).to.xmleql(ol.xml.parse(expected));
    });

    it('can serialize a Multi Point', function() {
      var expected =
        '<layer xmlns="http://www.openlayers.org/" fid="1">' +
        '  <geometry>' +
        '     <MultiPoint xmlns="http://www.opengis.net/gml" ' +
        '                 srsName="EPSG:4326">' +
        '       <pointMember>' +
        '         <Point srsName="EPSG:4326">' +
        '           <coordinates ' +
        '                    decimal="." cs="," ts=" ">' +
        '              2,1.1' +
        '           </coordinates>' +
        '         </Point>' +
        '       </pointMember>' +
        '      </MultiPoint>' +
        '    </geometry>' +
        '  </layer>';

      var feature = new ol.Feature({
        geometry: new ol.geom.MultiPoint([[1.1, 2]])
      });
      feature.setId(1);
      var objectStack = [{
        featureNS: featureNS,
        srsName: 'EPSG:4326'
      }];
      format.writeFeatureElement(node, feature, objectStack);

      expect(node).to.xmleql(ol.xml.parse(expected));
    });

    it('can serialize a Multi Line String', function() {
      var expected =
        '<layer xmlns="http://www.openlayers.org/" fid="1">' +
        '  <geometry>' +
        '     <MultiLineString xmlns="http://www.opengis.net/gml" ' +
        '                 srsName="EPSG:4326">' +
        '       <lineStringMember>' +
        '         <LineString srsName="EPSG:4326">' +
        '           <coordinates ' +
        '                    decimal="." cs="," ts=" ">' +
        '              2,1.1 4.2,3' +
        '           </coordinates>' +
        '         </LineString>' +
        '       </lineStringMember>' +
        '      </MultiLineString>' +
        '    </geometry>' +
        '  </layer>';

      var feature = new ol.Feature({
        geometry: new ol.geom.MultiLineString([[[1.1, 2], [3, 4.2]]])
      });
      feature.setId(1);
      var objectStack = [{
        featureNS: featureNS,
        srsName: 'EPSG:4326'
      }];
      format.writeFeatureElement(node, feature, objectStack);

      expect(node).to.xmleql(ol.xml.parse(expected));
    });

    it('can serialize a Multi Polygon', function() {
      var expected =
        '<layer xmlns="http://www.openlayers.org/" fid="1">' +
        '  <geometry>' +
        '     <MultiPolygon xmlns="http://www.opengis.net/gml" ' +
        '                 srsName="EPSG:4326">' +
        '       <polygonMember>' +
        '         <Polygon srsName="EPSG:4326">' +
        '           <outerBoundaryIs>' +
        '             <LinearRing srsName="EPSG:4326">' +
        '               <coordinates ' +
        '                        decimal="." cs="," ts=" ">' +
        '                  2,1.1 4.2,3 6,5.2' +
        '               </coordinates>' +
        '             </LinearRing>' +
        '           </outerBoundaryIs>' +
        '         </Polygon>' +
        '       </polygonMember>' +
        '      </MultiPolygon>' +
        '    </geometry>' +
        '  </layer>';

      var feature = new ol.Feature({
        geometry: new ol.geom.MultiPolygon([[[[1.1, 2], [3, 4.2], [5.2, 6]]]])
      });
      feature.setId(1);
      var objectStack = [{
        featureNS: featureNS,
        srsName: 'EPSG:4326'
      }];
      format.writeFeatureElement(node, feature, objectStack);

      expect(node).to.xmleql(ol.xml.parse(expected));
    });
  });
});

describe('ol.format.GML3', function() {

  var format, formatWGS84, formatNoSrs;
  beforeEach(function() {
    format = new ol.format.GML({srsName: 'CRS:84'});
    formatWGS84 = new ol.format.GML({
      srsName: 'urn:x-ogc:def:crs:EPSG:4326'
    });
    formatNoSrs = new ol.format.GML();
  });

  describe('#readGeometry', function() {

    describe('point', function() {

      it('can read and write a point geometry', function() {
        var text =
            '<gml:Point xmlns:gml="http://www.opengis.net/gml" ' +
            '    srsName="CRS:84">' +
            '  <gml:pos srsDimension="2">1 2</gml:pos>' +
            '</gml:Point>';
        var g = readGeometry(format, text);
        expect(g).to.be.an(ol.geom.Point);
        expect(g.getCoordinates()).to.eql([1, 2, 0]);
        var serialized = format.writeGeometryNode(g);
        expect(serialized.firstElementChild).to.xmleql(ol.xml.parse(text));
      });

      it('can read a point geometry with scientific notation', function() {
        var text =
            '<gml:Point xmlns:gml="http://www.opengis.net/gml" ' +
            '    srsName="CRS:84">' +
            '  <gml:pos>1E7 2</gml:pos>' +
            '</gml:Point>';
        var g = readGeometry(format, text);
        expect(g).to.be.an(ol.geom.Point);
        expect(g.getCoordinates()).to.eql([10000000, 2, 0]);
        text =
            '<gml:Point xmlns:gml="http://www.opengis.net/gml" ' +
            '    srsName="CRS:84">' +
            '  <gml:pos>1e7 2</gml:pos>' +
            '</gml:Point>';
        g = readGeometry(format, text);
        expect(g).to.be.an(ol.geom.Point);
        expect(g.getCoordinates()).to.eql([10000000, 2, 0]);
      });

      it('can read, transform and write a point geometry', function() {
        var config = {
          featureProjection: 'EPSG:3857'
        };
        var text =
            '<gml:Point xmlns:gml="http://www.opengis.net/gml" ' +
            '    srsName="CRS:84">' +
            '  <gml:pos>1 2</gml:pos>' +
            '</gml:Point>';
        var g = readGeometry(format, text, config);
        expect(g).to.be.an(ol.geom.Point);
        var coordinates = g.getCoordinates();
        expect(coordinates.splice(0, 2)).to.eql(
            ol.proj.transform([1, 2], 'CRS:84', 'EPSG:3857'));
        config.dataProjection = 'CRS:84';
        var serialized = format.writeGeometryNode(g, config);
        var pos = serialized.firstElementChild.firstElementChild.textContent;
        var coordinate = pos.split(' ');
        expect(coordinate[0]).to.roughlyEqual(1, 1e-9);
        expect(coordinate[1]).to.roughlyEqual(2, 1e-9);
      });

      it('can detect SRS, read and transform a point geometry', function() {
        var config = {
          featureProjection: 'EPSG:3857'
        };
        var text =
            '<gml:Point xmlns:gml="http://www.opengis.net/gml" ' +
            '    srsName="CRS:84">' +
            '  <gml:pos>1 2</gml:pos>' +
            '</gml:Point>';
        var g = readGeometry(formatNoSrs, text, config);
        expect(g).to.be.an(ol.geom.Point);
        var coordinates = g.getCoordinates();
        expect(coordinates.splice(0, 2)).to.eql(
            ol.proj.transform([1, 2], 'CRS:84', 'EPSG:3857'));
      });

      it('can read and write a point geometry in EPSG:4326', function() {
        var text =
            '<gml:Point xmlns:gml="http://www.opengis.net/gml" ' +
            '    srsName="urn:x-ogc:def:crs:EPSG:4326">' +
            '  <gml:pos srsDimension="2">2 1</gml:pos>' +
            '</gml:Point>';
        var g = readGeometry(formatWGS84, text);
        expect(g).to.be.an(ol.geom.Point);
        expect(g.getCoordinates()).to.eql([1, 2, 0]);
        var serialized = formatWGS84.writeGeometryNode(g);
        expect(serialized.firstElementChild).to.xmleql(ol.xml.parse(text));
      });

    });

    describe('linestring', function() {

      it('can read and write a linestring geometry', function() {
        var text =
            '<gml:LineString xmlns:gml="http://www.opengis.net/gml" ' +
            '    srsName="CRS:84">' +
            '  <gml:posList srsDimension="2">1 2 3 4</gml:posList>' +
            '</gml:LineString>';
        var g = readGeometry(format, text);
        expect(g).to.be.an(ol.geom.LineString);
        expect(g.getCoordinates()).to.eql([[1, 2, 0], [3, 4, 0]]);
        var serialized = format.writeGeometryNode(g);
        expect(serialized.firstElementChild).to.xmleql(ol.xml.parse(text));
      });

      it('can read, transform and write a linestring geometry', function() {
        var config = {
          dataProjection: 'CRS:84',
          featureProjection: 'EPSG:3857'
        };
        var text =
            '<gml:LineString xmlns:gml="http://www.opengis.net/gml" ' +
            '    srsName="CRS:84">' +
            '  <gml:posList>1 2 3 4</gml:posList>' +
            '</gml:LineString>';
        var g = readGeometry(format, text, config);
        expect(g).to.be.an(ol.geom.LineString);
        var coordinates = g.getCoordinates();
        expect(coordinates[0].slice(0, 2)).to.eql(
            ol.proj.transform([1, 2], 'CRS:84', 'EPSG:3857'));
        expect(coordinates[1].slice(0, 2)).to.eql(
            ol.proj.transform([3, 4], 'CRS:84', 'EPSG:3857'));
        var serialized = format.writeGeometryNode(g, config);
        var poss = serialized.firstElementChild.firstElementChild.textContent;
        var coordinate = poss.split(' ');
        expect(coordinate[0]).to.roughlyEqual(1, 1e-9);
        expect(coordinate[1]).to.roughlyEqual(2, 1e-9);
        expect(coordinate[2]).to.roughlyEqual(3, 1e-9);
        expect(coordinate[3]).to.roughlyEqual(4, 1e-9);
      });

      it('can read and write a linestring geometry in EPSG:4326', function() {
        var text =
            '<gml:LineString xmlns:gml="http://www.opengis.net/gml" ' +
            '    srsName="urn:x-ogc:def:crs:EPSG:4326">' +
            '  <gml:posList srsDimension="2">2 1 4 3</gml:posList>' +
            '</gml:LineString>';
        var g = readGeometry(formatWGS84, text);
        expect(g).to.be.an(ol.geom.LineString);
        expect(g.getCoordinates()).to.eql([[1, 2, 0], [3, 4, 0]]);
        var serialized = formatWGS84.writeGeometryNode(g);
        expect(serialized.firstElementChild).to.xmleql(ol.xml.parse(text));
      });

    });

    describe('axis order', function() {

      it('can read and write a linestring geometry with ' +
          'correct axis order',
      function() {
        var text =
                '<gml:LineString xmlns:gml="http://www.opengis.net/gml" ' +
                '    srsName="urn:x-ogc:def:crs:EPSG:4326">' +
                ' <gml:posList srsDimension="2">-90 -180 90 180</gml:posList>' +
                '</gml:LineString>';
        var g = readGeometry(format, text);
        expect(g).to.be.an(ol.geom.LineString);
        expect(g.getCoordinates()).to.eql([[-180, -90, 0], [180, 90, 0]]);
        var serialized = formatWGS84.writeGeometryNode(g);
        expect(serialized.firstElementChild).to.xmleql(ol.xml.parse(text));
      });

      it('can read and write a point geometry with correct axis order',
          function() {
            var text =
                '<gml:Point xmlns:gml="http://www.opengis.net/gml" ' +
                '    srsName="urn:x-ogc:def:crs:EPSG:4326">' +
                '  <gml:pos srsDimension="2">-90 -180</gml:pos>' +
                '</gml:Point>';
            var g = readGeometry(format, text);
            expect(g).to.be.an(ol.geom.Point);
            expect(g.getCoordinates()).to.eql([-180, -90, 0]);
            var serialized = formatWGS84.writeGeometryNode(g);
            expect(serialized.firstElementChild).to.xmleql(ol.xml.parse(text));
          });

      it('can read and write a surface geometry with right axis order',
          function() {
            var text =
                '<gml:MultiSurface xmlns:gml="http://www.opengis.net/gml" ' +
                '    srsName="urn:x-ogc:def:crs:EPSG:4326">' +
                '  <gml:surfaceMember>' +
                '    <gml:Polygon srsName="urn:x-ogc:def:crs:EPSG:4326">' +
                '      <gml:exterior>' +
                '        <gml:LinearRing srsName=' +
                '          "urn:x-ogc:def:crs:EPSG:4326">' +
                '          <gml:posList srsDimension="2">' +
                '          38.9661 -77.0081 38.9931 -77.0421 ' +
                '          38.9321 -77.1221 38.9151 -77.0781 38.8861 ' +
                '          -77.0671 38.8621 -77.0391 38.8381 -77.0401 ' +
                '          38.8291 -77.0451 38.8131 -77.0351 38.7881 ' +
                '          -77.0451 38.8891 -76.9111 38.9661 -77.0081' +
                '          </gml:posList>' +
                '        </gml:LinearRing>' +
                '      </gml:exterior>' +
                '    </gml:Polygon>' +
                '  </gml:surfaceMember>' +
                '</gml:MultiSurface>';
            var g = readGeometry(format, text);
            expect(g.getCoordinates()[0][0][0][0]).to.equal(-77.0081);
            expect(g.getCoordinates()[0][0][0][1]).to.equal(38.9661);
            format = new ol.format.GML({
              srsName: 'urn:x-ogc:def:crs:EPSG:4326',
              surface: false});
            var serialized = format.writeGeometryNode(g);
            expect(serialized.firstElementChild).to.xmleql(ol.xml.parse(text));
          });

    });

    describe('linestring 3D', function() {

      it('can read a linestring 3D geometry', function() {
        var text =
            '<gml:LineString xmlns:gml="http://www.opengis.net/gml" ' +
            '    srsName="CRS:84" srsDimension="3">' +
            '  <gml:posList>1 2 3 4 5 6</gml:posList>' +
            '</gml:LineString>';
        var g = readGeometry(format, text);
        expect(g).to.be.an(ol.geom.LineString);
        expect(g.getCoordinates()).to.eql([[1, 2, 3], [4, 5, 6]]);
      });

    });

    describe('linearring', function() {

      it('can read and write a linearring geometry', function() {
        var text =
            '<gml:LinearRing xmlns:gml="http://www.opengis.net/gml" ' +
            '    srsName="CRS:84">' +
            '  <gml:posList srsDimension="2">1 2 3 4 5 6 1 2</gml:posList>' +
            '</gml:LinearRing>';
        var g = readGeometry(format, text);
        expect(g).to.be.an(ol.geom.LinearRing);
        expect(g.getCoordinates()).to.eql(
            [[1, 2, 0], [3, 4, 0], [5, 6, 0], [1, 2, 0]]);
        var serialized = format.writeGeometryNode(g);
        expect(serialized.firstElementChild).to.xmleql(ol.xml.parse(text));
      });

    });

    describe('polygon', function() {

      it('can read and write a polygon geometry', function() {
        var text =
            '<gml:Polygon xmlns:gml="http://www.opengis.net/gml" ' +
            '    srsName="CRS:84">' +
            '  <gml:exterior>' +
            '    <gml:LinearRing srsName="CRS:84">' +
            '     <gml:posList srsDimension="2">1 2 3 2 3 4 1 2</gml:posList>' +
            '    </gml:LinearRing>' +
            '  </gml:exterior>' +
            '  <gml:interior>' +
            '    <gml:LinearRing srsName="CRS:84">' +
            '     <gml:posList srsDimension="2">2 3 2 5 4 5 2 3</gml:posList>' +
            '    </gml:LinearRing>' +
            '  </gml:interior>' +
            '  <gml:interior>' +
            '    <gml:LinearRing srsName="CRS:84">' +
            '     <gml:posList srsDimension="2">3 4 3 6 5 6 3 4</gml:posList>' +
            '    </gml:LinearRing>' +
            '  </gml:interior>' +
            '</gml:Polygon>';
        var g = readGeometry(format, text);
        expect(g).to.be.an(ol.geom.Polygon);
        expect(g.getCoordinates()).to.eql([[[1, 2, 0], [3, 2, 0], [3, 4, 0],
          [1, 2, 0]], [[2, 3, 0], [2, 5, 0], [4, 5, 0], [2, 3, 0]],
        [[3, 4, 0], [3, 6, 0], [5, 6, 0], [3, 4, 0]]]);
        var serialized = format.writeGeometryNode(g);
        expect(serialized.firstElementChild).to.xmleql(ol.xml.parse(text));
      });

    });

    describe('surface', function() {

      it('can read and write a surface geometry', function() {
        var text =
            '<gml:Surface xmlns:gml="http://www.opengis.net/gml" ' +
            '    srsName="CRS:84">' +
            '  <gml:patches>' +
            '    <gml:PolygonPatch>' +
            '      <gml:exterior>' +
            '        <gml:LinearRing srsName="CRS:84">' +
            '          <gml:posList srsDimension="2">' +
            '            1 2 3 2 3 4 1 2' +
            '          </gml:posList>' +
            '        </gml:LinearRing>' +
            '      </gml:exterior>' +
            '      <gml:interior>' +
            '        <gml:LinearRing srsName="CRS:84">' +
            '          <gml:posList srsDimension="2">' +
            '            2 3 2 5 4 5 2 3' +
            '          </gml:posList>' +
            '        </gml:LinearRing>' +
            '      </gml:interior>' +
            '      <gml:interior>' +
            '        <gml:LinearRing srsName="CRS:84">' +
            '          <gml:posList srsDimension="2">' +
            '            3 4 3 6 5 6 3 4' +
            '          </gml:posList>' +
            '        </gml:LinearRing>' +
            '      </gml:interior>' +
            '    </gml:PolygonPatch>' +
            '  </gml:patches>' +
            '</gml:Surface>';
        var g = readGeometry(format, text);
        expect(g).to.be.an(ol.geom.Polygon);
        expect(g.getCoordinates()).to.eql([[[1, 2, 0], [3, 2, 0], [3, 4, 0],
          [1, 2, 0]], [[2, 3, 0], [2, 5, 0], [4, 5, 0], [2, 3, 0]],
        [[3, 4, 0], [3, 6, 0], [5, 6, 0], [3, 4, 0]]]);
        format = new ol.format.GML({srsName: 'CRS:84', surface: true});
        var serialized = format.writeGeometryNode(g);
        expect(serialized.firstElementChild).to.xmleql(ol.xml.parse(text));
      });

    });

    describe('curve', function() {

      it('can read and write a curve geometry', function() {
        var text =
            '<gml:Curve xmlns:gml="http://www.opengis.net/gml" ' +
            '    srsName="CRS:84">' +
            '  <gml:segments>' +
            '    <gml:LineStringSegment>' +
            '      <gml:posList srsDimension="2">1 2 3 4</gml:posList>' +
            '    </gml:LineStringSegment>' +
            '  </gml:segments>' +
            '</gml:Curve>';
        var g = readGeometry(format, text);
        expect(g).to.be.an(ol.geom.LineString);
        expect(g.getCoordinates()).to.eql([[1, 2, 0], [3, 4, 0]]);
        format = new ol.format.GML({srsName: 'CRS:84', curve: true});
        var serialized = format.writeGeometryNode(g);
        expect(serialized.firstElementChild).to.xmleql(ol.xml.parse(text));
      });

    });

    describe('envelope', function() {

      it('can read an envelope geometry', function() {
        var text =
            '<gml:Envelope xmlns:gml="http://www.opengis.net/gml" ' +
            '    srsName="CRS:84">' +
            '  <gml:lowerCorner>1 2</gml:lowerCorner>' +
            '  <gml:upperCorner>3 4</gml:upperCorner>' +
            '</gml:Envelope>';
        var g = readGeometry(format, text);
        expect(g).to.eql([1, 2, 3, 4]);
      });

    });

    describe('multipoint', function() {

      it('can read and write a singular multipoint geometry', function() {
        var text =
            '<gml:MultiPoint xmlns:gml="http://www.opengis.net/gml" ' +
            '    srsName="CRS:84">' +
            '  <gml:pointMember>' +
            '    <gml:Point srsName="CRS:84">' +
            '      <gml:pos srsDimension="2">1 2</gml:pos>' +
            '    </gml:Point>' +
            '  </gml:pointMember>' +
            '  <gml:pointMember>' +
            '    <gml:Point srsName="CRS:84">' +
            '      <gml:pos srsDimension="2">2 3</gml:pos>' +
            '    </gml:Point>' +
            '  </gml:pointMember>' +
            '  <gml:pointMember>' +
            '    <gml:Point srsName="CRS:84">' +
            '      <gml:pos srsDimension="2">3 4</gml:pos>' +
            '    </gml:Point>' +
            '  </gml:pointMember>' +
            '</gml:MultiPoint>';
        var g = readGeometry(format, text);
        expect(g).to.be.an(ol.geom.MultiPoint);
        expect(g.getCoordinates()).to.eql([[1, 2, 0], [2, 3, 0], [3, 4, 0]]);
        var serialized = format.writeGeometryNode(g);
        expect(serialized.firstElementChild).to.xmleql(ol.xml.parse(text));
      });

      it('can read a plural multipoint geometry', function() {
        var text =
            '<gml:MultiPoint xmlns:gml="http://www.opengis.net/gml" ' +
            '    srsName="CRS:84">' +
            '  <gml:pointMembers>' +
            '    <gml:Point>' +
            '      <gml:pos>1 2</gml:pos>' +
            '    </gml:Point>' +
            '    <gml:Point>' +
            '      <gml:pos>2 3</gml:pos>' +
            '    </gml:Point>' +
            '    <gml:Point>' +
            '      <gml:pos>3 4</gml:pos>' +
            '    </gml:Point>' +
            '  </gml:pointMembers>' +
            '</gml:MultiPoint>';
        var g = readGeometry(format, text);
        expect(g).to.be.an(ol.geom.MultiPoint);
        expect(g.getCoordinates()).to.eql([[1, 2, 0], [2, 3, 0], [3, 4, 0]]);
      });

    });

    describe('multilinestring', function() {

      it('can read and write a singular multilinestring geometry', function() {
        var text =
            '<gml:MultiLineString xmlns:gml="http://www.opengis.net/gml" ' +
            '    srsName="CRS:84">' +
            '  <gml:lineStringMember>' +
            '    <gml:LineString srsName="CRS:84">' +
            '      <gml:posList srsDimension="2">1 2 2 3</gml:posList>' +
            '    </gml:LineString>' +
            '  </gml:lineStringMember>' +
            '  <gml:lineStringMember>' +
            '    <gml:LineString srsName="CRS:84">' +
            '      <gml:posList srsDimension="2">3 4 4 5</gml:posList>' +
            '    </gml:LineString>' +
            '  </gml:lineStringMember>' +
            '</gml:MultiLineString>';
        var g = readGeometry(format, text);
        expect(g).to.be.an(ol.geom.MultiLineString);
        expect(g.getCoordinates()).to.eql(
            [[[1, 2, 0], [2, 3, 0]], [[3, 4, 0], [4, 5, 0]]]);
        format = new ol.format.GML({srsName: 'CRS:84', multiCurve: false});
        var serialized = format.writeGeometryNode(g);
        expect(serialized.firstElementChild).to.xmleql(ol.xml.parse(text));
      });

      it('can read a plural multilinestring geometry', function() {
        var text =
            '<gml:MultiLineString xmlns:gml="http://www.opengis.net/gml" ' +
            '    srsName="CRS:84">' +
            '  <gml:lineStringMembers>' +
            '    <gml:LineString>' +
            '      <gml:posList>1 2 2 3</gml:posList>' +
            '    </gml:LineString>' +
            '    <gml:LineString>' +
            '      <gml:posList>3 4 4 5</gml:posList>' +
            '    </gml:LineString>' +
            '  </gml:lineStringMembers>' +
            '</gml:MultiLineString>';
        var g = readGeometry(format, text);
        expect(g).to.be.an(ol.geom.MultiLineString);
        expect(g.getCoordinates()).to.eql(
            [[[1, 2, 0], [2, 3, 0]], [[3, 4, 0], [4, 5, 0]]]);
      });

    });

    describe('multipolygon', function() {

      it('can read and write a singular multipolygon geometry', function() {
        var text =
            '<gml:MultiPolygon xmlns:gml="http://www.opengis.net/gml" ' +
            '    srsName="CRS:84">' +
            '  <gml:polygonMember>' +
            '    <gml:Polygon srsName="CRS:84">' +
            '      <gml:exterior>' +
            '        <gml:LinearRing srsName="CRS:84">' +
            '          <gml:posList srsDimension="2">' +
            '            1 2 3 2 3 4 1 2' +
            '          </gml:posList>' +
            '        </gml:LinearRing>' +
            '      </gml:exterior>' +
            '      <gml:interior>' +
            '        <gml:LinearRing srsName="CRS:84">' +
            '          <gml:posList srsDimension="2">' +
            '            2 3 2 5 4 5 2 3' +
            '          </gml:posList>' +
            '        </gml:LinearRing>' +
            '      </gml:interior>' +
            '      <gml:interior>' +
            '        <gml:LinearRing srsName="CRS:84">' +
            '          <gml:posList srsDimension="2">' +
            '            3 4 3 6 5 6 3 4' +
            '          </gml:posList>' +
            '        </gml:LinearRing>' +
            '      </gml:interior>' +
            '    </gml:Polygon>' +
            '  </gml:polygonMember>' +
            '  <gml:polygonMember>' +
            '    <gml:Polygon srsName="CRS:84">' +
            '      <gml:exterior>' +
            '        <gml:LinearRing srsName="CRS:84">' +
            '          <gml:posList srsDimension="2">' +
            '            1 2 3 2 3 4 1 2' +
            '          </gml:posList>' +
            '        </gml:LinearRing>' +
            '      </gml:exterior>' +
            '    </gml:Polygon>' +
            '  </gml:polygonMember>' +
            '</gml:MultiPolygon>';
        var g = readGeometry(format, text);
        expect(g).to.be.an(ol.geom.MultiPolygon);
        expect(g.getCoordinates()).to.eql([
          [[[1, 2, 0], [3, 2, 0], [3, 4, 0],
            [1, 2, 0]], [[2, 3, 0], [2, 5, 0], [4, 5, 0], [2, 3, 0]],
          [[3, 4, 0], [3, 6, 0], [5, 6, 0], [3, 4, 0]]],
          [[[1, 2, 0], [3, 2, 0], [3, 4, 0], [1, 2, 0]]]]);
        format = new ol.format.GML({srsName: 'CRS:84', multiSurface: false});
        var serialized = format.writeGeometryNode(g);
        expect(serialized.firstElementChild).to.xmleql(ol.xml.parse(text));
      });

      it('can read a plural multipolygon geometry', function() {
        var text =
            '<gml:MultiPolygon xmlns:gml="http://www.opengis.net/gml" ' +
            '    srsName="CRS:84">' +
            '  <gml:polygonMembers>' +
            '    <gml:Polygon>' +
            '      <gml:exterior>' +
            '        <gml:LinearRing>' +
            '          <gml:posList>1 2 3 2 3 4 1 2</gml:posList>' +
            '        </gml:LinearRing>' +
            '      </gml:exterior>' +
            '      <gml:interior>' +
            '        <gml:LinearRing>' +
            '          <gml:posList>2 3 2 5 4 5 2 3</gml:posList>' +
            '        </gml:LinearRing>' +
            '      </gml:interior>' +
            '      <gml:interior>' +
            '        <gml:LinearRing>' +
            '          <gml:posList>3 4 3 6 5 6 3 4</gml:posList>' +
            '        </gml:LinearRing>' +
            '      </gml:interior>' +
            '    </gml:Polygon>' +
            '    <gml:Polygon>' +
            '      <gml:exterior>' +
            '        <gml:LinearRing>' +
            '          <gml:posList>1 2 3 2 3 4 1 2</gml:posList>' +
            '        </gml:LinearRing>' +
            '      </gml:exterior>' +
            '    </gml:Polygon>' +
            '  </gml:polygonMembers>' +
            '</gml:MultiPolygon>';
        var g = readGeometry(format, text);
        expect(g).to.be.an(ol.geom.MultiPolygon);
        expect(g.getCoordinates()).to.eql([
          [[[1, 2, 0], [3, 2, 0], [3, 4, 0],
            [1, 2, 0]], [[2, 3, 0], [2, 5, 0], [4, 5, 0], [2, 3, 0]],
          [[3, 4, 0], [3, 6, 0], [5, 6, 0], [3, 4, 0]]],
          [[[1, 2, 0], [3, 2, 0], [3, 4, 0], [1, 2, 0]]]]);
      });

    });

    describe('multicurve', function() {

      it('can read and write a singular multicurve-linestring geometry',
          function() {
            var text =
                '<gml:MultiCurve xmlns:gml="http://www.opengis.net/gml" ' +
                '    srsName="CRS:84">' +
                '  <gml:curveMember>' +
                '    <gml:LineString srsName="CRS:84">' +
                '      <gml:posList srsDimension="2">1 2 2 3</gml:posList>' +
                '    </gml:LineString>' +
                '  </gml:curveMember>' +
                '  <gml:curveMember>' +
                '    <gml:LineString srsName="CRS:84">' +
                '      <gml:posList srsDimension="2">3 4 4 5</gml:posList>' +
                '    </gml:LineString>' +
                '  </gml:curveMember>' +
                '</gml:MultiCurve>';
            var g = readGeometry(format, text);
            expect(g).to.be.an(ol.geom.MultiLineString);
            expect(g.getCoordinates()).to.eql(
                [[[1, 2, 0], [2, 3, 0]], [[3, 4, 0], [4, 5, 0]]]);
            var serialized = format.writeGeometryNode(g);
            expect(serialized.firstElementChild).to.xmleql(ol.xml.parse(text));
          });

      it('can read and write a singular multicurve-curve geometry', function() {
        var text =
            '<gml:MultiCurve xmlns:gml="http://www.opengis.net/gml" ' +
            '    srsName="CRS:84">' +
            '  <gml:curveMember>' +
            '    <gml:Curve srsName="CRS:84">' +
            '      <gml:segments>' +
            '        <gml:LineStringSegment>' +
            '          <gml:posList srsDimension="2">1 2 2 3</gml:posList>' +
            '        </gml:LineStringSegment>' +
            '      </gml:segments>' +
            '    </gml:Curve>' +
            '  </gml:curveMember>' +
            '  <gml:curveMember>' +
            '    <gml:Curve srsName="CRS:84">' +
            '      <gml:segments>' +
            '        <gml:LineStringSegment>' +
            '          <gml:posList srsDimension="2">3 4 4 5</gml:posList>' +
            '        </gml:LineStringSegment>' +
            '      </gml:segments>' +
            '    </gml:Curve>' +
            '  </gml:curveMember>' +
            '</gml:MultiCurve>';
        var g = readGeometry(format, text);
        expect(g).to.be.an(ol.geom.MultiLineString);
        expect(g.getCoordinates()).to.eql(
            [[[1, 2, 0], [2, 3, 0]], [[3, 4, 0], [4, 5, 0]]]);
        format = new ol.format.GML({srsName: 'CRS:84', curve: true});
        var serialized = format.writeGeometryNode(g);
        expect(serialized.firstElementChild).to.xmleql(ol.xml.parse(text));
      });

    });

    describe('multisurface', function() {

      it('can read and write a singular multisurface geometry', function() {
        var text =
            '<gml:MultiSurface xmlns:gml="http://www.opengis.net/gml" ' +
            '    srsName="CRS:84">' +
            '  <gml:surfaceMember>' +
            '    <gml:Polygon srsName="CRS:84">' +
            '      <gml:exterior>' +
            '        <gml:LinearRing srsName="CRS:84">' +
            '          <gml:posList srsDimension="2">' +
            '            1 2 3 2 3 4 1 2' +
            '          </gml:posList>' +
            '        </gml:LinearRing>' +
            '      </gml:exterior>' +
            '      <gml:interior>' +
            '        <gml:LinearRing srsName="CRS:84">' +
            '          <gml:posList srsDimension="2">' +
            '            2 3 2 5 4 5 2 3' +
            '          </gml:posList>' +
            '        </gml:LinearRing>' +
            '      </gml:interior>' +
            '      <gml:interior>' +
            '        <gml:LinearRing srsName="CRS:84">' +
            '          <gml:posList srsDimension="2">' +
            '            3 4 3 6 5 6 3 4' +
            '          </gml:posList>' +
            '        </gml:LinearRing>' +
            '      </gml:interior>' +
            '    </gml:Polygon>' +
            '  </gml:surfaceMember>' +
            '  <gml:surfaceMember>' +
            '    <gml:Polygon srsName="CRS:84">' +
            '      <gml:exterior>' +
            '        <gml:LinearRing srsName="CRS:84">' +
            '          <gml:posList srsDimension="2">' +
            '            1 2 3 2 3 4 1 2' +
            '          </gml:posList>' +
            '        </gml:LinearRing>' +
            '      </gml:exterior>' +
            '    </gml:Polygon>' +
            '  </gml:surfaceMember>' +
            '</gml:MultiSurface>';
        var g = readGeometry(format, text);
        expect(g).to.be.an(ol.geom.MultiPolygon);
        expect(g.getCoordinates()).to.eql([
          [[[1, 2, 0], [3, 2, 0], [3, 4, 0],
            [1, 2, 0]], [[2, 3, 0], [2, 5, 0], [4, 5, 0], [2, 3, 0]],
          [[3, 4, 0], [3, 6, 0], [5, 6, 0], [3, 4, 0]]],
          [[[1, 2, 0], [3, 2, 0], [3, 4, 0], [1, 2, 0]]]]);
        var serialized = format.writeGeometryNode(g);
        expect(serialized.firstElementChild).to.xmleql(ol.xml.parse(text));
      });

      it('can read a plural multisurface geometry', function() {
        var text =
            '<gml:MultiSurface xmlns:gml="http://www.opengis.net/gml" ' +
            '    srsName="CRS:84">' +
            '  <gml:surfaceMembers>' +
            '    <gml:Polygon>' +
            '      <gml:exterior>' +
            '        <gml:LinearRing>' +
            '          <gml:posList>1 2 3 2 3 4 1 2</gml:posList>' +
            '        </gml:LinearRing>' +
            '      </gml:exterior>' +
            '      <gml:interior>' +
            '        <gml:LinearRing>' +
            '          <gml:posList>2 3 2 5 4 5 2 3</gml:posList>' +
            '        </gml:LinearRing>' +
            '      </gml:interior>' +
            '      <gml:interior>' +
            '        <gml:LinearRing>' +
            '          <gml:posList>3 4 3 6 5 6 3 4</gml:posList>' +
            '        </gml:LinearRing>' +
            '      </gml:interior>' +
            '    </gml:Polygon>' +
            '  </gml:surfaceMembers>' +
            '  <gml:surfaceMembers>' +
            '    <gml:Polygon>' +
            '      <gml:exterior>' +
            '        <gml:LinearRing>' +
            '          <gml:posList>1 2 3 2 3 4 1 2</gml:posList>' +
            '        </gml:LinearRing>' +
            '      </gml:exterior>' +
            '    </gml:Polygon>' +
            '  </gml:surfaceMembers>' +
            '</gml:MultiSurface>';
        var g = readGeometry(format, text);
        expect(g).to.be.an(ol.geom.MultiPolygon);
        expect(g.getCoordinates()).to.eql([
          [[[1, 2, 0], [3, 2, 0], [3, 4, 0],
            [1, 2, 0]], [[2, 3, 0], [2, 5, 0], [4, 5, 0], [2, 3, 0]],
          [[3, 4, 0], [3, 6, 0], [5, 6, 0], [3, 4, 0]]],
          [[[1, 2, 0], [3, 2, 0], [3, 4, 0], [1, 2, 0]]]]);
      });

      it('can read and write a multisurface-surface geometry', function() {
        var text =
            '<gml:MultiSurface xmlns:gml="http://www.opengis.net/gml" ' +
            '    srsName="CRS:84">' +
            '  <gml:surfaceMember>' +
            '    <gml:Surface srsName="CRS:84">' +
            '      <gml:patches>' +
            '        <gml:PolygonPatch>' +
            '          <gml:exterior>' +
            '            <gml:LinearRing srsName="CRS:84">' +
            '              <gml:posList srsDimension="2">' +
            '                1 2 3 2 3 4 1 2' +
            '              </gml:posList>' +
            '            </gml:LinearRing>' +
            '          </gml:exterior>' +
            '          <gml:interior>' +
            '            <gml:LinearRing srsName="CRS:84">' +
            '              <gml:posList srsDimension="2">' +
            '                2 3 2 5 4 5 2 3' +
            '              </gml:posList>' +
            '            </gml:LinearRing>' +
            '          </gml:interior>' +
            '          <gml:interior>' +
            '            <gml:LinearRing srsName="CRS:84">' +
            '              <gml:posList srsDimension="2">' +
            '                3 4 3 6 5 6 3 4' +
            '              </gml:posList>' +
            '            </gml:LinearRing>' +
            '          </gml:interior>' +
            '        </gml:PolygonPatch>' +
            '      </gml:patches>' +
            '    </gml:Surface>' +
            '  </gml:surfaceMember>' +
            '  <gml:surfaceMember>' +
            '    <gml:Surface srsName="CRS:84">' +
            '      <gml:patches>' +
            '        <gml:PolygonPatch>' +
            '          <gml:exterior>' +
            '            <gml:LinearRing srsName="CRS:84">' +
            '              <gml:posList srsDimension="2">' +
            '                1 2 3 2 3 4 1 2' +
            '              </gml:posList>' +
            '            </gml:LinearRing>' +
            '          </gml:exterior>' +
            '        </gml:PolygonPatch>' +
            '      </gml:patches>' +
            '    </gml:Surface>' +
            '  </gml:surfaceMember>' +
            '</gml:MultiSurface>';
        var g = readGeometry(format, text);
        expect(g).to.be.an(ol.geom.MultiPolygon);
        expect(g.getCoordinates()).to.eql([
          [[[1, 2, 0], [3, 2, 0], [3, 4, 0],
            [1, 2, 0]], [[2, 3, 0], [2, 5, 0], [4, 5, 0], [2, 3, 0]],
          [[3, 4, 0], [3, 6, 0], [5, 6, 0], [3, 4, 0]]],
          [[[1, 2, 0], [3, 2, 0], [3, 4, 0], [1, 2, 0]]]]);
        format = new ol.format.GML({srsName: 'CRS:84', surface: true});
        var serialized = format.writeGeometryNode(g);
        expect(serialized.firstElementChild).to.xmleql(ol.xml.parse(text));
      });

    });

  });

  describe('when parsing empty attribute', function() {
    it('generates undefined value', function() {
      var text =
          '<gml:featureMembers xmlns:gml="http://www.opengis.net/gml">' +
          '  <topp:gnis_pop gml:id="gnis_pop.148604" xmlns:topp="' +
          'http://www.openplans.org/topp">' +
          '    <gml:name>Aflu</gml:name>' +
          '    <topp:the_geom>' +
          '      <gml:Point srsName="urn:x-ogc:def:crs:EPSG:4326">' +
          '        <gml:pos>34.12 2.09</gml:pos>' +
          '      </gml:Point>' +
          '    </topp:the_geom>' +
          '    <topp:population>84683</topp:population>' +
          '    <topp:country>Algeria</topp:country>' +
          '    <topp:type>place</topp:type>' +
          '    <topp:name>Aflu</topp:name>' +
          '    <topp:empty></topp:empty>' +
          '  </topp:gnis_pop>' +
          '</gml:featureMembers>';
      var config = {
        'featureNS': 'http://www.openplans.org/topp',
        'featureType': 'gnis_pop'
      };
      var features = new ol.format.GML(config).readFeatures(text);
      var feature = features[0];
      expect(feature.get('empty')).to.be(undefined);
    });
  });

  describe('when parsing CDATA attribute', function() {
    var features;
    before(function(done) {
      try {
        var text =
            '<gml:featureMembers xmlns:gml="http://www.opengis.net/gml">' +
            '  <topp:gnis_pop gml:id="gnis_pop.148604" xmlns:topp="' +
            'http://www.openplans.org/topp">' +
            '    <gml:name>Aflu</gml:name>' +
            '    <topp:the_geom>' +
            '      <gml:Point srsName="urn:x-ogc:def:crs:EPSG:4326">' +
            '        <gml:pos>34.12 2.09</gml:pos>' +
            '      </gml:Point>' +
            '    </topp:the_geom>' +
            '    <topp:population>84683</topp:population>' +
            '    <topp:country>Algeria</topp:country>' +
            '    <topp:type>place</topp:type>' +
            '    <topp:name>Aflu</topp:name>' +
            '    <topp:cdata><![CDATA[<a>b</a>]]></topp:cdata>' +
            '  </topp:gnis_pop>' +
            '</gml:featureMembers>';
        var config = {
          'featureNS': 'http://www.openplans.org/topp',
          'featureType': 'gnis_pop'
        };
        features = new ol.format.GML(config).readFeatures(text);
      } catch (e) {
        done(e);
      }
      done();
    });

    it('creates 1 feature', function() {
      expect(features).to.have.length(1);
    });

    it('converts XML attribute to text', function() {
      expect(features[0].get('cdata')).to.be('<a>b</a>');
    });
  });

  describe('when parsing TOPP states WFS with autoconfigure', function() {
    var features, gmlFormat;
    before(function(done) {
      afterLoadText('spec/ol/format/gml/topp-states-wfs.xml', function(xml) {
        try {
          gmlFormat = new ol.format.GML();
          features = gmlFormat.readFeatures(xml);
        } catch (e) {
          done(e);
        }
        done();
      });
    });

    it('creates 3 features', function() {
      expect(features).to.have.length(3);
    });

    it('creates the right id for the feature', function() {
      expect(features[0].getId()).to.equal('states.1');
    });

    it('can reuse the parser for a different featureNS', function() {
      var text =
          '<gml:featureMembers xmlns:gml="http://www.opengis.net/gml">' +
          '  <foo:gnis_pop gml:id="gnis_pop.148604" xmlns:foo="' +
          'http://foo">' +
          '    <gml:name>Aflu</gml:name>' +
          '    <foo:the_geom>' +
          '      <gml:Point srsName="urn:x-ogc:def:crs:EPSG:4326">' +
          '        <gml:pos>34.12 2.09</gml:pos>' +
          '      </gml:Point>' +
          '    </foo:the_geom>' +
          '    <foo:population>84683</foo:population>' +
          '  </foo:gnis_pop>' +
          '</gml:featureMembers>';
      features = gmlFormat.readFeatures(text);
      expect(features).to.have.length(1);
      expect(features[0].get('population')).to.equal('84683');
    });

    it('can read an empty collection', function() {
      var text =
          '<gml:featureMembers xmlns:gml="http://www.opengis.net/gml">' +
          '</gml:featureMembers>';
      features = gmlFormat.readFeatures(text);
      expect(features).to.have.length(0);
    });

  });

  describe('when parsing TOPP states GML', function() {

    var features, text, gmlFormat;
    before(function(done) {
      afterLoadText('spec/ol/format/gml/topp-states-gml.xml', function(xml) {
        try {
          var schemaLoc = 'http://www.openplans.org/topp ' +
              'http://demo.opengeo.org/geoserver/wfs?service=WFS&version=' +
              '1.1.0&request=DescribeFeatureType&typeName=topp:states ' +
              'http://www.opengis.net/gml ' +
              'http://schemas.opengis.net/gml/3.2.1/gml.xsd';
          var config = {
            'featureNS': 'http://www.openplans.org/topp',
            'featureType': 'states',
            'multiSurface': true,
            'srsName': 'urn:x-ogc:def:crs:EPSG:4326',
            'schemaLocation': schemaLoc
          };
          text = xml;
          gmlFormat = new ol.format.GML(config);
          features = gmlFormat.readFeatures(xml);
        } catch (e) {
          done(e);
        }
        done();
      });
    });

    it('creates 10 features', function() {
      expect(features).to.have.length(10);
    });

    it('creates the right id for the feature', function() {
      expect(features[0].getId()).to.equal('states.1');
    });

    it('writes back features as GML', function() {
      var serialized = gmlFormat.writeFeaturesNode(features);
      expect(serialized).to.xmleql(ol.xml.parse(text), {ignoreElementOrder: true});
    });

  });

  describe('when parsing TOPP states GML from WFS', function() {

    var features, feature;
    before(function(done) {
      afterLoadText('spec/ol/format/gml/topp-states-wfs.xml', function(xml) {
        try {
          var config = {
            'featureNS': 'http://www.openplans.org/topp',
            'featureType': 'states'
          };
          features = new ol.format.GML(config).readFeatures(xml);
        } catch (e) {
          done(e);
        }
        done();
      });
    });

    it('creates 3 features', function() {
      expect(features).to.have.length(3);
    });

    it('creates a polygon for Illinois', function() {
      feature = features[0];
      expect(feature.getId()).to.equal('states.1');
      expect(feature.get('STATE_NAME')).to.equal('Illinois');
      expect(feature.getGeometry()).to.be.an(ol.geom.MultiPolygon);
    });

  });

  describe('when parsing more than one geometry', function() {

    var features;
    before(function(done) {
      afterLoadText('spec/ol/format/gml/more-geoms.xml', function(xml) {
        try {
          var config = {
            'featureNS': 'http://opengeo.org/#medford',
            'featureType': 'zoning'
          };
          features = new ol.format.GML(config).readFeatures(xml);
        } catch (e) {
          done(e);
        }
        done();
      });
    });

    it('creates 2 geometries', function() {
      var feature = features[0];
      expect(feature.get('center')).to.be.a(ol.geom.Point);
      expect(feature.get('the_geom')).to.be.a(ol.geom.MultiPolygon);
    });

  });

  describe('when parsing an attribute name equal to featureType', function() {

    var features;
    before(function(done) {
      afterLoadText('spec/ol/format/gml/repeated-name.xml', function(xml) {
        try {
          var config = {
            'featureNS': 'http://opengeo.org/#medford',
            'featureType': 'zoning'
          };
          features = new ol.format.GML(config).readFeatures(xml);
        } catch (e) {
          done(e);
        }
        done();
      });
    });

    it('creates the correct attribute value', function() {
      var feature = features[0];
      expect(feature.get('zoning')).to.equal('I-L');
    });

  });

  describe('when parsing only a boundedBy element and no geometry', function() {

    var features;
    before(function(done) {
      afterLoadText('spec/ol/format/gml/only-boundedby.xml', function(xml) {
        try {
          features = new ol.format.GML().readFeatures(xml);
        } catch (e) {
          done(e);
        }
        done();
      });
    });

    it('creates a feature without a geometry', function() {
      var feature = features[0];
      expect(feature.getGeometry()).to.be(undefined);
    });

  });

  describe('when parsing from OGR', function() {

    var features;
    before(function(done) {
      afterLoadText('spec/ol/format/gml/ogr.xml', function(xml) {
        try {
          features = new ol.format.GML().readFeatures(xml);
        } catch (e) {
          done(e);
        }
        done();
      });
    });

    it('reads all features', function() {
      expect(features.length).to.be(1);
    });

  });

  describe('when parsing multiple feature types', function() {

    var features;
    before(function(done) {
      afterLoadText('spec/ol/format/gml/multiple-typenames.xml', function(xml) {
        try {
          features = new ol.format.GML({
            featureNS: 'http://localhost:8080/official',
            featureType: ['planet_osm_polygon', 'planet_osm_line']
          }).readFeatures(xml);
        } catch (e) {
          done(e);
        }
        done();
      });
    });

    it('reads all features', function() {
      expect(features.length).to.be(12);
    });

  });

  describe('when parsing multiple feature types', function() {

    var features;
    before(function(done) {
      afterLoadText('spec/ol/format/gml/multiple-typenames.xml', function(xml) {
        try {
          features = new ol.format.GML().readFeatures(xml);
        } catch (e) {
          done(e);
        }
        done();
      });
    });

    it('reads all features with autoconfigure', function() {
      expect(features.length).to.be(12);
    });

  });

  describe('when parsing multiple feature types / namespaces', function() {

    var features;
    before(function(done) {
      var url = 'spec/ol/format/gml/multiple-typenames-ns.xml';
      afterLoadText(url, function(xml) {
        try {
          features = new ol.format.GML({
            featureNS: {
              'topp': 'http://www.openplans.org/topp',
              'sf': 'http://www.openplans.org/spearfish'
            },
            featureType: ['topp:states', 'sf:roads']
          }).readFeatures(xml);
        } catch (e) {
          done(e);
        }
        done();
      });
    });

    it('reads all features', function() {
      expect(features.length).to.be(2);
    });

  });

  describe('when parsing multiple feature types / namespaces', function() {

    var features;
    before(function(done) {
      var url = 'spec/ol/format/gml/multiple-typenames-ns.xml';
      afterLoadText(url, function(xml) {
        try {
          features = new ol.format.GML().readFeatures(xml);
        } catch (e) {
          done(e);
        }
        done();
      });
    });

    it('reads all features with autoconfigure', function() {
      expect(features.length).to.be(2);
    });

  });

  describe('when parsing srsDimension from WFS (Geoserver)', function() {

    var features, feature;
    before(function(done) {
      afterLoadText('spec/ol/format/gml/geoserver3DFeatures.xml',
          function(xml) {
            try {
              var config = {
                'featureNS': 'http://www.opengeospatial.net/cite',
                'featureType': 'geoserver_layer'
              };
              features = new ol.format.GML(config).readFeatures(xml);
            } catch (e) {
              done(e);
            }
            done();
          });
    });

    it('creates 3 features', function() {
      expect(features).to.have.length(3);
    });

    it('creates a LineString', function() {
      feature = features[0];
      expect(feature.getId()).to.equal('geoserver_layer.1');
      expect(feature.getGeometry()).to.be.an(ol.geom.LineString);
    });

    it('creates a Polygon', function() {
      feature = features[1];
      expect(feature.getId()).to.equal('geoserver_layer.2');
      expect(feature.getGeometry()).to.be.an(ol.geom.Polygon);
    });

    it('creates a Point', function() {
      feature = features[2];
      expect(feature.getId()).to.equal('geoserver_layer.3');
      expect(feature.getGeometry()).to.be.an(ol.geom.Point);
    });


    it('creates 3D Features with the expected geometries', function() {
      var expectedGeometry1 = [
        4.46386854, 51.91122415, 46.04679351,
        4.46382399, 51.91120839, 46.04679382
      ];
      var expectedGeometry2 = [
        4.46385491, 51.91119276, 46.06074531,
        4.4638264, 51.91118582, 46.06074609,
        4.46380612, 51.91121772, 46.06074168,
        4.46383463, 51.91122465, 46.06074089,
        4.46385491, 51.91119276, 46.06074531
      ];
      var expectedGeometry3 = [
        4.46383715, 51.91125849, 46.04679348
      ];

      feature = features[0];
      expect(feature.getGeometry().getFlatCoordinates())
          .to.eql(expectedGeometry1);
      feature = features[1];
      expect(feature.getGeometry().getFlatCoordinates())
          .to.eql(expectedGeometry2);
      feature = features[2];
      expect(feature.getGeometry().getFlatCoordinates())
          .to.eql(expectedGeometry3);
    });

  });

});
