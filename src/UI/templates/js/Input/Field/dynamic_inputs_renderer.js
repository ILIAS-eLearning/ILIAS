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
    const INPUT_ID_PLACEHOLDER = 'DYNAMIC_INPUT_ID';
    const SELECTORS = {
      dynamic_inputs_list: '.ui-input-dynamic-inputs-list',
      dynamic_input: '.ui-input-dynamic-input',
    };

    /**
     * @type {*[]}
     */
    let dynamic_inputs = [];

    /**
     * @param {string} input_id
     * @return {jQuery|null}
     */
    let render = function (input_id) {
      // abort if the DynamicInputsAware input was not yet registered.
      if (typeof dynamic_inputs[input_id] === 'undefined') {
        console.error(`Error: tried rendering dynamic sub input for '${input_id}' which is unregistered.`);
        return null;
      }

      let template = dynamic_inputs[input_id].template_html;

      template = addInputTemplateIds(template, dynamic_inputs[input_id].sub_input_count);

      template = $(template);
      template.appendTo($(`#${input_id} ${SELECTORS.dynamic_inputs_list}`));

      return template;
    }

    /**
     * @param {string} input_id
     * @param {string} template_html
     * @param {int} input_count
     */
    let init = function (input_id, template_html, input_count) {
      // abort if the DynamicInputsAware input was already registered.
      if (typeof dynamic_inputs[input_id] !== 'undefined') {
        console.error(`Error: tried to register input '${input_id}' as dynamic input twice.`);
        return;
      }

      let sub_inputs = $(template_html).find(':input');
      dynamic_inputs[input_id] = {
        template_html: template_html,
        sub_input_count: sub_inputs.length,
        index: input_count,
      };

      registerEventListeners(input_id);
    }

    /**
     * @param {string} input_id
     */
    let registerEventListeners = function (input_id) {
      // register the removal event listener for dynamic inputs.
      $(document).on('click', `#${input_id} .glyph[aria-label="Close"]`, removeDynamicInputHook);
    }

    let removeDynamicInputHook = function () {
      $(this).closest(SELECTORS.dynamic_input).remove();
    }

    /**
     * @param {string} template_html
     * @param {int} sub_input_count
     */
    let addInputTemplateIds = function (template_html, sub_input_count) {
      if (1 >= sub_input_count) {
        return replaceAll(template_html, INPUT_ID_PLACEHOLDER, generateId());
      }

      // Ids must not be all the same, therefore we need to generate
      // one for each sub-input contained in the template.
      for (let i = 0; i < sub_input_count; i++) {
        template_html = replaceAll(
          template_html,
          `${INPUT_ID_PLACEHOLDER}_${i}`,
          generateId()
        );
      }

      return template_html;
    }

    /**
     * @param {string} string
     * @param {string} expression
     * @param {string} replacement
     * @return {string}
     */
    let replaceAll = function (string, expression, replacement) {
      return string.replace(new RegExp(expression, 'g'), replacement);
    }

    /**
     * @return {string}
     */
    let generateId = function () {
      return Date.now().toString(36) + Math.random().toString(36).substr(2);
    }

    return {
      render: render,
      init: init,
    }
  })($)
})($, il.UI.Input);