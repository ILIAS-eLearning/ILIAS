
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

ilCOPagePCInteractiveImage =
{
	/**
	 * Basic init function
	 */
	init: function ()
	{
		$("a.mark_cmd").click(this.markerCommand);
		$("a.ov_cmd").click(this.overlayCommand);
		$("a.pop_cmd").click(this.popupCommand);
	},
	
	/**
	 * Marker command
	 */
	markerCommand: function (e)
	{
		il.COPagePres.startDraggingMarker(e.target.id.substr(5));
	},
	
	/**
	 * Overlay command
	 */
	overlayCommand: function (e)
	{
		il.COPagePres.startDraggingOverlay(e.target.id.substr(3));
	},
	
	/**
	 * Popup command
	 */
	popupCommand: function (e)
	{
		il.COPagePres.startDraggingPopup(e.target.id.substr(4));
	}

}
il.Util.addOnLoad(function() {ilCOPagePCInteractiveImage.init();});
