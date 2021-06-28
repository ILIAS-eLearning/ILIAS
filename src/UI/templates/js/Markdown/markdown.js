/**
 * Markup viewer initialization
 *
 * @author Adrian Lüthi <adi.l@bluewin.ch>
 */

var il = il || {};
il.UI = il.UI || {};

il.UI.Markdown = (function () {

	const initiateMarkup = function(markdown) {
		new toastui.Editor.factory(
		{
            el: markdown,
            viewer: true,
            initialValue: markdown.innerHTML
        });
	}
	
	/**
	 * Public interface
	 */
	return {
		initiateMarkup: initiateMarkup
	};

})();