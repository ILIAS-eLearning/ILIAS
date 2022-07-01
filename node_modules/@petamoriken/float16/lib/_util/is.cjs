"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.isArrayBuffer = isArrayBuffer;
exports.isCanonicalIntegerIndexString = isCanonicalIntegerIndexString;
exports.isNativeBigIntTypedArray = isNativeBigIntTypedArray;
exports.isNativeTypedArray = isNativeTypedArray;
exports.isObject = isObject;
exports.isObjectLike = isObjectLike;
exports.isOrdinaryArray = isOrdinaryArray;
exports.isOrdinaryNativeTypedArray = isOrdinaryNativeTypedArray;
exports.isSharedArrayBuffer = isSharedArrayBuffer;

var _primordials = require("./primordials.cjs");

function isObject(value) {
  return value !== null && typeof value === "object" || typeof value === "function";
}

function isObjectLike(value) {
  return value !== null && typeof value === "object";
}

function isNativeTypedArray(value) {
  return (0, _primordials.TypedArrayPrototypeGetSymbolToStringTag)(value) !== undefined;
}

function isNativeBigIntTypedArray(value) {
  const typedArrayName = (0, _primordials.TypedArrayPrototypeGetSymbolToStringTag)(value);
  return typedArrayName === "BigInt64Array" || typedArrayName === "BigUint64Array";
}

function isArrayBuffer(value) {
  try {
    (0, _primordials.ArrayBufferPrototypeGetByteLength)(value);
    return true;
  } catch (e) {
    return false;
  }
}

function isSharedArrayBuffer(value) {
  if (_primordials.NativeSharedArrayBuffer === null) {
    return false;
  }

  try {
    (0, _primordials.SharedArrayBufferPrototypeGetByteLength)(value);
    return true;
  } catch (e) {
    return false;
  }
}

function isOrdinaryArray(value) {
  if (!(0, _primordials.ArrayIsArray)(value)) {
    return false;
  }

  if (value[_primordials.SymbolIterator] === _primordials.NativeArrayPrototypeSymbolIterator) {
    return true;
  }

  const iterator = value[_primordials.SymbolIterator]();

  return iterator[_primordials.SymbolToStringTag] === "Array Iterator";
}

function isOrdinaryNativeTypedArray(value) {
  if (!isNativeTypedArray(value)) {
    return false;
  }

  if (value[_primordials.SymbolIterator] === _primordials.NativeTypedArrayPrototypeSymbolIterator) {
    return true;
  }

  const iterator = value[_primordials.SymbolIterator]();

  return iterator[_primordials.SymbolToStringTag] === "Array Iterator";
}

function isCanonicalIntegerIndexString(value) {
  if (typeof value !== "string") {
    return false;
  }

  const number = (0, _primordials.NativeNumber)(value);

  if (value !== number + "") {
    return false;
  }

  if (!(0, _primordials.NumberIsFinite)(number)) {
    return false;
  }

  return number === (0, _primordials.MathTrunc)(number);
}