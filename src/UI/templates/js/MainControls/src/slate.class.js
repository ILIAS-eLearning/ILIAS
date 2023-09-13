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

export default class Slate {
  /**
   * @type {jQuery}
   */
  #jquery;

  /**
   * @type {string}
   */
  clsEngaged = 'engaged';

  /**
   * @type {string}
   */
  clsDisEngaged = 'disengaged';

  /**
   * @type {string}
   */
  clsSingleSlate = 'il-maincontrols-slate';

  /**
   * @param {jQuery} jquery
   */
  constructor(jquery) {
    this.#jquery = jquery;
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
      this.onToggleSignal(slate, triggerer, isInMetabarMore);
    } else if (signalType === 'engage') {
      this.engage(slate);
    } else if (signalType === 'replace') {
      this.replaceFromSignal(id, signalData);
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
  onToggleSignal(slate, triggerer, isInMetabarMore) {
    // special case for metabar-more
    const metabarId = slate.closest('.il-maincontrols-metabar').attr('id');
    const metabar = il.UI.maincontrols.metabar.get(metabarId);
    if (triggerer.attr('id') === metabar.getMoreButton().attr('id')) {
      if (metabar.getEngagedSlates().length > 0) {
        metabar.disengageAllSlates();
      } else {
        this.toggle(slate);
      }
      return;
    }

    this.toggle(slate);
    if (isInMetabarMore) {
      return;
    }
    if (this.isEngaged(slate)) {
      triggerer.addClass(this.clsEngaged);
      triggerer.removeClass(this.clsDisEngaged);
      slate.trigger('in_view');
    } else {
      triggerer.removeClass(this.clsEngaged);
      triggerer.addClass(this.clsDisEngaged);
    }
  }

  /**
   * @param {jQueryDomObject} slate
   * @return {void}
   */
  toggle(slate) {
    if (this.isEngaged(slate)) {
      this.disengage(slate);
    } else {
      this.engage(slate);
    }
  }

  /**
   * @param {jQueryDomObject} slate
   * @return {bool}
   */
  isEngaged(slate) {
    return slate.hasClass(this.clsEngaged);
  }

  /**
   * @param {jQueryDomObject} slate
   * @return {void}
   */
  engage(slate) {
    slate.removeClass(this.clsDisEngaged);
    slate.addClass(this.clsEngaged);
    slate.attr('aria-expanded', 'true');
    slate.attr('aria-hidden', 'false');
  }

  /**
   * @param {jQueryDomObject} slate
   * @return {void}
   */
  disengage(slate) {
    slate.removeClass(this.clsEngaged);
    slate.addClass(this.clsDisEngaged);
    slate.attr('aria-expanded', 'false');
    slate.attr('aria-hidden', 'true');
  }

  /**
   * @param {string} id
   * @param {array} signalData
   * @return {void}
   */
  replaceFromSignal(id, signalData) {
    const { url } = signalData.options;
    il.UI.core.replaceContent(this.id, url, 'content');
  }
}
