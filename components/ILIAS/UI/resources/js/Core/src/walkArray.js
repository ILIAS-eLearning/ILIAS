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
 * This function will be used to iterate arrays instead of Array.forEach(),
 * during async processing.
 *
 * @param {Array} array
 * @param {function(*, number)} callback
 */
export default function walkArray(array, callback) {
  for (let index = 0; index < array.length; index += 1) {
    callback(array[index], index);
  }
}
