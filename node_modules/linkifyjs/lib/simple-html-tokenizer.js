'use strict';

exports.__esModule = true;

var _html5NamedCharRefs = require('./simple-html-tokenizer/html5-named-char-refs');

var _html5NamedCharRefs2 = _interopRequireDefault(_html5NamedCharRefs);

var _entityParser = require('./simple-html-tokenizer/entity-parser');

var _entityParser2 = _interopRequireDefault(_entityParser);

var _eventedTokenizer = require('./simple-html-tokenizer/evented-tokenizer');

var _eventedTokenizer2 = _interopRequireDefault(_eventedTokenizer);

var _tokenizer = require('./simple-html-tokenizer/tokenizer');

var _tokenizer2 = _interopRequireDefault(_tokenizer);

var _tokenize = require('./simple-html-tokenizer/tokenize');

var _tokenize2 = _interopRequireDefault(_tokenize);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var HTML5Tokenizer = {
	HTML5NamedCharRefs: _html5NamedCharRefs2.default,
	EntityParser: _entityParser2.default,
	EventedTokenizer: _eventedTokenizer2.default,
	Tokenizer: _tokenizer2.default,
	tokenize: _tokenize2.default
};

exports.default = HTML5Tokenizer;