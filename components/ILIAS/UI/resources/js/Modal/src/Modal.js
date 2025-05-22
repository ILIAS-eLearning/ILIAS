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

export default class Modal {
  /**
    * @type {jQuery}
    */
  #jquery;

  /**
   * @type {array}
   */
  #triggeredSignalsStorage = [];

  /**
   * @type {array}
   */
  #initializedModalboxes = {};

  /**
   * @param {jQuery} jquery
   */
  constructor(jquery) {
    this.#jquery = jquery;
  }

  /**
   * @param {HTMLDialogElement} component
   * @param {string} closeSignal
   * @param {array} options
   * @param {array} signalData
   */
  showModal(component, options, signalData, closeSignal) {
    if (!component
        || (component?.tagName !== 'DIALOG' && !options?.ajaxRenderUrl)
    ) {
      throw new Error('component is not a dialog (or triggers one).');
    }

    if (closeSignal) {
      this.#jquery(component.ownerDocument).on(
        closeSignal,
        () => component.close(),
      );
    }

    if (this.#triggeredSignalsStorage[signalData.id] === true) {
      return;
    }
    this.#triggeredSignalsStorage[signalData.id] = true;

    if (options.ajaxRenderUrl) {
      this.#jquery(component).load(options.ajaxRenderUrl, () => {
        const dialog = component.querySelector('dialog');
        if (!dialog) {
          throw new Error('url did not return a dialog');
        }
        dialog.showModal();
        il.UI.lightbox.maybeInitCarousel(component);
        this.#triggeredSignalsStorage[signalData.id] = false;
      });
    } else {
      component.showModal();
      il.UI.lightbox.maybeInitCarousel(component);
      this.#triggeredSignalsStorage[signalData.id] = false;
    }
    this.#initializedModalboxes[signalData.id] = component.id;

  }
}
