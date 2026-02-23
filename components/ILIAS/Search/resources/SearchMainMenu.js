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

il.SearchMainMenu = {
  /**
   * @param {string} acDatasource
   * @param {string} standardSearchAction
   * @param {string} userSearchAction
   */
  init(
    acDatasource,
    standardSearchAction,
    userSearchAction,
  ) {
    this.acDatasource = acDatasource;
    this.standardSearchAction = standardSearchAction;
    this.userSearchAction = userSearchAction;

    // we must bind the blur event before the autocomplete item is added
    this.suppressBlur();
    this.initAutocomplete(`${this.acDatasource}&search_type=4`);
    this.initChange();
  },

  suppressBlur() {
    document.getElementById('main_menu_search').addEventListener(
      'blur',
      (e) => { e.stopImmediatePropagation(); },
    );
  },

  switchFormActionToUserSearch() {
    const form = document.querySelector('#mm_search_form');
    form.action = this.userSearchAction;
  },

  switchFormActionToStandardSearch() {
    const form = document.querySelector('#mm_search_form');
    form.action = this.standardSearchAction;
  },

  /**
   * @param {string} dataSource
   */
  initAutocomplete(dataSource) {
    const autocomplete = document.querySelector('#main_menu_search');
    const target = document.querySelector('#mm_search_menu_ac');

    il.LegacyForm.autocomplete.init(autocomplete, {
      delimiter: null,
      dataSource,
      submitOnSelection: false,
      autocompleteLength: 3,
      submitUrl: null,
      moreText: null,
      appendTo: `#${target.id}`,
    });
  },

  initChange() {
    document.querySelectorAll("#ilMMSearchMenu input[type='radio']").forEach(this.initChangeForRadio, this);
  },

  /**
   * @param {Node} radio
   */
  initChangeForRadio(radio) {
    radio.addEventListener(
      'change',
      () => {
        /* disabled autocomplete */
        const originalInput = document.querySelector('#main_menu_search');
        const autocomplete = originalInput.cloneNode(true); // clone attributes, value, etc.
        originalInput.replaceWith(autocomplete); // replace the original input

        /* disable autocomplete for search at current position */
        const checkedInput = document.querySelector('#mm_search_form').querySelector('input[name=root_id]:checked');
        if (checkedInput.id !== 'ilmmsc') {
          const typeVal = checkedInput.value;
          this.initAutocomplete(`${this.acDatasource}&search_type=${typeVal}`);
        }

        /* change search link according to mode */
        if (checkedInput.id === 'ilmmsu') {
          this.switchFormActionToUserSearch();
        } else {
          this.switchFormActionToStandardSearch();
        }
      }
    );
  },
};
