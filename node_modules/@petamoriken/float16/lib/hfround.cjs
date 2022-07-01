"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.hfround = hfround;

var _converter = require("./_util/converter.cjs");

var _messages = require("./_util/messages.cjs");

var _primordials = require("./_util/primordials.cjs");

function hfround(num) {
  if (typeof num === "bigint") {
    throw (0, _primordials.NativeTypeError)(_messages.CANNOT_CONVERT_A_BIGINT_VALUE_TO_A_NUMBER);
  }

  num = (0, _primordials.NativeNumber)(num);

  if (!(0, _primordials.NumberIsFinite)(num) || num === 0) {
    return num;
  }

  const x16 = (0, _converter.roundToFloat16Bits)(num);
  return (0, _converter.convertToNumber)(x16);
}