/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Returns a hash code which is NOT cryptographically secure.
 * This can be used in contexts where Window.crypto is not available.
 *
 * The current algorithm is based on Paul Hsieh's SuperFastHash which
 * has been translated to JavaScript. Unlike the original algorithm,
 * this function returns a fixed-width string instead of an unsigned
 * integer.
 *
 * @see http://www.azillionmonkeys.com/qed/hash.html
 *
 * @param {string} string
 * @returns {string}
 */
export default function createHashCode(string) {
  /* eslint-disable no-bitwise */

  const bytes = new TextEncoder().encode(string);

  let len = bytes.length;
  if (len <= 0) return 0;

  let hash = len >>> 0;
  let tmp;
  let i = 0;

  const get16bits = (p) => bytes[p] | (bytes[p + 1] << 8);

  const rem = len & 3;
  len >>>= 2;

  // main loop
  for (; len > 0; len -= 1) {
    hash = (hash + get16bits(i)) >>> 0;
    tmp = ((get16bits(i + 2) << 11) ^ hash) >>> 0;
    hash = ((hash << 16) ^ tmp) >>> 0;
    i += 4;
    hash = (hash + (hash >>> 11)) >>> 0;
  }

  // handle end cases
  switch (rem) {
    case 3:
      hash = (hash + get16bits(i)) >>> 0;
      hash = (hash ^ (hash << 16)) >>> 0;
      // (signed char) cast: sign-extend the byte
      hash = (hash ^ (((bytes[i + 2] << 24) >> 24) << 18)) >>> 0;
      hash = (hash + (hash >>> 11)) >>> 0;
      break;
    case 2:
      hash = (hash + get16bits(i)) >>> 0;
      hash = (hash ^ (hash << 11)) >>> 0;
      hash = (hash + (hash >>> 17)) >>> 0;
      break;
    case 1:
      hash = (hash + ((bytes[i] << 24) >> 24)) >>> 0;
      hash = (hash ^ (hash << 10)) >>> 0;
      hash = (hash + (hash >>> 1)) >>> 0;
      break;
    default:
      // end case valid
      break;
  }

  // force "avalanching" of final 127 bits
  hash = (hash ^ (hash << 3)) >>> 0;
  hash = (hash + (hash >>> 5)) >>> 0;
  hash = (hash ^ (hash << 4)) >>> 0;
  hash = (hash + (hash >>> 17)) >>> 0;
  hash = (hash ^ (hash << 25)) >>> 0;
  hash = (hash + (hash >>> 6)) >>> 0;

  // return fixed-width 8 character hex string
  return hash.toString(16).padStart(8, '0');
}
