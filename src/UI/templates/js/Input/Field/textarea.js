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
            //edit the default byline with character limits.
            var byline =  $('#'+feedback_id_prefix+textarea_id).next().first().html();
            if(min_limit > 0) {
                byline = byline+'<br>'+il.Language.txt("ui_chars_min")+' '+min_limit;
            }
            if(max_limit > 0) {
                byline = byline+' '+il.Language.txt("ui_chars_max")+' '+max_limit;
            }
            $('#'+feedback_id_prefix+textarea_id).next().html(byline);
           //update feedback counter
            $('#'+textarea_id).keyup("input", function(){
                if(max_limit > 0) {
                    var currentLength = this.value.length;
                    var text_remaining = max_limit - currentLength;
                    $('#'+feedback_id_prefix+textarea_id).html(il.Language.txt("ui_chars_remaining") +" "+ text_remaining);
                    return true;
                }
            });
        };

        /**
         * Public interface
         */
        return {
            changeCounter: changeCounter
        };

    })($);
})($, il.UI);