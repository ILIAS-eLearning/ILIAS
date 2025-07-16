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
 * Returns a formatted string where each '%s' placeholder in the format string is replaced
 * by the corresponding value from the provided arguments, in order.
 *
 * Only '%s' placeholders are supported. If there are more placeholders than arguments,
 * unmatched placeholders will be replaced with an empty string. Extra arguments are ignored.
 *
 * @param {string} format format string containing zero or more '%s' placeholders.
 * @param {...any} substitutes values to substitute for each '%s' in the format string.
 * @returns {string}
 */
export default function sprintf(format, ...substitutes) {
  const queue = [...substitutes];
  return format.replace(/%s/g, () => queue.shift() ?? '');
}
