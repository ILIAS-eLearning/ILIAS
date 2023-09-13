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

export default class Metabar {
  /**
   * @type {jQuery}
   */
  #jquery;

  /**
   * @type {string}
   */
  id;

  /**
   * @type {string}
   */
  clsBtnEngaged = 'engaged';

  /**
   * @type {string}
   */
  clsEntries = 'il-maincontrols-metabar';

  /**
   * @type {string}
   */
  clsSlates = 'il-metabar-slates';

  /**
   * @type {string}
   */
  clsMoreBtn = 'il-metabar-more-button';

  /**
   * @type {string}
   */
  clsMoreSlate = 'il-metabar-more-slate';

  /**
   * @type {string}
   */
  clsSingleSlate = il.UI.maincontrols.slate.clsSingleSlate;

  /**
   * @type {string}
   */
  clsSlateEngaged = il.UI.maincontrols.slate.clsEngaged;

  /**
   * @type {bool}
   */
  propagationStopped;

  /**
   * @param {jQuery} jquery
   * @param {string} componentId
   */
  constructor(jquery, componentId) {
    this.#jquery = jquery;
    this.id = componentId;
  }

  /**
   * @param {string} entrySignal
   * @param {string} closeSlatesSignal
   */
  registerSignals(
    entrySignal,
    closeSlatesSignal,
  ) {
    this.#jquery(document).on(entrySignal, (event, signalData) => {
      this.onClickEntry(event, signalData);
      if (il.UI.page.isSmallScreen() && il.UI.maincontrols.mainbar) {
        il.UI.maincontrols.mainbar.disengageAll();
      }
      return false;
    });
    this.#jquery(document).on(closeSlatesSignal, () => {
      this.onClickDisengageAll();
      return false;
    });

    // close metabar when user clicks anywhere
    this.#jquery(`.${this.clsEntries}`).on('click', () => {
      this.propagationStopped = true;
    });
    this.#jquery('body').on('click', () => {
      if (this.propagationStopped) {
        this.propagationStopped = false;
      } else {
        this.onClickDisengageAll();
      }
    });

    // close metabar slate when focus moves out
    this.#jquery(`.${this.clsSlates} > .${this.clsSingleSlate}`).on('focusout', (event) => {
      if (!il.UI.page.isSmallScreen()) {
        const nextFocusTarget = event.relatedTarget;
        const currentSlate = event.currentTarget;
        if (!this.#jquery.contains(currentSlate, nextFocusTarget)) {
          this.onClickDisengageAll();
        }
      }
    });
  }

  /**
   * @param {MouseEvent} event
   * @param {array} signalData
   */
  onClickEntry(event, signalData) {
    const btn = signalData.triggerer;
    if (this.isEngaged(btn)) {
      this.disengageButton(btn);
    } else {
      this.disengageAllSlates();
      this.disengageAllButtons();
      if (btn.parents(`.${this.clsMoreSlate}`).length === 0) {
        this.engageButton(btn);
      }
    }
  }

  /**
   * @return {void}
   */
  onClickDisengageAll() {
    this.disengageAllButtons();
    this.disengageAllSlates();
  }

  /**
   * @param {jQueryDomObject} btn
   * @return {void}
   */
  engageButton(btn) {
    btn.addClass(this.clsBtnEngaged);
    btn.attr('aria-expanded', true);
  }

  /**
   * @param {jQueryDomObject} btn
   * @return {void}
   */
  disengageButton(btn) {
    btn.removeClass(this.clsBtnEngaged);
    btn.attr('aria-expanded', false);
  }

  /**
   * @param {jQueryDomObject} btn
   * @return {void}
   */
  isEngaged(btn) {
    return btn.hasClass(this.clsBtnEngaged);
  }

  /**
   * @return {void}
   */
  disengageAllButtons() {
    this.#jquery(`#${this.id}.${this.clsEntries}`)
      .children('li').children(`.btn.${this.clsBtnEngaged}`)
      .each(
        (i, btn) => {
          this.disengageButton(this.#jquery(btn));
        },
      );
  }

  /**
   * @return {void}
   */
  disengageAllSlates() {
    this.getEngagedSlates().each(
      (i, slate) => {
        il.UI.maincontrols.slate.disengage(this.#jquery(slate));
      },
    );
  }

  /**
   * @return {void}
   */
  disengageAll() {
    this.disengageAllSlates();
    this.disengageAllButtons();
  }

  getEngagedSlates() {
    const search = `#${this.id} .${this.clsSingleSlate}.${this.clsSlateEngaged}`;
    return this.#jquery(search);
  }

  /**
   * decide and init condensed/wide version
   * @return {void}
   */
  init() {
    this.tagMoreButton();
    this.tagMoreSlate();

    if (il.UI.page.isSmallScreen()) {
      this.initCondensed();
    } else {
      this.initWide();
    }

    // unfortunately, this does not work properly via a class
    this.#jquery(`.${this.clsEntries}`).css('visibility', 'visible');
    this.#jquery(`#${this.id} .${this.clsSlates}`).children(`.${this.clsSingleSlate}`)
      .attr('aria-hidden', true);
  }

  /**
   * @return {void}
   */
  initCondensed() {
    this.initMoreSlate();
    this.getMetabarEntries().hide();
    this.getMoreButton().show();
    this.collectCounters();
  }

  /**
   * @return {void}
   */
  initWide() {
    this.getMoreButton().hide();
    this.getMetabarEntries().show();
  }

  /**
   * @return {void}
   */
  tagMoreButton() {
    if (this.getMoreButton().length === 0) {
      const entries = this.#jquery(`#${this.id}.${this.clsEntries}`).find('.btn, .il-link');
      const more = entries.last();
      this.#jquery(more).addClass(this.clsMoreBtn);
    }
  }

  /**
   * @return {void}
   */
  tagMoreSlate() {
    if (this.getMoreSlate().length === 0) {
      const slates = this.#jquery(`#${this.id} .${this.clsSlates}`).children(`.${this.clsSingleSlate}`);
      const more = slates.last();
      this.#jquery(more).addClass(this.clsMoreSlate);
    }
  }

  /**
   * @return {void}
   */
  getMoreButton() {
    return this.#jquery(`.${this.clsMoreBtn}`);
  }

  /**
   * @return {void}
   */
  getMoreSlate() {
    return this.#jquery(`.${this.clsMoreSlate}`);
  }

  /**
   * @return {void}
   */
  getMetabarEntries() {
    return this.#jquery(`#${this.id}.${this.clsEntries}`)
      .children('li').children('.btn, .il-link')
      .not(`.${this.clsMoreBtn}`);
  }

  /**
   * @return {void}
   */
  initMoreSlate() {
    const content = this.getMoreSlate().children('.il-maincontrols-slate-content');
    if (content.children().length === 0) {
      this.getMetabarEntries().clone(true, true)
        .appendTo(content);
    }
  }

  /**
   * @return {void}
   */
  getAllSlates() {
    return this.#jquery(`#${this.id} .${this.clsSingleSlate}`)
      .not(`.${this.clsMoreSlate}`);
  }

  /**
   * @return {void}
   */
  collectCounters() {
    const moreSlateCounter = il.UI.counter.getCounterObjectOrNull(this.getMoreSlate());
    if (moreSlateCounter) {
      il.UI.counter.getCounterObject(this.getMoreButton())
        .setNoveltyTo(moreSlateCounter.getNoveltyCount())
        .setStatusTo(moreSlateCounter.getStatusCount());
    }
  }
}
