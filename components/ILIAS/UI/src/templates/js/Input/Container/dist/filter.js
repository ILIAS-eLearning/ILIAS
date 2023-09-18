var filter = function($) {

  //Init the Filter
  var init = function () {
    $("div.il-filter").each(function () {
      var $filter = this;
      var cnt_hid = 0;
      var cnt_bar = 1;

      //Set form action
      $($filter).find(".il-standard-form").attr('action', window.location.pathname);

      //Filter fields (hide hidden stuff)
      $($filter).find(".il-filter-field-status").each(function () {
        $hidden_input = this;
        if ($($hidden_input).val() === "0") {
          $($("div.il-filter .il-popover-container")[cnt_hid]).hide();
        } else {
          $($("div.il-filter .il-filter-add-list li")[cnt_hid]).hide();
        }
        cnt_hid++;
      });

      $(".il-filter-bar-opener").find("button:first").hide();
      $(".il-filter-bar-opener button").click(function() {
        $(".il-filter-bar-opener button").toggle();
        if ($(this).attr("aria-expanded") == "false") {
          $(this).attr("aria-expanded", "true");
        } else {
          $(this).attr("aria-expanded", "false");
        }
      });

      //Show labels and values in Filter Bar
      var rendered_active_inputs = false;
      $($filter).find(".il-popover-container").each(function () {
        var input_element = $(this).find(":input:not(:button)");
        var value = input_element.val();
        var label = $(this).find(".leftaddon").text();
        var presented_value = getPresentedValueForInput(input_element, value);

        if (presented_value !== "") {
          $(".il-filter-inputs-active").find("span[id='" + (cnt_bar) + "']").html(label + ": " + presented_value);
          rendered_active_inputs = true;
        }
        else {
          //Do not show Input if it has no applied value
          $(".il-filter-inputs-active").find("span[id='" + (cnt_bar) + "']").hide();
        }
        cnt_bar++;
      });
      //Hide Filter Content Area completely if there are no active inputs
      if (!rendered_active_inputs) {
        $(".il-filter-inputs-active").hide();
      }

      //Popover of Add-Button always at the bottom
      $('.input-group .btn.btn-bulky').attr('data-placement', 'bottom');

      //Hide Add-Button when all Input Fields are shown in the Filter at the beginning
      var addable_inputs = $(".il-popover-container:hidden").length;
      if (addable_inputs === 0) {
        $(".btn-bulky").parents(".il-popover-container").hide();
      }

      //Accessibility for complex Input Fields
      $(".il-filter-field").keydown(function (event) {
        var key = event.which;
        //Imitate a click on the Input Field in the Fiter and focus on the Input Element in the Popover
        if ((key === 13) || (key === 32)) {	// 13 = Return, 32 = Space
          $(this).click();
          //Focus on the first checkbox in the Multi Select Input Element in the Popover
          var checkboxes = searchInputElementMultiSelect($(this));
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
    var presented_value = "";

    //Handle value for Multi Select Input
    if (input_element.is(":checkbox")) {
      var options = [];
      input_element.each(function() {
        if ($(this).prop('checked')) {
          options.push($(this).parent().find('span').text());
        }
      });
      if (options.length != 0) {
        active_checkboxes = options.join(', ');
        presented_value = active_checkboxes;
      }
    }
    //Handle value for Select Input
    else if (input_element.is("select") && value !== "") {
      var selected_option = input_element.find("option:selected").text();
      presented_value = selected_option;
    }
    //Handle value for all other Inputs
    else if (value !== undefined && value !== "") {
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
  var storeFilterStatus = function ($el, index, val) {
    $($el.parents(".il-filter").find(".il-filter-field-status").get(index)).val(val);
  };

  /**
   * Create hidden inputs for GET-request and insert them into the DOM
   * @param $el
   * @param url_params
   */
  var createHiddenInputs = function ($el, url_params) {
    for (var param in url_params) {
      var input = "<input type=\"hidden\" name=\"" + param + "\" value=\"" + url_params[param] + "\">";
      $el.parents('form').find('.il-filter-bar').before(input);
    }
  };

  /**
   * Search for the Label of the Input which should be added to the Filter
   * @param $el
   * @param label
   */
  var searchInputLabel = function ($el, label) {
    var input_label = $el.parents(".il-standard-form").find(".input-group-addon.leftaddon").filter(function () {
      return $(this).text() === label;
    });
    return input_label;
  };

  /**
   * Search for the given Input Element
   * @param $el
   */
  var searchInputElement = function ($el) {
    var input_element = $el.parents(".il-popover-container").find(":input");
    return input_element;
  };

  /**
   * Search for the checkboxes in the given Multi Select Input Element (in the Popover)
   * @param $el
   */
  var searchInputElementMultiSelect = function ($el) {
    var checkboxes = $el.parents(".il-popover-container").find(".il-standard-popover-content").children().children().find("input");
    return checkboxes;
  };

  /**
   * Search for the Input Field which should be added to the Add-Button
   * @param $el
   * @param label
   */
  var searchInputField = function ($el, label) {
    var input_field = $el.parents(".il-standard-form").find(".btn-link").filter(function () {
      return $(this).text() === label;
    }).parents("li");
    return input_field;
  };

  /**
   * Search for the Add-Button in the Filter
   * @param $el
   */
  var searchAddButton = function ($el) {
    var add_button = $el.parents(".il-standard-form").find(".btn-bulky").parents(".il-popover-container");
    return add_button;
  };

  /**
   *
   * @param event
   * @param signalData
   */
  var onInputUpdate = function (event, signalData) {
    let outputSpan;
    var $el = $(signalData.triggerer[0]);
    var pop_id = $el.parents(".il-popover").attr("id");
    if (pop_id) {	// we have an already opened popover
      outputSpan = document.querySelector("span[data-target='" + pop_id + "']");
    } else {
      // no popover yet, we are still in the same input group and search for the il-filter-field span
      outputSpan = signalData
        .triggerer[0]
      .closest(".input-group")
      .querySelector("span.il-filter-field");
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
  var onRemoveClick = function (event, id) {
    var $el = $("#" + id);

    // Store show/hide status in hidden status inputs
    var index = $el.parents(".il-popover-container").index();
    storeFilterStatus($el, index, "0");

    //Remove Input Field from Filter
    $el.parents(".il-popover-container").hide();

    //Clear Input Field (Text, Numeric, Select) when it is removed
    var input_element = searchInputElement($el);
    input_element.val("");

    //Clear Multi Select Input Field when it is removed
    var checkboxes = searchInputElementMultiSelect($el);
    checkboxes.each(function () {
      $(this).prop("checked", false);
    });
    checkboxes.parents(".il-popover-container").find(".il-filter-field").html("");

    //Add Input Field to Add-Button
    var label = $el.parents(".input-group").find(".input-group-addon.leftaddon").html();
    var input_field = searchInputField($el, label);
    input_field.show();

    //Show Add-Button when not all Input Fields are shown in the Filter
    var add_button = searchAddButton($el);
    var addable_inputs = $el.parents(".il-standard-form").find(".il-popover-container:hidden").length;
    if (addable_inputs != 0) {
      add_button.show();
    }
  };

  /**
   *
   * @param event
   * @param id
   */
  var onAddClick = function (event, id) {
    var $el = $("#" + id);
    var label = $el.text();

    //Remove Input Field from Add-Button
    $el.parent().hide();

    // Store show/hide status in hidden status inputs
    var index = $el.parent().index();
    storeFilterStatus($el, index, "1");

    // Add Input Field to Filter
    var input_label = searchInputLabel($el, label);
    input_label.parents(".il-popover-container").show();

    //Focus on the Input Element (Text, Numeric, Select)
    var input_element = searchInputElement(input_label);
    input_element.focus();

    //Imitate a click on the Input Field in the Fiter (for complex Input Elements which use Popover)
    input_label.parent().find(".il-filter-field").click();

    //Focus on the first checkbox in the Multi Select Input Element in the Popover
    var checkboxes = searchInputElementMultiSelect(input_label);
    if (checkboxes.length != 0) {
      checkboxes[0].focus();
    }

    //Hide Add-Button when all Input Fields are shown in the Filter
    var add_button = searchAddButton($el);
    var addable_inputs = $el.parents(".il-filter").find(".il-filter-add-list").find("li:visible").length;
    if (addable_inputs === 0) {
      add_button.hide();
    }

    //Hide the Popover of the Add-Button when adding Input Field
    add_button.find(".il-popover").hide();
  };

  /**
   *
   * @param event
   * @param id
   * @param cmd
   */
  var onCmd = function (event, id, cmd) {
    //Get the URL for GET-request, put the components of the query string into hidden inputs and submit the filter
    var $el = $("#" + id);
    var action = $el.parents('form').attr("data-cmd-" + cmd);
    var url = parse_url(action);
    var url_params = url['query_params'];
    createHiddenInputs($el, url_params);
    $el.parents('form').attr('action', url['path']);
    $el.parents('form').submit();
  };

  /**
   *
   * @param event
   * @param id
   * @param cmd
   */
  var onAjaxCmd = function (event, id, cmd) {
    //Get the URL for GET-request
    var $el = $("#" + id);
    var action = $el.parents('form').attr("data-cmd-" + cmd);
    //Add the inputs to the URL (for correct rendering within the session) and perform the request as an Ajax-request
    var formData = $el.parents('form').serialize();
    $.ajax({
      type: 'GET',
      url: action + "&" + formData,
    })
  };

  /**
   * parse url, based on https://github.com/hirak/phpjs/blob/master/functions/url/parse_url.js
   * @param str
   * @returns {{}}
   */
  function parse_url(str) {
    var query;
    var key = [
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
      'fragment'
    ];
    var reg_ex = /^(?:([^:\/?#]+):)?(?:\/\/((?:(([^:@\/]*):?([^:@\/]*))?@)?([^:\/?#]*)(?::(\d*))?))?((((?:[^?#\/]*\/)*)([^?#]*))(?:\?([^#]*))?(?:#(.*))?)/;

    var m = reg_ex.exec(str);
    var uri = {};
    var i = 14;

    while (i--) {
      if (m[i]) {
        uri[key[i]] = m[i];
      }
    }

    var parser = /(?:^|&)([^&=]*)=?([^&]*)/g;
    uri['query_params'] = {};
    query = uri[key[12]] || '';
    query.replace(parser, function ($0, $1, $2) {
      if ($1) {
        uri['query_params'][$1] = $2;
      }
    });

    delete uri.source;
    return uri;
  }

  /**
   * Public interface
   */
  return {
    onInputUpdate: onInputUpdate,
    onRemoveClick: onRemoveClick,
    onAddClick: onAddClick,
    onCmd: onCmd,
    onAjaxCmd: onAjaxCmd
  };

};

il = il || {};
il.UI = il.UI || {};

il.UI.filter = filter($);
