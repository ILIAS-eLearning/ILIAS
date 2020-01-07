

goog.require('ol.Image');
goog.require('ol.events');
goog.require('ol.proj');
goog.require('ol.reproj.Image');


describe('ol.reproj.Image', function() {
  function createImage(pixelRatio) {
    return new ol.reproj.Image(
        ol.proj.get('EPSG:3857'), ol.proj.get('EPSG:4326'),
        [-180, -85, 180, 85], 10, pixelRatio,
        function(extent, resolution, pixelRatio) {
          return new ol.Image(extent, resolution, pixelRatio,
              'data:image/gif;base64,' +
              'R0lGODlhAQABAIAAAP///wAAACwAAAAAAQABAAACAkQBADs=', null,
              function(image, src) {
                image.getImage().src = src;
              });
        });
  }

  it('changes state as expected', function(done) {
    var image = createImage(1);
    expect(image.getState()).to.be(0); // IDLE
    ol.events.listen(image, 'change', function() {
      if (image.getState() == 2) { // LOADED
        done();
      }
    });
    image.load();
  });

  it('returns correct canvas size', function(done) {
    var image = createImage(1);
    ol.events.listen(image, 'change', function() {
      if (image.getState() == 2) { // LOADED
        var canvas = image.getImage();
        expect(canvas.width).to.be(36);
        expect(canvas.height).to.be(17);
        done();
      }
    });
    image.load();
  });

  it('respects pixelRatio', function(done) {
    var image = createImage(2);
    ol.events.listen(image, 'change', function() {
      if (image.getState() == 2) { // LOADED
        var canvas = image.getImage();
        expect(canvas.width).to.be(72);
        expect(canvas.height).to.be(34);
        done();
      }
    });
    image.load();
  });
});
