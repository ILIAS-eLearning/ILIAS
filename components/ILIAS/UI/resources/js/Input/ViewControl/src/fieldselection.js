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

export default class FieldSelection {
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
   * @param {string} componentName
   * @return {void}
   */
  init(component, internalSignal, containerSubmitSignal, componentName) {
    this.#eventDispatcher.register(
      component.ownerDocument,
      internalSignal,
      (event) => {
        const container = event.target.closest('.il-viewcontrol-fieldselection');
        const checkbox = container.querySelectorAll('input[type=checkbox]');
        const valueContainer = container.querySelector('.il-viewcontrol-value');
        const value = Object.values(checkbox).map((o) => (o.checked ? o.value : ''));

        valueContainer.innerHTML = '';
        value.forEach(
          (v) => {
            const element = component.ownerDocument.createElement('input');
            element.type = 'hidden';
            element.name = `${componentName}[]`;
            element.value = v;
            valueContainer.appendChild(element);
          },
        );

        this.#eventDispatcher.dispatch(event.target, containerSubmitSignal);
        return false;
      },
    );
  }
}
