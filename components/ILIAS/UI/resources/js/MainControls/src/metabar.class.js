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
const classForBtnEngaged = 'engaged';

/**
 * @type {string}
 */
const classForEntries = 'il-maincontrols-metabar';

/**
 * @type {string}
 */
const classForSlates = 'il-metabar-slates';

/**
 * @type {string}
 */
const classForMoreBtn = 'il-metabar-more-button';

/**
 * @type {string}
 */
const classForMoreSlate = 'il-metabar-more-slate';

/**
 * @type {string}
 */
const classForSingleSlate = 'il-maincontrols-slate';

/**
 * @type {string}
 */
const classForSlateEngaged = 'engaged';

/**
 * @param {jQueryDomObject} btn
 * @return {void}
 */
function engageButton(btn) {
  btn.addClass(classForBtnEngaged);
  btn.attr('aria-expanded', true);
}

/**
 * @param {jQueryDomObject} btn
 * @return {void}
 */
function disengageButton(btn) {
  btn.removeClass(classForBtnEngaged);
  btn.attr('aria-expanded', false);
}

/**
 * @param {jQueryDomObject} btn
 * @return {void}
 */
function isEngaged(btn) {
  return btn.hasClass(classForBtnEngaged);
}

export default class Metabar {
  /**
   * @type {jQuery}
   */
  #jquery;

  /**
   * @type {string}
   */
  #id;

  /**
   * @type {bool}
   */
  #propagationStopped;

  /**
   * @type {function}
   */
  #pageIsSmallScreen;

  /**
   * @type {counterFactory}
   */
  #counterFactory;

  /**
   * @type {function}
   */
  #disengageMainbar;

  /**
   * @type {function}
   */
  #disengageSlate;

  /**
   * @param {jQuery} jquery
   * @param {string} componentId
   * @param {function} pageIsSmallScreen
   * @param {counterFactory} counterFactory
   * @param {function} disengageMainbar
   * @param {function} disengageSlate
   */
  constructor(
    jquery,
    componentId,
    pageIsSmallScreen,
    counterFactory,
    disengageMainbar,
    disengageSlate,
  ) {
    this.#jquery = jquery;
    this.#id = componentId;
    this.#pageIsSmallScreen = pageIsSmallScreen;
    this.#counterFactory = counterFactory;
    this.#disengageMainbar = disengageMainbar;
    this.#disengageSlate = disengageSlate;
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
      this.#onClickEntry(event, signalData);
      if (this.#pageIsSmallScreen()) {
        this.#disengageMainbar();
      }
      return false;
    });
    this.#jquery(document).on(closeSlatesSignal, () => {
      this.onClickDisengageAll();
      return false;
    });

    // close metabar when user clicks anywhere
    this.#jquery(`.${classForEntries}`).on('click', () => {
      this.#propagationStopped = true;
    });
    this.#jquery('body').on('click', () => {
      if (this.#propagationStopped) {
        this.#propagationStopped = false;
      } else {
        this.onClickDisengageAll();
      }
    });

    // close metabar slate when focus moves out
    this.#jquery(`.${classForSlates} > .${classForSingleSlate}`).on('focusout', (event) => {
      if (!this.#pageIsSmallScreen()) {
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
  #onClickEntry(event, signalData) {
    const btn = signalData.triggerer;
    if (isEngaged(btn)) {
      disengageButton(btn);
    } else {
      this.disengageAllSlates();
      this.disengageAllButtons();
      if (btn.parents(`.${classForMoreSlate}`).length === 0) {
        engageButton(btn);
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
   * @return {void}
   */
  disengageAllButtons() {
    this.#jquery(`#${this.#id}.${classForEntries}`)
      .children('li').children(`.btn.${classForBtnEngaged}`)
      .each(
        (i, btn) => {
          disengageButton(this.#jquery(btn));
        },
      );
  }

  /**
   * @return {void}
   */
  disengageAllSlates() {
    this.getEngagedSlates().each(
      (i, slate) => {
        this.#disengageSlate(this.#jquery(slate));
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
    const search = `#${this.#id} .${classForSingleSlate}.${classForSlateEngaged}`;
    return this.#jquery(search);
  }

  /**
   * decide and init condensed/wide version
   * @return {void}
   */
  init() {
    this.#tagMoreButton();
    this.#tagMoreSlate();

    if (this.#pageIsSmallScreen()) {
      this.#initCondensed();
    } else {
      this.#initWide();
    }

    // unfortunately, this does not work properly via a class
    this.#jquery(`.${classForEntries}`).css('visibility', 'visible');
    this.#jquery(`#${this.#id} .${classForSlates}`).children(`.${classForSingleSlate}`)
      .attr('aria-hidden', true);
  }

  /**
   * @return {void}
   */
  #initCondensed() {
    this.#initMoreSlate();
    this.#getMetabarEntries().hide();
    this.getMoreButton().show();
    this.#collectCounters();
  }

  /**
   * @return {void}
   */
  #initWide() {
    this.getMoreButton().hide();
    this.#getMetabarEntries().show();
  }

  /**
   * @return {void}
   */
  #tagMoreButton() {
    if (this.getMoreButton().length === 0) {
      const entries = this.#jquery(`#${this.#id}.${classForEntries}`).find('.btn, .il-link');
      const more = entries.last();
      this.#jquery(more).addClass(classForMoreBtn);
    }
  }

  /**
   * @return {void}
   */
  #tagMoreSlate() {
    if (this.#getMoreSlate().length === 0) {
      const slates = this.#jquery(`#${this.#id} .${classForSlates}`).children(`.${classForSingleSlate}`);
      const more = slates.last();
      this.#jquery(more).addClass(classForMoreSlate);
    }
  }

  /**
   * @return {void}
   */
  getMoreButton() {
    return this.#jquery(`.${classForMoreBtn}`);
  }

  /**
   * @return {void}
   */
  #getMoreSlate() {
    return this.#jquery(`.${classForMoreSlate}`);
  }

  /**
   * @return {void}
   */
  #getMetabarEntries() {
    return this.#jquery(`#${this.#id}.${classForEntries}`)
      .children('li').children('.btn, .il-link')
      .not(`.${classForMoreBtn}`);
  }

  /**
   * @return {void}
   */
  #initMoreSlate() {
    const content = this.#getMoreSlate().children('.il-maincontrols-slate-content');
    if (content.children().length === 0) {
      this.#getMetabarEntries().clone(true, true)
        .appendTo(content);
    }
  }

  /**
   * @return {void}
   */
  #collectCounters() {
    const moreSlateCounter = this.#counterFactory.getCounterObjectOrNull(this.#getMoreSlate());
    if (moreSlateCounter) {
      this.#counterFactory.getCounterObject(this.getMoreButton())
        .setNoveltyTo(moreSlateCounter.getNoveltyCount())
        .setStatusTo(moreSlateCounter.getStatusCount());
    }
  }
}
