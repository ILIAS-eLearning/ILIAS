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
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */

/**
 * Function used by '@rollup/plugin-terser' to preserve only comments which
 * contain a string indicating a copyright notice.
 *
 * Additionally, it only keeps ILIAS copyright notices if they are at the beginning
 * of a file, so that the license is not duplicated in the bundled file (this works
 * due to plugins being ran after the bundle is created).
 *
 * @param {Object} node
 * @param {number} line
 * @param {string} value
 * @return {boolean}
 */
export default function preserveCopyright(node, { line, value }) {
  const copyrightRegex = /(copyright|license|@preserve|@license|@cc_on)/i;
  const iliasRegex = /(ilias)/i;

  if (!copyrightRegex.test(value)) {
    return false;
  }

  // keeps comments which are at the begining of a file.
  if (iliasRegex.test(value)) {
    return (line === 0 || line === 1);
  }

  return true;
}
