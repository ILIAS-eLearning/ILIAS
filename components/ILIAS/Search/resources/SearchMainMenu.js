/* eslint-env jquery */
il.SearchMainMenu = {
  acDatasource: 'ilias.php?baseClass=ilSearchControllerGUI&cmd=autoComplete',

  init() {
    // we must bind the blur event before the autocomplete item is added
    this.suppressBlur();
    this.initAutocomplete();
    this.initChange();
  },

  suppressBlur() {
    document.getElementById('main_menu_search').addEventListener(
      'blur',
      (e) => { e.stopImmediatePropagation(); },
    );
  },

  initAutocomplete() {
    $('#main_menu_search').autocomplete({
      source: `${this.acDatasource}&search_type=4`,
      appendTo: '#mm_search_menu_ac',
      open() {
        $('.ui-autocomplete').position({
          my: 'left top',
          at: 'left top',
          of: $('#mm_search_menu_ac'),
        });
      },
      minLength: 3,
    });
  },

  initChange() {
    $("#ilMMSearchMenu input[type='radio']").change(() => {
      /* close current search */
      $('#main_menu_search').autocomplete('close');
      $('#main_menu_search').autocomplete('enable');

      /* append search type */
      const checkedInput = $('input[name=root_id]:checked', '#mm_search_form');
      const typeVal = checkedInput.val();

      /* disable autocomplete for search at current position */
      if (checkedInput[0].id === 'ilmmsc') {
        $('#main_menu_search').autocomplete('disable');
        return;
      }

      $('#main_menu_search').autocomplete(
        'option',
        {
          source: `${this.acDatasource}&search_type=${typeVal}`,
        },
      );

      /* start new search */
      $('#main_menu_search').autocomplete('search');
    });
  },
};
