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

export default class Sortation {
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
   * @param {string} internalSignal
   * @param {string} containerSubmitSignal
   * @return {void}
   */
  init(component, internalSignal, containerSubmitSignal) {
    this.#eventDispatcher.register(
      component.ownerDocument,
      internalSignal,
      (event, signalData) => {
        let container = event.target.closest('.il-viewcontrol-fieldselection');
        if (signalData.options.parent_container) {
          container = component.ownerDocument.querySelector(
            `#${signalData.options.parent_container
            } .il-viewcontrol-sortation`,
          );
        } else {
          container = event.target.closest('.il-viewcontrol-sortation');
        }
        const inputs = container.querySelectorAll('.il-viewcontrol-value > input');
        const val = signalData.options.value.split(':');
        [inputs[0].value, inputs[1].value] = val;

        this.#eventDispatcher.dispatch(event.target, containerSubmitSignal);
        return false;
      },
    );
  }
}
