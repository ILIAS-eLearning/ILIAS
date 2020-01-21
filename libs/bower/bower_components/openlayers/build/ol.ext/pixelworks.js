
/**
 * @fileoverview
 * @suppress {accessControls, ambiguousFunctionDecl, checkDebuggerStatement, checkRegExp, checkTypes, checkVars, const, constantProperty, deprecated, duplicate, es5Strict, fileoverviewTags, missingProperties, nonStandardJsDocs, strictModuleDepCheck, suspiciousCode, undefinedNames, undefinedVars, unknownDefines, unusedLocalVariables, uselessCode, visibility}
 */
goog.provide('ol.ext.pixelworks.Processor');

/** @typedef {function(*)} */
ol.ext.pixelworks.Processor = function() {};

(function() {(function (exports) {
'use strict';

var hasImageData = true;
try {
  new ImageData(10, 10);
} catch (_) {
  hasImageData = false;
}
var context = document.createElement('canvas').getContext('2d');
function newImageData$1(data, width, height) {
  if (hasImageData) {
    return new ImageData(data, width, height);
  } else {
    var imageData = context.createImageData(width, height);
    imageData.data.set(data);
    return imageData;
  }
}
var newImageData_1 = newImageData$1;
var util = {
	newImageData: newImageData_1
};

var newImageData = util.newImageData;
function createMinion(operation) {
  var workerHasImageData = true;
  try {
    new ImageData(10, 10);
  } catch (_) {
    workerHasImageData = false;
  }
  function newWorkerImageData(data, width, height) {
    if (workerHasImageData) {
      return new ImageData(data, width, height);
    } else {
      return {data: data, width: width, height: height};
    }
  }
  return function(data) {
    var buffers = data['buffers'];
    var meta = data['meta'];
    var imageOps = data['imageOps'];
    var width = data['width'];
    var height = data['height'];
    var numBuffers = buffers.length;
    var numBytes = buffers[0].byteLength;
    var output, b;
    if (imageOps) {
      var images = new Array(numBuffers);
      for (b = 0; b < numBuffers; ++b) {
        images[b] = newWorkerImageData(
            new Uint8ClampedArray(buffers[b]), width, height);
      }
      output = operation(images, meta).data;
    } else {
      output = new Uint8ClampedArray(numBytes);
      var arrays = new Array(numBuffers);
      var pixels = new Array(numBuffers);
      for (b = 0; b < numBuffers; ++b) {
        arrays[b] = new Uint8ClampedArray(buffers[b]);
        pixels[b] = [0, 0, 0, 0];
      }
      for (var i = 0; i < numBytes; i += 4) {
        for (var j = 0; j < numBuffers; ++j) {
          var array = arrays[j];
          pixels[j][0] = array[i];
          pixels[j][1] = array[i + 1];
          pixels[j][2] = array[i + 2];
          pixels[j][3] = array[i + 3];
        }
        var pixel = operation(pixels, meta);
        output[i] = pixel[0];
        output[i + 1] = pixel[1];
        output[i + 2] = pixel[2];
        output[i + 3] = pixel[3];
      }
    }
    return output.buffer;
  };
}
function createWorker(config, onMessage) {
  var lib = Object.keys(config.lib || {}).map(function(name) {
    return 'var ' + name + ' = ' + config.lib[name].toString() + ';';
  });
  var lines = lib.concat([
    'var __minion__ = (' + createMinion.toString() + ')(', config.operation.toString(), ');',
    'self.addEventListener("message", function(event) {',
    '  var buffer = __minion__(event.data);',
    '  self.postMessage({buffer: buffer, meta: event.data.meta}, [buffer]);',
    '});'
  ]);
  var blob = new Blob(lines, {type: 'text/javascript'});
  var source = URL.createObjectURL(blob);
  var worker = new Worker(source);
  worker.addEventListener('message', onMessage);
  return worker;
}
function createFauxWorker(config, onMessage) {
  var minion = createMinion(config.operation);
  return {
    postMessage: function(data) {
      setTimeout(function() {
        onMessage({'data': {'buffer': minion(data), 'meta': data['meta']}});
      }, 0);
    }
  };
}
function Processor(config) {
  this._imageOps = !!config.imageOps;
  var threads;
  if (config.threads === 0) {
    threads = 0;
  } else if (this._imageOps) {
    threads = 1;
  } else {
    threads = config.threads || 1;
  }
  var workers = [];
  if (threads) {
    for (var i = 0; i < threads; ++i) {
      workers[i] = createWorker(config, this._onWorkerMessage.bind(this, i));
    }
  } else {
    workers[0] = createFauxWorker(config, this._onWorkerMessage.bind(this, 0));
  }
  this._workers = workers;
  this._queue = [];
  this._maxQueueLength = config.queue || Infinity;
  this._running = 0;
  this._dataLookup = {};
  this._job = null;
}
Processor.prototype.process = function(inputs, meta, callback) {
  this._enqueue({
    inputs: inputs,
    meta: meta,
    callback: callback
  });
  this._dispatch();
};
Processor.prototype.destroy = function() {
  for (var key in this) {
    this[key] = null;
  }
  this._destroyed = true;
};
Processor.prototype._enqueue = function(job) {
  this._queue.push(job);
  while (this._queue.length > this._maxQueueLength) {
    this._queue.shift().callback(null, null);
  }
};
Processor.prototype._dispatch = function() {
  if (this._running === 0 && this._queue.length > 0) {
    var job = this._job = this._queue.shift();
    var width = job.inputs[0].width;
    var height = job.inputs[0].height;
    var buffers = job.inputs.map(function(input) {
      return input.data.buffer;
    });
    var threads = this._workers.length;
    this._running = threads;
    if (threads === 1) {
      this._workers[0].postMessage({
        'buffers': buffers,
        'meta': job.meta,
        'imageOps': this._imageOps,
        'width': width,
        'height': height
      }, buffers);
    } else {
      var length = job.inputs[0].data.length;
      var segmentLength = 4 * Math.ceil(length / 4 / threads);
      for (var i = 0; i < threads; ++i) {
        var offset = i * segmentLength;
        var slices = [];
        for (var j = 0, jj = buffers.length; j < jj; ++j) {
          slices.push(buffers[i].slice(offset, offset + segmentLength));
        }
        this._workers[i].postMessage({
          'buffers': slices,
          'meta': job.meta,
          'imageOps': this._imageOps,
          'width': width,
          'height': height
        }, slices);
      }
    }
  }
};
Processor.prototype._onWorkerMessage = function(index, event) {
  if (this._destroyed) {
    return;
  }
  this._dataLookup[index] = event.data;
  --this._running;
  if (this._running === 0) {
    this._resolveJob();
  }
};
Processor.prototype._resolveJob = function() {
  var job = this._job;
  var threads = this._workers.length;
  var data, meta;
  if (threads === 1) {
    data = new Uint8ClampedArray(this._dataLookup[0]['buffer']);
    meta = this._dataLookup[0]['meta'];
  } else {
    var length = job.inputs[0].data.length;
    data = new Uint8ClampedArray(length);
    meta = new Array(length);
    var segmentLength = 4 * Math.ceil(length / 4 / threads);
    for (var i = 0; i < threads; ++i) {
      var buffer = this._dataLookup[i]['buffer'];
      var offset = i * segmentLength;
      data.set(new Uint8ClampedArray(buffer), offset);
      meta[i] = this._dataLookup[i]['meta'];
    }
  }
  this._job = null;
  this._dataLookup = {};
  job.callback(null,
      newImageData(data, job.inputs[0].width, job.inputs[0].height), meta);
  this._dispatch();
};
var processor = Processor;

var Processor_1 = processor;
var lib = {
	Processor: Processor_1
};

exports['default'] = lib;
exports.Processor = Processor_1;

}((this.pixelworks = this.pixelworks || {})));}).call(ol.ext);
