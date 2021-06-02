/**
 * Markup viewer initialization
 *
 * @author Adrian Lüthi <adi.l@bluewin.ch>
 */

var il = il || {};
il.UI = il.UI || {};

il.UI.markup = (function () {

	const initiateMarkup = function(markup) {
		new toastui.Editor.factory(
		{
            el: markup,
            viewer: true,
            initialValue: markup.innerHTML
        });
	}
	
	/**
	 * Public interface
	 */
	return {
		initiateMarkup: initiateMarkup
	};

})();