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

/* eslint-env jquery */
il.SearchMainMenu = {
  acDatasource: 'ilias.php?baseClass=ilSearchControllerGUI&cmd=autoComplete',

  init() {
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

  /**
   * @param {string} dataSource
   */
  initAutocomplete(dataSource) {
    const autocomplete = document.querySelector('#main_menu_search');
    const target = document.getElementById('mm_search_menu_ac');

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
    $("#ilMMSearchMenu input[type='radio']").change(() => {
      /* disabled autocomplete */
      const originalInput = document.querySelector('#main_menu_search');
      const autocomplete = originalInput.cloneNode(true); // clone attributes, value, etc.
      originalInput.replaceWith(autocomplete); // replace the original inpu

      /* disable autocomplete for search at current position */
      const checkedInput = $('input[name=root_id]:checked', '#mm_search_form');
      if (checkedInput[0].id === 'ilmmsc') {
        return;
      }

      const typeVal = checkedInput.val();

      this.initAutocomplete(`${this.acDatasource}&search_type=${typeVal}`);
    });
  },
};
