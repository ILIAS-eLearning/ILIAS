var il = il || {};
il.UI = il.UI || {};

(function($, UI) {

    UI.textarea = (function ($) {
        /**
         * Old changeCounter
         * TODO-> I need the element id in the DOM.
         */
        var changeCounter = function(textarea_id, feedback_id, min_limit, max_limit) {
            var text_length = $('#'+textarea_id).val().length;
            if(max_limit > 0)
            {
                var text_remaining = max_limit - text_length;
                $('#'+feedback_id).html(il.Language.txt("form_chars_remaining") +" "+ text_remaining);
                return true;
            }
        };

        /**
         * TODO-> remove the old one and implement this counter
         * @param textarea_id
         * @param feedback_id
         * @param min_limit
         * @param max_limit
         */
        var changeCounterNew = function(textarea_id, feedback_id, min_limit, max_limit) {
            $('#'+textarea_id).addEventListener("input", function(){
                if(max_limit > 0) {
                    var currentLength = this.value.length;
                    var text_remaining = max_limit - currentLength;
                    $('#'+feedback_id).html(il.Language.txt("form_chars_remaining") +" "+ text_remaining);
                    return true;
                }
            })
        };

        /**
         * Public interface
         */
        return {
            //remote this old changeCounter
            changeCounter: changeCounter,
            changeCounterNew: changeCounterNew
        };

    })($);
})($, il.UI);