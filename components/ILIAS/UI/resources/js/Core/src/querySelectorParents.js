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
 * Returns all (parent-)elements which match the given selector up to a specified limit of
 * matches. The DOM is traversed upwards until the limit is reached or no match is found.
 *
 * Note the returned array MAY contain the element itself and all elements will be ordered
 * by DOM position DESC (like searching downwards).
 *
 * @param {HTMLElement} element
 * @param {string} selector
 * @param {number} [limit=255]
 * @returns {HTMLElement[]}
 */
export default function querySelectorParents(element, selector, limit = 255) {
  const result = [];
  let current = element;
  for (let count = 0; count < limit; count += 1) {
    const match = current.closest(selector);
    if (match) {
      result.push(match);
    }
    if (!match || !match.parentElement) {
      break;
    }
    current = match.parentElement;
  }
  return result.reverse();
}
