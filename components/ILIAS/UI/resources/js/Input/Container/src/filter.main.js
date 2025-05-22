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

const filter = function ($) {
  // Init the Filter
  const init = function () {
    $('div.il-filter').each(function () {
      const $filter = this;
      const $form = $($filter).find('.il-standard-form');
      let cnt_hid = 0;
      let cnt_bar = 1;

      // Set form action
      $form.attr('action', window.location.pathname);

      // Filter fields (hide hidden stuff)
      $($filter).find('.il-filter-field-status').each(function () {
        $hidden_input = this;
        if ($($hidden_input).val() === '0') {
          $($('div.il-filter .il-popover-container')[cnt_hid]).hide();
        } else {
          $($('div.il-filter .il-filter-add-list li')[cnt_hid]).hide();
        }
        cnt_hid++;
      });

      // Expand and collapse behaviour
      const button = $filter.querySelector('.il-filter-bar-opener').querySelector('button');
      button.addEventListener('click', () => {
        if (button.getAttribute('aria-expanded') === 'false') {
          button.setAttribute('aria-expanded', true);
          showAndHideElementsForExpand($filter);
          performAjaxCmd($form, 'expand');
        } else {
          button.setAttribute('aria-expanded', false);
          showAndHideElementsForCollapse($filter);
          performAjaxCmd($form, 'collapse');
        }
      });

      // Show labels and values in Filter Bar
      let rendered_active_inputs = false;
      $($filter).find('.il-popover-container').each(function () {
        const input_element = $(this).find(':input:not(:button)');
        const value = input_element.val();
        const label = $(this).find('.leftaddon').text();
        const presented_value = getPresentedValueForInput(input_element, value);

        if (presented_value !== '') {
          $('.il-filter-inputs-active').find(`span[id='${cnt_bar}']`).html(`${label}: ${presented_value}`);
          rendered_active_inputs = true;
        } else {
          // Do not show Input if it has no applied value
          $('.il-filter-inputs-active').find(`span[id='${cnt_bar}']`).hide();
        }
        cnt_bar++;
      });
      // Hide Filter Content Area completely if there are no active inputs
      if (!rendered_active_inputs) {
        $('.il-filter-inputs-active').hide();
      }

      // Popover of Add-Button always at the bottom
      $('.input-group .btn.btn-bulky').attr('data-placement', 'bottom');

      // Hide Add-Button when all Input Fields are shown in the Filter at the beginning
      let empty_list = true;
      const addable_inputs = $($filter).find('.il-filter-add-list').find('li').each(function () {
        if ($(this).css('display') !== 'none' && $(this).css('visibility') !== 'hidden') {
          empty_list = false;
        }
      });
      if (empty_list) {
        $('.btn-bulky').parents('.il-popover-container').hide();
      }

      // Using Return while the focus is on an Input Field imitates a click on the Apply Button
      $form.on('keydown', ':input:not(:button)', function (event) {
        const key = event.which;
        if ((key === 13)) {	// 13 = Return
          const action = $form.attr('data-cmd-apply');
          const url = parse_url(action);
          const url_params = url.query_params;
          createHiddenInputs($(this), url_params);
          $form.attr('action', url.path);
          $form.submit();
          event.preventDefault();
        }
      });

      // Accessibility for complex Input Fields
      $('.il-filter-field').keydown(function (event) {
        const key = event.which;
        // Imitate a click on the Input Field in the Fiter and focus on the Input Element in the Popover
        if ((key === 13) || (key === 32)) {	// 13 = Return, 32 = Space
          $(this).click();
          // Focus on the first checkbox in the Multi Select Input Element in the Popover
          const checkboxes = searchInputElementMultiSelect($(this));
          if (checkboxes.length != 0) {
            checkboxes[0].focus();
          }
          event.preventDefault();
        }
      });
    });
  };

  $(init);

  /**
   * Get label and value for inputs which are shown in Filter Bar
   * @param input_element
   * @param value
   * @param label
   * @returns {string}
   */
  var getPresentedValueForInput = function (input_element, value) {
    let presented_value = '';

    // Handle value for Multi Select Input
    if (input_element.is(':checkbox')) {
      const options = [];
      input_element.each(function () {
        if ($(this).prop('checked')) {
          options.push($(this).parent().find('span').text());
        }
      });
      if (options.length != 0) {
        active_checkboxes = options.join(', ');
        presented_value = active_checkboxes;
      }
    }
    // Handle value for Select Input
    else if (input_element.is('select') && value !== '') {
      const selected_option = input_element.find('option:selected').text();
      presented_value = selected_option;
    }
    // Handle value for all other Inputs
    else if (value !== undefined && value !== '') {
      presented_value = value;
    }

    return presented_value;
  };

  /**
   * Store filter status (hidden or shown) in hidden input fields
   * @param $el
   * @param index
   * @param val
   */
  const storeFilterStatus = function ($el, index, val) {
    $($el.parents('.il-filter').find('.il-filter-field-status').get(index)).val(val);
  };

  /**
   * Create hidden inputs for GET-request and insert them into the DOM
   * @param $el
   * @param url_params
   */
  var createHiddenInputs = function ($el, url_params) {
    for (const param in url_params) {
      const input = `<input type="hidden" name="${param}" value="${url_params[param]}">`;
      $el.parents('form').find('.il-filter-bar').before(input);
    }
  };

  /**
   * Search for the Label of the Input which should be added to the Filter
   * @param $el
   * @param label
   */
  const searchInputLabel = function ($el, label) {
    const input_label = $el.parents('.il-standard-form').find('.input-group-addon.leftaddon').filter(function () {
      return $(this).text() === label;
    });
    return input_label;
  };

  /**
   * Search for the given Input Element
   * @param $el
   */
  const searchInputElement = function ($el) {
    const input_element = $el.parents('.il-popover-container').find(':input');
    return input_element;
  };

  /**
   * Search for the checkboxes in the given Multi Select Input Element (in the Popover)
   * @param $el
   */
  var searchInputElementMultiSelect = function ($el) {
    const checkboxes = $el.parents('.il-popover-container').find('.il-standard-popover-content').children().children()
      .find('input');
    return checkboxes;
  };

  /**
   * Search for the Input Field which should be added to the Add-Button
   * @param $el
   * @param label
   */
  const searchInputField = function ($el, label) {
    const input_field = $el.parents('.il-standard-form').find('.btn-link').filter(function () {
      return $(this).text() === label;
    }).parents('li');
    return input_field;
  };

  /**
   * Search for the Add-Button in the Filter
   * @param $el
   */
  const searchAddButton = function ($el) {
    const add_button = $el.parents('.il-standard-form').find('.btn-bulky').parents('.il-popover-container');
    return add_button;
  };

  /**
   *
   * @param event
   * @param signalData
   */
  const onInputUpdate = function (event, signalData) {
    let outputSpan;
    const $el = $(signalData.triggerer[0]);
    const pop_id = $el.parents('.il-popover').attr('id');
    if (pop_id) {	// we have an already opened popover
      outputSpan = document.querySelector(`span[data-target='${pop_id}']`);
    } else {
      // no popover yet, we are still in the same input group and search for the il-filter-field span
      outputSpan = signalData
        .triggerer[0]
        .closest('.input-group')
        .querySelector('span.il-filter-field');
    }
    if (outputSpan) {
      outputSpan.innerText = signalData.options.string_value;
    }
  };

  /**
   *
   * @param event
   * @param id
   */
  const onRemoveClick = function (event, id) {
    const $el = $(`#${id}`);

    // Store show/hide status in hidden status inputs
    const index = $el.parents('.il-popover-container').index();
    storeFilterStatus($el, index, '0');

    // Remove Input Field from Filter
    $el.parents('.il-popover-container').hide();

    // Clear Input Field (Text, Numeric, Select) when it is removed
    const input_element = searchInputElement($el);
    input_element.val('');

    // Clear Multi Select Input Field when it is removed
    const checkboxes = searchInputElementMultiSelect($el);
    checkboxes.each(function () {
      $(this).prop('checked', false);
    });
    checkboxes.parents('.il-popover-container').find('.il-filter-field').html('');

    // Add Input Field to Add-Button
    const label = $el.parents('.input-group').find('.input-group-addon.leftaddon').html();
    const input_field = searchInputField($el, label);
    input_field.show();

    // Show Add-Button when not all Input Fields are shown in the Filter
    const add_button = searchAddButton($el);
    const addable_inputs = $el.parents('.il-standard-form').find('.il-popover-container:hidden').length;
    if (addable_inputs != 0) {
      add_button.show();
    }
  };

  /**
   *
   * @param event
   * @param id
   */
  const onAddClick = function (event, id) {
    const $el = $(`#${id}`);
    const label = $el.text();

    // Remove Input Field from Add-Button
    $el.parent().hide();

    // Store show/hide status in hidden status inputs
    const index = $el.parent().index();
    storeFilterStatus($el, index, '1');

    // Add Input Field to Filter
    const input_label = searchInputLabel($el, label);
    input_label.parents('.il-popover-container').show();

    // Focus on the Input Element (Text, Numeric, Select)
    const input_element = searchInputElement(input_label);
    input_element.focus();

    // Imitate a click on the Input Field in the Fiter (for complex Input Elements which use Popover)
    input_label.parent().find('.il-filter-field').click();

    // Focus on the first checkbox in the Multi Select Input Element in the Popover
    const checkboxes = searchInputElementMultiSelect(input_label);
    if (checkboxes.length != 0) {
      checkboxes[0].focus();
    }

    // Hide Add-Button when all Input Fields are shown in the Filter
    const add_button = searchAddButton($el);
    const addable_inputs = $el.parents('.il-filter').find('.il-filter-add-list').find('li:visible').length;
    if (addable_inputs === 0) {
      add_button.hide();
    }

    // Hide the Popover of the Add-Button when adding Input Field
    add_button.find('.il-popover').hide();
  };

  /**
   * @param filter
   */
  var showAndHideElementsForCollapse = function (filter) {
    filter.querySelector('[data-collapse-glyph-visibility]').dataset.collapseGlyphVisibility = '0';
    filter.querySelector('[data-expand-glyph-visibility]').dataset.expandGlyphVisibility = '1';
    filter.querySelector('.il-filter-inputs-active').dataset.activeInputsExpanded = '1';
    filter.querySelector('.il-filter-input-section').dataset.sectionInputsExpanded = '0';
  };

  /**
   * @param filter
   */
  var showAndHideElementsForExpand = function (filter) {
    filter.querySelector('[data-expand-glyph-visibility]').dataset.expandGlyphVisibility = '0';
    filter.querySelector('[data-collapse-glyph-visibility]').dataset.collapseGlyphVisibility = '1';
    filter.querySelector('.il-filter-inputs-active').dataset.activeInputsExpanded = '0';
    filter.querySelector('.il-filter-input-section').dataset.sectionInputsExpanded = '1';
  };

  /**
   * @param form
   * @param cmd
   */
  var performAjaxCmd = function (form, cmd) {
    // Get the URL for GET-request
    const action = form.attr(`data-cmd-${cmd}`);
    // Add the inputs to the URL (for correct rendering within the session) and perform the request as an Ajax-request
    const formData = form.serialize();
    $.ajax({
      type: 'GET',
      url: `${action}&${formData}`,
    });
  };

  /**
   *
   * @param event
   * @param id
   * @param cmd
   */
  const onCmd = function (event, id, cmd) {
    // Get the URL for GET-request, put the components of the query string into hidden inputs and submit the filter
    const $el = $(`#${id}`);
    const action = $el.parents('form').attr(`data-cmd-${cmd}`);
    const url = parse_url(action);
    const url_params = url.query_params;
    createHiddenInputs($el, url_params);
    $el.parents('form').attr('action', url.path);
    $el.parents('form').submit();
  };

  /**
   * parse url, based on https://github.com/hirak/phpjs/blob/master/functions/url/parse_url.js
   * @param str
   * @returns {{}}
   */
  function parse_url(str) {
    let query;
    const key = [
      'source',
      'scheme',
      'authority',
      'userInfo',
      'user',
      'pass',
      'host',
      'port',
      'relative',
      'path',
      'directory',
      'file',
      'query',
      'fragment',
    ];
    const reg_ex = /^(?:([^:\/?#]+):)?(?:\/\/((?:(([^:@\/]*):?([^:@\/]*))?@)?([^:\/?#]*)(?::(\d*))?))?((((?:[^?#\/]*\/)*)([^?#]*))(?:\?([^#]*))?(?:#(.*))?)/;

    const m = reg_ex.exec(str);
    const uri = {};
    let i = 14;

    while (i--) {
      if (m[i]) {
        uri[key[i]] = m[i];
      }
    }

    const parser = /(?:^|&)([^&=]*)=?([^&]*)/g;
    uri.query_params = {};
    query = uri[key[12]] || '';
    query.replace(parser, ($0, $1, $2) => {
      if ($1) {
        uri.query_params[$1] = $2;
      }
    });

    delete uri.source;
    return uri;
  }

  /**
   * Public interface
   */
  return {
    onInputUpdate,
    onRemoveClick,
    onAddClick,
    onCmd,
  };
};

export default filter;
