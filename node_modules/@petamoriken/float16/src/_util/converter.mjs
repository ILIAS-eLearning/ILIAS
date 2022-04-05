// algorithm: http://fox-toolkit.org/ftp/fasthalffloatconversion.pdf

import {
  NativeArrayBuffer,
  NativeFloat32Array,
  NativeUint32Array,
} from "./primordials.mjs";

const buffer = new NativeArrayBuffer(4);
const floatView = new NativeFloat32Array(buffer);
const uint32View = new NativeUint32Array(buffer);

const baseTable = new NativeUint32Array(512);
const shiftTable = new NativeUint32Array(512);

for (let i = 0; i < 256; ++i) {
  const e = i - 127;

  // very small number (0, -0)
  if (e < -27) {
    baseTable[i]         = 0x0000;
    baseTable[i | 0x100] = 0x8000;
    shiftTable[i]         = 24;
    shiftTable[i | 0x100] = 24;

  // small number (denorm)
  } else if (e < -14) {
    baseTable[i]         =  0x0400 >> (-e - 14);
    baseTable[i | 0x100] = (0x0400 >> (-e - 14)) | 0x8000;
    shiftTable[i]         = -e - 1;
    shiftTable[i | 0x100] = -e - 1;

  // normal number
  } else if (e <= 15) {
    baseTable[i]         =  (e + 15) << 10;
    baseTable[i | 0x100] = ((e + 15) << 10) | 0x8000;
    shiftTable[i]         = 13;
    shiftTable[i | 0x100] = 13;

  // large number (Infinity, -Infinity)
  } else if (e < 128) {
    baseTable[i]         = 0x7c00;
    baseTable[i | 0x100] = 0xfc00;
    shiftTable[i]         = 24;
    shiftTable[i | 0x100] = 24;

  // stay (NaN, Infinity, -Infinity)
  } else {
    baseTable[i]         = 0x7c00;
    baseTable[i | 0x100] = 0xfc00;
    shiftTable[i]         = 13;
    shiftTable[i | 0x100] = 13;
  }
}

/**
 * round a number to a half float number bits.
 *
 * @param {unknown} num - double float
 * @returns {number} half float number bits
 */
export function roundToFloat16Bits(num) {
  floatView[0] = /** @type {any} */ (num);
  const f = uint32View[0];
  const e = (f >> 23) & 0x1ff;
  return baseTable[e] + ((f & 0x007fffff) >> shiftTable[e]);
}

const mantissaTable = new NativeUint32Array(2048);
const exponentTable = new NativeUint32Array(64);
const offsetTable = new NativeUint32Array(64);

mantissaTable[0] = 0;
for (let i = 1; i < 1024; ++i) {
  let m = i << 13;    // zero pad mantissa bits
  let e = 0;          // zero exponent

  // normalized
  while((m & 0x00800000) === 0) {
    e -= 0x00800000;  // decrement exponent
    m <<= 1;
  }

  m &= ~0x00800000;   // clear leading 1 bit
  e += 0x38800000;    // adjust bias

  mantissaTable[i] = m | e;
}
for (let i = 1024; i < 2048; ++i) {
  mantissaTable[i] = 0x38000000 + ((i - 1024) << 13);
}

exponentTable[0] = 0;
for (let i = 1; i < 31; ++i) {
  exponentTable[i] = i << 23;
}
exponentTable[31] = 0x47800000;
exponentTable[32] = 0x80000000;
for (let i = 33; i < 63; ++i) {
  exponentTable[i] = 0x80000000 + ((i - 32) << 23);
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

/**
 * convert a half float number bits to a number.
 *
 * @param {number} float16bits - half float number bits
 * @returns {number} double float
 */
export function convertToNumber(float16bits) {
  const m = float16bits >> 10;
  uint32View[0] = mantissaTable[offsetTable[m] + (float16bits & 0x3ff)] + exponentTable[m];
  return floatView[0];
}
