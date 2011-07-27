/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Tooltip object
 */
var ilTooltip =
{
	tooltips: [],	// array for all tooltips

	/**
	 * Add a tooltip
	 *
	 * @param string el_id element id
	 * @param object cfg configuration object
	 */
	add: function(el_id, cfg) {
		this.tooltips.push({el_id: el_id, cfg: cfg});
	},
	
	/**
	 * Init tooltips
	 */
	init: function() {
		
		var k;

		for (k in this.tooltips)
		{
			this.tooltips[k].tp = new YAHOO.widget.Tooltip("ttip_" + this.tooltips[k].el_id,
				this.tooltips[k].cfg);
		}
	}
}
