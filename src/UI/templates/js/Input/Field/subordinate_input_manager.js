/**
 * subordinate_input_manager.js
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * This script wraps the duplication of input-templates within
 * their current context or container.
 */

let il = il || {};
il.UI = il.UI || {};

(function ($, UI) {

    /**
     * Public interface for the subordinate input manager.
     *
     * @type {{init: init, render: render, remove: remove}}
     */
    UI.SubordinateInputManager = (function ($) {

        /**
         * Determines whether this manager is debugged or not.
         *
         * @type {boolean}
         */
        const DEBUG = true;

        /**
         * @type {*[]}
         */
        let instances = [];

        /**
         * Helper function to debug this manager.
         *
         * @param {*} vars
         */
        let debug = function (...vars) {
            if (DEBUG) {
                for (let i = 0, i_max = vars.length; i < i_max; i++) {
                    console.log(vars[i]);
                }
            }
        }

        /**
         * Removes subordinate inputs for the given container id.
         *
         * @param {string}     container_id
         * @param {string|int} inputs_index
         */
        let remove = function (container_id, inputs_index) {
            if (undefined !== instances[container_id]) {
                let container = $(`#${container_id}`);
                let inputs    = container.find(`input[name^='[${inputs_index}][']`);

                if (0 < inputs.length) {
                    for (let i = 0, i_max = inputs.length; i < i_max; i++) {
                        $(inputs[i]).closest('.form-group.row').remove();
                    }
                } else {
                    debug(`Could not find any inputs in container '${container_id}' with index '${inputs_index}'.`);
                }

                // @TODO: maybe trigger some sort of hook for post
                //        removal actions.
            } else {
                debug(`Tried removing subordinate inputs for container '${container_id}' which was not yet initialized.`);
            }
        };

        /**
         * Renders subordinate inputs for the given container id.
         *
         * @param {string} container_id
         */
        let render = function (container_id) {
            if (undefined !== instances[container_id]) {
                let container = $(`#${container_id}`);
                let inputs    = $(instances[container_id].inputs).find('input, select, textarea');

                for (let i = 0, i_max = inputs.length; i < i_max; i++) {
                    let input =  $(inputs[i]);
                    input.attr('name', input.attr('name').replace('SUBORDINATE_INPUT_INDEX', instances[container_id].count));
                }

                instances[container_id].count++;
                container.append(inputs);
            } else {
                debug(`Tried rendering subordinate inputs for container ${container_id} which was not yet initialized.`);
            }
        };

        /**
         * Initializes an instance of this manager for the given
         * information.
         *
         * @param {string} container_id
         * @param {int}    initial_count
         * @param {jQuery} template
         */
        let init = function (container_id, initial_count, template) {
            let html = $('<div />').append(template.clone()).html();

            instances[container_id] = {
                count:  initial_count,
                inputs: html,
            };

            template.remove();
        };

        /**
         * Helper function to generate (very) unique id's for
         * additional inputs rendered.
         *
         * @return {string}
         */
        let generateId = function () {
            return Date.now().toString(36) + Math.random().toString(36).substr(2);
        };

        return {
            init: init,
            render: render,
            remove: remove,
        };

    })($);
})($, il.UI);