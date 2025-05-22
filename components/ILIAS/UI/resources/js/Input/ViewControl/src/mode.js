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

export default class Mode {
  /**
   * @type {JQueryEventDispatcher}
   */
  #eventDispatcher;

  /**
   * @param {JQueryEventDispatcher} eventDispatcher
   */
  constructor(eventDispatcher) {
    this.#eventDispatcher = eventDispatcher;
  }

  /**
   * @param {HTMLElement} component
   * @param {string} optValue
   * @param {string} containerSubmitSignal
   * @return {void}
   */
  init(component, optValue, containerSubmitSignal) {
    component.addEventListener(
      'click',
      (event) => {
        const btn = event.target;
        btn.parentElement.querySelectorAll('button').forEach(
          (button) => button.classList.remove('engaged'),
        );
        btn.classList.add('engaged');
        btn.closest('.il-viewcontrol')
          .querySelector('.il-viewcontrol-value > input')
          .value = optValue;
        this.#eventDispatcher.dispatch(event.target, containerSubmitSignal);
        return false;
      },
    );
  }
}
