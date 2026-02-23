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

/* eslint-disable */
var ilMultiFormValues = {

  //
  autocompleteConfigs: {},

  /**
	 * Bind click events and handle preset values
	 */
  init() {
    // add click event to +-icons
    $('button[id*="ilMultiAdd"]').on('click', (e) => {
      ilMultiFormValues.addEvent(e);
    });
    // add click event to --icons
    $('button[id*="ilMultiRmv"]').on('click', (e) => {
      ilMultiFormValues.removeEvent(e);
    });
    // add click event to down-icons
    $('button[id*="ilMultiDwn"]').on('click', (e) => {
      ilMultiFormValues.downEvent(e);
    });
    // add click event to up-icons
    $('button[id*="ilMultiUp"]').on('click', (e) => {
      ilMultiFormValues.upEvent(e);
    });
    // return triggers add  (BEFORE adding preset items)
    $('button[id*="ilMultiAdd"]').each(function () {
      const id = $(this).attr('id').split('~');
      // only text inputs are supported yet
      $(`div[id="ilFormField~${id[1]}~${id[2]}"]`).find(`input:text[id*="${id[1]}"]`).on('keydown', (e) => {
        ilMultiFormValues.keyDown(e);
      });
    });

    // handle preset values (in hidden inputs)
    $('input[id*="ilMultiValues"]').each(function () {
      ilMultiFormValues.handlePreset(this);
    });
  },

  /**
	 * Add multi item (click event)
	 *
	 * @param event e
	 */
  addEvent(e) {
    const id = $(e.delegateTarget).attr('id').split('~');
    ilMultiFormValues.add(id[1], id[2], []);
  },

  /**
	 * Remove multi item (click event)
	 *
	 * @param event e
	 */
  removeEvent(e) {
    const id = $(e.delegateTarget).attr('id').split('~');
    if ($(`div[id*="ilFormField~${id[1]}"]`).length > 1) {
      $(`div[id="ilFormField~${id[1]}~${id[2]}"]`).remove();
    } else {
      $(`div[id="ilFormField~${id[1]}~${id[2]}"]`).find(`input:text[id*="${id[1]}"]`).attr('value', '');
      $(`div[id="ilFormField~${id[1]}~${id[2]}"]`).find(`select[id*="${id[1]}"]`).attr('value', ''); // #18055
    }
  },

  /**
	 * Move multi item down (click event)
	 *
	 * @param event e
	 */
  downEvent(e) {
    const id = $(e.delegateTarget).attr('id').split('~');
    const original_element = $(`div[id="ilFormField~${id[1]}~${id[2]}"]`);
    const next = $(original_element).next();
    if (next[0]) {
      $(next).after($(original_element));
    }
  },

  /**
	 * Move multi item up (click event)
	 *
	 * @param event e
	 */
  upEvent(e) {
    const id = $(e.delegateTarget).attr('id').split('~');
    const original_element = $(`div[id="ilFormField~${id[1]}~${id[2]}"]`);
    const prev = $(original_element).prev();
    if (prev[0]) {
      $(prev).before($(original_element));
    }
  },

  /**
	 * Add multi item
	 *
	 * @param string group_id
	 * @param int index
	 * @param mixed preset
	 */
  add(group_id, index, preset) {
    // console.log(group_id);
    // console.log(index);
    // find maximum id in group
    let new_id = 0;
    let sub_id = 0;
    $(`div[id*="ilFormField~${group_id}"]`).each(function () {
      sub_id = $(this).attr('id').split('~')[2];
      sub_id = parseInt(sub_id);
      if (sub_id > new_id)	{
        new_id = sub_id;
      }
    });
    new_id += 1;

    const original_element = $(`div[id="ilFormField~${group_id}~${index}"]`);

    // clone original element
    const new_element = $(original_element).clone();

    // fix id of cloned element
    $(new_element).attr('id', `ilFormField~${group_id}~${new_id}`);

    // binding +-icon
    $(new_element).find('[id*="ilMultiAdd"]').each(function () {
      $(this).attr('id', `ilMultiAdd~${group_id}~${new_id}`);
      $(this).on('click', (e) => {
        ilMultiFormValues.addEvent(e);
      });
    });

    // binding --icon
    $(new_element).find('[id*="ilMultiRmv"]').each(function () {
      $(this).attr('id', `ilMultiRmv~${group_id}~${new_id}`);
      $(this).on('click', (e) => {
        ilMultiFormValues.removeEvent(e);
      });
    });

    // binding down-icon
    $(new_element).find('[id*="ilMultiDwn"]').each(function () {
      $(this).attr('id', `ilMultiDwn~${group_id}~${new_id}`);
      $(this).on('click', (e) => {
        ilMultiFormValues.downEvent(e);
      });
    });

    // binding up-icon
    $(new_element).find('[id*="ilMultiUp"]').each(function () {
      $(this).attr('id', `ilMultiUp~${group_id}~${new_id}`);
      $(this).on('click', (e) => {
        ilMultiFormValues.upEvent(e);
      });
    });

    // resetting value for new elements if none given
    ilMultiFormValues.setValue(new_element, preset);

    // insert clone into html
    $(original_element).after(new_element);

    // #15798 - remove multi-values hidden inputs (when disabled)
    if (preset) {
      $(new_element).find(`input:hidden[name="${group_id}[]"]`).each(function () {
        // #15944
        if ($(this).prev().attr('disabled')) {
          $(this).remove();
        }
      });
    }

    // add autocomplete
    if (
      typeof ilMultiFormValues.autocompleteConfigs[group_id] !== 'undefined'
			&& ilMultiFormValues.autocompleteConfigs[group_id] != ''
    ) {
      il.LegacyForm.autocomplete.init(
        $(new_element).find('input')[0],
        ilMultiFormValues.autocompleteConfigs[group_id],
      );
    }
  },

  /**
	 * Use value from hidden item to add preset multi items
	 *
	 * @param node element
	 */
  handlePreset(element) {
    // build id for added elements
    let element_id = $(element).attr('id').split('~');
    element_id = element_id[1];

    // add element for each additional value
    JSON.parse(atob($(element).attr('value'))).slice(1).forEach((value, i) => {
      ilMultiFormValues.add(element_id, i, (typeof value === 'object') ? value : [value]);
    });
  },

  /**
	 * Set value for input element, set option for select
	 *
	 * @param node element
	 * @param mixed preset
	 */
  setValue(element, preset) {
    let group_id = $(element).attr('id').split('~');
    const element_id = group_id[2];
    group_id = group_id[1];

    // fix id of first element?
    const original = $(`#${group_id}`);
    if (original) {
      $(original).attr('id', `${group_id}~0`);
    }

    // only select and text inputs are supported yet

    // fixing id
    // $(element).find('select[id*="' + group_id + '"]').attr('id', group_id + '~' + element_id);
    // $(element).find('input:text[id*="' + group_id + '"]').attr('id', group_id + '~' + element_id);
    // new version, alex 10.5.2013, works also if multiple input fields are within one div
    $(element).find(`select[id*="${group_id}"], input:text[id*="${group_id}"], span[id*="${group_id}"], input:hidden[id*="hidden${group_id}"]`).each(function () {
      const cid = $(this).attr('id').split('~');
      $(this).attr('id', `${cid[0]}~${element_id}`);
    });

		// try to set value
		$(element).find('select[id*="' + group_id + '"],input:text[id*="' + group_id + '"]').each(function (i) {
			const value = preset[Object.keys(preset)[i]];
			$(this).find('option:selected').removeAttr('selected');
      if (this.tagName === 'INPUT') {
        $(this).val('');
      }
      if(value !== undefined && value !== '') {
        $(this).val(value);
      }
		});

    // non-editable value
    $(element).find(`span[id*="${group_id}"]`).html(preset);
    $(element).find(`input:hidden[id*="hidden${group_id}"]`).attr('value', preset);

    // return triggers add
    $(element).find(`input:text[id*="${group_id}"]`).on('keydown', (e) => {
      ilMultiFormValues.keyDown(e);
    });
  },

  keyDown(e) {
    if (e.which == 13) {
      e.preventDefault();

      const id = $(e.delegateTarget).attr('id').split('~');
      if (id.length < 2) {
        id[1] = '0';
      }
      $(`[id="ilMultiAdd~${id[0]}~${id[1]}"]`).click();
    }
  },

  addAutocomplete(group_id, config) {
    ilMultiFormValues.autocompleteConfigs[group_id] = config;
  },
};

$(document).ready(() => {
  ilMultiFormValues.init();
});
