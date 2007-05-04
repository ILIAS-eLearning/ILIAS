<script type="text/javascript">
	var overlay{FILTERELEMENTID};

	function createTextFilter{FILTERELEMENTID}(p_oEvent) 
	{
		var filtericon = document.getElementById("{FILTERELEMENTID}");
		var xPos = 0;
		var yPos = 0;
		if (filtericon != null) 
		{
			filtericon.style.visibility = 'visible';
		}
		overlay{FILTERELEMENTID} = new YAHOO.widget.Overlay("overlay{FILTERELEMENTID}", 
			{ xy:[0,0],
				visible:false, 
				width:"{OVERLAY_WIDTH}",
				height:"{OVERLAY_HEIGHT}"
			} 
		); 
		overlay{FILTERELEMENTID}.setHeader('<div align="right"><a href="javascript:overlay{FILTERELEMENTID}.hide();">Close</a></div>');
		overlay{FILTERELEMENTID}.setBody('<form name="form{FILTERELEMENTID}" method="post" action="{FORMACTION}"><input type="text" name="filter_text" size="20" value="{VALUE_FILTER_TEXT}"/> <input type="submit" class="submit" name="cmd[filter]" value="{VALUE_SUBMIT_FILTER}"/> <input type="submit" class="submit" name="cmd[resetFilter]" value="{VALUE_RESET_FILTER}" /> <input type="hidden" name="sel_filter_type" value="title"/></form>');
	}

	function onTextFilterMouseDown{FILTERELEMENTID}(p_oEvent) 
	{
		YAHOO.util.Event.stopPropagation(p_oEvent);
		overlay{FILTERELEMENTID}.render(document.body);

		var element = document.getElementById("{FILTERELEMENTID}");
		var xPos = 0;
		var yPos = 0;
		if (element != null)
		{
			xPos = YAHOO.util.Dom.getX(element);
			yPos = YAHOO.util.Dom.getY(element) + 20;
		}
		overlay{FILTERELEMENTID}.moveTo(xPos, yPos);
		overlay{FILTERELEMENTID}.show();
	}

	YAHOO.util.Event.addListener(window, "load", createTextFilter{FILTERELEMENTID});
	YAHOO.util.Event.addListener("{FILTERELEMENTID}", "mousedown", onTextFilterMouseDown{FILTERELEMENTID});
</script>