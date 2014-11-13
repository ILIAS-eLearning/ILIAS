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
				constraintoviewport: true,
				width:"{OVERLAY_WIDTH}",
				height:"{OVERLAY_HEIGHT}"
			} 
		); 
		overlay{FILTERELEMENTID}.setHeader('<div align="right"><a href="javascript:overlay{FILTERELEMENTID}.hide();"><img src="{IMAGE_CLOSE}" alt="{ALT_CLOSE}" title="{TITLE_CLOSE}" /></a></div>');
		overlay{FILTERELEMENTID}.setBody('<form name="form{FILTERELEMENTID}" method="post" action="{FORMACTION}"><input type="text" name="{TEXTFIELD_NAME}" id="txt_{TEXTFIELD_NAME}" tabindex="1" size="20" value="{VALUE_FILTER_TEXT}"/> <input type="submit" class="btn btn-default" name="cmd[filter]" value="{VALUE_SUBMIT_FILTER}"/> <input type="button" class="btn btn-default" name="reset" value="{VALUE_RESET_FILTER}" onclick="javascript: var textfield = document.getElementById(\'txt_{TEXTFIELD_NAME}\'); textfield.value = \'\'; document.form{FILTERELEMENTID}.submit();" /></form>');
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
	
	function onTextFilterMouseUp{FILTERELEMENTID}(p_oEvent)
	{
		var textfield = document.getElementById('txt_{TEXTFIELD_NAME}');
		if (textfield != null) textfield.focus();
	}

	YAHOO.util.Event.addListener(window, "load", createTextFilter{FILTERELEMENTID});
	YAHOO.util.Event.addListener("{FILTERELEMENTID}", "mousedown", onTextFilterMouseDown{FILTERELEMENTID});
	YAHOO.util.Event.addListener("{FILTERELEMENTID}", "mouseup", onTextFilterMouseUp{FILTERELEMENTID});
</script>