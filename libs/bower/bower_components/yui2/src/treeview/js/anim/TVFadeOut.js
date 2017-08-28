/**
 * A 1/2 second fade out animation.
 * @class TVFadeOut
 * @constructor
 * @param el {HTMLElement} the element to animate
 * @param callback {Function} function to invoke when the animation is finished
 */
YAHOO.widget.TVFadeOut = function(el, callback) {
    /**
     * The element to animate
     * @property el
     * @type HTMLElement
     */
    this.el = el;

    /**
     * the callback to invoke when the animation is complete
     * @property callback
     * @type function
     */
    this.callback = callback;

    this.logger = new YAHOO.widget.LogWriter(this.toString());
};

YAHOO.widget.TVFadeOut.prototype = {
    /**
     * Performs the animation
     * @method animate
     */
    animate: function() {
        var tvanim = this;
        var dur = 0.4;
        var a = new YAHOO.util.Anim(this.el, {opacity: {from: 1, to: 0.1, unit:""}}, dur);
        a.onComplete.subscribe( function() { tvanim.onComplete(); } );
        a.animate();
    },

    /**
     * Clean up and invoke callback
     * @method onComplete
     */
    onComplete: function() {
        var s = this.el.style;
        s.display = "none";
        s.opacity = 1;
        s.filter = "alpha(opacity=100)";
        this.callback();
    },

    /**
     * toString
     * @method toString
     * @return {string} the string representation of the instance
     */
    toString: function() {
        return "TVFadeOut";
    }
};
