"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.convertToNumber = convertToNumber;
exports.roundToFloat16Bits = roundToFloat16Bits;

var _primordials = require("./primordials.cjs");

const buffer = new _primordials.NativeArrayBuffer(4);
const floatView = new _primordials.NativeFloat32Array(buffer);
const uint32View = new _primordials.NativeUint32Array(buffer);
const baseTable = new _primordials.NativeUint32Array(512);
const shiftTable = new _primordials.NativeUint32Array(512);

for (let i = 0; i < 256; ++i) {
  const e = i - 127;

  if (e < -27) {
    baseTable[i] = 0x0000;
    baseTable[i | 0x100] = 0x8000;
    shiftTable[i] = 24;
    shiftTable[i | 0x100] = 24;
  } else if (e < -14) {
    baseTable[i] = 0x0400 >> -e - 14;
    baseTable[i | 0x100] = 0x0400 >> -e - 14 | 0x8000;
    shiftTable[i] = -e - 1;
    shiftTable[i | 0x100] = -e - 1;
  } else if (e <= 15) {
    baseTable[i] = e + 15 << 10;
    baseTable[i | 0x100] = e + 15 << 10 | 0x8000;
    shiftTable[i] = 13;
    shiftTable[i | 0x100] = 13;
  } else if (e < 128) {
    baseTable[i] = 0x7c00;
    baseTable[i | 0x100] = 0xfc00;
    shiftTable[i] = 24;
    shiftTable[i | 0x100] = 24;
  } else {
    baseTable[i] = 0x7c00;
    baseTable[i | 0x100] = 0xfc00;
    shiftTable[i] = 13;
    shiftTable[i | 0x100] = 13;
  }
}

function roundToFloat16Bits(num) {
  floatView[0] = num;
  const f = uint32View[0];
  const e = f >> 23 & 0x1ff;
  return baseTable[e] + ((f & 0x007fffff) >> shiftTable[e]);
}

const mantissaTable = new _primordials.NativeUint32Array(2048);
const exponentTable = new _primordials.NativeUint32Array(64);
const offsetTable = new _primordials.NativeUint32Array(64);
mantissaTable[0] = 0;

for (let i = 1; i < 1024; ++i) {
  let m = i << 13;
  let e = 0;

  while ((m & 0x00800000) === 0) {
    e -= 0x00800000;
    m <<= 1;
  }

  m &= ~0x00800000;
  e += 0x38800000;
  mantissaTable[i] = m | e;
}

for (let i = 1024; i < 2048; ++i) {
  mantissaTable[i] = 0x38000000 + (i - 1024 << 13);
}

exponentTable[0] = 0;

for (let i = 1; i < 31; ++i) {
  exponentTable[i] = i << 23;
}

exponentTable[31] = 0x47800000;
exponentTable[32] = 0x80000000;

for (let i = 33; i < 63; ++i) {
  exponentTable[i] = 0x80000000 + (i - 32 << 23);
}

exponentTable[63] = 0xc7800000;
offsetTable[0] = 0;

for (let i = 1; i < 64; ++i) {
  if (i === 32) {
    offsetTable[i] = 0;
  } else {
    offsetTable[i] = 1024;
  }
}

function convertToNumber(float16bits) {
  const m = float16bits >> 10;
  uint32View[0] = mantissaTable[offsetTable[m] + (float16bits & 0x3ff)] + exponentTable[m];
  return floatView[0];
}