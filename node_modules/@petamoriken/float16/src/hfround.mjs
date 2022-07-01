import { convertToNumber, roundToFloat16Bits } from "./_util/converter.mjs";
import { CANNOT_CONVERT_A_BIGINT_VALUE_TO_A_NUMBER } from "./_util/messages.mjs";
import {
  NativeNumber,
  NativeTypeError,
  NumberIsFinite,
} from "./_util/primordials.mjs";

/**
 * returns the nearest half-precision float representation of a number
 *
 * @param {number} num
 * @returns {number}
 */
export function hfround(num) {
  if (typeof num === "bigint") {
    throw NativeTypeError(CANNOT_CONVERT_A_BIGINT_VALUE_TO_A_NUMBER);
  }

  num = NativeNumber(num);

  // for optimization
  if (!NumberIsFinite(num) || num === 0) {
    return num;
  }

  const x16 = roundToFloat16Bits(num);
  return convertToNumber(x16);
}
