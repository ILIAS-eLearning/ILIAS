/**
 * Provides the behavior of textareas.
 *
 * @author Jesús López <lopez@leifos.com>
 */

var il = il || {};
il.UI = il.UI || {};

(function($, UI) {

    UI.textarea = (function ($) {
        /**
         * @param textarea_id
         * @param feedback_id_prefix
         * @param min_limit
         * @param max_limit
         */
        var changeCounter = function(textarea_id, feedback_id_prefix, min_limit, max_limit) {
            $('#'+textarea_id).keyup("input", function(){
                if(max_limit > 0) {
                    var currentLength = this.value.length;
                    var text_remaining = max_limit - currentLength;
                    $('#'+feedback_id_prefix+textarea_id).html(il.Language.txt("form_chars_remaining") +" "+ text_remaining);
                    return true;
                }
            })
        };

        /**
         * Public interface
         */
        return {
            changeCounter: changeCounter
        };

    })($);
})($, il.UI);