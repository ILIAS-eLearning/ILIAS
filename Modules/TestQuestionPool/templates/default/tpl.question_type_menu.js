<script type="text/javascript">
	var qtMenu;
	
	function createFilterMenu(p_oEvent) 
	{
		var filtericon = document.getElementById("filter");
		var xPos = 0;
		var yPos = 0;
		if (filtericon != null) 
		{
			filtericon.style.visibility = 'visible';
			xPos = YAHOO.util.Dom.getX(filtericon);
			yPos = YAHOO.util.Dom.getY(filtericon) + 20;
			var filterQuestionHTML = document.getElementById("filterQuestionHTML");
			if (filterQuestionHTML != null)
			{
				filterQuestionHTML.style.visibility = 'hidden';
			}
		}
		qtMenu = new YAHOO.widget.Menu("qtMenu", { x : xPos, y : yPos, clicktohide: true, hidedelay: 100, zIndex: 1000, maxheight: 500 });
		qtMenu.addItems([
<!-- BEGIN menuitem -->			{ text:"{ITEM_TEXT}", url:"{ITEM_URL}"<!-- BEGIN selected -->, selected: true, checked: true<!-- END selected --> },<!-- END menuitem -->
		]);
	}

	function onFilterMenuMouseDown(p_oEvent) 
	{
		YAHOO.util.Event.stopPropagation(p_oEvent);
		qtMenu.render(document.body);
		var element = document.getElementById("filter");
		var xPos = 0;
		var yPos = 0;
		if (element != null)
		{
			xPos = YAHOO.util.Dom.getX(element);
			yPos = YAHOO.util.Dom.getY(element) + 20;
		}
		qtMenu.moveTo(xPos, yPos);
		qtMenu.show();
	}

	YAHOO.util.Event.addListener(window, "load", createFilterMenu);
	YAHOO.util.Event.addListener("filter", "mousedown", onFilterMenuMouseDown);
</script>