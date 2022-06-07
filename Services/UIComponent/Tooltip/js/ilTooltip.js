/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Tooltip object
 */
il.Tooltip = {
	tooltips: [],	// array for all tooltips

	/**
	 * Add a tooltip
	 *
	 * @param string el_id element id
	 * @param object cfg configuration object
	 */
	add: function (el_id, cfg) {
		this.tooltips.push({el_id: "#" + el_id, cfg: cfg});
	},

	/**
	 * Add a tooltip to the nearest element given
	 *
	 * @param string el_id element id
	 * @param object cfg configuration object
	 */
	addToNearest: function (el_id, nearest_element_selector, cfg) {
		this.tooltips.push({el_id: $("#" + el_id).closest(nearest_element_selector), cfg: cfg});
	},

	/**
	 * Add a tooltip
	 *
	 * @param string selector
	 * @param object cfg configuration object
	 */
	addBySelector: function (el_id, cfg) {
		this.tooltips.push({el_id: el_id, cfg: cfg});
	},

	/**
	 * Init tooltips
	 */
	init: function () {
		var k;

		for (k in this.tooltips) {
			$(this.tooltips[k].el_id).qtip({
				position: {
					my: this.tooltips[k].cfg.my,
					at: this.tooltips[k].cfg.at,
					viewport: $(window)
				},
				style: {
					classes: 'ui-tooltip-shadow ui-tooltip-rounded'
				},
				content: {
					text: this.tooltips[k].cfg.text
				}
			});
		}
	}
};
