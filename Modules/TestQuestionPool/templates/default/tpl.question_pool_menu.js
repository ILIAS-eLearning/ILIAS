<script type="text/javascript">
	var qpMenu;
	
	function createQpFilterMenu(p_oEvent) 
	{
		var filtericon = document.getElementById("qpfilter");
		var xPos = 0;
		var yPos = 0;
		if (filtericon != null) 
		{
			filtericon.style.visibility = 'visible';
			xPos = YAHOO.util.Dom.getX(filtericon);
			yPos = YAHOO.util.Dom.getY(filtericon) + 20;
		}
		qpMenu = new YAHOO.widget.Menu("qpMenu", { x : xPos, y : yPos, clicktohide: true, hidedelay: 100, zIndex: 1000, maxheight: 500 });
		qpMenu.addItems([
<!-- BEGIN menuitem -->			{ text:"{ITEM_TEXT}", url:"{ITEM_URL}"<!-- BEGIN selected -->, selected: true, checked: true<!-- END selected --> },<!-- END menuitem -->
		]);
	}

	function onQpFilterMenuMouseDown(p_oEvent) 
	{
		YAHOO.util.Event.stopPropagation(p_oEvent);
		qpMenu.render(document.body);
		var element = document.getElementById("qpfilter");
		var xPos = 0;
		var yPos = 0;
		if (element != null)
		{
			xPos = YAHOO.util.Dom.getX(element);
			yPos = YAHOO.util.Dom.getY(element) + 20;
		}
		qpMenu.moveTo(xPos, yPos);
		qpMenu.show();
	}

	YAHOO.util.Event.addListener(window, "load", createQpFilterMenu);
	YAHOO.util.Event.addListener("qpfilter", "mousedown", onQpFilterMenuMouseDown);
</script>