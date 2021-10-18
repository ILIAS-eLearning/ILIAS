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
     * Public interface of the duplicator.
     *
     * @type {{duplicate: duplicate}}
     */
    UI.SubordinateInpputManager = (function ($) {

        /**
         * descr goes here
         *
         * @param {object} element
         * @param {object} container
         */
        let duplicate = function (element, container) {
            
        };

        return {
            duplicate: duplicate,
        };

    })($);
})($, il.UI);