var hasImageData = true;
try {
  new ImageData(10, 10);
} catch (_) {
  hasImageData = false;
}

var context = document.createElement('canvas').getContext('2d');

function newImageData(data, width, height) {
  if (hasImageData) {
    return new ImageData(data, width, height);
  } else {
    var imageData = context.createImageData(width, height);
    imageData.data.set(data);
    return imageData;
  }
}

exports.newImageData = newImageData;
