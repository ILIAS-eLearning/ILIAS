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
(function ($, il) {
  'use strict';

  function _interopDefaultLegacy (e) { return e && typeof e === 'object' && 'default' in e ? e : { 'default': e }; }

  var $__default = /*#__PURE__*/_interopDefaultLegacy($);
  var il__default = /*#__PURE__*/_interopDefaultLegacy(il);

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
   * @author Thibeau Fuhrer <thibeau@sr.solutions>
   */
  class Component1 {
    #componentName = 'Component1';

    public1() {
      // eslint-disable-next-line no-console
      console.log(`${this.#componentName}.public1()`);
    }
  }

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
   * @author Thibeau Fuhrer <thibeau@sr.solutions>
   */
  class Component2 {
    /** @type {string} */
    #componentName = 'Component2';

    /** @type {jQuery} */
    #jquery;

    /**
     * @param {jQuery} jquery
     */
    constructor(jquery) {
      this.#jquery = jquery;
    }

    public1() {
      // eslint-disable-next-line no-console
      console.log(`${this.#componentName}.public1()`);
    }

    public2() {
      // eslint-disable-next-line no-console
      console.log(`${this.#componentName}.public2() using jQuery ${this.#jquery().jquery}`);
    }
  }

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

  il__default["default"].Component1 = new Component1();
  il__default["default"].Component2 = new Component2($__default["default"]);

})($, il);
