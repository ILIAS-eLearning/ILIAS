/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

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
		this.tooltips.push({el_id: el_id, cfg: cfg});
	},

	/**
	 * Init tooltips
	 */
	init: function () {
		var k;

		for (k in this.tooltips) {
//			this.tooltips[k].tp = new YAHOO.widget.Tooltip("ttip_" + this.tooltips[k].el_id,
//				this.tooltips[k].cfg);
			$("#" + this.tooltips[k].el_id).qtip({
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
