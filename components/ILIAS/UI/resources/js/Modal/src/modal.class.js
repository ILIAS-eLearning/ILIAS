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
  #defaultShowOptions = {
    ajaxRenderUrl: '',
  };

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
   * @param {string} id
   * @param {array} conf
   * @param {array} signalData
   */
  showModal(id, conf, signalData) {
    if (this.#triggeredSignalsStorage[signalData.id] === true) {
      return;
    }
    this.#triggeredSignalsStorage[signalData.id] = true;
    const options = this.#jquery.extend(this.#defaultShowOptions, conf);

    if (options.ajaxRenderUrl) {
      const container = document.getElementById(id);
      this.#jquery(container).load(options.ajaxRenderUrl, () => {
        document.querySelector(`#${id} > dialog`).showModal();
        this.#triggeredSignalsStorage[signalData.id] = false;
      });
    } else {
      document.getElementById(id).showModal();
      this.#triggeredSignalsStorage[signalData.id] = false;
    }
    this.#initializedModalboxes[signalData.id] = id;
  }

  /**
   * @param {string} id
   */
  closeModal(id) {
    document.getElementById(id).close();
  }

  /**
   * Replace the content of the modalbox shown by the given showSignal
   * with the data returned by the URL set in the signal options.
   *
   * @param {string} id
   * @param {array} signalData
   */
  replaceFromSignal(id, signalData) {
    const { url } = signalData.options;
    il.UI.core.replaceContent(id, url, 'component');
  }
}
