goog.provide('ol.geom.flat.textpath');

goog.require('ol.math');


/**
 * @param {Array.<number>} flatCoordinates Path to put text on.
 * @param {number} offset Start offset of the `flatCoordinates`.
 * @param {number} end End offset of the `flatCoordinates`.
 * @param {number} stride Stride.
 * @param {string} text Text to place on the path.
 * @param {function(string):number} measure Measure function returning the
 * width of the character passed as 1st argument.
 * @param {number} startM m along the path where the text starts.
 * @param {number} maxAngle Max angle between adjacent chars in radians.
 * @return {Array.<Array.<*>>} The result array of null if `maxAngle` was
 * exceeded. Entries of the array are x, y, anchorX, angle, chunk.
 */
ol.geom.flat.textpath.lineString = function(
    flatCoordinates, offset, end, stride, text, measure, startM, maxAngle) {
  var result = [];

  // Keep text upright
  var reverse = flatCoordinates[offset] > flatCoordinates[end - stride];

  var numChars = text.length;

  var x1 = flatCoordinates[offset];
  var y1 = flatCoordinates[offset + 1];
  offset += stride;
  var x2 = flatCoordinates[offset];
  var y2 = flatCoordinates[offset + 1];
  var segmentM = 0;
  var segmentLength = Math.sqrt(Math.pow(x2 - x1, 2) + Math.pow(y2 - y1, 2));

  var chunk = '';
  var chunkLength = 0;
  var data, index, previousAngle;
  for (var i = 0; i < numChars; ++i) {
    index = reverse ? numChars - i - 1 : i;
    var char = text.charAt(index);
    chunk = reverse ? char + chunk : chunk + char;
    var charLength = measure(chunk) - chunkLength;
    chunkLength += charLength;
    var charM = startM + charLength / 2;
    while (offset < end - stride && segmentM + segmentLength < charM) {
      x1 = x2;
      y1 = y2;
      offset += stride;
      x2 = flatCoordinates[offset];
      y2 = flatCoordinates[offset + 1];
      segmentM += segmentLength;
      segmentLength = Math.sqrt(Math.pow(x2 - x1, 2) + Math.pow(y2 - y1, 2));
    }
    var segmentPos = charM - segmentM;
    var angle = Math.atan2(y2 - y1, x2 - x1);
    if (reverse) {
      angle += angle > 0 ? -Math.PI : Math.PI;
    }
    if (previousAngle !== undefined) {
      var delta = angle - previousAngle;
      delta += (delta > Math.PI) ? -2 * Math.PI : (delta < -Math.PI) ? 2 * Math.PI : 0;
      if (Math.abs(delta) > maxAngle) {
        return null;
      }
    }
    var interpolate = segmentPos / segmentLength;
    var x = ol.math.lerp(x1, x2, interpolate);
    var y = ol.math.lerp(y1, y2, interpolate);
    if (previousAngle == angle) {
      if (reverse) {
        data[0] = x;
        data[1] = y;
        data[2] = charLength / 2;
      }
      data[4] = chunk;
    } else {
      chunk = char;
      chunkLength = charLength;
      data = [x, y, charLength / 2, angle, chunk];
      if (reverse) {
        result.unshift(data);
      } else {
        result.push(data);
      }
      previousAngle = angle;
    }
    startM += charLength;
  }
  return result;
};
