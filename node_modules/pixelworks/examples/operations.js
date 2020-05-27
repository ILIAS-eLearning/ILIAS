/* global pixelworks */

var luminance = function(pixels) {
  var pixel = pixels[0];
  var l = 0.2126 * pixel[0] + 0.7152 * pixel[1] + 0.0722 * pixel[2];
  pixel[0] = l;
  pixel[1] = l;
  pixel[2] = l;
  return pixels;
};

var color = function(pixels, data) {
  var pixel = pixels[0];
  var l = pixel[0];
  if (l > data.threshold) {
    pixel[0] = 255;
    pixel[1] = 255;
    pixel[2] = 150;
    pixel[3] = 200;
  } else {
    pixel[3] = 0;
  }
  return pixel;
};

var inputContext = document.getElementById('input').getContext('2d');
var outputContext = document.getElementById('output').getContext('2d');
var image = new Image();

var worker = new pixelworks.Processor({
  operation: function(pixels, data) {
    return color(luminance(pixels), data);
  },
  lib: {
    luminance: luminance,
    color: color
  }
});

var threshold = document.getElementById('threshold');
var data = {
  threshold: threshold.value
};

function process() {
  var canvas = inputContext.canvas;
  var input = inputContext.getImageData(0, 0, canvas.width, canvas.height);
  worker.process([input], data, function(err, output) {
    if (err) {
      throw err;
    }
    outputContext.putImageData(output, 0, 0);
  });
}

threshold.oninput = function() {
  data.threshold = this.value;
  process();
};

image.onload = function() {
  inputContext.drawImage(image, 0, 0);
  process();
};

image.src = '0.jpg';
