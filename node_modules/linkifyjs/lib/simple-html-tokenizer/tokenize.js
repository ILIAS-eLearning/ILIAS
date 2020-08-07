'use strict';

exports.__esModule = true;
exports.default = tokenize;

var _tokenizer = require('./tokenizer');

var _tokenizer2 = _interopRequireDefault(_tokenizer);

var _entityParser = require('./entity-parser');

var _entityParser2 = _interopRequireDefault(_entityParser);

var _html5NamedCharRefs = require('./html5-named-char-refs');

var _html5NamedCharRefs2 = _interopRequireDefault(_html5NamedCharRefs);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function tokenize(input, options) {
  var tokenizer = new _tokenizer2.default(new _entityParser2.default(_html5NamedCharRefs2.default), options);
  return tokenizer.tokenize(input);
}