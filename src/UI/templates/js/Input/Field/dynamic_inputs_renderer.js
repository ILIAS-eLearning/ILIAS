/**
 * this script is responsible for clientside rendering of Inputs
 * ILIAS\UI\Component\Input\Field\DynamicInputsAware.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */

var il = il || {};
il.UI = il.UI || {};
il.UI.Input = il.UI.Input || {};

(function ($, Input) {
  Input.DynamicInputsRenderer = (function ($) {
    const INPUT_SELECTOR = 'input, select, textarea';
    const INPUT_INDEX_PLACEHOLDER = 'DYNAMIC_INPUT_INDEX';
    const INPUT_ID_PLACEHOLDER = 'DYNAMIC_INPUT_ID';

    /**
     * @type {*[]}
     */
    let dynamic_inputs = [];

    /**
     * @param {string} input_id
     */
    let render = function (input_id) {
      abortIfUndefined(input_id);

      $(`#${input_id} > ${dynamic_inputs[input_id].list_selector}`).append(
        indexDynamicInput(
          dynamic_inputs[input_id].template,
          dynamic_inputs[input_id].index
        )
      );

      dynamic_inputs[input_id].index++;
    }

    /**
     * @param {string} input_id
     * @param {int} index
     */
    let remove = function (input_id, index) {
      abortIfUndefined(input_id);

      let input = $(`#${input_id} > ${dynamic_inputs[input_id].list_selector}`).find(``);
    }

    /**
     * @param {string} input_id
     * @param {string} template_html
     * @param {string} list_selector (including '.' or '#')
     */
    let init = function (input_id, template_html, list_selector) {
      if (typeof dynamic_inputs[input_id] !== 'undefined') {
        return;
      }

      let current_dynamic_inputs = $(`#${input_id} ${list_selector}`).find(INPUT_SELECTOR);
      let count = current_dynamic_inputs.length;

      console.log("here:");
      console.log(`#${input_id} ${list_selector}`);
      console.log($(`#${input_id} ${list_selector}`));

      if (0 < count) {
        indexServersideInputs(current_dynamic_inputs);
      }

      dynamic_inputs[input_id] = {
        template: replaceInputIdWithPlaceholder(template_html),
        index: (0 < count) ? (count - 1) : count,
        list_selector: list_selector,
      };
    }

    /**
     * @param {string} template_html
     * @return {string}
     */
    let replaceInputIdWithPlaceholder = function (template_html) {
      let template = $(template_html);

    }

    /**
     * @param {[]} inputs
     */
    let indexServersideInputs = function (inputs) {
      for (let i = 0; i < count; i++) {
        $(inputs[i]).attr(
          'name',
          input.attr('name').replace(INPUT_INDEX_PLACEHOLDER, String(i))
        )
      }
    }

    /**
     * @param {string} input_id
     */
    let abortIfUndefined = function (input_id) {
      if (typeof dynamic_inputs[input_id] === 'undefined') {
        console.error(`Error: cannot render dynamic input for '${input_id}' before initialized.`);
        return;
      }
    }

    /**
     * @param {string} template
     * @param {int} index
     * @return {string}
     */
    let indexDynamicInput = function (template, index) {
      return template.replace('DYNAMIC_INPUT_INDEX', String(index));
    }

    /**
     * @return {string}
     */
    let generateId = function () {
      return Date.now().toString(36) + Math.random().toString(36).substr(2);
    }

    return {
      render: render,
      remove: remove,
      init: init,
    }
  })($)
})($, il.UI.Input);