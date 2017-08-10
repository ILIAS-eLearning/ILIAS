/**
 * A static factory class for tree view expand/collapse animations
 * @class TVAnim
 * @static
 */
YAHOO.widget.TVAnim = function() {
    return {
        /**
         * Constant for the fade in animation
         * @property FADE_IN
         * @type string
         * @static
         */
        FADE_IN: "TVFadeIn",

        /**
         * Constant for the fade out animation
         * @property FADE_OUT
         * @type string
         * @static
         */
        FADE_OUT: "TVFadeOut",

        /**
         * Returns a ygAnim instance of the given type
         * @method getAnim
         * @param type {string} the type of animation
         * @param el {HTMLElement} the element to element (probably the children div)
         * @param callback {function} function to invoke when the animation is done.
         * @return {YAHOO.util.Animation} the animation instance
         * @static
         */
        getAnim: function(type, el, callback) {
            if (YAHOO.widget[type]) {
                return new YAHOO.widget[type](el, callback);
            } else {
                return null;
            }
        },

        /**
         * Returns true if the specified animation class is available
         * @method isValid
         * @param type {string} the type of animation
         * @return {boolean} true if valid, false if not
         * @static
         */
        isValid: function(type) {
            return (YAHOO.widget[type]);
        }
    };
} ();
