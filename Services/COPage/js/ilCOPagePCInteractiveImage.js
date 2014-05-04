
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

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
