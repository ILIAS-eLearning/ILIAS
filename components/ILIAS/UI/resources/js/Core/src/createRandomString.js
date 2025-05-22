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
 * Returns a random string which is NOT cryptographically secure.
 * This can be used in contexts where Window.crypto is not available.
 *
 * @param {string} prefix
 * @returns {string}
 */
export default function createRandomString(prefix = '') {
  const timestampString = Date.now().toString(36);
  const randomString = Math.random().toString(36).substring(2);
  return `${prefix}${timestampString}_${randomString}`;
}
