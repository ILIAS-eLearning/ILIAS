"use strict";
/// <reference lib="es2018" />
Object.defineProperty(exports, "__esModule", { value: true });
exports.isIterator = exports.isAsyncIterator = void 0;
const _symbols_1 = require("./_symbols");
function isAsyncIterator(thing) {
    return thing && _symbols_1.hasSymbol("asyncIterator") && thing[Symbol.asyncIterator];
}
exports.isAsyncIterator = isAsyncIterator;
function isIterator(thing) {
    return thing && _symbols_1.hasSymbol("iterator") && thing[Symbol.iterator];
}
exports.isIterator = isIterator;
