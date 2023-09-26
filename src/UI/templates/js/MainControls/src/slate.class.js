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
 ******************************************************************** */

/**
 * @type {string}
 */
const replacementType = 'content';

/**
 * @type {string}
 */
const classForEngaged = 'engaged';

/**
 * @type {string}
 */
const classForDisEngaged = 'disengaged';

/**
 * @param {jQueryDomObject} slate
 * @return {bool}
 */
function isEngaged(slate) {
  return slate.hasClass(classForEngaged);
}

/**
 * @param {jQueryDomObject} slate
 * @return {void}
 */
function engage(slate) {
  slate.removeClass(classForDisEngaged);
  slate.addClass(classForEngaged);
  slate.attr('aria-expanded', 'true');
  slate.attr('aria-hidden', 'false');
}

/**
 * @param {jQueryDomObject} slate
 * @return {void}
 */
function disengage(slate) {
  slate.removeClass(classForEngaged);
  slate.addClass(classForDisEngaged);
  slate.attr('aria-expanded', 'false');
  slate.attr('aria-hidden', 'true');
}

/**
 * @param {jQueryDomObject} slate
 * @return {void}
 */
function toggle(slate) {
  if (isEngaged(slate)) {
    disengage(slate);
  } else {
    engage(slate);
  }
}

export default class Slate {
  /**
   * @type {jQuery}
   */
  #jquery;

  /**
   * @type {function}
   */
  #coreReplaceContent;

  /**
   * @type {MetabarFactory}
   */
  #metabarFactory;

  /**
   * @param {jQuery} jquery
   * @param {function} coreReplaceContent
   * @param {MetabarFactory} metabarFactory
   */
  constructor(jquery, coreReplaceContent, metabarFactory) {
    this.#jquery = jquery;
    this.#coreReplaceContent = coreReplaceContent;
    this.#metabarFactory = metabarFactory;
  }

  /**
   * @param {string} signalType
   * @param {Event} event
   * @param {array} signalData
   * @param {string} id
   * @return {void}
   * @throws {Error} if the signalType is not known
   */
  onSignal(signalType, event, signalData, id) {
    const slate = this.#jquery(`#${id}`);
    const { triggerer } = signalData;
    const isInMetabarMore = triggerer.parents('.il-metabar-more-slate').length > 0;

    if (signalType === 'toggle') {
      this.#onToggleSignal(slate, triggerer, isInMetabarMore);
    } else if (signalType === 'engage') {
      engage(slate);
    } else if (signalType === 'replace') {
      this.#replaceFromSignal(id, signalData);
    } else {
      throw new Error(`No such SignalType: ${signalType}`);
    }
  }

  /**
   * @param {jQueryDomObject} slate
   * @param {jQueryDomObject} triggerer
   * @param {bool} isInMetabarMore
   * @return null|{void}
   */
  #onToggleSignal(slate, triggerer, isInMetabarMore) {
    // special case for metabar-more
    const metabarId = slate.closest('.il-maincontrols-metabar').attr('id');
    const metabar = this.#metabarFactory.get(metabarId);

    if (triggerer.attr('id') === metabar.getMoreButton().attr('id')) {
      if (metabar.getEngagedSlates().length > 0) {
        metabar.disengageAllSlates();
      } else {
        toggle(slate);
      }
      return;
    }

    toggle(slate);
    if (isInMetabarMore) {
      return;
    }
    if (isEngaged(slate)) {
      triggerer.addClass(classForEngaged);
      triggerer.removeClass(classForDisEngaged);
      slate.trigger('in_view');
    } else {
      triggerer.removeClass(classForEngaged);
      triggerer.addClass(classForDisEngaged);
    }
  }

  /**
   * @param {jQueryDomObject} slate
   * @return {void}
   */
  disengage = disengage;

  /**
   * @param {string} id
   * @param {array} signalData
   * @return {void}
   */
  #replaceFromSignal(id, signalData) {
    const { url } = signalData.options;
    this.#coreReplaceContent(id, url, replacementType);
  }
}
