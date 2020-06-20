'use strict';

exports.__esModule = true;
exports.multi = exports.text = undefined;

var _text = require('./tokens/text');

var text = _interopRequireWildcard(_text);

var _multi = require('./tokens/multi');

var multi = _interopRequireWildcard(_multi);

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } else { var newObj = {}; if (obj != null) { for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) newObj[key] = obj[key]; } } newObj.default = obj; return newObj; } }

exports.text = text;
exports.multi = multi;