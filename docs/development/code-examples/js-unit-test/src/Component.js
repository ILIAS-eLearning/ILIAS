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

import MathMod from './MathMod';

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
export default class Component {
  /** @type {MathMod} */
  #mathMod;

  /**
   * @param {MathMod} mathMod
   */
  constructor(mathMod = null) {
    this.#mathMod = mathMod ?? MathMod;
  }

  /**
   * @param {number} a
   * @param {number} b
   * @return {number}
   */
  calculate(a, b) {
    return this.#mathMod.sum(a, b);
  }
}
